<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Settings_Edit_Controller_TSB_HC_MVC
{
	public function execute( $calendar_id )
	{
		$args = array();
		$args[] = $calendar_id;
		$calendar =  $this->app->make('/calendars/commands/read')
			->execute( $args )
			;

		$schedule_manager = $this->app->make('/schedule/manager')
			->set_calendar( $calendar_id )
			;
		$values = $schedule_manager->get_settings();

		$view = $this->app->make('/settings/edit/view')
			->render( $calendar, $values )
			;

		$view = $this->app->make('/settings/view/layout')
			->render( $view, $calendar, 'settings/' . $calendar_id )
			;

		$view = $this->app->make('/layout/view/body')
			->set_content($view)
			;
		return $this->app->make('/http/view/response')
			->set_view($view) 
			;
	}
}