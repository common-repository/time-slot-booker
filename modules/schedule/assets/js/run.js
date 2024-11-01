jQuery(document).ready( function()
{
	var divs = jQuery( '.hcj-tsb-schedule' );

	divs.each( function(index){
		var self = this;
		var $this = jQuery(this);
		var lang = $this.data('lang');

		var start_with = $this.data('startwith');
		if( ! start_with ){
			start_with = 'calendar';
		}

		var loader = new hcapp.tsb_schedule_Loader();

		var calendar = new hcapp.html.Month_Calendar();
		calendar.lang = lang['weekdays'];

		var slots = new hcapp.tsb_schedule_Slots();
		slots.orderlink = $this.data('orderlink');
		slots.lang = lang;

		var cart = new hcapp.tsb_schedule_Cart();
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

			var $more_link = jQuery('<a>', {
				href:	'#',
				class:	'hc-btn hc-theme-btn-submit page-title-action hc-theme-btn-secondary',
				})
				.append( '+' + ' ' + lang['More Slots'] )
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

			out = new hcapp.html.List()
				.set_gutter(2)
				.add( cart.render() )
				.add( $more_link )
				;

		// confirm link
			if( confirm_url && cart.get_slots().length ){
				var now_value = cart.get_value();
				confirm_url = confirm_url.replace( '_SLOTS_', now_value );

				var $confirm_link = jQuery('<a>', {
					href:	confirm_url,
					class:	'hc-btn hc-block hc-theme-btn-submit button-primary hc-theme-btn-primary hc-align-center',
					})
					.append( lang['Create New Order'] )
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
				.set_bookings( loader.data['bookings'] )
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

			var $link = jQuery('<a>', {
				href:	'#' + params.date,
				})
				.addClass('hc-block')
				.addClass('hc-fs4')
				.addClass('hc-btn')
				.addClass('hc-py1')
				.addClass('hc-mt1')
				.on('click', function(e){
					calendar.trigger('click-date', this_date);
					return false;
				})
				;

			var date_label = this_date;
			date_label = date_label.substr(6, 2);
			date_label = parseInt( date_label, 10 );

			$link
				.append( date_label )
				.attr('title', date_label)
				;

		// show available, booked slots
			var date_slots = slots.get_slots( this_date );
		// count capacity and booked
			var this_capacity = 0;
			var this_booked = 0;
			var this_bookings = 0;

			for( var ii = 0; ii < date_slots.length; ii++ ){
				if( date_slots[ii]['type'] == 'slot' ){
					this_capacity += date_slots[ii]['capacity'];
					this_booked += date_slots[ii]['booked'];
				}
				if( date_slots[ii]['type'] == 'booking' ){
					// if( date_slots[ii]['order']['status'] != 'cancelled' ){
						this_bookings++;
					// }
				}
			}

			if( this_bookings > this_booked ){
				this_capacity = this_capacity + (this_bookings - this_booked);
				this_booked = this_bookings;
			}

			var $occupy_indicator = jQuery('<div>')
				.addClass('hc-fs1')
				// .css('font-size', '.3em')
				;

			if( this_capacity || this_booked ){
			// horizontal
				var occupy_indicator = new hcapp.html.Grid()
					.set_gutter(0)
					;

				var percent_booked = this_capacity ? (this_booked / this_capacity) : 1;
				var indicator_steps = 12;
				percent_booked = indicator_steps * percent_booked;

				var width_booked = Math.ceil( percent_booked );
				if( (width_booked == indicator_steps) && (this_booked < this_capacity) ){
					width_booked = width_booked - 1;
				}
				var width_free = indicator_steps - width_booked;

				if( width_booked ){
					occupy_indicator.add(
						jQuery('<div>')
							.append( '&nbsp;' )
							.addClass('hc-bg-gray')
						, width_booked, width_booked
						);
				}
				if( width_free ){
					occupy_indicator.add(
						jQuery('<div>')
							.append( '&nbsp;' )
						, width_free, width_free
						);
				}

				$occupy_indicator
					.addClass('hc-border')
					.addClass('hc-border-gray')
					// .addClass('hc-rounded')
					.append( occupy_indicator.render() )
					;
			}
			else {
				$occupy_indicator
					.append( '&nbsp;' )
					;
			}

			$occupy_indicator
				.addClass('hc-mt1')
				;

			$link
				.append( $occupy_indicator )
				;

			params.cell
				// .html( date_label )
				.html( $link )
				.addClass('hc-mx1')
				;
		});

		var highlight_cell = function( $cell, on )
		{
			var hl_classes = ['hc-bg-gray', 'hc-white'];
			var hl_classes = ['hc-bg-silver'];
			var $as = $cell.find('a');
			for( var jj = 0; jj < hl_classes.length; jj++ ){
				if( on ){
					$as.addClass( hl_classes[jj] );
				}
				else {
					$as.removeClass( hl_classes[jj] );
				}
			}
		}

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

						.addClass('hc-btn')
						.addClass('hc-block')
						.addClass('hc-theme-btn-submit')
						.addClass('page-title-action')
						.addClass('hc-theme-btn-secondary')
						.addClass('hc-align-center')

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
			$el_cart.empty();

			$el_cart
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
