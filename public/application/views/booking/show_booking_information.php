<!-- Modal -->
<div class="modal fade" id="modify_booking_modal" tabindex="-1" role="dialog" aria-labelledby="label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo l('Modify Booking'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="panel-booking clearfix" style="">
                   <div class="form-group .input-group col-sm-6">
                      <label for="checkin-date"><small><?php echo l('Check-in Date', true); ?></small></label>
                      <div class="hourly-booking-enabled">
                         <input name="check_in_date" class="form-control check-in-date-wrapper">
                         <select name="check_in_time" class="form-control check-in-time-wrapper">
                         </select>
                      </div>
                   </div>

                   <div class="form-group .input-group col-sm-6">
                      <label for="checkout-date"><small><?php echo l('Check-out Date', true); ?></small></label>
                      <div class="hourly-booking-enabled">
                         <input name="check_out_date" class="form-control check-out-date-wrapper">
                         <select name="check_out_time" class="form-control check-out-time-wrapper">
                         </select>
                      </div>
                   </div>
                    <div class="form-group col-sm-12 error-message" style="color: red;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger cancel_booking" style="float: left;"><?php echo l('Cancel Booking'); ?></button>

                <button type="button" class="btn btn-primary save_booking"><?php echo l('save_changes'); ?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo l('close'); ?></button>
            </div>
        </div>
    </div>
</div>
<div id='invoice-container' class="container" style="max-width: 1024px;">

    <div class="page-header col-md-12 row panel">
        <span class="h2"><?php echo l('Booking Information'); ?></span>

        <div class="pull-right invoice-number padding-right-zero">
            <strong><?php echo l('Booking ID'); ?> #:</strong>
            <?php echo $booking_detail['booking_id']; ?>
        </div>
    </div> <!-- /.page-header -->

    <div class="col-md-12 row invoice-header">
        <div class="col-xs-6 padding-left-zero">
            <?php
            // show company logo image
            if (isset($company_logos[0]['filename'])) {
                echo "<img src=\"https://".getenv("AWS_S3_BUCKET").".s3.amazonaws.com/".$company['company_id']."/".$company_logos[0]['filename']."\" id='company-logo-image'/><br/>";
            }
            ?>
            <address>
                <?php

                $company_address = array(
                    $company['address'],
                    $company['city'],
                    $company['region'],
                    $company['postal_code']
                );

                echo "<strong>".$company['name']."</strong><br/>";
                echo implode(", ", $company_address);
                echo "<br/>";
                echo ($company['phone'] != "") ? l('Phone', true).": ".$company['phone']."<br/>":'';
                echo ($company['fax'] != "") ? l('Fax', true).": ".$company['fax']."<br/>":'';
                echo ($company['email'] != "") ? l('Email', true).": <span id='company-email'>".$company['email']."</span><br/>":'';
                echo ($company['website'] != "") ? l('Website', true).": ".$company['website']."<br/>":'';
                echo $company['GST_number'];
                echo ($company['bussiness_name'] != "")?"Bussiness Name: ".$company['bussiness_name']."<br/>":'';
                echo ($company['bussiness_number'] != "")?"Bussiness/NIFT Number: ".$company['bussiness_number']."<br/>":'';
                echo ($company['bussiness_fiscal_number'] != "")?"Fiscal Number: ".$company['bussiness_fiscal_number']."<br/>":'';
                ?>
                <?php echo '<p class="invoice-header">'.$company['invoice_header'].'</p>'; ?>
        </div>
        <div class="col-xs-6 text-right booking_id_div padding-right-zero">
            <address class="form-inline">
                <strong><?php  echo l('Customer'); ?>:</strong>
                <?php
                if (count($booking_customer) > 0):
                    ?>
                    <?php
                    echo $booking_customer['customer_name'];
                    ?>

                    <input type="hidden" class="text-right" id="customer_id"  disabled value="<?= !empty($booking_customer['customer_id']) ? $booking_customer['customer_id'] : "";?>"/>
                    <input type="hidden" class="text-right" id="evc_card_status"  value="<?= (isset($customer['evc_card_status']) && $customer['evc_card_status']) ? $customer['evc_card_status'] : ''; ?>"/>
                    <br/>
                    <?php
                    $customer_address = array();
                    if ($booking_customer['address']) $customer_address[] = $booking_customer['address'];
                    if ($booking_customer['address2']) $customer_address[] = $booking_customer['address2'];
                    if ($booking_customer['city']) $customer_address[] = $booking_customer['city'];
                    if ($booking_customer['region']) $customer_address[] = $booking_customer['region'];
                    if ($booking_customer['country']) $customer_address[] = $booking_customer['country'];
                    if ($booking_customer['postal_code']) $customer_address[] = $booking_customer['postal_code'];
                    $customer_address = implode(", ", $customer_address);
                    echo ($customer_address)?"Address: ".$customer_address."<br/>":'';

                    echo ($booking_customer['phone'])?"Phone: ".$booking_customer['phone']."<br/>":'';
                    echo ($booking_customer['fax'])?"Fax: ".$booking_customer['fax']."<br/>":'';
                    echo ($booking_customer['email'])?"Email: <span id='customer-email'>".$booking_customer['email']."</span><br/>":'';

                    if (isset($booking_customer['customer_fields']) && count($booking_customer['customer_fields']) > 0) {
                        foreach ($booking_customer['customer_fields'] as $customer_field) {
                            echo ($customer_field['name'] && $customer_field['value']) ? $customer_field['name'].": " . $customer_field['value'] . "<br/>" : '';
                        }
                    }

                endif;


                ?>
            </address>
            <address>
                <strong class="booking_status"><?php echo l('Status'); ?>:</strong>
                <?php if($booking_detail['state'] == RESERVATION) { echo l('Reservation', true); } 
                else if($booking_detail['state'] == INHOUSE) { echo l('Checked-In', true); } 
                else if($booking_detail['state'] == CHECKOUT) { echo l('Checked-Out', true); } 
                else if($booking_detail['state'] == CANCELLED) { echo l('Cancelled', true); } 
                else if($booking_detail['state'] == DELETED) { echo l('Deleted', true); } 
                else if($booking_detail['state'] == OUT_OF_ORDER) { echo l('Out of order', true); } ?>
                <br/>
                <?php  echo($company['default_room_singular']); ?>: <?php echo (isset($room_detail['room_name'])) ? $room_detail['room_name'] : l('Not Assigned', true); ?><br/>
                <?php echo($company['default_room_type']);?>: <?php echo (isset($room_detail['room_type_name']))?$room_detail['room_type_name']:''; ?><br/>

                <br/>
                <span style="font-size: 15px;">
                    <?php echo l('check_in_date'); ?>: <span id="check-in-date">

                    <?php echo isset($this->enable_hourly_booking)  && $this->enable_hourly_booking ? get_local_formatted_date($booking_detail['check_in_date']).' '.date('h:i A', strtotime($booking_detail['check_in_date'])) : get_local_formatted_date($booking_detail['check_in_date']);
                    ?>
                    </span>
                    <br/><br/>

                    <input  type="hidden" name="chk_out_dt" id="chk_out_dt" value="<?php echo get_local_formatted_date($booking_detail['check_out_date']); ?>">
                    
                    <input  type="hidden" name="chk_in_dt" id="chk_in_dt" value="<?php echo get_local_formatted_date($booking_detail['check_in_date']); ?>">

                    <input style="width: 70px;" type="hidden" name="chk_in_tm" id="chk_in_tm" value="<?php echo isset($this->enable_hourly_booking) && $this->enable_hourly_booking ? date('h:i A', strtotime($booking_detail['check_in_date'])) : ''; ?>">

                    <input style="width: 70px;" type="hidden" name="chk_out_tm" id="chk_out_tm" value="<?php echo isset($this->enable_hourly_booking) && $this->enable_hourly_booking ? date('h:i A', strtotime($booking_detail['check_out_date'])) : ''; ?>">

                    <input type="hidden" name="booking_id" value="<?php echo $booking_detail['booking_id']; ?>">
                    <input type="hidden" name="room_id" value="<?php echo $room_detail['room_id']; ?>">
                    <input type="hidden" name="date_format" class="date_format" value="<?php echo $this->company_date_format; ?>">
                    
                    <input type="hidden" name="enable_hourly_booking" class="enable_hourly_booking" value="<?php echo $this->enable_hourly_booking; ?>">

                    <?php echo l('check_out_date'); ?>:
                    <span class="change-check-out-date">
                    
                    <?php echo isset($this->enable_hourly_booking)  && $this->enable_hourly_booking ? get_local_formatted_date($booking_detail['check_out_date']).' '.date('h:i A', strtotime($booking_detail['check_out_date'])) : get_local_formatted_date($booking_detail['check_out_date']);
                    ?>

                    </span>
                </span>


                <br/>
                <?php
                if (isset($booking_fields) && count($booking_fields) > 0) {
                    foreach ($booking_fields as $booking_field) {
                        echo ($booking_field['name'] && $booking_field['value']) ? $booking_field['name'].": " . $booking_field['value'] . "<br/>" : '';
                    }
                }
                ?>
            </address>
        </div>
    </div> <!-- /.container -->
    <div class="col-md-12 row charges-and-payments <?php echo $booking_detail['state'] == CANCELLED ? 'hidden' : '' ?>">
        <div>
            <hr>
            <div class="col-sm-8">
            </div>
            <div class="col-sm-4 text-right" style="padding-right: 0;">
                <button class="btn btn-success modify_booking_btn">
                    <?php echo l('Modify this Booking'); ?>
                </button>
            </div>
        </div>
    </div> <!-- /. container -->


    
	