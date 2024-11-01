<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Notify_Email_Templates_Controller_Update_TSB_HC_MVC
{
	public function execute( $calendar_id )
	{
		$post = $this->app->make('/input/lib')->post();

		$inputs = $this->app->make('/orders.notify-email/templates/form')
			->inputs()
			;
		$helper = $this->app->make('/form/helper');

		list( $values, $errors ) = $helper->grab( $inputs, $post );

		if( $errors ){
			return $this->app->make('/http/view/response')
				->set_redirect('-referrer-') 
				;
		}

		$final_values = array();
		foreach( $values as $k => $v ){
			$final_k = $k . ':' . $calendar_id;
			$final_values[ $final_k ] = $v;
		}

		$response = $this->app->make('/conf/commands/update')
			->execute( $final_values )
			;

		if( isset($response['errors']) ){
			$session = $this->app->make('/session/lib');
			$session
				->set_flashdata('error', $response['errors'])
				;
			return $this->app->make('/http/view/response')
				->set_redirect('-referrer-') 
				;
		}

	// OK
		$this->app->make('/session/lib')
			->set_flashdata('form_errors', array())
			->set_flashdata('form_values', array())
			;
		return $this->app->make('/http/view/response')
			->set_redirect('-referrer-') 
			;
	}
}