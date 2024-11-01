<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Settings_Edit_Form_TSB_HC_MVC
{
	public function inputs()
	{
		$status_options = $this->app->make('/orders/presenter')
			->status_options()
			;
		unset($status_options['cancelled']);

		$max_slots_options = array();
		for( $ii = 1; $ii <= 10; $ii++ ){
			$max_slots_options[ $ii ] = $ii;
		}

		$return = array(
			'start_status'	=> array(
				'input'	=> $this->app->make('/form/radio')
					->set_options( $status_options ),
				'label'	=> HCM::__('Initial Status'),
				),

			'max_slots'	=> array(
				'input'	=> $this->app->make('/form/select')
					->set_options( $max_slots_options )
					,
				'label'	=> HCM::__('Max Slots In One Order'),
				'help'	=> HCM::__('How many different time slots one customer can book in one order.'),
				'validators' => array(
					$this->app->make('/validate/required')
					)
				),

			'min_from_now'	=> array(
				'input'	=> $this->app->make('/form/duration'),
				'label'	=> HCM::__('Min From Now'),
				'help'	=> HCM::__('For example, if this option is set to 1 day, then a customer can book slots from tomorrow and not earlier.'),
				'validators' => array(
					$this->app->make('/validate/required')
					)
				),

			'max_from_now'	=> array(
				'input'	=> $this->app->make('/form/duration'),
				'label'	=> HCM::__('Max From Now'),
				'help'	=> HCM::__('For example, if this option is set to 2 months, then a customer will not be able to book slots beyond 2 months from now.'),
				'validators' => array(
					$this->app->make('/validate/required')
					)
				),
			);
		return $return;
	}
}