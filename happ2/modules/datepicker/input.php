<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Datepicker_Input_HC_MVC implements Form_Input_Interface_HC_MVC
{
	protected $options = array();

	public function _init()
	{
		$enqueuer = $this->app->make('/app/enqueuer')
			->enqueue_script( 'datepicker' )
			->enqueue_style( 'datepicker' )
			;
		return $this;
	}

	public function grab( $name, $post )
	{
		$return = $this->app->make('/form/input')
			->grab($name, $post)
			;
		return $return;
	}

	function add_option( $k, $v )
	{
		$this->options[$k] = $v;
	}
	function options()
	{
		return $this->options;
	}

	function render( $name, $value = NULL )
	{
		$id = 'nts-' . $name;

		$t = $this->app->make('/app/lib')->time();
		if( $value ){
			$t->setDateDb( $value );
			$value = $t->formatDate_Db();
		}
		else {
			$t->setNow();
			$value = $t->formatDate_Db();
			
		}

		// $value ? $t->setDateDb( $value ) : $t->setNow();
		// $value = $t->formatDate_Db();

	/* hidden field to store our value */
		$hidden = $this->app->make('/form/hidden')
			->render( $name, $value )
			->add_attr('id', $id)
			;

	/* text field to display */
		$display_name = $name . '_display';
		$display_id = 'nts-' . $display_name;
		$datepicker_format = $t->formatToDatepicker();
		if( $value ){
			$display_value = $t->formatDate();
		}
		else {
			$display_value = NULL;
		}

		$text = $this->app->make('/form/text')
			->set_size(12)
			->render( $display_name, $display_value )
			->add_attr('id', $display_id)
			->add_attr('data-date-format', $datepicker_format)
			->add_attr('data-date-week-start', $t->weekStartsOn)
			->add_attr( 'class', 'hc-datepicker2' )
			->add_attr( 'readonly', 'true' )
			// ->add_attr('class', 'hc-inline')
			->add_attr('class', 'hc-xs-block')
			;

		$out = $this->app->make('/html/element')->tag(NULL)
			->add( $hidden )
			->add( $text )
			;

		return $out;
	}
}