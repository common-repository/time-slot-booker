<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class CalendarsOne_Migration_1_TSB_HC_MVC
{
	public function up()
	{
		if( $this->app->db->table_exists('calendars') ){
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
				'title' => array(
					'type' => 'VARCHAR(255)',
					'null' => FALSE,
					),
				)
			);
		$dbforge->add_key('id', TRUE);
		$dbforge->create_table('calendars');

	// create a default calendars
		$q = $this->app->db->query_builder();

		$new = array(
			'title'	=> HCM::__('Default Calendar')
			);

		$q->set( $new );
		$sql = $q->get_compiled_insert( 'calendars' );
		$this->app->db->query( $sql );
		$new_id = $this->app->db->insert_id();
	}
}