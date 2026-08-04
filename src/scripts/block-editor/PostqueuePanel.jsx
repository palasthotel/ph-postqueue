import { PluginDocumentSettingPanel } from '@wordpress/editor';
import {
	Button,
	CheckboxControl,
	Flex,
	FlexItem,
	Notice,
	SearchControl,
	Spinner,
	TextControl,
} from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect, useMemo, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

const { rest_namespace: ns, rest_field: FIELD, i18n } = window.PostQueueBlockEditor;

// wp_localize_script turns scalars into strings, so this is "8", not 8.
const SEARCH_THRESHOLD = Number( window.PostQueueBlockEditor.search_threshold ) || 8;

/**
 * Which postqueues this content belongs to.
 *
 * Built to match the core category panel, in behaviour and in markup: the assignment is
 * held in the editor's state and written when the post is saved, while creating a queue
 * happens immediately - exactly as adding a category does.
 *
 * The markup mirrors HierarchicalTermSelector, which WordPress does not export: a
 * Flex column with gap 4 for the spacing, and core's own class names on the list, the
 * choices, the add button and the name field. That is what gives us core's styling for
 * free, including the scrolling list at max-height 14em. If core ever renames those
 * classes the panel loses its polish but keeps working.
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
	const [ showForm, setShowForm ] = useState( false );
	const [ newName, setNewName ] = useState( '' );
	const [ creating, setCreating ] = useState( false );
	const [ error, setError ] = useState( null );

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

	const select = ( ids ) => editPost( { [ FIELD ]: ids.map( Number ) } );

	const toggle = ( id ) =>
		select(
			isSelected( id )
				? selected.filter( ( it ) => Number( it ) !== Number( id ) )
				: [ ...selected, id ]
		);

	const addQueue = ( event ) => {
		event.preventDefault();
		const name = newName.trim();
		if ( '' === name || creating ) {
			return;
		}

		setCreating( true );
		setError( null );

		apiFetch( {
			path: `/${ ns }/queues`,
			method: 'POST',
			data: { name },
		} )
			.then( ( queue ) => {
				// Creating is immediate, the way adding a category is. Only the
				// assignment waits for the post to be saved - and the new queue is
				// ticked straight away, which is also what core does.
				setQueues( ( current ) => [ ...( current || [] ), queue ] );
				select( [ ...selected, queue.id ] );
				setNewName( '' );
				setShowForm( false );
			} )
			.catch( ( err ) => setError( err?.message || i18n.create_error ) )
			.finally( () => setCreating( false ) );
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
			<Flex direction="column" gap="4">
				{ error && (
					<Notice status="error" onRemove={ () => setError( null ) }>
						{ error }
					</Notice>
				) }

				{ null === queues && (
					<Flex justify="center" style={ { height: '40px' } }>
						<Spinner />
					</Flex>
				) }

				{ null !== queues && queues.length > SEARCH_THRESHOLD && (
					<SearchControl
						__next40pxDefaultSize
						label={ i18n.panel_search }
						placeholder={ i18n.panel_search }
						value={ search }
						onChange={ setSearch }
					/>
				) }

				{ null !== queues && 0 === queues.length && <p>{ i18n.panel_empty }</p> }

				{ null !== queues && queues.length > 0 && 0 === visible.length && (
					<p>{ i18n.panel_no_match }</p>
				) }

				{ visible.length > 0 && (
					<div
						className="editor-post-taxonomies__hierarchical-terms-list"
						tabIndex="0"
						role="group"
						aria-label={ i18n.panel_title }
					>
						{ visible.map( ( queue ) => (
							<div
								key={ queue.id }
								className="editor-post-taxonomies__hierarchical-terms-choice"
							>
								<CheckboxControl
									label={ queue.name }
									checked={ isSelected( queue.id ) }
									onChange={ () => toggle( queue.id ) }
								/>
							</div>
						) ) }
					</div>
				) }

				<FlexItem>
					<Button
						__next40pxDefaultSize
						className="editor-post-taxonomies__hierarchical-terms-add"
						variant="link"
						aria-expanded={ showForm }
						onClick={ () => setShowForm( ( value ) => ! value ) }
					>
						{ i18n.create_toggle }
					</Button>
				</FlexItem>

				{ showForm && (
					<form onSubmit={ addQueue }>
						<Flex direction="column" gap="4">
							<TextControl
								__next40pxDefaultSize
								className="editor-post-taxonomies__hierarchical-terms-input"
								label={ i18n.create_label }
								value={ newName }
								onChange={ setNewName }
								required
							/>
							<FlexItem>
								<Button
									__next40pxDefaultSize
									variant="secondary"
									type="submit"
									isBusy={ creating }
									disabled={ creating || '' === newName.trim() }
								>
									{ i18n.create_submit }
								</Button>
							</FlexItem>
						</Flex>
					</form>
				) }
			</Flex>
		</PluginDocumentSettingPanel>
	);
}
