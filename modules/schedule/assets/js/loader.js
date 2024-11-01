hcapp.tsb_schedule_Loader = function()
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
			.addClass('hc-fs5')
			.addClass('hc-border')
			.addClass('hc-btn')
			// .addClass('hc-border-gray')
			.append( '&gt;&gt;' )
			.on('click', self.route)
			;

		var $prevlink = jQuery('<a>', {
			href: '#prev',
			})
			.addClass('hc-fs5')
			.addClass('hc-border')
			.addClass('hc-btn')
			.addClass('hc-block')
			.addClass('hc-align-center')
			.addClass('hc-p2')
			// .addClass('hc-border-gray')
			.append( '&lt;&lt;' )
			.on('click', self.route)
			;

		var $range_label = jQuery('<div>')
			.addClass('hc-fs4')
			.addClass('hc-block')
			.addClass('hc-align-center')
			.addClass('hc-p2')
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

	this._find_booking_index = function( slot )
	{
		var index = -1;
	// find index
		for( var ii = 0; ii < self.data['bookings'].length; ii++ ){
			var test_slot = self.data['bookings'][ii];
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

		var this_slot = self.data['slots'][index];
		var new_booking = {
			starts_at:			this_slot['starts_at'],
			ends_at:			this_slot['ends_at'],
			date:				this_slot['date'],
			formatted_start:	this_slot['formatted_start'],
			formatted_end:		this_slot['formatted_end'],
			type:		'booking',
		}

		self.data['bookings'].push( new_booking );
		return this;
	}

	this.release_slot = function( slot, capacity )
	{
		var index = self._find_slot_index( slot );
		if( index < 0 ){
			return;
		}
		self.data['slots'][index]['booked'] -= capacity;

		var index = self._find_booking_index( slot );
		if( index < 0 ){
			return;
		}
		self.data['bookings'].splice( index, 1 );
		return this;
	}
};