<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Notify_Email_Manager_TSB_HC_MVC
{
	public function options()
	{
		$return = array(
			'order-pending-customer'	=>
				HCM::__('Booking Pending') . ' &gt; ' . HCM::__('Customer')
				,
			'order-confirmed-customer'	=>
				HCM::__('Booking Confirmed') . ' &gt; ' . HCM::__('Customer')
				,
			'order-cancelled-customer'	=>
				HCM::__('Booking Cancelled') . ' &gt; ' . HCM::__('Customer')
				,
			'order-pending-admin'	=>
				HCM::__('Booking Pending') . ' &gt; ' . HCM::__('Admin')
				,
			'order-confirmed-admin'	=>
				HCM::__('Booking Confirmed') . ' &gt; ' . HCM::__('Admin')
				,
			'order-cancelled-admin'	=>
				HCM::__('Booking Cancelled') . ' &gt; ' . HCM::__('Customer')
				,
			);

		$return = $this->app
			->after( $this, $return )
			;

		return $return;
	}

	public function tags( $key )
	{
		$return = array();

		switch( $key ){
			default:
				$return = array(
					'bookings'	=> array( $this, '_tag_bookings' ),
					'customer'	=> array( $this, '_tag_customer' ),
					'refno'		=> array( $this, '_tag_refno' ),
					);
				break;
		}

		$return = $this->app
			->after( array($this, __FUNCTION__), $return, $key )
			;

		return $return;
	}

	public function message( $key, $order = NULL )
	{
		$return = NULL;

		$calendar_id = NULL;
		if( $order && isset($order['calendar']) ){
			$calendar_id = isset($order['calendar']['id']) ? $order['calendar']['id'] : $order['calendar'];
		} 

		$app_settings = $this->app->make('/app/settings');

	// active
		$active = 1;
		$try_keys = array();
		if( $calendar_id ){
			$try_keys[] = 'notify-email:' . $key . ':active' . ':' . $calendar_id;
		}
		$try_keys[] = 'notify-email:' . $key . ':active';

		foreach( $try_keys as $try_k ){
			$this_v = $app_settings->get( $try_k );
			if( strlen($this_v) ){
				$active = $this_v;
				break;
			}
		}
		if( ! $active ){
			return $return;
		}

		$subject = NULL;
		$body = NULL;

	// subject
		$try_keys = array();
		if( $calendar_id ){
			$try_keys[] = 'notify-email:' . $key . ':subject' . ':' . $calendar_id;
		}
		$try_keys[] = 'notify-email:' . $key . ':subject';

		foreach( $try_keys as $try_k ){
			$this_v = $app_settings->get( $try_k );
			if( strlen($this_v) ){
				$subject = $this_v;
				break;
			}
		}

	// body
		$try_keys = array();
		if( $calendar_id ){
			$try_keys[] = 'notify-email:' . $key . ':body' . ':' . $calendar_id;
		}
		$try_keys[] = 'notify-email:' . $key . ':body';

		foreach( $try_keys as $try_k ){
			$this_v = $app_settings->get( $try_k );
			if( strlen($this_v) ){
				$body = $this_v;
				break;
			}
		}

	// now parse
		$tags = $this->tags( $key );

		reset( $tags );
		foreach( $tags as $k => $callable ){
			$replace_from = '{' . strtoupper($k) . '}';
			$replace_to = call_user_func( $callable, $order );
			$subject = str_replace( $replace_from, $replace_to, $subject ); 
			$body = str_replace( $replace_from, $replace_to, $body ); 
		}

		$return = array( $subject, $body );

		$return = $this->app
			->after( array($this, __FUNCTION__), $return, $key, $order )
			;

		return $return;
	}

	protected function _tag_bookings( $order )
	{
		$bookings = isset($order['bookings']) ? $order['bookings'] : array();

		$p = $this->app->make('/bookings/presenter');

		$return = array();
		foreach( $bookings as $booking ){
			$this_booking_view = $p->present_time( $booking );
			$return[] = $this_booking_view;
		}
		$return = join( "\n", $return );
		return $return;
	}

	protected function _tag_customer( $order )
	{
		$p = $this->app->make('/orders/presenter');
		$return = $p->present_customer_details( $order );
		$return = join( "\n", $return );
		return $return;
	}

	protected function _tag_refno( $order )
	{
		$p = $this->app->make('/orders/presenter');
		$return = $p->present_refno( $order );
		return $return;
	}
	
}