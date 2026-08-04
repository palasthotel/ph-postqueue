import { Button } from '@wordpress/components';
import { chevronDown, chevronUp, dragHandle } from '@wordpress/icons';
import { useState } from '@wordpress/element';

const { i18n } = PostQueue;

/**
 * The ordered items of a queue, as the table WordPress uses for lists.
 *
 * Reordering works two ways on purpose. Dragging uses the browser's own drag events -
 * no library - and up/down buttons do the same thing for anyone on a keyboard, which
 * dragging alone never covers. It is the pair the block editor offers for moving blocks.
 */
export default function QueueItems( { items, onMove, onRemove } ) {
	const [ draggedIndex, setDraggedIndex ] = useState( null );
	const [ overIndex, setOverIndex ] = useState( null );

	if ( 0 === items.length ) {
		return (
			<table className="wp-list-table widefat fixed striped">
				<tbody>
					<tr>
						<td>{ i18n.queue_empty }</td>
					</tr>
				</tbody>
			</table>
		);
	}

	const drop = ( toIndex ) => {
		if ( null !== draggedIndex && draggedIndex !== toIndex ) {
			onMove( draggedIndex, toIndex );
		}
		setDraggedIndex( null );
		setOverIndex( null );
	};

	return (
		<table className="wp-list-table widefat fixed striped postqueue-items">
			<thead>
				<tr>
					<td className="manage-column column-order check-column">
						<span className="screen-reader-text">{ i18n.column_order }</span>
					</td>
					<th scope="col" className="manage-column column-primary">{ i18n.column_post }</th>
					<th scope="col" className="manage-column">{ i18n.column_status }</th>
					<th scope="col" className="manage-column">{ i18n.column_date }</th>
					<th scope="col" className="manage-column">
						<span className="screen-reader-text">{ i18n.column_actions }</span>
					</th>
				</tr>
			</thead>
			<tbody>
				{ items.map( ( item, index ) => (
					<tr
						key={ item.post_id }
						draggable
						onDragStart={ ( event ) => {
							setDraggedIndex( index );
							event.dataTransfer.effectAllowed = 'move';
							// Firefox ignores a drag without any data set.
							event.dataTransfer.setData( 'text/plain', String( item.post_id ) );
						} }
						onDragOver={ ( event ) => {
							event.preventDefault();
							setOverIndex( index );
						} }
						onDrop={ ( event ) => {
							event.preventDefault();
							drop( index );
						} }
						onDragEnd={ () => drop( draggedIndex ) }
						className={ [
							draggedIndex === index ? 'is-dragging' : '',
							overIndex === index && draggedIndex !== index ? 'is-drop-target' : '',
						].join( ' ' ).trim() }
					>
						<td className="column-order check-column">
							<span className="postqueue-items__handle" aria-hidden="true">
								{ dragHandle }
							</span>
							<Button
								size="small"
								icon={ chevronUp }
								label={ i18n.move_up }
								disabled={ 0 === index }
								onClick={ () => onMove( index, index - 1 ) }
							/>
							<Button
								size="small"
								icon={ chevronDown }
								label={ i18n.move_down }
								disabled={ index === items.length - 1 }
								onClick={ () => onMove( index, index + 1 ) }
							/>
						</td>
						<td className="column-primary">
							<strong>
								{ item.edit_post_link ? (
									<a href={ item.edit_post_link }>{ item.post_title }</a>
								) : (
									item.post_title
								) }
							</strong>
						</td>
						<td>
							{ 'future' === item.post_status ? i18n.status_future : i18n.status_published }
						</td>
						<td>{ item.post_date }</td>
						<td>
							<Button variant="link" isDestructive onClick={ () => onRemove( item ) }>
								{ i18n.remove }
							</Button>
						</td>
					</tr>
				) ) }
			</tbody>
		</table>
	);
}
