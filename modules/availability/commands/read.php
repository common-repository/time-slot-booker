<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Availability_Commands_Read_TSB_HC_MVC
{
	public function prepare( $return = array() )
	{
		if( ! is_array($return) ){
			$return = array( $return );
		}

		$return[] = array('sort', 'slot_start', 'asc');

		$return = $this->app
			->after( array($this, __FUNCTION__), $return )
			;
		return $return;
	}

	public function execute( $args = array() )
	{
		$args = $this->prepare( $args );

		$command = $this->app->make('/commands/read')
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