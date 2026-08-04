<?php


namespace Postqueue;

defined( 'ABSPATH' ) || exit;


class Assets extends Component\Assets {

	public function __construct( Component\Plugin $plugin ) {
		parent::__construct( $plugin );
		add_action( 'enqueue_block_editor_assets', [ $this, 'onBlockEditorEnqueue' ] );
	}

	/**
	 * The document sidebar panel and the Query Loop variation. Block editor only, so
	 * neither is loaded on the classic editor or the front end.
	 */
	public function onBlockEditorEnqueue() {
		if ( ! current_user_can( Plugin::instance()->editor->getCapability() ) ) {
			return;
		}
		if ( ! $this->registerScript( Plugin::HANDLE_BLOCK_EDITOR_JS, 'dist/block-editor.js' ) ) {
			return;
		}
		wp_localize_script(
			Plugin::HANDLE_BLOCK_EDITOR_JS,
			'PostQueueBlockEditor',
			[
				"rest_namespace" => REST::NAMESPACE,
				"rest_field"     => Plugin::REST_FIELD_QUEUES,
				"query_key"      => QueryLoop::QUERY_KEY,
				// The core term selector only shows its search field once a taxonomy has
				// enough terms to need one. The exact number is not readable from the
				// minified bundle; 8 is what WordPress's own source has used for
				// MIN_TERMS_COUNT_FOR_FILTER.
				"search_threshold" => (int) apply_filters( Plugin::FILTER_POSTQUEUE_PANEL_SEARCH_THRESHOLD, 8 ),
				// Plain _x() for the same reason as on the postqueue screen: localize()
				// does not decode entities nested this deep, so esc_html_x() turned the
				// apostrophe in select_help into a literal &#039; on screen.
				"i18n"           => [
					"panel_title"    => _x( "Postqueues", "block editor", 'postqueue' ),
					"panel_empty"    => _x( "No postqueues exist yet.", "block editor", 'postqueue' ),
					"panel_search"   => _x( "Search postqueues", "block editor", 'postqueue' ),
					"panel_no_match" => _x( "No postqueue matches.", "block editor", 'postqueue' ),
					"create_toggle"  => _x( "Add new postqueue", "block editor", 'postqueue' ),
					"create_label"   => _x( "New postqueue name", "block editor", 'postqueue' ),
					"create_submit"  => _x( "Add new postqueue", "block editor", 'postqueue' ),
					"create_error"   => _x( "The postqueue could not be created.", "block editor", 'postqueue' ),
					"variation"      => _x( "Postqueue", "block editor", 'postqueue' ),
					"variation_desc" => _x( "A manually ordered queue of posts.", "block editor", 'postqueue' ),
					"select_queue"   => _x( "Postqueue", "block editor", 'postqueue' ),
					"select_none"    => _x( "— none —", "block editor", 'postqueue' ),
					"select_help"    => _x( "The loop shows the posts of this queue, in the queue's order.", "block editor", 'postqueue' ),
				],
			]
		);
		wp_enqueue_script( Plugin::HANDLE_BLOCK_EDITOR_JS );
	}

	public function onAdminEnqueue( string $hook ) {
		parent::onAdminEnqueue( $hook );
		$this->registerStyle(
			Plugin::HANDLE_EDITOR_CSS,
			'dist/editor.css'
		);
		// The dependencies come from dist/editor.asset.php, which the build writes. The
		// editor used to name jquery, jquery-ui-autocomplete and jquery-ui-sortable on
		// top of that; nothing in it has touched jQuery since the rewrite.
		$this->registerScript(
			Plugin::HANDLE_EDITOR_JS,
			'dist/editor.js'
		);
		// Plain _x() here, no esc_html_x(). WP_Scripts::localize() only runs
		// html_entity_decode() over top-level scalars and skips anything that is not one,
		// so entities inside this nested array would reach the screen as written - an
		// apostrophe showing up as &#039;.
		wp_localize_script(
			Plugin::HANDLE_EDITOR_JS,
			'PostQueue',
			[
				"rest_namespace" => REST::NAMESPACE,
				"DOMAIN"         => Plugin::DOMAIN,
				"i18n"           => [
					"save"                    => _x( "Save", "postqueue screen", 'postqueue' ),
					"reset"                   => _x( "Reset", "postqueue screen", 'postqueue' ),
					"saved"                   => _x( "Order saved.", "postqueue screen", 'postqueue' ),
					"remove"                  => _x( "Remove", "postqueue screen", 'postqueue' ),
					"search_label"            => _x( "Add a post to this postqueue", "postqueue screen", 'postqueue' ),
					"search_post_placeholder" => _x( "Search for posts", "postqueue screen", 'postqueue' ),
					"search_help"             => _x( "Pick a result to put it at the top of the queue.", "postqueue screen", 'postqueue' ),
					"no_posts_found"          => _x( "No posts found.", "postqueue screen", 'postqueue' ),
					"queue_empty"             => _x( "This postqueue is empty. Search for a post above to add one.", "postqueue screen", 'postqueue' ),
					"column_order"            => _x( "Order", "postqueue screen", 'postqueue' ),
					"column_post"             => _x( "Post", "postqueue screen", 'postqueue' ),
					"column_status"           => _x( "Status", "postqueue screen", 'postqueue' ),
					"column_date"             => _x( "Date", "postqueue screen", 'postqueue' ),
					"column_actions"          => _x( "Actions", "postqueue screen", 'postqueue' ),
					"move_up"                 => _x( "Move up", "postqueue screen", 'postqueue' ),
					"move_down"               => _x( "Move down", "postqueue screen", 'postqueue' ),
					"drag_hint"               => _x( "Drag to reorder", "postqueue screen", 'postqueue' ),
				],
			]
		);
	}
}