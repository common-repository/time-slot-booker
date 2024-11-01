<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Availability_Regular_Controller_Update_TSB_HC_MVC
{
	public function execute( $calendar_id )
	{
		$post = $this->app->make('/input/lib')->post();

		$inputs = $this->app->make('/availability/regular/form')
			->inputs()
			;
		$helper = $this->app->make('/form/helper');

		list( $values, $errors ) = $helper->grab( $inputs, $post );

		if( $errors ){
			return $this->app->make('/http/view/response')
				->set_redirect('-referrer-') 
				;
		}

		$prfx = 'regular_';

		$final_values = array();
		foreach( $values as $k => $vs ){
			if( ! $vs ){
				continue;
			}

			$this_applied_on = substr($k, strlen($prfx));
			reset( $vs );
			foreach( $vs as $v ){
				$v['applied_on_weekday']	= $this_applied_on;
				$v['calendar_id']			= $calendar_id;
				$final_values[] = $v;
			}
		}

		$index_props = array( 'applied_on_weekday', 'slot_start', 'slot_end', 'slot_interval' );
		$command = $this->app->make('/availability/commands/read');

		$args = array();
		$args[] = array('applied_on_weekday', '<>', NULL);
		$args[] = array('calendar_id', '=', $calendar_id);

		$stored = $command
			->execute( $args )
			;

		$stored_index = array();
		foreach( $stored as $sto ){
			$index = array();
			foreach( $index_props as $p ){
				$index[] = $sto[$p];
			}
			$index = join('-', $index);
			$stored_index[$index] = $sto['id'];
		}

		$to_add = array();
		$to_delete = array();

		reset( $final_values );
		foreach( $final_values as $v ){
			$index = array();
			foreach( $index_props as $p ){
				$index[] = $v[$p];
			}
			$index = join('-', $index);

			if( isset($stored_index[$index]) ){
				unset( $stored_index[$index] );
			}
			else {
				$to_add[] = $v;
			}
		}
		$to_delete = $stored_index;

	// do
		$cm = $this->app->make('/commands/manager');

		if( $to_delete ){
			$command1 = $this->app->make('/availability/commands/delete');
			foreach( $to_delete as $id ){
				$command1
					->execute( $id )
					;

				$errors = $cm->errors( $command1 );
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
		}

		if( $to_add ){
			$command2 = $this->app->make('/availability/commands/create');
			foreach( $to_add as $values ){
				$command2
					->execute( $values )
					;

				$errors = $cm->errors( $command2 );
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
		}

	// OK
		return $this->app->make('/http/view/response')
			->set_redirect('-referrer-')
			;
	}
}