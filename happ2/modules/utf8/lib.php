<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Utf8 Class
 *
 * Provides support for UTF-8 environments
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	UTF-8
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/utf8.html
 */
class Utf8_Lib_HC_MVC {

	/**
	 * Constructor
	 *
	 * Determines if UTF-8 support is to be enabled
	 *
	 */
	function __construct()
	{
		if (
			preg_match('/./u', 'e') === 1					// PCRE must support UTF-8
			AND function_exists('iconv')					// iconv must be installed
			AND ini_get('mbstring.func_overload') != 1		// Multibyte string function overloading cannot be enabled
			// AND $CFG->item('charset') == 'UTF-8'			// Application charset must be UTF-8
			)
		{
			// set internal encoding for multibyte string functions if necessary
			// and set a flag so we don't have to repeatedly use extension_loaded()
			// or function_exists()
			if (extension_loaded('mbstring'))
			{
				mb_internal_encoding('UTF-8');
			}
			else
			{
			}
		}
		else
		{
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Clean UTF-8 strings
	 *
	 * Ensures strings are UTF-8
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function clean_string($str)
	{
		if ($this->_is_ascii($str) === FALSE){
			$str = @iconv('UTF-8', 'UTF-8//IGNORE', $str);
		}
		return $str;
	}

	// --------------------------------------------------------------------

	/**
	 * Remove ASCII control characters
	 *
	 * Removes all ASCII control characters except horizontal tabs,
	 * line feeds, and carriage returns, as all others can cause
	 * problems in XML
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function safe_ascii_for_xml($str)
	{
		return hc_remove_invisible_characters($str, FALSE);
	}

	// --------------------------------------------------------------------

	/**
	 * Convert to UTF-8
	 *
	 * Attempts to convert a string to UTF-8
	 *
	 * @access	public
	 * @param	string
	 * @param	string	- input encoding
	 * @return	string
	 */
	function convert_to_utf8($str, $encoding)
	{
		if (function_exists('iconv'))
		{
			$str = @iconv($encoding, 'UTF-8', $str);
		}
		elseif (function_exists('mb_convert_encoding'))
		{
			$str = @mb_convert_encoding($str, 'UTF-8', $encoding);
		}
		else
		{
			return FALSE;
		}

		return $str;
	}

	// --------------------------------------------------------------------

	/**
	 * Is ASCII?
	 *
	 * Tests if a string is standard 7-bit ASCII or not
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function _is_ascii($str)
	{
		return (preg_match('/[^\x00-\x7F]/S', $str) == 0);
	}

	// --------------------------------------------------------------------

	public function clean_input( $input )
	{
		if( is_array($input) ){
			if( count($input) > 0 ){
				foreach ($input as $key => $val){
					$input[$this->clean_string($key)] = $this->clean_string($val);
				}
			}
		}
		elseif( is_string($input) ) {
			$input = $this->clean_string($input);
		}
		return $input;
	}
}
// End Utf8 Class

/* End of file Utf8.php */
/* Location: ./system/core/Utf8.php */