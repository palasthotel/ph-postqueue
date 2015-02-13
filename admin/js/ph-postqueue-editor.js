/**
 * Javascript for postqueue admin tools
 */
 (function( $ ) {
 	'use strict';
	/**
	 * Start after dom is ready
	 */
	 $(function() {

	 	/**
	 	 * queue list section
	 	 */
	 	var $queues_widget = $(".ph-postqueues-widget");
	 	var $queue_name = $queues_widget.find(".ph-postqueue-name");
	 	var $new_queue = $queues_widget.find(".ph-new-queue");
	 	var $new_queue_name = $new_queue.find(".queue-name");
	 	var $queue_list = $queues_widget.find(".queues-list");
	 	var $postqueue_name_display = $(".ph-postqueues-name-display");

	 	/**
	 	 * handle new queue button
	 	 */
	 	$queue_name.on("change", function(e){
	 		$new_queue.removeClass("ph-error");
	 		if(this.value == ""){
	 			$new_queue.removeClass("ph-active");
	 		} else {
	 			$new_queue.addClass("ph-active");
	 		}
	 		$new_queue_name.html(this.value);
	 	});
	 	/**
	 	 * watch for queue search input
	 	 */
	 	$queue_name.on("keyup", function(e){
	 		
	 		/**
	 		 * if enter try to save postqueue
	 		 */
	 		var code = e.keyCode || e.which;
			if(code == 13) 
			{
				$new_queue.trigger("click");
				return;
			}
			/**
			 * else filter queues
			 */
	 		var query = this.value.toLowerCase();
	 		$queue_list.children().each(function(index, element){
	 			var $element = $(element);
	 			var lower_name = $element.attr("data-name").toLowerCase();
	 			if( query == "" || lower_name.indexOf(query) >= 0 )
	 			{
	 				$element.show();
	 			} else {
	 				$element.hide();
	 			}
	 		});
	 		/**
	 		 * update new queue element
	 		 */
	 		$queue_name.trigger("change");
	 	});
	 	/**
	 	 * on new queue item
	 	 */
	 	$new_queue.on("click", function(e){
			if( $new_queue.hasClass("ph-loading") ) return;
			$new_queue.addClass("ph-loading");
	 		$.ajax({
 	 			url: "/wp-admin/admin-ajax.php?action=ph_postqueue_create_queue",
 	 			dataType: "json",
 	 			data: {
 	 				queue_name: $queue_name.val(),
 	 			},
 	 			success: function( data ) {
 	 				if(data.result.success){
 	 					add_queue_item(data.result);
 	 					$queue_name.val("");
 	 					$queue_name.trigger("change");
 	 					$queue_name.trigger("keyup");
 	 				} else {
 	 					$new_queue.addClass("ph-error");
 	 					console.log(data );
 	 					console.error("error with new queue item");
 	 				}
 	 				$new_queue.removeClass("ph-loading");
 	 			},
 	 			error: function(jqXHR, textStatus, errorThrown){
 	 				console.error([jqXHR, textStatus, errorThrown]);
 	 			},
 	 		});
	 	});

	 	/**
	 	 * render queue list element
	 	 */
	 	function render_queue_item(element)
	 	{
	 		var $name = $("<div>"+element.name+"</div>")
	 					.addClass("queue-name");
	 		var $controls = $("<div>"
				+"["+element.slug+"]"
				+" | "
				+"<a href='#' target='_new'>RSS-Feed</a>"
				+" | "
				+"<a href='#' class='queue-edit'>Bearbeiten</a>"
				+"</div>")
	 			.addClass("queue-controls");
	 		var $li = $("<li></li>")
	 					.addClass("queue")
	 					.attr("data-id", element.id)
	 					.attr("data-slug", element.slug)
	 					.attr("data-name", element.name);
	 		$li.append($name);
	 		$li.append($controls);
	 		return $li;
	 	}

	 	/**
	 	 * adds an item to queue list
	 	 */
	 	function add_queue_item(element)
	 	{
	 		$queue_list.prepend(render_queue_item(element));
	 	}

	 	/**
	 	 * init postqueues editor
	 	 */
	 	function init(){
	 		$.each(window.ph_postqueues, function(index, element){
	 			add_queue_item(element);
	 		});
	 	}
	 	init();

	 	/**
	 	 * edit queue listener
	 	 */
	 	$queue_list.on("click", ".queue-edit", function(e){
	 		e.preventDefault();
	 		var $this = $(this);
	 		var $queue = $this.parents(".queue");
	 		$postqueue_name_display.text($queue.attr("data-name"));
	 		$queues_widget.hide();
	 		$the_queue_wrapper.show();

	 		$.ajax({
 	 			url: "/wp-admin/admin-ajax.php?action=ph_postqueue_load_queue",
 	 			dataType: "json",
 	 			data: {
 	 				queue_id: $queue.attr("data-id"),
 	 			},
 	 			success: function( data ) {
 	 				console.log(data)
 	 			},
 	 			error: function(jqXHR, textStatus, errorThrown){
 	 				console.error([jqXHR, textStatus, errorThrown]);
 	 			},
 	 		});
	 	});

	 	/**
	 	 * queue list section ENDE
	 	 */
	 	
	 	/**
	 	 * queue section
	 	 */
	 	var $the_queue_wrapper = $(".ph-the-queue-wrapper");

	 	

	 });

	

})( jQuery );
