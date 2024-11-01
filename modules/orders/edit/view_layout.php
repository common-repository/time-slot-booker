<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Edit_View_Layout_TSB_HC_MVC
{
	public function header( $model )
	{
		$return = HCM::__('Order');

		$p = $this->app->make('/orders/presenter');
		$return .= ' ' . $p->present_refno($model);

		return $return;
	}

	public function menubar( $model )
	{
		$return = array();

		$return = $this->app
			->after( array($this, __FUNCTION__), $return, $model )
			;

		return $return;
	}

	public function render( $content, $model )
	{
		$menubar = $this->menubar($model);
		$header = $this->header($model);

		$out = $this->app->make('/layout/view/content-header-menubar')
			->set_content( $content )
			->set_header( $header )
			->set_menubar( $menubar )
			;

		return $out;
	}
}