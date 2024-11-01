<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Bookings_Commands_Read_TSB_HC_MVC
{
	public function prepare( $return = array() )
	{
		if( ! is_array($return) ){
			$return = array( $return );
		}

		$return[] = array('sort', 'starts_at', 'asc');

		$return = $this->app
			->after( array($this, __FUNCTION__), $return )
			;
		return $return;
	}

	public function execute( $args = array() )
	{
		$args = $this->prepare( $args );

		$command = $this->app->make('/commands/read')
			->set_table('bookings')
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