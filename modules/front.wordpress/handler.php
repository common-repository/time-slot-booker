<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Front_WordPress_Handler_TSB_HC_MVC
{
	protected $view = NULL;
	protected $shortcode = 'timeslotbooker';

	public function start()
	{
		add_shortcode( $this->shortcode, array($this, 'view'));
		add_action( 'template_redirect', array($this, 'execute') );
	}

	public function view()
	{
		if( $this->view === NULL ){
			$this->view = $this->app->handle_request( 'front' );
		}
		return $this->view;
	}

	public function execute()
	{
		global $post;
		if( isset($post->post_content) && has_shortcode($post->post_content, $this->shortcode) ){
			$this->view = $this->app->handle_request( 'front' );
		}
	}
}