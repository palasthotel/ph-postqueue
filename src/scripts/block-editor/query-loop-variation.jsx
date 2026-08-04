import { registerBlockVariation } from '@wordpress/blocks';
import { addFilter } from '@wordpress/hooks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, Spinner } from '@wordpress/components';
import { createHigherOrderComponent } from '@wordpress/compose';
import { useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

const { rest_namespace: ns, query_key: QUERY_KEY, i18n } = window.PostQueueBlockEditor;

const VARIATION = 'postqueue/queue';

/**
 * A variation of core/query, so a curated queue can be rendered with the same
 * pagination, layout and Post Template inner blocks as any other loop.
 *
 * The queue slug is written into the block's own query attribute. The Post Template
 * block spreads query keys it does not know into its REST request, so the editor
 * preview is filtered by the same PHP that filters the front end.
 */
registerBlockVariation( 'core/query', {
	name: VARIATION,
	title: i18n.variation,
	description: i18n.variation_desc,
	icon: 'list-view',
	scope: [ 'inserter' ],
	isActive: ( blockAttributes ) => blockAttributes.namespace === VARIATION,
	attributes: {
		namespace: VARIATION,
		query: {
			perPage: 10,
			pages: 0,
			offset: 0,
			postType: 'post',
			order: 'desc',
			orderBy: 'date',
			author: '',
			search: '',
			exclude: [],
			sticky: '',
			inherit: false,
			[ QUERY_KEY ]: '',
		},
	},
} );

const QueueSelect = ( { attributes, setAttributes } ) => {
	const [ queues, setQueues ] = useState( null );

	useEffect( () => {
		let cancelled = false;
		apiFetch( { path: `/${ ns }/queues` } )
			.then( ( result ) => ! cancelled && setQueues( result ) )
			.catch( () => ! cancelled && setQueues( [] ) );
		return () => {
			cancelled = true;
		};
	}, [] );

	if ( null === queues ) {
		return <Spinner />;
	}

	const options = [
		{ label: i18n.select_none, value: '' },
		...queues.map( ( queue ) => ( { label: queue.name, value: queue.slug } ) ),
	];

	return (
		<SelectControl
			label={ i18n.select_queue }
			help={ i18n.select_help }
			value={ attributes.query?.[ QUERY_KEY ] || '' }
			options={ options }
			onChange={ ( value ) =>
				setAttributes( {
					query: { ...attributes.query, [ QUERY_KEY ]: value },
				} )
			}
			__next40pxDefaultSize
			__nextHasNoMarginBottom
		/>
	);
};

const withQueueControl = createHigherOrderComponent(
	( BlockEdit ) => ( props ) => {
		if ( 'core/query' !== props.name || VARIATION !== props.attributes?.namespace ) {
			return <BlockEdit { ...props } />;
		}
		return (
			<>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody title={ i18n.select_queue } initialOpen>
						<QueueSelect { ...props } />
					</PanelBody>
				</InspectorControls>
			</>
		);
	},
	'withPostqueueQueueControl'
);

addFilter( 'editor.BlockEdit', 'postqueue/query-loop-control', withQueueControl );
