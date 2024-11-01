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
