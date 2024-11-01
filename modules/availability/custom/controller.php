<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Availability_Custom_Controller_TSB_HC_MVC
{
	public function execute( $calendar_id )
	{
		$args = array();
		$args[] = $calendar_id;
		$calendar =  $this->app->make('/calendars/commands/read')
			->execute( $args )
			;

		$args = array();
		$args[] = array('applied_on_date', '<>', NULL);
		$args[] = array('calendar_id', '=', $calendar_id);
		$args[] = array('sort', 'applied_on_date', 'desc');
		$entries = $this->app->make('/availability/commands/read')
			->execute( $args )
			;

		$view = $this->app->make('/availability/custom/view')
			->render( $entries, $calendar )
			;

		$view = $this->app->make('/availability/custom/view/layout')
			->render( $view, $calendar )
			;

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