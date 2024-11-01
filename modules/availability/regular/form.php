<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Availability_Regular_Form_TSB_HC_MVC
{
	public function inputs()
	{
		$return = array();

		$t = $this->app->make('/app/lib')->time();
		$wkds = $t->getWeekdays();

		foreach( $wkds as $wkd => $label ){
			$return['regular_' . $wkd] = array(
				'input'	=> $this->app->make('/availability/input'),
				);
		}
		return $return;
	}
}