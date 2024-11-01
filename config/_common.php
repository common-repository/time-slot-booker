<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
$config['app_version'] = '1.0.2';
$config['dbprefix_version'] = 'v1';

$config['modules'] = array(
	'app',
	'api2',
	'time',
	'utf8',
	'http',
	'html',
	'input',
	'form',
	'validate',
	'security',
	'encrypt',
	'session',

	'msgbus',
	'flashdata',
	'layout',
	'root',
	'setup',
	'acl',
	'icons',
	'icons_dashicons',

	'commands',

	'conf',
	'auth',

	'datetime',
	'datepicker',

	'users',

	'ormrelations',
	'modelsearch',

////////////////
	'settings',
	'availability',
	'bookings',
	'orders',
	'schedule',
	'email',
	'notify',
	'notify_email',
	'front',
	);
