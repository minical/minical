<?php
// Load Language
$language = $this->session->userdata('language');
$this->lang->load('booking', $language);

$flag = 1;
$permissions = $this->session->userdata('permissions');
if ($permissions && in_array('bookings_view_only', $permissions) && !(in_array('access_to_bookings', $permissions))) {
    $flag = 0;
}
?>

<input type="hidden" name="flag" value="<?php echo $flag; ?>" >
<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo l('add_payment'); ?></h4>
            </div>
            <div class="modal-body form-horizontal">
                <div class="form-group">
                    <label for="payment_date" class="col-sm-4 control-label"><?php echo l('payment_date'); ?></label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="payment_date" placeholder="<?php echo l('payment_date', true); ?>" value="<?php echo $company['selling_date']; ?>">
                    </div>
                </div>

                <!-- Amount is manually entered only when user is paying for 1 booking -->
                <div class="form-group">
                    <label for="payment_amount" class="col-sm-4 control-label">
                        <?php echo l('amount'); ?>
                    </label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="payment_amount" >
                        <p class="help-block" id="paymentNotice"></p>
                        <p class="help-block" style="color:#ff0000;" id="overPaymentNotice"></p>
                    </div>

                </div>
                <div class="form-group">
                    <label for="pay_for" class="col-sm-4 control-label"><?php echo l('method'); ?>: </label>
                    <div class="col-sm-8">
                        <select name="payment_type_id" class="input-field form-control">
                            <?php
                            foreach ($payment_types as $payment_type):
                                ?>
                                <option value="<?php echo $payment_type->payment_type_id; ?>">
                                    <?php echo $payment_type->payment_type; ?>
                                </option>
                                <?php
                            endforeach;
                            ?>
                        </select>

                    </div>
                </div>
                
                <div class="form-group">
                    <label for="payment_distribution" class="col-sm-4 control-label"><?php echo l('payment_distribution'); ?></label>
                    <div class="col-sm-8">
                        <select name="payment_distribution" class="input-field form-control">
                            <option value="No"><?php echo l('settle_folio_balance'); ?></option>
                            <option value="Yes"><?php echo l('distribute_payment_evenly'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="form-group" id="description-form-group">
                    <label for="amount" class="col-sm-4 control-label">
                        <?php echo l('comment'); ?> <small class="text-muted">(<?php echo l('optional'); ?>)</small>
                    </label>
                    <div class="col-sm-8">
                        <textarea class="form-control" name="description" row=2></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
				<input type="hidden"  id="manual_payment_capture" value="<?=$company['manual_payment_capture'];?>">
				<?php if($company['manual_payment_capture'] != 1){ ?>
					<button type="button" class="btn btn-primary addPaymentButton hidden" id="addPaymentButton"><?php echo l('add_payments'); ?></button>

					<?php } else { ?>
					<button type="button" class="btn btn-primary addPaymentButton hidden" id="auth_and_capture"><?php echo l('Charge'); ?></button>
					<button type="button" class="btn btn-primary addPaymentButton hidden" id="authorize_only"><?php echo l('Pre-Authorize'); ?></button>
					<?php } ?>
					
                <button type="button" class="btn btn-primary addPaymentButton" id="add_payment_normal"><?php echo l('add_payments'); ?></button>
                <button type="button" class="btn btn-light" data-dismiss="modal"><?php echo l('close'); ?></button>
            </div>
        </div>
    </div>
</div>






<div id="printable-container">


<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-power text-success"></i>
            </div>
            <div>
                 <span class="hidden-print token"  id='<?php echo $customer_detail['customer_id']; ?>' >
                    <div class="token-label">
                        <?php echo $customer_detail['customer_name']; ?>
                    </div>
                </span>
            </div>
        </div>
        <?php if(check_active_extensions('customer_statements', $this->company_id)){
             ?>
            <div class="page-title-actions">
                <a href="<?php echo base_url().'customer/statements/'.$this->uri->segment(3); ?>" id="add-room-button" class="btn btn-primary"><?php echo l('Statements', true); ?></a>
            </div>
        <?php } ?>
    </div>
</div>


<div class="main-card mb-3 card">
    <div class="card-body">

    <div class="panel panel-default hidden-print ">
        <div class="panel-body">
            <div id="search-filter-container" class="form-inline customer-booking-history m-1 ">
                <form method="get" action="
                <?php
                echo base_url() . "customer/history/" . $customer_detail['customer_id'];
                ?>
                      " autocomplete="off" class="form-wep">				

                    <span class="span-fix-wep"><?php echo l('show'); ?></span>
                    <select class="form-control m-1" name="status">
                        <option value="all" <?php if ($status == 'all') echo "selected"; ?> ><?php echo l('all', true); ?></option>							
                        <option value="reservation" <?php if ($status == 'reservation') echo "selected"; ?> ><?php echo l('reservations', true); ?></option>								
                        <option value="checkin" <?php if ($status == 'checkin') echo "selected"; ?> ><?php echo l('in-house', true); ?></option>
                        <option value="checkout" <?php if ($status == 'checkout') echo "selected"; ?> ><?php echo l('checked-out', true); ?></option>								
                        <option value="cancelled" <?php if ($status == 'cancelled') echo "selected"; ?> ><?php echo l('cancelled', true); ?></option>								
                    </select>

                    <span class="span-fix-wep"><?php echo l('bookings_between'); ?></span>
                    <input type="text" name="start-date" size="12" class="form-control m-1" value="<?php echo isset($start_date) ? $start_date : ''; ?>" placeholder="<?php echo l('from date', true); ?>"/>
                    <span class="span-fix-wep"> <?php echo l('and'); ?></span>
                    <input type="text" name="end-date" size="13" class="form-control m-1" value="<?php echo isset($end_date) ? $end_date : ''; ?>" 
                           placeholder="<?php echo l('to date', true); ?>" 
                           onchange="if (this.value && $('input[name=\'start-date\']').val() && new Date(this.value) < new Date($('input[name=\'start-date\']').val())) {
                                       alert('to date can\'t be less than from date.');
                                       this.value = $('input[name=\'start-date\']').val();
                                   }"/>

                    <span class="span-fix-wep"><?php echo l('that_are'); ?></span>
                    <select class="form-control m-1"  name="show_paid">
                        <option value="all"><?php echo l('paid or unpaid', true); ?></option>
                        <option value="paid_only" <?php if ($show_paid == 'paid_only') echo "selected"; ?> ><?php echo l('paid', true); ?></option>
                        <option value="unpaid_only" <?php if ($show_paid == 'unpaid_only') echo "selected"; ?> ><?php echo l('unpaid', true); ?></option>								
                    </select>
                    <span class="span-fix-wep"><?php echo l('in_group'); ?></span>
                    <select class="form-control m-1" name="groups">
                        <option value="all"><?php echo l('all', true); ?></option>
                        <option value="unassigned" <?php if ($group == 'unassigned') {
                    echo 'selected';
                } ?> ><?php echo l('none', true); ?></option>
                        <?php for ($k = 0; $k < count($groups); $k++) { ?>
                            <option value="<?php echo $groups[$k]['group_id']; ?>" <?php if ($group == $groups[$k]['group_id']) {
                            echo 'selected';
                        } ?> ><?php echo $groups[$k]['group_name']; ?></option>
<?php } ?>
                    </select>
                    <span class="span-fix-wep"><?php echo l('in_statement'); ?></span>
                    <select class="form-control m-1" name="in_statement">
                        <option value="either"><?php echo l('either', true); ?></option>
                        <option value="no" <?php if ($in_statement == 'no') echo "selected"; ?>><?php echo l('no', true); ?></option>
                        <option value="yes" <?php if ($in_statement == 'yes') echo "selected"; ?>><?php echo l('yes', true); ?></option>
                    </select>
                    <span class="span-fix-wep"><?php echo l('with_group_id'); ?></span>
                    <input type="text" name="linked_group_id" size="12" class="form-control m-1" value="<?php echo isset($linked_group_id) ? $linked_group_id : ''; ?>" /> 
                    <input type="hidden" name="staying_guest_name" size="12" class="form-control m-1" placeholder="<?php echo l('search customer', true); ?>" value="<?php echo $staying_guest_name; ?>" /> 
                    <button type='submit' name='submit' value='search' class="btn btn-light m-1" id="click-statement">
                        <span class="glyphicon glyphicon-search m-1" aria-hidden="true"></span>
                        <?php echo l('filter'); ?>
                    </button>
                    <button type='reset' name='reset' value='<?php echo l('clear_filter'); ?>' class="btn btn-light m-1" id="clear-filter-btn"><?php echo l('clear_filter'); ?></button>
                    <div class="pull-right m-1 m-001">
                        <?php echo l('statement_date'); ?> <input type="text" name="statement_date" size="10" class="form-control" value="<?php if ($statement_date) {
    echo $statement_date;
} ?>" onchange="filterStatementDate()" placeholder="<?php echo l('Select Date', true); ?>"/>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="panel panel-default visible-print-block">
        <div class="panel-body">

            <div class="col-md-12 row invoice-header">
                <div class="col-xs-6">
                    <address>
                        <?php
                        echo "<strong>" . $company['name'] . "</strong><br/>";
                        echo ($company['address'] != "") ? l('Address', true).": " . $company['address'] . "<br/>" : '';
                        echo ($company['city'] != "") ? $company['city'] : '';
                        echo ($company['region'] != "") ? ", " . $company['region'] : '';
                        echo ($company['postal_code'] != "") ? " " . $company['postal_code'] : '';
                        echo "<br/>";
                        echo ($company['phone'] != "") ? l('Phone', true).": " . $company['phone'] . "<br/>" : '';
                        echo ($company['fax'] != "") ? l('Fax', true).": " . $company['fax'] . "<br/>" : '';
                        echo ($company['email'] != "") ? l('Email', true).": <span id='company-email'>" . $company['email'] . "</span><br/>" : '';
                        echo ($company['website'] != "") ? l('Website', true).": " . $company['website'] . "<br/>" : '';
                        echo $company['GST_number'];
                        ?>
                    </address>
                </div>
             
                <div class="col-xs-6 text-right">
                    <address>
                        <?php
                        echo "<strong>".l("Billed to:")."</strong> " . $customer_detail['customer_name'] . "<br/>";
                        echo ($customer_detail['address'] != "") ? l('Address', true).": " . $customer_detail['address'] . "<br/>" : '';
                        echo ($customer_detail['city'] != "") ? $customer_detail['city'] : '';
                        echo ($customer_detail['region'] != "") ? ", " . $customer_detail['region'] : '';
                        echo ($customer_detail['postal_code'] != "") ? ", " . $customer_detail['postal_code'] . "<br/>" : '';
                        echo ($customer_detail['phone'] != "") ? l('Phone', true).": " . $customer_detail['phone'] . "<br/>" : '';
                        echo ($customer_detail['fax'] != "") ? l('Fax', true).": " . $customer_detail['fax'] . "<br/>" : '';
                        echo ($customer_detail['email'] != "") ? l('Email', true).": <span id='customer-email'>" . $customer_detail['email'] . "</span><br/>" : '';
                        echo "<br/>";
                        ?>
                    </address>
                </div>
            </div> <!-- /.container -->

        </div>
    </div>
    <div class="panel panel-default hidden-print">
        <div class="panel-body">
            <div class="pull-left">
            <button id='<?php echo $customer_detail['customer_id']; ?>' class="customer-profile btn btn-light btn-sm colors-wep">
                <?php echo l('edit_profile'); ?>
            </button>
            <button class="btn btn-success btn-sm colors-wep" id="openPaymentModal" ><?php echo l('add_payments'); ?></button>
            <button class="btn btn-primary btn-sm colors-wep" id="print-statement-button"><?php echo l('print_statement'); ?></button>
            
            <?php if(check_active_extensions('customer_statements', $this->company_id)){
            ?>
                <button class="btn btn-primary btn-sm colors-wep" id="create-invoice-button" class="create-invoice"><?php echo l('create_statement'); ?></button>
            <?php } ?>
            
            <button class="btn btn-danger btn-sm colors-wep" onclick="clearAllGroups()" class="clear-all-groups"><?php echo l('clear_all_groups'); ?></button>
            <span id="guest-name-filter">
                <input type="text" style="width:auto;display: inline-block;height:30px" id="search-guest-name" size="18" class="form-control colors-wep" placeholder="<?php echo l('Search Keywords', true); ?>" value="<?php echo $staying_guest_name; ?>" />
                <button class="btn btn-light btn-sm colors-wep" id="search-customer-btn" ><?php echo l('search_customers'); ?></button>
            </span>
        </div>
            <div class="pull-right mt-2 mt-004">
                <span class="bttn-gapp"><?php echo l('legend'); ?>:</span>
                <span class='legend-color state0 bttn-gapp'><?php echo l('Reservation', true); ?></span>
                <span class='legend-color state1 bttn-gapp'><?php echo l('Checked in', true); ?></span>
                <span class='legend-color state2 bttn-gapp'><?php echo l('Checked out', true); ?></span>
                <span class='legend-color state4 bttn-gapp'><?php echo l('Cancelled', true); ?></span>
            </div>
        </div>
    </div>
    <div class="table-responsive">
    <table class="table table-hover room-invoice">
        <tr>
            <th class="hidden-print">
                <input type="checkbox" id="check-all" class="no-margin text-center booking-checkbox">
            </th>
            <th><?php echo l('id'); ?></th>
            <th><a href="<?php echo base_url() . "customer/history/" . $customer_detail['customer_id'] . "/room_name/" . ($order == 'desc' ? "asc" : "desc"); ?><?= $_SERVER['QUERY_STRING'] ? "?" . $_SERVER['QUERY_STRING'] : ""; ?>">
                    <?php
                    if ($this->session->userdata('property_type') == 0) {
                        echo l($this->default_room_singular);
                    } else {
                        echo l('bed');
                    }
                    ?>
                </a></th>	
            <th><a href="<?php echo base_url() . "customer/history/" . $customer_detail['customer_id'] . "/guest_name/" . ($order == 'desc' ? "asc" : "desc"); ?><?= $_SERVER['QUERY_STRING'] ? "?" . $_SERVER['QUERY_STRING'] : ""; ?>"><?php echo l('staying_guests'); ?></a></th>
            <th><a href="<?php echo base_url() . "customer/history/" . $customer_detail['customer_id'] . "/check_in_date/" . ($order == 'desc' ? "asc" : "desc"); ?><?= $_SERVER['QUERY_STRING'] ? "?" . $_SERVER['QUERY_STRING'] : ""; ?>"><?php echo l('start_date'); ?></a></th>
            <th><a href="<?php echo base_url() . "customer/history/" . $customer_detail['customer_id'] . "/check_out_date/" . ($order == 'desc' ? "asc" : "desc"); ?><?= $_SERVER['QUERY_STRING'] ? "?" . $_SERVER['QUERY_STRING'] : ""; ?>"><?php echo l('end_date'); ?></a></th>			
            <th class="text-right"><?php echo l('Sub-total', true); ?></th>
            <th class="text-right"><?php echo l('Tax', true); ?></th>
            <th class="text-right"><a href="<?php echo base_url() . "customer/history/" . $customer_detail['customer_id'] . "/charge_total/" . ($order == 'desc' ? "asc" : "desc"); ?><?= $_SERVER['QUERY_STRING'] ? "?" . $_SERVER['QUERY_STRING'] : ""; ?>"><?php echo l('charge'); ?></a></th>					
            <th class="text-right"><a href="<?php echo base_url() . "customer/history/" . $customer_detail['customer_id'] . "/payment_total/" . ($order == 'desc' ? "asc" : "desc"); ?><?= $_SERVER['QUERY_STRING'] ? "?" . $_SERVER['QUERY_STRING'] : ""; ?>"><?php echo l('payment'); ?></a></th>					
            <th class="text-right"><a href="<?php echo base_url() . "customer/history/" . $customer_detail['customer_id'] . "/balance/" . ($order == 'desc' ? "asc" : "desc"); ?><?= $_SERVER['QUERY_STRING'] ? "?" . $_SERVER['QUERY_STRING'] : ""; ?>"><?php echo l('balance'); ?></a></th>
            <th class="text-center"><?php echo l('group_id'); ?></th>
            <th class="text-center"><?php echo l('statement'); ?>#</th>
            <th class="hidden-print"><?php echo l('group'); ?></th></tr>
        <tbody>
            <?php
            $sub_total_sum = 0;
            $charge_total_sum = 0;
            $payment_total_sum = 0;
            $balance_sum = 0;
            if (isset($bookings_made_by_this_customer))
                foreach ($bookings_made_by_this_customer as $booking) :
                    if (isset($booking['booking_id'])):
                        $booking_id = $booking['booking_id'];
                        ?>
                        <tr class='booking <?php echo "state" . $booking['state']; ?>'  data-booking-id='<?php echo $booking['booking_id']; ?>' style="background-color: #<?php echo $booking['color']; ?>;">
                            <td id="td-check-box" class="hidden-print"><input type="checkbox" id="<?php echo $booking_id; ?>" class="booking-checkbox no-margin" name="<?php echo $booking_id; ?>"></td>
                            <td><?php echo $booking_id; ?></td>
                            <td><?php echo $booking['room_name'] ? $booking['room_name'] : 'Not Assigned'; ?></td>
                            <td>
                                <?php
                                echo $booking['guest_name'];
                                if ($booking['guest_count'] > 1) {
                                    echo " and " . ($booking['guest_count'] - 1) . " other(s)";
                                }
                                ?>
                            </td>
                            <td class="date-td"><?= ($this->enable_hourly_booking == 1 ? date('Y-m-d h:i A', strtotime($booking['check_in_date'])) : ($booking['check_in_date'] ? date('Y-m-d', strtotime($booking['check_in_date'])) : '')); ?></td>

                            <td class="date-td"><?= ($this->enable_hourly_booking == 1 ? date('Y-m-d h:i A', strtotime($booking['check_out_date'])) : ($booking['check_out_date'] ? date('Y-m-d', strtotime($booking['check_out_date'])) : '')); ?></td>
                            <td class="text-right sub-charge">
                                <?php
                                $sub_total = $booking['charge_total'];
                                if (isset($booking['taxes'])) {
                                    foreach ($booking['taxes'] as $tax) {
                                        $sub_total -= (float) $tax['tax_total'];
                                    }
                                }
                                $sub_total_sum += $sub_total;
                                echo number_format($sub_total, 2, ".", ",");
                                ?>

                            </td>
                            <td class="text-right td-tax">
                                <?php
                                // calculation is done in invoice temporarily
                                // eventually migrate it to controller

                                if (isset($booking['taxes'])) {
                                    foreach ($booking['taxes'] as $tax) {
                                        if (!isset($tax_total[$tax['tax_type']])) {
                                            $tax_total[$tax['tax_type']] = 0;
                                        }
                                        $tax_amount = (float) $tax['tax_total'];
                                        $tax_total[$tax['tax_type']] += $tax_amount;
                                        echo '<small> <div class="tax">'
                                        . '<span class="tax-type">' . $tax['tax_type'] . ' </span>'
                                        . '<span class="tax-amount">' . number_format($tax_amount, 2, ".", ",") . '</span>'
                                        . '</div> </small>';
                                    }
                                } else {
                                    echo '0.00';
                                }
                                ?>
                            </td>
                            <td class="text-right booking-charge">
                                <?php
                                echo number_format($booking['charge_total'], 2, ".", ",");
                                $charge_total_sum += $booking['charge_total'];
                                ?>
                            </td>
                            <td class="text-right booking-payment">
                                <?php
                                echo number_format($booking['payment_total'], 2, ".", ",");
                                $payment_total_sum += $booking['payment_total'];
                                ?>
                            </td>
                            <?php
                                $balance = $booking['charge_total'] - $booking['payment_total'];
                            ?>
                            <td class="text-right booking-balance" data-actual-balance="<?=$balance;?>">
                                <?php
                                $formated_balance = number_format($balance, 2, ".", ",");
                                echo $formated_balance == "-0.00" ? "0.00" : $formated_balance;
                                $balance_sum += $balance;
                                ?>
                            </td>
                            <td class="text-center">
                                <b><?= $booking['is_group_booking']; ?></b>
                            </td>
                            <td class="text-center statement-box">
                                <?php 
                                    if($booking['booking_statements']){
                                        $booking_statements_ar = explode(",", $booking['booking_statements']);
                                        if(count($booking_statements_ar) > 0)
                                        {          
                                            $statements_links = array();
                                            foreach($booking_statements_ar as $booking_statement){
                                                $booking_statement_ar = explode("_", $booking_statement);
                                                $statements_links[] = '<a href="'.base_url().'customer/statements/'.$customer_detail['customer_id'].'/'.$booking_statement_ar[0].'"/>'.$booking_statement_ar[1].'</a>';
                                            }
                                            echo implode(", ", $statements_links);
                                        }
                                    }                            
                                ?>
                            </td>
                            <td class="td-group-box hidden-print" style="width: 96px;">
                                    <?php $selected_group = $this->Group_model->get_booking_group($booking_id); ?>
                                <select onchange="createBookingGroup(this)" booking_id="<?php echo $booking_id; ?>" class="form-control">
                                    <option value="unassigned"><?php echo l('none', true); ?></option>
                                    <?php for ($k = 0; $k < count($groups); $k++) { ?>
                                        <option value="<?php echo $groups[$k]['group_id']; ?>" <?php if (isset($selected_group[0]['group_id']) && ($groups[$k]['group_id'] == $selected_group[0]['group_id'])) {
                            echo 'selected';
                        } ?>><?php echo $groups[$k]['group_name']; ?></option>
                        <?php } ?>
                                </select>
                            </td>
                        </tr>
            <?php
        endif;
    endforeach;
?>
        </tbody>
        <tfoot>
            <tr class="">
                <td class="hidden-print"></td>
                <td colspan="5" class='text-right'>
                    <?php echo l('total'); ?>:
                </td>
                <td class="text-right" id="sub-total">
                    <?php echo number_format($sub_total_sum, 2, ".", ","); ?>
                </td>
                <td class="text-right" id="tax-total">
                    <?php
                    if (isset($tax_total)) {
                        foreach ($tax_total as $tax_type => $tax_amount) {
                            echo '<small> <div class="tax">'
                            . '<span class="tax-type">' . $tax_type . ' </span>'
                            . '<span class="tax-amount">' . number_format($tax_amount, 2, ".", ",") . '</span>'
                            . '</div> </small>';
                        }
                    } else {
                        echo '0.00';
                    }
                    ?>
                </td>
                <td class="text-right" id="charge-total">
<?php echo number_format($charge_total_sum, 2, ".", ","); ?>
                </td>
                <td class="text-right" id="payment-total">
<?php echo number_format($payment_total_sum, 2, ".", ","); ?>
                </td>
                <td class="text-right" id="balance-total">
<?php echo number_format($balance_sum, 2, ".", ","); ?>
                </td>
            </tr>	
        </tfoot>
    </table>
                </div>

</div>



<div class="panel panel-default hidden-print m-4" >
    <div class="panel-body text-center h4 pagination-div">
<?php echo $this->pagination->create_links(); ?>
    </div>
</div>

<div class="hidden-print" style="padding:30px">
    <span class="bold"><?php echo l('booking_that_customer_not_reserve'); ?></span>
    <div class="table-responsive">
    <table class="table table-hover">
        <tr>
            <th><?php echo l('id'); ?></th>
            <th><?php echo l($this->default_room_singular); ?></th>
            <th><?php echo l('reserved_by'); ?></th>
            <th><?php echo l('checkin'); ?></th>
            <th><?php echo l('checkout'); ?></th>	
        </tr>
        <?php
        if (isset($bookings_that_this_customer_stayed_in))
            foreach ($bookings_that_this_customer_stayed_in as $booking) :
                if (isset($booking['booking_id'])):
                    $booking_id = $booking['booking_id'];
                    ?>
                    <tr class='booking_tr <?php echo "state" . $booking['state']; ?>'  name='<?php echo $booking['booking_id']; ?>'  style="background-color: #<?php echo $booking['color']; ?>;" onclick="" >
                        <td><?php echo $booking_id; ?></td>
                        <td><?php echo $booking['room_name']; ?></td>
                        <td><?php echo $booking['customer_name']; ?></td>
                        <td><?php echo isset($booking['charge_start_selling_date']) ? $booking['charge_start_selling_date'] : ""; ?></td>
                        <td><?php echo isset($booking['charge_end_selling_date']) ? min($booking['charge_end_selling_date'], $statement_date) : ""; ?></td>
                    </tr>
            <?php
        endif;
    endforeach;
?>
   </table></div></div></div></div>
<div class="modal fade" id="addPaymentsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo l('add_payment'); ?></h4>
            </div>
            <div class="modal-body form-horizontal">
                <div class="form-group">
                    <label for="payment_date" class="col-sm-4 control-label"><?php echo l('payment_date'); ?></label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="payment_date" placeholder="Payment Date" value="<?php echo $company['selling_date']; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="payment_amount" class="col-sm-4 control-label">
                        <?php echo l('amount'); ?>
                    </label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="payment_amount" readonly>
                        <p class="help-block" id="paymentNotice"></p>
                    </div>

                </div>
                <div class="form-group">
                    <label for="pay_for" class="col-sm-4 control-label"><?php echo l('method'); ?>: </label>
                    <div class="col-sm-8">
                        <select name="payment_type_id" class="input-field form-control">
                                <?php
                                foreach ($payment_types as $payment_type):
                                    ?>
                                <option value="<?php echo $payment_type->payment_type_id; ?>">
                                <?php echo $payment_type->payment_type; ?>
                                </option>
    <?php
endforeach;
?>
                        </select>

                    </div>
                </div>
                <div class="form-group" id="description-form-group">
                    <label for="amount" class="col-sm-4 control-label">
                        <?php echo l('comment'); ?> <small class="text-muted">(<?php echo l('optional'); ?>)</small>
                    </label>
                    <div class="col-sm-8">
                        <textarea class="form-control" name="description" row=2></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary addPaymentButton" id="addPaymentButton"><?php echo l('add_payments'); ?></button>
                <button type="button" class="btn btn-light" data-dismiss="modal"><?php echo l('close'); ?></button>
            </div>
        </div>
    </div>
</div>
<input id="customer_id" type="hidden" value="<?= $customer_detail['customer_id'] ?>"/>



