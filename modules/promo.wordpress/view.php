<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Promo_Wordpress_View_TSB_HC_MVC
{
	public function render()
	{
		$is_me = $this->app->make('/app/lib')->isme();

		if( ! $is_me ){
			return;
		}

		ob_start();
		require( dirname(__FILE__) . '/view.html.php' );
		$out = ob_get_contents();
		ob_end_clean();

		$out = $this->app->make('/html/element')->tag('div')
			->add( $out )
			->add_attr('class', 'update-nag')
			->add_attr('class', 'hc-block' )
			;

		return $out;
	}
}