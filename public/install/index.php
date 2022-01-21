<?php
$check = true;
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
$apiUrl = getenv("API_URL");
$environment = getenv("ENVIRONMENT");
}

$mysqli_connection = @mysqli_connect("$dbHost", "$dbUser", "$dbPass", "$dbName");

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
        <div class="main-background-color pre_installation">
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
                                $check = false;
                            echo '<p class="error">Require PHP version in not installed</p>'; 
                            }elseif(phpversion() < '7.2' || phpversion() > '7.4.16'){
                                $check = false;
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
                                $check = false;
                                echo '<p class="error">Require MySql version is not installed</p>'; 
                            }elseif(mysqli_get_client_info() < '5.4'){
                                $check = false;
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
                                $check = false;
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
                                $check = false;
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
                                        $check = false;
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
                                        $check = false;
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
                                        $check = false;
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
                                    <td>Config the URL till minical/public directory<br>(E.g. http://localhost/minical/public)</td>
                                    <td><?php 
                                    if (isset($projectUrl) && $projectUrl !== '') {
                                        echo '<i class="fas fa-check-circle" style="font-size:24px;color:green"></i>';
                                    }else{
                                        $check = false;
                                        echo '<p class="error">Project URL is not configure</p>';
                                    }
                                    ?></td>
                                </tr>
                                <tr>
                                    <td>API_URL</td>
                                    <td><?php      
                                    if (isset($apiUrl) && $apiUrl != null) {
                                        echo $apiUrl;   
                                    }else{
                                        echo "";
                                    }
                                    ?></td>
                                    <td>Config the URL till minical/api folder<br>(E.g. http://localhost/minical/api)</td>
                                    <td><?php 
                                    if (isset($apiUrl) && $apiUrl !== '') {
                                        echo '<i class="fas fa-check-circle" style="font-size:24px;color:green"></i>';
                                    }else{
                                        $check = false;
                                        echo '<p class="error">API URL is not configure</p>';
                                    }
                                    ?></td>
                                </tr>
                                <tr>
                                    <td>ENVIRONMENT</td>
                                    <td><?php      
                                    if (isset($environment) && $environment != null) {
                                        echo $environment;   
                                    }else{
                                        echo "";
                                    }
                                    ?></td>
                                    <td>Set it to 'production' or 'development'</td>
                                    <td><?php 
                                    if (isset($environment) && $environment !== '') {
                                        echo '<i class="fas fa-check-circle" style="font-size:24px;color:green"></i>';
                                    }else{
                                        $check = false;
                                        echo '<p class="error">Project URL is not configure</p>';
                                    }
                                    ?></td>
                                </tr>
                            </td>
                        </tr>
                        <?php }?>

                    </tbody>
                </table>
                <div class="pull-right btn-element">
                    <a class="btn btn-primary btncss refresh" type="button" role="button">Refresh</a>         
                    <a class="btn btn-primary btncss <?php if($check){ echo 'next';}?>" role="button">Next</a>
                </div>
        </div>
        <div class="main-background-color database_connection">
        <h1>Database Installation</h1>
            <br>
                <table class="table">
                    <thead>
                        <tr style="font-size: initial;">
                            <th>Setting</th>
                            <th>Current Setting</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Check Database Connection</td>
                            <td>
                                <?php 
                                $connectionFlag = false;
                                if (!$mysqli_connection) {
                                    echo 'Connection Failed.';
                                }else{
                                    $connectionFlag = true;
                                    echo 'Connected successfully';
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                if (!$mysqli_connection) {
                                    echo '<p class="error">'.mysqli_connect_error().'</p>';
                                }else{
                                    echo '<i class="fas fa-check-circle" style="font-size:24px;color:green"></i>';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php ?>
                        <tr>
                            <td>Install Database Schema</td>
                            <td class="db_schema">
                            <?php 
                                if(!$mysqli_connection && $mysqli_connection == null) {
                                    echo 'Connection Failed';
                                }
                                ?>    
                            <div class="loader db_schema_loader"></div>
                            </td>
                            <td>
                            <?php 
                                if(!$mysqli_connection && $mysqli_connection == null) {
                                    echo '<p class="error">Database schema not installed due to "Connection Failed"</p>';
                                }
                                ?>
                                <!-- <p class="error db_schema_error">Database schema not installed due to "Connection Failed".</p> -->
                                <p class="error db_schema_pending">The database schema installation is pending.</p>
                                <i class="fas fa-check-circle install_status" style="font-size:24px;color:green"></i>
                            </td>
                        </tr>
                        <?php  ?>
                        <tr>
                            <td>Database Seeding</td>
                            <td class="db_seeding"><div class="loader db_seeding_loader"></div>
                            <?php 
                                if(!$mysqli_connection && $mysqli_connection == null) {
                                    echo 'Connection Failed';
                                }
                            ?>
                            </td>

                            <td>
                                <?php 
                                if(!$mysqli_connection && $mysqli_connection == null) {
                                    echo '<p class="error">Database not seeded due to "Connection Failed"</p>';
                                }
                                ?>
                                <p class="error db_seeding_error">Database not seeded due to "Connection Failed".</p>
                                <p class="error db_seeding_pending">Database seeding is pending.</p>
                                <i class="fas fa-check-circle db_seeding_status" style="font-size:24px;color:green"></i>
                            </td>
                        </tr>
                        <tr><?php
                            if ($mysqli_connection != null) {
                                    $query = "SELECT count(*) AS TOTALNUMBEROFTABLES  FROM INFORMATION_SCHEMA.TABLES
                                    WHERE TABLE_SCHEMA = '$dbName'";
                                    if($result = mysqli_query($mysqli_connection, $query)){
                                        $row = mysqli_fetch_row($result);
                                    }
                            }?>
                            <td>Verify Database Installation</td>
                            <td>
                            <?php
                                    if($mysqli_connection == null) {
                                        echo 'Connection Failed.';
                                    }elseif(isset($row[0]) && $row[0] = 11002){
                                        echo '<p class="db_verify">Database Installation Done</p>';
                                    }
                                ?>
                            </td>
                            <td>
                            <?php
                                if($mysqli_connection == null) {
                                        echo '<p class="error">Database installation Failed due to "Connection Failed"</p>';
                                }
                                elseif(isset($row) && $row[0] = 1102){
                                        echo '<i class="fas fa-check-circle db_verify" style="font-size:24px;color:green"></i>';
                                }else{
                                        echo '<p class="error db_verify">Database Installation Failed</p>';
                                }
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="row pull-right btn-element register-btn">
                <b class="size">miniCal installation has been completed. Proceed with account setup:&nbsp;</b> 
                <a type="button" class="btn btn-primary btncss" href="<?php echo $projectUrl;?>/auth/register">Create Admin Account</a>
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
.loader {
  border: 4px solid #dedbed;
  border-radius: 50%;
  border-top: 4px solid #3498db;
  width: 20px;
  height: 20px;
  -webkit-animation: spin 2s linear infinite; /* Safari */
  animation: spin 2s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}   
.size{
        font-size: 16px;
}
.acc-btn{
    display: inline-flex;
    align-items: center;
 }   
.main-background-color{
    padding: 35px 35px 120px 35px;
    background: #eae6f973;
    
}
.main-data{
    margin-top: 20px;
    margin-bottom : 20px;
}
.btn-element{
    margin-top: 35px;
}
.error{
    color: red;
}
.btncss{
    padding: 0.25rem 1.5rem;
    font-size: 1.875rem;
    line-height: 1.5;
    border-radius: 4px;
    color: #fff;
    background-color: #007bff !important;
    border-color: #007bff;
}
.btncss a:hover {
    color: #fff;
    background-color: #007bff !important;
    border-color: #007bff !important;
}
</style>

<script type="text/javascript">
 
$(".register-btn").hide(); 

$(".db_verify").hide();
$(".db_schema_loader").hide();
$(".db_schema_pending").hide();
$(".install_status").hide();

$(".db_seeding_loader").hide();
$(".db_seeding_error").hide();    
$(".db_seeding_pending").hide(); 
$(".db_seeding_status").hide(); 


$(".database_connection").hide();
$(".refresh").click(function(){
    location.reload();
});

$(".next").click(function(){
    $(".pre_installation").remove();
    $(".database_connection").show();
});

var ajax_interval;
var proUrl = "<?php echo $projectUrl ?? '';?>";
var connectionFlag = "<?php echo $connectionFlag ?? false;?>";

if(connectionFlag != undefined && connectionFlag){
    $(".db_schema_loader").show();
    $(".db_schema_pending").show();
    $.ajax({
			type: "POST",
			url: proUrl +"/migrate?MIGRATION_REQUEST=1",
            data: {},
        	success: function(data) 
			{
				console.log(data);
                $(".db_schema_loader").hide();
                $(".db_schema").html(data);
                // $(".db_schema_error").hide();
                $(".db_schema_pending").hide();
                $(".install_status").show();
                seed_database();
			},
            error: function(error){
                console.log(error);
                $(".db_schema").html(error);
            }
		});

        
    function seed_database()
    {
        $(".db_seeding_loader").show();
        $(".db_seeding_pending").show();

        var check = false;

        ajax_interval = setInterval(function () {   
            $.ajax({
                url: "database_seeding.php",
                type: "POST",
                data: {},
                dataType: "JSON",
                success: function(resp){
                    console.log(resp);
                    if(resp.success){
                        stopinterval();                 
                        $(".db_seeding_loader").hide();
                        $(".db_seeding_pending").hide(); 
                        $(".db_seeding_status").show();
                        $(".db_seeding").html('Database Seeding done successfully'); 
                        $(".db_verify").show();
                    }
                },
                error: function(error){
                    stopinterval();
                    $(".db_seeding_error").show();   
                    $(".db_seeding").html('Database Seeding failed!'); 
                }
                
            });
        }, 3000);    
   
    }

    function stopinterval(){
    clearInterval(ajax_interval); 
    $(".register-btn").show();
    return false;
    }
}


</script>