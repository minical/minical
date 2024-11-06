<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_create_base extends CI_Migration {

	public function up() {

		## Create Table booking
		$this->dbforge->add_field(array(
			'booking_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'rate' => array(
				'type' => 'FLOAT',
				'null' => TRUE,

			),
			'adult_count' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'null' => TRUE,

			),
			'children_count' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'null' => FALSE,
				'default' => '0',

			),
			'state' => array(
				'type' => 'INT',
				'constraint' => 2,
				'null' => FALSE,
				'default' => '0',

			),
			'booking_notes' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'booking_customer_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'booked_by' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'balance' => array(
				'type' => 'FLOAT',
				'null' => TRUE,
				'default' => '0',

			),
			'balance_without_forecast' => array(
				'type' => 'FLOAT',
				'null' => FALSE,
				'default' => '0',

			),
			'invoice_hash' => array(
				'type' => 'CHAR',
				'constraint' => 32,
				'null' => TRUE,

			),
			'use_rate_plan' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'rate_plan_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'color' => array(
				'type' => 'VARCHAR',
				'constraint' => 11,
				'null' => FALSE,
				'default' => 'transparent',

			),
			'housekeeping_notes' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
			'charge_type_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'guest_review' => array(
				'type' => 'DECIMAL',
				'constraint' => 2,1,
				'null' => TRUE,

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'source' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'default' => '0',

			),
			'is_ota_booking' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'pay_period' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'revenue' => array(
				'type' => 'FLOAT',
				'null' => TRUE,
				'default' => '0',

			),
			'add_daily_charge' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'residual_rate' => array(
				'type' => 'INT',
				'constraint' => 10,
				'null' => TRUE,

			),
			'is_invoice_auto_sent' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
		));
		$this->dbforge->add_key("booking_id",true);
		$this->dbforge->create_table("booking", TRUE);
		$this->db->query('ALTER TABLE  `booking` ENGINE = InnoDB');

		## Create Table booking_block
		$this->dbforge->add_field(array(
			'booking_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'room_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'room_type_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'check_in_date' => array(
				'type' => 'DATETIME',
				'null' => TRUE,

			),
			'check_out_date' => array(
				'type' => 'DATETIME',
				'null' => TRUE,

			),
			'booking_room_history_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
		));
		$this->dbforge->add_key("booking_room_history_id",true);
		$this->dbforge->create_table("booking_block", TRUE);
		$this->db->query('ALTER TABLE  `booking_block` ENGINE = InnoDB');

		## Create Table booking_field
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => 225,
				'null' => TRUE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'show_on_booking_form' => array(
				'type' => 'INT',
				'constraint' => 4,
				'null' => FALSE,
				'default' => '1',

			),
			'show_on_registration_card' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'null' => FALSE,
				'default' => '1',

			),
			'show_on_in_house_report' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'null' => FALSE,
				'default' => '1',

			),
			'show_on_invoice' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'is_required' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'null' => FALSE,
				'default' => '0',

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'null' => FALSE,
				'default' => '0',

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("booking_field", TRUE);
		$this->db->query('ALTER TABLE  `booking_field` ENGINE = InnoDB');

		## Create Table booking_linked_group
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => 20,
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("booking_linked_group", TRUE);
		$this->db->query('ALTER TABLE  `booking_linked_group` ENGINE = InnoDB');

		## Create Table booking_log
		$this->dbforge->add_field(array(
			'booking_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'date_time' => array(
				'type' => 'DATETIME',
				'null' => FALSE,

			),
			'user_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'log' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
			'log_type' => array(
				'type' => 'INT',
				'constraint' => 2,
				'null' => TRUE,
				'default' => '0',

			),
			'selling_date' => array(
				'type' => 'DATE',
				'null' => TRUE,

			),
		));

		$this->dbforge->create_table("booking_log", TRUE);
		$this->db->query('ALTER TABLE  `booking_log` ENGINE = InnoDB');

		## Create Table booking_review
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'booking_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'customer_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'rating' => array(
				'type' => 'FLOAT',
				'null' => FALSE,

			),
			'comment' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
			'created' => array(
				'type' => 'DATETIME',
				'null' => TRUE,

			),
		));

		$this->dbforge->create_table("booking_review", TRUE);
		$this->db->query('ALTER TABLE  `booking_review` ENGINE = InnoDB');

		## Create Table booking_source
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => FALSE,

			),
			'commission_rate' => array(
				'type' => 'VARCHAR',
				'constraint' => 20,
				'null' => TRUE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'is_hidden' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'sort_order' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'default' => '0',

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("booking_source", TRUE);
		$this->db->query('ALTER TABLE  `booking_source` ENGINE = InnoDB');

		## Create Table booking_staying_customer_list
		$this->dbforge->add_field(array(
			'booking_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'customer_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
		));
		// $this->dbforge->add_key("customer_id",true);
		$this->dbforge->create_table("booking_staying_customer_list", TRUE);
		$this->db->query('ALTER TABLE  `booking_staying_customer_list` ENGINE = InnoDB');

		## Create Table booking_x_booking_field
		$this->dbforge->add_field(array(
			'booking_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'booking_field_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'value' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
		));

		$this->dbforge->create_table("booking_x_booking_field", TRUE);
		$this->db->query('ALTER TABLE  `booking_x_booking_field` ENGINE = InnoDB');

		## Create Table booking_x_booking_linked_group
		$this->dbforge->add_field(array(
			'booking_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'booking_group_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
		));

		$this->dbforge->create_table("booking_x_booking_linked_group", TRUE);
		$this->db->query('ALTER TABLE  `booking_x_booking_linked_group` ENGINE = InnoDB');

		## Create Table booking_x_extra
		$this->dbforge->add_field(array(
			'booking_extra_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'extra_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'start_date' => array(
				'type' => 'DATE',
				'null' => TRUE,

			),
			'end_date' => array(
				'type' => 'DATE',
				'null' => TRUE,

			),
			'quantity' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'rate' => array(
				'type' => 'DECIMAL',
				'constraint' => "10,2",
				'null' => TRUE,

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
			'booking_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("booking_extra_id",true);
		$this->dbforge->create_table("booking_x_extra", TRUE);
		$this->db->query('ALTER TABLE  `booking_x_extra` ENGINE = InnoDB');

		## Create Table booking_x_group
		$this->dbforge->add_field(array(
			'booking_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'group_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
		));

		$this->dbforge->create_table("booking_x_group", TRUE);
		$this->db->query('ALTER TABLE  `booking_x_group` ENGINE = InnoDB');

		## Create Table booking_x_invoice
		$this->dbforge->add_field(array(
			'booking_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'invoice_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
		));
		$this->dbforge->add_key("booking_id",true);
		$this->dbforge->create_table("booking_x_invoice", TRUE);
		$this->db->query('ALTER TABLE  `booking_x_invoice` ENGINE = InnoDB');

		## Create Table booking_x_statement
		$this->dbforge->add_field(array(
			'booking_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'statement_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
		));

		$this->dbforge->create_table("booking_x_statement", TRUE);
		$this->db->query('ALTER TABLE  `booking_x_statement` ENGINE = InnoDB');

		## Create Table charge
		$this->dbforge->add_field(array(
			'charge_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'description' => array(
				'type' => 'VARCHAR',
				'constraint' => 125,
				'null' => TRUE,

			),
			'date_time' => array(
				'type' => 'DATETIME',
				'null' => TRUE,

			),
			'booking_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'amount' => array(
				'type' => 'DECIMAL',
				'constraint' => "10,2",
				'null' => TRUE,

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'charge_type_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'selling_date' => array(
				'type' => 'DATE',
				'null' => TRUE,

			),
			'user_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'customer_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'pay_period' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,

			),
			'is_night_audit_charge' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
		));
		$this->dbforge->add_key("charge_id",true);
		$this->dbforge->create_table("charge", TRUE);
		$this->db->query('ALTER TABLE  `charge` ENGINE = InnoDB');

		## Create Table charge_folio
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'charge_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'folio_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("charge_folio", TRUE);
		$this->db->query('ALTER TABLE  `charge_folio` ENGINE = InnoDB');

		## Create Table charge_type
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => 20,
				'null' => FALSE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
			'is_room_charge_type' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
			'is_default_room_charge_type' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
			'is_tax_exempt' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("charge_type", TRUE);
		$this->db->query('ALTER TABLE  `charge_type` ENGINE = InnoDB');

		## Create Table charge_type_tax_list
		$this->dbforge->add_field(array(
			'tax_type_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'charge_type_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
		));
		// $this->dbforge->add_key("charge_type_id",true);
		$this->dbforge->create_table("charge_type_tax_list", TRUE);
		$this->db->query('ALTER TABLE  `charge_type_tax_list` ENGINE = InnoDB');

		## Create Table common_booking_source_setting
		$this->dbforge->add_field(array(
			'booking_source_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'is_hidden' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,

			),
			'sort_order' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'commission_rate' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,

			),
		));

		$this->dbforge->create_table("common_booking_source_setting", TRUE);
		$this->db->query('ALTER TABLE  `common_booking_source_setting` ENGINE = InnoDB');

		## Create Table common_customer_fields_setting
		$this->dbforge->add_field(array(
			'customer_field_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'show_on_customer_form' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'null' => FALSE,
				'default' => '1',

			),
			'show_on_registration_card' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'show_on_in_house_report' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'show_on_invoice' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'is_required' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,

			),
			'room_type_id' => array(
				'type' => 'MEDIUMTEXT',
				'null' => true,

			),
		));

		$this->dbforge->create_table("common_customer_fields_setting", TRUE);
		$this->db->query('ALTER TABLE  `common_customer_fields_setting` ENGINE = InnoDB');

		## Create Table common_customer_type_setting
		$this->dbforge->add_field(array(
			'customer_type_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'sort_order' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'default' => '0',

			),
		));

		$this->dbforge->create_table("common_customer_type_setting", TRUE);
		$this->db->query('ALTER TABLE  `common_customer_type_setting` ENGINE = InnoDB');

		## Create Table company
		$this->dbforge->add_field(array(
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'name' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
			'address' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
			'phone' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
			'city' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
			'region' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
			'country' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
			'website' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
			'email' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
			'fax' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
			'selling_date' => array(
				'type' => 'DATE',
				'null' => TRUE,

			),
			'nightly_audit_charge_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'time_zone' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
			'GST_number' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
			'postal_code' => array(
				'type' => 'VARCHAR',
				'constraint' => 15,
				'null' => TRUE,

			),
			'is_daily_rate_including_tax' => array(
				'type' => 'INT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'is_setup' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
			'number_of_rooms' => array(
				'type' => 'INT',
				'constraint' => 3,
				'null' => FALSE,
				'default' => '0',

			),
			'reservation_policies' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
			'check_in_policies' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
			'night_audit_auto_prompt' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'night_audit_auto_prompt_time' => array(
				'type' => 'TIME',
				'null' => FALSE,
				'default' => '05:00:00',

			),
			'night_audit_force_check_out' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'night_audit_multiple_days' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'require_paypal_payment' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'null' => FALSE,
				'default' => '0',

			),
			'paypal_account' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
			'percentage_of_required_paypal_payment' => array(
				'type' => 'INT',
				'constraint' => 2,
				'null' => FALSE,
				'default' => '50',

			),
			'housekeeping_auto_clean_is_enabled' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'housekeeping_auto_clean_time' => array(
				'type' => 'TIME',
				'null' => FALSE,
				'default' => '16:00:00',

			),
			'housekeeping_day_interval_for_full_cleaning' => array(
				'type' => 'INT',
				'constraint' => 2,
				'null' => FALSE,
				'default' => '4',

			),
			'online_reservation_confirmation_message' => array(
				'type' => 'VARCHAR',
				'constraint' => 512,
				'null' => TRUE,
				'default' => 'To secure your reservation spot, please confirm your reservation with the [name] by calling [phone] or by sending email to [email]',

			),
			'default_currency_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'night_audit_auto_run_is_enabled' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'night_audit_auto_run_time' => array(
				'type' => 'TIME',
				'null' => FALSE,
				'default' => '04:00:00',

			),
			'property_type' => array(
				'type' => 'INT',
				'unsigned' => TRUE,
				'null' => FALSE,
				'default' => '0',

			),
			'invoice_email_header' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
			'booking_confirmation_email_header' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
			'send_invoice_email_automatically' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'ask_for_review_in_invoice_email' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'hide_decimal_places' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'companycol' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
			'redirect_to_trip_advisor' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'tripadvisor_link' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
			'employee_auto_logout_is_enabled' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'api_key' => array(
				'type' => 'VARCHAR',
				'constraint' => 32,
				'null' => TRUE,

			),
			'website_uri' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
			'website_about' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
			'website_latitude' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
			'website_longitude' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
			'website_is_taking_online_reservation' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
			'website_theme_color' => array(
				'type' => 'VARCHAR',
				'constraint' => 6,
				'null' => TRUE,
				'default' => 'EDE8EB',

			),
			'website_facebook_page_url' => array(
				'type' => 'VARCHAR',
				'constraint' => 60,
				'null' => TRUE,

			),
			'website_tripadvisor_location_id' => array(
				'type' => 'VARCHAR',
				'constraint' => 20,
				'null' => TRUE,

			),
			'google_analytics_id' => array(
				'type' => 'VARCHAR',
				'constraint' => 20,
				'null' => TRUE,

			),
			'google_conversion_id' => array(
				'type' => 'VARCHAR',
				'constraint' => 20,
				'null' => TRUE,

			),
			'google_conversion_label' => array(
				'type' => 'VARCHAR',
				'constraint' => 20,
				'null' => TRUE,

			),
			'logo_image_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'logo_image_group_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'gallery_image_group_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'slideshow_image_group_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'website_title' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
			'website_tripadvisor_widget_type' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
			'allow_same_day_check_in' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '1',

			),
			'booking_email_confirmation_message' => array(
				'type' => 'VARCHAR',
				'constraint' => 512,
				'null' => TRUE,
				'default' => 'This reservation is provisionary. It is not confirmed until the payment information is given from the customer. For questions, please contact us at [email].',

			),
			'invoice_header' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
			'last_night_audit' => array(
				'type' => 'DATE',
				'null' => TRUE,

			),
			'night_audit_charge_in_house_only' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
			'night_audit_ignore_check_out_date' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
			'book_over_unconfirmed_reservations' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'statement_number' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'default' => '1',

			),
			'enable_card_tokenization' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'booking_engine_booking_status' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'is_total_balance_include_forecast' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'is_display_tooltip' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'partner_id' => array(
				'type' => 'INT',
				'constraint' => 50,
				'null' => FALSE,
				'default' => '0',

			),
			'booking_dot_com_rate_type' => array(
				'type' => 'VARCHAR',
				'constraint' => 50,
				'null' => TRUE,
				'default' => 'base_rate',

			),
			'common_additional_adult_rate' => array(
				'type' => 'DECIMAL',
				'constraint' => "10,2",
				'null' => TRUE,

			),
			'allow_non_continuous_bookings' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'maximum_no_of_blocks' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'default' => '2',

			),
			'manual_payment_capture' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'include_cancelled_noshow_bookings' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'force_room_selection' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'send_copy_to_additional_emails' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'additional_company_emails' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
			'hide_forecast_charges' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'housekeeping_auto_dirty_is_enabled' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'housekeeping_auto_dirty_time' => array(
				'type' => 'TIME',
				'null' => FALSE,
				'default' => '16:00:00',

			),
			'automatic_feedback_email' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,
				'default' => '0',

			),
			'subscription_country' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,
				'default' => 'US',

			),
			'auto_no_show' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'last_login' => array(
				'type' => 'DATE',
				'null' => TRUE,

			),
			'show_rate_on_registration_card' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'automatic_email_confirmation' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'automatic_email_cancellation' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'email_confirmation_for_ota_reservations' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'make_guest_field_mandatory' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'ui_theme' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'default_charge_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,
				'default' => 'Room Charge',

			),
			'date_format' => array(
				'type' => 'VARCHAR',
				'constraint' => 50,
				'null' => FALSE,
				'default' => 'YY-MM-DD',

			),
			'default_room_singular' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,
				'default' => 'Room',

			),
			'default_room_plural' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,
				'default' => 'Rooms',

			),
			'default_room_type' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,
				'default' => 'Room Type',

			),
			'avoid_dmarc_blocking' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'enable_hourly_booking' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'default_checkin_time' => array(
				'type' => 'VARCHAR',
				'constraint' => 50,
				'null' => FALSE,
				'default' => '12:00 AM',

			),
			'default_checkout_time' => array(
				'type' => 'VARCHAR',
				'constraint' => 50,
				'null' => FALSE,
				'default' => '12:00 AM',

			),
			'allow_free_bookings' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'show_logo_on_registration_card' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
			'restrict_booking_dates_modification' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'null' => TRUE,
				'default' => '0',

			),
			'restrict_checkout_with_balance' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'null' => TRUE,
				'default' => '0',

			),
			'enable_api_access' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
			'customer_modify_booking' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'booking_cancelled_with_balance' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'booking_checkedout_with_balance' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'enable_new_calendar' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'default_language' => array(
				'type' => 'INT',
				'constraint' => 10,
				'null' => FALSE,
				'default' => '1',

			),
			'hide_room_name' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'email_confirmation_for_booking_engine' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'is_cc_visualization_enabled' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'send_booking_notes' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'show_guest_group_invoice' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'booking_engine_tracking_code' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
			'email_cancellation_for_ota_reservations' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'restrict_cvc_not_mandatory' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',
			),
			'calendar_days' => array(
				'type' => 'TINYINT',
				'constraint' => 2,
				'null' => TRUE
			),
			'restrict_edit_after_checkout' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',
			),
			'allow_change_previous_booking_status' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',
			),
			'bussiness_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,
			),
			'bussiness_number' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,
			),
			'bussiness_fiscal_number' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,
			),
		));
		$this->dbforge->add_key("company_id",true);
		$this->dbforge->create_table("company", TRUE);
		$this->db->query('ALTER TABLE  `company` ENGINE = InnoDB');

		## Create Table company_admin_panel_info
		$this->dbforge->add_field(array(
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'salesforce_url' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
			'admin_comment' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
			'creation_date' => array(
				'type' => 'DATE',
				'null' => TRUE,

			),
			'trial_expiry_date' => array(
				'type' => 'DATE',
				'null' => TRUE,

			),
			'trial_extension_reason' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
			'conversion_date' => array(
				'type' => 'DATE',
				'null' => TRUE,

			),
			'churn_date' => array(
				'type' => 'DATE',
				'null' => TRUE,

			),
			'lead_source_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => TRUE,

			),
			'utm_source' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'inhouse_settings' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
			'reservation_settings' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("company_id",true);
		$this->dbforge->create_table("company_admin_panel_info", TRUE);
		$this->db->query('ALTER TABLE  `company_admin_panel_info` ENGINE = InnoDB');

		## Create Table company_charge
		$this->dbforge->add_field(array(
			'company_charge_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'description' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
			'amount' => array(
				'type' => 'DECIMAL',
				'constraint' => "10,2",
				'null' => TRUE,
				'default' => '0.00',

			),
			'charge_type' => array(
				'type' => 'VARCHAR',
				'constraint' => 20,
				'null' => TRUE,

			),
			'date' => array(
				'type' => 'DATE',
				'null' => TRUE,

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
		));
		$this->dbforge->add_key("company_charge_id",true);
		$this->dbforge->create_table("company_charge", TRUE);
		$this->db->query('ALTER TABLE  `company_charge` ENGINE = InnoDB');

		## Create Table company_payment_gateway
		$this->dbforge->add_field(array(
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'beanstream_merchant_id' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
			'beanstream_api_access_passcode' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
			'beanstream_profile_api_access_passcode' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
			'paypal_email' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
			'selected_payment_gateway' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
			'stripe_secret_key' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'stripe_publishable_key' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'gateway_login' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'gateway_password' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
			'gateway_meta_data' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
			'store_cc_in_booking_engine' => array(
				'type' => 'INT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
		));

		$this->dbforge->create_table("company_payment_gateway", TRUE);
		$this->db->query('ALTER TABLE  `company_payment_gateway` ENGINE = InnoDB');

		## Create Table company_subscription
		$this->dbforge->add_field(array(
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'subscription_id' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
			'subscription_type' => array(
				'type' => 'VARCHAR',
				'constraint' => 50,
				'null' => TRUE,
				'default' => 'STANDARD',

			),
			'subscription_state' => array(
				'type' => 'VARCHAR',
				'constraint' => 50,
				'null' => TRUE,
				'default' => 'trialing',

			),
			'renewal_period' => array(
				'type' => 'VARCHAR',
				'constraint' => 10,
				'null' => FALSE,
				'default' => '1 month',

			),
			'cost_amount' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'tax' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'renewal_cost' => array(
				'type' => 'DECIMAL',
				'constraint' => "10,2",
				'null' => TRUE,

			),
			'balance' => array(
				'type' => 'DECIMAL',
				'constraint' => "10,2",
				'null' => TRUE,
				'default' => '0.00',

			),
			'region' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'payment_method' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,
				'default' => 'none',

			),
			'expiration_date' => array(
				'type' => 'DATE',
				'null' => TRUE,

			),
			'transition_after_expired' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'chargify_subscription_link' => array(
				'type' => 'VARCHAR',
				'constraint' => 70,
				'null' => TRUE,

			),
			'invoice_link' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => TRUE,

			),
			'conversion_date' => array(
				'type' => 'DATE',
				'null' => TRUE,

			),
			'churn_date' => array(
				'type' => 'DATE',
				'null' => TRUE,

			),
			'subscription_level' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'limit_feature' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'meta_data' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("company_id",true);
		$this->dbforge->create_table("company_subscription", TRUE);
		$this->db->query('ALTER TABLE  `company_subscription` ENGINE = InnoDB');

		## Create Table company_security
		$this->dbforge->add_field(array(
		    'id' => array(
		        'type' => 'INT',
		        'constraint' => 11,
		        'unsigned' => TRUE,
		        'auto_increment' => TRUE,
		        'null' => FALSE,
		    ),
		    'company_id' => array(
		        'type' => 'INT',
		        'constraint' => 11,
		        'unsigned' => TRUE,
		        'null' => FALSE,
		    ),
		    'user_id' => array(
		        'type' => 'INT',
		        'constraint' => 11,
		        'null' => TRUE,
		    ),
		    'secret_code' => array(
		        'type' => 'VARCHAR',
		        'constraint' => 255,
		        'null' => TRUE,
		    ),
		    'qr_code_url' => array(
		        'type' => 'TINYTEXT',
		        'null' => TRUE,
		    ),
		    'security_name' => array(
		        'type' => 'VARCHAR',
		        'constraint' => 255,
		        'null' => TRUE,
		    ),
		    '`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP',
		));

		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('company_security');
		$this->db->query('ALTER TABLE  `company_security` ENGINE = InnoDB');

		## Create Table company_x_currency
		$this->dbforge->add_field(array(
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'currency_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
		));

		$this->dbforge->create_table("company_x_currency", TRUE);
		$this->db->query('ALTER TABLE  `company_x_currency` ENGINE = InnoDB');

		## Create Table company_x_tag
		$this->dbforge->add_field(array(
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'tag' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => FALSE,

			),
		));

		$this->dbforge->create_table("company_x_tag", TRUE);
		$this->db->query('ALTER TABLE  `company_x_tag` ENGINE = InnoDB');

		## Create Table cron_email_log
		$this->dbforge->add_field(array(
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'email_type' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => FALSE,

			),
			'to' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
			'date_sent' => array(
				'type' => 'DATETIME',
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("email_type",true);
		$this->dbforge->create_table("cron_email_log", TRUE);
		$this->db->query('ALTER TABLE  `cron_email_log` ENGINE = InnoDB');

		## Create Table cron_log
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'system_time' => array(
				'type' => 'DATETIME',
				'null' => TRUE,

			),
			'log' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("cron_log", TRUE);
		$this->db->query('ALTER TABLE  `cron_log` ENGINE = InnoDB');

		## Create Table currency
		$this->dbforge->add_field(array(
			'currency_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'currency_code' => array(
				'type' => 'CHAR',
				'constraint' => 3,
				'null' => FALSE,

			),
			'currency_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => FALSE,

			),
			'currency_symbol' => array(
				'type' => 'VARCHAR',
				'constraint' => 5,
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("currency_id",true);
		$this->dbforge->create_table("currency", TRUE);
		$this->db->query('ALTER TABLE  `currency` ENGINE = InnoDB');

		## Create Table customer
		$this->dbforge->add_field(array(
			'customer_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'customer_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'address' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
			'city' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => TRUE,

			),
			'region' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => TRUE,

			),
			'country' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => TRUE,

			),
			'postal_code' => array(
				'type' => 'VARCHAR',
				'constraint' => 10,
				'null' => TRUE,

			),
			'phone' => array(
				'type' => 'CHAR',
				'constraint' => 20,
				'null' => TRUE,

			),
			'fax' => array(
				'type' => 'VARCHAR',
				'constraint' => 15,
				'null' => TRUE,

			),
			'email' => array(
				'type' => 'VARCHAR',
				'constraint' => 200,
				'null' => TRUE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'customer_notes' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'customer_type' => array(
				'type' => 'VARCHAR',
				'constraint' => 11,
				'null' => TRUE,
				'default' => 'PERSON',

			),
			'cc_number' => array(
				'type' => 'VARCHAR',
				'constraint' => 55,
				'null' => TRUE,

			),
			'cc_expiry_month' => array(
				'type' => 'VARCHAR',
				'constraint' => 2,
				'null' => TRUE,

			),
			'cc_expiry_year' => array(
				'type' => 'VARCHAR',
				'constraint' => 2,
				'null' => TRUE,

			),
			'stripe_customer_id' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'customer_type_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => TRUE,
				'default' => '1',

			),
			'address2' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
			'phone2' => array(
				'type' => 'CHAR',
				'constraint' => 20,
				'null' => TRUE,

			),
			'cc_tokenex_token' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'cc_cvc_encrypted' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("customer_id",true);
		$this->dbforge->create_table("customer", TRUE);
		$this->db->query('ALTER TABLE  `customer` ENGINE = InnoDB');

		## Create Table customer_card_detail
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'is_primary' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'evc_card_status' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => TRUE,

			),
			'card_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'customer_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'customer_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'cc_number' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'cc_expiry_month' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'cc_expiry_year' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'cc_tokenex_token' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'cc_cvc_encrypted' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'is_card_deleted' => array(
				'type' => 'INT',
				'constraint' => 10,
				'null' => FALSE,
				'default' => '0',

			),
			'customer_meta_data' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("customer_card_detail", TRUE);
		$this->db->query('ALTER TABLE  `customer_card_detail` ENGINE = InnoDB');

		## Create Table customer_field
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'show_on_customer_form' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'show_on_registration_card' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'show_on_in_house_report' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'show_on_invoice' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'is_required' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
			'room_type_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("customer_field", TRUE);
		$this->db->query('ALTER TABLE  `customer_field` ENGINE = InnoDB');

		## Create Table customer_type
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
			'sort_order' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'default' => '0',

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("customer_type", TRUE);
		$this->db->query('ALTER TABLE  `customer_type` ENGINE = InnoDB');

		## Create Table customer_x_customer_field
		$this->dbforge->add_field(array(
			'customer_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'customer_field_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'value' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
		));

		$this->dbforge->create_table("customer_x_customer_field", TRUE);
		$this->db->query('ALTER TABLE  `customer_x_customer_field` ENGINE = InnoDB');

		## Create Table date_color
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => 50,
				'null' => TRUE,

			),
			'start_date' => array(
				'type' => 'DATE',
				'null' => TRUE,

			),
			'end_date' => array(
				'type' => 'DATE',
				'null' => TRUE,

			),
			'color_code' => array(
				'type' => 'VARCHAR',
				'constraint' => 50,
				'null' => TRUE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'user_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'is_deleted' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'default' => '0',

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("date_color", TRUE);
		$this->db->query('ALTER TABLE  `date_color` ENGINE = InnoDB');

		## Create Table date_interval
		$this->dbforge->add_field(array(
			'date' => array(
				'type' => 'DATE',
				'null' => FALSE,

			),
		));
		$this->dbforge->add_key("date",true);
		$this->dbforge->create_table("date_interval", TRUE);
		$this->db->query('ALTER TABLE  `date_interval` ENGINE = InnoDB');

		## Create Table date_range
		$this->dbforge->add_field(array(
			'date_range_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'date_start' => array(
				'type' => 'DATE',
				'null' => TRUE,
				'default' => '2000-01-01',

			),
			'date_end' => array(
				'type' => 'DATE',
				'null' => TRUE,
				'default' => '2030-01-01',

			),
			'monday' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '1',

			),
			'tuesday' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '1',

			),
			'wednesday' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '1',

			),
			'thursday' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '1',

			),
			'friday' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '1',

			),
			'saturday' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '1',

			),
			'sunday' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '1',

			),
		));
		$this->dbforge->add_key("date_range_id",true);
		$this->dbforge->create_table("date_range", TRUE);
		$this->db->query('ALTER TABLE  `date_range` ENGINE = InnoDB');

		## Create Table date_range_x_extra_rate
		$this->dbforge->add_field(array(
			'extra_rate_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'date_range_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
		));
		$this->dbforge->add_key("date_range_id",true);
		$this->dbforge->create_table("date_range_x_extra_rate", TRUE);
		$this->db->query('ALTER TABLE  `date_range_x_extra_rate` ENGINE = InnoDB');

		## Create Table date_range_x_rate
		$this->dbforge->add_field(array(
			'rate_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'date_range_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'minimum_length_of_stay' => array(
				'type' => 'INT',
				'constraint' => 4,
				'null' => FALSE,
				'default' => '1',

			),
			'maximum_length_of_stay' => array(
				'type' => 'INT',
				'constraint' => 4,
				'null' => FALSE,
				'default' => '365',

			),
			'closed_to_arrival' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'closed_to_departure' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'can_be_sold_online' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'date_range_x_rate_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'minimum_length_of_stay_arrival' => array(
				'type' => 'INT',
				'constraint' => 4,
				'null' => FALSE,
				'default' => '1',

			),
			'maximum_length_of_stay_arrival' => array(
				'type' => 'INT',
				'constraint' => 4,
				'null' => FALSE,
				'default' => '365',

			),
		));
		$this->dbforge->add_key("date_range_x_rate_id",true);
		$this->dbforge->create_table("date_range_x_rate", TRUE);
		$this->db->query('ALTER TABLE  `date_range_x_rate` ENGINE = InnoDB');

		## Create Table date_range_x_rate_plan_tbd
		$this->dbforge->add_field(array(
			'rate_plan_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'date_range_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'minimum_length_of_stay' => array(
				'type' => 'INT',
				'constraint' => 4,
				'null' => FALSE,
				'default' => '1',

			),
			'maximum_length_of_stay' => array(
				'type' => 'INT',
				'constraint' => 4,
				'null' => FALSE,
				'default' => '999',

			),
			'closed_to_arrival' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'closed_to_departure' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'can_be_sold_online' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
		));
		$this->dbforge->add_key("date_range_id",true);
		$this->dbforge->create_table("date_range_x_rate_plan_tbd", TRUE);
		$this->db->query('ALTER TABLE  `date_range_x_rate_plan_tbd` ENGINE = InnoDB');

		## Create Table date_range_x_rate_supplied
		$this->dbforge->add_field(array(
			'date_range_x_rate_supplied_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'rate_supplied_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'date_range_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
		));
		$this->dbforge->add_key("date_range_x_rate_supplied_id",true);
		$this->dbforge->create_table("date_range_x_rate_supplied", TRUE);
		$this->db->query('ALTER TABLE  `date_range_x_rate_supplied` ENGINE = InnoDB');

		## Create Table date_range_x_room_type
		$this->dbforge->add_field(array(
			'date_range_x_room_type_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'room_type_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'date_range_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'can_be_sold_online' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
		));
		$this->dbforge->add_key("date_range_x_room_type_id",true);
		$this->dbforge->create_table("date_range_x_room_type", TRUE);
		$this->db->query('ALTER TABLE  `date_range_x_room_type` ENGINE = InnoDB');

		## Create Table date_range_x_room_type_x_channel
		$this->dbforge->add_field(array(
			'date_range_x_room_type_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'channel_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'availability' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
		));
		$this->dbforge->add_key("date_range_x_room_type_id",true);
		$this->dbforge->create_table("date_range_x_room_type_x_channel", TRUE);
		$this->db->query('ALTER TABLE  `date_range_x_room_type_x_channel` ENGINE = InnoDB');

		## Create Table date_range_x_room_type_x_status
		$this->dbforge->add_field(array(
			'date_range_x_room_type_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'channel_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'can_be_sold_online' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
		));

		$this->dbforge->create_table("date_range_x_room_type_x_status", TRUE);
		$this->db->query('ALTER TABLE  `date_range_x_room_type_x_status` ENGINE = InnoDB');

		## Create Table employee_log
		$this->dbforge->add_field(array(
			'user_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'selling_date' => array(
				'type' => 'DATE',
				'null' => FALSE,

			),
			'date_time' => array(
				'type' => 'DATETIME',
				'null' => FALSE,

			),
			'log' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,

			),
		));

		$this->dbforge->create_table("employee_log", TRUE);
		$this->db->query('ALTER TABLE  `employee_log` ENGINE = InnoDB');

		## Create Table extensions_log
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'extension_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => FALSE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,
				'default' => '0',

			),
			'vendor_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,
				'default' => '0',

			),
			'user_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,
				'default' => '0',

			),
			'status' => array(
				'type' => 'VARCHAR',
				'constraint' => 50,
				'null' => FALSE,

			),
			'date_time' => array(
				'type' => 'DATETIME',
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("extensions_log", TRUE);
		$this->db->query('ALTER TABLE  `extensions_log` ENGINE = InnoDB');

		## Create Table extensions_x_company
		$this->dbforge->add_field(array(
			'extension_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => FALSE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'is_active' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'is_favourite' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
		));

		$this->dbforge->create_table("extensions_x_company", TRUE);
		$this->db->query('ALTER TABLE  `extensions_x_company` ENGINE = InnoDB');

		## Create Table extensions_x_vendor
		$this->dbforge->add_field(array(
			'extension_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => FALSE,

			),
			'vendor_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'is_installed' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
		));

		$this->dbforge->create_table("extensions_x_vendor", TRUE);
		$this->db->query('ALTER TABLE  `extensions_x_vendor` ENGINE = InnoDB');

		## Create Table extra
		$this->dbforge->add_field(array(
			'extra_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'extra_type' => array(
				'type' => 'VARCHAR',
				'constraint' => 10,
				'null' => TRUE,

			),
			'extra_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'charge_type_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'charging_scheme' => array(
				'type' => 'VARCHAR',
				'constraint' => 20,
				'null' => TRUE,
				'default' => 'on_start_date',

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
			'show_on_pos' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
		));
		$this->dbforge->add_key("extra_id",true);
		$this->dbforge->create_table("extra", TRUE);
		$this->db->query('ALTER TABLE  `extra` ENGINE = InnoDB');

		## Create Table extra_rate
		$this->dbforge->add_field(array(
			'extra_rate_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'rate' => array(
				'type' => 'FLOAT',
				'null' => FALSE,
				'default' => '0',

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
			'currency_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'extra_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("extra_rate_id",true);
		$this->dbforge->create_table("extra_rate", TRUE);
		$this->db->query('ALTER TABLE  `extra_rate` ENGINE = InnoDB');

		## Create Table feature
		$this->dbforge->add_field(array(
			'feature_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'feature_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => FALSE,

			),
			'show_on_website' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
		));
		$this->dbforge->add_key("feature_id",true);
		$this->dbforge->create_table("feature", TRUE);
		$this->db->query('ALTER TABLE  `feature` ENGINE = InnoDB');

		## Create Table floor
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'floor_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => FALSE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("floor", TRUE);
		$this->db->query('ALTER TABLE  `floor` ENGINE = InnoDB');

		## Create Table folio
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'booking_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'folio_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,

			),
			'customer_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP',
			'is_deleted' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'default' => '0',

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("folio", TRUE);
		$this->db->query('ALTER TABLE  `folio` ENGINE = InnoDB');

		## Create Table group
		$this->dbforge->add_field(array(
			'group_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'group_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,

			),
		));
		$this->dbforge->add_key("group_id",true);
		$this->dbforge->create_table("group", TRUE);
		$this->db->query('ALTER TABLE  `group` ENGINE = InnoDB');

		## Create Table guider
		$this->dbforge->add_field(array(
			'guider_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'title' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
			'description' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
			'location' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
			'attachTo' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
			'placement' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
		));

		$this->dbforge->create_table("guider", TRUE);
		$this->db->query('ALTER TABLE  `guider` ENGINE = InnoDB');

		## Create Table hoteli_pay_bank_details
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'bank_code' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => TRUE,

			),
			'owner_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => TRUE,

			),
			'cpf_cnpj' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => TRUE,

			),
			'agency' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'bank_account_number' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'bank_account_digit' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'account_type' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => TRUE,

			),
			'amount' => array(
				'type' => 'DECIMAL',
				'constraint' => "10,2",
				'null' => TRUE,

			),
			'transfer_id' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'transfer_status' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => TRUE,

			),
			'created_date' => array(
				'type' => 'DATETIME',
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("hoteli_pay_bank_details", TRUE);
		$this->db->query('ALTER TABLE  `hoteli_pay_bank_details` ENGINE = InnoDB');

		## Create Table image
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'image_group_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => TRUE,

			),
			'filename' => array(
				'type' => 'VARCHAR',
				'constraint' => 32,
				'null' => TRUE,

			),
			'image_order' => array(
				'type' => 'INT',
				'constraint' => 3,
				'null' => TRUE,
				'default' => '0',

			),
			'caption' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("image", TRUE);
		$this->db->query('ALTER TABLE  `image` ENGINE = InnoDB');

		## Create Table image_group
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'image_type_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("image_group", TRUE);
		$this->db->query('ALTER TABLE  `image_group` ENGINE = InnoDB');

		## Create Table image_type
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
			'width' => array(
				'type' => 'INT',
				'constraint' => 5,
				'null' => TRUE,

			),
			'height' => array(
				'type' => 'INT',
				'constraint' => 5,
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("image_type", TRUE);
		$this->db->query('ALTER TABLE  `image_type` ENGINE = InnoDB');

		## Create Table import_mapping
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'new_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'old_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'company_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => TRUE,

			),
			'type' => array(
				'type' => 'VARCHAR',
				'constraint' => 230,
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("import_mapping", TRUE);
		$this->db->query('ALTER TABLE  `import_mapping` ENGINE = InnoDB');

		## Create Table invoice
		$this->dbforge->add_field(array(
			'invoice_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'invoice_number' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("invoice_id",true);
		$this->dbforge->create_table("invoice", TRUE);
		$this->db->query('ALTER TABLE  `invoice` ENGINE = InnoDB');

		## Create Table invoice_log
		$this->dbforge->add_field(array(
			'booking_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'date_time' => array(
				'type' => 'DATETIME',
				'null' => TRUE,

			),
			'user_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'action_id' => array(
				'type' => 'INT',
				'constraint' => 2,
				'null' => TRUE,

			),
			'log' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
			'charge_or_payment_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'new_amount' => array(
				'type' => 'DECIMAL',
				'constraint' => "10,2",
				'null' => TRUE,

			),
		));

		$this->dbforge->create_table("invoice_log", TRUE);
		$this->db->query('ALTER TABLE  `invoice_log` ENGINE = InnoDB');

		## Create Table key
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'key' => array(
				'type' => 'VARCHAR',
				'constraint' => 40,
				'null' => FALSE,

			),
			'level' => array(
				'type' => 'INT',
				'constraint' => 2,
				'null' => FALSE,

			),
			'ignore_limits' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'date_created' => array(
				'type' => 'DATE',
				'null' => FALSE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("key", TRUE);
		$this->db->query('ALTER TABLE  `key` ENGINE = InnoDB');

		## Create Table key_x_company
		$this->dbforge->add_field(array(
			'key_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
		));

		$this->dbforge->create_table("key_x_company", TRUE);
		$this->db->query('ALTER TABLE  `key_x_company` ENGINE = InnoDB');

		## Create Table language
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'language_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,

			),
			'is_default_lang' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,

			),
			'is_enable' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'version' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,
				'default' => '1',

			),
			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP',
			'flag' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("language", TRUE);
		$this->db->query('ALTER TABLE  `language` ENGINE = InnoDB');

		## Create Table language_phrase
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'phrase_keyword' => array(
				'type' => 'TEXT',
				'null' => FALSE,

			),
			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP',
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("language_phrase", TRUE);
		$this->db->query('ALTER TABLE  `language_phrase` ENGINE = InnoDB');

		## Create Table language_translation
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'phrase_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'language_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'phrase' => array(
				'type' => 'MEDIUMTEXT',
				'null' => FALSE,

			),
			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP',
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("language_translation", TRUE);
		$this->db->query('ALTER TABLE  `language_translation` ENGINE = InnoDB');

		## Create Table login_attempt
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'ip_address' => array(
				'type' => 'VARCHAR',
				'constraint' => 40,
				'null' => FALSE,

			),
			'login' => array(
				'type' => 'VARCHAR',
				'constraint' => 50,
				'null' => FALSE,

			),
			'`time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP',
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("login_attempt", TRUE);
		$this->db->query('ALTER TABLE  `login_attempt` ENGINE = InnoDB');

		## Create Table menu
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,

			),
			'link' => array(
				'type' => 'MEDIUMTEXT',
				'null' => FALSE,

			),
			'icon' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,

			),
			'parent_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'partner_type_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("menu", TRUE);
		$this->db->query('ALTER TABLE  `menu` ENGINE = InnoDB');

		## Create Table minical_installation_meta
		$this->dbforge->add_field(array(
			'pointer' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'error' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
		));

		$this->dbforge->create_table("minical_installation_meta", TRUE);
		$this->db->query('ALTER TABLE  `minical_installation_meta` ENGINE = InnoDB');

		## Create Table night_audit_log
		$this->dbforge->add_field(array(
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'selling_date' => array(
				'type' => 'DATE',
				'null' => FALSE,

			),
			'local_time' => array(
				'type' => 'DATETIME',
				'null' => FALSE,

			),
			'user_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'response' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
		));

		$this->dbforge->create_table("night_audit_log", TRUE);
		$this->db->query('ALTER TABLE  `night_audit_log` ENGINE = InnoDB');

		## Create Table online_booking_engine_field
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'show_on_booking_form' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'is_required' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
		));

		$this->dbforge->create_table("online_booking_engine_field", TRUE);
		$this->db->query('ALTER TABLE  `online_booking_engine_field` ENGINE = InnoDB');

		## Create Table options
		$this->dbforge->add_field(array(
			'option_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'company_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'option_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 191,
				'null' => FALSE,

			),
			'option_value' => array(
				'type' => 'LONGTEXT',
				'null' => FALSE,

			),
			'autoload' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
		));
		$this->dbforge->add_key("option_id",true);
		$this->dbforge->create_table("options", TRUE);
		$this->db->query('ALTER TABLE  `options` ENGINE = InnoDB');

		## Create Table ota_bookings
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'ota_booking_id' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => FALSE,

			),
			'ota_type' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => TRUE,

			),
			'booking_type' => array(
				'type' => 'VARCHAR',
				'constraint' => 10,
				'null' => TRUE,

			),
			'create_date_time' => array(
				'type' => 'DATETIME',
				'null' => TRUE,

			),
			'pms_booking_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'check_in_date' => array(
				'type' => 'DATE',
				'null' => TRUE,

			),
			'check_out_date' => array(
				'type' => 'DATE',
				'null' => TRUE,

			),
			'xml_out' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("ota_bookings", TRUE);
		$this->db->query('ALTER TABLE  `ota_bookings` ENGINE = InnoDB');

		## Create Table ota_manager
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'ota_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => TRUE,

			),
			'email' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'password' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'meta_data' => array(
				'type' => 'TEXT',
				'null' => FALSE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'created_date' => array(
				'type' => 'DATETIME',
				'null' => FALSE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("ota_manager", TRUE);
		$this->db->query('ALTER TABLE  `ota_manager` ENGINE = InnoDB');

		## Create Table ota_properties
		$this->dbforge->add_field(array(
			'ota_manager_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'channex_property_data' => array(
				'type' => 'LONGTEXT',
				'null' => FALSE,

			),
		));

		$this->dbforge->create_table("ota_properties", TRUE);
		$this->db->query('ALTER TABLE  `ota_properties` ENGINE = InnoDB');

		## Create Table ota_rate_plans
		$this->dbforge->add_field(array(
			'ota_x_company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'ota_rate_plan_id' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'minical_rate_plan_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'ota_room_type_id' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'rate_update_type' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'company_id' => array(
				'type' => 'INT',
				'constraint' => 10,
				'null' => TRUE,

			),
		));

		$this->dbforge->create_table("ota_rate_plans", TRUE);
		$this->db->query('ALTER TABLE  `ota_rate_plans` ENGINE = InnoDB');

		## Create Table ota_room_types
		$this->dbforge->add_field(array(
			'ota_x_company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'ota_room_type_id' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,

			),
			'minical_room_type_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'company_id' => array(
				'type' => 'INT',
				'constraint' => 10,
				'null' => TRUE,

			),
		));

		$this->dbforge->create_table("ota_room_types", TRUE);
		$this->db->query('ALTER TABLE  `ota_room_types` ENGINE = InnoDB');

		## Create Table ota_x_company
		$this->dbforge->add_field(array(
			'ota_x_company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'ota_manager_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'rate_update_type' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'ota_property_id' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,

			),
			'is_active' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,

			),
			'is_extra_charge' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '1',

			),
		));
		$this->dbforge->add_key("ota_x_company_id",true);
		$this->dbforge->create_table("ota_x_company", TRUE);
		$this->db->query('ALTER TABLE  `ota_x_company` ENGINE = InnoDB');

		## Create Table ota_xml_logs
		$this->dbforge->add_field(array(
			'xml_log_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'xml_in' => array(
				'type' => 'LONGTEXT',
				'null' => TRUE,

			),
			'xml_out' => array(
				'type' => 'LONGTEXT',
				'null' => TRUE,

			),
			'`datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP',
			'request_type' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,

			),
			'response_type' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,

			),
			'ota_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => TRUE,

			),
			'ota_property_id' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("xml_log_id",true);
		$this->dbforge->create_table("ota_xml_logs", TRUE);
		$this->db->query('ALTER TABLE  `ota_xml_logs` ENGINE = InnoDB');

		## Create Table otas
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'key' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => FALSE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("otas", TRUE);
		$this->db->query('ALTER TABLE  `otas` ENGINE = InnoDB');

		## Create Table payment
		$this->dbforge->add_field(array(
			'payment_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'description' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'date_time' => array(
				'type' => 'DATETIME',
				'null' => TRUE,

			),
			'booking_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'amount' => array(
				'type' => 'DECIMAL',
				'constraint' => "10,2",
				'null' => TRUE,

			),
			'payment_type_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'credit_card_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'selling_date' => array(
				'type' => 'DATE',
				'null' => TRUE,

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
			'user_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'customer_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'payment_gateway_used' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'gateway_charge_id' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'read_only' => array(
				'type' => 'INT',
				'constraint' => 1,
				'null' => TRUE,

			),
			'payment_status' => array(
				'type' => 'VARCHAR',
				'constraint' => 20,
				'null' => TRUE,

			),
			'parent_charge_id' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'is_captured' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'logs' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
			'payment_link_id' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("payment_id",true);
		$this->dbforge->create_table("payment", TRUE);
		$this->db->query('ALTER TABLE  `payment` ENGINE = InnoDB');

		## Create Table payment_folio
		$this->dbforge->add_field(array(
			'payment_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'folio_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
		));

		$this->dbforge->create_table("payment_folio", TRUE);
		$this->db->query('ALTER TABLE  `payment_folio` ENGINE = InnoDB');

		## Create Table payment_type
		$this->dbforge->add_field(array(
			'payment_type_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'payment_type' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => TRUE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
			'is_read_only' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
		));
		$this->dbforge->add_key("payment_type_id",true);
		$this->dbforge->create_table("payment_type", TRUE);
		$this->db->query('ALTER TABLE  `payment_type` ENGINE = InnoDB');

		## Create Table postmeta
		$this->dbforge->add_field(array(
			'meta_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'post_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'default' => '0',

			),
			'meta_key' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'meta_value' => array(
				'type' => 'LONGTEXT',
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("meta_id",true);
		$this->dbforge->create_table("postmeta", TRUE);
		$this->db->query('ALTER TABLE  `postmeta` ENGINE = InnoDB');

		## Create Table posts
		$this->dbforge->add_field(array(
			'post_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'company_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'user_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'default' => '0',

			),
			'post_date' => array(
				'type' => 'DATETIME',
				'null' => TRUE,
                
			),
			'post_content' => array(
				'type' => 'LONGTEXT',
				'null' => TRUE,

			),
			'post_title' => array(
				'type' => 'TEXT',
				'null' => TRUE,

			),
			'post_status' => array(
				'type' => 'VARCHAR',
				'constraint' => 20,
				'null' => FALSE,
				'default' => 'publish',

			),
			'post_modified' => array(
				'type' => 'DATETIME',
				'null' => TRUE,

			),
			'post_parent' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'default' => '0',

			),
			'guid' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,

			),
			'sort_order' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'default' => '0',

			),
			'post_type' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,
				'default' => 'post',

			),
			'post_mime_type' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => FALSE,

			),
			'is_deleted' => array(
				'type' => 'INT',
				'constraint' => 4,
				'null' => FALSE,
				'default' => '0',

			),
		));
		$this->dbforge->add_key("post_id",true);
		$this->dbforge->create_table("posts", TRUE);
		$this->db->query('ALTER TABLE  `posts` ENGINE = InnoDB');

		## Create Table property_build
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'property_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'property_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,

			),
			'setting_json' => array(
				'type' => 'TEXT',
				'null' => FALSE,

			),
			'dependences_json' => array(
				'type' => 'TEXT',
				'null' => FALSE,

			),
		));

		$this->dbforge->create_table("property_build", TRUE);
		$this->db->query('ALTER TABLE  `property_build` ENGINE = InnoDB');

		## Create Table rate
		$this->dbforge->add_field(array(
			'rate_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'rate_plan_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'base_rate' => array(
				'type' => 'DECIMAL',
				'constraint' => "10,2",
				'null' => TRUE,

			),
			'adult_1_rate' => array(
				'type' => 'DECIMAL',
				'constraint' => "10,2",
				'null' => TRUE,

			),
			'adult_2_rate' => array(
				'type' => 'DECIMAL',
				'constraint' => "10,2",
				'null' => TRUE,

			),
			'adult_3_rate' => array(
				'type' => 'DECIMAL',
				'constraint' => "10,2",
				'null' => TRUE,

			),
			'adult_4_rate' => array(
				'type' => 'DECIMAL',
				'constraint' => "10,2",
				'null' => TRUE,

			),
			'additional_adult_rate' => array(
				'type' => 'DECIMAL',
				'constraint' => "10,2",
				'null' => TRUE,

			),
			'additional_child_rate' => array(
				'type' => 'DECIMAL',
				'constraint' => "10,2",
				'null' => TRUE,

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,

			),
			'minimum_length_of_stay' => array(
				'type' => 'INT',
				'constraint' => 4,
				'null' => TRUE,

			),
			'maximum_length_of_stay' => array(
				'type' => 'INT',
				'constraint' => 4,
				'null' => TRUE,

			),
			'minimum_length_of_stay_arrival' => array(
				'type' => 'INT',
				'constraint' => 4,
				'null' => TRUE,

			),
			'maximum_length_of_stay_arrival' => array(
				'type' => 'INT',
				'constraint' => 4,
				'null' => TRUE,

			),
			'closed_to_arrival' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,

			),
			'closed_to_departure' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,

			),
			'can_be_sold_online' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '1',

			),
		));
		$this->dbforge->add_key("rate_id",true);
		$this->dbforge->create_table("rate", TRUE);
		$this->db->query('ALTER TABLE  `rate` ENGINE = InnoDB');

		## Create Table rate_plan
		$this->dbforge->add_field(array(
			'rate_plan_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'rate_plan_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => TRUE,

			),
			'room_type_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'charge_type_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'description' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'number_of_adults_included_for_base_rate' => array(
				'type' => 'INT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '4',

			),
			'currency_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'base_rate_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'is_selectable' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '1',

			),
			'image_group_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'is_shown_in_online_booking_engine' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '1',

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'parent_rate_plan_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'policy_code' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'`date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ',
		));
		$this->dbforge->add_key("rate_plan_id",true);
		$this->dbforge->create_table("rate_plan", TRUE);
		$this->db->query('ALTER TABLE  `rate_plan` ENGINE = InnoDB');

		## Create Table rate_plan_x_extra
		$this->dbforge->add_field(array(
			'room_type_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'extra_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'rate_plan_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
		));

		$this->dbforge->create_table("rate_plan_x_extra", TRUE);
		$this->db->query('ALTER TABLE  `rate_plan_x_extra` ENGINE = InnoDB');

		## Create Table rate_supplied
		$this->dbforge->add_field(array(
			'rate_supplied_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'rate_plan_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'room_type_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'supplied_adult_1_rate' => array(
				'type' => 'DECIMAL',
				'constraint' => "10,2",
				'null' => TRUE,

			),
			'supplied_adult_2_rate' => array(
				'type' => 'DECIMAL',
				'constraint' => "10,2",
				'null' => TRUE,

			),
			'supplied_adult_3_rate' => array(
				'type' => 'DECIMAL',
				'constraint' => "10,2",
				'null' => TRUE,

			),
			'supplied_adult_4_rate' => array(
				'type' => 'DECIMAL',
				'constraint' => "10,2",
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("rate_supplied_id",true);
		$this->dbforge->create_table("rate_supplied", TRUE);
		$this->db->query('ALTER TABLE  `rate_supplied` ENGINE = InnoDB');

		## Create Table review_management
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'ota_id' => array(
				'type' => 'INT',
				'constraint' => 10,
				'null' => FALSE,

			),
			'review_link' => array(
				'type' => 'TEXT',
				'null' => FALSE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("review_management", TRUE);
		$this->db->query('ALTER TABLE  `review_management` ENGINE = InnoDB');

		## Create Table room
		$this->dbforge->add_field(array(
			'room_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 56,
				'null' => FALSE,

			),
			'room_type_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'status' => array(
				'type' => 'VARCHAR',
				'constraint' => 10,
				'null' => FALSE,
				'default' => 'Clean',

			),
			'notes' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
			'room_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'can_be_sold_online' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'group_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'default' => '0',

			),
			'floor_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'default' => '0',

			),
			'location_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'default' => '0',

			),
			'sort_order' => array(
				'type' => 'INT',
				'constraint' => 10,
				'null' => FALSE,
				'default' => '0',

			),
			'is_hidden' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'null' => FALSE,
				'default' => '0',

			),
			'score' => array(
				'type' => 'FLOAT',
				'null' => FALSE,
				'default' => '0',

			),
			'instructions' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("room_id",true);
		$this->dbforge->create_table("room", TRUE);
		$this->db->query('ALTER TABLE  `room` ENGINE = InnoDB');

		## Create Table room_location
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'location_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => FALSE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("room_location", TRUE);
		$this->db->query('ALTER TABLE  `room_location` ENGINE = InnoDB');

		## Create Table room_type
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => 50,
				'null' => FALSE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'acronym' => array(
				'type' => 'VARCHAR',
				'constraint' => 6,
				'null' => TRUE,

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
			'max_occupancy' => array(
				'type' => 'INT',
				'constraint' => 10,
				'null' => FALSE,
				'default' => '0',

			),
			'min_occupancy' => array(
				'type' => 'INT',
				'constraint' => 10,
				'null' => FALSE,
				'default' => '0',

			),
			'max_adults' => array(
				'type' => 'INT',
				'constraint' => 2,
				'null' => TRUE,
				'default' => '4',

			),
			'max_children' => array(
				'type' => 'INT',
				'constraint' => 2,
				'null' => TRUE,
				'default' => '4',

			),
			'image_group_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'description' => array(
				'type' => 'MEDIUMTEXT',
				'null' => TRUE,

			),
			'can_be_sold_online' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '1',

			),
			'ota_close_out_threshold' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'default' => '1',

			),
			'sort' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'default' => '0',

			),
			'default_room_charge' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'prevent_inline_booking' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("room_type", TRUE);
		$this->db->query('ALTER TABLE  `room_type` ENGINE = InnoDB');

		## Create Table sessions
		$this->dbforge->add_field(array(
			'session_id' => array(
				'type' => 'VARCHAR',
				'constraint' => 40,
				'null' => FALSE,
				'default' => '0',

			),
			'ip_address' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => FALSE,
				'default' => '0',

			),
			'user_agent' => array(
				'type' => 'VARCHAR',
				'constraint' => 120,
				'null' => TRUE,

			),
			'last_activity' => array(
				'type' => 'INT',
				'constraint' => 1,
				'unsigned' => TRUE,
				'null' => FALSE,
				'default' => '0',

			),
			'user_data' => array(
				'type' => 'TEXT',
				'null' => FALSE,

			),
		));

		$this->dbforge->create_table("sessions", TRUE);
		$this->db->query('ALTER TABLE  `sessions` ENGINE = InnoDB');

		## Create Table statement
		$this->dbforge->add_field(array(
			'statement_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'statement_number' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => TRUE,

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'creation_date' => array(
				'type' => 'DATETIME',
				'null' => FALSE,

			),
			'statement_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,

			),
		));
		$this->dbforge->add_key("statement_id",true);
		$this->dbforge->create_table("statement", TRUE);
		$this->db->query('ALTER TABLE  `statement` ENGINE = InnoDB');

		## Create Table subscription_restriction
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'subscription_plan' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,

			),
			'controller' => array(
				'type' => 'VARCHAR',
				'constraint' => 200,
				'null' => FALSE,

			),
			'function' => array(
				'type' => 'VARCHAR',
				'constraint' => 200,
				'null' => FALSE,

			),
		));

		$this->dbforge->create_table("subscription_restriction", TRUE);
		$this->db->query('ALTER TABLE  `subscription_restriction` ENGINE = InnoDB');

		## Create Table tax_price_bracket
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'tax_type_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'start_range' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => TRUE,

			),
			'end_range' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => TRUE,

			),
			'tax_rate' => array(
				'type' => 'FLOAT',
				'null' => FALSE,

			),
			'is_percentage' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'is_deleted' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("tax_price_bracket", TRUE);
		$this->db->query('ALTER TABLE  `tax_price_bracket` ENGINE = InnoDB');

		## Create Table tax_type
		$this->dbforge->add_field(array(
			'tax_type' => array(
				'type' => 'VARCHAR',
				'constraint' => 35,
				'null' => FALSE,

			),
			'tax_rate' => array(
				'type' => 'DECIMAL',
				'constraint' => 10,3,
				'null' => FALSE,
				'default' => '0.000',

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'tax_type_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
			'is_percentage' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '1',

			),
			'is_brackets_active' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'is_tax_inclusive' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
		));
		$this->dbforge->add_key("tax_type_id",true);
		$this->dbforge->create_table("tax_type", TRUE);
		$this->db->query('ALTER TABLE  `tax_type` ENGINE = InnoDB');

		## Create Table user_autologin
		$this->dbforge->add_field(array(
			'key_id' => array(
				'type' => 'CHAR',
				'constraint' => 32,
				'null' => FALSE,

			),
			'user_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'default' => '0',

			),
			'user_agent' => array(
				'type' => 'VARCHAR',
				'constraint' => 150,
				'null' => FALSE,

			),
			'last_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => 40,
				'null' => FALSE,

			),
			'`last_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP',
		));

		$this->dbforge->create_table("user_autologin", TRUE);
		$this->db->query('ALTER TABLE  `user_autologin` ENGINE = InnoDB');

		## Create Table user_permissions
		$this->dbforge->add_field(array(
			'permission_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'user_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'permission' => array(
				'type' => 'VARCHAR',
				'constraint' => 25,
				'null' => TRUE,

			),
		));
		$this->dbforge->add_key("permission_id",true);
		$this->dbforge->create_table("user_permissions", TRUE);
		$this->db->query('ALTER TABLE  `user_permissions` ENGINE = InnoDB');

		## Create Table user_profiles
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'user_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'current_company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => TRUE,

			),
			'first_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 35,
				'null' => TRUE,

			),
			'last_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 35,
				'null' => TRUE,

			),
			'language' => array(
				'type' => 'VARCHAR',
				'constraint' => 15,
				'null' => FALSE,
				'default' => 'english',

			),
			'language_id' => array(
				'type' => 'INT',
				'constraint' => 4,
				'null' => FALSE,
				'default' => '0',

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("user_profiles", TRUE);
		$this->db->query('ALTER TABLE  `user_profiles` ENGINE = InnoDB');

		## Create Table user_roles
		$this->dbforge->add_field(array(
			'user_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'company_id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,

			),
			'role' => array(
				'type' => 'INT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
		));

		$this->dbforge->create_table("user_roles", TRUE);
		$this->db->query('ALTER TABLE  `user_roles` ENGINE = InnoDB');

		## Create Table user_x_guider
		$this->dbforge->add_field(array(
			'user_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'current_guider_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,

			),
			'completed_at' => array(
				'type' => 'DATETIME',
				'null' => TRUE,

			),
		));

		$this->dbforge->create_table("user_x_guider", TRUE);
		$this->db->query('ALTER TABLE  `user_x_guider` ENGINE = InnoDB');

		## Create Table users
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 2,
				'unsigned' => TRUE,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'partner_id' => array(
				'type' => 'INT',
				'constraint' => 50,
				'null' => FALSE,
				'default' => '0',

			),
			'username' => array(
				'type' => 'VARCHAR',
				'constraint' => 50,
				'null' => TRUE,

			),
			'password' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,

			),
			'email' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => FALSE,

			),
			'activated' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'banned' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'ban_reason' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'new_password_key' => array(
				'type' => 'VARCHAR',
				'constraint' => 50,
				'null' => TRUE,

			),
			'new_password_requested' => array(
				'type' => 'DATETIME',
				'null' => TRUE,

			),
			'new_email' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => TRUE,

			),
			'new_email_key' => array(
				'type' => 'VARCHAR',
				'constraint' => 50,
				'null' => TRUE,

			),
			'last_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => 40,
				'null' => TRUE,

			),
			'last_login' => array(
				'type' => 'DATETIME',
				'null' => TRUE,

			),
			'created' => array(
				'type' => 'DATETIME',
				'null' => FALSE,

			),
			'`modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP',
			'accepted_TOS' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,

			),
			'completed_tutorial' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'is_reminder_hidden' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'tos_agreed_date' => array(
				'type' => 'DATE',
				'null' => TRUE,

			),
			'is_admin' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',

			),
			'is_overview_calendar' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("users", TRUE);
		$this->db->query('ALTER TABLE  `users` ENGINE = InnoDB');

		## Create Table whitelabel_partner
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 50,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'type_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'default' => '1',

			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => FALSE,

			),
			'username' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => FALSE,

			),
			'logo' => array(
				'type' => 'VARCHAR',
				'constraint' => 250,
				'null' => FALSE,

			),
			'admin_user_id' => array(
				'type' => 'BIGINT',
				'constraint' => 255,
				'null' => FALSE,

			),
			'status' => array(
				'type' => 'ENUM("0","1")',
				'null' => FALSE,

			),
			'is_deleted' => array(
				'type' => 'ENUM("0","1")',
				'null' => FALSE,

			),
			'domain' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,

			),
			'privacy_policy' => array(
				'type' => 'TINYTEXT',
				'null' => FALSE,

			),
			'terms_of_service' => array(
				'type' => 'TINYTEXT',
				'null' => FALSE,

			),
			'timezone' => array(
				'type' => 'MEDIUMTEXT',
				'null' => FALSE,

			),
			'currency_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => FALSE,

			),
			'location' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,

			),
			'support_email' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,

			),
			'do_not_reply_email' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,

			),
			'intercom_app_id' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => TRUE,

			),
			'auto_close_io' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '0',

			),
			'logo_image_group_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'overbooking_alert_email' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,

			),
			'show_on_partners_page' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => '1',

			),
			'website' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE

			),
			'default_property_status' => array(
				'type' => 'VARCHAR',
				'constraint' => 25,
				'null' => FALSE,
				'default' => 'active',

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("whitelabel_partner", TRUE);
		$this->db->query('ALTER TABLE  `whitelabel_partner` ENGINE = InnoDB');

		## Create Table whitelabel_partner_type
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,

			),
			'slug' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,

			),
			'is_deleted' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("whitelabel_partner_type", TRUE);
		$this->db->query('ALTER TABLE  `whitelabel_partner_type` ENGINE = InnoDB');

		## Create Table whitelabel_partner_x_admin
		$this->dbforge->add_field(array(
			'partner_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
			'admin_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'null' => TRUE,

			),
		));

		$this->dbforge->create_table("whitelabel_partner_x_admin", TRUE);
		$this->db->query('ALTER TABLE  `whitelabel_partner_x_admin` ENGINE = InnoDB');
	 }

	public function down()	{
		### Drop table booking ##
		$this->dbforge->drop_table("booking", TRUE);
		### Drop table booking_block ##
		$this->dbforge->drop_table("booking_block", TRUE);
		### Drop table booking_field ##
		$this->dbforge->drop_table("booking_field", TRUE);
		### Drop table booking_linked_group ##
		$this->dbforge->drop_table("booking_linked_group", TRUE);
		### Drop table booking_log ##
		$this->dbforge->drop_table("booking_log", TRUE);
		### Drop table booking_review ##
		$this->dbforge->drop_table("booking_review", TRUE);
		### Drop table booking_source ##
		$this->dbforge->drop_table("booking_source", TRUE);
		### Drop table booking_staying_customer_list ##
		$this->dbforge->drop_table("booking_staying_customer_list", TRUE);
		### Drop table booking_x_booking_field ##
		$this->dbforge->drop_table("booking_x_booking_field", TRUE);
		### Drop table booking_x_booking_linked_group ##
		$this->dbforge->drop_table("booking_x_booking_linked_group", TRUE);
		### Drop table booking_x_extra ##
		$this->dbforge->drop_table("booking_x_extra", TRUE);
		### Drop table booking_x_group ##
		$this->dbforge->drop_table("booking_x_group", TRUE);
		### Drop table booking_x_invoice ##
		$this->dbforge->drop_table("booking_x_invoice", TRUE);
		### Drop table booking_x_statement ##
		$this->dbforge->drop_table("booking_x_statement", TRUE);
		### Drop table charge ##
		$this->dbforge->drop_table("charge", TRUE);
		### Drop table charge_folio ##
		$this->dbforge->drop_table("charge_folio", TRUE);
		### Drop table charge_type ##
		$this->dbforge->drop_table("charge_type", TRUE);
		### Drop table charge_type_tax_list ##
		$this->dbforge->drop_table("charge_type_tax_list", TRUE);
		### Drop table common_booking_source_setting ##
		$this->dbforge->drop_table("common_booking_source_setting", TRUE);
		### Drop table common_customer_fields_setting ##
		$this->dbforge->drop_table("common_customer_fields_setting", TRUE);
		### Drop table common_customer_type_setting ##
		$this->dbforge->drop_table("common_customer_type_setting", TRUE);
		### Drop table company ##
		$this->dbforge->drop_table("company", TRUE);
		### Drop table company_admin_panel_info ##
		$this->dbforge->drop_table("company_admin_panel_info", TRUE);
		### Drop table company_charge ##
		$this->dbforge->drop_table("company_charge", TRUE);
		### Drop table company_payment_gateway ##
		$this->dbforge->drop_table("company_payment_gateway", TRUE);
		### Drop table company_subscription ##
		$this->dbforge->drop_table("company_subscription", TRUE);
		### Drop table company_x_currency ##
		$this->dbforge->drop_table("company_x_currency", TRUE);
		### Drop table company_x_tag ##
		$this->dbforge->drop_table("company_x_tag", TRUE);
		### Drop table cron_email_log ##
		$this->dbforge->drop_table("cron_email_log", TRUE);
		### Drop table cron_log ##
		$this->dbforge->drop_table("cron_log", TRUE);
		### Drop table currency ##
		$this->dbforge->drop_table("currency", TRUE);
		### Drop table customer ##
		$this->dbforge->drop_table("customer", TRUE);
		### Drop table customer_card_detail ##
		$this->dbforge->drop_table("customer_card_detail", TRUE);
		### Drop table customer_field ##
		$this->dbforge->drop_table("customer_field", TRUE);
		### Drop table customer_type ##
		$this->dbforge->drop_table("customer_type", TRUE);
		### Drop table customer_x_customer_field ##
		$this->dbforge->drop_table("customer_x_customer_field", TRUE);
		### Drop table date_color ##
		$this->dbforge->drop_table("date_color", TRUE);
		### Drop table date_interval ##
		$this->dbforge->drop_table("date_interval", TRUE);
		### Drop table date_range ##
		$this->dbforge->drop_table("date_range", TRUE);
		### Drop table date_range_x_extra_rate ##
		$this->dbforge->drop_table("date_range_x_extra_rate", TRUE);
		### Drop table date_range_x_rate ##
		$this->dbforge->drop_table("date_range_x_rate", TRUE);
		### Drop table date_range_x_rate_plan_tbd ##
		$this->dbforge->drop_table("date_range_x_rate_plan_tbd", TRUE);
		### Drop table date_range_x_rate_supplied ##
		$this->dbforge->drop_table("date_range_x_rate_supplied", TRUE);
		### Drop table date_range_x_room_type ##
		$this->dbforge->drop_table("date_range_x_room_type", TRUE);
		### Drop table date_range_x_room_type_x_channel ##
		$this->dbforge->drop_table("date_range_x_room_type_x_channel", TRUE);
		### Drop table date_range_x_room_type_x_status ##
		$this->dbforge->drop_table("date_range_x_room_type_x_status", TRUE);
		### Drop table employee_log ##
		$this->dbforge->drop_table("employee_log", TRUE);
		### Drop table extensions_log ##
		$this->dbforge->drop_table("extensions_log", TRUE);
		### Drop table extensions_x_company ##
		$this->dbforge->drop_table("extensions_x_company", TRUE);
		### Drop table extensions_x_vendor ##
		$this->dbforge->drop_table("extensions_x_vendor", TRUE);
		### Drop table extra ##
		$this->dbforge->drop_table("extra", TRUE);
		### Drop table extra_rate ##
		$this->dbforge->drop_table("extra_rate", TRUE);
		### Drop table feature ##
		$this->dbforge->drop_table("feature", TRUE);
		### Drop table floor ##
		$this->dbforge->drop_table("floor", TRUE);
		### Drop table folio ##
		$this->dbforge->drop_table("folio", TRUE);
		### Drop table group ##
		$this->dbforge->drop_table("group", TRUE);
		### Drop table guider ##
		$this->dbforge->drop_table("guider", TRUE);
		### Drop table hoteli_pay_bank_details ##
		$this->dbforge->drop_table("hoteli_pay_bank_details", TRUE);
		### Drop table image ##
		$this->dbforge->drop_table("image", TRUE);
		### Drop table image_group ##
		$this->dbforge->drop_table("image_group", TRUE);
		### Drop table image_type ##
		$this->dbforge->drop_table("image_type", TRUE);
		### Drop table import_mapping ##
		$this->dbforge->drop_table("import_mapping", TRUE);
		### Drop table invoice ##
		$this->dbforge->drop_table("invoice", TRUE);
		### Drop table invoice_log ##
		$this->dbforge->drop_table("invoice_log", TRUE);
		### Drop table key ##
		$this->dbforge->drop_table("key", TRUE);
		### Drop table key_x_company ##
		$this->dbforge->drop_table("key_x_company", TRUE);
		### Drop table language ##
		$this->dbforge->drop_table("language", TRUE);
		### Drop table language_phrase ##
		$this->dbforge->drop_table("language_phrase", TRUE);
		### Drop table language_translation ##
		$this->dbforge->drop_table("language_translation", TRUE);
		### Drop table login_attempt ##
		$this->dbforge->drop_table("login_attempt", TRUE);
		### Drop table menu ##
		$this->dbforge->drop_table("menu", TRUE);
		### Drop table minical_installation_meta ##
		$this->dbforge->drop_table("minical_installation_meta", TRUE);
		### Drop table night_audit_log ##
		$this->dbforge->drop_table("night_audit_log", TRUE);
		### Drop table online_booking_engine_field ##
		$this->dbforge->drop_table("online_booking_engine_field", TRUE);
		### Drop table options ##
		$this->dbforge->drop_table("options", TRUE);
		### Drop table ota_bookings ##
		$this->dbforge->drop_table("ota_bookings", TRUE);
		### Drop table ota_manager ##
		$this->dbforge->drop_table("ota_manager", TRUE);
		### Drop table ota_properties ##
		$this->dbforge->drop_table("ota_properties", TRUE);
		### Drop table ota_rate_plans ##
		$this->dbforge->drop_table("ota_rate_plans", TRUE);
		### Drop table ota_room_types ##
		$this->dbforge->drop_table("ota_room_types", TRUE);
		### Drop table ota_x_company ##
		$this->dbforge->drop_table("ota_x_company", TRUE);
		### Drop table ota_xml_logs ##
		$this->dbforge->drop_table("ota_xml_logs", TRUE);
		### Drop table otas ##
		$this->dbforge->drop_table("otas", TRUE);
		### Drop table payment ##
		$this->dbforge->drop_table("payment", TRUE);
		### Drop table payment_folio ##
		$this->dbforge->drop_table("payment_folio", TRUE);
		### Drop table payment_type ##
		$this->dbforge->drop_table("payment_type", TRUE);
		### Drop table postmeta ##
		$this->dbforge->drop_table("postmeta", TRUE);
		### Drop table posts ##
		$this->dbforge->drop_table("posts", TRUE);
		### Drop table property_build ##
		$this->dbforge->drop_table("property_build", TRUE);
		### Drop table rate ##
		$this->dbforge->drop_table("rate", TRUE);
		### Drop table rate_plan ##
		$this->dbforge->drop_table("rate_plan", TRUE);
		### Drop table rate_plan_x_extra ##
		$this->dbforge->drop_table("rate_plan_x_extra", TRUE);
		### Drop table rate_supplied ##
		$this->dbforge->drop_table("rate_supplied", TRUE);
		### Drop table review_management ##
		$this->dbforge->drop_table("review_management", TRUE);
		### Drop table room ##
		$this->dbforge->drop_table("room", TRUE);
		### Drop table room_location ##
		$this->dbforge->drop_table("room_location", TRUE);
		### Drop table room_type ##
		$this->dbforge->drop_table("room_type", TRUE);
		### Drop table sessions ##
		$this->dbforge->drop_table("sessions", TRUE);
		### Drop table statement ##
		$this->dbforge->drop_table("statement", TRUE);
		### Drop table subscription_restriction ##
		$this->dbforge->drop_table("subscription_restriction", TRUE);
		### Drop table tax_price_bracket ##
		$this->dbforge->drop_table("tax_price_bracket", TRUE);
		### Drop table tax_type ##
		$this->dbforge->drop_table("tax_type", TRUE);
		### Drop table user_autologin ##
		$this->dbforge->drop_table("user_autologin", TRUE);
		### Drop table user_permissions ##
		$this->dbforge->drop_table("user_permissions", TRUE);
		### Drop table user_profiles ##
		$this->dbforge->drop_table("user_profiles", TRUE);
		### Drop table user_roles ##
		$this->dbforge->drop_table("user_roles", TRUE);
		### Drop table user_x_guider ##
		$this->dbforge->drop_table("user_x_guider", TRUE);
		### Drop table users ##
		$this->dbforge->drop_table("users", TRUE);
		### Drop table whitelabel_partner ##
		$this->dbforge->drop_table("whitelabel_partner", TRUE);
		### Drop table whitelabel_partner_type ##
		$this->dbforge->drop_table("whitelabel_partner_type", TRUE);
		### Drop table whitelabel_partner_x_admin ##
		$this->dbforge->drop_table("whitelabel_partner_x_admin", TRUE);

	}
}
