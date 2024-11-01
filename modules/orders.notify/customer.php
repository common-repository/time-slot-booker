<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Notify_Customer_TSB_HC_MVC
{
	public function execute( $order, $event )
	{
		$customer = array(
			'email'	=> $order['customer_email'],
			);

		$this->app
			->after( $this, $order, $event, $customer )
			;
	}
}