<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Presenter_TSB_HC_MVC
{
	public function present_refno( $order )
	{
		$return = $order['ref'];
		$helper = $this->app->make('/orders/helper');
		$return = $helper->format_refno( $return );
		return $return;
	}

	public function present_status( $order )
	{
		$return = $order['status'];

		$status_options = $this
			->status_options()
			;
		$return = isset($status_options[$return]) ? $status_options[$return] : $return;

		return $return;
	}

	public function status_options()
	{
		$return = array(
			'confirmed'	=> HCM::__('Confirmed'),
			'pending'	=> HCM::__('Pending'),
			'cancelled'	=> HCM::__('Cancelled'),
			);

		return $return;
	}

	public function present_customer_title( $order )
	{
		$customer_details = $this->present_customer_details( $order );
		$return = array_shift( $customer_details );
		return $return;
	}

	public function present_customer_details( $order )
	{
		$return = array();

		foreach( $order as $k => $v ){
			if( is_array($v) ){
				continue;
			}

			$prfx = 'customer_';
			if( substr($k, 0, strlen($prfx)) == $prfx ){
				if( strlen($v) ){
					$return[ $k ] = $v;
					continue;
				}
			}

			$prfx = 'misc';
			if( substr($k, 0, strlen($prfx)) == $prfx ){
				if( strlen($v) ){
					$return[ $k ] = $v;
					continue;
				}
			}
		}

		return $return;
	}
}