<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Schedule_Controller_TSB_HC_MVC
{
	public function execute( $calendar_id )
	{
		$args = array();
		$args[] = $calendar_id;
		$calendar =  $this->app->make('/calendars/commands/read')
			->execute( $args )
			;

		$t = $this->app->make('/app/lib')->time();
		$date = $t->setNow()->formatDateDb();
		$date_from = $t->setDateDb( $date )->setStartMonth()->formatDateDb();

		$cart = $this->app->make('/http/uri')
			->param('cart')
			;
		if( $cart ){
			$cart = $this->app->make('/orders/input-bookings')
				->from_string( $cart )
				;
		}
		else {
			$cart = array();
		}

		$view = $this->app->make('/schedule/view')
			->render( $calendar, $date_from, $cart )
			;

		$view = $this->app->make('/schedule/view/layout')
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