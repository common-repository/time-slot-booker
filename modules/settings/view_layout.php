<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Settings_View_Layout_TSB_HC_MVC
{
	public function tabs( $calendar )
	{
		$return = array();

		$return = $this->app
			->after( array($this, __FUNCTION__), $return, $calendar )
			;

		return $return;
	}

	public function header( $calendar )
	{
		$return = isset($calendar['title']) ? $calendar['title'] : NULL;
		return $return;
	}

	public function menubar( $calendar, $current_tab = NULL )
	{
		$return = array();
		$tabs = $this->tabs( $calendar );

		reset( $tabs );
		foreach( $tabs as $tab_key => $tab ){
			if( is_array($tab) ){
				$tab_link = array_shift( $tab );
				$tab_label = array_shift( $tab );
				if( substr($tab_link, 0, 1) != '/' ){
					$tab_link = '/' . $tab_link;
				}
			}
			else {
				$tab_link = '/settings/' . $tab_key;
				$tab_label = $tab;
			}

			$link = $this->app->make('/html/ahref')
				->to( $tab_link )
				->add( $tab_label )
				;

			if( trim($tab_link, '/') == $current_tab ){
				$link
					->add_attr('class', 'hc-theme-btn-submit')
					->add_attr('class', 'hc-theme-btn-primary')
					;
			}

			$return[ $tab_key ] = $link;
		}

		return $return;
	}

	public function render( $content, $calendar, $current_tab = NULL )
	{
		$this->app->make('/layout/top-menu')
			->set_current( 'settings' )
			;

		$menubar = $this->menubar( $calendar, $current_tab );
		$header = $this->header( $calendar, $current_tab );

		$out = $this->app->make('/layout/view/content-header-menubar')
			->set_content( $content )
			->set_header( $header )
			->set_menubar( $menubar )
			;

		return $out;
	}
}