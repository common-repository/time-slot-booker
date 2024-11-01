<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_New_Controller_Add_TSB_HC_MVC
{
	public function execute( $calendar_id )
	{
		$post = $this->app->make('/input/lib')->post();

		$args = array();
		$args[] = $calendar_id;
		$calendar =  $this->app->make('/calendars/commands/read')
			->execute( $args )
			;

		$inputs = $this->app->make('/orders/new/form')
			->inputs( $calendar )
			;
		$helper = $this->app->make('/form/helper');

		list( $values, $errors ) = $helper->grab( $inputs, $post );

		if( $errors ){
			return $this->app->make('/http/view/response')
				->set_redirect('-referrer-') 
				;
		}

		$values['calendar'] = $calendar_id;

		$cm = $this->app->make('/commands/manager');

	// create bookings
		$booking_ids = array();
		$command1 = $this->app->make('/bookings/commands/create');

	// first validate
		$bookings = $values['bookings'];
		reset( $bookings );
		$errors = array();
		foreach( $bookings as $booking ){
			$booking['calendar_id'] = $calendar_id;
			$this_errors = $command1->validate( $booking );
			if( $this_errors !== TRUE ){
				foreach( $this_errors as $err ){
					$errors[] = $err;
				}
			}
		}

		if( $errors ){
			$session = $this->app->make('/session/lib');
			$session
				->set_flashdata('error', $errors)
				;
			return $this->app->make('/http/view/response')
				->set_redirect('-referrer-') 
				;
		}

	// now create
		reset( $bookings );
		foreach( $bookings as $booking ){
			$booking['calendar_id'] = $calendar_id;
			$command1->execute( $booking );
			$booking = $cm->results( $command1 );

			if( $booking['id'] ){
				$booking_ids[] = $booking['id'];
			}
		}
		$values['bookings'] = $booking_ids;

		$command2 = $this->app->make('/orders/commands/create');
		$command2->execute( $values );

		$errors = $cm->errors( $command2 );

		if( $errors ){
			$session = $this->app->make('/session/lib');
			$session
				->set_flashdata('error', $errors)
				;
			return $this->app->make('/http/view/response')
				->set_redirect('-referrer-') 
				;
		}

	// OK
		$redirect_to = $this->app->make('/http/uri')
			->url('/schedule/' . $calendar_id )
			;
		return $this->app->make('/http/view/response')
			->set_redirect($redirect_to) 
			;
	}
}