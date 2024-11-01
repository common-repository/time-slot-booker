<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Availability_Custom_View_TSB_HC_MVC
{
	public function render( $entries, $calendar )
	{
		if( ! $entries ){
			return;
		}

		$header = $this->header();

		$helper = $this->app->make('/availability/helper');
	// group entries by date
		$entries = $helper->group( $entries );

		foreach( $entries as $applied_on_date => $slots ){
			$rows[] = $this->row($applied_on_date, $slots, $calendar);
		}

		$out = $this->app->make('/html/table-responsive')
			->set_header($header)
			->set_rows($rows)
			;

		$out = $this->app->make('/html/element')->tag('div')
			->add( $out )
			->add_attr('class', 'hc-border')
			;

		return $out;
	}

	public function header()
	{
		$return = array(
			'date' 		=> HCM::__('Date'),
			'slots' 	=> HCM::__('Timeslots'),
			'actions' 	=> NULL,
			);

		$return = $this->app
			->after( array($this, __FUNCTION__), $return )
			;

		return $return;
	}

	public function row( $applied_on_date, $slots, $calendar )
	{
		$return = array();
		if( ! $slots ){
			return $return;
		}

		$calendar_id = $calendar['id'];
		$t = $this->app->make('/app/lib')->time();

		$date_view = $applied_on_date;
		$date_view = $t->setDateDb( $date_view )
			->formatDateFullShort()
			;

		$delete_link = $this->app->make('/html/ahref')
			->to('/availability/custom/' . $calendar_id . '/delete/' . $applied_on_date)
			->add( HCM::__('Delete') )
			->add_attr('class', 'hcj2-confirm')
			->add_attr('class', 'hc-fs2')
			->add_attr('class', 'hc-red')
			;

		// $date_view = $this->app->make('/html/list')
			// ->set_gutter(0)
			// ->add( $date_view )
			// ->add( $delete_link )
			// ;
		$return['date'] = $date_view;

		$return['actions'] = $delete_link;

		$t = $this->app->make('/app/lib')->time();

		if( $slots ){
			$slots_view = $this->app->make('/html/list')
				->set_gutter(2)
				;
			foreach( $slots as $slot ){
				$this_time_view = $t->formatPeriodOfDay( $slot['slot_start'], $slot['slot_end'] );
				$this_duration_view = $t->formatPeriodShort( $slot['slot_interval'], 'hour' );
				$this_count = ($slot['slot_end'] - $slot['slot_start']) / $slot['slot_interval'];
				$this_duration_view .= ' [' . $this_count . ']';
				$this_duration_view = $this->app->make('/html/element')->tag('span')
					->add( $this_duration_view )
					->add_attr('class', 'hc-muted2')
					->add_attr('class', 'hc-fs2')
					;

				$this_slot_view = $this->app->make('/html/list')
					->set_gutter(0)
					->add( $this_time_view )
					->add( $this_duration_view )
					;

				$slots_view
					->add( $this_slot_view )
					;
			}
		}
		else {
			$slots_view = HCM::__('Day Off');
		}
		$return['slots'] = $slots_view;

		$return = $this->app
			->after( array($this, __FUNCTION__), $return, $applied_on_date, $slots )
			;

		return $return;
	}
}