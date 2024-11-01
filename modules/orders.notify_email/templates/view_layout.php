<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Calendars_Select_View_Layout_TSB_HC_MVC
{
	public function header()
	{
		$return = HCM::__('Select Calendar');
		return $return;
	}

	public function menubar()
	{
		$return = array();
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