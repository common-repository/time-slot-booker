<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Bookings_Migration_1_TSB_HC_MVC
{
	public function up()
	{
		if( $this->app->db->table_exists('bookings') ){
			return;
		}

		$dbforge = $this->app->db->dbforge();

		$dbforge->add_field(
			array(
				'id' => array(
					'type' => 'INT',
					'null' => FALSE,
					'unsigned' => TRUE,
					'auto_increment' => TRUE
					),
				'starts_at' => array(
					'type' => 'BIGINT',
					'null' => FALSE,
					),
				'ends_at' => array(
					'type' => 'BIGINT',
					'null' => FALSE,
					),
				'calendar_id' => array(
					'type' => 'INT',
					'null' => FALSE,
					),
				)
			);
		$dbforge->add_key('id', TRUE);
		$dbforge->create_table('bookings');
	}
}