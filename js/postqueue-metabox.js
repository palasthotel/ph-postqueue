/**
 * Javascript for postqueue metabox functionality
 */
(function( $ ) {
	'use strict';
	/**
	 * Start after dom is ready
	 */
	$(function() {
		var $messages = $('.postqueue-metabox-wrapper').find('.messages');
		$('.postqueue-remove').on( 'click', function(e) {
  		let $parent = $(this).closest('.postqueue-metabox-postqueuelist-wrapper');
  		$parent.addClass('is-loading');
      let postid = $(this).attr('data-postid');
      let queueid = $(this).attr('data-queueid');
  		let data = {
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
  			}
  			$parent.removeClass('is-loading');
  			
  		});
    });
    
    $('.postqueue-add').on( 'click', function(e) {
      let $parent = $(this).closest('.postqueue-metabox-postqueueselect-wrapper');
  		$parent.addClass('is-loading');
      let postqueue_select_value = $parent.find('.postqueue-select').val();
      if( postqueue_select_value != 'none' ) {
        let postid = $(this).attr('data-postid');
        let queueid = postqueue_select_value;
        console.log('queueid:'+queueid);
    		let data = {
    			'action': 'postqueue_add_post',
    			'postid': postid,
    			'queueid': queueid
    		};
        
    		jQuery.post( ajaxurl, data, function(response) {
          if( response <= 0 ) {
            $messages.text(objectL10n.erroroccured);
      			$messages.addClass('error');
    			} else {
      			$messages.text(objectL10n.postadded);
      			$messages.removeClass('error');
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
})( jQuery );
