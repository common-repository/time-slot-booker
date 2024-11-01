<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Availability_Custom_Delete_Controller_TSB_HC_MVC
{
	public function execute( $calendar_id, $date )
	{
		$args = array();
		$args[] = array('applied_on_date', '=', $date);
		$args[] = array('calendar_id', '=', $calendar_id);
		$entries = $this->app->make('/availability/commands/read')
			->execute( $args )
			;

		$command = $this->app->make('/availability/commands/delete');
		foreach( $entries as $e ){
			$command->execute( $e['id'] );
		}

	// OK
		return $this->app->make('/http/view/response')
			->set_redirect('-referrer-') 
			;
	}
}