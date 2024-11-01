<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Input_Refno_TSB_HC_MVC implements Form_Input_Interface_HC_MVC
{
	public function grab( $name, $post )
	{
		$return = $this->app->make('/form/text')->grab( $name, $post );

		$helper = $this->app->make('/orders/helper');
		$return = $helper->unformat_refno( $return );

		return $return;
	}

	public function render( $name, $value = NULL )
	{
		if( strlen($value) ){
			$helper = $this->app->make('/orders/helper');
			$value = $helper->format_refno( $value );
		}

		// $helper = $this->app->make('/orders/helper');
		// if( ! strlen($value) ){
			// $value = $helper->get_new_refno();
		// }
		// $value = $helper->format_refno( $value );

		return $this->app->make('/form/text')->render( $name, $value );
	}
}
