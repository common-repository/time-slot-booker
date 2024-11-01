<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class CalendarsOne_Select_Controller_TSB_HC_MVC
{
	public function execute( $to_template )
	{
		$args = array();
		$args[] = array('limit', 1);
		$calendar = $this->app->make('/calendars/commands/read')
			->execute( $args )
			;

		$calendar_id = $calendar['id'];
		$to = str_replace( '_ID_', $calendar_id, $to_template );
		return $this->app->make('/http/view/response')
			->set_redirect($to) 
			;
	}
}