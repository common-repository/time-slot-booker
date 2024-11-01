<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Availability_Migration_1_TSB_HC_MVC
{
	public function up()
	{
		if( $this->app->db->table_exists('availability') ){
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

				'applied_on_weekday' => array(
					'type' => 'INT',
					'null' => TRUE,
					),
				'applied_on_date' => array(
					'type' => 'INT',
					'null' => TRUE,
					),

				'slot_start' => array(
					'type' => 'INT',
					'null' => FALSE,
					),
				'slot_end' => array(
					'type' => 'INT',
					'null' => FALSE,
					),
				'slot_interval' => array(
					'type' => 'INT',
					'null' => FALSE,
					),

				'calendar_id' => array(
					'type' => 'INT',
					'null' => FALSE,
					),
				)
			);
		$dbforge->add_key('id', TRUE);
		$dbforge->create_table('availability');
	}
}