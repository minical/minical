<?php if (!$read_only): ?>
    <!-- -->
    <div class="modal fade"  id="add-payment-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo l('add').' '.l('payment'); ?></h4>
                </div>
                <div class="modal-body form-horizontal">
                    <div class="form-group" id="payment_date">
                        <label for="payment_date" class="col-sm-4 control-label"><?php echo l('payment').' '.l('date'); ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="payment_date" placeholder="Payment Date" value="<?php echo get_local_formatted_date($company['selling_date']); ?>">
                            
                        </div>
                    </div>

                    <div class="form-group" id="option-to-add-multiple-payments">
                        <label for="pay_for" class="col-sm-4 control-label"><?php echo l('pay_for'); ?></label>
                        <div class="col-sm-8">
                            <select class="form-control" name="pay_for">
                                <option value="this_invoice_only"><?php echo l('this_invoice_only'); ?></option>
                                <option value="all_bookings" name="all_bookings_option"></option>
                            </select>
                        </div>
                    </div>

                    <!-- Amount is manually entered only when user is paying for 1 booking -->
                    <div class="form-group">
                        <label for="payment_amount" class="col-sm-4 control-label">
                            <?php echo l('amount'); ?>
                        </label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="payment_amount">
                        </div>
                    </div>

                    <div class="form-group payment_type_div">
                        <label for="pay_for" class="col-sm-4 control-label"><?php echo l('method'); ?></label>
                        <div class="col-sm-8">
                            <select name="payment_type_id" class="input-field form-control">
                                <?php
                                foreach ($payment_types as $payment_type):
                                    ?>
                                    <option value="<?php echo $payment_type->payment_type_id;?>">
                                        <?php echo $payment_type->payment_type; ?>
                                    </option>
                                <?php
                                endforeach;
                                ?>
                            </select>
                            <input type="hidden" name="current_payment_gateway" value="<?php echo $this->current_payment_gateway; ?>">
                        </div>
                    </div>

                    <?php if(check_active_extensions($this->current_payment_gateway, $this->company_id)){ ?>
                    <div style="display:none;" class="form-group use-payment-gateway-btn ">
                        <label for="payment_amount" class="col-sm-4 control-label">
                            <?php echo l('use_payment_gateway'); ?>
                        </label>

                        <div class="col-sm-8" id="use-gateway-div">
                            <div class="col-sm-2"><input type="checkbox" class="form-control use-gateway" id="check-wep" data-gateway_name="<?=$selected_payment_gateway;?>" name="<?=$selected_payment_gateway;?>_use_gateway"></div>
                        </div>
                    </div>
                    
                    <?php }
                    if (count(array_filter($customers)) > 0):
                        ?>
                        <div class="form-group">
                            <label for="pay_for" class="col-sm-4 control-label"><?php echo l('paid_by'); ?> <small class="text-muted">(<?php echo l('optional'); ?>)</small></label>
                            <div class="col-sm-8">
                                <select name="customer_id" class="input-field form-control paid-by-customers">
                                    <?php
                                    $available_gateway = '';
                                    foreach ($customers as $customer):
                                        if ($customer['customer_id'] == $selected_customer_id) {
                                            $selected_customer = 'selected';
                                        } else {
                                            $selected_customer = '';
                                        }
                                        if ($customer['stripe_customer_id'] || $customer['cc_tokenex_token'] || $customer['customer_meta_token']) {
                                            $is_gateway_available = 'true';
                                            if($customer['stripe_customer_id'])
                                                $available_gateway = 'stripe';
                                            else
                                                $available_gateway = 'tokenex';
                                        } else {
                                            $is_gateway_available = 'false';
                                        }
                                        ?>
                                        <option data-available-gateway="<?=$available_gateway;?>" is-gateway-available="<?=$is_gateway_available;?>" <?php echo $selected_customer; ?> value="<?php echo $customer['customer_id']; ?>">
                                            <?php echo $customer['customer_name']; ?>
                                        </option>
                                    <?php
                                    endforeach;
                                    ?>
                                </select>

                            </div>
                        </div>
                    <?php
                    endif;
                    ?>

                    <div class="form-group" id="description-form-group">
                        <label for="amount" class="col-sm-4 control-label">
                            <?php echo l('description'); ?> <small class="text-muted">(<?php echo l('optional'); ?>)</small>
                        </label>
                        <div class="col-sm-8">
                            <textarea class="form-control" name="description" row=2></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden"  id="manual_payment_capture" value="<?=$company['manual_payment_capture'];?>">
                    <?php if($company['manual_payment_capture'] != 1){ ?>
                        <button type="button" class="btn btn-success add_payment_button hidden" id="add_payment_button">
                            <?php echo l('add').' '.l('payment'); ?>
                        </button>

                    <?php } else { ?>
                        <button type="button" class="btn btn-success add_payment_button hidden" id="auth_and_capture">
                            <?php echo l('Add Charge'); ?>
                        </button>
                        <button type="button" class="btn btn-success add_payment_button hidden" id="authorize_only">
                            <?php echo l('Pre-Authorize'); ?>
                        </button> 
                    <?php } ?>
                    <button type="button" class="btn btn-success add_payment_button" id="add_payment_normal">
                        <?php echo l('add').' '.l('payment'); ?>
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <?php echo l('close'); ?>
                    </button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div class="modal fade"  id="add-invoice-transfer-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo l('Invoice Transfer Payment'); ?></h4>
                </div>
                <div class="modal-body form-horizontal">
                    <div class="form-group" id="add_bookings">
                        <label for="pay_for" class="col-sm-4 control-label">Add Booking ID</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control invoice_booking_id" name="invoice_booking_id" />
                        </div>
                    </div>

                    <div class="form-group" id="payment_date">
                        <label for="payment_date" class="col-sm-4 control-label"><?php echo l('payment').' '.l('date'); ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="payment_date" placeholder="Payment Date" value="<?php echo get_local_formatted_date($company['selling_date']); ?>">
                            
                        </div>
                    </div>

                    <!-- Amount is manually entered only when user is paying for 1 booking -->
                    <div class="form-group">
                        <label for="payment_amount" class="col-sm-4 control-label">
                            <?php echo l('amount'); ?>
                        </label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="invoice_pay_amount">
                        </div>
                    </div>

                    <div class="form-group payment_type_div">
                        <label for="pay_for" class="col-sm-4 control-label"><?php echo l('method'); ?></label>
                        <div class="col-sm-8">
                            <select name="inv_payment_type_id" class="input-field form-control">
                                <?php
                                foreach ($payment_types as $payment_type):
                                    ?>
                                    <option value="<?php echo $payment_type->payment_type_id;?>">
                                        <?php echo $payment_type->payment_type; ?>
                                    </option>
                                <?php
                                endforeach;
                                ?>
                            </select>
                            <input type="hidden" name="current_payment_gateway" value="<?php echo $this->current_payment_gateway; ?>">
                        </div>
                    </div>

                    <?php if(check_active_extensions($this->current_payment_gateway, $this->company_id)){ ?>
                    <div style="display:none;" class="form-group use-payment-gateway-btn ">
                        <label for="payment_amount" class="col-sm-4 control-label">
                            <?php echo l('use_payment_gateway'); ?>
                        </label>

                        <div class="col-sm-8" id="use-gateway-div">
                            <div class="col-sm-2"><input type="checkbox" class="form-control use-gateway" id="check-wep" data-gateway_name="<?=$selected_payment_gateway;?>" name="<?=$selected_payment_gateway;?>_use_gateway"></div>
                        </div>
                    </div>
                    
                    <?php }
                    if (count(array_filter($customers)) > 0):
                        ?>
                        <div class="form-group">
                            <label for="pay_for" class="col-sm-4 control-label"><?php echo l('paid_by'); ?> <small class="text-muted">(<?php echo l('optional'); ?>)</small></label>
                            <div class="col-sm-8">
                                <select name="customer_id" class="input-field form-control paid-by-customers">
                                    <?php
                                    $available_gateway = '';
                                    foreach ($customers as $customer):
                                        if ($customer['customer_id'] == $selected_customer_id) {
                                            $selected_customer = 'selected';
                                        } else {
                                            $selected_customer = '';
                                        }
                                        if ($customer['stripe_customer_id'] || $customer['cc_tokenex_token'] || $customer['customer_meta_token']) {
                                            $is_gateway_available = 'true';
                                            if($customer['stripe_customer_id'])
                                                $available_gateway = 'stripe';
                                            else
                                                $available_gateway = 'tokenex';
                                        } else {
                                            $is_gateway_available = 'false';
                                        }
                                        ?>
                                        <option data-available-gateway="<?=$available_gateway;?>" is-gateway-available="<?=$is_gateway_available;?>" <?php echo $selected_customer; ?> value="<?php echo $customer['customer_id']; ?>">
                                            <?php echo $customer['customer_name']; ?>
                                        </option>
                                    <?php
                                    endforeach;
                                    ?>
                                </select>

                            </div>
                        </div>
                    <?php
                    endif;
                    ?>

                    <div class="form-group" id="description-form-group">
                        <label for="amount" class="col-sm-4 control-label">
                            <?php echo l('description'); ?> <small class="text-muted">(<?php echo l('optional'); ?>)</small>
                        </label>
                        <div class="col-sm-8">
                            <textarea class="form-control invoice_description" name="invoice_description" row=2></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <!-- <input type="hidden"  id="manual_payment_capture" value="<?=$company['manual_payment_capture'];?>"> -->
                    <button type="button" class="btn btn-success add_inv_pay_button" id="add_inv_pay_button">
                        <?php echo l('add').' '.l('payment'); ?>
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <?php echo l('close'); ?>
                    </button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->



<?php endif; ?>
<!--move charge or payment modal-->
<div class="modal fade"  id="move-charge-payment-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="margin: 0 auto;width: 350px;">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo l('Select Folio'); ?>
                </h4>
            </div>
            <div class="modal-body form-horizontal">
                <form class="form-horizontal" role="form">
                    <div class="form-group form-group-lg">
                        <div class="col-lg-10 error">
                            <select id="folios-list" class="selectpicker show-tick form-control">
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="charge_id" >
                    <input type="hidden" name="payment_id" >
                    <button type="button" id="move-charge-payment" class="btn btn-primary"><?=l("Save");?></button>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

<div class="modal fade"  id="add-daily-charges-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="z-index:11111;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo l('Pay Period settings'); ?></h4>
            </div>
            <div class="modal-body form-horizontal">
                <div class="form-group" id="option-to-add-multiple-payments">
                    <div class="col-sm-12">
                        <input type="checkbox" class="add-daily-charge" name="add-daily-charge" value="1">
                        <?php echo l('Allow Daily',true).' '.l($this->default_room_singular).' '.l('Charges to be added for remaining days of a Monthly/Weekly period bookings.',true) ; ?>
                    </div>
                </div>
                <div class="form-group" id="residual_rate_div">
                    <div class="col-sm-12">
                        <?php echo l('Residual rate'); ?>
                        <input type="number" class="residual_rate" name="residual_rate" value="">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <span class="daily_charge_msg" style="color: green;display: none;margin: 32px;"><?php echo l('Details Saved', true); ?></span>
                <button type="button" class="btn btn-success" id="add_save_daily_charge_button">
                    <?php echo l('Ok'); ?>
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?php echo l('close'); ?>
                </button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!--success message bootstrap modal-->
<div class="modal fade"  id="alert-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-body form-horizontal" style="padding: 0px;">
            <div class="alert alert-success">
                <h3 style="text-align: center;margin: 0;"><?php echo l('Saved', true); ?>!</h3>
            </div>
        </div>
        <!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

<div class="modal fade" id="display-errors">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<input type="hidden" name="pos_booking_id" class="pos_booking_id" value="<?php echo $booking_detail['booking_id']; ?>">
<input type="hidden" name="default_charge_name" class="default_charge_name" value="<?php echo $company['default_charge_name']; ?>">
<input type="hidden" name="booking_state" class="booking_state" id="booking_state" value="<?php echo $booking_detail['state']; ?>">

<!--bootstrap popover-->
<div id="popover-update-folio-name" class="hide">
    <div class="input-group">
        <input type="text" placeholder="Edit name" class="form-control updated-folio-name">
        <input type="hidden" class="update-folio-id" value="">
        <span class="input-group-btn">
            <button type="button" class="btn btn-primary btn-update-folio-name">
                <i class="fa fa-check" aria-hidden="true"></i>
            </button>
            <i class="fa fa-close"></i>
        </span>
    </div>
</div>

<div id='invoice-container' class="">
    <div class="app-page-title">
        <div class="page-title-wrapper page-title-wrapper-wep">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="pe-7s-notebook text-success"></i>
                </div>
                <?php echo l('invoice'); ?>
            </div>
            <div class="page-title-actions m-025">
                <div>
                    <button class="btn btn-primary m-1" id="print-invoice-button">
                        <?php echo l('print').' '.l('invoice'); ?>
                    </button>

                    <?php
                    if ($menu_on === true):
                        ?>
                    <button class="btn btn-primary dropdown-toggle m-1 " type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                        <?php echo l('more').' '.l('options'); ?>
                       <!--  <span class="caret"></span> -->
                    </button>
                    <ul class="dropdown-menu pull-right" role="menu" aria-labelledby="dropdownMenu1">
                        <li role="presentation">
                            <a role="menuitem" id="email-invoice-button" tabindex="-1" href="#">
                                <?php echo l('email').' '.l('invoice'); ?>
                            </a>
                        </li>
                        <li role="presentation">
                            <a role="menuitem" class="open-booking-button" data-booking-id="<?php echo $booking_detail['booking_id']; ?>" tabindex="-1" href="#">
                                <?php echo l('booking').' '.l('information'); ?>
                            </a>
                        </li>
                        <li role="presentation">
                            <a role="menuitem" class="open-invoice-logs-button" data-booking-id="<?php echo $booking_detail['booking_id']; ?>" tabindex="-1" href="#">
                                <?php echo l('invoice').' '.l('logs'); ?>
                            </a>
                        </li>
                        <?php if ($this->review_management_settings) { ?>
                            <li role="presentation">
                                <a role="menuitem" id="feedback-email-button" tabindex="-1" href="#">
                                    <?php echo l('Email Customer Feedback Request'); ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                    <?php
                    endif; ?>
                </div>
                
            </div>
        </div>
    </div>
<!--Show success or error message-->
<?php 
if((isset($this->is_nestpay_enabled) && $this->is_nestpay_enabled == true ) ||
 (isset($this->is_nestpaymkd_enabled) && $this->is_nestpaymkd_enabled == true) ||
 (isset($this->is_nestpayalb_enabled) && $this->is_nestpayalb_enabled == true) ||
 (isset($this->is_nestpaysrb_enabled) && $this->is_nestpaysrb_enabled == true)) {
    if($this->session->flashdata('payment_success_message'))
    {
?>
        <div class="alert alert-success">
            <h3><b><?php echo $this->session->flashdata('payment_success_message'); ?></b></h3>
        </div>
<?php
    }
    else if($this->session->flashdata('payment_error_message'))
    {
?>
        <div class="alert alert-danger">
           <h3><b> <?php echo $this->session->flashdata('payment_error_message'); ?></b></h3>
        </div>
<?php
    } }
?>



<div class="main-card mb-3 card">
    <div class="card-body card-img">

    <?php
    // show company logo image
    if (isset($company_logos[0]['filename'])) {
        echo "<img src='" . $this->image_url . $company['company_id'] . "/" . $company_logos[0]['filename'] . "' id='company-logo-image'/><br/>";
    }
    ?>

    <div class="col-md-12 row invoice-header">
        <div class="col-xs-4 padding-left-zero padding-left-zero-wep">
            <address class="text-gapp">
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
                echo ($company['phone'] != "")?"Phone: ".$company['phone']."<br/>":'';
                echo ($company['fax'] != "")?"Fax: ".$company['fax']."<br/>":'';
                echo ($company['email'] != "")?"Email: <span id='company-email'>".$company['email']."</span><br/>":'';
                echo ($company['website'] != "")?"Website: ".$company['website']."<br/>":'';
                echo $company['GST_number'];
                echo ($company['bussiness_name'] != "")?"Bussiness Name: ".$company['bussiness_name']."<br/>":'';
                echo ($company['bussiness_number'] != "")?"Bussiness/NIFT Number: ".$company['bussiness_number']."<br/>":'';
                echo ($company['bussiness_fiscal_number'] != "")?"Fiscal Number: ".$company['bussiness_fiscal_number']."<br/>":'';
                ?>
                <!-- <?php echo '<p class="invoice-header">'.$company['invoice_email_header'].'</p>'; ?> -->
                <?php if($this->vendor_id != 9) { ?>
                    <?php echo '<p class="invoice-header">'.$company['invoice_header'].'</p>'; ?>
                <?php } ?>
        </div>
        <div class="col-xs-4 invoice_heading_div padding-left-zero-wep">
            <address class="form-inline text-gapp billed-gap">
                <strong><?php  echo l('billed_to'); ?>:</strong>
                <?php
                if (count($booking_customer) > 0):
                    if ($booking_customer['customer_name'] != ""):
                        ?>

                        <select class="input-field form-control hidden-print" onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
                            <option <?php if ($customer_id == false) echo "SELECTED"; ?> value="<?php echo base_url()."invoice/show_invoice/".$booking_detail['booking_id']."/"; ?>">
                                <?php echo l('everyone'); ?>
                            </option>
                            <?php
                            foreach ($customers as $customer):
                                ?>
                                <option value="<?php echo base_url()."invoice/show_invoice/".$booking_detail['booking_id']."/0/".$customer['customer_id']; ?>"
                                    <?php if ($customer_id == $customer['customer_id']) echo "SELECTED"; ?>>
                                    <?php echo $customer['customer_name']; ?>
                                </option>
                            <?php
                            endforeach;
                            ?>
                        </select>

                        <br/>
                    <?php
                    endif;
                    ?>
                    <strong><a href='<?php echo base_url()."customer/history/".$booking_customer['customer_id']; ?>'>
                            <?php
                            echo $booking_customer['customer_name'];
                            ?>

                        </a></strong>
                    <input type="hidden" name="customer_name" class="customer_name" value="<?php echo $booking_customer['customer_name']; ?>">
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
                    echo ($customer_address) ? l('Address', true).": ".$customer_address."<br/>":'';

                    echo (array_search('Phone', array_column($customer_fields, 'name')) !== FALSE && $booking_customer['phone']) ? l('Phone', true).": ".$booking_customer['phone']."<br/>":'';
                    echo (array_search('Fax', array_column($customer_fields, 'name')) !== FALSE && $booking_customer['fax']) ? l('Fax', true).": ".$booking_customer['fax']."<br/>":'';
                    echo (array_search('Email', array_column($customer_fields, 'name')) !== FALSE && $booking_customer['email']) ? l('Email', true).": <span id='customer-email'>".$booking_customer['email']."</span><br/>":'';

                    if (isset($booking_customer['customer_fields']) && count($booking_customer['customer_fields']) > 0) {
                        foreach ($booking_customer['customer_fields'] as $customer_field) {
                            echo (isset($customer_field['name']) && $customer_field['name'] && isset($customer_field['value']) && $customer_field['value']) ? $customer_field['name'].": " . $customer_field['value'] . "<br/>" : '';
                        }
                    }

                endif;


                ?>
            </address>
        </div>
           <div class="pull-right right-wep">
                    <strong><?php echo l('invoice'); ?> #:</strong>
                    <?php echo str_pad((isset($invoice_number))?$invoice_number:0, 8, "0", STR_PAD_LEFT); ?>
                </div>
          <?php if(isset($this->is_custom_invoice_enabled) && $this->is_custom_invoice_enabled == true) { ?>
            <div class="pull-right create-wep">
                <strong ><?php 

                $invoice_createdate = isset($invoice_create_data['date_time']) && $invoice_create_data['date_time'] ? date('Y-m-d h:i A', strtotime($invoice_create_data['date_time']))  : date('h:i A', strtotime($booking_detail['check_in_date']));
                echo l('Invoice Date'); ?>:</strong> <span data-invoice_createdate="<?php echo $invoice_createdate;?>" id="invoice-create-date">
                <?php echo $invoice_createdate; 
                ?>
                </span><br/>
            </div>
            <?php }?>     
        <div class="col-xs-4 text-right padding-right-zero padding-left-zero-wep">
             
            <address class="text-gapp book-wep">
                <strong class="invoice_booking_id"><?php echo l('booking').' '.l('id'); ?>:</strong>
                <input type="text" class="text-right m-119 m-024" id="booking_id" disabled value="<?php
                // add 8 leading zeroes
                echo $booking_detail['booking_id']
                ?>"/>
                <br/>
                <?php if(!$read_only || !$company['hide_room_name'])
                {
                    echo($company['default_room_singular']); ?>: <?php echo (isset($room_detail['room_name'])) ? $room_detail['room_name']: l('Not Assigned', true) ; ?><br/>
                <?php 
                }
                    echo($company['default_room_type']);?>: <?php echo (isset($room_detail['room_type_name']))?$room_detail['room_type_name']:''; ?><br/>
                <?php echo l('check_in_date'); ?>: <span id="check-in-date">

                <?php echo isset($this->enable_hourly_booking) && $this->enable_hourly_booking ? get_local_formatted_date($booking_detail['check_in_date']).' '.date('h:i A', strtotime($booking_detail['check_in_date'])) : get_local_formatted_date($booking_detail['check_in_date']);
                ?>
                    
                </span><br/>
                
                <?php echo l('check_out_date'); ?>: <span id="check-out-date">
                
                <?php echo isset($this->enable_hourly_booking) && $this->enable_hourly_booking ? get_local_formatted_date($booking_detail['check_out_date']).' '.date('h:i A', strtotime($booking_detail['check_out_date'])) : get_local_formatted_date($booking_detail['check_out_date']);
                ?>

                </span><br/>

                <?php echo l('Adult'); ?>: <span id="adult_count"><?php echo $booking_detail['adult_count']; ?></span>,
                <?php echo l('Children'); ?>: <span id="child_count"><?php echo $booking_detail['children_count']; ?>
                
                </span><br/>

                <?php echo l('Booking Source', true); ?>: <?php echo get_booking_source($booking_detail['source']); ?>

               <span id="booking-field">
                <?php

                if (isset($booking_fields) && count($booking_fields) > 0) {
                    foreach ($booking_fields as $booking_field) {
                        $value = $custom_booking_fields[$booking_field['id']];
                        if ($value) {
                        echo ($booking_field['name'] && $value) ? $booking_field['name'].": " . $value . "<br/>" : '';
                        }
                    }
                }
                ?>
                </span>
            </address>


        </div>
    </div> <!-- /.container -->

    <?php if($this->vendor_id == 9) { ?>
        <div class="col-sm-12">
            <?php echo '<p class="invoice-header">'.$company['invoice_header'].'</p>'; ?>
        </div>
    <?php } ?>

    <div class="col-md-12 row charges-and-payments">
        <?php if(!$read_only) { ?>
            <div class="panel panel-success hidden-print" style="box-shadow: none;">
                <div id="folios" class="panel-heading folios">
                    <ul class="nav nav-tabs">
                        <?php
                        if($folios && count($folios) > 0) {
                            foreach ($folios as $key => $folio) { ?>
                                <li class="<?=($current_folio_id == $folio['id']) ? " active " : ""?> <?=$folio['charge_count'] || $folio['payment_count'] ? "non-empty-folio" : "";?> extra-large-wep" data-folio-id="<?=$folio['id'];?>">
                                    <div>
                                        <a href="<?= base_url()."invoice/show_invoice/".$booking_detail['booking_id']."/".$folio['id'];?>">
                                            <div><?php echo l('Folio', true); ?> #<?php echo $key + 1; ?></div>
                                        </a>
                                        <a href="#" id="folio-name-<?=$folio['id']?>" class="folio-name" data-toggle="popover">
                                            <?php echo $folio['folio_name'];?>
                                        </a>
                                    </div>
                                    <span class="remove-folio">
                                    <i class="fa fa-close"></i>
                                </span>
                                </li>
                            <?php }
                        } else { ?>
                            <li class="active <?= (isset($charges) && count($charges)) || (isset($payments) && count($payments)) ? "non-empty-folio" : "";?>" data-folio-id="">
                                <div>
                                    <a href="<?= base_url()."invoice/show_invoice/".$booking_detail['booking_id'];?>">
                                        <div><?php echo l('Folio', true); ?> #1</div>
                                    </a>
                                    <a href="#" id="folio-name-" class="folio-name" data-toggle="popover">
                                        <?php echo l('Folio', true); ?> #1
                                    </a>
                                </div>
                                <span class="remove-folio">
                                <i class="fa fa-close"></i>
                            </span>
                            </li>
                        <?php } ?>
                        <button class="btn btn-primary dropdown-toggle pull-right hidden-print" type="button" id="add-new-folio" aria-expanded="true">
                            <i class="fa fa-plus"></i>
                            <?php echo l('Add', true).' '.l('Folio', true); ?>
                        </button>
                        <input type="text" class="hidden" id="current_folio_id" disabled value="<?=$current_folio_id;?>"/>
                    </ul>
                </div>
            </div>
        <?php } ?>
        <div class="panel panel-default" style="border: yellowgreen;">
            <div class="h2 charge_div">
                <div class="col-sm-8">
                    <?php echo l('charges'); ?>
                </div>
                <div class="col-sm-4 text-right m-022">
                    <button class="hidden-print btn btn-light btn-sm expand-all">
                        <i class="glyphicon glyphicon-plus" aria-hidden="true"></i>
                        <?php echo l('expand_all'); ?>
                    </button>
                    <button class="hidden-print btn btn-light btn-sm collapse-all">
                        <i class="glyphicon glyphicon-minus" aria-hidden="true"></i>
                        <?php echo l('collapse_all'); ?>
                    </button>
                </div>
            </div>

            <div class="table-responsive col-lg-12">
            <table id="charge-table" class="table" >
                <thead>
                <tr>
                    <th class="text-left"><?php echo l('date'); ?></th>
                    <th class="text-left"><?php echo l('description'); ?></th>
                    <th class="text-left"><?php echo l('paying').' '.l('customer'); ?></th>
                    <th class="text-left"><?php echo l('charge_type'); ?></th>
                    <th class="text-right"><?php echo l('amount'); ?></th>
                    <th class="text-right tax_column"><?php echo l('tax'); ?></th>
                    <th class="text-right"><?php echo l('total'); ?></th>
                    <th class="delete-td"></th> <!-- for x button -->
                </tr>
                </thead>

                <tbody>
                <?php
                $click_desible ="";
                if($this->restrict_edit_after_checkout == 1 &&  $booking_detail['state'] == 2 ){

                    $click_desible ="disabled";

                }
                if (isset($charges)):
                    foreach($charges as $charge):
                        ?>
                        <tr <?php
                        if (isset($charge["charge_id"]))
                        {
                            echo "class='editable_tr charge_row' id = '".$charge["charge_id"]."' charge_id ='".$charge["charge_id"]."'";
                            echo " data-pay_period='".$charge["pay_period"]."'";
                        }
                        else
                        {
                            echo "class='bg-warning charge_row' data-toggle ='popover' data-content='".l('This is a forecast of upcoming charge. Hence, it is not modifiable', true)."' data-trigger ='hover' data-placement ='bottom'";
                            echo " data-pay_period='".$charge["pay_period"]."'";
                        }
                        ?> >
                            <td class="<?php echo (isset($charge["charge_id"]))?"editable_td":""; ?>" onclick="">
                                    <span name="selling-date">
                                        <?php echo get_local_formatted_date($charge['selling_date']); ?>
                                    </span>
                            </td>
                            <td class="<?php echo (isset($charge["charge_id"]))?"editable_td":""; ?>" onclick="">
                                    <span name="description">
                                        <?php
                                        // description
                                        echo $charge['description'];
                                        ?>
                                    </span>
                            </td>
                            <td class="<?php echo (isset($charge['charge_id']))?"editable_td":""; ?>" onclick="">
                                <?php

                                ?>
                                <span id="<?php
                                if (isset($charge['charge_id']))
                                    echo $charge['customer_id'];
                                ?>" name="customer">
                                            <?php
                                            if (isset($charge['charge_id']))
                                                echo $charge['customer_name'];
                                            ?>
                                        </span>
                                <?php

                                ?>
                            </td>
                            <td class='<?php echo (isset($charge["charge_id"])) ? "editable_td" : ""; ?>' onclick="">
                                    <span id="<?php echo $charge['charge_type_id']; ?>" name="charge-type">
                                        <?php echo $charge['charge_type_name']; ?>
                                    </span>
                            </td>
                            <td class='<?php echo (isset($charge["charge_id"]))?"editable_td":""; ?> text-right' onclick="">
                                    <span name='amount'>
                                        <?php
                                        $rate = (float)$charge['amount'];
                                        echo number_format($rate, 2, ".", ",");
                                        ?>
                                    </span>
                            </td>
                            <td class='text-right h5'>
                                <small>
                                        <span class='td-tax'>
                                            <?php
                                            // calculation is done in invoice temporarily
                                            // eventually migrate it to controller
                                            $combined_tax = 0;

                                            if (isset($charge['taxes'])) {
                                                foreach ($charge['taxes'] as $tax) {
                                                    if ($tax['is_percentage'] == 1)
                                                    {
                                                        if($tax['is_tax_inclusive'] == 1)
                                                        {
                                                            $tax_amount = (float)$charge['amount'] - ($rate / (1 + ((float)$tax['tax_rate'] * 0.01)));
                                                            $combined_tax = $combined_tax;
                                                        }
                                                        else
                                                        {
                                                            $tax_amount = (float)$tax['tax_rate'] * $rate * 0.01;
                                                            $combined_tax = $combined_tax + $tax_amount;
                                                        }
                                                    }
                                                    else
                                                    {
                                                        if($tax['is_tax_inclusive'] == 1)
                                                        {
                                                            $tax_amount = (float)$tax['tax_rate'];
                                                            $combined_tax = $combined_tax;
                                                        } else {
                                                            $tax_amount = (float)$tax['tax_rate'];
                                                            $combined_tax = $combined_tax + $tax_amount;
                                                        }
                                                    }

                                                    echo '<div class="tax">'
                                                        . '<span id="'.$tax['tax_type_id'].'"'
                                                        .'class="'.($tax['is_tax_inclusive'] == 1 ? "hidden" : "").' tax-type">'.$tax['tax_type'].' </span>'
                                                        . '<span data-tax-rate="'.$tax['tax_rate'].'" data-tax-unit="'.$tax['is_percentage'].'" data-real-taxes="'.$tax_amount.'"class="'.($tax['is_tax_inclusive'] == 1 ? "hidden" : "").' tax-amount">'. number_format($tax_amount, 2, ".", ",") . '</span>'
                                                        . '</div>';
                                                }
                                            }
                                            ?>
                                        </span>
                                </small>
                            </td>
                            <td class='text-right'>
                                            <span data-real-total-charge="<?php echo ($rate + $combined_tax); ?>" class='charge'>
                                                <?php
                                                // as stated above, combinedTax is to be migrated to controller
                                                if (isset($charge['charge_type_id']))
                                                    echo number_format($rate + $combined_tax, 2, ".", ","); // charge
                                                ?>
                                            </span>
                            </td>

                            <td class="delete-td" width=30>
                                <?php
                                // only display X button if it's actual charge (not forecasted charge)
                                if (isset($charge["charge_id"])):
                                    ?>
                                    <!-- <i class="x-button hidden-print" title="Created by <?php echo $charge['user_name']; ?>"></i> -->
                                    <!--<i class="fa fa-caret-down hidden-print " title="Created by <?php echo $charge['user_name']; ?>"></i>-->
                                    <div class="dropdown hidden-print">
                                                <span class="dropdown-toggle" type="button" data-toggle="dropdown">
                                                <!--    <span class="caret"></span> -->
                                                </span>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <li><a class="x-button delete_charge" title="Created by <?php echo $charge['user_name']; ?>"><?php echo l('Delete', true); ?></a>
                                            </li>
                                            <li>
                                                <a class="folios_modal" href="#" data-toggle="modal" data-target="#move-charge-modal" class="update-charge-folio"><?php echo l('Move to another Folio', true); ?></a>
                                            </li>
                                        </ul>
                                    </div>
                                <?php
                                endif;
                                ?>
                            </td>
                        </tr>
                    <?php
                    endforeach;
                endif;
                ?>
                </tbody>
                <tfoot>
                <?php
                if ($menu_on === true):
                    ?>
                    <tr class="hidden-print">
                        <td>
                            <strong>
                                <?php echo l('add_new_row'); ?>
                            </strong>
                        </td>
                        <td>
                            <input id="row-description-input" class="input-field form-control" value="" placeholder="<?php echo l('Description', true); ?>"/>
                        </td>
                        <td>
                            <select id="row-customer-input" class="input-field form-control">
                                <option value="0"><?php echo l('select').' '.l('paying').' '.l('customer'); ?></option>
                                <?php
                                foreach ($customers as $customer):
                                    if (isset($customer['customer_id'])):
                                        ?>
                                        <option value="<?php echo $customer['customer_id'];?>">
                                            <?php echo $customer['customer_name']; ?>
                                        </option>
                                    <?php
                                    endif;
                                endforeach;
                                ?>
                            </select>
                        </td>
                        <td>
                            <select id="row-type-input" class="input-field form-control">
                                <option value="0"><?php echo l('select').' '.l('charge_type'); ?></option>
                                <?php
                                foreach ($charge_types as $charge_type):
                                    ?>
                                    <option is_room_charge_type="<?=$charge_type['is_room_charge_type'];?>" value="<?php echo $charge_type['id'];?>" >
                                        <?php echo $charge_type['name']; ?>
                                    </option>
                                <?php
                                endforeach;
                                ?>
                            </select>
                        </td>
                        <td class="hidden" id="pay-period-td">
                            <select class="input-field form-control">
                                <option value="0"><?php echo l('Nightly', true); ?></option>
                                <option value="1"><?php echo l('Weekly',true).' '.l($this->default_room_singular).' '.l('Charge',true) ; ?></option>
                                <option value="2"><?php echo l('Monthly',true).' '.l($this->default_room_singular).' '.l('Charge',true) ; ?></option>
                                <option value="3"><?php echo l('One Time Charge', true); ?></option>
                            </select>
                        </td>
                        <td>
                            <input id="row-rate-input" class="input-field form-control text-right" value="" placeholder="<?php echo l('Amount', true); ?>"/>
                        </td>
                        <td class="td-border-right text-right" colspan="3">
                            <button
                                    id="button-add-charge"
                                    class="btn btn-success form-control"
                                    style="width:200px;"
                                    <?php echo $click_desible; ?>
                            >
                                <?php echo l('add').' '.l('charge'); ?>
                            </button>
                        </td>
                    </tr>
                <?php
                endif;
                ?>
                <tr>
                    <td colspan="8" class="h4 text-muted">
                        <div class="col-md-12 charges_div_spacing">
                            <div class="col-xs-6">
                            </div>
                            <div class="col-xs-4 text-right smaller_fonts charges_text_spacing">
                                <?php echo l('subtotal'); ?>:
                            </div>
                            <div class="col-xs-2 text-right smaller_fonts" id="subtotal">
                            </div>
                        </div>

                        <div class="col-md-12 charges_div_spacing">
                            <div class="col-xs-6">
                               
                                <button id="button-save-invoice" class="hidden-print btn btn-primary form-control" data-loading-text="Loading..." style ="display:none;width: 200px;"><?php echo l('save_changes'); ?></button>
                            </div>
                            <div class="col-xs-4 text-right smaller_fonts charges_text_spacing">
                                <?php echo l('taxes'); ?>:
                            </div>
                            <div class="col-xs-2 text-right smaller_fonts" id="total-taxes" >
                            </div>
                        </div>
                        <div class="col-md-12 charges_div_spacing">
                            <div class="col-xs-6">
                            </div>
                            <div class="col-xs-4 text-right smaller_fonts charges_text_spacing">
                                <?php echo l('charge').' '.l('total'); ?>:
                            </div>
                            <div class="col-xs-2 text-right smaller_fonts" id="total-charge">
                            </div>

                        </div>
                    </td>
                </tr>
                </tfoot>
            </table>
            </div>
        </div>
        <!-- /.panel -->

        <div class="panel panel-default">
            <div class="h2 text-left payment_div">
                <?php echo l('payments'); ?>
            </div>
            <div class="table-responsive">
            <table id="payment-table" class="table table-hover" >
                <thead>
                <tr>
                    <th class="text-left"><?php echo l('date'); ?></th>
                    <th class="text-left"><?php echo l('description'); ?></th>
                    <th class="text-left"><?php echo l('paid_by'); ?></th>
                    <th class="text-left"><?php echo l('payment_type'); ?></th>
                    <th class="text-left"><?php echo l('Status'); ?></th>
                    <th class="text-right"><?php echo l('amount'); ?></th>
                    <th class="delete-td"></th> <!-- for x button -->
                </tr>
                </thead>

                <tbody>
                <?php


                if (isset($payments)):
                    
                    foreach ($payments as $key => $payment):

                        ?>
                        <tr class="payment_row"
                            payment_id="<?php echo $payment["payment_id"]; ?>" id="<?php echo $payment["payment_id"]; ?>"
                            data-is_gateway="<?php if ($payment['payment_gateway_used']): ?> 1 <?php else: ?> 0 <?php endif; ?>"
                            data-pay-type="<?= ($payment['is_captured'] == 0) ? 'Authorized' : (($payment['payment_status'] == 'charge') ? 'Catured' : 'Refunded') ?>" data-remaining-amount="<?php echo ($payment['payment_status'] == 'charge' || $payment['payment_status'] == NULL) ? $payment['remaining_amount'] : ''; ?>"
                        >
                            <td>
                                <?php echo get_local_formatted_date($payment['selling_date']); ?>
                            </td>

                            <td>
                                <?php echo $payment['description'] ? $payment['description'].'<br/>' : '';?>

                                <?php if ($payment['payment_gateway_used'] && !$read_only): ?>
                                    <?php
                                    if($payment['payment_status'] == 'partial'){
                                        if (strlen($payment['gateway_charge_id'] ) > 30){
                                            $charge_id = substr($payment['gateway_charge_id'], 0, 20) . '...';
                                            printf(
                                                    '%s %s',
                                                    'Partial Refund ID:',
                                                    $charge_id
                                                ); 
                                        }else{
                                            printf(
                                                    '%s %s',
                                                    'Partial Refund ID:',
                                                    $payment['gateway_charge_id']
                                                ); 
                                        }
                                        // printf(
                                        //     '%s %s',
                                        //     'Partial Refund ID:',
                                        //     $payment['gateway_charge_id']
                                        // );
                                    } elseif($payment['payment_status'] == 'void'){
                                        if (strlen($payment['gateway_charge_id'] ) > 30){
                                            $charge_id = substr($payment['gateway_charge_id'], 0, 20) . '...';
                                            printf(
                                                '%s %s',
                                                $payment['amount'] > 0 && $charge_id ? 'Void ID:' : ($charge_id ? 'Void ID:' : ''),
                                                $charge_id
                                            ); 
                                        }else{
                                            printf(
                                                '%s %s',
                                                $payment['amount'] > 0 && $payment['gateway_charge_id'] ? 'Void ID:' : ($payment['gateway_charge_id'] ? 'Void ID:' : ''),
                                                $payment['gateway_charge_id']
                                            ); 
                                        }
                                    } else{
                                        if (strlen($payment['gateway_charge_id'] ) > 30){
                                            $charge_id = substr($payment['gateway_charge_id'], 0, 20) . '...';
                                            printf(
                                                '%s %s',
                                                $payment['amount'] > 0 && $charge_id ? 'Charge ID:' : ($charge_id ? ($payment['selected_gateway'] = 'nestpay' || $payment['selected_gateway'] == 'nestpaymkd' || $payment['selected_gateway'] == 'nestpayalb' || $payment['selected_gateway'] == 'nestpaysrb') ? " " :'Refund ID:' : ''),
                                                $charge_id
                                            ); 
                                        }else{
                                            printf(
                                                '%s %s',
                                                $payment['amount'] > 0 && $payment['gateway_charge_id'] ? 'Charge ID:' : ($payment['gateway_charge_id'] ?($payment['selected_gateway'] = 'nestpay' || $payment['selected_gateway'] == 'nestpaymkd' || $payment['selected_gateway'] == 'nestpayalb' || $payment['selected_gateway'] == 'nestpaysrb') ? " ": 'Refund ID:' : ''),
                                                $payment['gateway_charge_id']
                                            ); 
                                        }
                                        // printf(
                                        //     '%s %s',
                                        //     $payment['amount'] > 0 && $payment['gateway_charge_id'] ? 'Charge ID:' : ($payment['gateway_charge_id'] ? 'Refund ID:' : ''),
                                        //     $payment['gateway_charge_id']
                                        // );
                                    }
                                    ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                echo $payment['customer_name'];
                                ?>
                            </td>
                            <td>
                                <?php
                                /*
                                if ($payment['payment_gateway_used']):
                                    echo sprintf('%s', PaymentGateway::getGatewayName($payment['payment_gateway_used']));
                                else:
                                */
                                echo $payment['payment_type'];
                                /*
                                endif;
                                */
                                ?>
                            </td>
                            <td class="capture-td">
                                <?php if(isset($payment['payment_gateway_used'])) { ?>
                                    <?php if($payment['is_captured']==0 && $company['manual_payment_capture']==1){ ?>
                                        <span><?php 
                                         if($payment['payment_status'] == 'refund' && $payment['is_captured'] == 0){
                                             echo 'Canceled';
                                         }
                                        elseif($payment['payment_status'] == 'void' && $payment['is_captured'] == 0){
                                           echo 'Voided';
                                        }else{
                                            echo 'Authorized';
                                        }
                                        ?>
                                    
                                    </span>
                                        <?php if($payment['is_captured']==0 && $payment['read_only']==1) { ?>
                                            <div class="payment_status_buttons">
                                                <button class="btn btn-primary delete-payment not-allowed" data-toggle="tooltip" title="Refund" disabled><i class="fa fa-reply" aria-hidden="true"></i></button>
                                                <button class="btn btn-success capture-payment-button hidden-print not-allowed" disabled>
                                                    <i class="fa fa-credit-card" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        <?php } else { ?>
                                            <div class="payment_status_buttons">
                                                <?php 
                                                if($payment['payment_type'] == 'nexio'){ ?>
                                                    <button class="btn btn-danger void-payment" data-toggle="tooltip" title="void">
                                                        <i class="fa fa-ban" aria-hidden="true"></i></button>

                                                <?php }else{ ?>
                                                    <button class="btn btn-primary delete-payment" data-toggle="tooltip" title="Refund"><i class="fa fa-reply" aria-hidden="true"></i></button>

                                               <?php }  ?>
                                                <button class="btn btn-success  capture-payment-modal hidden-print" data-toggle="tooltip" title="Capture"  data-capture-payment-type="<?= $payment['payment_type'] ?>" data-capture-authorize-id="<?= $payment['gateway_charge_id'] ?>"  data-customer-id="<?= $booking_customer['customer_id'] ?>" data-booking-id="<?= $this->uri->segment(3) ?>">
                                                    <i class="fa fa-credit-card" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        <?php }?>
                                        <span class="visible-print-block"><?php echo l('Authorized', true); ?></span>
                                    <?php } else { ?>
                                        <span><?php echo ($payment['payment_status'] == 'charge') ? l('Captured', true) : ($payment['payment_status'] == 'payment_link' ? l('Pending', true) : l('Refunded', true)); ?></span>
                                        <div class="payment_status_buttons">
                                            <?php if (!$payment['read_only']){ if($payment['is_captured']){?>
                                                <button class="btn btn-primary hidden-print delete-payment" data-toggle="tooltip" title="Refund"  title="Created by <?php echo $payment['user_name']; ?>">
                                                    <i class="fa fa-reply" aria-hidden="true"></i>
                                                </button>
                                                <button type="button" class="btn btn-success capture-payment-button hidden-print not-allowed" disabled>
                                                    <i class="fa fa-credit-card" aria-hidden="true"></i>
                                                </button>
                                            <?php } else if($payment['is_captured'] == 0 && ($payment['selected_gateway'] == 'nestpay' || $payment['selected_gateway'] == 'nestpaymkd' || $payment['selected_gateway'] == 'nestpayalb' || $payment['selected_gateway'] == 'nestpaysrb')) { ?> 
                                                  <button class="btn btn-primary hidden-print delete-payment" data-toggle="tooltip" title="Refund"  title="Created by <?php echo $payment['user_name']; ?>">
                                                    <i class="fa fa-reply" aria-hidden="true"></i>
                                                </button>
                                                <button type="button" class="btn btn-success capture-payment-button hidden-print not-allowed" disabled>
                                                    <i class="fa fa-credit-card" aria-hidden="true"></i>
                                                </button>
                                            <?php } else if($menu_on === true && $payment['payment_link_id']) { ?>
                                                <button type="button" class="btn btn-info hidden-print verify_payment" data-payment_link_id="<?php echo $payment['payment_link_id']; ?>" data-payment_id="<?php echo $payment['payment_id']; ?>">
                                                    Verify
                                                </button>
                                            <?php } ?>
                                            <?php } else { ?>
                                                <button class="btn btn-primary delete-payment hidden-print not-allowed" data-toggle="tooltip" title="Refund" disabled>
                                                    <i class="fa fa-reply" aria-hidden="true"></i>
                                                </button>
                                                <button type="button" class="btn btn-success capture-payment-button hidden-print not-allowed" data-toggle="tooltip" title="Capture" disabled>
                                                    <i class="fa fa-credit-card" aria-hidden="true"></i>
                                                </button>
                                            <?php } ?>
                                        </div>
                                    <?php } } ?>
                            </td>
                            <td class="text-right payment">
                                <?php
                                $rate = (float)$payment['amount'];
                                echo number_format($rate, 2, ".", ",");
                                ?>
                            </td>
                            <td class="center delete-td" width="60">
                                <?php if (!$payment['read_only']): ?>
                                    <div class="dropdown pull-right delete-menu" style="display:none">
                                        <button aria-expanded="true" data-toggle="dropdown" id="dropdownMenu1" type="button" class="btn btn-default btn-xs dropdown-toggle">
                                           <!--  <span class="caret"></span> -->
                                        </button>
                                        <ul aria-labelledby="dropdownMenu1" role="menu" class="dropdown-menu">
                                            <?php if($payment['is_captured'] == 1){ ?>
                                                <li role="presentation">
                                                    <a href="#" class="delete-payment" title="Created by <?php echo $payment['user_name']; ?>">
                                                        <?php if ($payment['payment_gateway_used']): echo l('refund'); ?>  <?php else: echo l('delete'); ?>  <?php endif; ?>
                                                    </a>
                                                </li>
                                            <?php } elseif($payment['is_captured'] == 0 && $payment['payment_link_id']){ ?>
                                                <li role="presentation">
                                                    <a href="javascript:" class="delete-payment-link-row" title="Created by <?php echo $payment['user_name']; ?>">
                                                        <?php echo l('delete', true); ?>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                            <li>
                                                <a class="folios_modal" href="#" data-toggle="modal" data-target="#move-charge-modal" class="update-charge-folio"><?php echo l('Move to another Folio', true); ?></a>
                                            </li>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php
                    endforeach;
                endif;
                ?>
                </tbody>
                <tfoot>
                <?php
                if ($menu_on === true):
                    ?>
                    <tr class="hidden-print">
                        <td colspan="6" class="text-right add_pay_btn">
                            <button
                                    class="btn btn-success form-control payment-modal-button"
                                    data-toggle="modal"
                                    data-target="#add-payment-modal"
                                    style="width:200px;"
                            >
                                <?php echo l('add').' '.l('payment'); ?>
                            </button>
                        </td>
                    </tr>
                <?php
                endif;
                ?>
                <tr>
                    <td colspan="6">
                        <div class="h4 text-muted">
                            <div class="col-xs-12 payment_total">
                                <div class="col-xs-6">
                                </div>
                                <div class="col-xs-4 text-right smaller_fonts payments_text_spacing">
                                    <?php echo l('payment').' '.l('total'); ?>:
                                </div>
                                <div class="col-xs-2 text-right smaller_fonts" id="payment_total">
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                </tfoot>
            </table>
            </div>

        </div> <!-- /.panel -->
        <div class="h2 text-muted" style="max-width: 100%; padding: 10px;">
            <div class="amount_due">
                <div class="text-right smaller_fonts payments_text_spacing">
                    <?php echo l('amount', true).' '.l('due', true); ?>:
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <span class="text-right smaller_fonts" id="amount_due">
                    </span>  
                   <span class="text-right smaller_fonts currency_symbol" id="currency_symbol">
                    <?php echo $currency_symbol;?>
                    </span>
                </div>
            </div>
            <div>
                <div class="text-right paid_in_full" id="paid_in_full">
                </div>
            </div>
        </div>

    </div> <!-- /. container -->
    <?php
    if ($menu_on === true):
        ?>
        <div class="md-col-12 hidden-print">
            <div class="col-md-3">
            </div>

            <div class="jumbotron col-md-6 col-xs-12">
                <h4><?php echo l('invoice').' '.l('log'); ?></h4>
                <div class="table-responsive">
                <table class="table">
                    <?php
                    foreach ($invoice_log as $r):
                        ?>
                        <tr>
                            <td class="text-left">
                                <?php
                                $log_date = new DateTime($r->date_time, new DateTimeZone('UTC'));
                                $log_date->setTimeZone(new DateTimeZone($company['time_zone']));
                                echo $log_date->format('y-m-d h:i A');
                                ?>
                            </td>
                            <td class="text-left">
                                <?php echo $r->log; ?>
                            </td>
                        </tr>
                    <?php
                    endforeach;
                    ?>

                </table>
                </div>
            </div>  <!-- /. container.. -->

            <div class="col-md-3">
            </div>
        </div>
    <?php
    endif;
    ?>
</div></div>