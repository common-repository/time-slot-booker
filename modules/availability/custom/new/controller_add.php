<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Availability_Custom_New_Controller_Add_TSB_HC_MVC
{
	public function execute( $calendar_id )
	{
		$args = array();
		$args[] = $calendar_id;
		$calendar =  $this->app->make('/calendars/commands/read')
			->execute( $args )
			;

		$post = $this->app->make('/input/lib')->post();

		$inputs = $this->app->make('/availability/custom/form')
			->inputs( $calendar )
			;
		$helper = $this->app->make('/form/helper');

		list( $values, $errors ) = $helper->grab( $inputs, $post );

		if( $errors ){
			return $this->app->make('/http/view/response')
				->set_redirect('-referrer-') 
				;
		}

		$cm = $this->app->make('/commands/manager');
		$values['calendar_id'] = $calendar_id;

		$slots = $values['slots'];
		unset( $values['slots'] );

		$final_values = array();
		foreach( $slots as $slot ){
			$this_values = array_merge( $values, $slot );
			$final_values[] = $this_values;
		}

		$command = $this->app->make('/availability/commands/create');

		foreach( $final_values as $v ){
			$command
				->execute( $v )
				;

			$errors = $cm->errors( $command );
			if( $errors ){
				$session = $this->app->make('/session/lib');
				$session
					->set_flashdata('error', $errors)
					;
				return $this->app->make('/http/view/response')
					->set_redirect('-referrer-') 
					;
			}
		}

	// OK
		$redirect_to = $this->app->make('/http/uri')
			->url('/availability/custom/' . $calendar_id)
			;
		return $this->app->make('/http/view/response')
			->set_redirect($redirect_to) 
			;
	}
}