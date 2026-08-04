<?php


namespace Postqueue;

defined( 'ABSPATH' ) || exit;


use WP_REST_Request;
use WP_REST_Server;

class REST extends Component\Component {

	const NAMESPACE = "postqueue/v1";

	public function onCreate() {
		add_action( 'rest_api_init', [ $this, 'rest_api_init' ] );
	}

	public function rest_api_init() {

		$this->register_post_field();
		register_rest_route( REST::NAMESPACE, '/queues', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => function ( WP_REST_Request $request ) {
				$name   = $request->get_param( "name" );
				$result = $this->plugin->store->create( $name );

				if ( empty( $result->success ) ) {
					$duplicate = isset( $result->reason ) && 'duplicate' === $result->reason;

					return new \WP_Error(
						$duplicate ? 'postqueue_duplicate_name' : 'postqueue_invalid_name',
						$duplicate
							? __( 'A postqueue with that name already exists.', Plugin::DOMAIN )
							: __( 'Please enter a name for the postqueue.', Plugin::DOMAIN ),
						array( 'status' => 400 )
					);
				}

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
					// The result used to be discarded and the full list returned, so
					// searching queues did nothing at all.
					return $this->plugin->store->search( $request->get_param( "search" ) );
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
				$result->search = sanitize_text_field( $search );

				// sanitize_text_field() does not escape SQL - it leaves single quotes
				// alone - so every value here goes through $wpdb->prepare(), and the
				// LIKE term through esc_like() so a % in a search does not become a
				// wildcard.
				$postTypes    = array_values( get_post_types( [ "public" => true ] ) );
				$placeholders = implode( ", ", array_fill( 0, max( 1, count( $postTypes ) ), "%s" ) );

				global $wpdb;
				$sql = $wpdb->prepare(
					"SELECT ID, post_title FROM {$wpdb->posts}"
					. " WHERE ("
					. " post_title LIKE %s"
					. " AND (post_status = 'publish' OR post_status = 'future')"
					. " AND post_type IN ($placeholders)"
					. " ) OR ID = %d"
					. " ORDER BY ID DESC LIMIT 10",
					array_merge(
						[ '%' . $wpdb->esc_like( $result->search ) . '%' ],
						$postTypes ?: [ '' ],
						[ intval( $result->search ) ]
					)
				);
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

	/**
	 * The queues a post belongs to, as a field on the post itself.
	 *
	 * A field rather than its own routes, because that is what makes the panel behave
	 * like the category panel: the editor reads it off the post, edits are held in the
	 * editor's state, and they are written when the post is saved - not on every click.
	 */
	public function register_post_field() {
		$postTypes = array_values( get_post_types( [ 'public' => true, 'show_in_rest' => true ] ) );

		register_rest_field( $postTypes, Plugin::REST_FIELD_QUEUES, [
			'get_callback'    => function ( $post ) {
				return $this->plugin->store->get_queue_ids_for_post( (int) $post['id'] );
			},
			'update_callback' => function ( $value, $post ) {
				// register_rest_field() has no permission_callback - a key passed there
				// is silently ignored - so the check belongs here. Core already refuses
				// the request without edit_post; this is the plugin's own gate on top.
				if ( ! current_user_can( $this->plugin->editor->getCapability() ) ) {
					return new \WP_Error(
						'postqueue_cannot_edit',
						'You are not allowed to change postqueues.',
						[ 'status' => rest_authorization_required_code() ]
					);
				}
				if ( ! is_array( $value ) ) {
					return;
				}

				$store   = $this->plugin->store;
				$post_id = (int) $post->ID;
				$wanted  = array_values( array_unique( array_filter(
					array_map( 'intval', $value ),
					function ( $id ) use ( $store ) {
						return $id > 0 && $store->queueExists( $id );
					}
				) ) );
				$current = $store->get_queue_ids_for_post( $post_id );

				foreach ( array_diff( $current, $wanted ) as $queue_id ) {
					$store->remove_post_from_queue( $post_id, $queue_id );
				}
				foreach ( array_diff( $wanted, $current ) as $queue_id ) {
					$store->add_post_to_queue( $post_id, $queue_id );
				}
			},
			'schema'          => [
				'description' => 'Ids of the postqueues this post belongs to.',
				'type'        => 'array',
				'items'       => [ 'type' => 'integer' ],
				'context'     => [ 'view', 'edit' ],
			],
		] );
	}

	public function permissionCheck( WP_REST_Request $request ): bool {
		return current_user_can( $this->plugin->editor->getCapability() );
	}

}