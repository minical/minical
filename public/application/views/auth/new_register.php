<style>
            
    .main{
        padding-right: 0px !important;
        padding-left: 0px !important;
    }
    .footer{
        height: 40px !important;
        margin-top: 20px !important;
    }
    .wrapper {        
        background-color: #fff !important;
    }
    div#processing-modal {
        z-index: 9999;
    }
</style>

<div id="dialogProcessingRequest">
    <span alt="processing_request_please_wait" title="processing_request_please_wait">Processing request. Please wait...</span>
</div>





<!-- Modal -->
<div class="modal fade" id="processing-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Your request has been submitted!</h4>
            </div>
            <div class="modal-body">
                We are setting up your property. Please wait...
            </div>
        </div>
    </div>
</div>
       
<div class="modal" id="registeration-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="margin-top: -7px;">
    <div class="modal-dialog">
        <div class="modal-content">
            <form 
                action=""
                method="post" 
                accept-charset="utf-8">
                <div class="text-center" style="padding: 15px;">
                    <div class="panel panel-success">
                        <div>
                            <h3 style="padding-top: 7px;">
                                Create an admin account
                            </h3>
                            <h5 style="padding-top: 10px;">Step 2 of 3</h5>
                        </div>
                        <div class="panel-body form-horizontal">
                            <div class="form-group">
                                <label for="email" class="col-sm-3 control-label">Email: </label>
                                <div class="col-sm-9">
                                    <input name="email" class="form-control" type="text" placeholder="Email" autocomplete="off" value="<?php echo set_value('email'); ?>" />
                                </div>
                                
                            </div>
                            <div class="form-group">
                                <label for="email" class="col-sm-3 control-label">Password: </label>
                                <div class="col-sm-9">
                                    <input name="password" class="form-control" type="password" placeholder="Password" autocomplete="off" value="<?php echo set_value('password'); ?>" />
                                </div>  
                            </div>
                            
                          
                            <div class="form-group">
                                <label for="email" class="col-sm-3 control-label"></label>
                                <div class="col-sm-9" style="text-align: left;">
                                    <input name="accept_tnc" type="checkbox" value="1" checked/>
                                    <span style="padding-left: 5px;">I agree to the 

                                        <?php if(isset($whitelabelinfo['terms_of_service']) && $whitelabelinfo['terms_of_service']) { ?>
                                            <a href="<?php echo $whitelabelinfo['terms_of_service']; ?>" target="_blank">Terms Of Service</a>
                                        <?php } else { ?>
                                            <a href="javascript:show_terms_of_service();">terms of service</a>
                                        <?php } ?>
                                        <?php echo " and " ?>

                                        <?php if(isset($whitelabelinfo['privacy_policy']) && $whitelabelinfo['privacy_policy']) { ?>
                                            <a href="<?php echo $whitelabelinfo['privacy_policy']; ?>" target="_blank">Privacy Policy</a>.
                                        <?php } else { ?>
                                            <a href="javascript:show_privacy_policy();">privacy policy</a>.
                                        <?php } ?>
                                        
                                    </span>
                                </div>  
                            </div>
                            <div class="form-group">
                                <label for="email" class="col-sm-3 control-label"></label>
                                <div class="col-sm-9">
                                    <input 
                                        type="submit" 
                                        class="btn btn-lg btn-success btn-block register_submit_form"                       
                                        value="Next Step"
                                        name="register_submit_form" />
                                </div>
                            </div>
                        </div>
                    </div>

<!--                    --><?php //echo "By clicking on “Next Step”, you agree to our "; ?><!-- -->
<!--                    <a href="javascript:" onclick="show_terms_of_service()">Terms</a>-->
<!--                    --><?php //echo " and " ?>
<!--                    <a href="javascript:" onclick="show_privacy_policy()">Privacy</a>-->
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="myModal" data-backdrop="static" 
   data-keyboard="false" style="z-index:99999"
   >
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"></h4>
      </div>
      <div class="modal-body" style="position: relative; padding: 15px; overflow-y: scroll; max-height: 75vh;">
      </div>
      <div class="modal-footer">
        
      </div>
      
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<script src="https://code.jquery.com/jquery-1.10.2.js"></script>
<script>
    
    var isInitialized = false;
    function initialize () {
        
        isInitialized = true;
        
        $('#registeration-modal').modal('show');
    }
    
    function bindVideoEndEvent() {
        if (typeof Wistia !== "undefined") {
            var video = Wistia.api("bkn09yxmrp");
            if (video) {
                video.on("end", function() {
                    console.log("on - video ended.");
                    $('#tutorial-video-modal').modal('hide');
                    $('#registeration-modal').modal('show');
                });
                video.bind("end", function() {
                    console.log("bind - video ended.");
                    $('#tutorial-video-modal').modal('hide');
                    $('#registeration-modal').modal('show');
                });
                return true;
            }
        }
        return false;
    }
    
    $(window).load(function () {
        if (!isInitialized) initialize();
    });
    // fallback
    $(document).ready(function () {
        if (!isInitialized) initialize();
    });
    
    $(document).on('click', '.register_submit_form', function (e) {
        e.preventDefault();
        
        $('.register_submit_form').attr('disabled', true).val('Processing...');
        
        var email = $("input[name=email]").val();
        var password = $("input[name=password]").val();
        var paswordLength = $("input[name=password]").val().length;
        var accept_tnc = $("input[name=accept_tnc]:checked").val();
        var url= '<?php  echo 'https://'. $_SERVER['HTTP_HOST'];?>';

        if(email == ''){
            alert('The Email field is required.');
            $('.register_submit_form').attr('disabled', false).val('Next Step');
            return false;
        }
        if(password == ''){
            alert('The Password field is required.');
            $('.register_submit_form').attr('disabled', false).val('Next Step');
            return false;
        }
        if(paswordLength > 20 || paswordLength < 6){
            alert('The password must contain at least one uppercase letter, one lowercase letter, one number, one special character, and be between 6 and 20 characters long.');
            $('.register_submit_form').attr('disabled', false).val('Next Step');
            return false;
        }

        $.ajax({
            type: "POST",
            url: getBaseURL() + 'auth/new_register_AJAX',
            data: {
                    email: email,
                    password: password,
                    accept_tnc: accept_tnc
                    // 'g-recaptcha-response': $('#g-recaptcha-response').val()
                },
            success: function (data) {
                if (data == 'success')
                {
                    window.location.href = getBaseURL() + 'booking/';
                    
                }
                else if (data == 'loggedin')
                {
                    window.location.href = getBaseURL() + 'booking/';
                }
                else
                {
                    alert(data);
                    $('.register_submit_form').attr('disabled', false).val('Next Step');
                    // grecaptcha.reset()
                }
            },
            error: function (err) {
                $('.register_submit_form').attr('disabled', false).val('Next Step');
                console.log('in error');
            }
        });
    });

function show_terms_of_service()
{
    $(".modal-content").css("overflow", "auto");
    $(".modal-content").css("height", "100%");

    var myModal = $('#myModal');

    myModal.find(".modal-title").html("Terms of Service");
    myModal.find(".modal-body").load(getBaseURL() + "auth/show_terms_of_service");
    myModal.find(".modal-footer").html(
        $('<form/>', {id: ''})
            .append($('<a/>', {
                href: 'javascript:',
                type: 'button',
                class: 'btn btn-default',
                text: 'Close'
            })).on('click',function(){
                myModal.modal('hide');
            })
    );
    myModal.modal('show');
}

function show_privacy_policy()
{
    $(".modal-content").css("overflow", "auto");
    $(".modal-content").css("height", "100%");

    var myModal = $('#myModal');

    myModal.find(".modal-title").html("Privacy Policy");
    myModal.find(".modal-body").load(getBaseURL() + "auth/show_privacy_policy");
    myModal.find(".modal-footer").html(
        $('<form/>', {id: ''})
            .append($('<a/>', {
                href: 'javascript:',
                type: 'button',
                class: 'btn btn-default',
                text: 'Close'
            })).on('click',function(){
                myModal.modal('hide');
            })
    );
    myModal.modal('show');
}
</script>
