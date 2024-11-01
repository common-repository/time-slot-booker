<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Edit_View_TSB_HC_MVC
{
	public function render( $values )
	{
		$id = $values['id'];

		if( isset($values['bookings']) && $values['bookings'] ){
			foreach( array_keys($values['bookings']) as $si ){
				$values['bookings'][$si]['order'] = $id;
			}
		}

		$calendar = isset($values['calendar']) ? $values['calendar'] : NULL;
		$inputs = $this->app->make('/orders/edit/form')
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
			// array(
				// array(
					// array('calendar', 'ref', 'status'),
					// array('customer_*')
					// )
				// )
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

			->add(
				$this->app->make('/html/ahref')
					->to('/orders/' . $id . '/delete' )
					->add( HCM::__('Delete') )
					->add_attr('class', 'hcj2-confirm')

					->add_attr('class', 'hc-theme-btn-submit')
					->add_attr('class', 'hc-theme-btn-danger')
					->add_attr('class', 'hc-xs-block')
				)
			;

		$out = $this->app->make('/html/list')
			->set_gutter(2)
			;

		if( isset($values['calendar']['title']) ){
			$calendar_label = $values['calendar']['title'];
			$calendar_label = $this->app->make('/html/element')->tag('div')
				->add( $calendar_label )
				->add_attr('class', 'hc-fs4')
				->add_attr('class', 'hc-white')
				->add_attr('class', 'hc-bg-gray')
				->add_attr('class', 'hc-p2')
				->add_attr('class', 'hc-rounded')
				;

			$out
				->add( $calendar_label )
				;
		}

		$out
			->add( $out_inputs )
			->add( $out_buttons )
			;

		$link = $this->app->make('/http/uri')
			->url('/orders/' . $id . '/update')
			;
		$out = $helper
			->render( array('action' => $link) )
			->add( $out )
			;

		return $out;
	}
}