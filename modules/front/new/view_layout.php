<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Front_New_View_Layout_TSB_HC_MVC
{
	public function header( $calendar )
	{
		$return = isset($calendar['title']) ? $calendar['title'] : NULL;
		return $return;
	}

	public function menubar( $calendar )
	{
		$return = array();

		$return = $this->app
			->after( array($this, __FUNCTION__), $return )
			;

		return $return;
	}

	public function render( $content, $calendar )
	{
		$header = $this->header( $calendar );
		$menubar = $this->menubar( $calendar );

		$out = $this->app->make('/layout/view/content-header-menubar')
			->set_content( $content )
			->set_header( $header )
			->set_menubar( $menubar )
			;

		return $out;
	}
}