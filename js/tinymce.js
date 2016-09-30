(function( $ ) {
	'use strict';
	
	if (typeof (tinymce) != "undefined"){
		
		/**
		 * Add plugin to tinymce editor
		 */
		tinymce.PluginManager.add('postqueue', function( editor, url ) {
			
			console.log("postqueue", window.postqueue);
			const queues = window.postqueue.queues;
			const viewmodes = window.postqueue.viewmodes;
			
			
			/**
			 * start edit postqueue if node is postqueue node
			 * @param node
			 */
			function editPostqueue( node ) {
				var data;
				
				if ( node.nodeName !== 'DIV' || !editor.dom.hasClass( node, 'postqueue' ) ) {
					return;
				}
				
				data = window.decodeURIComponent( editor.dom.getAttrib( node, 'data-postqueue' ) );
				
				// Make sure we've selected a postqueue node.
				if ( editor.dom.hasClass( node, 'postqueue' ) ) {
					openEditor(node);
				}
			}
			
			/**
			 * open editor for postqueue
			 * @param node
			 */
			function openEditor(node){
				
				console.log(window.postqueue);
				
				let slug = '';
				let viewmode = '';
				
				if(typeof node !== typeof undefined){
					slug = node.getAttribute("data-slug");
					if(typeof slug == typeof undefined){
						slug = '';
					}
					viewmode = node.getAttribute("data-viewmode");
					if(typeof viewmode == typeof undefined){
						viewmode = '';
					}
				}
				
				editor.windowManager.open({
					title: "Postqueue",
					body: [
						{
							type: 'listbox',
							name: 'slug',
							label: 'Postqueue',
							values: queues,
							value: slug,
						},
						{
							type: 'listbox',
							name: 'viewmode',
							label: 'Viewmode',
							values: viewmodes,
							value: viewmode,
						}
					],
					onSubmit: function(e){
						let shortcode = "[postqueue slug=\""+e.data.slug+"\"";
						if(e.data.viewmode != '' && e.data.viewmode != null){
							shortcode+= " viewmode=\""+e.data.viewmode+"\"";
						}
						shortcode+= "]";
						editor.insertContent(shortcode);
					}
				});
			}
			
			
			/**
			 * check mouseup in editor
			 */
			editor.on( 'mouseup', function( event ) {
				var dom = editor.dom,
					node = event.target;
				
				function unselect() {
					dom.removeClass( dom.select( 'div.postqueue' ), 'postqueue-selected' );
				}
				
				if ( node.nodeName === 'DIV' && dom.getAttrib( node, 'data-postqueue' ) ) {
					// Don't trigger on right-click
					if ( event.button !== 2 ) {
						if ( dom.hasClass( node, 'postqueue-selected' ) ) {
							editPostqueue( node );
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
				if ( node.nodeName === 'DIV' && dom.getAttrib( node, 'data-postqueue' ) ) {
					if ( dom.hasClass( node, 'postqueue' ) ) {
						event.name = 'postqueue';
					}
				}
			});
			
			/**
			 * init the postqueue tinymce items
			 */
			editor.on('preInit', function() {
				function hasPostqueueClass(node) {
					var className = node.attr('class');
					return className && /\bpostqueue\b/.test(className);
				}
				
				function toggleContentEditableState(state) {
					return function(nodes) {
						var i = nodes.length, node;
						
						function toggleContentEditable(node) {
							node.attr('contenteditable', state ? 'true' : null);
						}
						
						while (i--) {
							node = nodes[i];
							
							if (hasPostqueueClass(node)) {
								node.attr('contenteditable', state ? 'false' : null);
								// tinymce.each(node.getAll('figcaption'), toggleContentEditable);
							}
						}
					};
				}
				
				editor.parser.addNodeFilter('div', toggleContentEditableState(true));
				editor.serializer.addNodeFilter('div', toggleContentEditableState(false));
			});
			
			/**
			 * replace shortcode by html representation for tinymce
			 */
			editor.on( 'BeforeSetContent', function( event ) {
				event.content = event.content.replace( /\[postqueue([^\]]*)\]/g, function( match, attrs ) {
					
					let parts = attrs.split(" ");
					let found = [];
					let data = [];
					for(let i = 0; i < parts.length; i++){
						
						if(!parts[i].match(/([\w\-_]+)="([^"]+)"/i)) continue;
						
						found.push(parts[i].replace(/([\w\-_]+)="([^"]+)"/i, "<b>$1:</b> $2"));
						
						data.push(parts[i].replace(/([\w\-_]+)="([^"]+)"/i, "data-$1=\"$2\""));
					}
					
					return '<div data-postqueue="'+window.encodeURIComponent( match )+'" ' +
						data.join(" ")+' class="postqueue">'+
							'<span class="postqueue__title">Postqueue</span><br/>'+
							found.join(" <br/> ")+
						'<!--postqueue--></div>';
				});
			});
			
			/**
			 * restore shortcode from html
			 */
			editor.on( 'PostProcess', function( event ) {
				if ( event.get ) {
					event.content = event.content.replace(/<div[^>]+?data-postqueue="([^"]*?)".+?<!--postqueue--><\/div>/gi, function(match, data){
						return "<p>"+window.decodeURIComponent(data)+"</p>";
					});
				}
				
			});
			
			/**
			 * Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('...');
			 */
			editor.addCommand( 'Postqueue', function() {
				console.log("add command edit");
				editPostqueue( editor.selection.getNode() );
			});
			
			/**
			 * add button to editor
			 */
			editor.addButton('postqueue', {
				text: 'Postqueue',
				title: "Postqueue",
				// image: url+'/../images/icon.png',
				// icon: 'icon dashicons-before dashicons-location-alt',
				onclick: function() {
					/**
					 * opens jquery dialog
					 */
					openEditor(editor.selection.getNode());
				}
			});
			
		});
		
	}
	
})( jQuery );