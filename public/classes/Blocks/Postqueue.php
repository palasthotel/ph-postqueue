<?php


namespace Postqueue\Blocks;


use Palasthotel\WordPress\BlockX\Blocks\_BlockType;
use Palasthotel\WordPress\BlockX\Model\BlockId;
use Palasthotel\WordPress\BlockX\Model\ContentStructure;
use Palasthotel\WordPress\BlockX\Model\Option;
use Palasthotel\WordPress\BlockX\Widgets\Number;
use Palasthotel\WordPress\BlockX\Widgets\Select;
use Postqueue\Plugin;
use stdClass;

class Postqueue extends _BlockType {

	const SLUG = "instance";

	public function id(): BlockId {
		return BlockId::build(Plugin::DOMAIN, self::SLUG);
	}

	public function category(): string {
		return "embed";
	}

	public function title(): string {
		return __("PostQueue", Plugin::DOMAIN);
	}

	public function registerBlockTypeArgs(): array {
		$args                = parent::registerBlockTypeArgs();
		$args["icon"]        = "list-view";
		$args["supports"]    = [
			"align"           => true,
			"customClassName" => true,
		];

		return $args;
	}

	public function contentStructure(): ContentStructure {

		return new ContentStructure([

			Select::build("slug", "Queue", array_map(function($queue){
				return Option::build($queue->slug, $queue->name);
			},Plugin::instance()->store->get_queues())),

			Select::build(
				"viewmode",
				"View mode",
				array_map(function($item){
					return Option::build($item["key"], $item["text"]);
				}, Plugin::getViewmodes())
			),

			Number::build("limit", "Limit", 1),
			Number::build("offset", "Offset", 0),

		]);
	}

	public function prepare( stdClass $content ): stdClass {
		$content->post_ids = [];
		$content->args = [];
		if(empty($content->slug)){
			return $content;
		}

		$store = Plugin::instance()->store;
		$queue = $store->get_queue_by_slug($content->slug);

		$pids = array();
		foreach ($queue as $item) {
			$pids[]=$item->post_id;
		}

		/**
		 * if no id in array return
		 * otherwise wp_query will render all posts
		 */
		$content->post_ids = $pids;
		if( count($pids) < 1 ) return $content;

		/**
		 * build query args for loop
		 */
		$content->args = array (
			'post__in'      => $pids,
			'post_status'   => 'publish',
			'orderby'       => 'post__in',
			'post_type'     => 'any',
			'posts_per_page' => count($pids),
		);

		if(!empty($content->offset)){
			$content->args["offset"] = $content->offset;
		}
		if(!empty($content->limit)){
			$content->args["posts_per_page"] = $content->limit;
		}

		return parent::prepare( $content );
	}
}
