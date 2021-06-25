<?php


namespace Postqueue;


class Assets extends Component\Assets {

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