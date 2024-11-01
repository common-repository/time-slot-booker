<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Availability_Input_TSB_HC_MVC implements Form_Input_Interface_HC_MVC
{
	protected $props = array( 'slot_start', 'slot_end', 'slot_interval' );

	public function _init()
	{
	// add javascript
		$this->app->make('/app/enqueuer')
			->register_script( 'hc-tsb-availability-input', 'modules/availability/assets/js/input.js' )
			->enqueue_script( 'hc-tsb-availability-input' )
			;
		return $this;
	}

	public function lang()
	{
		$return = array(
			'From'			=> HCM::__('From'),
			'To'			=> HCM::__('To'),
			'Interval'		=> HCM::__('Interval'),
			'Total Slots'	=> HCM::__('Total Slots'),
			'Add'			=> HCM::__('Add'),
			'Day Off'		=> HCM::__('Day Off'),
			'Remove'		=> HCM::__('Remove'),
			);
		return $return;
	}

	public function grab( $name, $post )
	{
		$return = array();

		$input_value = $this->app->make('/form/input')
			->grab($name, $post)
			;

		if( ! $input_value ){
			return $return;
		}

		$slots = explode( ',', $input_value );
		foreach( $slots as $slot ){
			$this_value = explode('-', $slot);
			for( $ii = 0; $ii < count($this->props); $ii++ ){
				$this_slot[ $this->props[$ii] ] = $this_value[ $ii ];
			}
			$return[] = $this_slot;
		}

		return $return;
	}

	public function render( $name, $value = NULL )
	{
	// time options
		$time_format = $this->app->make('/form/time')
			->options()
			;

	// duration
		$raw_duration_options = array( 
			5*60, 10*60, 15*60, 20*60, 30*60, 45*60, 60*60,
			75*60, 90*60, 2*60*60, 2.5*60*60, 3*60*60, 4*60*60, 5*60*60, 6*60*60,
			7*60*60, 8*60*60, 9*60*60, 10*60*60, 11*60*60, 12*60*60, 
			16*60*60, 24*60*60
			);
		$t = $this->app->make('/app/lib')->time();
		$t->setDateDb( 20170802 );

		$duration_format = array();
		foreach( $raw_duration_options as $o ){
			$duration_format[$o] = $t->formatPeriodShort( $o, 'hour' );
		}

		$out = $this->app->make('/html/element')->tag('div')
			->add_attr('class', 'hcj-tsb-availability-input')
			;

		$data_atts = array(
			'time-format'		=> $time_format,
			'duration-format'	=> $duration_format,
			);

		reset( $data_atts );
		foreach( $data_atts as $k => $v ){
			$out
				->add_attr('data-' . $k, htmlentities(json_encode($v)))
				;
		}

		$out
			->add_attr('data-lang', htmlentities(json_encode($this->lang())))
			;

		$input_value = NULL;
		if( $value ){
			$input_value = array();
			foreach( $value as $v ){
				$this_v = array();
				foreach( $this->props as $p ){
					$this_v[] = $v[$p];
				}
				$input_value[] = join('-', $this_v);
			}
			$input_value = join(',', $input_value);
		}

		$hidden = $this->app->make('/form/hidden')
			->render( $name, $input_value )
			;

		$display = $this->app->make('/html/element')->tag('div')
			->add_attr('class', 'hcj-display')
			;

		$out
			->add( $hidden )
			->add( $display )
			;

		return $out;
	}
}