<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Availability_Commands_Update_TSB_HC_MVC
{
	public function execute( $id, $args = array() )
	{
		$command = $this->app->make('/commands/update')
			->set_table('availability')
			;
		$return = $command
			->execute( $id, $args )
			;
		$return = $this->app
			->after( $this, $return )
			;
		return $return;
	}
}