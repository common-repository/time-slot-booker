<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Front_View_TSB_HC_MVC
{
	public function render( $calendar, $date_from )
	{
	// add javascript
		$enqueuer = $this->app->make('/app/enqueuer');

		if( defined('NTS_DEVELOPMENT2') ){
			$js = array('loader.js', 'slots.js', 'cart.js', 'run.js');
		}
		else {
			$js = array('front.js');
		}

		for( $ii = 0; $ii < count($js); $ii++ ){
			$enqueuer
				->register_script( 'hc-tsb-front' . ($ii + 1), 'modules/front/assets/js/' . $js[$ii] )
				->enqueue_script( 'hc-tsb-front' . ($ii + 1) )
				;
		}

		$calendar_id = $calendar['id'];

		$manager = $this->app->make('/schedule/manager')
			->set_calendar( $calendar_id )
			;
		$settings = $manager->get_settings();

		$data_link = $this->app->make('/http/uri')
			->mode('api')
			->url('/front/data/' . $calendar_id . '/' . $date_from )
			;

		$order_confirm_link = $this->app->make('/http/uri')
			->url('/front/new/' . $calendar_id, array('slots' => '_SLOTS_'))
			;

		$out = $this->app->make('/html/list')
			->set_gutter(2)
			;

		$js_out = $this->app->make('/html/element')
			->tag('div')
			->add_attr('class', 'hcj-tsb-front')
			->add_attr('style', 'min-height: 4em;')
			;

	// configure the js view
		$t = $this->app->make('/app/lib')->time();
		$weekdays = $t->getWeekdays();
		$lang = array(
			'Create New Order'	=> HCM::__('Book Now'),
			'Not Available'	=> HCM::__('Not Available'),
			'Remove'		=> HCM::__('Remove'),
			'More Slots'	=> HCM::__('More Slots'),
			'Continue'		=> HCM::__('Continue'),
			'weekdays'		=> $weekdays,
			);
		$conf = array(
			'auto-start'	=> 1,
			'maxslots'		=> $settings['max_slots']
			);

		$maxqty = 1;

		$js_out
			->add_attr('data-nextlink',		$data_link)
			->add_attr('data-confirmlink',	$order_confirm_link)
			->add_attr('data-lang',			htmlentities(json_encode($lang)))
			->add_attr('data-conf',			htmlentities(json_encode($conf)))
			;

		$out
			->add( $js_out )
			;

		return $out;
	}
}