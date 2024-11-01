<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Validate_Min_HC_MVC
{
	public function validate( $value, $min )
	{
		$msg = sprintf( HCM::__('At least %s is required'), $min );

		$return = ( $value >= $min ) ? TRUE : $msg;
		return $msg;
	}
}