<?php
$file = '../../vendor/autoload.php';
if (file_exists($file)) {
    include_once $file;
}
$envfile = '../../.env';
if (file_exists($envfile)) {
    
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../')->load();

$dbHost = getenv("DATABASE_HOST");
$dbUser = getenv("DATABASE_USER");
$dbPass = getenv("DATABASE_PASS");
$dbName = getenv("DATABASE_NAME");
$projectUrl = getenv("PROJECT_URL");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Minical Installation</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
</head>
<body>
<div class="container">
<div class="container-fluid text-center"> 
  <div class="row content">
    <div class="col-sm-1">
    </div>
    <div class="col-sm-10 main-data text-left">
        <img src="https://user-images.githubusercontent.com/604232/125141099-e5e4f300-e0c8-11eb-9477-3e8601382ec9.png" 
        style="width: 70px;">   
        <div class="main-background-color">
            <h1>Pre-Installation</h1>
            <br>
                <h4><b>1.</b>&nbsp;Please configure your settings to match the requirements listed below.</h4>
                <br>
                <table class="table">
                    <thead>
                        <tr style="font-size: initial;">
                            <th>Setting</th>
                            <th>Current Setting</th>
                            <th>Required Setting</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>PHP version</td>
                            <td><?php echo phpversion();?></td>
                            <td>7.2.0 or higher</td>
                            <td>
                            <?php
                            if (phpversion() == null){
                            echo '<p class="error">Require PHP version in not installed</p>'; 
                            }elseif(phpversion() < '7.2' || phpversion() > '8.0'){
                                echo '<p class="error">Not compatible with this installed PHP version</p>'; 
                            }
                            else{
                                echo '<i class="fas fa-check-circle" style="font-size:24px;color:green"></i>';
                            }
                            ?> 
                            </td>
                        </tr>
                        <tr>
                            <td>MySQL</td>
                            <td><?php echo mysqli_get_client_info(); ?></td>
                            <td>5.0.4</td>
                            <td>
                            <?php
                            if (mysqli_get_client_info() == null){
                            echo '<p class="error">Require MySql version in not installed</p>'; 
                            }elseif(mysqli_get_client_info() < '5.4'){
                                echo '<p class="error">Not compatible with this installed MySql version</p>'; 
                            }
                            else{
                                echo '<i class="fas fa-check-circle" style="font-size:24px;color:green"></i>';
                            }
                            ?>    
                        </td>
                        </tr>
                        <!-- <tr>
                            <td>Server</td>
                            <td><?php print_r($_SERVER['SERVER_NAME']) ;?></td>
                            <td>-</td>
                            <td><i class="fas fa-check-circle" style="font-size:24px;color:green"></i></td>
                        </tr> -->
                        <tr>
                            <td>Composer</td>
                            <td>-</td>
                            <td>-</td>
                            <td>
                            <?php
                            if (!file_exists($file)) {
                                echo '<p class="error">composer is not installed</p>';
                            }else{
                                echo '<i class="fas fa-check-circle" style="font-size:24px;color:green"></i>';
                            }
                            ?>    
                            </td>
                        </tr>
                        <tr>
                            <td>ENV File</td>
                            <td>-</td>
                            <td>-</td>
                            <td><?php 
                            if (!file_exists($envfile)) {
                                echo '<p class="error">.env file does not exist</p>';
                            }else{
                                echo '<i class="fas fa-check-circle" style="font-size:24px;color:green"></i>';
                            }
                            ?></td>
                        </tr>
                        <?php
                        if(file_exists($envfile)){?>
                        <tr>
                            <td><b>Require ENV Variables</b>
                                <tr>
                                    <td>DATABASE_HOST</td>
                                    <td><?php 
                                    if (isset($dbHost) && $dbHost != null) {
                                        echo $dbHost;   
                                    }else{
                                        echo "";
                                    }
                                    ?></td>
                                    <td>Database Host Name</td>
                                    <td><?php 
                                    if (isset($dbHost) && $dbHost != null) {
                                        echo '<i class="fas fa-check-circle" style="font-size:24px;color:green"></i>';
                                    }else{
                                        echo '<p class="error">Database host name is not configure</p>';
                                    }
                                    ?></td>
                                </tr>
                                <tr>
                                    <td>DATABASE_USER</td>
                                    <td><?php 
                                    if (isset($dbUser) && $dbUser != null) {
                                        echo $dbUser;   
                                    }else{
                                        echo "";
                                    }
                                    ?></td>
                                    <td>Database User Name</td>
                                    <td><?php 
                                    if (isset($dbUser) && $dbUser != null) {
                                        echo '<i class="fas fa-check-circle" style="font-size:24px;color:green"></i>';
                                    }else{
                                        echo '<p class="error">Database user name is not configure</p>';
                                    }
                                    ?></td>
                                </tr>
                                <tr>
                                    <td>DATABASE_PASS</td>
                                    <td><?php
                                    if (isset($dbPass) && $dbPass != null) {
                                        echo $dbPass;   
                                    }else{
                                        echo "";
                                    }
                                    ?></td>
                                    <td>Database Password</td>
                                    <td><?php 
                                    // if (isset($dbPass) && $dbPass != null) {
                                        echo '<i class="fas fa-check-circle" style="font-size:24px;color:green"></i>';
                                    // }else{
                                    //     echo "Database password name is not configure";
                                    // }
                                    ?></td>
                                </tr>
                                <tr>
                                    <td>DATABASE_NAME</td>
                                    <td><?php 
                                    if (isset($dbName) && $dbName != null) {
                                        echo $dbName;   
                                    }else{
                                        echo "";
                                    }
                                    ?></td>
                                    <td>Database Name</td>
                                    <td><?php 
                                    if (isset($dbName) && $dbName != null) {
                                        echo '<i class="fas fa-check-circle" style="font-size:24px;color:green"></i>';
                                    }else{
                                        echo '<p class="error">Databse name for miniCal is not configure</p>';
                                    }
                                    ?></td>
                                </tr>
                                <tr>
                                    <td>PROJECT_URL</td>
                                    <td><?php      
                                    if (isset($projectUrl) && $projectUrl != null) {
                                        echo $projectUrl;   
                                    }else{
                                        echo "";
                                    }
                                    ?></td>
                                    <td>Config the URL till /public directory<br>(E.g. http://localhost/minical/public)</td>
                                    <td><?php 
                                    if (isset($projectUrl) && $projectUrl !== '') {
                                        echo '<i class="fas fa-check-circle" style="font-size:24px;color:green"></i>';
                                    }else{
                                        echo '<p class="error">Project URL is not configure</p>';
                                    }
                                    ?></td>
                                </tr>
                            </td>
                        </tr>
                        <?php }?>

                    </tbody>
                </table>
                <div>
                    <a class="btn btn-primary pull-right" href="#" role="button">Continue</a>
                </div>
        </div>
    </div>
    <div class="col-sm-1">
    </div>
  </div>
</div>

<footer class="container-fluid text-center">
 
</footer>
</div>
</body>
</html>

<style>
.main-background-color{
    padding: 35px 35px 70px 35px;
    background: #eae6f973;
    
}
.main-data{
    margin-top: 20px;
    margin-bottom : 20px;
}
.error{
    color: red;
}
</style>