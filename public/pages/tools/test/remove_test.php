<?php

class Remove_test extends Spark_test_case {

    function test_remove_with_version()
    {
        // Test install with a version specified
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('install', array('-v1.0', 'example-spark')); // Spark needs installed first
            $cli->execute('remove', array('-v1.0', 'example-spark'));
        });
        $success = (bool) (strpos(end($clines), chr(27) . '[1;36m[ SPARK ]' . chr(27) . '[0m Spark removed') === 0 && ! is_dir(SPARK_PATH.'/example-spark'));
        $this->assertEquals(true, $success);
        Spark_utils::remove_full_directory(SPARK_PATH . '/example-spark');
    }

    function test_remove_without_flags() 
    {
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('install', array('-v1.0', 'example-spark')); // Spark needs installed first
            $cli->execute('remove', array('example-spark'));
        });
        $success = (bool) (strpos(end($clines), chr(27) . '[1;31m[ ERROR ]' . chr(27) . '[0m Please specify') === 0 && is_dir(SPARK_PATH.'/example-spark'));
        $this->assertEquals(true, $success);
        Spark_utils::remove_full_directory(SPARK_PATH . '/example-spark');
    }

    function test_remove_with_f_flag()
    {
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('install', array('-v1.0', 'example-spark')); // Spark needs installed first
            $cli->execute('remove', array('-f', 'example-spark'));
        });
        $success = (bool) (strpos(end($clines), chr(27) . '[1;36m[ SPARK ]' . chr(27) . '[0m Spark removed') === 0 && ! is_dir(SPARK_PATH.'/example-spark'));
        $this->assertEquals(true, $success);
        Spark_utils::remove_full_directory(SPARK_PATH . '/example-spark');
    }

    function test_remove_with_invalid_version()
    {
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('install', array('-v1.0', 'example-spark')); // Spark needs installed first
            $cli->execute('remove', array('-v9.4', 'example-spark'));
        });
        $success = (bool) (strpos(end($clines), chr(27) . '[1;36m[ SPARK ]' . chr(27) . '[0m Looks like that spark isn\'t installed') === 0 && is_dir(SPARK_PATH.'/example-spark'));
        $this->assertEquals(true, $success);
        Spark_utils::remove_full_directory(SPARK_PATH . '/example-spark');
    }

}
