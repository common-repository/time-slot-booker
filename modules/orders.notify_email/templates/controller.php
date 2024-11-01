<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Notify_Email_Templates_Controller_TSB_HC_MVC
{
	public function execute( $calendar_id )
	{
		$args = array();
		$args[] = $calendar_id;
		$calendar =  $this->app->make('/calendars/commands/read')
			->execute( $args )
			;

		$options = $this->app->make('/orders.notify-email/manager')
			->options()
			;

		$values = array();
		$app_settings = $this->app->make('/app/settings');

		$props = array('active', 'subject', 'body');

		foreach( $options as $k => $v ){
			reset( $props );
			foreach( $props as $p ){
				$this_k2 = 'notify-email:' . $k . ':' . $p . ':' . $calendar_id;
				$this_k = 'notify-email:' . $k . ':' . $p;
				$this_v = $app_settings->get( $this_k2 );
				if( ! strlen($this_v) ){
					$this_v = $app_settings->get( $this_k );
				}
				$values[ $this_k ] = $this_v;
			}
		}

		$view = $this->app->make('/orders.notify-email/templates/view')
			->render( $calendar, $options, $values )
			;

		$view = $this->app->make('/settings/view/layout')
			->render( $view, $calendar, 'orders.notify-email/templates/' . $calendar_id )
			;

		$view = $this->app->make('/layout/view/body')
			->set_content($view)
			;
		return $this->app->make('/http/view/response')
			->set_view($view) 
			;
	}
}