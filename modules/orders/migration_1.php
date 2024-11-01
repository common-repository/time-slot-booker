<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class Orders_Migration_1_TSB_HC_MVC
{
	public function up()
	{
		if( $this->app->db->table_exists('orders') ){
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
				'ref' => array(
					'type' => 'VARCHAR(32)',
					'null' => FALSE,
					),
				'status' => array(
					'type' => 'VARCHAR(16)',
					'null' => FALSE,
					),

				'customer_name' => array(
					'type' => 'VARCHAR(255)',
					'null' => FALSE,
					),
				'customer_email' => array(
					'type' => 'VARCHAR(255)',
					'null' => FALSE,
					),
				'customer_phone' => array(
					'type' => 'VARCHAR(255)',
					'null' => FALSE,
					),
				)
			);
		$dbforge->add_key('id', TRUE);
		$dbforge->create_table('orders');
	}
}