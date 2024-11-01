<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Form_Text_HC_MVC implements Form_Input_Interface_HC_MVC
{
	protected $size = NULL;
	protected $label = NULL;

	public function set_label( $label )
	{
		$this->label = $label;
		return $this;
	}

	public function set_size( $size )
	{
		$this->size = $size;
		return $this;
	}

	public function grab( $name, $post )
	{
		$return = $this->app->make('/form/input')
			->grab($name, $post)
			;
		return $return;
	}

	public function render( $name, $value = NULL )
	{
		$name = $this->app->make('/form/input')->name($name);

		$out = $this->app->make('/html/element')->tag('input')
			->add_attr('type', 'text' )
			->add_attr('name', $name )
			->add_attr('class', 'hc-field')
			;

		if( $value !== NULL ){
			$out
				->add_attr('value', $value )
				;
		}

		if( $this->label ){
			$out
				->add_attr('placeholder', $this->label)
				;
		}

		if( $this->size !== NULL ){
			$out
				->add_attr('size', $this->size)
				;
		}
		else {
			$out
				->add_attr('class', 'hc-full-width')
				;
		}

		return $out;
	}
}