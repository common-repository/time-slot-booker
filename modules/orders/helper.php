<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Helper_TSB_HC_MVC
{
	public function get_managers( $order )
	{
		$args = array();
		$args[] = array('roles', array('admin', 'manager'));
		$return = $this->app->make('/users/commands/read')
			->execute( $args )
			;
		return $return;
	}

	public function get_new_refno()
	{
		$command = $this->app->make('/orders/commands/read');

		$exists = TRUE;
		while( $exists ){
			$return = $this->generate_refno();

			$args = array();
			$args[] = array('count', 1);
			$args[] = array('ref', '=', $return);

			$exists = $command
				->execute( $args )
				;
		}

		return $return;
	}

	public function format_refno( $refno )
	{
		$return = array();

		$return[] = strtoupper( substr($refno, 0, 3) );
		$return[] = strtoupper( substr($refno, 3, 3) );
		$return[] = strtoupper( substr($refno, 6, 3) );

		$return = join( '-', $return );
		return $return;
	}

	public function unformat_refno( $refno )
	{
		$return = str_replace( '-', '', $refno );
		$return = strtolower( $return );
		return $return;
	}

	public function generate_refno()
	{
		$return = array();

		$return[] = rand( 100, 999 );
		$return[] = rand( 100, 999 );
		$return[] = rand( 100, 999 );

		$return = join('', $return);
		return $return;


		$return[] = HC_Lib2::generate_rand( 
			1,
			array(
				'caps'		=> FALSE,
				'hex'		=> TRUE,
				'letters'	=> FALSE,
				'digits'	=> FALSE,
				)
			);

		$return[] = HC_Lib2::generate_rand( 
			3, 
			array(
				'caps'		=> FALSE,
				'hex'		=> FALSE,
				'letters'	=> FALSE,
				'digits'	=> TRUE,
				)
			);

		$return[] = HC_Lib2::generate_rand( 
			3, 
			array(
				'caps'		=> FALSE,
				'hex'		=> FALSE,
				'letters'	=> FALSE,
				'digits'	=> TRUE,
				)
			);

		$return = join('', $return);

		$return = array();
		$return[] = HC_Lib2::generate_rand( 
			6,
			array(
				'caps'		=> FALSE,
				'hex'		=> TRUE,
				'letters'	=> FALSE,
				'digits'	=> TRUE,
				)
			);
		$return = join('', $return);

		return $return;
	}
}
