<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Bookings_Commands_Delete_TSB_HC_MVC
{
	public function execute( $id )
	{
		$command = $this->app->make('/commands/delete')
			->set_table('bookings')
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