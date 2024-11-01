<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Front_New_View_TSB_HC_MVC
{
	public function render( $values, $calendar )
	{
		$inputs = $this->app->make('/front/new/form')
			->inputs( $calendar )
			;
		$helper = $this->app->make('/form/helper');

		$inputs_view = $helper->prepare_render( $inputs, $values );
		$out_inputs = $helper->render_inputs( 
			$inputs_view
			);

		$out_buttons = $this->app->make('/html/list-inline')
			->add(
				$this->app->make('/html/element')->tag('input')
					->add_attr('type', 'submit')
					->add_attr('title', HCM::__('Confirm Booking') )
					->add_attr('value', HCM::__('Confirm Booking') )
					// ->add_attr('class', 'hc-theme-btn-submit')
					->add_attr('class', 'hc-theme-btn-primary')
					->add_attr('class', 'hc-xs-block')
				)
			;

		$start_over = $this->app->make('/html/ahref')
			->to('/front/' . $calendar['id'])
			->add( '&lt;' . ' ' . HCM::__('Start Over') )
			;
		$start_over = $this->app->make('/html/element')->tag('div')
			->add( $start_over )
			->add_attr('class', 'hc-p2')
			->add_attr('class', 'hc-fs4')
			;
		$inputs_view['back'] = $start_over;

		$out_inputs = $this->app->make('/html/list')
			->set_gutter(2)
			->add( $start_over )
			->add( $out_inputs )
			;

		$link = $this->app->make('/http/uri')
			->url('/front/add/' . $calendar['id'])
			;
		$out = $helper
			->render( array('action' => $link) )
			->add( 
				$this->app->make('/html/list')
					->set_gutter(2)
					->add( $out_inputs )
					->add( $out_buttons )
				)
			;

		return $out;
	}
}