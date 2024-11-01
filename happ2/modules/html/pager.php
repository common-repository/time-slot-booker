<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Html_Pager_HC_MVC
{
	protected $total_count;
	protected $per_page = 10;
	protected $current_page = 1;
	protected $to = '-';

	public function set_to( $to )
	{
		$this->to = $to;
		return $this;
	}

	public function render()
	{
		$out = $this->app->make('/html/list-inline')
			->set_mobile(TRUE)
			->set_gutter(1)
			;

		$parts = array();
		$disable = array();

		if( $this->current_page() == 1 ){
			$disable[] = 'first';
			$disable[] = 'previous';
		}
		if( $this->current_page() == 2 ){
			$disable[] = 'first';
		}
		if( $this->current_page() == $this->number_of_pages() ){
			$disable[] = 'next';
			$disable[] = 'last';
		}
		if( $this->current_page() == ($this->number_of_pages() - 1) ){
			$disable[] = 'last';
		}

		$parts_config = array(
			'first'		=> array( '&lt;&lt;',	array('page' => 1) ),
			'previous'	=> array( '&lt;',		array('page' => ($this->current_page() - 1)) ),
			'next'		=> array( '&gt;',		array('page' => ($this->current_page() + 1)) ),
			'last'		=> array( '&gt;&gt;',	array('page' => $this->number_of_pages()) ),
			);


		foreach( $parts_config as $k => $a ){
			if( in_array($k, $disable) ){
				$parts[$k] = $this->app->make('/html/element')->tag('span')
					->add_attr('class', 'hc-muted3')
					->add_attr('class', 'hc-inline-block')
					->add( $a[0] )
					;
			}
			else {
				$parts[$k] = $this->app->make('/html/ahref')
					->to( $this->to, $a[1] )
					->add( $a[0] )
					;
			}

			$parts[$k]
				->add_attr('class', 'hc-theme-btn-submit')
				;
		}

		$parts['current'] = $this->app->make('/html/element')->tag('span')
			->add( $this->current_page() . ' / ' . $this->number_of_pages() )
			->add_attr('class', 'hc-inline-block')
			->add_attr('class', 'hc-btn')
			->add_attr('class', 'hc-p2')
			->add_attr('class', 'hc-m0')
			;

		$show_order = array('first', 'previous', 'current', 'next', 'last');

		foreach( $show_order as $k ){
			if( ! isset($parts[$k]) ){
				continue;
			}
			$out->add( $parts[$k] );
		}

		return $out;
	}

	public function number_of_pages()
	{
		if( ($this->per_page() == 0) || ($this->total_count() == 0) ){
			$return = 1;
		}
		else {
			$return = ceil( $this->total_count() / $this->per_page() );
		}

		return $return;
	}

	public function set_total_count( $total_count )
	{
		$this->total_count = $total_count;
		return $this;
	}
	public function total_count()
	{
		return $this->total_count;
	}
	public function set_per_page( $per_page )
	{
		$this->per_page = $per_page;
		return $this;
	}
	public function per_page()
	{
		return $this->per_page;
	}
	public function set_current_page( $current_page )
	{
		$this->current_page = $current_page;
		return $this;
	}
	public function current_page()
	{
		return $this->current_page;
	}
}