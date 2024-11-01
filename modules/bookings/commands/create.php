<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Bookings_Commands_Create_TSB_HC_MVC
{
	public function validators( $values = array() )
	{
		$return = array();

		$return['calendar_id'] = array(
			$this->app->make('/validate/required'),
			);

		$return['starts_at'] = array(
			$this->validator_availability( $values )
			);

		$return = $this->app
			->after( array($this, __FUNCTION__), $return )
			;

		return $return;
	}

	public function validate( $values )
	{
		$return = TRUE;

		$validators = $this->validators( $values );
		$errors = $this->app->make('/validate/helper')
			->validate( $values, $validators )
			;
		if( $errors ){
			$return = $errors;
		}

		return $return;
	}

	public function execute( $values )
	{
		$cm = $this->app->make('/commands/manager');

		$errors = $this->validate( $values );
		if( $errors !== TRUE ){
			$cm->set_errors( $this, $errors );
			return;
		}

		$command = $this->app->make('/commands/create')
			->set_table('bookings')
			;
		$command->execute( $values );

		$errors = $cm->errors( $command );
		if( $errors ){
			$cm->set_errors( $this, $errors );
			return;
		}

		$results = $cm->results( $command );
		$cm->set_results( $this, $results );

		$this->app
			->after( $this, $this )
			;
	}

	public function validator_availability( $values )
	{
		$app = $this->app;

		$return = function( $value ) use ( $app, $values ){
			$return = TRUE;
			$msg = HCM::__('Not available');
			$need_capacity = 1;

			$t = $app->make('/app/lib')->time();

			$t->setDateTimeDb2( $value );
			$this_date = $t->formatDateDb();

			$manager = $app->make('/schedule/manager')
				->set_calendar( $values['calendar_id'] )
				;

			$slots = $manager->get_slots( $this_date, $this_date );

			$available = FALSE;
			reset( $slots );
			foreach( $slots as $slot ){
				if( 
					( $slot['starts_at'] == $values['starts_at'] ) && 
					( $slot['ends_at'] == $values['ends_at'] ) && 
					( ($slot['capacity'] - $slot['booked']) >= $need_capacity )
					){
						$available = TRUE;
						break;
					}
			}

			if( ! $available ){
				$t->setDateTimeDb2( $values['starts_at'] );
				$time_view = $t->formatFull();

				$return = $time_view . ': ' . $msg;
				return $return;
			}

			return $return;
		};

		return $return;
	}
}