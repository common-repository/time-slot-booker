<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Settings_View_TSB_HC_MVC
{
	public function render( $values, $post_to = '/settings/update' )
	{
		$inputs = $this->app->make('/settings/form')
			->inputs()
			;
		$helper = $this->app->make('/form/helper');

		$inputs_view = $helper->prepare_render( $inputs, $values );

		$out_inputs = $helper->render_inputs( $inputs_view );

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
			->url( $post_to )
			;

		$out = $helper
			->render( array('action' => $link) )
			->add( $out_inputs )
			->add( $out_buttons )
			;

		return $out;
	}
}