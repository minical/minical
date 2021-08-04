<?php

// your config
$file = '../vendor/autoload.php';
if (file_exists($file)) {
    include_once $file;
}

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__. '/../')->load();
$filename = "minical.sql";

$dbHost = getenv("DATABASE_HOST");
$dbUser = getenv("DATABASE_USER");
$dbPass = getenv("DATABASE_PASS");
$dbName = getenv("DATABASE_NAME");

$maxRuntime = 3; // less then your max script execution limit
$deadline = time() + $maxRuntime;
$errorFilename = $filename . '_error'; // tmp file for erro

$mysqli_connection = mysqli_connect("$dbHost", "$dbUser", "$dbPass", "$dbName");
mysqli_select_db($mysqli_connection, $dbName) OR die('select db: ' . $dbName . ' failed: ' . mysqli_error($mysqli_connection));

($fp = fopen($filename, 'r')) OR die('failed to open file:' . $filename);

$sql = "SELECT pointer FROM minical_installation_meta";
if ($result = mysqli_query($mysqli_connection, $sql)) {
  // Fetch one and one row
    while ($row = mysqli_fetch_row($result)) {
        $file_position = $row[0];
    }
    fseek($fp, $file_position);
    mysqli_free_result($result);
} else {
    $file_position = 0;
}

$queryCount = 0;
$query = '';
while ($deadline > time() AND ($line = fgets($fp, 102400))) {
    if (substr($line, 0, 2) == '--' OR trim($line) == '') {
        continue;
    }
    $query .= $line;
    if (substr(trim($query), -1) == ';') {
        if (!mysqli_query($mysqli_connection, $query)) {

        }
        $query = '';
        if(mysqli_query($mysqli_connection, "SELECT 1 FROM minical_installation_meta LIMIT 1") == TRUE)
        {
            $file_position = ftell($fp);
            mysqli_query($mysqli_connection, "UPDATE minical_installation_meta SET pointer = $file_position");
            $modal_flag = 0;
        } else {
            $sql = "CREATE TABLE `minical_installation_meta` (
                        `pointer` bigint(20) NOT NULL,
                        `error` text
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            $result = mysqli_query($mysqli_connection, $sql);

            $file_position = ftell($fp);
            mysqli_query($mysqli_connection, "INSERT INTO minical_installation_meta (pointer, error) VALUES($file_position, 'no')");
        }
        $queryCount++;
    }
}
if (feof($fp)) {

    $project_url = getenv('PROJECT_URL');
    echo json_encode(array('success' => true, 'project_url' => trim($project_url)), true);
    return;
} 

echo json_encode(array('file_position' => $file_position, 'file_size' => filesize($filename)), true);

