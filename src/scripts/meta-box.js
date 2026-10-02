/**
 * The postqueue meta box of the classic editor: add the post to a queue or remove it from
 * one, through the postqueue_add_post and postqueue_remove_post AJAX actions.
 *
 * Plain DOM, no jQuery. Everything that comes from a queue name is set as text or as an
 * attribute value, never parsed as HTML.
 */
import domReady from '@wordpress/dom-ready';

const l10n = window.PostqueueMetaBoxL10n || {};

/**
 * Sends one of the meta box's AJAX actions.
 *
 * @param {string} action  postqueue_add_post or postqueue_remove_post
 * @param {string} postId  the post being edited
 * @param {string} queueId the queue to add it to or remove it from
 * @return {Promise<boolean>} whether WordPress accepted the request
 */
function send( action, postId, queueId ) {
	const body = new URLSearchParams( {
		action,
		_ajax_nonce: l10n.nonce,
		postid: postId,
		queueid: queueId,
	} );
	return window
		.fetch( window.ajaxurl, {
			method: 'POST',
			credentials: 'same-origin',
			body,
		} )
		.then( ( response ) => response.ok )
		.catch( () => false );
}

domReady( () => {
	const wrapper = document.querySelector( '.postqueue-metabox-wrapper' );
	if ( ! wrapper ) {
		return;
	}

	const messages = wrapper.querySelector( '.messages' );
	const listWrapper = wrapper.querySelector(
		'.postqueue-metabox-postqueuelist-wrapper'
	);
	const list = listWrapper.querySelector( 'ul' );
	const selectWrapper = wrapper.querySelector(
		'.postqueue-metabox-postqueueselect-wrapper'
	);
	const select = selectWrapper.querySelector( '.postqueue-select' );

	const showMessage = ( text, isError ) => {
		messages.textContent = text;
		messages.classList.toggle( 'error', isError );
	};

	const checkEmptyList = () => {
		const info = listWrapper.querySelector(
			'.postqueue-metabox-postqueuelist-emptyinfo'
		);
		if ( list.querySelector( 'li' ) ) {
			if ( info ) {
				info.remove();
			}
			return;
		}
		if ( ! info ) {
			const span = document.createElement( 'span' );
			span.className = 'postqueue-metabox-postqueuelist-emptyinfo';
			span.textContent = l10n.notstoredyet;
			listWrapper.appendChild( span );
		}
	};

	const addListItem = ( queueId, queueName, postId ) => {
		const li = document.createElement( 'li' );
		li.textContent = queueName + ' ';
		const remove = document.createElement( 'span' );
		remove.className = 'dashicons dashicons-no postqueue-remove';
		remove.dataset.queueid = queueId;
		remove.dataset.postid = postId;
		remove.dataset.queuename = queueName;
		remove.title = l10n.removepostfromthispostqueue;
		li.appendChild( remove );
		list.appendChild( li );
	};

	const addSelectOption = ( queueId, queueName ) => {
		if ( ! select ) {
			return;
		}
		const option = document.createElement( 'option' );
		option.value = queueId;
		option.dataset.queuename = queueName;
		option.textContent = queueName;
		select.appendChild( option );
	};

	checkEmptyList();

	// Removing: delegated, so list items added after the page loaded work too.
	list.addEventListener( 'click', ( event ) => {
		const button = event.target.closest( '.postqueue-remove' );
		if ( ! button ) {
			return;
		}
		const { queueid, postid, queuename } = button.dataset;
		listWrapper.classList.add( 'is-loading' );
		send( 'postqueue_remove_post', postid, queueid ).then( ( ok ) => {
			if ( ok ) {
				showMessage( l10n.postremoved, false );
				button.closest( 'li' ).remove();
				addSelectOption( queueid, queuename );
				checkEmptyList();
			} else {
				showMessage( l10n.erroroccured, true );
			}
			listWrapper.classList.remove( 'is-loading' );
		} );
	} );

	const addButton = selectWrapper.querySelector( '.postqueue-add' );
	if ( ! addButton ) {
		return;
	}
	addButton.addEventListener( 'click', () => {
		const queueId = select ? select.value : 'none';
		if ( 'none' === queueId ) {
			showMessage( l10n.pleasechoose, true );
			return;
		}
		const option = select.options[ select.selectedIndex ];
		const queueName = option.dataset.queuename;
		const postId = addButton.dataset.postid;

		selectWrapper.classList.add( 'is-loading' );
		send( 'postqueue_add_post', postId, queueId ).then( ( ok ) => {
			if ( ok ) {
				showMessage( l10n.postadded, false );
				option.remove();
				addListItem( queueId, queueName, postId );
				checkEmptyList();
			} else {
				showMessage( l10n.erroroccured, true );
			}
			selectWrapper.classList.remove( 'is-loading' );
		} );
	} );
} );
