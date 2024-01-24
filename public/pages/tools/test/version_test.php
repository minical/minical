<?php

class Version_test extends Spark_test_case {

    function test_version()
    {
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('version'); 
        });
        $this->assertEquals(array(SPARK_VERSION), $clines);
    }

    function test_sources()
    {
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('sources');
        });
        $this->assertEquals($this->source_names, $clines);
    }

    function test_bad_command()
    {
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('fake');
        });
        $this->assertEquals(array(chr(27) . '[1;31m[ ERROR ]' . chr(27) . '[0m Uh-oh!', chr(27) . '[1;31m[ ERROR ]' . chr(27) . '[0m Unknown action: fake'), $clines);
    }

    function test_search_empty()
    {
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('search', array('nothing_found_here'));
        });
        $this->assertEquals(array(), $clines);
    }

}
