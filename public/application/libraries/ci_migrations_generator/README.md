# Codeigniter Migrations Generator

## Create a Base Migration File from current DB

Generate CodeIgniter 3.x Migrations from an existing database, including indexes and foreign keys!
When all goes well it will create a file under migrations called 001_create_base.php under your migrations folder


## To use:

1: Enable migrations and set version to 1;

2: Create and Enable to write migration folder ``application/migrations``;

3: Clone repository to your library folder (/application/library);   
``git clone git@github.com:fastworkx/ci_migrations_generator.git``

4: In controller:

```php
    function make_base(){

        $this->load->library('ci_migrations_generator/Sqltoci');

        // All Tables:

        $this->sqltoci->generate();

        //Single Table:

        $this->sqltoci->generate('table');

    }
```   

5: You'll get something like this ``applications/migration/001_create_base.php``.

```php
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_create_base extends CI_Migration {

	public function up() {
        
        ## Create Table sis_customer
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'VARCHAR',
				'constraint' => 40,
				'null' => FALSE,

			),
			'ip_address' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => FALSE,

			),
			'timestamp' => array(
				'type' => 'INT',
				'unsigned' => TRUE,
				'null' => FALSE,
				'default' => '0',

			),
			'data' => array(
				'type' => 'BLOB',
				'null' => FALSE,

			),
			'`datetime_reg` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ',
		));

		$this->dbforge->create_table("sessions", TRUE);
		$this->db->query('ALTER TABLE  `sessions` ENGINE = InnoDB');
		));


	public function down()	{

        ### Drop table sessions ##
		$this->dbforge->drop_table("sessions", TRUE);
	}
}
```

It's an improve of repository ``liaan/codeigniter_migration_base_generation``;

