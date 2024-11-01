<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Schedule_Controller_Data_TSB_HC_MVC
{
	public function execute( $calendar_id, $date )
	{
		$t = $this->app->make('/app/lib')->time();
		$return = array();

		$date_from = $t->setDateDb($date)->setStartMonth()->formatDateDb();
		$date_to = $t->setDateDb($date)->setEndMonth()->formatDateDb();

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

		$manager = $this->app->make('/schedule/manager')
			->set_calendar( $calendar_id )
			;

		$return['slots'] = $manager
			->get_slots( $date_from, $date_to )
			;
		$return['bookings'] = $manager
			->get_bookings( $date_from, $date_to )
			;
	// set links
		for( $ii = 0; $ii < count($return['bookings']); $ii++ ){
			$link = NULL;
			if( isset($return['bookings'][$ii]['order']['id']) ){
				$link = $this->app->make('/http/uri')
					->mode('web')
					->url('/orders/' . $return['bookings'][$ii]['order']['id'] )
					;
			}

			$return['bookings'][$ii]['link'] = $link;
		}

	// next/prev links
		$t2 = $this->app->make('/app/lib')->time();

		$next_date_from = $t2->setDateDb($date)->modify('+1 month')->setStartMonth()->formatDateDb();
		$next_date_to = $t2->setEndMonth()->formatDateDb();

		$prev_date_from = $t2->setDateDb( $date )->modify('-1 month')->setStartMonth()->formatDateDb();
		$prev_date_to = $t2->setEndMonth()->formatDateDb();

		$next_link = $this->app->make('/http/uri')
			->mode('api')
			->url('/schedule/data/' . $calendar_id . '/' . $next_date_from )
			;

		$prev_link = $this->app->make('/http/uri')
			->mode('api')
			->url('/schedule/data/' . $calendar_id . '/' . $prev_date_from )
			;

		$return['nextlink'] = $next_link;
		$return['prevlink'] = $prev_link;

		$t->setDateDb( $date_from );
		$range_label = $t->getMonthName() . ' ' . $t->getYear();
		$return['range_label'] = $range_label;

		$return = json_encode( $return );
// return $return;
		echo $return;

if( ! $this->app->make('/input/lib')->is_ajax_request() ){
	echo $this->app->profiler()->run();
}

		exit;
		return $return;
	}
}