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
				"i18n" => [
					'edit'             => esc_html__( 'Edit', Plugin::DOMAIN ),
					'delete'           => esc_html__( 'Delete', Plugin::DOMAIN ),
					'add_post'         => esc_html__( 'Add post', Plugin::DOMAIN ),
					'cancel'           => esc_html__( 'Cancel', Plugin::DOMAIN ),
					'post_title_or_id' => esc_html__( 'Post title or ID', Plugin::DOMAIN ),
				],
			]
		);
	}
}