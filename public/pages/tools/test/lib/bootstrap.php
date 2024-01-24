<?php

define('SPARK_PATH', __DIR__ . '/test-sparks');

require __DIR__ . '/../../lib/spark/spark_cli.php';

class Spark_test_case extends PHPUnit_Framework_TestCase {

    function setUp()
    {
        $this->source_names[] = 'getsparks.org';
        $this->sources = array_map(function($n) {
            return new Spark_source($n);
        }, $this->source_names);
        $this->cli = new Spark_CLI($this->sources);
    }

    function tearDown()
    {
        if (is_dir(SPARK_PATH . '/example-spark'))
        {
            Spark_utils::remove_full_directory(SPARK_PATH . '/example-spark');
        }
    }

    protected function capture_buffer_lines($func) {
        ob_start();
        $func($this->cli); 
        $t = ob_get_contents();
        ob_end_clean();
        if ($t == '') return array(); // empty
        return explode("\n", substr($t, 0, count($t) - 2));
    }
}
