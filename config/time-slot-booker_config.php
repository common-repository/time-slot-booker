<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
// $config['after']['/acl/roles'][] = function( $app, $return )
// {
	// $return = array(
		// 'admin'		=> HCM::__('Administrator'),
		// 'manager'	=> HCM::__('Manager'),
		// );
	// return $return;
// };
$config['after']['/settings/view/layout->tabs'][] = function( $app, $return, $calendar )
{
	$calendar_id = $calendar['id'];

	$key = 'availability/regular/' . $calendar_id;
	$return[$key] = array( $key, HCM::__('Regular Availability') );

	$key = 'availability/custom/' . $calendar_id;
// count how many custom options
	$command = $app->make('/availability/commands/read');
	$count_args = array();
	$count_args[] = 'count';
	$count_args[] = array('calendar_id', '=', $calendar_id);
	$count_args[] = array('applied_on_date', '<>', NULL);
	$count_args[] = array('groupby', 'applied_on_date');

	$count_by_date = $command
		->execute( $count_args )
		;

	$label = HCM::__('Custom Availability');
	if( $count_by_date ){
		$label .= ' [' . count($count_by_date) . ']';
	}

	$return[$key] = array( $key, $label );
	return $return;
};
$availability_msg = function( $app )
{
	$msg_key = 'availability-update';
	$msgbus = $app->make('/msgbus/lib');

	$msg = HCM::__('Availability Updated');
	$msgbus->add('message', $msg, $msg_key, TRUE);
};

$config['after']['/availability/commands/create'][] = $availability_msg;
$config['after']['/availability/commands/update'][] = $availability_msg;
$config['after']['/availability/commands/delete'][] = $availability_msg;

$config['after']['/orders/commands/create'][] = function( $app )
{
	$msg_key = 'orders-create';
	$msgbus = $app->make('/msgbus/lib');

	$msg = HCM::__('New Booking Order Created');
	$msgbus->add('message', $msg, $msg_key, TRUE);
};

$config['after']['/orders/commands/update'][] = function( $app, $return )
{
	$msg_key = 'orders-update';
	$msgbus = $app->make('/msgbus/lib');

	$msg = HCM::__('Order Updated');
	$msgbus->add('message', $msg, $msg_key, TRUE);
};

$config['after']['/orders/commands/delete'][] = function( $app, $return )
{
	$msg_key = 'orders-delete';
	$msgbus = $app->make('/msgbus/lib');

	$msg = HCM::__('Order Deleted');
	$msgbus->add('message', $msg, $msg_key, TRUE);
};
$config['after']['/orders/commands/update'][] = function( $app, $command )
{
	$cm = $app->make('/commands/manager');

	$new = $cm->results( $command );
	$before = $cm->before( $command );

	$events = array();

	// status changed to confirmed
	if( $new['status'] == 'confirmed' ){
		if( isset($before['status']) ){
			$events['order-confirmed'] = 1;
		}
		if( isset($before['bookings']) ){
			$events['order-confirmed'] = 1;
		}
	}

	// status changed to pending
	if( $new['status'] == 'pending' ){
		if( isset($before['status']) ){
			$events['order-pending'] = 1;
		}
		if( isset($before['bookings']) ){
			$events['order-pending'] = 1;
		}
	}

	// status changed to cancelled
	if( $new['status'] == 'cancelled' ){
		if( isset($before['status']) ){
			$events['order-cancelled'] = 1;
		}
	}

// load full
	$args = array();
	$args[] = $new['id'];
	$args[] = array('with', '-all-');
	$order = $app->make('/orders/commands/read')
		->execute( $args )
		;

	foreach( $events as $event => $one ){
		$app->make('/orders.notify/admin')
			->execute( $order, $event )
			;
		$app->make('/orders.notify/customer')
			->execute( $order, $event )
			;
	}
};

$config['after']['/orders/commands/create'][] = function( $app, $command )
{
	$cm = $app->make('/commands/manager');
	$new = $cm->results( $command );

	if( ! array_key_exists('status', $new) ){
		return;
	}

	$events = array();

	if( $new['status'] == 'confirmed' ){
		$events['order-confirmed'] = 1;
	}
	if( $new['status'] == 'pending' ){
		$events['order-pending'] = 1;
	}

	if( ! $events ){
		return;
	}

// load full
	$args = array();
	$args[] = $new['id'];
	$args[] = array('with', '-all-');
	$order = $app->make('/orders/commands/read')
		->execute( $args )
		;

	foreach( $events as $event => $one ){
		$app->make('/orders.notify/admin')
			->execute( $order, $event )
			;
		$app->make('/orders.notify/customer')
			->execute( $order, $event )
			;
	}
};

$config['after']['/settings/view/layout->tabs'][] = function( $app, $return, $calendar )
{
	$key = 'orders.notify-email/templates/' . $calendar['id'];
	$return[$key] = array( $key, HCM::__('Email Notifications') );

	return $return;
};

$config['after']['/orders.notify/admin'][] = function( $app, $order, $event, $admins )
{
	$notifier = $app->make('/orders.notify-email/admin');
	foreach( $admins as $admin ){
		$notifier->execute( $order, $event, $admin );
	}
	return;
};

$config['after']['/orders.notify/customer'][] = function( $app, $order, $event, $customer )
{
	$notifier = $app->make('/orders.notify-email/customer');
	$notifier->execute( $order, $event, $customer );
	return;
};
$config['after']['/layout/top-menu'][] = function( $app, $return )
{
	$label = 'TimeSlotBooker Pro';

	$link = $app->make('/html/ahref')
		->to( 'http://www.wptimeslotbooker.com/order/' )
		->set_outside( TRUE )
		->add( $app->make('/html/icon')->icon('star') )
		->add( $label )
		->add_attr( 'target', '_blank' )
		;
	$return['promo'] = array( $link, 200 );

	return $return;
};

$config['after']['/layout/view/content-header-menubar'][] = function( $app, $return )
{
	$promo = $app->make('/promo.wordpress/view');

	$return = $app->make('/html/list')
		->set_gutter(2)
		->add( $promo )
		->add( $return )
		;

	return $return;
};
$config['after']['/layout/top-menu'][] = function( $app, $return )
{
	$link = $app->make('/html/ahref')
		->to('/schedule')
		->add( $app->make('/html/icon')->icon('calendar') )
		->add( HCM::__('Schedule') )
		;
	$return['schedule'] = $link;
	return $return;
};
$config['after']['/root/link'][] = function( $app, $return )
{
	if( ! $return ){
		return $return;
	}

	// check module
	$module = 'schedule';
	if( ($module != $return) && (substr($return, 0, strlen($module . '/')) != $module . '/') ){
		return $return;
	}

	// check admin
	$logged_in = $app->make('/auth/lib')
		->logged_in()
		;
	$is_admin = $app->make('/acl/roles')
		->has_role( $logged_in, 'admin')
		;
	if( $is_admin ){
		return $return;
	}

	$return = FALSE;
	return $return;
};
$config['after']['/layout/top-menu'][] = function( $app, $return )
{
	$link = $app->make('/html/ahref')
		->to('/settings')
		->add( $app->make('/html/icon')->icon('clock') )
		->add( HCM::__('Settings') )
		;
	$return['settings'] = $link;
	return $return;
};

$config['after']['/settings/view/layout->tabs'][] = function( $app, $return, $calendar )
{
	$return['settings'] = array( 'settings/' . $calendar['id'], HCM::__('Booking Settings') );
	return $return;
};
$config['after']['/root/link'][] = function( $app, $return )
{
	if( ! $return ){
		return $return;
	}

	// check module
	$module = 'settings';
	if( ($module != $return) && (substr($return, 0, strlen($module . '/')) != $module . '/') ){
		return $return;
	}

	// check admin
	$logged_in = $app->make('/auth/lib')
		->logged_in()
		;
	$is_admin = $app->make('/acl/roles')
		->has_role( $logged_in, 'admin')
		;
	if( $is_admin ){
		return $return;
	}

	$return = FALSE;
	return $return;
};
$settings_msg = function( $app, $return )
{
	$msg_key = 'settings-update';
	$msgbus = $app->make('/msgbus/lib');

	if( (! $return) OR isset($return['errors']) ){
		// $msg = $return['errors'];
		// $msgbus->add('error', $msg, $msg_key);
	}
	else {
		$msg = HCM::__('Settings Updated');
		$msgbus->add('message', $msg, $msg_key);
	}
	return $return;
};

$config['after']['/settings/commands/create'][] = $settings_msg;
$config['after']['/settings/commands/update'][] = $settings_msg;
$config['after']['/settings/commands/delete'][] = $settings_msg;

$config['after']['/app/enqueuer'][] = function( $app, $enqueuer )
{
	$enqueuer
		->register_script( 'hc', 'happ2/assets/js/hc2.js' )

		->register_style( 'hc-start', 'happ2/assets/css/hc-start.css' )
		->register_style( 'hc', 'happ2/assets/css/hc.css' )
		->register_style( 'font', 'https://fonts.googleapis.com/css?family=PT+Sans' )
		;

// enqueue
	$enqueuer
		->enqueue_script( 'hc' )
		;
};
$config['after']['/app/lib->isme'][] = function( $app, $return )
{
	if( ! $return ){
		return $return;
	}

	global $pagenow;
	$return = FALSE;

	$pages = array('edit.php', 'post.php', 'admin.php');
	$my_type_prefix = $app->app_short_name() . '-';
	$my_pages = $app->app_pages();

	if( ! is_admin() ){
		return $return;
	}

	if( ! in_array($pagenow, $pages) ){
		return $return;
	}

	switch( $pagenow ){
		case 'edit.php':
			$check_post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : '';
			if( (substr($check_post_type, 0, strlen($my_type_prefix)) != $my_type_prefix) ){
				return $return;
			}
			break;

		case 'post.php':
			global $post;
			$check_post_type = isset($post->post_type) ? $post->post_type : '';
			if( (substr($check_post_type, 0, strlen($my_type_prefix)) != $my_type_prefix) ){
				return $return;
			}
			break;

		case 'admin.php':
			$check_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
			if( ! in_array($check_page, $my_pages) ){
				return $return;
			}
			break;

		default:
			return $return;
			break;
	}

	$return = TRUE;
	return $return;
};
$config['after']['/layout/top-menu'][] = function( $app, $return )
{
	$link = $app->make('/html/ahref')
		->to('/conf')
		->add( HCM::__('Configuration') )
		;
	$return['conf'] = array($link, 100);

	return $return;
};

$config['after']['/root/link'][] = function( $app, $return )
{
	if( ! $return ){
		return $return;
	}

	// check module
	// also check if it ends with .conf
	$module = 'conf';

	$is_me = FALSE;

	if( ($module == $return) OR (substr($return, 0, strlen($module . '/')) == $module . '/') ){
		$is_me = TRUE;
	}
	else {
		$dotmodule = '.' . $module;
		if( substr($return, -strlen($dotmodule)) == $dotmodule ){
			$is_me = TRUE;
		}
		if( strpos($return, $dotmodule . '/') !== FALSE ){
			$is_me = TRUE;
		}

		$dotmodule = $module . '.';
		if( substr($return, 0, strlen($dotmodule)) == $dotmodule ){
			$is_me = TRUE;
		}
	}

	if( ! $is_me ){
		return $return;
	}

	// check if admin
	$logged_in = $app->make('/auth/lib')
		->logged_in()
		;
	$is_admin = $app->make('/acl/roles')
		->has_role( $logged_in, 'admin')
		;
	if( $is_admin ){
		return $return;
	}

	$return = FALSE;
	return $return;
};
$config['after']['/conf/model->save'][] = function( $app, $return )
{
	$msg = HCM::__('Settings Updated');
	$msgbus = $app->make('/msgbus/lib');
	$msgbus->add('message', $msg);
};

$config['after']['/app/enqueuer'][] = function( $app, $enqueuer )
{
	$enqueuer
		->register_script( 'datepicker', 'happ2/modules/datepicker/assets/js/hc-datepicker2.js' )
		->register_style( 'datepicker', 'happ2/modules/datepicker/assets/css/hc-datepicker2.css' )
		;
};
$config['after']['/conf/view/layout->tabs'][] = function( $app, $return )
{
	$return['datetime'] = array( 'datetime.conf', HCM::__('Date and Time') );
	return $return;
};

$config['after']['/http/view/response->prepare_redirect'][] = function( $app, $return )
{
	$msgbus = $app->make('/msgbus/lib');
	$session = $app->make('/session/lib');

	$msg = $msgbus->get('message');
	if( $msg ){
		$session->set_flashdata('message', $msg);
	}
	$error = $msgbus->get('error');
	if( $error ){
		$session->set_flashdata('error', $error);
	}
	$warning = $msgbus->get('warning');
	if( $warning ){
		$session->set_flashdata('warning', $warning);
	}
	$debug = $msgbus->get('debug');
	if( $debug ){
		$session->set_flashdata('debug', $debug);
	}

	return $return;
};
$config['after']['/layout/view/body->content'][] = function( $app, $return )
{
	// in admin show by admin notices
	if( is_admin() ){
		return;
	}

	$flash_out = $app->make('/flashdata.layout/view')
		->render()
		;

	if( ! $flash_out ){
		return;
	}

	$return = $app->make('/html/list')
		->set_gutter(1)
		->add( $flash_out )
		->add( $return )
		;

	return $return;
};
$config['after']['/html/icon'][] = function( $app, $return, $src )
{
	$convert = array(
		'networking'	=> 'networking',
		'star-o'	=> 'star-empty',
		// 'plus'		=> 'plus-alt', // simple plus appears off center vertically
		'cog'		=> 'admin-generic',
		'user'		=> 'admin-users',
		'group'		=> 'groups',
		'times'		=> 'dismiss',
		'check'		=> 'yes',
		'status'	=> 'post-status',
		'list'		=> 'editor-ul',
		'history'	=> 'book',
		'exclamation'	=> 'warning',
		'printer'		=> 'media-text',
		'home'			=> 'admin-home',
		'star'			=> 'star-filled',

		'purchase'		=> 'products',
		'sale'			=> 'cart',
		'inventory'		=> 'admin-page',
		'copy'			=> 'admin-page',
		'chart'			=> 'chart-bar',
		'message'		=> 'email',
		'holidays'		=> 'palmtree',
		'connection'	=> 'admin-links',
		'view'			=> 'visibility',
		'password'		=> 'admin-network',

		'confirmed'		=> 'star-filled',
		'pending'		=> 'star-half',
		'tools'			=> 'admin-tools',
	);

	$return = isset($convert[$return]) ? $convert[$return] : $return;

	if( $return && strlen($return) ){
		if( substr($return, 0, 1) == '&' ){
			$return = $app->make('/html/element')->tag('span')
				->add( $return )
				->add_attr('class', 'hc-mr1')
				->add_attr('class', 'hc-ml1')
				->add_attr('class', 'hc-char')
				;
		}
		else {
			$return = $app->make('/html/element')->tag('i')
				->add_attr('class', 'dashicons')
				->add_attr('class', 'dashicons-' . $return)
				->add_attr('class', 'hc-dashicons')
				;
		}
	}

	return $return;
};

$config['after']['/app/enqueuer'][] = function( $app, $enqueuer )
{
	$enqueuer
		->enqueue_style( 'hc' )
		;
};

$config['after']['/app/enqueuer->register_script'][] = function( $app, $handle, $path )
{
	$wp_handle = 'hc2-script-' . $handle;
	$path = $app->make('/layout.wordpress/path')
		->full_path( $path )
		;
	wp_register_script( $wp_handle, $path, array('jquery') );
};

$config['after']['/app/enqueuer->register_style'][] = function( $app, $handle, $path )
{
	$skip = array('reset', 'style', 'form', 'font', 'hc-start');
	if( in_array($handle, $skip) ){
		return;
	}

	$wp_handle = 'hc2-style-' . $handle;
	$path = $app->make('/layout.wordpress/path')
		->full_path( $path )
		;
	wp_register_style( $wp_handle, $path );
};

$config['after']['/app/enqueuer->enqueue_script'][] = function( $app, $handle )
{
	$wp_handle = 'hc2-script-' . $handle;
// echo "ENQUEUEWP '$wp_handle'<br>";
	wp_enqueue_script( $wp_handle );
};

$config['after']['/app/enqueuer->enqueue_style'][] = function( $app, $handle )
{
	$wp_handle = 'hc2-style-' . $handle;
	wp_enqueue_style( $wp_handle );
};

$config['after']['/app/enqueuer->localize_script'][] = function( $app, $handle, $params )
{
	$wp_handle = 'hc2-script-' . $handle;
	$js_var = 'hc2_' . $handle . '_vars'; 
	wp_localize_script( $wp_handle, $js_var, $params );
};

$config['after']['/layout/view/body'][] = function( $app )
{
	$enqueuer = $app->make('/app/enqueuer');
	return;
};

$config['after']['/form/helper->render'][] = function( $app, $return )
{
	$security = $app->make('/security/lib');

	$csrf_name = $security->get_csrf_token_name();
	$csrf_value = $security->get_csrf_hash();

	if( strlen($csrf_name) && strlen($csrf_value) ){
		$hidden = $app->make('/form/hidden')
			->render( $csrf_name, $csrf_value )
			;

		$return->add(
			$app->make('/html/element')->tag('div')
				->add_attr('style', 'display:none')
				->add( $hidden )
			);
	}

	return $return;
};
$config['after']['/root/link'][] = function( $app, $return )
{
	if( ! $return ){
		return $return;
	}

	// check module
	$module = 'users';

	$is_me = FALSE;

	if( ($module == $return) OR (substr($return, 0, strlen($module . '/')) == $module . '/') ){
		$is_me = TRUE;
	}
	else {
		$dotmodule = $module . '.';
		if( substr($return, 0, strlen($dotmodule)) == $dotmodule ){
			$is_me = TRUE;
		}
	}

	if( ! $is_me ){
		return $return;
	}

	// check admin
	$logged_in = $app->make('/auth/lib')
		->logged_in()
		;
	$is_admin = $app->make('/acl/roles')
		->has_role( $logged_in, 'admin')
		;
	if( $is_admin ){
		return $return;
	}

	$return = FALSE;
	return $return;
};
// $config['after']['/layout/top-menu'][] = function( $app, $return )
// {
	// $link = $app->make('/html/ahref')
		// ->to('/users.wordpress')
		// ->add( $app->make('/html/icon')->icon('user') )
		// ->add( HCM::__('Users') )
		// ;
	// $return['users'] = array( $link, 90 );

	// return $return;
// };

$config['after']['/conf/view/layout->tabs'][] = function( $app, $return )
{
	$return['users'] = array( 'users.wordpress', HCM::__('Users') );
	return $return;
};

$config['after']['/users/index/view/layout->menubar'][] = function( $app, $return )
{
	$return['settings'] = $app->make('/html/ahref')
		->to('/users.wordpress.conf')
		->add( $app->make('/html/icon')->icon('cog') )
		->add( HCM::__('Settings') )
		;

	if( current_user_can('create_users') ){
		$link = admin_url( 'user-new.php' );
		$return['add'] = $app->make('/html/ahref')
			->to($link)
			->add( $app->make('/html/icon')->icon('plus') )
			->add( HCM::__('Add New') )
			;
	}

	return $return;
};

$config['after']['/conf/view/layout->tabs'][] = function( $app, $return )
{
	$return['wordpress-users'] = array( 'users.wordpress.conf', HCM::__('Roles') );
	return $return;
};
$config['after']['/root/link'][] = function( $app, $return )
{
	if( ! $return ){
		return $return;
	}

	// check module
	$module = 'users.wordpress.conf';
	if( ($module != $return) && (substr($return, 0, strlen($module . '/')) != $module . '/') ){
		return $return;
	}

	// check admin
	$wp_always_admin = $app->make('/acl.wordpress/roles')->always_admin();
	$wp_user = wp_get_current_user();
	if( array_intersect($wp_always_admin, (array) $wp_user->roles) ){
		return $return;
	}

	$return = FALSE;
	return $return;
};
$config['route'][''] = array( '/calendars/select/controller', '/schedule/_ID_' );

$config['route']['availability'] = array( '/calendars/select/controller', '/availability/regular/_ID_' );

$config['route']['availability/regular/{calendar}']			= '/availability/regular/controller';
$config['route']['availability/regular/update/{calendar}']	= '/availability/regular/controller-update';

$config['route']['availability/custom/{calendar}']			= '/availability/custom/controller';
$config['route']['availability/custom/update/{calendar}']	= '/availability/custom/controller-update';

$config['route']['availability/custom/{calendar}/new']		= '/availability/custom/new/controller';
$config['route']['availability/custom/add/{calendar}']		= '/availability/custom/new/controller-add';

$config['route']['availability/custom/{calendar}/delete/{date}']		= '/availability/custom/delete/controller';


$config['route']['front'] = array( '/calendars/select/controller', '/front/_ID_' );

$config['route']['front/{calendar}'] = '/front/controller';
$config['route']['front/data/{calendar}/{date}']	= '/front/controller-data';

$config['route']['front/new/{calendar}']	= '/front/new/controller';
$config['route']['front/add/{calendar}']	= '/front/new/controller/add';


$config['route']['orders']				= '/orders/index/controller';
$config['route']['orders/{id}/edit']	= '/orders/edit/controller';
$config['route']['orders/{id}/update']	= '/orders/edit/controller/update';
$config['route']['orders/{id}/delete']	= '/orders/delete/controller';

$config['route']['orders/add/{calendar}']	= '/orders/new/controller/add';
$config['route']['orders/new/{calendar}']	= '/orders/new/controller';

$config['route']['orders.notify-email/templates/{calendar}']	= '/orders.notify-email/templates/controller';
$config['route']['orders.notify-email/templates/update/{calendar}']	= '/orders.notify-email/templates/controller-update';

$config['route']['schedule'] = array( '/calendars/select/controller', '/schedule/_ID_' );

$config['route']['schedule/{calendar}'] = '/schedule/controller';
$config['route']['schedule/data/{calendar}/{date}']	= '/schedule/controller-data';

$config['route']['settings'] = array( '/calendars/select/controller', '/settings/_ID_' );

$config['route']['settings/{calendar}'] 		= '/settings/edit/controller';
$config['route']['settings/update/{calendar}']	= '/settings/edit/controller-update';

$config['route']['acl/notallowed'] = '/acl.wordpress/notallowed';
$auth_wordpress_login_redirect = function( $app ){
	$redirect_to = wp_login_url();
	return $app->make('/http/view/response')
		->set_redirect($redirect_to) 
		;
};

$config['route']['auth/login'] = $auth_wordpress_login_redirect;
$config['route']['login'] = $auth_wordpress_login_redirect;

$config['route']['users.wordpress'] = '/users.wordpress/index/controller';
$config['migration']['availability'] = 1;
$config['migration']['bookings'] = 1;
$config['migration']['calendarsone'] = 1;
$config['migration']['orders'] = 1;
$config['migration']['conf'] = 1;

$config['migration']['ormrelations'] = 2;
$config['migration']['users.wordpress.conf'] = 1;

$config['alias']['/calendars/commands/read'] = '/calendarsone/commands/read';
$config['alias']['/calendars/select/controller'] = '/calendarsone/select/controller';

$config['alias']['/auth/lib'] = '/auth.wordpress/lib';
$config['alias']['/email'] = '/email.wordpress/transport';

$config['alias']['/http/lib/client'] = 'lib/client';
$config['alias']['/users/commands/read'] = '/users.wordpress/commands/read';

$config['bootstrap'][] = function( $app )
{
	$app->make('/front.wordpress/handler')
		->start()
		;
};
$config['bootstrap'][] = function( $app )
{
	// $view = $app->make('/promo.wordpress/view/notices');
	// add_action( 'admin_notices', array($view, 'render') );
};
$config['bootstrap'][] = function( $app )
{
	$is_me = $app->make('/app/lib')
		->isme()
		;
	if( $is_me ){
		$enqueuer = $app->make('/app/enqueuer');
	}
};
$config['bootstrap'][] = function( $app )
{
	$is_me = $app->make('/app/lib')
		->isme()
		;
	if( ! $is_me ){
		return;
	}

	$view = $app->make('/flashdata.wordpress.layout/view-admin-notices');
	add_action( 'admin_notices', array($view, 'render') );
};
$config['bootstrap'][] = function( $app )
{
	$setup = $app->db->table_exists('migrations');
	if( ! $setup ){
		$app->migration->init();
		if( ! $app->migration->current()){
			hc_show_error( $app->migration->error_string());
		}
	}
};
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

$config['settings']['notify-email:order-pending-customer:active'] = 1;
$config['settings']['notify-email:order-pending-customer:subject'] = 'Booking Pending {REFNO}';
$config['settings']['notify-email:order-pending-customer:body'] = '
Thank you for your booking request.
We kindly ask you to wait a little before we review your request. We will notify you when your booking is confirmed.

Your booking reference:
{REFNO}

Your booking details:
{BOOKINGS}
';

$config['settings']['notify-email:order-confirmed-customer:active'] = 1;
$config['settings']['notify-email:order-confirmed-customer:subject'] = 'Booking Confirmed {REFNO}';
$config['settings']['notify-email:order-confirmed-customer:body'] = '
Thank you for your booking request. Your booking is now confirmed.

Your booking reference:
{REFNO}

Your booking details:
{BOOKINGS}
';

$config['settings']['notify-email:order-cancelled-customer:active'] = 1;
$config['settings']['notify-email:order-cancelled-customer:subject'] = 'Booking Cancelled {REFNO}';
$config['settings']['notify-email:order-cancelled-customer:body'] = '
Your booking was cancelled.

Your booking reference:
{REFNO}

Your booking details:
{BOOKINGS}
';

$config['settings']['notify-email:order-pending-admin:active'] = 1;
$config['settings']['notify-email:order-pending-admin:subject'] = 'Booking Requires Approval {REFNO}';
$config['settings']['notify-email:order-pending-admin:body'] = '
There is a new booking request for you.

Booking reference:
{REFNO}

Booking details:
{BOOKINGS}

Customer details:
{CUSTOMER}
';

$config['settings']['notify-email:order-confirmed-admin:active'] = 1;
$config['settings']['notify-email:order-confirmed-admin:subject'] = 'Booking Confirmed {REFNO}';
$config['settings']['notify-email:order-confirmed-admin:body'] = '
This booking request for you is now confirmed.

Booking reference:
{REFNO}

Booking details:
{BOOKINGS}

Customer details:
{CUSTOMER}
';

$config['settings']['notify-email:order-cancelled-admin:active'] = 1;
$config['settings']['notify-email:order-cancelled-admin:subject'] = 'Booking Cancelled {REFNO}';
$config['settings']['notify-email:order-cancelled-admin:body'] = '
This booking request for you was cancelled.

Booking reference:
{REFNO}

Booking details:
{BOOKINGS}

Customer details:
{CUSTOMER}
';

$config['settings']['settings:start_status'] = 'pending';
$config['settings']['settings:max_slots'] = 3;
$config['settings']['settings:min_from_now'] = '1 days';
$config['settings']['settings:max_from_now'] = '3 months';
$config['settings']['datetime:date_format'] = 'j M Y';
$config['settings']['datetime:time_format'] = 'g:ia';
$config['settings']['datetime:week_starts'] = 0;
$wp_roles = new WP_Roles();
$wordpress_roles = $wp_roles->get_names();

foreach( $wordpress_roles as $role_value => $role_name ){
	$default = 1;

	switch( $role_value ){
		case 'administrator':
		case 'developer':
			$config['settings']['wordpress_users:role_' . $role_value ] = 1;
			break;

		default:
			$config['settings']['wordpress_users:role_' . $role_value ] = 0;
			break;
	}
}
