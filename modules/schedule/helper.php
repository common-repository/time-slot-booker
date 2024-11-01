<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Schedule_Helper_TSB_HC_MVC
{
	public function get_dates( $date_from, $date_to )
	{
		$return_dates = array();
		$return_dates_details = array();

		$t = $this->app->make('/app/lib')->time();
		$t->setDateDb( $date_from );

		$rex_date = $date_from;
		while( $rex_date <= $date_to ){
			$day_start = $t->getStartDay();
			$weekday = $t->getWeekday();
			$weekday_formatted = $t->formatWeekdayShort();
			$day_formatted = $t->formatDate();
			$day_short = $t->getDay();

			$date_key = (int) $rex_date;
			$return_dates[] = $date_key;

			$return_dates_details[ $date_key ] = array(
				// 'date'		=> $date_key,
				'formatted'	=> $day_formatted,
				'start'		=> (int) $day_start,
				'short'		=> (int) $day_short,
				'weekday'	=> (int) $weekday,
				'weekday_formatted'	=> $weekday_formatted,
				);

			$t->modify('+1 day');
			$rex_date = $t->formatDateDb();
		}

		$return = array( $return_dates, $return_dates_details );
		return $return;
	}
}