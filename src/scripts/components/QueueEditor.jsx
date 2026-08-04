import { Button, Flex, FlexItem, Notice, Spinner } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { useQueryPosts, useQueueItems } from '../hooks/use-queues';
import QueueItems from './QueueItems.jsx';

const { i18n } = PostQueue;
const screen = window.PostQueueScreen || {};

/**
 * Search for a post and put it at the top of the queue.
 */
const AddPost = ( { postIdsInQueue, onAdd } ) => {
	const [ query, setQuery ] = useState( '' );
	const [ posts, isLoading ] = useQueryPosts( query );
	const suggestions = posts.filter( ( p ) => ! postIdsInQueue.includes( p.post_id ) );

	return (
		<div className="postqueue-add">
			<Flex justify="flex-start" gap="2">
				<FlexItem>
					<input
						type="search"
						className="postqueue-add__input"
						value={ query }
						onChange={ ( event ) => setQuery( event.target.value ) }
						placeholder={ i18n.search_post_placeholder }
						aria-label={ i18n.search_post_placeholder }
					/>
				</FlexItem>
				{ isLoading && (
					<FlexItem>
						<Spinner />
					</FlexItem>
				) }
			</Flex>

			{ '' !== query.trim() && ! isLoading && (
				<ul className="postqueue-add__suggestions">
					{ 0 === suggestions.length && <li className="is-empty">{ i18n.no_posts_found }</li> }
					{ suggestions.map( ( post ) => (
						<li key={ post.post_id }>
							<Button
								variant="link"
								onClick={ () => {
									onAdd( post );
									setQuery( '' );
								} }
							>
								{ post.post_title }
							</Button>
						</li>
					) ) }
				</ul>
			) }
		</div>
	);
};

export default function QueueEditor() {
	const queueId = screen.queueId;
	const { items, saveItems, isLoading } = useQueueItems( queueId );

	const [ draft, setDraft ] = useState( [] );
	const [ saved, setSaved ] = useState( false );

	useEffect( () => {
		setDraft( [ ...items ] );
	}, [ items, queueId ] );

	const isDirty =
		items.length !== draft.length ||
		draft.some( ( item, index ) => items[ index ]?.post_id !== item.post_id );

	// The overview is a normal page now, so leaving is a link rather than a button we
	// can disable. This is the only thing standing between a reorder and losing it.
	useEffect( () => {
		if ( ! isDirty ) {
			return;
		}
		const warn = ( event ) => {
			event.preventDefault();
			event.returnValue = '';
		};
		window.addEventListener( 'beforeunload', warn );
		return () => window.removeEventListener( 'beforeunload', warn );
	}, [ isDirty ] );

	const move = ( from, to ) => {
		if ( from === to || to < 0 || to >= draft.length ) {
			return;
		}
		const next = [ ...draft ];
		const [ moved ] = next.splice( from, 1 );
		next.splice( to, 0, moved );
		setDraft( next );
		setSaved( false );
	};

	return (
		<>
			{ saved && ! isDirty && (
				<Notice status="success" onRemove={ () => setSaved( false ) }>
					{ i18n.saved }
				</Notice>
			) }

			<AddPost
				postIdsInQueue={ draft.map( ( item ) => item.post_id ) }
				onAdd={ ( post ) => {
					setDraft( [ post, ...draft ] );
					setSaved( false );
				} }
			/>

			{ isLoading && (
				<Flex justify="center" style={ { height: '40px' } }>
					<Spinner />
				</Flex>
			) }

			{ ! isLoading && (
				<QueueItems
					items={ draft }
					onMove={ move }
					onRemove={ ( item ) => {
						setDraft( draft.filter( ( it ) => it.post_id !== item.post_id ) );
						setSaved( false );
					} }
				/>
			) }

			<p className="submit">
				<Button
					variant="primary"
					disabled={ ! isDirty || isLoading }
					onClick={ () => {
						saveItems( draft.map( ( item ) => item.post_id ) );
						setSaved( true );
					} }
				>
					{ i18n.save }
				</Button>
				{ ' ' }
				<Button variant="tertiary" disabled={ ! isDirty } onClick={ () => setDraft( [ ...items ] ) }>
					{ i18n.reset }
				</Button>
			</p>
		</>
	);
}
