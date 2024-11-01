<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Notify_Admin_TSB_HC_MVC
{
	public function execute( $order, $event )
	{
		$helper = $this->app->make('/orders/helper');
		$admins = $helper->get_managers( $order );

		$this->app
			->after( $this, $order, $event, $admins )
			;
	}
}