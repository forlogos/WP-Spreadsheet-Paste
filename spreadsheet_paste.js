var el = wp.element.createElement;

wp.blocks.registerBlockType('spreadsheet-paste/paste-block', {
	title: 'Spreadsheet Paste',
	icon: 'media-spreadsheet',
	category: 'common',
	attributes: {
		headers: { type: 'string', default: 'headers-first-row' },
		data: { type: 'string' },
	},

	edit: function(props) {
		function updateHeaders( newdata ) {
			props.setAttributes( { headers: event.target.value } );
		}

		function updateData( event ) {
			props.setAttributes( { data: event.target.value } );
		}

		return el( 'div', 
			{ 
				className: 'spreadsheet-paste show-' + props.attributes.headers
			}, 
			el(
				'select', 
				{
					onChange: updateHeaders,
					value: props.attributes.headers,
				},
				el("option", {value: "headers-first-row" }, "First Row Is Headers"),
				el("option", {value: "headers-first-column" }, "First Column Is Headers"),
				el("option", {value: "headers-first-row-and-column" }, "First Row and Column are Headers"),
				el("option", {value: "headers-none" }, "No Headers")
			),
			el(
				'textarea', 
				{
					type: 'text', 
					placeholder: 'Paste spreadsheet data here...',
					value: props.attributes.data,
					onChange: updateData,
					style: { width: '100%' }
				}
			)
		);
	},
	save: function(props) {
		return el( 'div', 
			{ 
				className: 'spreadsheet-paste show-' + props.attributes.headers
			}, 
			el(
				'figure', 
				null,
				props.attributes.data
			)
		);
	}
});