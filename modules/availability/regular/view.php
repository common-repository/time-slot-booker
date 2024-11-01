<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Availability_Regular_View_TSB_HC_MVC
{
	public function render( $values, $calendar)
	{
		$calendar_id = $calendar['id'];
		$inputs = $this->app->make('/availability/regular/form')
			->inputs()
			;

		$helper = $this->app->make('/form/helper');
		$inputs_view = $helper->prepare_render( $inputs, $values );

		$out_inputs = $this->app->make('/html/grid')
			->set_gutter(1)
			;

		$t = $this->app->make('/app/lib')->time();
		$wkds = $t->getWeekdays();

		$ii = 0;
		foreach( $inputs_view as $input_name => $input ){
			$out_inputs
				->add(
					$this->app->make('/html/list')
						->set_gutter(1)
						->add( $wkds[$ii] )
						->add( $input )
					, '1-7'
					)
				
				;
			$ii++;
		}

		$out_buttons = $this->app->make('/html/list-inline')
			->set_gutter(2)
			;
		$out_buttons->add(
			$this->app->make('/html/element')->tag('input')
				->add_attr('type', 'submit')
				->add_attr('title', HCM::__('Save') )
				->add_attr('value', HCM::__('Save') )
				->add_attr('class', 'hc-theme-btn-submit')
				->add_attr('class', 'hc-theme-btn-primary')
				->add_attr('class', 'hc-xs-block')
			);

		$link = $this->app->make('/http/uri')
			->url( '/availability/regular/update/' . $calendar_id )
			;

		$out = $helper
			->render( array('action' => $link) )
			->add( $out_inputs )
			->add( $out_buttons )
			;

		return $out;
	}
}