hcapp.tsb_schedule_Slots = function()
{
	var self = this;
	var $this = jQuery({});
	this.on = function( e, callback ){
		$this.on( e, callback );
	}
	this.trigger = function( e, params ){
		$this.trigger( e, params );
	}

	self.slots = [];
	self.bookings = [];

	self.slots_by_date = {};

	self.orderlink = null;
	self.lang = {};
	self._cells = {};

	this.set_slots = function( slots )
	{
		self.slots = slots;
		self.slots_by_date = {};

		var parse_int = ['capacity', 'booked', 'starts_at', 'ends_at'];
		for( var ii = 0; ii < self.slots.length; ii++ ){
			for( var jj = 0; jj < parse_int.length; jj++ ){
				var pname = parse_int[jj];
				if( self.slots[ii].hasOwnProperty(pname) ){
					self.slots[ii][pname] = parseInt( self.slots[ii][pname] );
				}
			}
			self.slots[ii]['type'] = 'slot';

			var this_date = self.slots[ii]['date'];
			if( ! self.slots_by_date.hasOwnProperty(this_date) ){
				self.slots_by_date[this_date] = [];
			}
			self.slots_by_date[this_date].push( self.slots[ii] );
		}

		return this;
	}

	this.set_bookings = function( bookings )
	{
		self.bookings = bookings;

		var parse_int = ['starts_at', 'ends_at'];
		for( var ii = 0; ii < self.bookings.length; ii++ ){
			for( var jj = 0; jj < parse_int.length; jj++ ){
				var pname = parse_int[jj];
				if( self.bookings[ii].hasOwnProperty(pname) ){
					self.bookings[ii][pname] = parseInt( self.bookings[ii][pname] );
				}
			}
			self.bookings[ii]['type'] = 'booking';

			var this_date = self.bookings[ii]['date'];
			if( ! self.slots_by_date.hasOwnProperty(this_date) ){
				self.slots_by_date[this_date] = [];
			}
			self.slots_by_date[this_date].push( self.bookings[ii] );
		}

		return this;
	}

	this.get_slots = function( date )
	{
		var this_return = [];

		if( self.slots_by_date.hasOwnProperty(date) ){
			this_return = self.slots_by_date[date];
		}

		this_return.sort( function(a,b){
			return (a['starts_at'] > b['starts_at']) ? 1 : ((b['starts_at'] > a['starts_at']) ? -1 : 0);
		});

		return this_return;
	}

	this.render = function( date )
	{
	// combine & sort
		var all_slots = self.get_slots( date );

		if( ! all_slots.length ){
			var out = self.lang['Day Off'];
			return out;
		}

	// render
		var out = new hcapp.html.Grid()
			.set_gutter(1)
			;

		for( var ii = 0; ii < all_slots.length; ii++ ){
			var this_out = '';

			switch( all_slots[ii]['type'] ){
				case 'slot':
					if( all_slots[ii]['booked'] >= all_slots[ii]['capacity'] ){
						continue;
					}
					this_out = self.render_slot(all_slots[ii]);
					break;

				case 'booking':
					this_out = self.render_booking(all_slots[ii]);
					break;
			}

			out.add( this_out, 3, 6 );
		}

		return out.render();
	}

	this.slot_key = function( slot )
	{
		var cell_key = slot['starts_at'] + '-' + slot['ends_at'];
		return cell_key;
	}

	this.set_cell = function( slot, $view )
	{
		var cell_key = self.slot_key( slot );
		self._cells[ cell_key ] = $view;
		return this;
	}

	this.get_cell = function( slot )
	{
		var cell_key = self.slot_key( slot );		
		return self._cells[ cell_key ];
	}

	this.render_slot = function( slot )
	{
		var $link = jQuery('<a>', {
			href: '#',
			})
			.on('click', function(e){
				self.trigger('select-slot', slot);
				return false;
			})
			;

		var my_label = slot['formatted_start'] + ' - ' + slot['formatted_end'];
		$link
			.append( my_label )
			.attr('title', my_label)
			;

		var $out = jQuery('<div>', {
			});

		$out
			.append( jQuery('<div>').append($link) )
			;

		$out
			.addClass('hc-block')
			.addClass('hc-nowrap')
			.addClass('hc-py1')
			.addClass('hc-px2')
			// .addClass('hc-border')
			// .addClass('hc-rounded')
			;

		self.trigger('render-slot', slot);
		return $out;
	}

	this.render_booking = function( slot )
	{
		var order_url = null;

		try {
			var order_id = slot['order']['id'];
			order_url = self.orderlink;
			order_url = order_url.replace( '_ID_', order_id );
		}
		catch(err){
		}

		var order_status = null;
		try {
			order_status = slot['order']['status'];
		}
		catch(err){
			order_status = null;
		}

		var customer_details = null;
		try {
			customer_details = slot['order']['customer'];
		}
		catch(err){
			customer_details = null;
		}

		var $link = jQuery('<a>')
			.append( slot['formatted_start'] + ' - ' + slot['formatted_end'] )
			;

		if( order_url ){
			$link
				.attr('href', order_url)
				;
		}
		else {
			$link
				.attr('href', '#')
				.on('click', function(e){
					self.trigger('select-slot', slot);
					return false;
				})
				;
		}

		var $out = jQuery('<div>', {
			});

		$out
			.append( jQuery('<div>').append($link) )
			;
		if( customer_details ){
			$out
				.append( jQuery('<div>').append(customer_details) )
				;
		}

		$out
			.addClass('hc-block')
			.addClass('hc-rounded')
			.addClass('hc-py1')
			.addClass('hc-px2')
			.addClass('hc-nowrap')
			;

		switch( order_status ){
			case 'pending':
				$out.addClass('hc-bg-striped');
				break;

			case 'cancelled':
				$out.addClass('hc-line-through');
				break;

			default:
				$out.addClass('hc-bg-silver');
				break;
		}

		return $out;
	}
};