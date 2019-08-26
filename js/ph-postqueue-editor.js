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
		const $queues_widget = $(".ph-postqueues-widget");
		const $queue_name = $queues_widget.find(".ph-postqueue-name");
		const $new_queue = $queues_widget.find(".ph-new-queue");
		const $new_queue_name = $new_queue.find(".queue-name");
		const $queue_list = $queues_widget.find(".queues-list");
		const $postqueue_name_display = $(".ph-postqueues-name-display");

		/**
		 * handle new queue button
		 */
		$queue_name.on("change", function(e){
			$new_queue.removeClass("ph-error");
			if(this.value === ""){
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
			const code = e.keyCode || e.which;
			if(code === 13)
			{
				$new_queue.trigger("click");
				return;
			}
			/**
			 * else filter queues
			 */
			const query = this.value.toLowerCase();
			$queue_list.children().each(function(index, element){
				const $element = $(element);
				const lower_name = $element.attr("data-name").toLowerCase();
				if( query === "" || lower_name.indexOf(query) >= 0 )
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
				url: "/wp-admin/admin-ajax.php",
				dataType: "json",
				method: 'POST',
				data: {
					action:'ph_postqueue_create_queue',
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
			const $name = $("<div>"+element.name+"</div>")
				.addClass("queue-name");
			let feed_link = "";
			if(window.ph_postqueue_feed_url  !== "")
			{
				feed_link+= " | <a href='/";
				feed_link+= window.ph_postqueue_feed_url+"/"+element.slug;
				feed_link+= "' target='_new'>RSS-Feed</a>";
			}
			const $controls = $("<div>"
				+'[postqueue slug="'+element.slug+'" viewmode=""]'
				+" | "
				+"<a href='#' class='queue-edit'>"+objectL10n.edit+"</a>"
				+" | "
				+"<a href='#' class='queue-delete submitdelete'>"+objectL10n.delete+"</a>"
				+ feed_link
				+"</div>")
				.addClass("queue-controls");
			const $li = $("<li></li>")
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
			const $this = $(this);
			const $queue = $this.parents(".queue");
			$postqueue_name_display.text($queue.attr("data-name"));
			$queues_widget.hide();
			$the_queue_wrapper.show();
			const queue_id = $queue.attr("data-id");
			$the_queue.empty();
			$.ajax({
				url: "/wp-admin/admin-ajax.php",
				dataType: "json",
				method: 'POST',
				data: {
					action:'ph_postqueue_load_queue',
					queue_id: queue_id,
				},
				success: function( data ) {
					console.log(data);
					$the_queue.attr("data-queue-id", queue_id);

					$the_queue.removeClass("prevent-add-post");
					if(data.result.length > 0){
						$.each(data.result, function(index, item){
							add_post_item(item.post_id, item.post_title);
						});
					} else {
						$the_queue.append( render_new_post_widget(true) );
					}

				},
				error: function(jqXHR, textStatus, errorThrown){
					console.error([jqXHR, textStatus, errorThrown]);
				},
			});
		});
		/**
		 * edit queue listener
		 */
		$queue_list.on("click", ".queue-delete", function(e){
			e.preventDefault();
			const $this = $(this);
			const $queue = $this.parents(".queue");
			const queue_id = $queue.attr("data-id");
			$.ajax({
				url: "/wp-admin/admin-ajax.php",
				dataType: "json",
				method: 'POST',
				data: {
					action: "ph_postqueue_delete_queue",
					queue_id: queue_id,
				},
				success: function( data ) {
					console.log(data);
					$queue.remove();
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
		const $the_queue_wrapper = $(".ph-the-queue-wrapper");
		const $the_queue = $the_queue_wrapper.find(".the-queue");
		let $new_post_item = null;
		let $new_posts_list = null;

		let post_autocomplete_interval = null;

		/**
		 * make it sortable
		 */
		$the_queue.sortable({
			handle: ".drag-handle",
			appendTo: window.document.body,
			start: function(event, ui){
				$the_queue.addClass("prevent-add-post");
			},
			stop: function(event, ui){
				$the_queue.removeClass("prevent-add-post");
			},
		});
		/**
		 * on cancel queue
		 */
		$the_queue_wrapper.on("click", ".save-queue", function(){
			savePostqueue(function(data){
				$the_queue_wrapper.find(".cancel-queue").trigger("click");
			});
		});
		/**
		 * on save queue
		 */
		$the_queue_wrapper.on("click", ".cancel-queue", function(){
			$queues_widget.show();
			$the_queue_wrapper.hide();
			$postqueue_name_display.text("");
		});
		/**
		 * add post to post list
		 */
		function add_post_item(post_id, title){
			$the_queue.append( render_post_item(post_id, title) );
			$the_queue.sortable( "refresh" );
		}
		/**
		 * build new queue list item
		 */
		function render_post_item(post_id, title){
			const $item = $(
				'<li class="queue-item queue-item-set">'
				+'<div class="drag-handle"></div>'
				+'<div class="add-post add-post-top">'+objectL10n.add_post+'</div>'
				+'<span>'+title+'</span>'
				+'<div class="add-post add-post-bottom">'+objectL10n.add_post+'</div>'
				+'<div class="delete-post">'+objectL10n.delete+'</div>'
				+'</li>');
			$item.attr("data-post-id", post_id);
			return $item;
		}
		/**
		 * add new post
		 */
		$the_queue_wrapper.on("click", ".add-post", function(e){
			const $this = $(this);
			$the_queue.addClass("prevent-add-post");
			if($this.hasClass("add-post-top"))
			{
				$this.parents(".queue-item").before( render_new_post_widget() );
			} else {
				$this.parents(".queue-item").after( render_new_post_widget() );
			}
		});
		/**
		 * delete post from queue
		 */
		$the_queue_wrapper.on("click", ".delete-post", function(e){
			$(this).parents(".queue-item").remove();
		});
		/**
		 * build add post list item
		 */
		function render_new_post_widget(not_removeable){
			$new_post_item = $(
				'<li class="queue-item queue-item-new new-post-widget">'
				+'<div class="new-post-controls">'
				+'<input class="search-query" type="text" placeholder="'+objectL10n.post_title_or_id+'" />'
					// +'<button class="add-new-post">Hinzufügen</button>'
				+'<button class="cancel-new-post button-secondary delete">'+objectL10n.cancel+'</button>'
				+'</div>'
				+'<ul class="new-posts-list post-suggestions"></ul>'
				+'</li> ');
			if(not_removeable){
				$new_post_item.find(".cancel-new-post").remove();
			}
			$new_posts_list = $new_post_item.find(".new-posts-list");
			return $new_post_item;
		}
		/**
		 * listen to input of post search
		 */
		$the_queue.on("keyup", ".search-query", function(e){
			const query = this.value;
			clearTimeout(post_autocomplete_interval);
			if(query !== "" && query.length > 0)
			{
				post_autocomplete_interval = setTimeout(function(){
					$.ajax({
						url: "/wp-admin/admin-ajax.php",
						dataType: "json",
						method: 'POST',
						data: {
							action: 'ph_postqueue_search_posts',
							search: query,
						},
						success: function( data ) {
							$new_posts_list.empty();
							$.each(data.result.posts, function(index, post){
								const $post = $("<li></li>");
								$post.text(post.post_title);
								$post.attr("data-post-id", post.post_id);
								$post.addClass("post-suggestion");
								$new_posts_list.prepend($post);
							});
						},
						error: function(jqXHR, textStatus, errorThrown){
							console.error([jqXHR, textStatus, errorThrown]);
						},
					});
				}, 1000);
			} else {
				$new_posts_list.empty();
			}
		});
		/**
		 * cancel new post
		 */
		$the_queue_wrapper.on("click", ".cancel-new-post", function(e){
			$(this).parents(".queue-item").remove();
			$the_queue.removeClass("prevent-add-post");
		});
		/**
		 * save post in queue
		 */
		$the_queue_wrapper.on("click", ".post-suggestion", function(e){
			const $suggestion_item = $(this);
			const post_id = $suggestion_item.attr("data-post-id");
			const $queue_item = $suggestion_item.parents(".queue-item");
			$queue_item.attr("data-post-id", post_id);
			const title = $suggestion_item.text();
			$the_queue.removeClass("prevent-add-post");
			$queue_item.replaceWith( render_post_item( post_id, title ) );
		});
		/**
		 * saves postqueue to database
		 */
		function savePostqueue(success){
			/**
			 * get all post ids of queue
			 * @type {Array}
			 */
			const items = [];
			$the_queue.children(".queue-item").each(function(index, element){
				const $element = $(element);
				const post_id = parseInt( $element.attr("data-post-id") );
				if(post_id > 0){
					items.push(parseInt( $element.attr("data-post-id") ));
				}

			});
			/**
			 * save the post id array to database
			 */
			$.ajax({
				url: "/wp-admin/admin-ajax.php",
				dataType: "json",
				method: 'POST',
				data: {
					action: 'ph_postqueue_save_post_items',
					queue_id: $the_queue.attr("data-queue-id"),
					items: items,
				},
				success: function( data ) {
					if(typeof success === "function") success(data);
				},
				error: function(jqXHR, textStatus, errorThrown){
					console.error([jqXHR, textStatus, errorThrown]);
				},
			});
		}

	});
})( jQuery );
