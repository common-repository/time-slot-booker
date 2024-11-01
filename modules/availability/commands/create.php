<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Availability_Commands_Create_TSB_HC_MVC
{
	public function execute( $args = array() )
	{
		$command = $this->app->make('/commands/create')
			->set_table('availability')
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