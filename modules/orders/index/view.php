<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Index_View_TSB_HC_MVC
{
	public function render( $entries, $total_count, $page = 1, $search = '', $per_page = 10 )
	{
		$header = $this->header();

		$rows = array();
		foreach( $entries as $e ){
			$rows[$e['id']] = $this->row($e);
		}

		$out = $this->app->make('/html/list')
			->set_gutter(2)
			;

		$submenu = $this->app->make('/html/list-inline')
			->set_gutter(2)
			;
		if( $total_count > $per_page ){
			$pager = $this->app->make('/html/pager')
				->set_total_count( $total_count )
				->set_current_page( $page )
				->set_per_page($per_page)
				;

			$submenu
				->add( $pager )
				;
		}

		$search_view = $this->app->make('/modelsearch/view');
		$submenu
			->add( $search_view->render($search) )
			;

		$out
			->add( $submenu )
			;


		if( $rows ){
			$table = $this->app->make('/html/table-responsive')
				->set_header($header)
				->set_rows($rows)
				;

			$table = $this->app->make('/html/element')->tag('div')
				->add( $table )
				->add_attr('class', 'hc-border')
				;

			$out
				->add( $table )
				;
		}
		elseif( $search ){
			$msg = HCM::__('No Matches');
			$out
				->add( $msg )
				;
		}

		return $out;
	}

	public function header()
	{
		$return = array(
			'ref' 		=> HCM::__('Order'),
			'bookings' 	=> HCM::__('Bookings'),
			'customer' 	=> HCM::__('Customer'),
			// 'id' 		=> 'ID',
			);

		$return = $this->app
			->after( array($this, __FUNCTION__), $return )
			;

		return $return;
	}

	public function row( $e )
	{
		$return = array();
		if( ! $e ){
			return $return;
		}

		$helper = $this->app->make('/orders/helper');

		$ref_view = $e['ref'];
		$ref_view = $helper->format_refno( $ref_view );

		$ref_view = $this->app->make('/html/ahref')
			->to('/orders/' . $e['id'] . '/edit')
			->add( $ref_view )
			;

		$calendar = $e['calendar'];
		$ref_view = $this->app->make('/html/list')
			->set_gutter(0)
			->add( $ref_view )
			;

		if( isset($calendar['title']) && strlen($calendar['title']) ){
			$ref_view
				->add( $calendar['title'] )
				;
		}

		$p = $this->app->make('/orders/presenter');
		$status_view = $p->present_status( $e );
		$ref_view
			->add( $status_view )
			;

		$customer_details = $p->present_customer_details( $e );
		$customer_view = $this->app->make('/html/list')
			->set_gutter(0)
			;

		$ii = 0;
		foreach( $customer_details as $k => $v ){
			$v = $this->app->make('/html/element')->tag('span')
				->add( $v )
				->add_attr('title', $k)
				;

			if( $ii ){
				$v
					->add_attr('class', 'hc-muted2')
					->add_attr('class', 'hc-fs2')
					;
			}
			$customer_view
				->add( $v )
				;
			$ii++;
		}
		$return['customer'] = $customer_view;

		$return['ref'] = $ref_view;
		$return['id']	= $e['id'];

		$p = $this->app->make('/bookings/presenter');
		$bookings_view = $this->app->make('/html/list')
			->set_gutter(1)
			;
		foreach( $e['bookings'] as $booking ){
			$this_booking_view = $p->present_time( $booking );
			$bookings_view
				->add( $this_booking_view )
				;
		}
		$return['bookings'] = $bookings_view;

		$return = $this->app
			->after( array($this, __FUNCTION__), $return, $e )
			;

		return $return;
	}
}