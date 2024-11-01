<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Notify_Email_Templates_Form_TSB_HC_MVC
{
	public function inputs()
	{
		$return = array();

		$options = $this->app->make('/orders.notify-email/manager')
			->options()
			;

		foreach( $options as $k => $v ){
			$return['notify-email:' . $k . ':active'] = array(
				'input' => $this->app->make('/form/checkbox')
					->set_label( HCM::__('Active') )
					,
				);

			$return['notify-email:' . $k . ':subject'] = array(
				'input' => $this->app->make('/form/text'),
				'label' => HCM::__('Subject'),
				'validators' => array(
					$this->app->make('/validate/required'),
					),
				);

			$return['notify-email:' . $k . ':body'] = array(
				'input' => $this->app->make('/form/textarea')
					->set_rows(8)
					,
				'label' => HCM::__('Message'),
				'validators' => array(
					$this->app->make('/validate/required'),
					),
				);
		}

		return $return;
	}
}