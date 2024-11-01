<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Availability_Regular_Controller_TSB_HC_MVC
{
	public function execute( $calendar_id )
	{
		$args = array();
		$args[] = $calendar_id;
		$calendar =  $this->app->make('/calendars/commands/read')
			->execute( $args )
			;

		$args = array();
		$args[] = array('applied_on_weekday', '<>', NULL);
		$args[] = array('calendar_id', '=', $calendar_id);
		$entries = $this->app->make('/availability/commands/read')
			->execute( $args )
			;

		$helper = $this->app->make('/availability/helper');
		$entries = $helper->group( $entries );

		$values = array();
		foreach( $entries as $applied_on_weekday => $slots ){
			$k = 'regular_' . $applied_on_weekday;
			$values[ $k ] = $slots;
		}

		$view = $this->app->make('/availability/regular/view')
			->render( $values, $calendar )
			;
		// $view = $this->app->make('/availability/regular/view/layout')
			// ->render( $view, $calendar )
			// ;

		$view = $this->app->make('/settings/view/layout')
			->render( $view, $calendar, 'availability/regular/' . $calendar_id )
			;

		$view = $this->app->make('/layout/view/body')
			->set_content($view)
			;
		return $this->app->make('/http/view/response')
			->set_view($view)
			;
	}
}