<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Form_Checkbox_Set_HC_MVC implements Form_Input_Interface_HC_MVC
{
	protected $options = array();
	protected $readonly_options = array();

	public function set_options( $options )
	{
		$this->options = $options;
		return $this;
	}

	public function set_readonly_options( $readonly_options )
	{
		$this->readonly_options = $readonly_options;
		return $this;
	}

	public function options()
	{
		return $this->options;
	}

	public function grab( $name, $post )
	{
		$return = $this->app->make('/form/input')
			->grab($name, $post)
			;

		if( ! is_array($return) ){
			if( strlen($return) ){
				$return = array( $return );
			}
			else {
				$return = array();
			}
		}

		return $return;
	}

	public function render( $name, $value = NULL )
	{
		$name = $this->app->make('/form/input')->name($name);
		if( $value && (! is_array($value)) ){
			$value = array( $value );
		}

		$out = $this->app->make('/html/list-inline')
			// ->set_mobile(TRUE)
			->set_gutter(2)
			;

		$id = 'hc2r_' . hc_random();
		$options = $this->options();

		foreach( $options as $k => $v ){
			$this_id = $id . '_' . $k;

			if( $this->readonly_options && in_array($k, $this->readonly_options) ){
				if( ($value !== NULL) && in_array($k, $value) ){
					$this_input = $this->app->make('/html/element')->tag('span')
						->add( $this->app->make('/html/icon')->icon('check') )
						->add_attr('class', 'hc-olive')
						;
				}
				else {
					$this_input = $this->app->make('/html/element')->tag('span')
						->add( $this->app->make('/html/icon')->icon('times') )
						->add_attr('class', 'hc-maroon')
						;
				}
			}
			else {
				$this_input = $this->app->make('/html/element')->tag('input')
					->add_attr('type', 'checkbox' )
					->add_attr('name', $name . '[]' )
					->add_attr('value', $k )
					->add_attr('id', $this_id )
					;

				if( ($value !== NULL) && in_array($k, $value) ){
					$this_input
						->add_attr('checked', 'checked')
						;
				}
			}

			$this_label = $this->app->make('/html/element')->tag('label')
				->add_attr('for', $this_id )
				->add( $v )
				; 

			$this_out = $this->app->make('/html/list-inline')
				->set_mobile(TRUE)
				->set_gutter(0)
				;

			$this_out
				->add( $this_input )
				->add( $this_label )
				;

			$out
				->add( $this_out )
				;
		}

		$out = $this->app->make('/html/element')->tag('div')
			->add_attr('class', 'hc-field')
			->add( $out )
			;

		return $out;
	}
}