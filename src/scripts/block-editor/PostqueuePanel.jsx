import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { CheckboxControl, SearchControl, Spinner } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect, useMemo, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

const { rest_namespace: ns, rest_field: FIELD, i18n } = window.PostQueueBlockEditor;

// wp_localize_script turns scalars into strings, so this is "3", not 3.
const SEARCH_THRESHOLD = Number( window.PostQueueBlockEditor.search_threshold ) || 3;

/**
 * Which postqueues this content belongs to.
 *
 * Modelled on the core category panel: a checkbox per queue, a search field once the
 * list is long enough to need one, and - the part that matters - the change is held in
 * the editor's state and written when the post is saved. Nothing is sent on click.
 *
 * The value lives in a REST field on the post, so editPost() marks the post as having
 * unsaved changes exactly the way editing a title or picking a category does.
 */
export default function PostqueuePanel() {
	const { saved, edited } = useSelect( ( select ) => {
		const editor = select( 'core/editor' );

		return {
			saved: editor.getCurrentPost()?.[ FIELD ],
			edited: editor.getPostEdits()?.[ FIELD ],
		};
	}, [] );
	const { editPost } = useDispatch( 'core/editor' );

	const [ queues, setQueues ] = useState( null );
	const [ search, setSearch ] = useState( '' );

	useEffect( () => {
		let cancelled = false;
		apiFetch( { path: `/${ ns }/queues` } )
			.then( ( result ) => ! cancelled && setQueues( result ) )
			.catch( () => ! cancelled && setQueues( [] ) );
		return () => {
			cancelled = true;
		};
	}, [] );

	// An edit of [] is a real value and has to win over the saved one, so the check is
	// on undefined rather than on falsiness.
	const selected = ( undefined !== edited ? edited : saved ) || [];
	const isSelected = ( id ) => selected.map( Number ).includes( Number( id ) );

	const toggle = ( id ) => {
		const next = isSelected( id )
			? selected.filter( ( it ) => Number( it ) !== Number( id ) )
			: [ ...selected.map( Number ), Number( id ) ];

		editPost( { [ FIELD ]: next } );
	};

	const visible = useMemo( () => {
		if ( ! queues ) {
			return [];
		}
		const term = search.trim().toLowerCase();
		if ( '' === term ) {
			return queues;
		}
		return queues.filter( ( queue ) => queue.name.toLowerCase().includes( term ) );
	}, [ queues, search ] );

	return (
		<PluginDocumentSettingPanel name="postqueue" title={ i18n.panel_title }>
			{ null === queues && <Spinner /> }

			{ null !== queues && 0 === queues.length && <p>{ i18n.panel_empty }</p> }

			{ null !== queues && queues.length > SEARCH_THRESHOLD && (
				<SearchControl
					label={ i18n.panel_search }
					placeholder={ i18n.panel_search }
					value={ search }
					onChange={ setSearch }
					__nextHasNoMarginBottom
				/>
			) }

			{ null !== queues && queues.length > 0 && 0 === visible.length && (
				<p>{ i18n.panel_no_match }</p>
			) }

			{ visible.map( ( queue ) => (
				<CheckboxControl
					key={ queue.id }
					label={ queue.name }
					checked={ isSelected( queue.id ) }
					onChange={ () => toggle( queue.id ) }
					__nextHasNoMarginBottom
				/>
			) ) }
		</PluginDocumentSettingPanel>
	);
}
