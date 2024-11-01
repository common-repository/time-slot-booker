<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_New_Controller_TSB_HC_MVC
{
	public function execute( $calendar_id )
	{
		$args = array();
		$args[] = $calendar_id;
		$calendar =  $this->app->make('/calendars/commands/read')
			->execute( $args )
			;

		$params = $this->app->make('/http/uri')
			->params()
			;

		$bookings = array();
		if( array_key_exists('slots', $params) ){
			$bookings = $params['slots'];
			if( ! is_array($bookings) ){
				$bookings = array( $bookings );
			}

			for( $ii = 0; $ii < count($bookings); $ii++ ){
				$this_booking = explode('-', $bookings[$ii]);
				$bookings[$ii] = array(
					'starts_at'		=> $this_booking[0],
					'ends_at'		=> $this_booking[1],
					'calendar_id'	=> $calendar_id, 
					);
			}
		}

		$values = array();
		$values['bookings'] = $bookings;

		$order_helper = $this->app->make('/orders/helper');
		$values['ref'] = $order_helper->get_new_refno();

		$view = $this->app->make('/orders/new/view')
			->render( $values, $calendar )
			;
		$view = $this->app->make('/orders/new/view/layout')
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