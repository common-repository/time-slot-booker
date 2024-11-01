<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Bookings_Presenter_TSB_HC_MVC
{
	public function present_time( $slot )
	{
		$t = $this->app->make('/app/lib')->time();

		$t->setDateTimeDb2( $slot['starts_at'] );
		$ts_start = $t->getTimestamp();

		$t->setDateTimeDb2( $slot['ends_at'] );
		$ts_end = $t->getTimestamp();

		$formatted = $t->getFormatTimeRange( $ts_start, $ts_end, 'with_weekday' );

		$return = array();
		if( isset($formatted['date']) ){
			$return[] = is_array($formatted['date']) ? join(' - ', $formatted['date']) : $formatted['date'];
		}
		if( isset($formatted['time']) ){
			$return[] = is_array($formatted['time']) ? join(' - ', $formatted['time']) : $formatted['time'];
		}

		$return = join(' ', $return);
		return $return;
	}
}