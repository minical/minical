<?php

class Install_test extends Spark_test_case {

    function test_install_with_version()
    {
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('install', array('-v1.0', 'example-spark'));
        });
        $success = (bool) (strpos(end($clines), chr(27) . '[1;36m[ SPARK ]' .
            chr(27) . '[0m Spark installed') === 0);
        Spark_utils::remove_full_directory(SPARK_PATH . '/example-spark');
        $this->assertEquals(true, $success);
    }

    function test_install_without_version()
    {
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('install', array('example-spark'));
        });
        $success = (bool) (strpos(end($clines), chr(27) . '[1;36m[ SPARK ]' .
            chr(27) . '[0m Spark installed') === 0);
        Spark_utils::remove_full_directory(SPARK_PATH . '/example-spark');
        $this->assertEquals(true, $success);
    }

    function test_install_with_invalid_spark()
    {
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('install', array('jjks7878erHjhsjdkksj'));
        });
        $success = (bool) (strpos(end($clines), chr(27) . '[1;31m[ ERROR ]' .
            chr(27) . '[0m Unable to find spark') === 0);
        Spark_utils::remove_full_directory(SPARK_PATH . '/example-spark');
        $this->assertEquals(true, $success);
    }

    function test_install_with_invalid_spark_version()
    {
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('install', array('v9.4', 'example-spark'));
        });
        $success = (bool) (strpos(reset($clines), chr(27) . '[1;31m[ ERROR ]' .
            chr(27) . '[0m Uh-oh!') === 0);
        Spark_utils::remove_full_directory(SPARK_PATH . '/example-spark');
        $this->assertEquals(true, $success);
    }

}
