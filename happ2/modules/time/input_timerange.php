<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Time_Input_Timerange_HC_MVC implements Form_Input_Interface_HC_MVC
{
	protected $_with_all_day = FALSE;

	public function with_all_day( $with = TRUE )
	{
		$this->_with_all_day = $with;
		return $this;
	}

	public function _init()
	{
	// add javascript
		$this->app->make('/app/enqueuer')
			->register_script( 'timerange-input', 'happ2/modules/time/assets/js/timerange-input.js' )
			->enqueue_script( 'timerange-input' )
			;
		return $this;
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

		$return = explode('-', $input_value);
		return $return;
	}

	public function render( $name, $value = NULL )
	{
	// time options
		$time_format = $this->app->make('/form/time')
			->options()
			;

		$out = $this->app->make('/html/element')->tag('div')
			->add_attr('class', 'hcj-timerange-input')
			;

		$data_atts = array(
			'time-format'		=> $time_format,
			);

		reset( $data_atts );
		foreach( $data_atts as $k => $v ){
			$out
				->add_attr('data-' . $k, htmlentities(json_encode($v)))
				;
		}

		$input_value = NULL;
		if( $value ){
			$input_value = array();
			foreach( $value as $v ){
				$input_value[] = $v;
			}
			$input_value = join('-', $input_value);
		}

		$hidden = $this->app->make('/form/hidden')
			->render( $name, $input_value )
			;

		$display = $this->app->make('/html/element')->tag('div')
			->add_attr('class', 'hcj-display')
			;

		if( $this->_with_all_day ){
			$display
				->add_attr('class', 'hc-mr2')
				;
		}

		$display = $this->app->make('/html/list-inline')
			->set_gutter(0)
			->add( $display )
			;

		if( $this->_with_all_day ){
			$inputs_value['allday'] = 0;

			$input_allday = $this->app->make('/form/checkbox')
				->set_label( HCM::__('All Day') )
				;
			$display
				->add(
					$input_allday
						->render( $name . '_allday', $inputs_value['allday'] )
					)
				;
		}

		$out
			->add( $hidden )
			->add( $display )
			;

		return $out;
	}
}