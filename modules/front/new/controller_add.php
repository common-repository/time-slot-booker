<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Front_New_Controller_Add_TSB_HC_MVC
{
	public function execute( $calendar_id )
	{
		$post = $this->app->make('/input/lib')->post();

		$args = array();
		$args[] = $calendar_id;
		$calendar =  $this->app->make('/calendars/commands/read')
			->execute( $args )
			;

		$inputs = $this->app->make('/front/new/form')
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

	// first validate bookings
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

	// refno and status
		$order_helper = $this->app->make('/orders/helper');
		$values['ref'] = $order_helper->get_new_refno();

		$schedule_manager = $this->app->make('/schedule/manager')
			->set_calendar( $calendar_id )
			;
		$settings = $schedule_manager->get_settings();
		$values['status'] = $settings['start_status'];

	// bookings okay now validate the order itself without bookings so far
		$command2 = $this->app->make('/orders/commands/create');

		$validators = $command2->validators();
		unset( $validators['bookings'] );
	
		$errors = $this->app->make('/validate/helper')
			->validate( $values, $validators )
			;
		if( $errors ){
			$session = $this->app->make('/session/lib');
			$session
				->set_flashdata('error', $errors)
				;
			return $this->app->make('/http/view/response')
				->set_redirect('-referrer-') 
				;
		}

	// finally create
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
			->url('/front/' . $calendar_id )
			;
		return $this->app->make('/http/view/response')
			->set_redirect($redirect_to) 
			;
	}
}