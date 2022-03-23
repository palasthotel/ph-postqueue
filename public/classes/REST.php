<?php


namespace Postqueue;


use WP_REST_Request;
use WP_REST_Server;

class REST extends Component\Component {

	const NAMESPACE = "postqueue/v1";

	public function onCreate() {
		add_action( 'rest_api_init', [ $this, 'rest_api_init' ] );
	}

	public function rest_api_init() {
		register_rest_route( REST::NAMESPACE, '/queues', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => function ( WP_REST_Request $request ) {
				$name   = $request->get_param( "name" );
				$result = $this->plugin->store->create( $name );

				do_action( "ph_postqueue_created", (object) array( "id" => $result->id, "slug" => $result->slug ) );

				return $result;
			},
			'permission_callback' => [ $this, 'permissionCheck' ],
			'args'                => [
				"name" => array(
					'required' => true,
					"type"     => "string",
				),
			]
		) );
		register_rest_route( REST::NAMESPACE, '/queues', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => function ( WP_REST_Request $request ) {
				if ( $request->has_param( "search" ) && ! empty( $request->get_param( "search" ) ) ) {
					$this->plugin->store->search($request->get_param("search"));
				}
				return $this->plugin->store->get_queues();
			},
			'permission_callback' => [ $this, 'permissionCheck' ],
			'args'                => [
				"search" => array(
					'required' => false,
					'type'     => 'string',
				),
			]
		) );
		register_rest_route( REST::NAMESPACE, '/queues/(?P<id>\d+)', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => function ( WP_REST_Request $request ) {
				$items = $this->plugin->store->get_queue_by_id( $request->get_param( "id" ) );
				return array_map(function($item){
					$post_id = $item->post_id;
					$item->edit_post_link = get_edit_post_link($post_id, '');
					$item->post_status = get_post_status($post_id);
					$item->post_date = get_the_date('l, F j, Y', $post_id);
					return $item;
				}, $items);
			},
			'permission_callback' => [ $this, 'permissionCheck' ],
		) );
		register_rest_route( REST::NAMESPACE, '/queues/(?P<id>\d+)', array(
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => function ( WP_REST_Request $request ) {
				$queue_id = $request->get_param( "id" );
				do_action( "ph_postqueue_deleting", $queue_id );
				$this->plugin->store->delete_queue( $queue_id );

				return true;
			},
			'permission_callback' => [ $this, 'permissionCheck' ],
		) );
		register_rest_route( REST::NAMESPACE, '/queues/(?P<id>\d+)/items', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => function ( WP_REST_Request $request ) {

				$queue_id = $request->get_param( "id" );
				$items    = $request->get_param( "items" );

				$this->plugin->store->queue_clear( $queue_id );
				$this->plugin->store->queue_add_all( $queue_id, $items );

				return [
					"queue_id" => $queue_id,
					"items"    => $this->plugin->store->get_queue_by_id( $queue_id ),
				];
			},
			'args'                => [
				"items" => array(
					'required'          => true,
					'validate_callback' => function ( $value ) {
						return is_array( $value );
					},
					'sanitize_callback' => function ( $value ) {
						return array_map( function ( $item ) {
							return intval( $item );
						}, $value );
					},
				),
			],
			'permission_callback' => [ $this, 'permissionCheck' ],
		) );
		register_rest_route( REST::NAMESPACE, '/queues/(?P<id>\d+)/items/(?P<pid>\d+)', array(
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => function ( WP_REST_Request $request ) {

				$queue_id = $request->get_param( "id" );
				$post_id  = $request->get_param( "pid" );

				$this->plugin->store->delete_queue_post( $queue_id, $post_id );

				return [
					"queue_id" => $queue_id,
					"items"    => $this->plugin->store->get_queue_by_id( $queue_id ),
				];
			},
			'permission_callback' => [ $this, 'permissionCheck' ],
		) );
		register_rest_route( REST::NAMESPACE, '/posts', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => function ( WP_REST_Request $request ) {
				$search = $request->get_param("search");

				$result         = (object) array();
				$result->search = sanitize_text_field( $search);

				$postTypes = implode(
					", ",
					array_map( function($item){ return "'$item'"; }, get_post_types(["public" => true]))
				);

				global $wpdb;
				$sql = "SELECT ID, post_title FROM " . $wpdb->prefix . "posts" .
				       " WHERE" .
				       " (".
				        "post_title LIKE '%" . $result->search . "%'" .
				        " AND (post_status = 'publish' OR post_status = 'future' ) ".
				        " AND post_type IN ($postTypes)".
				       ")" .
				       " OR ID = '" . $result->search . "'" .
				       " ORDER BY ID DESC LIMIT 10";
				$results = $wpdb->get_results($sql);


				$result->posts = array();
				foreach ( $results as $index => $post ) {
					$p               = (object) array();
					$p->post_id      = $post->ID;
					$p->post_title   = $post->post_title;
					$result->posts[] = $p;
				}

				return $result;
			},
			'permission_callback' => [ $this, 'permissionCheck' ],
			'args'                => [
				"search" => array(
					'required' => true,
					'type'     => 'string',
				),
			]
		) );

	}

	public function permissionCheck( WP_REST_Request $request ): bool {
		return current_user_can( $this->plugin->editor->getCapability() );
	}

}