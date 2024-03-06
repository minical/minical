

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-settings text-success"></i>
            </div>
            <div><?php echo l('turn_features_on_off', true); ?>

        </div>
    </div>
  </div>
</div>


<div class="main-card  card">
    <div class="mb-3">


<form method="post" class="form_feature_settings" autocomplete="off">


    <div style="border:1px solid #DDDDDD;padding:20px;margin:20px">
        <div>
            <h4 style="padding-bottom:20px;
  border-bottom: 2px solid #DDDDDD"><b><?php echo l('EMAIL', true) ?></b></h4>
        </div>

        <div style="padding-top:10px">

            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="automatic_email_confirmation"
                               <?= $company_data['automatic_email_confirmation'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="automatic_email_confirmation"><b><?= l("Send booking confirmation email to guest automatically (When booking is created).", true); ?></b></label>
                </div>
            </div>

            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="automatic_email_cancellation"
                               <?= $company_data['automatic_email_cancellation'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="automatic_email_cancellation"><b><?= l("Send booking cancellation email to guest automatically (When booking is cancelled).", true); ?></b></label>
                </div>
            </div>

            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="send_booking_notes"
                               <?= $company_data['send_booking_notes'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="send_booking_notes"><b><?= l("Send booking notes with confirmation email", true); ?></b></label>
                </div>
            </div>

            <div class="form-group features-div-padding form-inline ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="send_copy_to_additional_emails"
                               <?= $company_data['send_copy_to_additional_emails'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="additional_company_emails"><b><?= l("Send copy of confirmation email and invoice email to this address", true); ?></b></label>
                    <input type="text" class="form-control" name="additional_company_emails"
                           value="<?php echo $company_data['additional_company_emails']; ?>" size=50
                           placeholder="<?php echo l('comma separated email addresses', true) ?>"/>
                </div>
            </div>

            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="email_confirmation_for_ota_reservations"
                               <?= $company_data['email_confirmation_for_ota_reservations'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="email_confirmation_for_ota_reservations"><b><?= l("Do not send email confirmation for OTA reservations", true); ?></b></label>
                </div>
            </div>

            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="email_cancellation_for_ota_reservations"
                               <?= $company_data['email_cancellation_for_ota_reservations'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="email_cancellation_for_ota_reservations"><b><?= l("Do not send email cancellation for OTA reservations", true); ?></b></label>
                </div>
            </div>

            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="send_invoice_email_automatically"
                               <?= $company_data['send_invoice_email_automatically'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="send_invoice_email_automatically"><b><?= l("send email to the guest Automatically on checkout", true); ?></b></label>
                </div>
            </div>

            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="hide_room_name"
                               <?= $company_data['hide_room_name'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="hide_room_name"><b><?= l("Hide room name in email invoice", true); ?></b></label>
                </div>
            </div>

            <div class="form-group features-div-padding ">
                <?php $whitelabelinfo = $this->session->userdata('white_label_information');
                $partner_name = isset($whitelabelinfo['name']) ? ucfirst($whitelabelinfo['name']) : $this->config->item('branding_name');
                ?>
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="avoid_dmarc_blocking"
                               <?= $company_data['avoid_dmarc_blocking'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="avoid_dmarc_blocking"><b><?= l("Use " . $partner_name . "’s email as sender to avoid blocking of your email because of domain’s DMARC policy", true); ?></b></label>
                </div>
            </div>
        </div>
    </div>


    <div style="border:1px solid #DDDDDD;padding:20px;margin:20px;">
        <div>
            <h4 style="padding-bottom:20px;border-bottom: 2px solid #DDDDDD">
                <b><?php echo l('RESERVATIONS', true); ?></b></h4>
        </div>
        <div style="padding-top:10px">

            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="is_display_tooltip"
                               <?= $company_data['is_display_tooltip'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="is_display_tooltip"><b><?= l("When mouse hovers over a booking, show booking information", true); ?></b></label>
                </div>
            </div>

            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="make_guest_field_mandatory"
                               <?= $company_data['make_guest_field_mandatory'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="make_guest_field_mandatory"><b><?= l("Make guest field required when creating bookings", true); ?></b></label>
                </div>
            </div>


            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                 <label>
                        <input type="checkbox" name="book_over_unconfirmed_reservations"
                               <?= $company_data['book_over_unconfirmed_reservations'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="book_over_unconfirmed_reservations"><b><?= l("Allow an online reservation in a room with an unconfirmed reservation", true); ?></b></label>
                </div>
            </div>


             <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="allow_non_continuous_bookings"
                               <?= $company_data['allow_non_continuous_bookings'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="allow_non_continuous_bookings"><b><?= l("Allow OTA bookings with non-continuous rooms/blocks", true); ?></b></label>
                    <br/>
                    <div class="form-group features-div-padding  form-inline max-number-blocks">
                        <?= l("Maximum number of blocks:", true); ?> <input type="text" class="form-control" name="maximum_no_of_blocks" value="<?php echo $company_data['maximum_no_of_blocks']; ?>" size=10/>
                    </div>
                </div>
            </div>

            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="include_cancelled_noshow_bookings"
                               <?= $company_data['include_cancelled_noshow_bookings'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="include_cancelled_noshow_bookings"><b><?= l("Show 'Total' with Cancelled/No show booking's charges/payments in Ledger summary report", true); ?></b></label>
                </div>
            </div>

            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="enable_hourly_booking"
                               <?= $company_data['enable_hourly_booking'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="enable_hourly_booking"><b><?= l("Enable hourly bookings", true); ?></b></label>
                </div>

                <div class="form-group features-div-padding  form-inline default-check-in-out-time">
                    <label for="default_checkin_time">
                        <?= l("Default checkin time", true); ?>
                    </label>
                    <select name="default_checkin_time" class="form-control" id="default_checkin_time">
                        <option value="12:00 AM" <?php if ($company_data['default_checkin_time'] == '12:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >12:00 AM
                        </option>
                        <option value="12:30 AM" <?php if ($company_data['default_checkin_time'] == '12:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >12:30 AM
                        </option>
                        <option value="01:00 AM" <?php if ($company_data['default_checkin_time'] == '01:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >01:00 AM
                        </option>
                        <option value="01:30 AM" <?php if ($company_data['default_checkin_time'] == '01:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >01:30 AM
                        </option>
                        <option value="02:00 AM" <?php if ($company_data['default_checkin_time'] == '02:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >02:00 AM
                        </option>
                        <option value="02:30 AM" <?php if ($company_data['default_checkin_time'] == '02:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >02:30 AM
                        </option>
                        <option value="03:00 AM" <?php if ($company_data['default_checkin_time'] == '03:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >03:00 AM
                        </option>
                        <option value="03:30 AM" <?php if ($company_data['default_checkin_time'] == '03:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >03:30 AM
                        </option>
                        <option value="04:00 AM" <?php if ($company_data['default_checkin_time'] == '04:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >04:00 AM
                        </option>
                        <option value="04:30 AM" <?php if ($company_data['default_checkin_time'] == '04:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >04:30 AM
                        </option>
                        <option value="05:00 AM" <?php if ($company_data['default_checkin_time'] == '05:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >05:00 AM
                        </option>
                        <option value="05:30 AM" <?php if ($company_data['default_checkin_time'] == '05:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >05:30 AM
                        </option>
                        <option value="06:00 AM" <?php if ($company_data['default_checkin_time'] == '06:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >06:00 AM
                        </option>
                        <option value="06:30 AM" <?php if ($company_data['default_checkin_time'] == '06:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >06:30 AM
                        </option>
                        <option value="07:00 AM" <?php if ($company_data['default_checkin_time'] == '07:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >07:00 AM
                        </option>
                        <option value="07:30 AM" <?php if ($company_data['default_checkin_time'] == '07:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >07:30 AM
                        </option>
                        <option value="08:00 AM" <?php if ($company_data['default_checkin_time'] == '08:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >08:00 AM
                        </option>
                        <option value="08:30 AM" <?php if ($company_data['default_checkin_time'] == '08:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >08:30 AM
                        </option>
                        <option value="09:00 AM" <?php if ($company_data['default_checkin_time'] == '09:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >09:00 AM
                        </option>
                        <option value="09:30 AM" <?php if ($company_data['default_checkin_time'] == '09:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >09:30 AM
                        </option>
                        <option value="10:00 AM" <?php if ($company_data['default_checkin_time'] == '10:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >10:00 AM
                        </option>
                        <option value="10:30 AM" <?php if ($company_data['default_checkin_time'] == '10:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >10:30 AM
                        </option>
                        <option value="11:00 AM" <?php if ($company_data['default_checkin_time'] == '11:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >11:00 AM
                        </option>
                        <option value="11:30 AM" <?php if ($company_data['default_checkin_time'] == '11:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >11:30 AM
                        </option>
                        <option value="12:00 PM" <?php if ($company_data['default_checkin_time'] == '12:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >12:00 PM
                        </option>
                        <option value="12:30 PM" <?php if ($company_data['default_checkin_time'] == '12:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >12:30 PM
                        </option>
                        <option value="01:00 PM" <?php if ($company_data['default_checkin_time'] == '01:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >01:00 PM
                        </option>
                        <option value="01:30 PM" <?php if ($company_data['default_checkin_time'] == '01:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >01:30 PM
                        </option>
                        <option value="02:00 PM" <?php if ($company_data['default_checkin_time'] == '02:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >02:00 PM
                        </option>
                        <option value="02:30 PM" <?php if ($company_data['default_checkin_time'] == '02:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >02:30 PM
                        </option>
                        <option value="03:00 PM" <?php if ($company_data['default_checkin_time'] == '03:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >03:00 PM
                        </option>
                        <option value="03:30 PM" <?php if ($company_data['default_checkin_time'] == '03:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >03:30 PM
                        </option>
                        <option value="04:00 PM" <?php if ($company_data['default_checkin_time'] == '04:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >04:00 PM
                        </option>
                        <option value="04:30 PM" <?php if ($company_data['default_checkin_time'] == '04:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >04:30 PM
                        </option>
                        <option value="05:00 PM" <?php if ($company_data['default_checkin_time'] == '05:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >05:00 PM
                        </option>
                        <option value="05:30 PM" <?php if ($company_data['default_checkin_time'] == '05:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >05:30 PM
                        </option>
                        <option value="06:00 PM" <?php if ($company_data['default_checkin_time'] == '06:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >06:00 PM
                        </option>
                        <option value="06:30 PM" <?php if ($company_data['default_checkin_time'] == '06:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >06:30 PM
                        </option>
                        <option value="07:00 PM" <?php if ($company_data['default_checkin_time'] == '07:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >07:00 PM
                        </option>
                        <option value="07:30 PM" <?php if ($company_data['default_checkin_time'] == '07:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >07:30 PM
                        </option>
                        <option value="08:00 PM" <?php if ($company_data['default_checkin_time'] == '08:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >08:00 PM
                        </option>
                        <option value="08:30 PM" <?php if ($company_data['default_checkin_time'] == '08:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >08:30 PM
                        </option>
                        <option value="09:00 PM" <?php if ($company_data['default_checkin_time'] == '09:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >09:00 PM
                        </option>
                        <option value="09:30 PM" <?php if ($company_data['default_checkin_time'] == '09:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >09:30 PM
                        </option>
                        <option value="10:00 PM" <?php if ($company_data['default_checkin_time'] == '10:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >10:00 PM
                        </option>
                        <option value="10:30 PM" <?php if ($company_data['default_checkin_time'] == '10:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >10:30 PM
                        </option>
                        <option value="11:00 PM" <?php if ($company_data['default_checkin_time'] == '11:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >11:00 PM
                        </option>
                        <option value="11:30 PM" <?php if ($company_data['default_checkin_time'] == '11:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >11:30 PM
                        </option>
                    </select>
                </div>

                <div class="form-group features-div-padding  form-inline default-check-in-out-time">
                    <label for="default_checkout_time">
                        <?= l("Default checkout time", true); ?>
                    </label>
                    <select name="default_checkout_time" class="form-control" id="default_checkout_time">
                        <option value="12:00 AM" <?php if ($company_data['default_checkout_time'] == '12:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >12:00 AM
                        </option>
                        <option value="12:30 AM" <?php if ($company_data['default_checkout_time'] == '12:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >12:30 AM
                        </option>
                        <option value="01:00 AM" <?php if ($company_data['default_checkout_time'] == '01:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >01:00 AM
                        </option>
                        <option value="01:30 AM" <?php if ($company_data['default_checkout_time'] == '01:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >01:30 AM
                        </option>
                        <option value="02:00 AM" <?php if ($company_data['default_checkout_time'] == '02:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >02:00 AM
                        </option>
                        <option value="02:30 AM" <?php if ($company_data['default_checkout_time'] == '02:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >02:30 AM
                        </option>
                        <option value="03:00 AM" <?php if ($company_data['default_checkout_time'] == '03:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >03:00 AM
                        </option>
                        <option value="03:30 AM" <?php if ($company_data['default_checkout_time'] == '03:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >03:30 AM
                        </option>
                        <option value="04:00 AM" <?php if ($company_data['default_checkout_time'] == '04:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >04:00 AM
                        </option>
                        <option value="04:30 AM" <?php if ($company_data['default_checkout_time'] == '04:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >04:30 AM
                        </option>
                        <option value="05:00 AM" <?php if ($company_data['default_checkout_time'] == '05:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >05:00 AM
                        </option>
                        <option value="05:30 AM" <?php if ($company_data['default_checkout_time'] == '05:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >05:30 AM
                        </option>
                        <option value="06:00 AM" <?php if ($company_data['default_checkout_time'] == '06:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >06:00 AM
                        </option>
                        <option value="06:30 AM" <?php if ($company_data['default_checkout_time'] == '06:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >06:30 AM
                        </option>
                        <option value="07:00 AM" <?php if ($company_data['default_checkout_time'] == '07:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >07:00 AM
                        </option>
                        <option value="07:30 AM" <?php if ($company_data['default_checkout_time'] == '07:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >07:30 AM
                        </option>
                        <option value="08:00 AM" <?php if ($company_data['default_checkout_time'] == '08:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >08:00 AM
                        </option>
                        <option value="08:30 AM" <?php if ($company_data['default_checkout_time'] == '08:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >08:30 AM
                        </option>
                        <option value="09:00 AM" <?php if ($company_data['default_checkout_time'] == '09:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >09:00 AM
                        </option>
                        <option value="09:30 AM" <?php if ($company_data['default_checkout_time'] == '09:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >09:30 AM
                        </option>
                        <option value="10:00 AM" <?php if ($company_data['default_checkout_time'] == '10:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >10:00 AM
                        </option>
                        <option value="10:30 AM" <?php if ($company_data['default_checkout_time'] == '10:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >10:30 AM
                        </option>
                        <option value="11:00 AM" <?php if ($company_data['default_checkout_time'] == '11:00 AM') {
                            echo 'selected = "selected"';
                        } ?> >11:00 AM
                        </option>
                        <option value="11:30 AM" <?php if ($company_data['default_checkout_time'] == '11:30 AM') {
                            echo 'selected = "selected"';
                        } ?> >11:30 AM
                        </option>
                        <option value="12:00 PM" <?php if ($company_data['default_checkout_time'] == '12:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >12:00 PM
                        </option>
                        <option value="12:30 PM" <?php if ($company_data['default_checkout_time'] == '12:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >12:30 PM
                        </option>
                        <option value="01:00 PM" <?php if ($company_data['default_checkout_time'] == '01:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >01:00 PM
                        </option>
                        <option value="01:30 PM" <?php if ($company_data['default_checkout_time'] == '01:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >01:30 PM
                        </option>
                        <option value="02:00 PM" <?php if ($company_data['default_checkout_time'] == '02:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >02:00 PM
                        </option>
                        <option value="02:30 PM" <?php if ($company_data['default_checkout_time'] == '02:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >02:30 PM
                        </option>
                        <option value="03:00 PM" <?php if ($company_data['default_checkout_time'] == '03:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >03:00 PM
                        </option>
                        <option value="03:30 PM" <?php if ($company_data['default_checkout_time'] == '03:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >03:30 PM
                        </option>
                        <option value="04:00 PM" <?php if ($company_data['default_checkout_time'] == '04:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >04:00 PM
                        </option>
                        <option value="04:30 PM" <?php if ($company_data['default_checkout_time'] == '04:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >04:30 PM
                        </option>
                        <option value="05:00 PM" <?php if ($company_data['default_checkout_time'] == '05:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >05:00 PM
                        </option>
                        <option value="05:30 PM" <?php if ($company_data['default_checkout_time'] == '05:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >05:30 PM
                        </option>
                        <option value="06:00 PM" <?php if ($company_data['default_checkout_time'] == '06:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >06:00 PM
                        </option>
                        <option value="06:30 PM" <?php if ($company_data['default_checkout_time'] == '06:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >06:30 PM
                        </option>
                        <option value="07:00 PM" <?php if ($company_data['default_checkout_time'] == '07:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >07:00 PM
                        </option>
                        <option value="07:30 PM" <?php if ($company_data['default_checkout_time'] == '07:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >07:30 PM
                        </option>
                        <option value="08:00 PM" <?php if ($company_data['default_checkout_time'] == '08:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >08:00 PM
                        </option>
                        <option value="08:30 PM" <?php if ($company_data['default_checkout_time'] == '08:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >08:30 PM
                        </option>
                        <option value="09:00 PM" <?php if ($company_data['default_checkout_time'] == '09:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >09:00 PM
                        </option>
                        <option value="09:30 PM" <?php if ($company_data['default_checkout_time'] == '09:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >09:30 PM
                        </option>
                        <option value="10:00 PM" <?php if ($company_data['default_checkout_time'] == '10:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >10:00 PM
                        </option>
                        <option value="10:30 PM" <?php if ($company_data['default_checkout_time'] == '10:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >10:30 PM
                        </option>
                        <option value="11:00 PM" <?php if ($company_data['default_checkout_time'] == '11:00 PM') {
                            echo 'selected = "selected"';
                        } ?> >11:00 PM
                        </option>
                        <option value="11:30 PM" <?php if ($company_data['default_checkout_time'] == '11:30 PM') {
                            echo 'selected = "selected"';
                        } ?> >11:30 PM
                        </option>
                    </select>
                </div>
            </div>


            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="allow_free_bookings"
                               <?= $company_data['allow_free_bookings'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="allow_free_bookings"><b><?= l("Allow rooms to be booked for FREE (If charge type is not set)", true); ?></b></label>
                </div>
            </div>



            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                   <label>
                        <input type="checkbox" name="customer_modify_booking"
                               <?= $company_data['customer_modify_booking'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="customer_modify_booking"><b><?= l("Allow customers to modify their booking", true); ?></b></label>
                </div>
            </div>


            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="booking_cancelled_with_balance"
                               <?= $company_data['booking_cancelled_with_balance'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="booking_cancelled_with_balance"><b><?= l("Allow bookings to be cancelled with outstanding balance", true); ?></b></label>
                </div>
            </div>


            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="restrict_booking_dates_modification"
                               id="restrict_booking_dates_modification"
                               <?= $company_data['restrict_booking_dates_modification'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="restrict_booking_dates_modification"><b><?= l("Restrict booking dates modification once customer checkout", true); ?></b></label>
                </div>
            </div>

            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="restrict_checkout_with_balance" id="restrict_checkout_with_balance"
                               <?= $company_data['restrict_checkout_with_balance'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="restrict_checkout_with_balance"><b><?= l("Restrict bookings to checkout with balance", true); ?></b></label>
                </div>
            </div>
            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="restrict_cvc_not_mandatory" id="restrict_cvc_not_mandatory"
                               <?= $company_data['restrict_cvc_not_mandatory'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="restrict_cvc_not_mandatory"><b><?= l("CVC not mandatory with the credit card details", true); ?></b></label>
                </div>
            </div>
            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="restrict_edit_after_checkout" id="restrict_edit_after_checkout"
                               <?= $company_data['restrict_edit_after_checkout'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="restrict_edit_after_checkout"><b><?= l("Restrict edit booking invoice after booking checkout", true); ?></b></label>
                </div>
            </div>
            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="allow_change_previous_booking_status" id="allow_change_previous_booking_status"
                               <?= $company_data['allow_change_previous_booking_status'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="allow_change_previous_booking_status"><b><?= l("Allow Change Previous booking status", true); ?></b></label>
                </div>
            </div>
            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="auto_no_show"
                               <?= $company_data['auto_no_show'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="auto_no_show"><b><?= l("set booking type to 'No show' Automatically", true); ?></b></label>
                </div>
            </div>


            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="force_room_selection"
                               <?= $company_data['force_room_selection'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="force_room_selection"><b><?= l("make room selection mandatory when creating bookings", true); ?></b></label>
                </div>
            </div>

            <div class="form-group features-div-padding  form-inline">
                <label for="date_format">
                    <?= l("Date Format", true); ?>
                </label>
                <select class="form-control" name="date_format" id="date_format">
                    <option value="YY-MM-DD" <?php if ($company_data['date_format'] == 'YY-MM-DD') {
                        echo 'selected = "selected"';
                    } ?>>YYYY-MM-DD
                    </option>
                    <option value="DD-MM-YY" <?php if ($company_data['date_format'] == 'DD-MM-YY') {
                        echo 'selected = "selected"';
                    } ?>>DD-MM-YYYY
                    </option>
                    <option value="MM-DD-YY" <?php if ($company_data['date_format'] == 'MM-DD-YY') {
                        echo 'selected = "selected"';
                    } ?>>MM-DD-YYYY
                    </option>
                </select>
            </div>

        </div>

    </div>


    <div style="border:1px solid #DDDDDD;padding:20px;margin:20px">
        <div>
            <h4 style="padding-bottom:20px;border-bottom: 2px solid #DDDDDD">
                <b><?php echo l('INVOICE', true); ?></b></h4></div>
        <div style="padding-top:10px">

            <!-- <div class="form-group features-div-padding  form-inline">
                <label for="payment_capture">
                    <?= l("Payment Capture", true); ?>
                </label>
                <select class="form-control" name="payment_capture" id="payment_capture">
                    <option value="0" <?php if ($company_data['manual_payment_capture'] == 0) {
                        echo 'selected = "selected"';
                    } ?>><?php echo l('Instant', true); ?>
                    </option>
                    <option value="1" <?php if ($company_data['manual_payment_capture'] == 1) {
                        echo 'selected = "selected"';
                    } ?>><?php echo l('Manual', true); ?>
                    </option>
                </select>
               
            </div> -->

           

            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="hide_forecast_charges" class="hide_forecast_charges"
                               <?= $company_data['hide_forecast_charges'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="hide_forecast_charges"><b><?= l("Hide forecast charges from invoice", true); ?></b></label>
                </div>
            </div>


            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="is_total_balance_include_forecast"
                               <?= $company_data['is_total_balance_include_forecast'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="is_total_balance_include_forecast"><b><?= l("Total and balance includes forecast charges", true); ?></b></label>
                </div>
            </div>

            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="show_guest_group_invoice"
                               <?= $company_data['show_guest_group_invoice'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="show_guest_group_invoice"><b><?= l("Show all guests on group invoice", true); ?></b></label>
                </div>
            </div>

        </div>
    </div>


    <div style="border:1px solid #DDDDDD;padding:20px;margin:20px">
        <div>
            <h4 style="padding-bottom:20px;border-bottom: 2px solid #DDDDDD"><b><?php echo l('APPEARANCE', true); ?></b>
            </h4></div>
        <div style="padding-top:10px">

            <div class="form-group features-div-padding  form-inline">
                <label for="calendar_days">
                    <?= l("Show number of days on Calendar", true); ?>
                </label>
                <input type="number" name="calendar_days" class="form-control"
                       value="<?php echo $company_data['calendar_days']; ?>" size=50
                       placeholder="<?php echo l('Calendar Days', true); ?>" min="10" max="30" />
            </div>

            <div class="form-group features-div-padding  form-inline">
                <label for="ui_theme">
                    <?= l("UI Theme", true); ?>
                </label>
                <select class="form-control" name="ui_theme" id="ui_theme">
                    <option value="<?php echo THEME_DEFAULT; ?>" <?php if ($company_data['ui_theme'] == THEME_DEFAULT) {
                        echo 'selected = "selected"';
                    } ?>><?php echo l('Default', true); ?>
                    </option>
                    <option value="<?php echo THEME_DARK; ?>" <?php if ($company_data['ui_theme'] == THEME_DARK) {
                        echo 'selected = "selected"';
                    } ?>><?php echo l('Dark', true); ?>
                    </option>
                </select>
            </div>


            <div class="form-group features-div-padding ">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="enable_new_calendar"
                               <?= $company_data['enable_new_calendar'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="enable_new_calendar"><b><?= l("Enable new calendar UI (with hourly calendar support)", true); ?></b></label>
                </div>
            </div>

            <div class="form-group features-div-padding  form-inline">

                <label for="default_charge_name"><?= l("Default charge name", true); ?></label>

                <input type="text" name="default_charge_name" class="form-control"
                       value="<?php echo $company_data['default_charge_name']; ?>" size=50
                       placeholder="<?php echo l('Default charge name', true); ?>"/>
            </div>

            <div class="form-group features-div-padding  form-inline">
                <label for="default_room_singular"><?= l("Default room label", true); ?></label>
                <span>: (<?php echo l('singular', true); ?>)</span>
                <input type="text" name="default_room_singular" class="form-control"
                       value="<?php echo $company_data['default_room_singular']; ?>" size=15
                       placeholder="<?php echo l('Room', true); ?>"/>
                <span>, (<?php echo l('plural', true); ?>)</span>
                <input type="text" name="default_room_plural" class="form-control"
                       value="<?php echo $company_data['default_room_plural']; ?>" size=15
                       placeholder="<?php echo l('Rooms', true); ?>"/>
            </div>
            <div class="form-group features-div-padding  form-inline">
                <label for="default_room_type"><?= l("Default room type label", true); ?></label>
                <input type="text" name="default_room_type" class="form-control" value="<?php echo $company_data['default_room_type']; ?>"
                       size=50 placeholder="<?php echo l('Room type', true); ?>"/>
            </div>

        </div>
    </div>

    <div class="col-sm-12 ml-2 ">
        <input type="submit" class="btn btn-primary" style="width: 200px;" value="<?php echo l('Update', true); ?>"/>
    </div>
</form>
</div></div>