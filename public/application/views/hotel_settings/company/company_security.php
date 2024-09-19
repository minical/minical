<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading d-flex align-items-center">
            <div class="page-title-icon">
                <i class="pe-7s-key text-success"></i>
            </div>
            <div><?php echo l('Company Security Feature Setting', true); ?></div>
        </div>
    </div>
</div>

<div class="card main-card">
    <div class="card-body">
        <?php if ($this->session->flashdata('code_changed')) : ?>
            <div class="alert alert-success alert-dismissible" role="alert" >
                <?php echo $this->session->flashdata('code_changed'); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="company_security_settings" action="<?php echo base_url(); ?>settings/company_security/add_update_company_security">
            <div class="mb-4">
                
                <!-- <div class="form-group form-check checkbox checbox-switch switch-primary">
                    <label>
                            <input type="checkbox" name="security_status"
                                   <?php echo isset($company_security['security_status']) && $company_security['security_status'] == 1 ? 'checked=checked' : ''; ?>/>
                            <span></span>
                        </label>
                    <label class="form-check-label" for="security_status"><b><?= l("Require users to setup 2-factor authentication", true); ?></b></label>
                </div> -->

                <div class="form-group features-div-padding ">
                    <div class="checkbox checbox-switch switch-primary">
                        <label>
                            <input type="checkbox" name="security_status"
                                   <?php echo isset($company_security['security_status']) && $company_security['security_status'] == 1 ? 'checked=checked' : ''; ?>/>
                            <span></span>
                        </label>
                        <label for="security_status"><b><?= l("Require users to setup 2-factor authentication", true); ?></b></label>
                    </div>
                </div>

                <div class="form-group form-inline lock_timer_div" >
                    <label for="lock_timer" class="mr-2"><?= l("Lock user after", true); ?></label>
                    <input style="width: 65px;" type="number" class="form-control mr-2" name="lock_timer" id="lock_timer" min="1" max="60" value="<?php echo isset($company_security['lock_timer']) ? $company_security['lock_timer'] : 10; ?>" placeholder="10" />
                    <label for="lock_timer"><b>
                        <?php if(isset($company_security['lock_timer']) && $company_security['lock_timer'] == 1) { ?>
                            <?= l("minute of inactivity", true); ?>
                        <?php } else { ?>
                            <?= l("minutes of inactivity", true); ?>
                        <?php } ?>
                    </b></label>
                </div>
            </div>
            <div class="d-flex justify-content-center">
                <input type="submit" class="btn btn-primary" value="<?php echo l('Update', true); ?>" />
            </div>
        </form>
    </div>
</div>
