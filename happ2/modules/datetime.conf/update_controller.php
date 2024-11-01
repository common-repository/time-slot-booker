<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Datetime_Conf_Update_Controller_HC_MVC
{
	public function execute()
	{
		$form = $this->app->make('/datetime.conf/form');
		return $this->app->make('/conf/update/controller')
			->execute( $form )
			;
	}
}