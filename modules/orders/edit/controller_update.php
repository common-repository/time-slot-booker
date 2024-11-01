<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Edit_Controller_Update_TSB_HC_MVC
{
	public function execute( $id )
	{
		$cm = $this->app->make('/commands/manager');
		$post = $this->app->make('/input/lib')->post();

		$cm = $this->app->make('/commands/manager');

		$args = array();
		$args[] = array('orders', '=', $id);
		$args[] = array('limit', 1);

		$calendar = $this->app->make('/calendars/commands/read')
			->execute( $args )
			;

		$calendar_id = $calendar['id'];

		$inputs = $this->app->make('/orders/edit/form')
			->inputs( $calendar )
			;
		$helper = $this->app->make('/form/helper');

		list( $values, $errors ) = $helper->grab( $inputs, $post );
// _print_r( $values );
// exit;

		if( $errors ){
			return $this->app->make('/http/view/response')
				->set_redirect('-referrer-') 
				;
		}

		$values['id'] = $id;

	// check bookings
		$args = array();
		$args[] = $id;
		$args[] = array('with', 'bookings');
		$model = $this->app->make('/orders/commands/read')
			->execute( $args )
			;

		$current_bookings = array();
		foreach( $model['bookings'] as $e ){
			$k = $e['starts_at'] . '-' . $e['ends_at'];
			$current_bookings[$k] = $e;
		}

		$new_bookings = array();
		foreach( $values['bookings'] as $e ){
			$k = $e['starts_at'] . '-' . $e['ends_at'];
			$new_bookings[$k] = $e;
		}

	// to delete
		$to_delete_keys = array_diff( array_keys($current_bookings), array_keys($new_bookings) );
		$to_delete = array();
		foreach( $to_delete_keys as $k ){
			$to_delete[] = $current_bookings[$k]['id'];
			unset( $current_bookings[$k] );
		}

		$new_bookings_value = array();
		foreach( $current_bookings as $k => $e ){
			$new_bookings_value[] = $e['id'];
		}

	// to add
		$to_add_keys = array_diff( array_keys($new_bookings), array_keys($current_bookings) );
		$to_add = array();
		foreach( $to_add_keys as $k ){
			$to_add[] = $new_bookings[$k];
		}

		if( $to_add ){
			$command1 = $this->app->make('/bookings/commands/create');
			foreach( $to_add as $booking ){
				// $booking['order'] = $id;
				$booking['calendar_id'] = $calendar_id;

				$command1->execute( $booking );
				$booking = $cm->results( $command1 );

				$new_id = $booking['id'];
				$new_bookings_value[] = $new_id; 
			}
		}

		$values['bookings'] = $new_bookings_value;

		$command2 = $this->app->make('/orders/commands/update');
		$command2->execute( $id, $values );

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

		if( $to_delete ){
			$command = $this->app->make('/bookings/commands/delete');
			foreach( $to_delete as $this_id ){
				$command->execute( $this_id );
			}
		}

	// OK
		$this->app->make('/session/lib')
			->set_flashdata('form_errors', array())
			->set_flashdata('form_values', array())
			;
		$redirect_to = $this->app->make('/http/uri')
			->url('/orders/' . $id . '/edit')
			;
		return $this->app->make('/http/view/response')
			->set_redirect($redirect_to) 
			;
	}
}