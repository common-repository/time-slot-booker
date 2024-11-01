<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Email_Wordpress_Transport_HC_MVC
{
	public function send( $to, $subj, $msg )
	{
		@wp_mail( $to, $subj, $msg );

		if( defined('NTS_DEVELOPMENT2') ){
			$logger = $this->app->make('/email/logger')
				->execute( $to, $subj, $msg )
				;
		}

		return $this;
	}
}