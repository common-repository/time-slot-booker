<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Wordpress_Setup_View_Form_HC_MVC
{
	public function render( $form )
	{
		$return = $this->app->make('/html/list');
		$return
			->add(
				HCM::__('Please define which WordPress user roles will be able to access the plugin.')
				)
			;
		$return
			->add( $form )
			;

		return $return;
	}
}