/**
 * Javascript for postqueue metabox functionality
 */
(function( $, objectL10n ) {
	'use strict';

	const wrapperSelector = '.postqueue-metabox-wrapper';

	/**
	 * Start after dom is ready
	 */
	$(function() {

		const $messages = $(wrapperSelector).find('.messages');
		postqueue_check_empty_list();

		postqueue_add_remove_eventlisteners( $messages );

	    $('.postqueue-add').on( 'click', function(e) {
	      const $parent = $(this).closest('.postqueue-metabox-postqueueselect-wrapper');
	        $parent.addClass('is-loading');
	      const postqueue_select_value = $parent.find('.postqueue-select').val();

	      if( postqueue_select_value !== 'none' ) {
	        const postid = $(this).attr('data-postid');
	        const queueid = postqueue_select_value;
	        const $selectedoption = $parent.find('[value="' + postqueue_select_value + '"]');
	        const queuename = $selectedoption.data('queuename');
	            const data = {
	                'action': 'postqueue_add_post',
	                'postid': postid,
	                'queueid': queueid
	            };

	            $.post( ajaxurl, data, function(response) {
	          if( response <= 0 ) {
	            $messages.text(objectL10n.erroroccured);
	                $messages.addClass('error');
	                } else {
	                $messages.text(objectL10n.postadded);
	                $messages.removeClass('error');
	                postqueue_metabox_remove_selectoption( queueid, queuename, postid );
	                postqueue_metabox_add_listitem( queueid, queuename, postid );
	                postqueue_check_empty_list();
	                }
	                $parent.removeClass('is-loading');
	            });
	      } else {
	        $messages.text(objectL10n.pleasechoose);
	        $parent.removeClass('is-loading');
	        $messages.addClass('error');
	      }
	    });
	});

	/*
   * helper function, adds selectoption to metabox DOM
   */
	function postqueue_metabox_add_selectoption( queueid, queuename, postid ) {
	    const $wrapper = $(wrapperSelector).find('.postqueue-metabox-postqueueselect-wrapper');
	    $wrapper.find('select').append('<option value="'+queueid+'" data-queuename="'+queuename+'">'+queuename+'</option>');
	}
	/*
   * helper function, removes selectoption to metabox DOM
   */
	function postqueue_metabox_remove_selectoption( queueid, queuename, postid ) {
	    const $wrapper = $(wrapperSelector).find('.postqueue-metabox-postqueueselect-wrapper');
	    $wrapper.find("[value='" + queueid + "']").remove();
	}
  /*
   * helper function, adds listitem to metabox DOM
   */
  function postqueue_metabox_add_listitem( queueid, queuename, postid ) {

    const $wrapper = $(wrapperSelector).find('.postqueue-metabox-postqueuelist-wrapper');
    $wrapper.find('ul').append('<li>'+queuename+'<span class="dashicons dashicons-no postqueue-remove" data-queueid="'+queueid+'" data-postid="'+postid+'" title="'+objectL10n.removepostfromthispostqueue+'" data-queuename="'+queuename+'"></span></li>');
    postqueue_add_remove_eventlisteners( $(wrapperSelector).find('.messages') );
  }
  /*
   * helper function, removes listitem from metabox DOM
   */
  function postqueue_metabox_remove_listitem( queueid, queuename, postid ) {
    const $wrapper = $(wrapperSelector).find('.postqueue-metabox-postqueuelist-wrapper');
    $wrapper.find("[data-queueid='" + queueid + "']").closest('li').remove();
  }

  /*
   * helper function, checks if list is empty and prints a text if so
   */
  function postqueue_check_empty_list() {
    const $wrapper = $(wrapperSelector).find('.postqueue-metabox-postqueuelist-wrapper ul');
    if( !$wrapper.html().trim() ) {
      $wrapper.parent().append('<span class="postqueue-metabox-postqueuelist-emptyinfo">'+objectL10n.notstoredyet+'</span>');
    } else {
      $wrapper.parent().find('.postqueue-metabox-postqueuelist-emptyinfo').remove();
    }
  }

  function postqueue_add_remove_eventlisteners( $messages ) {
    $('.postqueue-remove').off( 'click' ); //remove all click listeners and add them again
    $('.postqueue-remove').on( 'click', function(e) {
  		const $this = $(this);
  		const $parent = $this.closest('.postqueue-metabox-postqueuelist-wrapper');
  		$parent.addClass('is-loading');
        const postid = $this.data('postid');
        const queueid = $this.data('queueid');
        const queuename = $this.data('queuename');
  		const data = {
  			'action': 'postqueue_remove_post',
  			'postid': postid,
  			'queueid': queueid
  		};

  		jQuery.post( ajaxurl, data, function(response) {
  			if( response <= 0 ) {
    			$messages.text(objectL10n.erroroccured);
    			$messages.addClass('error');
  			} else {
    			$messages.text(objectL10n.postremoved);
    			$messages.removeClass('error');
    			postqueue_metabox_remove_listitem( queueid, queuename, postid );
    			postqueue_metabox_add_selectoption( queueid, queuename, postid );
    			postqueue_check_empty_list();
  			}
  			$parent.removeClass('is-loading');

  		});
    });
  }

})( jQuery, PostqueueMetaBoxL10n );
