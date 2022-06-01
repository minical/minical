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
    <title>Minical Installation Wizard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
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
                            <td><?php echo phpversion(); ?></td>
                            <td>7.2.0 or higher</td>
                            <td>
                                <?php
                                if (phpversion() == null) {
                                    $check = false;
                                    echo '<p class="error">Required PHP version in not installed</p>';
                                } elseif (phpversion() < '7.2') {
                                    $check = false;
                                    echo '<p class="error">Not compatible with this installed PHP version</p>';
                                } else {
                                    echo '<i class="fa fa-check-circle" style="font-size:24px;color:green"></i>';
                                }
                                ?>
                            </td>
                        </tr>
                        <!--tr>
                            <td>MySQL</td>
                            <td><?php
                                $mysql_version = null;
//                                if($mysqli_connection && $result = mysqli_query($mysqli_connection, "select @@version")) {
//                                    $row = mysqli_fetch_row($result);
//                                    if ($row) {
//                                        $mysql_version = $row[0];
//                                    }
//                                }
                                echo $mysql_version;
                                ?></td>
                            <td>MySql 5.6 and above</td>
                            <td>
                                <?php
//                                if ($mysql_version == null) {
//                                    $check = false;
//                                    echo '<p class="error">Required MySql version is not installed</p>';
//                                } elseif ($mysql_version < '5.6') {
//                                    $check = false;
//                                    echo '<p class="error">Not compatible with this version of MySql</p>';
//                                } else {
//                                    echo '<i class="fa fa-check-circle" style="font-size:24px;color:green"></i>';
//                                }
                                ?>
                            </td>
                        </tr-->
                        <!-- <tr>
                            <td>Server</td>
                            <td><?php print_r($_SERVER['SERVER_NAME']); ?></td>
                            <td>-</td>
                            <td><i class="fa fa-check-circle" style="font-size:24px;color:green"></i></td>
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
                                } else {
                                    echo '<i class="fa fa-check-circle" style="font-size:24px;color:green"></i>';
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
                                } else {
                                    echo '<i class="fa fa-check-circle" style="font-size:24px;color:green"></i>';
                                }
                                ?></td>
                        </tr>
                        <?php
                        if (file_exists($envfile)) {
                            ?>
                            <tr>
                                <td colspan="4">
                                    <br/><br/>
                                    <b>Required Environment Variables (Configured in .env file)</b>
                                </td>
                            </tr>
                            <tr>
                                <td>DATABASE_HOST</td>
                                <td><?php
                                    if (isset($dbHost) && $dbHost != null) {
                                        echo $dbHost;
                                    } else {
                                        echo "";
                                    }
                                    ?></td>
                                <td>
                                    Database Host Name.<br/>
                                    Mysql container name if setting inside a Docker container.
                                </td>
                                <td><?php
                                    if (isset($dbHost) && $dbHost != null) {
                                        echo '<i class="fa fa-check-circle" style="font-size:24px;color:green"></i>';
                                    } else {
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
                                    } else {
                                        echo "";
                                    }
                                    ?></td>
                                <td>Database User Name</td>
                                <td><?php
                                    if (isset($dbUser) && $dbUser != null) {
                                        echo '<i class="fa fa-check-circle" style="font-size:24px;color:green"></i>';
                                    } else {
                                        $check = false;
                                        echo '<p class="error">Database user name is not configured</p>';
                                    }
                                    ?></td>
                            </tr>
                            <tr>
                                <td>DATABASE_PASS</td>
                                <td><?php
                                    if (isset($dbPass) && $dbPass != null) {
                                        echo "*******";
                                    } else {
                                        echo "";
                                    }
                                    ?></td>
                                <td>Database User Password</td>
                                <td><?php
                                    // if (isset($dbPass) && $dbPass != null) {
                                    echo '<i class="fa fa-check-circle" style="font-size:24px;color:green"></i>';
                                    // }else{
                                    //     echo "Database password name is not configured";
                                    // }
                                    ?></td>
                            </tr>
                            <tr>
                                <td>DATABASE_NAME</td>
                                <td><?php
                                    if (isset($dbName) && $dbName != null) {
                                        echo $dbName;
                                    } else {
                                        echo "";
                                    }
                                    ?></td>
                                <td>Database Name</td>
                                <td><?php
                                    if (isset($dbName) && $dbName != null) {
                                        echo '<i class="fa fa-check-circle" style="font-size:24px;color:green"></i>';
                                    } else {
                                        $check = false;
                                        echo '<p class="error">Database name is not configured</p>';
                                    }
                                    ?></td>
                            </tr>
                            <tr>
                                <td>PROJECT_URL</td>
                                <td><?php
                                    if (isset($projectUrl) && $projectUrl != null) {
                                        echo $projectUrl;
                                    } else {
                                        echo "";
                                    }
                                    ?></td>
                                <td>Project URL that points to minical/public directory<br>(E.g.
                                    http://localhost/minical/public)
                                </td>
                                <td><?php
                                    if (isset($projectUrl) && $projectUrl !== '') {
                                        echo '<i class="fa fa-check-circle" style="font-size:24px;color:green"></i>';
                                    } else {
                                        $check = false;
                                        echo '<p class="error">Project URL is not configured</p>';
                                    }
                                    ?></td>
                            </tr>
                            <tr>
                                <td>API_URL</td>
                                <td><?php
                                    if (isset($apiUrl) && $apiUrl != null) {
                                        echo $apiUrl;
                                    } else {
                                        echo "";
                                    }
                                    ?></td>
                                <td>Project URL that points to minical/api directory<br>(E.g.
                                    http://localhost/minical/api)
                                </td>
                                <td><?php
                                    if (isset($apiUrl) && $apiUrl !== '') {
                                        echo '<i class="fa fa-check-circle" style="font-size:24px;color:green"></i>';
                                    } else {
                                        $check = false;
                                        echo '<p class="error">API URL is not configured</p>';
                                    }
                                    ?></td>
                            </tr>
                            <tr>
                                <td>ENVIRONMENT</td>
                                <td><?php
                                    if (isset($environment) && $environment != null) {
                                        echo $environment;
                                    } else {
                                        echo "";
                                    }
                                    ?></td>
                                <td>Set it to 'production' or 'development'</td>
                                <td><?php
                                    if (isset($environment) && $environment !== '') {
                                        echo '<i class="fa fa-check-circle" style="font-size:24px;color:green"></i>';
                                    } else {
                                        $check = false;
                                        echo '<p class="error">ENVIRONMENT is not configured</p>';
                                    }
                                    ?></td>
                            </tr>

                        <?php } ?>

                        </tbody>
                    </table>
                    <div class="pull-right btn-element">
                        <a class="btn btn-default refresh" type="button" role="button">Refresh</a>
                        <a class="btn btn-primary <?php if ($check) {
                            echo 'next';
                        } ?>" role="button">Next</a>
                    </div>
                </div>
                <div class="main-background-color database_connection">
                    <h1>Database Installation</h1>
                    <br>
                    <table class="table">
                        <thead>
                        <tr style="font-size: initial;">
                            <th>Setting</th>
                            <th>Message</th>
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
                                } else {
                                    $connectionFlag = true;
                                    echo '<div class="database-connection-status-loader loader"></div><span class="database-connection-status" style="display:none;">Connected successfully</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if (!$mysqli_connection) {
                                    echo '<p class="error database-connection-status" style="display:none;">' . mysqli_connect_error() . '</p>';
                                } else {
                                    echo '<i class="database-connection-status-icon fa fa-check-circle" style="font-size:24px;color:green;display:none;"></i>';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Database Version</td>
                            <td>
                                <span class="database-connection-status" style="display:none;">
                                <?php
                                $mysql_version = null;
                                $mysql_type = 'mysql';
                                if($mysqli_connection && $result = mysqli_query($mysqli_connection, "select @@version")) {
                                    $row = mysqli_fetch_row($result);
                                    if ($row) {
                                        $mysql_type = strpos($row[0], 'MariaDB') ? 'MariaDB' : 'MySql';
                                        $mysql_version = (float) $row[0];
                                    }
                                }
                                echo $mysql_type. ' ' .$mysql_version;
                                ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $mysqli_compatible = true;
                                if ($mysql_version == null) {
                                    $check = false;
                                    echo '<p class="error database-connection-status" style="display:none;">Connection Failed</p>';
                                    $mysqli_compatible = false;
                                    $mysqli_connection = false;
                                } elseif ($mysql_version <= 5.5) {
                                    $check = false;
                                    echo '<p class="error database-connection-status" style="display:none;">Not compatible with this version of '.($mysql_type ? $mysql_type : 'MySql').' <br/>Minical supports Mysql/MariaDB 5.5 and above.</p>';
                                    $mysqli_compatible = false;
                                    $mysqli_connection = false;
                                } else {
                                    echo '<i class="database-connection-status-icon fa fa-check-circle" style="display:none;font-size:24px;color:green"></i>';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Install Database Schema</td>
                            <td class="db_schema">
                                <?php
                                if (!$mysqli_connection && $mysqli_connection == null) {
                                    echo 'Connection Failed';
                                }
                                ?>
                                <div class="loader db_schema_loader"></div>
                            </td>
                            <td>
                                <?php
                                if (!$mysqli_connection && $mysqli_connection == null) {
                                    echo '<p class="error">Database schema installation failed.</p>';
                                }
                                ?>
                                <!-- <p class="error db_schema_error">Database schema not installed due to "Connection Failed".</p> -->
                                <p class="error db_schema_pending"></p>
                                <i class="fa fa-check-circle install_status" style="font-size:24px;color:green"></i>
                            </td>
                        </tr>
                        <tr>
                            <td>Database Seeding</td>
                            <td class="db_seeding">
                                <div class="loader db_seeding_loader"></div>
                                <?php
                                if (!$mysqli_connection && $mysqli_connection == null) {
                                    echo 'Connection Failed';
                                }
                                ?>
                            </td>

                            <td>
                                <?php
                                if (!$mysqli_connection && $mysqli_connection == null) {
                                    echo '<p class="error">Database seeding failed.</p>';
                                }
                                ?>
                                <p class="error db_seeding_error">Database seeding failed.</p>
                                <p class="error db_seeding_pending"></p>
                                <i class="fa fa-check-circle db_seeding_status" style="font-size:24px;color:green"></i>
                            </td>
                        </tr>
                        <tr>
                            <td>Verify Database Installation</td>
                            <td>
                                <?php
                                if ($mysqli_connection == null) {
                                    echo 'Connection Failed.';
                                } else {
                                    echo '<p class="db_verify">Installation Verified.</p>';
                                }
                                ?>
                                <p class="db_verify_status"></p>
                            </td>
                            <td>
                                <?php
                                if ($mysqli_connection == null) {
                                    echo '<p class="error">Database installation failed.</p>';
                                }
                                ?>
                                <i class="fa fa-check-circle db_verify" style="font-size:24px;color:green"></i>
                                <p class="db_verify_error error"></p>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <div class="row pull-right btn-element register-btn">
                        <b class="size">
                            <span style="color: #259326;">Minical installation has been completed.</span>
                            <br/><br/>
                            Proceed with account setup:&nbsp;</b>
                        <a type="button" class="btn btn-primary" href="<?php echo $projectUrl; ?>/auth/register">Create
                            Admin Account</a>
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
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    .size {
        font-size: 16px;
    }

    .acc-btn {
        display: inline-flex;
        align-items: center;
    }

    .main-background-color {
        padding: 35px 35px 120px 35px;
        background: #eae6f973;

    }

    .main-data {
        margin-top: 20px;
        margin-bottom: 20px;
    }

    .btn-element {
        margin-top: 35px;
    }

    .error {
        color: red;
    }

</style>

<script type="text/javascript">

    var ajax_interval;
    var proUrl = "<?php echo $projectUrl ?? '';?>";
    var connectionFlag = "<?php echo $connectionFlag ?? false;?>";
    var mysqlCompatible = "<?php echo $mysqli_compatible ?? false;?>";

    $(".register-btn").hide();

    $(".db_verify").hide();
    $(".db_verify_status").hide();

    $(".db_schema_loader").hide();
    $(".db_schema_pending").hide();
    $(".install_status").hide();

    $(".db_seeding_loader").hide();
    $(".db_seeding_error").hide();
    $(".db_seeding_pending").hide();
    $(".db_seeding_status").hide();


    $(".database_connection").hide();
    $(".refresh").click(function () {
        location.reload();
    });

    $(".next").click(function () {
        $(".pre_installation").remove();
        $(".database_connection").show();

        if (connectionFlag != undefined && connectionFlag && mysqlCompatible != undefined && mysqlCompatible) {
            setTimeout(function () {
                initializeDatabaseSetup();
            }, 2000);
        } else {
            $('.database-connection-status-loader').hide();
            $('.database-connection-status').show();
        }
    });

    function initializeDatabaseSetup() {

            $('.database-connection-status-loader').hide();
            $('.database-connection-status-icon').show();
            $('.database-connection-status').show();

            $(".db_schema_loader").show();
            $(".db_schema_pending").show();

            $.ajax({
                type: "POST",
                url: proUrl + "/migrate?MIGRATION_REQUEST=1",
                data: {},
                success: function (data) {
                    setTimeout(function () {
                        $(".db_schema_loader").hide();
                        $(".db_schema").html(data);
                        // $(".db_schema_error").hide();
                        $(".db_schema_pending").hide();
                        $(".install_status").show();
                        seed_database();
                    }, 3000);
                },
                error: function (error) {
                    $(".db_schema").html(error);
                }
            });


            function seed_database() {
                $(".db_seeding_loader").show();
                $(".db_seeding_pending").show();

                var check = false;

                ajax_interval = setInterval(function () {
                    $.ajax({
                        url: "database_seeding.php",
                        type: "POST",
                        data: {},
                        dataType: "JSON",
                        success: function (resp) {
                            console.log(resp);
                            if (resp.success) {
                                stopinterval();
                                $(".db_seeding_loader").hide();
                                $(".db_seeding_pending").hide();
                                $(".db_seeding_status").show();
                                $(".db_seeding").html('Database seeding done successfully');
                                db_validation();
                            }
                        },
                        error: function (error) {
                            stopinterval();
                            $(".db_seeding_error").show();
                            $(".db_seeding").html('Database seeding failed!');
                        }

                    });
                }, 3000);

            }

            function stopinterval() {
                clearInterval(ajax_interval);

                return false;
            }

            function db_validation() {
                $.ajax({
                    url: "db_verification.php",
                    type: "POST",
                    data: {},
                    dataType: "JSON",
                    success: function (resp) {
                        console.log(resp);
                        if (resp.success) {
                            if (resp.message < 112) {
                                $(".db_verify_status").show();
                                $(".db_verify_status").html('Only ' + resp.message + ' tables migrated.');
                                $(".db_verify_error").show();
                                $(".db_verify_error").html('Database verification failed! Please contact support.');
                            } else {
                                $(".register-btn").show();
                                $(".db_verify").show();
                            }
                        }
                    },
                    error: function (error) {
                        $(".db_verify_error").show();
                        $(".db_verify_error").html(error);
                    }

                });
            }
    }


</script>