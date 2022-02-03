<?php

$file = '../../vendor/autoload.php';
if (file_exists($file)) {
    include_once $file;
}

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../')->load();

$dbHost = getenv("DATABASE_HOST");
$dbUser = getenv("DATABASE_USER");
$dbPass = getenv("DATABASE_PASS");
$dbName = getenv("DATABASE_NAME");

$mysqli_connection = @mysqli_connect("$dbHost", "$dbUser", "$dbPass", "$dbName");
if (!$mysqli_connection) {
    echo json_encode(array('success' => false, 'message' => "Database connection failed with error: " . mysqli_connect_error()), true);
    return;
}else{
    
    $query = "SELECT count(*) AS TOTALNUMBEROFTABLES  FROM INFORMATION_SCHEMA.TABLES  WHERE TABLE_SCHEMA = '$dbName'";
     if($result = mysqli_query($mysqli_connection, $query)){
            $row = mysqli_fetch_row($result);
            echo json_encode(array('success' => true, 'message' => $row[0]), true);
            return;
        }
    echo json_encode(array('success' => false, 'error' => 'Database validation failed'), true);  
    return;
}


