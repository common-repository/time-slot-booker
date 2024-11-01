jQuery(document).ready( function()
{
	var inputs = jQuery( '.hcj-datetimerange-input' );
	inputs.each( function(index){
		var $this = jQuery(this);

		var $selector_input = $this.find('.hcj-selector').find(':checkbox');

		$selector_input.on('change', function(e){
			var is_all_day = jQuery(this)[0].checked;
			if( is_all_day ){
				$this.find('.hcj-all-day').show();
				$this.find('.hcj-partial-day').hide();
			}
			else {
				$this.find('.hcj-all-day').hide();
				$this.find('.hcj-partial-day').show();
			}
		});

		$selector_input.trigger('change');
	});
});
