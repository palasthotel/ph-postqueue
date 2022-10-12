<?php

namespace Postqueue\Headless;

use Palasthotel\WordPress\Headless\Interfaces\IBlockPreparation;
use Palasthotel\WordPress\Headless\Model\BlockName;
use Postqueue\Blocks\Postqueue;
use Postqueue\Plugin;

class PostqueueBlockPreparation implements IBlockPreparation {

	public function blockName(): BlockName {
		return BlockName::build(Plugin::DOMAIN, Postqueue::SLUG);
	}

	public function prepare( array $block ): array {
		$attrs = $block["attrs"];#
		$blockInstance = new Postqueue();
		if(is_array($attrs) && !empty($attrs["content"])){
			$prepared = $blockInstance->prepare((object) $attrs["content"]);
			if(is_array($prepared->args)){

				$args = $prepared->args;
				$args["fields"] = "ids";
				$posts = get_posts($args);

				$block["attrs"]["content"]["posts"] = array_map(function($post){
					return $this->buildPostTeaser($post);
				}, $posts);

			}
		}

		return $block;
	}

	private function buildPostTeaser( $id_or_post ) {
		return apply_filters(\Palasthotel\WordPress\Headless\Plugin::FILTER_PREPARE_POST, [], $id_or_post);
	}
}
