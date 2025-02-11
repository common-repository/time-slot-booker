<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Users_Wordpress_Index_View_Layout_HC_MVC
{
	public function header()
	{
		$return = HCM::__('Users');
		$return = NULL;

		return $return;
	}

	public function menubar()
	{
		$return = array();

		// $return['settings'] = $this->app->make('/html/ahref')
			// ->to('/users.wordpress.conf')
			// ->add( $this->app->make('/html/icon')->icon('cog') )
			// ->add( HCM::__('Settings') )
			// ;

		if( current_user_can('create_users') ){
			$link = admin_url( 'user-new.php' );
			$return['new'] = $this->app->make('/html/ahref')
				->to($link)
				->add( $this->app->make('/html/icon')->icon('plus') )
				->add( HCM::__('Add New') )
				;
		}

		$return = $this->app
			->after( array($this, __FUNCTION__), $return )
			;

		return $return;
	}

	public function render( $content )
	{
		$header = $this->header();
		$menubar = $this->menubar();

		$out = $this->app->make('/layout/view/content-header-menubar')
			->set_content( $content )
			->set_header( $header )
			->set_menubar( $menubar )
			;

		return $out;
	}
}