<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Edit_Form_TSB_HC_MVC
{
	public function inputs( $calendar = NULL )
	{
		$status_options = $this->app->make('/orders/presenter')
			->status_options()
			;

		$return = array(
			'ref' => array(
				'input'	=> $this->app->make('/orders/input/refno'),
				'label'	=> HCM::__('Ref Code'),
				'validators'	=> array(
					$this->app->make('/validate/required')
					)
				),

			'status' => array(
				'input'	=> $this->app->make('/form/radio')
					->set_options( $status_options )
					,
				'label'	=> HCM::__('Status'),
				'validators'	=> array(
					$this->app->make('/validate/required')
					)
				),

			'bookings'	=> array(
				'input'	=> $this->app->make('/orders/input/bookings')
					->set_calendar( $calendar )
					->set_show_removed( TRUE )
					,
				'label'	=> HCM::__('Slots'),
				'validators'	=> array(
					$this->app->make('/validate/required')
					)
				),
			);

		$fm = $this->app->make('/orders/form-manager');
		$customer_inputs_conf = $fm->inputs( $calendar );

		foreach( $customer_inputs_conf as $k => $v ){
			if( isset($v['use']) && (! $v['use']) ){
				continue;
			}

			$this_input = array();
			$this_input['input'] = $this->app->make('/form/text');
			if( isset($v['label']) ){
				$this_input['label'] = $v['label'];
			}

			if( isset($v['required']) && $v['required'] ){
				$this_input['validators'] = array(
					$this->app->make('/validate/required')
					);
			}
			$return[$k] = $this_input;
		}

		$return = $this->app
			->after( $this, $return, $calendar )
			;

		return $return;
	}
}