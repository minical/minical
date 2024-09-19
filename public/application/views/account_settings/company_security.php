<div class="app-page-title">

    <div class="topnav mb-3">
        <ul>
            <li><a href="<?php echo base_url().'account_settings'?>" ><?php echo l('Password'); ?></a></li>
            <li><a href="<?php echo base_url().'account_settings/company_security'?>" class="active"><?php echo l('Security'); ?></a></li>
        </ul>
    </div>


    <div class="page-title-wrapper">
        <div class="page-title-heading d-flex align-items-center">
            <div class="page-title-icon">
                <i class="pe-7s-key text-success"></i>
            </div>
            <div><?php echo l('Company Security Management', true); ?></div>
        </div>
    </div>
</div>

<div class="card main-card">
    <div class="card-body">
        <?php if ($this->session->flashdata('code_changed')) : ?>
            <div class="alert alert-success alert-dismissible" role="alert">
                <?php echo $this->session->flashdata('code_changed'); ?>
            </div>
        <?php endif; ?>

        <?php if ($security_data) : ?>
        <form method="post" class="company_security_settings" action="<?php echo base_url(); ?>settings/company_security/add_update_company_security">
            <div class="mb-4">
                <div class="form-group form-inline lock_timer_div" style="font-size: 14px;">
                    <label for="lock_timer" ><?= l("Lock the user after inactivity of", true); ?></label>
                    <u><b><?php echo isset($company_security['lock_timer']) ? $company_security['lock_timer'] : 10; ?></b></u>


                    <?php if(isset($this->company_lock_time)){
                        if($this->company_lock_time == 1){
                            $lock_timer = " minute";
                        } else {
                            $lock_timer = " minutes";
                        }
                    }  else {
                            $lock_timer = " minutes";
                        }?>


                    <label for="lock_timer"><b><?= l($lock_timer, true); ?></b></label>
                </div>
                <?php //if (!$security_data) : ?>
                    <!-- <div class="form-group form-check checkbox checbox-switch switch-primary">
                        <label>
                                <input type="checkbox" name="security_status"
                                       <?php echo isset($company_security['security_status']) && $company_security['security_status'] == 1 ? 'checked=checked' : ''; ?>/>
                                <span></span>
                            </label>
                        <label class="form-check-label" for="security_status"><b><?= l("Enable 2-Factor Authentication", true); ?></b></label>
                    </div> -->
                <?php //else: ?>
                <?php if ($security_data) : ?>
                    <div class="mt-4" >
                        <div class="form-group" >
                            <!-- <label for="change_qr_code"><b><?= l("2-Factor Authentication", true); ?></b></label> -->
                            <div >
                                <a href="javascript:" class="btn btn-warning" id="change_qr_code">Reset QR Code</a>
                                <!-- <a href="javascript:" class="btn btn-danger" id="disable_security">Disable</a> -->
                            </div>
                        </div>
                    </div>
                <?php endif; ?>


                <!-- <div class="form-group form-check checkbox checbox-switch switch-primary">
                    <label>
                            <input type="checkbox" name="lock_timer_setting" id="lock_timer_setting" />
                            <span></span>
                        </label>
                    <label class="form-check-label" for="lock_timer"><b><?= l("Lock Timer Setting", true); ?></b></label>
                </div> -->

                
            </div>
            <!-- <div class="d-flex justify-content-center lock_timer_div" <?php if($security_data) { ?> style="display: none;" <?php } ?>>
                <input type="submit" class="btn btn-primary" value="<?php echo l('Update', true); ?>" />
            </div> -->
        </form>

        <?php endif; if ($security_data) : ?>
            
            <div class="show_qr_code_form"></div>
        <?php elseif(
            SUPER_ADMIN != $this->user_email && 
            !$security_data && 
            isset($company_security['security_status']) &&
            $company_security['security_status'] == 1
        ):?>
            <div style="text-align: center;margin: 15px 0px;font-size: 15px;">
                <p>
                    <b>2-Factor Authentication setup is required. Please scan the QR code for security.</b>
                </p>



                <style type="text/css">

                    .container {
                        background-color: #ffffff;
                        padding: 20px;
                        border-radius: 8px;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                        text-align: center;
                        max-width: 400px;
                        width: 100%;
                    }

                    h1 {
                        font-size: 24px;
                        margin-bottom: 20px;
                    }

                    .qr-code img {
                        width: 200px;
                        height: 200px;
                        margin-bottom: 20px;
                    }

                    form {
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                    }

                    label {
                        font-size: 14px;
                        margin-bottom: 10px;
                    }

                    input[type="text"] {
                        padding: 10px;
                        font-size: 16px;
                        border: 1px solid #ccc;
                        border-radius: 4px;
                        margin-bottom: 20px;
                        width: calc(100% - 24px); /* Full width minus padding */
                        box-sizing: border-box;
                    }

                    button {
                        padding: 10px 20px;
                        font-size: 16px;
                        background-color: #007bff;
                        color: #ffffff;
                        border: none;
                        border-radius: 4px;
                        cursor: pointer;
                    }

                    button:hover {
                        background-color: #0056b3;
                    }

                    #secret {
                        font-weight: bold;
                        color: #333;
                    }

                    /*#new_otp {
                      padding-left: 15px;
                      letter-spacing: 42px;
                      border: 0;
                      background-image: linear-gradient(to left, black 70%, rgba(255, 255, 255, 0) 0%);
                      background-position: bottom;
                      background-size: 50px 1px;
                      background-repeat: repeat-x;
                      background-position-x: 35px;
                      width: 300px;
                      outline : none;
                    }*/

                    .otp-input {
                        display: flex;
                        justify-content: space-between;
                    }
                    .otp-input input {
                        width: 50px;
                        height: 50px;
                        text-align: center;
                        font-size: 24px;
                        margin: 0 5px;
                        border: 1px solid #ccc;
                        border-radius: 5px;
                    }

                </style>
                <div class="container">
                    
                    <h1>Two-Factor Authentication (2FA)</h1>
                    <p>Scan the QR code below with your Google Authenticator app:</p>
                    <div class="qr-code">
                        <img src="<?php echo $secure_data['qr_code_url']; ?>" alt="QR Code">
                    </div>

                    <p>If you can't scan the QR code, use this code: <span id="secret"><?php echo $secure_data['secret_code']; ?></span></p>

                    <form action="" method="post">
                        <label for="otp">Enter the code from your Google Authenticator app:</label>
                        <!-- <input id="new_otp" name="new_otp" type="text" maxlength="6" /> -->

                        <div class="otp-input">
                            <input type="text" maxlength="1" required>
                            <input type="text" maxlength="1" required>
                            <input type="text" maxlength="1" required>
                            <input type="text" maxlength="1" required>
                            <input type="text" maxlength="1" required>
                            <input type="text" maxlength="1" required>
                        </div>


                        <input id="secret_code" name="secret_code" type="hidden" value="<?php echo $secure_data['secret_code']; ?>"/>
                        <input id="qr_code_url" name="qr_code_url" type="hidden" value="<?php echo $secure_data['qr_code_url']; ?>"/>
                        <input id="security_name" name="security_name" type="hidden" value="<?php echo $security_name; ?>"/>
                        <a style="margin-top: 15px;" href="javascript:" class="verify_new_otp btn btn-primary">Verify</a>
                    </form>

                </div>

            </div>
       
        
        <?php endif; ?>
    </div>
</div>
