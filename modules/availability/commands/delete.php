<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Availability_Commands_Delete_TSB_HC_MVC
{
	public function execute( $id )
	{
		$command = $this->app->make('/commands/delete')
			->set_table('availability')
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