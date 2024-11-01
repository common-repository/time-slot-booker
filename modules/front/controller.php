<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Front_Controller_TSB_HC_MVC
{
	public function execute( $calendar_id )
	{
		$args = array();
		$args[] = $calendar_id;
		$calendar =  $this->app->make('/calendars/commands/read')
			->execute( $args )
			;

		$manager = $this->app->make('/schedule/manager')
			->set_calendar( $calendar_id )
			;
		$settings = $manager->get_settings();

		$t = $this->app->make('/app/lib')->time();
		$date = $t->setNow()->formatDateDb();

		$min_from_now = isset($settings['min_from_now']) ? $settings['min_from_now'] : '1 days';
		$max_from_now = isset($settings['max_from_now']) ? $settings['max_from_now'] : '3 months';

		if( $min_from_now ){
			$allowed_date_from = $t
				->setNow()
				->cute_modify( $min_from_now, '-' )
				->formatDateDb()
				;
			if( $allowed_date_from > $date ){
				$date = $allowed_date_from;
			}
		}

		$date_from = $t->setDateDb( $date )->setStartMonth()->formatDateDb();

		$view = $this->app->make('/front/view')
			->render( $calendar, $date_from )
			;

		$view = $this->app->make('/front/view/layout')
			->render( $view, $calendar )
			;
		$view = $this->app->make('/layout/view/body')
			->set_content($view)
			;
		return $this->app->make('/http/view/response')
			->set_view($view)
			;
	}
}