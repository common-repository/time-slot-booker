<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Availability_Custom_New_Controller_TSB_HC_MVC
{
	public function execute( $calendar_id )
	{
		$args = array();
		$args[] = $calendar_id;
		$calendar =  $this->app->make('/calendars/commands/read')
			->execute( $args )
			;

		$view = $this->app->make('/availability/custom/new/view')
			->render( $calendar )
			;
		// $view = $this->app->make('/availability/custom/new/layout')
			// ->render($view)
			// ;

		$view = $this->app->make('/settings/view/layout')
			->render( $view, $calendar, 'availability/custom/' . $calendar_id )
			;

		$view = $this->app->make('/layout/view/body')
			->set_content($view)
			;
		return $this->app->make('/http/view/response')
			->set_view($view)
			;
	}
}