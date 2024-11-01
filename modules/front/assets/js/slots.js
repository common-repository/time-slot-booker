hcapp.tsb_front_Slots = function()
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
	self.slots_by_date = {};

	self.orderlink = null;
	self.lang = {};

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

			var this_date = self.slots[ii]['date'];
			if( ! self.slots_by_date.hasOwnProperty(this_date) ){
				self.slots_by_date[this_date] = [];
			}
			self.slots_by_date[this_date].push( self.slots[ii] );
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

			if( all_slots[ii]['booked'] >= all_slots[ii]['capacity'] ){
				continue;
			}
			this_out = self.render_slot(all_slots[ii]);
			out.add( this_out, 4, 6 );
		}

		return out.render();
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
};