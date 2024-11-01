<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Datetime_Conf_Form_HC_MVC
{
	public function inputs()
	{
		$return = array();

		$return['datetime:date_format'] = array(
			'input' => $this->app->make('/form/select')
				->set_options( 
					array(
						'd/m/Y'	=> date('d/m/Y'),
						'd-m-Y'	=> date('d-m-Y'),
						'n/j/Y'	=> date('n/j/Y'),
						'Y/m/d'	=> date('Y/m/d'),
						'd.m.Y'	=> date('d.m.Y'),
						'j M Y'	=> date('j M Y'),
						'Y-m-d'	=> date('Y-m-d'),
						)
					),
			'label'	=> HCM::__('Date Format')
			);

		$return['datetime:time_format'] = array(
			'input' => $this->app->make('/form/select')
				->set_options( 
					array(
						'g:ia'	=> date('g:ia'),
						'g:i A'	=> date('g:i A'),
						'H:i'	=> date('H:i'),
						)
					),
			'label'	=> HCM::__('Time Format')
			);

		$return['datetime:week_starts'] = array(
			'input' => $this->app->make('/form/select')
				->set_options( 
					array(
						0	=> HCM::__('Sun'),
						1	=> HCM::__('Mon'),
						2	=> HCM::__('Tue'),
						3	=> HCM::__('Wed'),
						4	=> HCM::__('Thu'),
						5	=> HCM::__('Fri'),
						6	=> HCM::__('Sat'),
						)
					),
			'label'	=> HCM::__('Week Starts On')
			);

		$return = $this->app
			->after( $this, $return )
			;

		return $return;
	}
}