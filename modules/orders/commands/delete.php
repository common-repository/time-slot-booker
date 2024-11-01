<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Commands_Delete_TSB_HC_MVC
{
	public function execute( $id )
	{
	// delete bookings
		$args = array();
		$args[] = array('order', '=', $id);
		$bookings = $this->app->make('/bookings/commands/read')
			->execute( $args )
			;

		$command = $this->app->make('/bookings/commands/delete');
		foreach( $bookings as $e ){
			$command->execute( $e['id'] );
		}

		$command = $this->app->make('/commands/delete')
			->set_table('orders')
			;
		$return = $command
			->execute( $id )
			;

		$return = $this->app
			->after( $this, $return )
			;

		return $return;
	}
}