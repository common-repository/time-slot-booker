<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Settings_Edit_View_TSB_HC_MVC
{
	public function render( $calendar, $values )
	{
		$calendar_id = $calendar['id'];
		$inputs = $this->app->make('/settings/edit/form')
			->inputs()
			;
		$helper = $this->app->make('/form/helper');

		$inputs_view = $helper->prepare_render( $inputs, $values );

	// show current values for min_from_now and max_from_now
		$t = $this->app->make('/app/lib')->time();

		$min_from_now = $t
			->setNow()
			->cute_modify( $values['min_from_now'], '-' )
			->formatFull()
			;
		$min_from_now = $this->app->make('/html/element')->tag('div')
			->add_attr('class', 'hc-p1')
			->add_attr('class', 'hc-bg-silver')
			->add_attr('class', 'hc-rounded')
			->add( $min_from_now . ' -&gt;' )
			;

		$max_from_now = $t
			->setNow()
			->cute_modify( $values['max_from_now'], '+' )
			->formatFull()
			;
		$max_from_now = $this->app->make('/html/element')->tag('div')
			->add_attr('class', 'hc-p1')
			->add_attr('class', 'hc-bg-silver')
			->add_attr('class', 'hc-rounded')
			->add( '-&gt; ' . $max_from_now )
			;

		$inputs_view['min_from_now'] = $this->app->make('/html/grid')
			->set_gutter(2)
			->add( $inputs_view['min_from_now'], 8 )
			->add( $min_from_now, 4 )
			;

		$inputs_view['max_from_now'] = $this->app->make('/html/grid')
			->set_gutter(2)
			->add( $inputs_view['max_from_now'], 8 )
			->add( $max_from_now, 4 )
			;

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
			->url( '/settings/update/' . $calendar_id )
			;

		$out = $helper
			->render( array('action' => $link) )
			->add( $out_inputs )
			->add( $out_buttons )
			;

		return $out;
	}
}