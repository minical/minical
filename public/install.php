<!DOCTYPE html>
<html lang="en">
<head>
    <title>Minical Installation</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
    <!-- Modal -->
    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content panel panel-success">
                <div class="modal-header">
                    <h4 class="modal-title" style="text-align:center;">
                        Minical Installation
                    </h4>
                    <p style="text-align:center;">Step 1 of 3</p>
                </div>
                <div class="modal-body">
                    <p id="install_process" style="margin-left: 10px;">Database installation in progress</p>

                    <div class="progress" style=" margin: 10px;width: 550px;">
                        <div id="dynamic" class="progress-bar progress-bar-success progress-bar-striped active"
                             role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                             style="width: 0%">
                            <span id="current-progress"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<script type="text/javascript">

    $('#myModal').modal('show');

    var filePosition = "";
    var fileSize = "";
    var processing = 0;

    setTimeout(function () {
        var progress = 10;
        $("#dynamic")
            .css("width", progress + "%")
            .attr("aria-valuenow", progress)
            .text(progress + "% Complete");
    }, 1000);
    setTimeout(function () {
        var progress = 25;
        $("#dynamic")
            .css("width", progress + "%")
            .attr("aria-valuenow", progress)
            .text(progress + "% Complete");
    }, 2000);
    setTimeout(function () {
        var progress = 50;
        $("#dynamic")
            .css("width", progress + "%")
            .attr("aria-valuenow", progress)
            .text(progress + "% Complete");
    }, 3000);


    var ajax_interval = setInterval(function () {
        $.ajax({
                url: "db_install.php",
                type: "POST",
                data: {},
                dataType: "JSON",
                success: function(resp){
                    if(resp.success){
                        var host_name = "<?php echo $_SERVER['HTTP_HOST']; ?>";
                        if(host_name == 'localhost'){

                            window.location.href = resp.project_url + "/auth/register ";
                        } else {
                            window.location.href = "<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/auth/register' ?>";
                        }
                    }

                    filePosition = resp.file_position;
                    fileSize = resp.file_size;

                    processing = Math.round((filePosition / fileSize) * 100)
                    console.log('processing',processing);
                    if(!Number.isNaN(processing)){
                        if (processing >= 50) {
                            $("#dynamic")
                                .css("width", processing + "%")
                                .attr("aria-valuenow", processing)
                                .text(processing + "% Complete");
                        }
                        if (processing >= 100)
                            $('#install_process').html('Please do not interrupt the process or do not reload the page. Wait for automatic browser refresh!');
                    }
                }
        });
    }, 3000);

</script>