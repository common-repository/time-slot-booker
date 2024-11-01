<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Input_Bookings_TSB_HC_MVC implements Form_Input_Interface_HC_MVC
{
	protected $input = NULL;
	protected $calendar = NULL;
	protected $show_removed = FALSE;

	public function set_calendar( $calendar )
	{
		$this->calendar = $calendar;
		return $this;
	}

	public function set_show_removed( $show = TRUE )
	{
		$this->show_removed = $show;
		return $this;
	}

	public function to_string( $array )
	{
		$return = NULL;

		if( $array ){
			$return = array();
			foreach( $array as $slot ){
				$return[] = $slot['starts_at'] . '-' . $slot['ends_at'];
			}
			$return = join('|', $return);
		}

		return $return;
	}

	public function from_string( $value )
	{
		if( ! is_array($value) ){
			$value = trim( $value );
			$value = explode('|', $value);
		}

		$return = array();
		for( $ii = 0; $ii < count($value); $ii++ ){
			$this_slot = explode('-', $value[$ii]);
			if( count($this_slot) <= 1 ){
				continue;
			}

			$return[$ii] = array(
				'starts_at'	=> $this_slot[0],
				'ends_at'	=> $this_slot[1],
				);
		}

		return $return;
	}

	public function grab( $name, $post )
	{
		$src_value = $this->app->make('/form/hidden')
			->grab($name, $post)
			;
		$return = $this->from_string( $src_value );
		return $return;
	}

	public function render( $name, $value = NULL )
	{
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

	// convert array to string
		$input_value = $this->to_string( $value );

		$hidden = $this->app->make('/form/hidden')
			->render($name, $input_value)
			;

		$calendar_id = isset($this->calendar['id']) ? $this->calendar['id'] : NULL;

		$out = $this->app->make('/html/element')->tag('div')
			->add_attr('class', 'hcj-tsb-schedule')
			->add( $hidden )
			;

		$t = $this->app->make('/app/lib')->time();

		$cart = array();
		if( $value ){
			$sm = $this->app->make('/schedule/manager');

			foreach( array_keys($value) as $ii ){
				$value[$ii]['starts_at'] = $value[$ii]['starts_at'];
				$value[$ii]['ends_at'] = $value[$ii]['ends_at'];

				$value[$ii] = $sm->prepare_slot( $value[$ii] );
				$cart[] = $value[$ii];
			}
		}
		else {
			$cart = NULL;
		}

		if( $cart ){
			$cart = htmlentities(json_encode($cart));
		}

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

		$data_show_removed = $this->show_removed ? 1 : 0;

		if( $value ){
			foreach( array_keys($value) as $ii ){
				$t->setDateTimeDb2( $value[$ii]['starts_at'] );
				$date_from = $t->setStartMonth()->formatDateDb();
				break;
			}
		}
		else {
			$date_from = $t->setNow()->setStartMonth()->formatDateDb();
		}

		$data_link = $this->app->make('/http/uri')
			->mode('api')
			->url('/schedule/data/' . $calendar_id . '/' . $date_from )
			;

		$order_edit_link = $this->app->make('/http/uri')
			->url('/orders/_ID_/edit')
			;

		$out
			->add_attr('data-startcart', $cart)
			->add_attr('data-lang', $lang)
			->add_attr('data-showremoved', $data_show_removed)
			->add_attr('data-startwith', 'cart')

			->add_attr('data-nextlink',		$data_link)
			->add_attr('data-sethidden',	1)
			->add_attr('data-orderlink',	$order_edit_link)
			// ->add_attr('data-confirmlink',	$order_confirm_link)
			;

		return $out;
	}
}