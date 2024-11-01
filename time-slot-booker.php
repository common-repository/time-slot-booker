<?php
/*
Plugin Name: Time Slot Booker
Plugin URI: http://www.wptimeslotbooker.com/
Description: Time slot booking plugin
Version: 1.0.2
Author: hitcode.com
Author URI: http://www.hitcode.com/
Text Domain: time-slot-booker
Domain Path: /languages/
*/

if (! defined('ABSPATH')) exit; // Exit if accessed directly

if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
	add_action( 'admin_notices', create_function( '', "echo '<div class=\"error\"><p>".__('Time Slot Booker requires PHP 5.3 to function properly. Please upgrade PHP or deactivate Time Slot Booker.', 'timeslotbooker') ."</p></div>';" ) );
	return;
}

if( file_exists(dirname(__FILE__) . '/config.php') ){
	include_once( dirname(__FILE__) . '/config.php' );
	$happ_path = NTS_DEVELOPMENT2;
}
else {
	$happ_path = dirname(__FILE__) . '/happ2';
}

include_once( $happ_path . '/lib-wp/hcWpBase6.php' );

class TimeSlotBookerHC extends hcWpBase6
{
	public function __construct()
	{
		parent::__construct(
			array('time-slot-booker', 'tsb'),	// app
			__FILE__,	// path,
			'',			// hc product,
			'time-slot-booker',	// slug
			'timeslotbooker'		// db prefix
			);

		add_action(	'init', array($this, '_this_init') );
	}

	public function _this_init()
	{
		$this->hcapp_start();
	}
}

$tsb = new TimeSlotBookerHC();
