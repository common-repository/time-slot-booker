<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class ModelSearch_View_HC_MVC
{
	public function render( $search = '', $ajax = FALSE )
	{
		$current_slug = $this->app->make('/http/uri')
			->slug()
			;

		$form = $this->app->make('/modelsearch/form');
		$helper = $this->app->make('/form/helper');

		$inputs_view = $helper->prepare_render( $form->inputs(), array('search' => $search) );
		$out_inputs = $helper->render_inputs( $inputs_view );

		$out_buttons = $this->app->make('/html/list-inline')
			->set_gutter(1)
			;

		$out_buttons->add(
			$this->app->make('/html/element')->tag('input')
				->add_attr('type', 'submit')
				->add_attr('title', HCM::__('Search') )
				->add_attr('value', HCM::__('Search') )
				->add_attr('class', 'hc-theme-btn-submit')
			);

		if( $search ){
			$out_buttons->add(
				$this->app->make('/html/ahref')
					->to('-', array('search' => NULL))
					->add( HCM::__('Reset') ) 
					->add_attr('class', 'hc-theme-btn-submit')
				);
		}

		$out = $this->app->make('/html/list-inline')
			->set_gutter(1)
			->add( $out_inputs )
			->add( $out_buttons )
			;

		$link = $this->app->make('/http/uri')
			->url('/modelsearch')
			;

		$out = $helper
			->render( array('action' => $link) )
			->add( $out )
			;
		return $out;
	}
}