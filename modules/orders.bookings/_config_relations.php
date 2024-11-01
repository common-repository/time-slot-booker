<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
$config['relations']['orders']['bookings'] = array( 
	'their_class'		=> 'bookings',
	'relation_name'		=> 'booking_to_order',
	'their_field'		=>	'from_id',
	'many'				=>	TRUE,
	);
$config['relations']['bookings']['order'] = array( 
	'their_class'		=> 'orders',
	'relation_name'		=> 'booking_to_order',
	'their_field'		=>	'to_id',
	'many'				=>	FALSE,
	);
