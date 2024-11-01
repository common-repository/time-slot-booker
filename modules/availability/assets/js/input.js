(function($) {

this.input = function( $el )
{
	var self = this;

	self.blocks = [
		[ 8*60*60, 4*60*60, 30*60 ],
		[ 13*60*60, 4*60*60, 30*60 ],
		];

	self.blocks = [];

	this.init = function()
	{
		// parse default value
		$hidden = $el.find('input[type=hidden]');
		var this_value = $hidden.val();

		if( this_value.length ){
			var blocks = this_value.split(',');
			for( var ii = 0; ii < blocks.length; ii++ ){
				var this_block = blocks[ii].split('-');
				for( var jj = 0; jj < this_block.length; jj++ ){
					this_block[jj] = parseInt( this_block[jj] );
				}
				self.blocks.push( this_block );
			}
		}
	}

	this.render = function()
	{
		var $container = $el.find('.hcj-display:first');
		var lang = $el.data('lang');

		$container.empty();

		if( ! self.blocks.length ){
			var $this_block = jQuery('<div>', {
				class:	'hc-block hc-px1 hc-py1 hc-mb1 hc-border hc-rounded',
				});
			$this_block.append( lang['Day Off'] );
			$container.append( $this_block );
		}

		for( var ii = 0; ii < self.blocks.length; ii++ ){
			var $this_block = jQuery('<div>', {
				class:	'hc-block hc-px1 hc-py1 hc-mb1 hc-border hc-rounded',
				// class:	'hc-block hc-mb2',
				});

//<a class="hc-red hc-closer"><i class="dashicons dashicons-dismiss hc-dashicons"></i></a>
			var $delete_link = jQuery('<a>', {
				href:	'#',
				class:	'hc-red hc-closer',
				data:	{index: ii}
				})
				.append(
					jQuery('<i>', {
						class:	'dashicons dashicons-dismiss hc-dashicons',
						})
					)
				;

			var $delete_link = jQuery('<a>', {
				href:	'#',
				data:	{index: ii}
				})
				.addClass('hc-block')
				.addClass('hc-fs2')
				.addClass('hc-red')
				.append( lang['Remove'] )
				.on('click', function(e){
					var ii = jQuery(this).data('index');
					self.delete_block( ii );
					return false;
					})
				;

			$this_block
				.append(
					new hcapp.html.List()
						.set_gutter(2)
						.add( self.render_block(self.blocks[ii]) )
						.add( $delete_link )
						.render()
					)
				;

			$container.append( $this_block );
		}

		$container.append( self.render_add_btn() );

		var this_value = self.get_value();
		$hidden = $el.find('input[type=hidden]');
		$hidden.val( this_value );
	}

	this.get_value = function()
	{
		var out = [];

		for( var ii = 0; ii < self.blocks.length; ii++ ){
			var start = self.blocks[ii][0];
			var end = self.blocks[ii][1];
			var slot = self.blocks[ii][2];

			var this_out = [start, end, slot].join('-');
			out.push( this_out );
		}

		var final_out = out.join(',');
		return final_out;
	}

	this.render_block = function( block )
	{
		var start = parseInt(block[0]);
		var end = parseInt(block[1]);
		var slot = parseInt(block[2]);
		var end_day = 24 * 60 * 60;

		var time_format = $el.data('time-format');
		var duration_format = $el.data('duration-format');

		var start_view = time_format[ start ];
		var end_view = ( end > end_day ) ? ' > ' + time_format[end % end_day] : time_format[end];

		var duration_view = duration_format[slot];
		var how_many_view = (end - start) / slot;

		var out = '';

		out += '<div class="hc-nowrap">';

		out += '<div>';
		out += start_view + ' - ' + end_view;
		out += '</div>';

		out += '<div class="hc-muted2 hc-fs2">';
		out += duration_view + ' [' + how_many_view + ']';;
		out += '</div>';

		out += '</div>';

		// out += start_view + ' - ' + end_view + ' / ' + duration_view + ' [' + how_many_view + ']';
		return out;
	}

	this.delete_block = function( ii )
	{
		self.blocks.splice( ii, 1 );
		self.render();
	}

	this.add_block = function( block_array )
	{
		self.blocks.push( block_array );
		self.render();
	}

	this.render_add_btn = function()
	{
		var $input_btn = jQuery('<input>', {
			type:	'button',
			class:	'hc-field hc-block hc-py1 hc-px3 hc-btn hc-theme-btn-secondary button',
			value:	'+',
			});

		var $out = jQuery('<div>', {
			// class:	'hc-block hc-p2 hc-mb1 hc-border hc-rounded hc-border-red',
			class:	'hc-block hc-mb1 hc-mt2',
			});

	// listeners
		$input_btn.on('click', function(e){
			$out.empty();
			$out.append( self.render_add_form() );
			return false;
		});

		$out.append( $input_btn );
		return $out;
	}

	this.render_add_form = function()
	{
		var time_unit = 5 * 60;
		var end_day = 24 * 60 * 60;
		var time_format = $el.data('time-format');
		var duration_format = $el.data('duration-format');
		var lang = $el.data('lang');

		var $input_start = jQuery('<select>', {
			class:	'hc-field hc-block',
			});
		var $input_end = jQuery('<select>', {
			class:	'hc-field hc-block',
			});
		var $input_slot = jQuery('<select>', {
			class:	'hc-field hc-block',
			});
		var $input_btn = jQuery('<input>', {
			type:	'button',
			class:	'hc-field hc-block hc-py1 hc-px3 hc-btn hc-theme-btn-secondary button',
			value:	lang['Add'],
			});

	// start
		var start_from = 0;
		if( self.blocks.length > 0 ){
			var last_block = self.blocks[ self.blocks.length-1 ];
			// var end = last_block[0] + last_block[1];
			var end = last_block[1];
			start_from = end;
		}
		if( start_from >= end_day ){
			return false;
		}
		$input_start.empty();
		for( var ts = start_from; ts <= end_day; ts += time_unit ){
			$input_start.append(
				jQuery('<option>', {
					value: ts,
					text : time_format[ts]
					})
				);
		}

	// duration
		var slot_options = [
			5*60, 10*60, 15*60, 20*60, 30*60, 45*60, 60*60,
			75*60, 90*60, 2*60*60, 2.5*60*60, 3*60*60, 4*60*60, 5*60*60, 6*60*60,
			7*60*60, 8*60*60, 9*60*60, 10*60*60, 11*60*60, 12*60*60, 
			16*60*60, 24*60*60
			];

	// listeners
		$input_btn.on('click', function(e){
			var start = parseInt( $input_start.val() );
			var end = parseInt( $input_end.val() );
			var slot = parseInt( $input_slot.val() );
			// self.add_block( [start, (end - start), slot] );
			self.add_block( [start, end, slot] );
			return false;
		});

	// update end depending on start
		$input_start.on('change', function(e){
			$input_end.trigger('listen');
		});
		$input_end.on('change', function(e){
			$input_slot.trigger('listen');
		});

		$input_end.on('listen', function(e){
			var start = parseInt( $input_start.val() );

			var current_end = parseInt( $input_end.val() );
			var current_end_exists = false;

			var end_from = start + time_unit;
			var end_to = start + end_day;

			$input_end.empty();
			for( var ts = end_from; ts <= end_to; ts += time_unit ){
				if( ts == current_end ){
					current_end_exists = true;
				}
				var ts_view = ( ts > end_day ) ? ' > ' + time_format[ts % end_day] : time_format[ts];
				$input_end.append(
					jQuery('<option>', {
						value: ts,
						text : ts_view
						})
					);
			}

			if( ! current_end_exists ){
				current_end = end_from;
			}
			$input_end.val(current_end);

			$input_slot.trigger('listen');
			return false;
		});

	// update slot depending on start/end
		$input_slot.on('listen', function(e){
			var start = parseInt( $input_start.val() );
			var end = parseInt( $input_end.val() );
			var max_duration = end - start;

			var current_slot = parseInt( $input_slot.val() );
			var current_slot_exists = false;

			var new_options = [];
			for( var ii = 0; ii < slot_options.length; ii++ ){
				var ts = slot_options[ii];
				if( ts > max_duration ){
					continue;
				}

				if( max_duration % ts ){
					continue;
				}

				new_options.push(ts);
				if( ts == current_slot ){
					current_slot_exists = true;
				}
			}

			if( ! current_slot_exists ){
				current_slot = new_options[0];
			}

			$input_slot.empty();
			for( var ii = 0; ii < new_options.length; ii++ ){
				var ts = new_options[ii];
				$input_slot.append(
					jQuery('<option>', {
						value: ts,
						text : duration_format[ts]
						})
					);
			}

			$input_slot.val(current_slot);
			return false;
		});

	// end
		$input_end.trigger('listen');
	// duration
		$input_slot.trigger('listen');

	// display
		var out = []

		out.push(
			jQuery('<div>', {
				class:	'hc-mb1 hc-muted2',
				})
				.append( lang['From'] )
			);
		out.push(
			jQuery('<div>', {
				class:	'hc-mb1',
				})
				.append( $input_start )
			);

		out.push(
			jQuery('<div>', {
				class:	'hc-mb1 hc-muted2',
				})
				.append( lang['To'] )
			);
		out.push(
			jQuery('<div>', {
				class:	'hc-mb1',
				})
				.append( $input_end )
			);

		out.push(
			jQuery('<div>', {
				class:	'hc-mb1 hc-muted2',
				})
				.append( lang['Interval'] )
			);
		out.push(
			jQuery('<div>', {
				class:	'hc-mb1',
				})
				.append( $input_slot )
			);

		out.push( $input_btn );

		return out;
	}
}

jQuery(document).ready( function()
{
	var inputs = jQuery( '.hcj-tsb-availability-input' );
	inputs.each( function(index){
		var this_input = new self.input( jQuery(this) );
		this_input.init();
		this_input.render();
	});
});

}());
