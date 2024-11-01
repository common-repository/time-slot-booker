(function($) {

this.input = function( $el )
{
	var self = this;
	self.input_value = [0, 0];

	var $container = $el.find('.hcj-display:first');
	var $hidden = $el.find('input[type=hidden]');
	var $input_start = jQuery('<select>', {
		class:	'hc-field hc-block',
		});
	var $input_end = jQuery('<select>', {
		class:	'hc-field hc-block',
		});

	var $allday_input = $el.find(':checkbox');

	if( $allday_input.length ){
		$allday_input.on('change', function(e){
			var is_all_day = jQuery(this)[0].checked;
			if( is_all_day ){
				$container.hide();
			}
			else {
				$container.show();
			}
			$hidden.val( self.get_value() );
		});
	}

	this.init = function()
	{
		// parse default value
		var this_value = $hidden.val();

		if( this_value.length ){
			self.input_value = [];
			var this_times = this_value.split('-');
			for( var jj = 0; jj < this_times.length; jj++ ){
				self.input_value.push( parseInt(this_times[jj]) );
			}
		}
	}

	this.get_value = function()
	{
		var times = [];
		var is_all_day = $allday_input.length ? $allday_input[0].checked : false;

		if( is_all_day ){
			times.push( 0 );
			times.push( 24*60*60 );
		}
		else {
			times.push( $input_start.val() );
			times.push( $input_end.val() );
		}

		var out = times.join('-');
		return out;
	}

	this.render = function()
	{
		var time_unit = 5 * 60;
		var end_day = 24 * 60 * 60;
		var time_format = $el.data('time-format');

	// start
		var start_from = 0;

		$input_start.empty();
		for( var ts = start_from; ts <= end_day; ts += time_unit ){
			$input_start.append(
				jQuery('<option>', {
					value: ts,
					text : time_format[ts]
					})
				);
		}

	// update end depending on start
		$input_start.on('change', function(e){
			$input_end.trigger('listen');
		});

		$input_end.on('listen', function(e){
			var start = parseInt( $input_start.val() );

			var current_end = self.input_value[1] ? self.input_value[1] : $input_end.val();
			current_end = parseInt( current_end );

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
			self.input_value[1] = current_end;
			return false;
		});

		$input_start.val( self.input_value[0] );
		$input_end.val( self.input_value[1] );

	// end
		$input_end.trigger('listen');

	// update hidden value
		$input_start.on('change', function(e){
			$hidden.val( self.get_value() );
		});

		$input_end.on('change', function(e){
			$hidden.val( self.get_value() );
		});

	// display
		var out = new hcapp.html.List_Inline()
			.set_gutter(2)
			.add( $input_start )
			.add( '-' )
			.add( $input_end )
			;
		out = out.render();

		$container
			.empty()
			.append( out )
			;

		$hidden.val( self.get_value() );
	}
}

jQuery(document).ready( function()
{
	var inputs = jQuery( '.hcj-timerange-input' );
	inputs.each( function(index){
		var this_input = new self.input( jQuery(this) );
		this_input.init();
		this_input.render();
	});
});

}());
