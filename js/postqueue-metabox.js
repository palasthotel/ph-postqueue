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
  		var $this = $(this);
  		let $parent = $this.closest('.postqueue-metabox-postqueuelist-wrapper');
  		$parent.addClass('is-loading');
      let postid = $this.data('postid');
      let queueid = $this.data('queueid');
      let queuename = $this.data('queuename');
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
    			postqueue_metabox_remove_listitem( queueid, queuename, postid );
    			postqueue_metabox_add_selectoption( queueid, queuename, postid );
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
        let $selectedoption = $parent.find('[value="' + postqueue_select_value + '"]');
        let queuename = $selectedoption.data('queuename');
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
      			postqueue_metabox_remove_selectoption( queueid, queuename, postid );
      			postqueue_metabox_add_listitem( queueid, queuename, postid );
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
  	let $wrapper = $('.postqueue-metabox-wrapper').find('.postqueue-metabox-postqueueselect-wrapper');
  	$wrapper.find('select').append('<option value="'+queueid+'" data-queuename="'+queuename+'">'+queuename+'</option>');
	}
	/*
   * helper function, removes selectoption to metabox DOM
   */
	function postqueue_metabox_remove_selectoption( queueid, queuename, postid ) {
  	let $wrapper = $('.postqueue-metabox-wrapper').find('.postqueue-metabox-postqueueselect-wrapper');
  	$wrapper.find("[value='" + queueid + "']").remove();
  }
  /*
   * helper function, adds listitem to metabox DOM
   */
  function postqueue_metabox_add_listitem( queueid, queuename, postid ) {
    let $wrapper = $('.postqueue-metabox-wrapper').find('.postqueue-metabox-postqueuelist-wrapper');
    $wrapper.find('ul').append('<li>'+queuename+'<span class="dashicons dashicons-no postqueue-remove" data-queueid="'+queueid+'" data-postid="'+postid+'" title="'+objectL10n.removepostfromthispostqueue+'" data-queuename="'+queuename+'"></span></li>');
  }
  /*
   * helper function, removes listitem from metabox DOM
   */
  function postqueue_metabox_remove_listitem( queueid, queuename, postid ) {
    let $wrapper = $('.postqueue-metabox-wrapper').find('.postqueue-metabox-postqueuelist-wrapper');
    $wrapper.find("[data-queueid='" + queueid + "']").closest('li').remove();
  }
})( jQuery );
