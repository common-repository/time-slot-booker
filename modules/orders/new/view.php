<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_New_View_TSB_HC_MVC
{
	public function render( $values, $calendar )
	{
		$inputs = $this->app->make('/orders/new/form')
			->inputs( $calendar )
			;
		$helper = $this->app->make('/form/helper');

		$inputs_view = $helper->prepare_render( $inputs, $values );

		$out_inputs = $helper->render_inputs( 
			$inputs_view,
			array(
				array(
					array('bookings')
					),
				array(
					array('customer_*'),
					array('calendar', 'ref', 'status'),
					)
				)
			);

		$out_buttons = $this->app->make('/html/list-inline')
			->add(
				$this->app->make('/html/element')->tag('input')
					->add_attr('type', 'submit')
					->add_attr('title', HCM::__('Create New Order') )
					->add_attr('value', HCM::__('Create New Order') )
					->add_attr('class', 'hc-theme-btn-submit')
					->add_attr('class', 'hc-theme-btn-primary')
					->add_attr('class', 'hc-xs-block')
				)
			;

		$link = $this->app->make('/http/uri')
			->url('/orders/add/' . $calendar['id'])
			;
		$out = $helper
			->render( array('action' => $link) )
			->add( $out_inputs )
			->add( $out_buttons )
			;

		return $out;
	}
}