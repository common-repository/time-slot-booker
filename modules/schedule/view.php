<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Schedule_View_TSB_HC_MVC
{
	public function render( $calendar, $date_from, $cart = array() )
	{
		$calendar_id = $calendar['id'];
		$data_link = $this->app->make('/http/uri')
			->mode('api')
			->url('/schedule/data/' . $calendar_id . '/' . $date_from )
			;
		$order_confirm_link = $this->app->make('/http/uri')
			->url('/orders/new/' . $calendar_id, array('slots' => '_SLOTS_'))
			;

	// add javascript
		$enqueuer = $this->app->make('/app/enqueuer');
		$js = array( 
			'loader.js',
			'slots.js',
			'cart.js',
			'run.js',
			);
		for( $ii = 0; $ii < count($js); $ii++ ){
			$enqueuer
				->register_script( 'hc-tsb-schedule' . ($ii + 1), 'modules/schedule/assets/js/' . $js[$ii] )
				->enqueue_script( 'hc-tsb-schedule' . ($ii + 1) )
				;
		}

		$out = $this->app->make('/html/list')
			->set_gutter(2)
			;

		$js_out = $this->app->make('/html/element')
			->tag('div')
			->add_attr('class', 'hcj-tsb-schedule')
			->add_attr('style', 'min-height: 4em;')
			;

	// configure the js view
		$order_edit_link = $this->app->make('/http/uri')
			->url('/orders/_ID_/edit')
			;

		$t = $this->app->make('/app/lib')->time();
		$weekdays = $t->getWeekdays();
		$lang = array(
			'Create New Order'	=> HCM::__('Book Now'),
			'Day Off'		=> HCM::__('Day Off'),
			'Remove'		=> HCM::__('Remove'),
			'Restore'		=> HCM::__('Restore'),
			'More Slots'	=> HCM::__('More Slots'),
			'weekdays'		=> $weekdays,
			);
		$lang = htmlentities(json_encode($lang));

		$js_out
			->add_attr('data-nextlink',		$data_link)
			->add_attr('data-orderlink',	$order_edit_link)
			->add_attr('data-confirmlink',	$order_confirm_link)
			->add_attr('data-lang',			$lang)
			;

		if( $cart ){
			$sm = $this->app->make('/schedule/manager');

			for( $ii = 0; $ii < count($cart); $ii++ ){
				$cart[$ii]['starts_at'] = $cart[$ii]['starts_at'];
				$cart[$ii]['ends_at'] = $cart[$ii]['ends_at'];

				$cart[$ii] = $sm->prepare_slot( $cart[$ii] );
			}
		}
		else {
			$cart = NULL;
		}

		if( $cart ){
			$cart = htmlentities(json_encode($cart));
		}

		$js_out
			->add_attr('data-startcart', $cart)
			;

	// check if we don't have availability or bookings for this calendar
		$args = array();
		$args[] = array('calendar_id', '=', $calendar_id);
		$args[] = array('limit', 1);
		$count_availability = $this->app->make('/availability/commands/read')
			->execute( $args )
			;

		if( ! $count_availability ){
			$alert_out = HCM::__('No availability configured for this calendar!');

			$alert_out = $this->app->make('/html/ahref')
				->to('/availability/regular/' . $calendar_id)
				->add( $alert_out )
				->add_attr('class', 'hc-darkred')
				;

			$alert_out = $this->app->make('/html/element')->tag('div')
				->add( $alert_out )
				->add_attr('class', 'hc-p2')
				->add_attr('class', 'hc-border')
				->add_attr('class', 'hc-darkred')
				->add_attr('class', 'hc-border-darkred')
				;

			$out
				->add( $alert_out )
				;
			
		}

		$out
			->add( $js_out )
			;

		return $out;
	}
}