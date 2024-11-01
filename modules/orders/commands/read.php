<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Commands_Read_TSB_HC_MVC
{
	public function execute( $args = array() )
	{
		$command = $this->app->make('/commands/read')
			->set_table('orders')
			->set_search_in( array('customer_name', 'customer_email') );
			;
		$return = $command
			->execute( $args )
			;
		$return = $this->app
			->after( $this, $return )
			;
		return $return;
	}
}