<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Html_Label_Input_HC_MVC
{
	protected $label = NULL;
	protected $label_for = NULL;
	protected $content = NULL;
	protected $help = NULL;
	protected $error = NULL;

	function set_error( $error )
	{
		$this->error = $error;
		return $this;
	}

	function set_label( $label, $label_for = NULL )
	{
		$this->label = $label;
		$this->label_for = $label_for;
		return $this;
	}

	function set_content( $content )
	{
		$this->content = $content;
		return $this;
	}

	function set_help( $help )
	{
		$this->help = $help;
		return $this;
	}

	public function render( $hori = TRUE )
	{
		$content = $this->app->make('/html/element')->tag('div')
			// ->add_attr('class', 'hc-block')
			->add( $this->content )
			;

		if( $this->error ){
			$error = $this->app->make('/html/element')->tag('div')
				->add( $this->error )
				->add_attr('class', 'hc-inline-block')
				->add_attr('class', 'hc-border-top')
				->add_attr('class', 'hc-border-red')
				->add_attr('class', 'hc-red')
				->add_attr('class', 'hc-py1')
				->add_attr('class', 'hc-mt2')
				;

			$error = $this->app->make('/html/element')->tag('div')
				// ->add_attr('class', 'hc-block')
				->add( $error )
				;

			$content
				->add( $error )
				;
		}

		if( $this->help ){
			$help = $this->app->make('/html/element')->tag('div')
				->add_attr('class', 'hc-block')
				->add_attr('class', 'hc-muted2')
				->add_attr('class', 'hc-italic')
				->add_attr('class', 'hc-mt1')
				->add( $this->help )
				;

			$content
				->add( $help )
				;
		}

		$label = NULL;
		if( $this->label ){
			$label = $this->app->make('/html/element')->tag('label')
				// ->add_attr('class', 'hc-block')
				// ->add_attr('class', 'hc-muted1')
				->add_attr('class', 'hc-bold')
				->add( $this->label )
				;

			if( $this->label_for ){
				$label
					->add_attr('for', $this->label_for )
					;
			}
		}

		if( $hori ){
			$out = $this->app->make('/html/element')->tag('div')
				->add_attr('class', 'hc-full-width')
				->add_attr('class', 'hc-table')
				// ->add_attr('class', 'hc-border')
				->add( 
					$this->app->make('/html/element')->tag('div')
						->add( $label )
						->add_attr('class', 'hc-table-cell')
						->add_attr('class', 'hc-label')
					)
				->add(
					$this->app->make('/html/element')->tag('div')
						->add( $content )
						->add_attr('class', 'hc-table-cell')
					)
				;
		}
		else {
			$out = $this->app->make('/html/element')->tag('div')
				->add_attr('class', 'hc-block')
				->add_attr('class', 'hc-mb2')
				// ->add_attr('class', 'hc-border')
				;

			if( $this->label ){
				$label
					->add_attr('class', 'hc-mb1')
					;
				$out->add( $label );
			}
			$out->add( $content );
		}

		return $out;
	}
}