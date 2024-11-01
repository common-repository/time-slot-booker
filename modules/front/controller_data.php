<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Front_Controller_Data_TSB_HC_MVC
{
	public function execute( $calendar_id, $date )
	{
		$is_ajax = $this->app->make('/input/lib')->is_ajax_request();

		$manager = $this->app->make('/schedule/manager')
			->set_calendar( $calendar_id )
			;

		$t = $this->app->make('/app/lib')->time();

		$date_from = $t->setDateDb($date)->setStartMonth()->formatDateDb();
		$date_to = $t->setDateDb($date)->setEndMonth()->formatDateDb();

		$settings = $manager->get_settings();
		$min_from_now = isset($settings['min_from_now']) ? $settings['min_from_now'] : '1 days';
		$max_from_now = isset($settings['max_from_now']) ? $settings['max_from_now'] : '3 months';

	// check allowed dates
		$slots_date_from = $date_from;
		$slots_date_to = $date_to;

		if( $min_from_now ){
			$allowed_date_from = $t
				->setNow()
				->cute_modify( $min_from_now, '-' )
				->formatDateDb()
				;
			if( $allowed_date_from > $slots_date_from ){
				$slots_date_from = $allowed_date_from;
			}
		}

		if( $max_from_now ){
			$allowed_date_to = $t
				->setNow()
				->cute_modify( $max_from_now, '+' )
				->formatDateDb()
				;
			if( $allowed_date_to < $slots_date_to ){
				$slots_date_to = $allowed_date_to;
			}
		}

// if( ! $is_ajax ){
	// echo "ALLOWED FROM = '$allowed_date_from', ALLOWED TO = '$allowed_date_to'<br><br>";
// }
	// check date from
		$return = array();

		$helper = $this->app->make('/schedule/helper');

		list( $dates, $dates_details ) = $helper
			->get_dates( $date_from, $date_to )
			;

		$t->setDateDb( $date_from);
		$month_matrix = $t->getMonthMatrix( $date_to, TRUE );
		// _print_r( $month_matrix );

		$return['dates'] = $dates;
		$return['dates_details'] = $dates_details;
		$return['dates_matrix'] = $month_matrix;

		$return['slots'] = $manager
			->get_slots( $slots_date_from, $slots_date_to )
			;

	// next/prev links
		$t2 = $this->app->make('/app/lib')->time();

		$next_date_from = $t2->setDateDb($date)->modify('+1 month')->setStartMonth()->formatDateDb();
		$next_date_to = $t2->setEndMonth()->formatDateDb();

		$prev_date_from = $t2->setDateDb( $date )->modify('-1 month')->setStartMonth()->formatDateDb();
		$prev_date_to = $t2->setEndMonth()->formatDateDb();

		$next_link = $this->app->make('/http/uri')
			->mode('api')
			->url('/front/data/' . $calendar_id . '/' . $next_date_from )
			;
		if( $allowed_date_to && ($allowed_date_to < $next_date_from) ){
			$next_link = NULL;
		}

		$prev_link = $this->app->make('/http/uri')
			->mode('api')
			->url('/front/data/' . $calendar_id . '/' . $prev_date_from )
			;
		if( $allowed_date_from && ($allowed_date_from > $prev_date_to) ){
			$prev_link = NULL;
		}

		$return['nextlink'] = $next_link;
		$return['prevlink'] = $prev_link;

		$t->setDateDb( $date_from );
		$range_label = $t->getMonthName() . ' ' . $t->getYear();
		$return['range_label'] = $range_label;

		$return = json_encode( $return );
// return $return;
		echo $return;

if( ! $is_ajax ){
	echo $this->app->profiler()->run();
}

		exit;
		return $return;
	}
}