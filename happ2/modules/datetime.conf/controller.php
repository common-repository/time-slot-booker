<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Datetime_Conf_Controller_HC_MVC
{
	public function execute()
	{
		$view = $this->app->make('/datetime.conf/view')
			->render()
			;
		$view = $this->app->make('/conf/view/layout')
			->render( $view, 'datetime' )
			;
		$view = $this->app->make('/layout/view/body')
			->set_content($view)
			;
		return $this->app->make('/http/view/response')
			->set_view($view)
			;
	}
}