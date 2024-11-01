<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Notify_Email_Admin_TSB_HC_MVC
{
	public function execute( $order, $event, $admin )
	{
		$key = $event . '-admin';

		$manager = $this->app->make('/orders.notify-email/manager');
		$message = $manager
			->message( $key, $order )
			;

		if( ! $message ){
			return;
		}

		$subject = $message[0];
		$body = $message[1];

		$email = $this->app->make('/email');
		$email
			->send( $admin['email'], $subject, $body )
			;
	}
}