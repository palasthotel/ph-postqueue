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
				// Below this many queues a search field is noise; above it, the list
				// needs one - the same shape as the core category panel.
				"search_threshold" => (int) apply_filters( Plugin::FILTER_POSTQUEUE_PANEL_SEARCH_THRESHOLD, 3 ),
				"i18n"           => [
					"panel_title"   => esc_html_x( "Postqueues", "block-editor", Plugin::DOMAIN ),
					"panel_empty"   => esc_html_x( "No postqueues exist yet.", "block-editor", Plugin::DOMAIN ),
					"panel_search"  => esc_html_x( "Search postqueues", "block-editor", Plugin::DOMAIN ),
					"panel_no_match" => esc_html_x( "No postqueue matches.", "block-editor", Plugin::DOMAIN ),
					"variation"     => esc_html_x( "Postqueue", "block-editor", Plugin::DOMAIN ),
					"variation_desc" => esc_html_x( "A manually ordered queue of posts.", "block-editor", Plugin::DOMAIN ),
					"select_queue"  => esc_html_x( "Postqueue", "block-editor", Plugin::DOMAIN ),
					"select_none"   => esc_html_x( "— none —", "block-editor", Plugin::DOMAIN ),
					"select_help"   => esc_html_x( "The loop shows the posts of this queue, in the queue's order.", "block-editor", Plugin::DOMAIN ),
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
		$this->registerScript(
			Plugin::HANDLE_EDITOR_JS,
			'dist/editor.js',
			[ 'jquery', 'jquery-ui-autocomplete', 'jquery-ui-sortable' ]
		);
		wp_localize_script(
			Plugin::HANDLE_EDITOR_JS,
			'PostQueue',
			[
				"rest_namespace" => REST::NAMESPACE,
				"DOMAIN"         => Plugin::DOMAIN,
				"i18n"           => [
					"create"                  => esc_html_x( "Create", "editor.jsx", Plugin::DOMAIN ),
					"confirm_delete"          => esc_html_x( "Are you sure you want to delete this queue?", "editor.jsx", Plugin::DOMAIN ),
					"confirm_delete_yes"      => esc_html_x( "Yes, delete it!", "editor.jsx", Plugin::DOMAIN ),
					"confirm_delete_no"       => esc_html_x( "No, do not delete.", "editor.jsx", Plugin::DOMAIN ),
					"search_or_create"        => esc_html_x( "Search or create queue", "editor.jsx", Plugin::DOMAIN ),
					"back"                    => esc_html_x( "Back", "editor.jsx", Plugin::DOMAIN ),
					"save"                    => esc_html_x( "Save", "editor.jsx", Plugin::DOMAIN ),
					"reset"                    => esc_html_x( "Reset", "editor.jsx", Plugin::DOMAIN ),
					"search_post_placeholder" => esc_html_x( "Search for posts", "editor.jsx", Plugin::DOMAIN ),
					'edit'                    => esc_html_x( 'Edit', "editor.jsx", Plugin::DOMAIN ),
					'delete'                  => esc_html_x( 'Delete', "editor.jsx", Plugin::DOMAIN ),
					'remove'                  => esc_html_x( 'Remove', "editor.jsx", Plugin::DOMAIN ),
					'add_post'                => esc_html_x( 'Add post', "editor.jsx", Plugin::DOMAIN ),
					'cancel'                  => esc_html_x( 'Cancel', "editor.jsx", Plugin::DOMAIN ),
					'post_title_or_id'        => esc_html_x( 'Post title or ID', "editor.jsx", Plugin::DOMAIN ),
				],
			]
		);
	}
}