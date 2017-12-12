/**
 * Javascript for postqueue metabox functionality
 */
(function( $ ) {
	'use strict';
	/**
	 * Start after dom is ready
	 */
	$(function() {

		// @todo remove debugging
		console.log('postqueue-metabox.js loaded!');
		
		$('.postqueue-remove').on( 'click', function(e) {
  		console.log('delete clicked!');
      let postid = $(this).attr('data-postid');
      let queueid = $(this).attr('data-queueid');
  		let data = {
  			'action': 'postqueue_remove_post',
  			'postid': postid,
  			'queueid': queueid
  		};
      
  		jQuery.post( ajaxurl, data, function(response) {
  			alert( 'Ajax call done: ' + response );
  		});
    });
    
    $('.postqueue-add').on( 'click', function(e) {
      console.log('add button clicked!');
      let postqueue_select_value = $(this).parent().find('.postqueue-select').val();
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
    			alert( 'Ajax call done: ' + response );
    		});
      } else {
        alert('Please choose a postqueue.');
      }
    });
	});
})( jQuery );
