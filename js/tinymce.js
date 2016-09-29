(function( $ ) {
	'use strict';
	
	if (typeof (tinymce) != "undefined"){
		var $modal = null;
		
		/**
		 * Add plugin to tinymce editor
		 */
		tinymce.PluginManager.add('postqueue', function( editor, url ) {
			
			function replaceShortcodes( content ) {
				return content.replace( /\[postqueue([^\]]*)\]/g, function( match ) {
					return html( 'postqueue', match );
				});
			}
			
			function html( cls, data ) {
				data = window.encodeURIComponent( data );
				// TODO: display data (shortcode) to user
				return '<div class="' + cls + '" data-raw="'+data+'" >' + data + '</div>';
			}
			
			function restoreShortcodes( content ) {
				function getAttr( str, name ) {
					name = new RegExp( name + '=\"([^\"]+)\"' ).exec( str );
					return name ? window.decodeURIComponent( name[1] ) : '';
				}
				
				return content.replace( /(?:<p(?: [^>]+)?>)*(<img [^>]+>)(?:<\/p>)*/g, function( match, image ) {
					var data = getAttr( image, 'data-postqueue' );
					
					if ( data ) {
						return '<p>' + data + '</p>';
					}
					
					return match;
				});
			}
			
			function editGeolocations( node ) {
				var data;
				
				if ( node.nodeName !== 'DIV' ) {
					return;
				}
				
				data = window.decodeURIComponent( editor.dom.getAttrib( node, 'data-postqueue' ) );
				
				// Make sure we've selected a gallery node.
				if ( editor.dom.hasClass( node, 'postqueue' ) ) {
					console.log("edit postqueue", node, data );
				}
			}
			
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('...');
			editor.addCommand( 'Postqueue', function() {
				editGeolocations( editor.selection.getNode() );
			});
			
			editor.on( 'mouseup', function( event ) {
				var dom = editor.dom,
					node = event.target;
				
				function unselect() {
					dom.removeClass( dom.select( 'img.postqueue' ), 'postqueue-map-selected' );
				}
				
				if ( node.nodeName === 'IMG' && dom.getAttrib( node, 'data-postqueue' ) ) {
					// Don't trigger on right-click
					if ( event.button !== 2 ) {
						if ( dom.hasClass( node, 'postqueue-selected' ) ) {
							editGeolocations( node );
						} else {
							unselect();
							dom.addClass( node, 'postqueue-selected' );
						}
					}
				} else {
					unselect();
				}
			});
			
			// Display gallery, audio or video instead of img in the element path
			editor.on( 'ResolveName', function( event ) {
				var dom = editor.dom,
					node = event.target;
				
				if ( node.nodeName === 'IMG' && dom.getAttrib( node, 'data-postqueue' ) ) {
					if ( dom.hasClass( node, 'postqueue' ) ) {
						event.name = 'postqueue';
					}
				}
			});
			
			
			/**
			 * add button to editor
			 */
			console.log(url);
			editor.addButton('postqueue', {
				text: 'Postqueue',
				title: "Postqueue",
				// image: url+'/../images/icon.png',
				// icon: 'icon dashicons-before dashicons-location-alt',
				onclick: function() {
					/**
					 * opens jquery dialog
					 */
					// if($modal != null && $modal.length > 0){
					// 	$modal.dialog("open");
					// }
					editor.insertContent("[postqueue slug=\"slug\"]");
				}
			});
			
			editor.on( 'BeforeSetContent', function( event ) {
				event.content = replaceShortcodes( event.content );
			});
			
			editor.on( 'PostProcess', function( event ) {
				if ( event.get ) {
					event.content = restoreShortcodes( event.content );
				}
			});
			
			
		});
		
	}
	
})( jQuery );