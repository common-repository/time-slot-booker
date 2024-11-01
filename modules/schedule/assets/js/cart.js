hcapp.tsb_schedule_Cart = function()
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
			out.add( self.render_slot(this_slot), 3, 6);
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
