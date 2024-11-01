<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Datetime_Conf_View_HC_MVC
{
	public function render()
	{
		$form = $this->app->make('/datetime.conf/form');
		$to = '/datetime.conf/update';

		return $this->app->make('/conf/view')
			->render( $form, $to )
			;
	}
}