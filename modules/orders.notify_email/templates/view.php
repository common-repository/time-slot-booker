<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Notify_Email_Templates_View_TSB_HC_MVC
{
	public function render( $calendar, $options, $values )
	{
	// add javascript
		$this->app->make('/app/enqueuer')
			->register_script( 'hc-tell-listen', 'happ2/assets/js/tell-listen.js' )
			->enqueue_script( 'hc-tell-listen' )
			;

		$notify_manager = $this->app->make('/orders.notify-email/manager');

		$inputs = $this->app->make('/orders.notify-email/templates/form')
			->inputs()
			;

	// add labels
		reset( $options );
		foreach( $options as $k => $v ){
			$label = $v;
			$label = $this->app->make('/html/element')->tag('div')
				->add( $label )
				// ->add_attr('class', 'hc-fs4')
				->add_attr('class', 'hc-white')
				->add_attr('class', 'hc-bg-gray')
				->add_attr('class', 'hc-px2')
				->add_attr('class', 'hc-py1')
				->add_attr('class', 'hc-rounded')
				;
			$toggle_key = 'notify-email:' . $k . ':active';
			$inputs[$toggle_key]['input']
				->set_label( $label )
				;
		}

		$helper = $this->app->make('/form/helper');
		$inputs_view = $helper->prepare_render( $inputs, $values );

		$out_inputs = $this->app->make('/html/list')
			->set_gutter(3)
			;

		reset( $options );
		foreach( $options as $k => $v ){
			$this_inputs_view = $this->app->make('/html/list')
				->set_gutter(1)
				->add( $inputs_view['notify-email:' . $k . ':subject'] )
				->add( $inputs_view['notify-email:' . $k . ':body'] )
				;

			$tags = $notify_manager
				->tags( $k )
				;
			$tags_view = $this->app->make('/html/list')
				->set_gutter(1)
				->add(
					$this->app->make('/html/element')->tag('div')
						->add( HCM::__('Tags') )
						->add_attr('class', 'hc-underline')
					)
				;
			foreach( array_keys($tags) as $tk ){
				$tk_view = '{' . strtoupper($tk) . '}';
				$tags_view
					->add( $tk_view )
					;
			}
			$tags_view = $this->app->make('/html/element')->tag('div')
				->add( $tags_view )
				->add_attr('class', 'hc-border')
				->add_attr('class', 'hc-rounded')
				->add_attr('class', 'hc-p2')
				;

			$this_inputs_view = $this->app->make('/html/grid')
				->set_gutter(3)
				->add( $this_inputs_view, 8 )
				->add( $tags_view, 4 )
				;

			$this_inputs_view = $this->app->make('/html/element')->tag('div')
				->add_attr('class', 'hcj-listen')
				->add( $this_inputs_view )
				;

			$this_view = $this->app->make('/html/list')
				->set_gutter(1)
				->add(
					$this->app->make('/html/element')->tag('div')
						->add_attr('class', 'hcj-tell')
						->add( $inputs_view['notify-email:' . $k . ':active'] )
					)
				->add( $this_inputs_view )
				;

			$this_view = $this->app->make('/html/element')->tag('div')
				->add_attr('class', 'hcj-tell-listen')
				->add( $this_view )
				;

			$out_inputs
				->add( $this_view )
				;
		}

		$out_buttons = $this->app->make('/html/list-inline')
			->add(
				$this->app->make('/html/element')->tag('input')
					->add_attr('type', 'submit')
					->add_attr('title', HCM::__('Save') )
					->add_attr('value', HCM::__('Save') )
					->add_attr('class', 'hc-theme-btn-submit')
					->add_attr('class', 'hc-theme-btn-primary')
					->add_attr('class', 'hc-xs-block')
				)
			;

		$link = $this->app->make('/http/uri')
			->url('/orders.notify-email/templates/update/' . $calendar['id'])
			;

		$out = $helper
			->render( array('action' => $link) )
			->add( 
				$this->app->make('/html/list')->set_gutter(2)
					->add( $out_inputs )
					->add( $out_buttons )
				)
			;

		return $out;
	}
}