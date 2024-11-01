<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Front_New_Input_Bookings_TSB_HC_MVC implements Form_Input_Interface_HC_MVC
{
	public function grab( $name, $post )
	{
		$return = array();

		$src_value = $this->app->make('/form/hidden')
			->grab($name, $post)
			;

		$src_value = trim( $src_value );
	// convert string to array
		$src_value = explode('|', $src_value);

		for( $ii = 0; $ii < count($src_value); $ii++ ){
			$this_slot = explode('-', $src_value[$ii]);
			if( count($this_slot) <= 1 ){
				continue;
			}

			$return[$ii] = array(
				'starts_at'	=> $this_slot[0],
				'ends_at'	=> $this_slot[1],
				);
		}

		return $return;
	}

	public function render( $name, $value = NULL )
	{
		$out = NULL;

		$input_value = '';
		if( $value ){
			$input_value = array();
			foreach( $value as $slot ){
				$input_value[] = $slot['starts_at'] . '-' . $slot['ends_at'];
				if( isset($slot['order']) ){
					$current_order_id = $slot['order'];
				}
			}
			$input_value = join('|', $input_value);
			
			
			$out = $this->app->make('/html/grid')
				->set_gutter(1)
				;

			$sm = $this->app->make('/schedule/manager');
			foreach( $value as $slot ){
				$slot = $sm->prepare_slot( $slot );
				// _print_r( $slot );

				$this_view = $this->app->make('/html/list')
					->set_gutter(0)
					->add( 
						$this->app->make('/html/element')->tag('div')
							->add_attr('class', 'hc-muted2')
							->add( $slot['formatted_date'] )
						)
					->add( 
						$this->app->make('/html/element')->tag('div')
							->add( $slot['formatted_start'] . ' - ' . $slot['formatted_end'] )
						)
					;
				$out
					->add( $this_view, 4, 6 )
					;
			}
		}

		$hidden = $this->app->make('/form/hidden')
			->render($name, $input_value)
			;

		$out = $this->app->make('/html/element')->tag('div')
			->add( $hidden )
			->add( $out )
			;

		return $out;
	}
}

