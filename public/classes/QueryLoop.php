<?php

namespace Postqueue;

defined( 'ABSPATH' ) || exit;

/**
 * Lets the core Query Loop block filter by postqueue.
 *
 * The block variation is registered in JavaScript and writes the queue slug into the
 * block's own query attribute, as query.postqueue. Two places then have to turn that
 * into a real query:
 *
 * - the front end, through query_loop_block_query_vars
 * - the editor preview, which is not rendered by PHP at all: the Post Template block
 *   builds a REST request from the query attribute and spreads keys it does not know
 *   into it, so postqueue arrives at /wp/v2/<post type> and is picked up by
 *   rest_{$post_type}_query
 *
 * Both paths end in the same translation: the queue's post ids as post__in, ordered by
 * that list.
 */
class QueryLoop extends Component\Component {

	const QUERY_KEY = "postqueue";

	public function onCreate() {
		add_filter( 'query_loop_block_query_vars', [ $this, 'query_loop_block_query_vars' ], 10, 2 );

		add_action( 'rest_api_init', function () {
			foreach ( get_post_types( [ 'public' => true, 'show_in_rest' => true ] ) as $post_type ) {
				add_filter( "rest_{$post_type}_query", [ $this, 'rest_query' ], 10, 2 );
			}
		} );
	}

	/**
	 * Front end: the Query Loop's WP_Query arguments.
	 *
	 * @param array     $query
	 * @param \WP_Block $block
	 * @return array
	 */
	public function query_loop_block_query_vars( $query, $block ) {
		$slug = $block->context['query'][ self::QUERY_KEY ] ?? null;

		return $this->applyQueue( $query, $slug );
	}

	/**
	 * Editor preview: the REST arguments of the posts collection.
	 *
	 * @param array             $args
	 * @param \WP_REST_Request  $request
	 * @return array
	 */
	public function rest_query( $args, $request ) {
		return $this->applyQueue( $args, $request->get_param( self::QUERY_KEY ) );
	}

	/**
	 * Turns a queue slug into post__in, or leaves the arguments alone.
	 */
	private function applyQueue( array $args, $slug ): array {
		if ( ! is_string( $slug ) || '' === $slug ) {
			return $args;
		}

		$ids = array();
		foreach ( $this->plugin->store->get_queue_by_slug( sanitize_title( $slug ) ) as $item ) {
			$ids[] = (int) $item->post_id;
		}

		// An empty post__in is ignored by WP_Query, which would silently return every
		// post - the opposite of what an empty queue means. 0 matches nothing.
		$args['post__in'] = empty( $ids ) ? array( 0 ) : $ids;
		$args['orderby']  = 'post__in';
		unset( $args['order'] );

		return $args;
	}
}
