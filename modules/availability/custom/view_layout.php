<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Availability_Custom_View_Layout_TSB_HC_MVC
{
	public function header( $calendar )
	{
		$return = NULL;
		return $return;
	}

	public function menubar( $calendar )
	{
		$return = array();

		$return['new'] = $this->app->make('/html/ahref')
			->to('/availability/custom/' . $calendar['id'] . '/new')
			->add( $this->app->make('/html/icon')->icon('plus') )
			->add( HCM::__('Add New') )
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