<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Availability_Helper_TSB_HC_MVC
{
	public function group( $entries )
	{
		$return = array();

		foreach( $entries as $e ){
			$this_date = isset($e['applied_on_date']) ? $e['applied_on_date'] : $e['applied_on_weekday'];

			if( ! isset($return[$this_date]) ){
				$return[$this_date] = array();
			}

			$return[$this_date][] = $e;
		}

		return $return;
	}
}
