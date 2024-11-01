<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class CalendarsOne_Commands_Read_TSB_HC_MVC
{
	public function execute( $args = array() )
	{
		$command = $this->app->make('/commands/read')
			->set_table('calendars')
			;
		$return = $command->execute( $args );

		$return = $this->app
			->after( $this, $return )
			;

		unset($return['title']);
		return $return;
	}
}