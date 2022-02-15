<?php if (!defined('BASEPATH')) {
    exit("No direct script access allowed");
}

/**
 * Class Migrate
 *
 * To use migrations see https://ellislab.com/codeigniter/user-guide/libraries/migration.html
 *
 * tl;dr
 *
 * 1) In the folder /application/migrations create a new file with the filename 002_some_name
 * one version up, then adjust up and own logic
 *
 * 2) In the application/config/migration set the new migration number (2 in this case)
 *
 * 3a) To migrate to latest version
 * Open console - php index.php migrate
 *
 * 3b) To migrate to specific version
 * Open console - php index.php migrate ver 1
 *
 * To execute in browser minical.io/migrate
 *
 * @property CI_Migration migration
 */
class Migrate extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        // limit access to controller for CLI interface only, uncomment below:
        /*
        $this->input->is_cli_request()
        or exit("Execute via command line: php index.php migrate");
        */
        $this->load->library('migration');
    }

    public function index()
    {
        try {
            $this->migration->latest();
            printf("\n\n Migrated successfully \n\n");
        } catch (Exception $e) {
            print_r($e->getMessage());
            print_r($this->migration->error_string());
        }

    }

    public function ver($ver)
    {
        try {
            $this->migration->version($ver);
            printf("\n\n Migrated successfully \n\n");
        } catch (Exception $e) {
            print_r($e->getMessage());
            print_r($this->migration->error_string());
        }
    }

    function generate_migrations() {
        $this->load->library('ci_migrations_generator/Sqltoci');

        $this->sqltoci->generate();
    }
}