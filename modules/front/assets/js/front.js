hcapp.tsb_front_Loader = function()
{
	var self = this;
	var $this = jQuery({});
	this.on = function( e, callback ){
		$this.on( e, callback );
	}
	this.trigger = function( e, params ){
		$this.trigger( e, params );
	}

	self.nextlink = null;
	self.prevlink = null;
	self.range_label = null;
	self.data = {};

	this.render = function()
	{
		var $nextlink = jQuery('<a>', {
			href: '#next',
			class: 'hc-block hc-align-center hc-p2'
			})
			.addClass('hc-fs4')
			.addClass('hc-border')
			// .addClass('hc-border-gray')
			.append( '&gt;&gt;' )
			.on('click', self.route)
			;
		if( ! self.nextlink ){
			$nextlink = null;
		}

		var $prevlink = jQuery('<a>', {
			href: '#prev',
			class: 'hc-block hc-align-center hc-p2'
			})
			.addClass('hc-fs4')
			.addClass('hc-border')
			// .addClass('hc-border-gray')
			.append( '&lt;&lt;' )
			.on('click', self.route)
			;
		if( ! self.prevlink ){
			$prevlink = null;
		}

		var $range_label = jQuery('<div>', {
			class: 'hc-block hc-align-center hc-p2'
			})
			.append( self.range_label )
			;

		var out = new hcapp.html.Grid()
			.add( $prevlink, 3, 3 )
			.add( $range_label, 6, 6 )
			.add( $nextlink, 3, 3 )
			;

		return out.render();
	}

	this.route = function( e )
	{
		var route = jQuery(this).prop('hash').substr(1);

		switch( route ){
			case 'next':
				if( self.nextlink ){
					self.load( self.nextlink );
				}
				break;

			case 'prev':
				if( self.prevlink ){
					self.load( self.prevlink );
				}
				break;
		}
		return false;
	}

	this.load = function( url )
	{
		if( ! url.length ){
			return false;
		}

		self.trigger( 'load-start', url );

		jQuery.ajax({
			type: 'GET',
			url: url,
			dataType: "json",
			success: function(data, textStatus){
				self.nextlink = data.hasOwnProperty('nextlink') ? data['nextlink'] : null;
				self.prevlink = data.hasOwnProperty('prevlink') ? data['prevlink'] : null;
				self.range_label = data.hasOwnProperty('range_label') ? data['range_label'] : null;

				self.data = data;
				self.trigger( 'load-success', data );
			}
			})
			.fail( function(jqXHR, textStatus, errorThrown){
				alert( 'Ajax Error: ' + url );
				alert( jqXHR.responseText );
				})
			;
		return false;
	}

	this._find_slot_index = function( slot )
	{
		var index = -1;

	// find index
		if( ! self.data.hasOwnProperty('slots') ){
			return index;
		}

		for( var ii = 0; ii < self.data['slots'].length; ii++ ){
			var test_slot = self.data['slots'][ii];
			if( (test_slot['starts_at'] == slot['starts_at']) && (test_slot['ends_at'] == slot['ends_at']) ){
				index = ii;
				break;
			}
		}
		return index;
	}

	this.book_slot = function( slot, capacity )
	{
		var index = self._find_slot_index( slot );
		if( index < 0 ){
			return;
		}

		self.data['slots'][index]['booked'] += capacity;
		return this;
	}

	this.release_slot = function( slot, capacity )
	{
		var index = self._find_slot_index( slot );
		if( index < 0 ){
			return;
		}
		self.data['slots'][index]['booked'] -= capacity;
		return this;
	}
};

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

hcapp.tsb_front_Cart = function()
{
	var self = this;
	var $this = jQuery({});
	this.on = function( e, callback ){
		$this.on( e, callback );
	}
	this.trigger = function( e, params ){
		$this.trigger( e, params );
	}

	self.cart = [];
	self.default_cart = [];
	self.lang = {};
	self.show_removed = false;

	this.get_slots = function()
	{
		return self.cart;
	}

	this.in_cart = function( slot )
	{
		var index = self._find_index( slot, self.cart );
		var in_cart = ( index < 0 ) ? false : true;
		return in_cart;
	}

	this.in_default = function( slot )
	{
		var index = self._find_index( slot, self.default_cart );
		var in_cart = ( index < 0 ) ? false : true;
		return in_cart;
	}

	this.add_slot = function( slot )
	{
		if( ! self.in_cart(slot) ){
			slot['cart_removed'] = false;
			self.cart.push( slot );
			self.trigger( 'add-slot', slot );
		}
		return this;
	}

	this.add_default_slot = function( slot )
	{
		self.default_cart.push( slot );
		return this;
	}

	this.remove_slot = function( slot )
	{
		var index = self._find_index( slot, self.cart );
		if( index > -1 ){
			if( self.show_removed ){
				slot['cart_removed'] = true;
			}
			else {
				self.cart.splice( index, 1 );
			}
			self.trigger( 'remove-slot', slot );
		}
		return this;
	}

	this.restore_slot = function( slot )
	{
		var index = self._find_index( slot, self.cart );
		if( index > -1 ){
			slot['cart_removed'] = false;
			self.trigger( 'add-slot', slot );
		}
	}

	this._find_index = function( slot, where_in )
	{
		var index = -1;
	// find index
		for( var ii = 0; ii < where_in.length; ii++ ){
			var test_slot = where_in[ii];
			if( (test_slot['starts_at'] == slot['starts_at']) && (test_slot['ends_at'] == slot['ends_at']) ){
				index = ii;
				break;
			}
		}
		return index;
	}

	this.render_slot = function( slot )
	{
		var $remove_link = jQuery('<a>', {
			href:	'#',
			class:	'hc-fs2',
			})
			.on('click', function(e){
				self.remove_slot( slot );
				return false;
			})
			.append( self.lang['Remove'] )
			;

		var $restore_link = jQuery('<a>', {
			href:	'#',
			class:	'hc-fs2',
			})
			.on('click', function(e){
				self.restore_slot( slot );
				return false;
			})
			.append( self.lang['Restore'] )
			;

		var $out = jQuery('<div>');
		var $out_details = jQuery('<div>');

		$out_details
			.append(
				jQuery('<div>')
					.addClass('hc-muted2')
					.append( slot['formatted_date'] )
				)
			.append(
				jQuery('<div>')
					.append( slot['formatted_start'] + ' - ' + slot['formatted_end'] )
				)
			;

		if( slot['cart_removed'] ){
			$out_details
				.addClass('hc-line-through')
				;
		}

		if( self.show_removed ){
			if( ! self.in_default(slot) ){
				$out_details
					.addClass('hc-underline')
					;
			}
		}

		$out
			.append( $out_details )
			;

		if( ! slot['cart_removed'] ){
			$out.append(
				jQuery('<div>', {
					class:	'hc-block',
					})
					.append( $remove_link )
				);
		}
		else {
			$out.append(
				jQuery('<div>', {
					class:	'hc-block',
					})
					.append( $restore_link )
				);
		}

		$out
			.addClass('hc-block')
			// .addClass('hc-rounded')
			// .addClass('hc-border')
			.addClass('hc-nowrap')
			.addClass('hc-py1')
			.addClass('hc-px2')
			.addClass('hc-mb1')
			;

		return $out;
	}

	this.render = function()
	{
	// sort
		self.cart.sort( function(a,b){
			return (a['starts_at'] > b['starts_at']) ? 1 : ((b['starts_at'] > a['starts_at']) ? -1 : 0);
		}); 

	// render
		var out = new hcapp.html.Grid()
			.set_gutter(1)
			;

		for( var ii = 0; ii < self.cart.length; ii++ ){
			var this_slot = self.cart[ii];
			out.add( self.render_slot(this_slot), 4, 6);
		}

		return out.render();
	}

	this.get_value = function()
	{
		var out = [];

		for( var ii = 0; ii < self.cart.length; ii++ ){
			if( self.cart[ii]['cart_removed'] ){
				continue;
			}

			var this_out = [ self.cart[ii]['starts_at'], self.cart[ii]['ends_at'] ].join('-');
			out.push( this_out );
		}

		var final_out = out.join('|');
		return final_out;
	}
};

jQuery(document).ready( function()
{
	var divs = jQuery( '.hcj-tsb-front' );

	divs.each( function(index){
		var self = this;
		var $this = jQuery(this);
		var lang = $this.data('lang');

		var start_with = $this.data('startwith');
		if( ! start_with ){
			start_with = 'calendar';
		}

		var loader = new hcapp.tsb_front_Loader();

		var calendar = new hcapp.html.Month_Calendar();
		calendar.lang = lang['weekdays'];

		var slots = new hcapp.tsb_front_Slots();
		slots.orderlink = $this.data('orderlink');
		slots.lang = lang;

		var cart = new hcapp.tsb_front_Cart();
		cart.lang = lang;
		cart.show_removed = $this.data('showremoved');

		var set_hidden = $this.data('sethidden');
		if( set_hidden ){
			$hidden = $this.find('input[type=hidden]');
		}

		var startcart = $this.data('startcart');
		if( startcart ){
			for( var ii = 0; ii < startcart.length; ii++ ){
				cart.add_slot( startcart[ii] );
				if( cart.show_removed ){
					cart.add_default_slot( startcart[ii] );
				}
			}
		}

		var $el_loader = jQuery('<div>');
		var $el_calendar = jQuery('<div>');
		var $el_slots = jQuery('<div>');
		var $el_cart = jQuery('<div>');

		$this
			.append(
				new hcapp.html.List()
					.add( $el_cart )
					.add( $el_loader )
					.add( $el_calendar )
					.add( $el_slots )
					.render()
				)
			;

		var dates_details = {};
		var initially_loaded = false;

		this.render_cart = function( cart )
		{
			var confirm_url = $this.data('confirmlink');

			out = new hcapp.html.List()
				.set_gutter(2)
				.add( cart.render() )
				;

			var max_slots = parseInt( $this.data('conf')['maxslots'] );
			if( (! max_slots) || (max_slots > cart.get_slots().length) ){
				var $more_link = jQuery('<a>', {
					href:	'#',
					})
					.append( '+' + ' ' + lang['More Slots'] )
					.addClass('hc-fs4')
					
					.on('click', function(e){
						if( start_with != 'cart' ){
							$el_cart.hide();
						}

						if( ! initially_loaded ){
							initially_loaded = true;
							loader.load( $this.data('nextlink') );
						}

						$el_loader.show();
						$el_calendar.show();
						return false;
					})
					;

				out
					.add( $more_link )
					;
			}

		// confirm link
			if( confirm_url && cart.get_slots().length ){
				var now_value = cart.get_value();
				confirm_url = confirm_url.replace( '_SLOTS_', now_value );

				var $confirm_link = jQuery('<a>', {
					href:	confirm_url,
					})
					.append( lang['Continue'] )

					.addClass('hc-fs4')
					.addClass('hc-p3')
					.addClass('hc-block')
					.addClass('hc-border')
					.addClass('hc-border-gray')
					.addClass('hc-border-rounded')
					.addClass('hc-align-center')
					;

				out
					.add( $confirm_link )
					;
			}

			out = out.render();
			return out;
		};

		loader.on('load-start', function(e, url)
		{
			console.log( url );
			hc2_set_loader( $this );
		});

		loader.on('load-success', function(e, data)
		{
			dates_details = data['dates_details'];
			hc2_unset_loader( $this );

			$el_loader
				.empty()
				.append( loader.render() )
				;

			calendar
				.select_date( data['dates'][0] )
				;

			var selected_slots = cart.get_slots();
			for( var ii = 0; ii < selected_slots.length; ii++ ){
				loader.book_slot( selected_slots[ii], 1 );
			}

			loader.trigger('data-changed');
			$el_slots.hide();
		});

		loader.on('data-changed', function(e)
		{
			if( ! loader.data.hasOwnProperty('slots') ){
				return;
			}

			slots
				.set_slots( loader.data['slots'] )
				;
			calendar
				.set_dates( loader.data['dates_matrix'] )
				;
			$el_calendar
				.empty()
				.append( calendar.render() )
				;
			// calendar.trigger('click-date', calendar.get_selected_date());
		});

	// render date in calendar
		calendar.on('render-date', function(e, params)
		{
			if( ! params.date ){
				return;
			}

			var this_date = params.date;

			var date_label = this_date;
			date_label = date_label.substr(6, 2);
			date_label = parseInt( date_label, 10 );

		// show available, booked slots
			var date_slots = slots.get_slots( this_date );
		// count capacity and booked
			var this_capacity = 0;
			var this_booked = 0;

			for( var ii = 0; ii < date_slots.length; ii++ ){
				this_capacity += date_slots[ii]['capacity'];
				this_booked += date_slots[ii]['booked'];
			}

			if( this_capacity > this_booked ){
				var $link = jQuery('<a>', {
					href:	'#' + params.date,
					})
					// .addClass('hc-btn')
					.on('click', function(e){
						calendar.trigger('click-date', this_date);
						return false;
					})
					;
			}
			else {
				var $link = jQuery('<div>')
					.addClass('hc-muted3')
					.css('cursor', 'not-allowed')
					;
			}

			$link
				.append( date_label )
				// .append( '/' + date_slots.length + '/' + this_capacity + '/' + this_booked )
				.attr('title', date_label)
				.addClass('hc-block')
				.addClass('hc-py1')
				.addClass('hc-mt1')
				.addClass('hc-fs4')
				;

			params.cell
				// .html( date_label )
				.html( $link )
				.addClass('hc-mx1')
				;
		});

	// show slots on date select
		calendar.on('click-date', function(e, date)
		{
			calendar.select_date( date );

			var slots_view = new hcapp.html.List()
				.set_gutter(3)
				.add(
					jQuery('<a>', {
						href:	'#',
						})
						.append( dates_details[date]['weekday_formatted'] + ', ' + dates_details[date]['formatted'] )

						.addClass('hc-block')
						.addClass('hc-p2')
						.addClass('hc-fs4')
						.addClass('hc-my2')
						.addClass('hc-underline')

						.on('click', function(e){
							$el_slots.hide();
							$el_loader.show();
							$el_calendar.show();
							return false;
						})
					)
				.add( 
					slots.render(date)
					)
				;

			$el_slots
				.empty()
				.show()
				.append( slots_view.render() )
				;

			$el_loader.hide();
			$el_calendar.hide();
		});

	// catch click on slots
		slots.on('select-slot', function(e, slot)
		{
			// if already in the cart?
			if( cart.in_cart(slot) ){
				cart.remove_slot( slot );
			}
			else {
				cart.add_slot( slot );
			}
		});

	// redraw cart
		cart.on('add-slot', function(e, slot)
		{
			$el_cart
				.empty()
				.show()
				.append( self.render_cart(cart) )
				;
			$el_slots.hide();

			if( set_hidden ){
				$hidden.val( cart.get_value() );
			}
		});

		cart.on('remove-slot', function(e, slot)
		{
			$el_cart.empty();

			if( cart.get_slots().length ){
				$el_cart.show()
					.append( self.render_cart(cart) )
					;
			}
			else {
				if( start_with == 'cart' ){
					$el_cart.show()
						.append( self.render_cart(cart) )
						;
				}
				else {
					$el_cart.hide();
					$el_loader.show();
					$el_calendar.show();
				}
			}

			if( set_hidden ){
				$hidden.val( cart.get_value() );
			}
		});

	// highlight slots already in cart
		cart.on('add-slot', function(e, slot)
		{
			loader.book_slot( slot, 1 );
			loader.trigger('data-changed');
		});

		cart.on('remove-slot', function(e, slot)
		{
			loader.release_slot( slot, 1 );
			loader.trigger('data-changed');
		});

	// ok load
		if( start_with == 'cart' ){
			$el_cart.empty();
			$el_cart.show()
				.append( self.render_cart(cart) )
				;
		}
		else {
			initially_loaded = true;
			loader.load( $this.data('nextlink') );
		}
	});
});
