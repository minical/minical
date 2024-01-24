<?php

class Search_test extends Spark_test_case {

    function test_search()
    {
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('search', array('markdown'));
        });
        // Less than ideal, I know
        $this->assertEquals(array("\033[33mmarkdown\033[0m - A markdown helper for easy parsing of markdown"), $clines);
    }

}
