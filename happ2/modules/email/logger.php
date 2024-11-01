<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Email_Logger_HC_MVC
{
	public function execute( $to, $subj, $msg )
	{
		$outFile = $this->app->dir() . '/emaillog.txt';

		$now = time();
		$date = date( "F j, Y, g:i a", $now );

		$out = array();
		$out[] = $date;
		$out[] = $to;
		$out[] = $subj;
		$out[] = $msg;

		$out = join( "\n", $out );

		$fp = fopen( $outFile, 'a' );
		fwrite( $fp, $out . "\n\n" );
		fclose($fp);
	}
}