<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Form_Manager_TSB_HC_MVC
{
	public function inputs( $calendar = NULL )
	{
		$return = array(
			'customer_name'	=> array(
				'label'			=> HCM::__('Name'),
				'use'			=> TRUE,
				'required'		=> TRUE,
				),

			'customer_email'	=> array(
				'label'			=> HCM::__('Email'),
				'use'			=> TRUE,
				'required'		=> TRUE,
				),

			'customer_phone'	=> array(
				'label'			=> HCM::__('Phone'),
				'use'			=> TRUE,
				'required'		=> TRUE,
				),
			);

		$return = $this->app
			->after( $this, $return, $calendar )
			;

		return $return;
	}
}