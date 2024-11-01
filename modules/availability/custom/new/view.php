<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Availability_Custom_New_View_TSB_HC_MVC
{
	public function render( $calendar )
	{
		$calendar_id = $calendar['id'];

		$inputs = $this->app->make('/availability/custom/form')
			->inputs($calendar)
			;
		$helper = $this->app->make('/form/helper');

		$inputs_view = $helper->prepare_render( $inputs );
		$out_inputs = $helper->render_inputs( 
			$inputs_view,
			array(
				array(
					array('slots'),
					array('applied_on_date'),
					)
				)
			);

		$out_buttons = $this->app->make('/html/list-inline')
			->add(
				$this->app->make('/html/element')->tag('input')
					->add_attr('type', 'submit')
					->add_attr('title', HCM::__('Save') )
					->add_attr('value', HCM::__('Save') )
					->add_attr('class', 'hc-theme-btn-submit')
					->add_attr('class', 'hc-theme-btn-primary')
					->add_attr('class', 'hc-xs-block')
				)
			;

		$link = $this->app->make('/http/uri')
			->url('/availability/custom/add/' . $calendar_id)
			;

		$out = $helper
			->render( array('action' => $link) )
			->add( 
				$this->app->make('/html/list')->set_gutter(2)
					->add( $out_inputs )
					->add( $out_buttons )
				)
			;

		return $out;
	}
}