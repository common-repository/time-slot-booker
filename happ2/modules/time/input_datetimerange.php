<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Time_Input_DateTimeRange_HC_MVC implements Form_Input_Interface_HC_MVC
{
	protected $props = array( 'start', 'end' );
	protected $inputs = array();

	public function validator()
	{
		$return = function( $value ){
			$return = TRUE;
			$msg = HCM::__('The end time should be after the start time');

			if( $value[1] <= $value[0] ){
				$return = $msg;
			}
			return $return;
		};

		return $return;
	}

	public function _init()
	{
		$this->inputs = array(
			'selector'		=> $this->app->make('/form/checkbox')
				->set_label( HCM::__('All Day') )
				,
			'date'		=> $this->app->make('/datepicker/input'),
			'date_end'	=> $this->app->make('/datepicker/input'),
			'time'		=> $this->app->make('/time/input-timerange'),
			);

	// add javascript
		$this->app->make('/app/enqueuer')
			->register_script( 'datetimerange-input', 'happ2/modules/time/assets/js/datetimerange-input.js' )
			->enqueue_script( 'datetimerange-input' )
			;
		return $this;
	}

	public function grab( $name, $post )
	{
		$return = array();

		$t = $this->app->make('/app/lib')->time();

		$all_day = $this->inputs['selector']
			->grab( $name . '_selector', $post )
			;
		$date = $this->inputs['date']
			->grab( $name . '_date', $post )
			;

		if( $all_day ){
			$date_end = $this->inputs['date_end']
				->grab( $name . '_date_end', $post )
				;
			$return[] = $date . '0000';
			$return[] = $date_end . '2400';
		}
		else {
			$time = $this->inputs['time']
				->grab( $name . '_time', $post )
				;

			$t->setDateDb( $date );
			$t->modify( '+' . $time[0] . ' seconds' );
			$return[] = $t->formatDateTimeDb2();

			$t->setDateDb( $date );
			$t->modify( '+' . $time[1] . ' seconds' );
			$return[] = $t->formatDateTimeDb2();
		}

		return $return;
	}

	public function render( $name, $value = NULL )
	{
		$inputs_value = array(
			'selector'		=> NULL,
			'date'			=> NULL,
			'date_end'		=> NULL,
			'time'			=> NULL,
			);

		if( $value ){
			$t = $this->app->make('/app/lib')->time();

			$inputs_value['time'] = array();

			$t->setDateTimeDb2( $value[0] );
			$this_seconds1 = $t->getSecondsOfDay();
			$this_date1 = $t->formatDateDb();
			$inputs_value['time'][] = $this_seconds1;
			$inputs_value['date'] = $this_date1;

			$t->setDateTimeDb2( $value[1] );
			$this_seconds2 = $t->getSecondsOfDay();
			if( ! $this_seconds2 ){
				$t->modify('-1 day');
				$this_seconds2 = 24*60*60;
			}
			$this_date2 = $t->formatDateDb();

			$inputs_value['time'][] = $this_seconds2;
			$inputs_value['date_end'] = $this_date2;

			if( ($inputs_value['time'][0] == 0) && ( ($inputs_value['time'][1] == 0) OR ($inputs_value['time'][1] == 24*60*60) ) ){
				$inputs_value['selector'] = 1;
			}
		}

		$out_all_day = $this->app->make('/html/list-inline')
			->set_gutter(2)
			->add( '-' )
			->add( 
				$this->inputs['date_end']
					->render( $name . '_date_end', $inputs_value['date_end'] ) 
				)
			;
		$out_all_day = $this->app->make('/html/element')->tag('div')
			->add_attr('class', 'hcj-all-day')
			->add( $out_all_day )
			;

		$out_date = $this->inputs['date']
			->render( $name . '_date', $inputs_value['date'] )
			;

		$out_partial_day = $this->app->make('/html/list-inline')
			->set_gutter(2)
			->add( 
				$this->inputs['time']
					->render( $name . '_time', $inputs_value['time'] ) 
				)
			;
		$out_partial_day = $this->app->make('/html/element')->tag('div')
			->add_attr('class', 'hcj-partial-day')
			->add( $out_partial_day )
			->add_attr('class', 'hc-mr2')
			;

		$out_selector = $this->inputs['selector']
			->render( $name . '_selector', $inputs_value['selector'] ) 
			;
		$out_selector = $this->app->make('/html/element')->tag('div')
			->add_attr('class', 'hcj-selector')
			->add( $out_selector )
			;

		$out = $this->app->make('/html/list')
			->set_gutter(2)

			// ->add( $out_selector )
			->add( 
				$this->app->make('/html/list')
					->set_gutter(2)
					->add( 
						$this->app->make('/html/list-inline')
							->set_gutter(2)
							->add( $out_date )
							->add( $out_all_day )
						)
					->add( 
						$this->app->make('/html/list-inline')
							->set_gutter(0)
							->add( $out_partial_day )
							->add( $out_selector )
						)
				)

			// ->add( 
				// $this->app->make('/html/list')
					// ->set_gutter(2)
					// ->add( $out_selector )
				// )
			;

		$out = $this->app->make('/html/element')->tag('div')
			->add_attr('class', 'hcj-datetimerange-input')
			->add( $out )
			;

		return $out;
	}
}