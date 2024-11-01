<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
$config['relations']['orders']['calendar'] = array( 
	'their_class'		=> 'calendarsone',
	'relation_name'		=> 'order_to_calendar',
	'their_field'		=>	'to_id',
	'many'				=>	FALSE,
	);
$config['relations']['calendars']['orders'] = array( 
	'their_class'		=> 'orders',
	'relation_name'		=> 'order_to_calendar',
	'their_field'		=>	'from_id',
	'many'				=>	TRUE,
	);
