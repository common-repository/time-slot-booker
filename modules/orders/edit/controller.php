<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Edit_Controller_TSB_HC_MVC
{
	public function execute( $id )
	{
		$args = array();
		$args[] = $id;
		$args[] = array('with', '-all-');

		$model = $this->app->make('/orders/commands/read')
			->execute( $args )
			;

		$view = $this->app->make('/orders/edit/view')
			->render( $model )
			;
		$view = $this->app->make('/orders/edit/view/layout')
			->render( $view, $model )
			;
		$view = $this->app->make('/layout/view/body')
			->set_content($view)
			;
		return $this->app->make('/http/view/response')
			->set_view($view) 
			;
	}
}