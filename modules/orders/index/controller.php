<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Index_Controller_TSB_HC_MVC
{
	public function execute()
	{
		$uri = $this->app->make('/http/uri');

		$search = $uri->param('search');
		$page = $uri->param('page');
		if( ! $page ){
			$page = 1;
		}
		$per_page = 10;

		$command = $this->app->make('/orders/commands/read');
		$count_args = array();
		$count_args[] = 'count';
		if( $search ){
			$count_args[] = array('search', $search);
		}
		$total_count = $command
			->execute( $count_args )
			;

		$limit = $per_page;

		if( $total_count > $per_page ){
			$pager = $this->app->make('/html/pager')
				->set_total_count( $total_count )
				->set_per_page( $per_page )
				;
			if( $page > $pager->number_of_pages() ){
				$page = $pager->number_of_pages();
			}
		}

		$entries = array();

		if( $total_count ){
			$args = array();
			$args[] = array('with', '-all-');

			if( $page && $page > 1 ){
				$args[] = array('limit', $per_page, ($page - 1) * $per_page);
			}
			else {
				$args[] = array('limit', $per_page);
			}
			if( $search ){
				$args[] = array('search', $search);
			}

			$entries = $this->app->make('/orders/commands/read')
				->execute( $args )
				;
		}

		$view = $this->app->make('/orders/index/view')
			->render($entries, $total_count, $page, $search, $per_page)
			;
		$view = $this->app->make('/orders/index/view/layout')
			->render($view)
			;
		$view = $this->app->make('/layout/view/body')
			->set_content($view)
			;
		return $this->app->make('/http/view/response')
			->set_view($view)
			;
	}
}