<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface Form_Input_Interface_HC_MVC {
	public function grab( $name, $post );
	public function render( $name, $value = NULL );
}

class Form_Input_HC_MVC
{
	public function name( $name )
	{
		$prefix = 'hc-';

		$return = $name;
		if( substr($return, 0, strlen($prefix)) != $prefix ){
			$return = $prefix . $return;
		}
		return $return;
	}

	public function grab( $name, $post )
	{
		$name = $this->name($name);
		$return = NULL;

		if( substr($name, -strlen('[]')) == '[]' ){
			$core_name = substr($name, 0, -strlen('[]'));
			if( isset($post[$core_name]) ){
				$return = $post[$core_name];
			}
		}
		else {
			if( isset($post[$name]) ){
				$return = $post[$name];
			}
		}
		if( ! is_array($return) ){
			$return = trim( $return );
		}

		return $return;
	}
}