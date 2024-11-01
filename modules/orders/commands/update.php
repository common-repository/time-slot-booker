<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Commands_Update_TSB_HC_MVC
{
	public function validators( $id )
	{
		$return = array();

		$return['ref'] = array(
			$this->app->make('/validate/required'),
			$this->app->make('/validate/maxlen')
				->params( 250 ),
			$this->app->make('/validate/unique')
				->params( 'orders', 'ref', $id )
			);

		$return['status'] = array(
			$this->app->make('/validate/required'),
			);

		$return['bookings'] = array(
			$this->app->make('/validate/required'),
			);

		$return = $this->app
			->after( array($this, __FUNCTION__), $return, $id )
			;

		return $return;
	}

	public function execute( $id, $values = array() )
	{
		$cm = $this->app->make('/commands/manager');

		$validators = $this->validators( $id );
		$errors = $this->app->make('/validate/helper')
			->validate( $values, $validators )
			;
		if( $errors ){
			$cm->set_errors( $this, $errors );
			return;
		}

		$command = $this->app->make('/commands/update')
			->set_table('orders')
			;
		$command->execute( $id, $values );

		$errors = $cm->errors( $command );
		if( $errors ){
			$cm->set_errors( $this, $errors );
			return;
		}

		$results = $cm->results( $command );
		$before = $cm->before( $command );

		$cm->set_results( $this, $results );
		$cm->set_before( $this, $before );

		$this->app
			->after( $this, $this )
			;
	}
}