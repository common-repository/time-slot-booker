<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Front_New_Form_TSB_HC_MVC
{
	public function inputs( $calendar = NULL )
	{
		$return = array(
			'bookings'	=> array(
				'input'	=> $this->app->make('/front/new/input-bookings')
					// ->set_calendar( $calendar )
					,
				'label'	=> HCM::__('Slots'),
				'validators'	=> array(
					$this->app->make('/validate/required')
					)
				),
			);

		$fm = $this->app->make('/orders/form-manager');
		$inputs_conf = $fm->inputs( $calendar );

		foreach( $inputs_conf as $k => $v ){
			if( ! $v ){
				continue;
			}

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