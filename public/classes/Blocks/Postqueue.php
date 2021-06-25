<?php


namespace Postqueue\Blocks;


use Palasthotel\WordPress\BlockX\Blocks\_BlockType;
use Palasthotel\WordPress\BlockX\Model\BlockId;
use Palasthotel\WordPress\BlockX\Model\ContentStructure;
use Palasthotel\WordPress\BlockX\Widgets\Number;
use Palasthotel\WordPress\BlockX\Widgets\Select;
use Palasthotel\WordPress\BlockX\Widgets\Text;
use Palasthotel\WordPress\Model\Option;
use Postqueue\Plugin;

class Postqueue extends _BlockType {

	public function id(): BlockId {
		return BlockId::build(Plugin::DOMAIN, "single");
	}

	public function category(): string {
		return "embed";
	}

	public function title(): string {
		return __("PostQueue", Plugin::DOMAIN);
	}

	public function contentStructure(): ContentStructure {
		return new ContentStructure([
			Text::build("slug", "Queue slug"), // TODO: custom autocomplete widget
			Select::build(
				"viewmode",
				"View mode",
				array_map(function($item){
					return Option::build($item["key"], $item["label"]);
				}, Plugin::getViewmodes())
			),
			Number::build("offset", "Offset"),
			Number::build("limit", "Limit")
			// TODO: description widget
		]);
	}
}