<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Delete_Controller_TSB_HC_MVC
{
	public function execute( $id )
	{
		$command = $this->app->make('/orders/commands/delete');
		$response = $command
			->execute( $id )
			;

		if( isset($response['errors']) ){
			echo $response['errors'];
			exit;
		}

	// OK
		$redirect_to = $this->app->make('/http/uri')
			->url('/schedule')
			;
		return $this->app->make('/http/view/response')
			->set_redirect($redirect_to) 
			;
	}
}