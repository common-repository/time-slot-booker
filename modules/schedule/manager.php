<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Schedule_Manager_TSB_HC_MVC
{
	protected $calendar_id = NULL;

	public function set_calendar( $calendar_id )
	{
		$this->calendar_id = $calendar_id;
		return $this;
	}

	public function get_setting( $name )
	{
		$settings = $this->get_settings();
		$return = array_key_exists($name, $settings) ? $settings[$name] : NULL;
		return $return;
	}

	public function get_settings()
	{
		if( ! $this->calendar_id ){
			echo 'CALENDAR ID REQUIRED FOR SCHEDULE MANAGER!';
			exit;
		}

		$inputs = $this->app->make('/settings/edit/form')
			->inputs()
			;

		$return = array();
		$app_settings = $this->app->make('/app/settings');

		foreach( array_keys($inputs) as $k ){
			$settings_k2 = 'settings:' . $k . ':' . $this->calendar_id;
			$settings_k1 = 'settings:' . $k;

			$this_v = $app_settings->get( $settings_k2 );
			if( ! strlen($this_v) ){
				$this_v = $app_settings->get( $settings_k1 );
			}

			$return[ $k ] = $this_v;
		}

		return $return;
	}

	public function get_bookings( $date_from, $date_to )
	{
		$return = array();

		if( ! $this->calendar_id ){
			echo 'CALENDAR ID REQUIRED FOR SCHEDULE MANAGER!';
			exit;
		}

		$args = $this->prepare_bookings_args( $date_from, $date_to );
		$args[] = array('calendar_id', '=', $this->calendar_id);

		$results = $this->app->make('/bookings/commands/read')
			->execute( $args )
			;

		$return = $this->finalize_bookings( $results );
		return $return;
	}

	public function get_availability( $date_from, $date_to )
	{
		if( ! $this->calendar_id ){
			echo 'CALENDAR ID REQUIRED FOR SCHEDULE MANAGER!';
			exit;
		}

	// regular
		$args = array();
		$args[] = array( 'applied_on_weekday', '<>', NULL );
		$args[] = array('calendar_id', '=', $this->calendar_id);
		$entries = $this->app->make('/availability/commands/read')
			->execute( $args )
			;

	// custom
		$args = array();
		$args[] = array( 'applied_on_date', '<>', NULL );
		$args[] = array('calendar_id', '=', $this->calendar_id);
		$custom_entries = $this->app->make('/availability/commands/read')
			->execute( $args )
			;

		if( $custom_entries ){
			$entries = array_merge( $entries, $custom_entries );
		}

		$return = $this->finalize_availability( $entries, $date_from, $date_to );
		return $return;
	}

	public function prepare_bookings_args( $date_from, $date_to )
	{
		$date_from2 = $date_from . '0000';
		$date_to2 = $date_to . '2400';

		$args = array();
		$args[] = array('starts_at', '<', $date_to2);
		$args[] = array('ends_at', '>', $date_from2);
		$args[] = array('with', 'order', 'flat');

		return $args;
	}

	public function finalize_bookings( $results = array() )
	{
		$return = array();

		$order_ids = array();
		reset( $results );
		foreach( $results as $r ){
			$order_ids[ $r['order'] ] = (int) $r['order'];
		}

	// load orders
		$orders = array();
		if( $order_ids ){
			$command2 = $this->app->make('/orders/commands/read');
			$args = array();
			$args[] = array('id', 'IN', $order_ids);
			$orders = $command2
				->execute( $args )
				;
		}

		$p = $this->app->make('/orders/presenter');
		reset( $results );
		foreach( $results as $r ){
			$this_order_id = $r['order'];

			$r['order'] = array();
			if( isset($orders[$this_order_id]) ){
				$r['order'] = $orders[ $this_order_id ];
				$r['order']['customer'] = $p->present_customer_title( $orders[$this_order_id] );
			}

			$r = $this->prepare_slot( $r );
			$return[] = $r;
		}

		return $return;
	}

	public function finalize_slots( $return = array(), $bookings = array() )
	{
		$count_all_slots = count($return);
		for( $ii = 0; $ii < $count_all_slots; $ii++ ){
			$this_slot_start = $return[$ii]['starts_at'];
			$this_slot_end = $return[$ii]['ends_at'];

			$return[$ii]['booked'] = 0;
			$return[$ii]['capacity'] = 1;

			foreach( $bookings as $this_b ){
				$this_b_start = $this_b['starts_at'];
				$this_b_end = $this_b['ends_at'];

				if( ($this_slot_end <= $this_b_start) OR ($this_b_end <= $this_slot_start) ){
					continue;
				}

				if( isset($this_b['order']['status']) && ($this_b['order']['status'] == 'cancelled') ){
					continue;
				}

				$return[$ii]['booked'] += 1;
			}
		}

		return $return;
	}

	public function get_slots( $date_from, $date_to )
	{
		$return = $this
			->get_availability( $date_from, $date_to )
			;
		$bookings = $this
			->get_bookings( $date_from, $date_to )
			;

		$return = $this->finalize_slots( $return, $bookings );
		return $return;
	}

	public function finalize_availability( array $entries, $date_from, $date_to )
	{
		$return = array();

		$t = $this->app->make('/app/lib')->time();

		$helper = $this->app->make('/availability/helper');
		$entries = $helper->group( $entries );

		$regular_availability = array();
		$custom_availability = array();

		reset( $entries );
		foreach( $entries as $applied_on => $slots  ){
			if( $applied_on > 10 ){
				$custom_availability[ $applied_on ] = $slots;
			}
			else {
				$regular_availability[ $applied_on ] = $slots;
			}
		}

		$t->setDateDb( $date_from );
		$rex_date = $date_from;
		while( $rex_date <= $date_to ){
			$this_slots = array();

			$day_start = $t->getStartDay();
			$weekday = $t->getWeekday();

			$this_availability = array();
			if( isset($custom_availability[$rex_date]) ){
				$this_availability = $custom_availability[$rex_date];
			}
			elseif( isset($regular_availability[$weekday]) ){
				$this_availability = $regular_availability[$weekday];
			}
// _print_r( $this_availability );

			foreach( $this_availability as $a ){
				$t->setStartDay();
				$t->modify( '+' . $a['slot_start'] . ' seconds');

				for( $ts_short = $a['slot_start']; $ts_short < $a['slot_end']; $ts_short += $a['slot_interval'] ){
					$starts_at = $t->formatDateTimeDb2();
					$t->modify( '+' . $a['slot_interval'] . ' seconds');
					$ends_at = $t->formatDateTimeDb2();

					$this_slot = array( 
						'starts_at'			=> $starts_at,
						'ends_at'			=> $ends_at,
						);

					$this_slot = $this->prepare_slot( $this_slot );
					$return[] = $this_slot;
				}
			}

			$t->modify('+1 day');
			$rex_date = $t->formatDateDb();
		}

		return $return;
	}

	public function prepare_slot( $slot )
	{
		$t = $this->app->make('/app/lib')->time();

		$t->setDateTimeDb2( $slot['starts_at'] );
		$date = $t->formatDateDb();
		$formatted_date = $t->formatDateFullShort();
		$formatted_start = $t->formatTime();

		$t->setDateTimeDb2( $slot['ends_at'] );
		$formatted_end = $t->formatTime();

		$slot['formatted_date'] = $formatted_date;
		$slot['formatted_start'] = $formatted_start;
		$slot['formatted_end'] = $formatted_end;
		$slot['date'] = $date;

		return $slot;
	}
}