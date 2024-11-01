<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Validate_Match_HC_MVC
{
	public function validate( $value, $compare_to, $compare_to_label )
	{
		$return = TRUE;
		$msg = HCM::__('This field does not match the %s field');

		if( $value != $compare_to ){
			$return = sprintf($msg, $compare_to_label);
		}

		return $return;
	}
}