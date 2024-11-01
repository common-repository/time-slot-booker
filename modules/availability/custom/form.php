<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Availability_Custom_Form_TSB_HC_MVC
{
	public function inputs( $calendar, $current_id = NULL )
	{
		$return = array();

		$return['applied_on_date'] = array(
			'input' => $this->app->make('/datepicker/input')
				,
			'label' => HCM::__('Date'),
			'validators' => array(
				$this->app->make('/validate/required'),
				$this->validator_date($calendar, $current_id)
				),
			);

		$return['slots'] = array(
			'input'	=> $this->app->make('/availability/input'),
			'label'	=> HCM::__('Timeslots'),

			'validators' => array(
				// $this->app->make('/validate/required'),
				),
			);


		return $return;
	}

	public function validator_date( $calendar, $current_id = NULL )
	{
		$app = $this->app;

		$return = function( $value ) use ( $app, $calendar, $current_id ){
			$return = TRUE;
			$msg = HCM::__('Custom availability already defined for this date');

			$calendar_id = $calendar['id'];
			$args = array();
			$args[] = 'count';
			$args[] = array('calendar_id', '=', $calendar_id);
			if( $current_id ){
				$args[] = array('id', '<>', $current_id);
			}
			$args[] = array('applied_on_date', '=', $value);

			$total_count = $app->make('/availability/commands/read')
				->execute( $args )
				;
			
			if( $total_count ){
				$return = $msg;
			}

			return $return;
		};

		return $return;
	}
}