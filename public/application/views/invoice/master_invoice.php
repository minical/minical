<?php if (!$read_only): ?>
            <input type="hidden" class="text-right" id="booking_id" value="<?php echo $single_booking_id; ?>"/> 
			<input type="hidden" class="text-right" id="booking_id_for_group_confirmation_email" value="<?php echo $first_booking_id; ?>"/>   
			<div class="modal fade"  id="add-payment-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title"><?php echo l('add').' '.l('payment'); ?></h4>
						</div>
						<div class="modal-body form-horizontal">
							<div class="form-group">
								<label for="payment_date" class="col-sm-4 control-label"><?php echo l('payment').' '.l('date'); ?></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" name="payment_date" placeholder="Payment Date" value="<?php echo get_local_formatted_date($company['selling_date']); ?>">
								</div>
							</div>
							<?php if(!$is_group_invoice): ?>
								<div class="form-group" id="option-to-add-multiple-payments">
									<label for="pay_for" class="col-sm-4 control-label"><?php echo l('pay_for'); ?></label>
									<div class="col-sm-8">
										<select class="form-control" name="pay_for">
											<option value="this_invoice_only"><?php echo l('this_invoice_only'); ?></option>
											<option value="all_bookings" name="all_bookings_option"></option>
										</select>
									</div>
								</div>
							<?php endif; ?>
							<!-- Amount is manually entered only when user is paying for 1 booking -->
							<div class="form-group">
								<label for="payment_amount" class="col-sm-4 control-label">
									<?php echo l('amount'); ?>
								</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" name="payment_amount">
								</div>
							</div>

							<div class="form-group">
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
			                	if (count(array_filter($customers)) > 0 ): 
			                ?>
									<div class="form-group">
										<label for="pay_for" class="col-sm-4 control-label"><?php echo l('paid_by'); ?> <small class="text-muted">(<?php echo l('optional'); ?>)</small></label>
										<div class="col-sm-8">
											<select name="customer_id" class="input-field form-control paid-by-customers">
												
												<?php $cust_group = '';
                                                    $available_gateway = '';
                                                    
													foreach ($customers as $customer):
                                                        if ($customer['customer_id'] == $selected_customer_id) {
                                                            $selected_customer = 'selected';
                                                        } else {
                                                            $selected_customer = '';
                                                        }
                                                        if ($customer['cc_expiry_year'] || $customer['cc_cvc_encrypted'] || $customer['customer_meta_token']) {
                                                            $is_gateway_available = 'true';
                                                            if($customer['cc_cvc_encrypted'])
                                                                $available_gateway = $selected_payment_gateway;
                                                            else
                                                                $available_gateway = 'tokenex';
                                                        } else {
                                                            $is_gateway_available = 'false';
                                                        }
												?>
												<?php if(isset($customer['cust_type']) && $customer['cust_type'] && $customer['cust_type'] != $cust_group):
														$cust_group = $customer['cust_type'];
													?>
													<optgroup label="<?php echo $cust_group; ?>">
													<?php endif; ?>
                                                		<option data-available-gateway="<?=$available_gateway;?>" is-gateway-available="<?=$is_gateway_available;?>" <?php echo $selected_customer; ?> value="<?php echo $customer['customer_id']; ?>">
															<?php echo $customer['customer_name']; ?>
														</option>
													<?php if(isset($customer['cust_type']) && $customer['cust_type'] && $customer['cust_type'] != $cust_group): ?>
													</optgroup>
												
												<?php endif; endforeach;
												?>
											</select>

										</div>
									</div>
							<?php
								endif;
							?>
							
							<?php if($is_group_invoice){ ?>

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
				                
							<?php } else { ?>
								<div class="form-group" id="description-form-group">
									<label for="amount" class="col-sm-4 control-label">
										<?php echo l('description'); ?> <small class="text-muted">(<?php echo l('optional'); ?>)</small>
									</label>
									<div class="col-sm-8">
										<textarea class="form-control" name="description" row=2></textarea>
									</div>
								</div>
							<?php } ?>
						</div>
						<div class="modal-footer">
							<input type="hidden"  id="manual_payment_capture" value="<?=$company['manual_payment_capture'];?>">
							<input type="hidden"  id="is_group_invoice" value="<?=$is_group_invoice;?>">
							<input type="hidden"  id="group_id" value="<?=$group_id;?>">
							<?php if($company['manual_payment_capture'] != 1){ ?>
							<button type="button" class="btn btn-success add_payment_button hidden" id="add_payment_button">
								<?php echo l('add').' '.l('payment'); ?>
							</button>
							
							<?php } else { ?>
							<button type="button" class="btn btn-success add_payment_button hidden" id="auth_and_capture">
								<?php echo l('Charge'); ?>
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

<input type="hidden" name="default_charge_name" class="default_charge_name" value="<?php echo $company['default_charge_name']; ?>">

<div id='invoice-container' class="container">
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="pe-7s-notebook text-success"></i>
                </div>
                <?php echo l('Group Invoice'); ?>
            </div>
            <div class="page-title-actions">
            	
            	<div class="form-group col-sm-12">
					<button class="btn btn-primary m-1" id="print-invoice-button">
						<?php echo l('print').' '.l('invoice'); ?>
					</button>
				
				<?php
					if ($menu_on === true):
				?>
						<button class="btn btn-primary dropdown-toggle pull-right m-1" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
							<?php echo l('more').' '.l('options'); ?>
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu pull-right m-1" role="menu" aria-labelledby="dropdownMenu1">
							<li role="presentation">
								<a role="menuitem" id="email-invoice-button" tabindex="-1" href="#">
									<?php echo l('email').' '.l('invoice'); ?>
								</a>
							</li>
							<li role="presentation">
								<a role="menuitem" class="open-booking-button" data-booking-id="<?php echo $single_booking_id ? $single_booking_id : $first_booking_id; ?>" tabindex="-1" href="#">
									<?php echo l('booking').' '.l('information'); ?>
								</a>
							</li>
							<?php 
							    if($single_booking_id) { 
								    $booking_id_for_invoice_logs[] = $single_booking_id; 
                                } else {
									foreach ($booking_ids as $value) {
									    $booking_id_for_invoice_logs[] = $value['booking_id'];
                                    }
                                }  ?>
                            <li role="presentation">
								<a role="menuitem" class="open-invoice-logs-button" data-booking-id="<?php echo implode(',', $booking_id_for_invoice_logs); ?>" tabindex="-1" href="#">
									<?php echo l('invoice').' '.l('logs'); ?>
								</a>
							</li>
						</ul>
						<!--
                        <button
                            class="btn btn-success form-control payment-modal-button"
                            data-toggle="modal"
							data-target="#add-payment-modal"
						>
							Add Payment
						</button>
						-->
				<?php
					endif;
				?>
				</div>

            </div>
        </div>
    </div>

   

<div class="main-card mb-3 card">
    <div class="card-body">



    <?php
        // show company logo image
        if (isset($company_logos[0]['filename'])) {
            echo "<img src='" . $this->image_url . $company['company_id'] . "/" . $company_logos[0]['filename'] . "' id='company-logo-image'/><br/>";
        }
    ?>

   	<div class="col-md-12 row invoice-header">
		<div class="col-xs-4 padding-left-zero">
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
                	
                </address>
		</div>
		<div class="col-xs-4 invoice_heading_div">
			<address class="form-inline">
                <strong><?php echo l('booked_by'); ?>:</strong>
				<?php
	                if (count($booking_customer) > 0):
						if ($booking_customer['customer_name'] != ""): ?>
						    <a href='<?php echo base_url()."customer/history/".$booking_customer['customer_id']; ?>'> <?php echo $booking_customer['customer_name'];?> </a><br/>
					    <?php endif; ?>
						<input type="hidden" class="text-right" id="customer_id"  disabled value="<?= !empty($booking_customer['customer_id']) ? $booking_customer['customer_id'] : "";?>"/>
						<?php
							$customer_address = array();
							if ($booking_customer['address']) $customer_address[] = $booking_customer['address'];
							if ($booking_customer['address2']) $customer_address[] = $booking_customer['address2'];
							if ($booking_customer['city']) $customer_address[] = $booking_customer['city'];
							if ($booking_customer['region']) $customer_address[] = $booking_customer['region'];
							if ($booking_customer['postal_code']) $customer_address[] = $booking_customer['postal_code'];
								$customer_address = implode(", ", $customer_address);
								echo ($customer_address) ? l('Address', true).": ".$customer_address."<br/>":'';

								echo ($booking_customer['phone']) ? l('Phone', true).": ".$booking_customer['phone']."<br/>":'';
								echo ($booking_customer['fax']) ? l('Fax', true).": ".$booking_customer['fax']."<br/>":'';
								echo ($booking_customer['email']) ? l('Email', true).": <span id='customer-email'>".$booking_customer['email']."</span><br/>":'';
							if (isset($booking_customer['customer_fields']) && count($booking_customer['customer_fields']) > 0) {
                        		foreach ($booking_customer['customer_fields'] as $customer_field) {
                           		 echo (isset($customer_field['name']) && $customer_field['name'] && isset($customer_field['value']) && $customer_field['value']) ? $customer_field['name'].": " . $customer_field['value'] . "<br/>" : '';
                        		}
                    		}

					endif; 
					if(isset($company['show_guest_group_invoice']) && $company['show_guest_group_invoice']): ?>
						<br/>
						<strong><?php echo l('Guests'); ?>:</strong>
						<br/>
						<?php if(count($customers) > 0): 
							foreach ($customers as $key => $customer) {
								if(isset($booking_customer) && isset($booking_customer['customer_id']) && $booking_customer['customer_id'] != $customer['customer_id']){
									echo $customer['customer_name'];
									echo $customer['email'] ? ' ('. $customer['email']. ')' : '';
									echo '<br/>';
								}
								
							}
						endif;
					endif;
					?>

			</address>
		</div>
		<div class="col-xs-4 text-right booking_id_div padding-right-zero">
			<?php if(isset($this->is_custom_invoice_enabled) && $this->is_custom_invoice_enabled == true) { 
				?>
            	<div class="create-gpwep">
	                <strong ><?php 

	                echo l('Group Invoice'); ?>:</strong> <span  id="group-invoice">
	                <?php echo isset($invoice_group) ? $invoice_group : ''; ?>
	                <span class="group_invoice_edit_icon"></span>
	                </span><br/>
            	</div>
            <?php }
               // else {
                // if($read_only){
            	?>
                 <!-- <div class="create-gpwep">
	                <strong >

	                //echo l('Group Invoice'); ?>:</strong> <span  id="group-invoice">
	                //echo isset($invoice_group) ? $invoice_group : ''; ?>
	                <span class="group_invoice_edit_icon"></span>
	                </span><br/>
                 </div> -->
           <?php// }} ?>
            <address>
                <strong><?php echo l('group').' '.l('id'); ?>:</strong>
                <?php if(isset($booking_group_detail)) { echo $booking_group_detail['id'];} ?>
                <input type="hidden" id="group_id" value="<?php if(isset($booking_group_detail)) { echo $booking_group_detail['id'];}?>">
			    <br/>
			    <?php echo l('group').' '.l('name'); ?>: <?php if(isset($booking_group_detail)){echo $booking_group_detail['name'];} ?><br/>
			    <?php
			    	if(isset($this->is_group_booking_features) && $this->is_group_booking_features == true) { 
			     echo l('check_in_date'); ?>: <span id="check-in-date">

                <?php echo isset($this->enable_hourly_booking) ? get_local_formatted_date($booking_detail['check_in_date']).' '.date('h:i A', strtotime($booking_detail['check_in_date'])) : get_local_formatted_date($booking_detail['check_in_date']);
                ?>
                    
                </span><br/>
                
                <?php echo l('check_out_date'); ?>: <span id="check-out-date">
                
                <?php echo isset($this->enable_hourly_booking) ? get_local_formatted_date($booking_detail['check_out_date']).' '.date('h:i A', strtotime($booking_detail['check_out_date'])) : get_local_formatted_date($booking_detail['check_out_date']);
                }
                ?>

                </span><br/>
			    
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

	<div class="col-md-12 row charges-and-payments">
		<?php if(!$read_only) { ?>	
			<div class="panel panel-success" style="overflow: hidden;width:100%;">
                
				<div id="folios" class="panel-heading" <?php if(count($booking_ids) > 6) { ?> style="min-width:max-content;" <?php } ?>>
					<ul class="nav nav-tabs" style="white-space:nowrap;width:auto;">
						<li style="display: inline-block !important;" class="all_bookings <?php if($group_id == end($this->uri->segments)) echo 'active'; ?> ">
							<div>
							    <a href="<?php echo base_url()."invoice/show_master_invoice/".$group_id;?>">
								<?php echo l('All', true); ?>
							    </a>
							</div>
						</li>
						<?php 
						    if(!empty($booking_ids)) {
						    	foreach ($booking_ids as $value) { ?>
						 	        <li class="single_booking <?php if($value['booking_id'] == end($this->uri->segments)) echo 'active'; ?> " style="display: inline-block !important;">
						 	        	<div>
						 	        		<span>
						 	        			<div style="color: #457ec3;">
								                    <?php echo l($this->default_room_singular).' '.$value['room_name'];?>
								                </div> 
							                </span>
							                <a href="<?php echo base_url()."invoice/show_master_invoice/".$group_id."/".$value['booking_id'];?>">
								            <?php echo l('ID')." #".$value['booking_id'];?> 
							                </a>
							            </div>    
						            </li>
						        <?php } 
						    } 
						?>
					</ul>
				</div>
				<i class="fa fa-angle-left invoice-arrow-icon" style="display: none;"></i>
				<i class="fa fa-angle-right invoice-arrow-icon" style="display: none;"></i>
			</div>
		<?php } ?>
        <div class="panel panel-default">
			<div class="h2 charge_div">
				<div class="col-sm-8">
					<?php echo l('charges'); ?>
				</div>
				<div class="col-sm-4 text-right">
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
            
			<table id="charge-table" class="table" >
				<thead>
					<tr>
                        <th class="text-left"><?php echo l('date'); ?></th>
                        <th class="text-left"><?php echo l('booking_id'); ?></th>
						<th class="text-left" id="room_name"><?php echo l($this->default_room_singular); ?></th>
						<th class="text-left"><?php echo l('Room Type'); ?></th>
						<th class="text-left"><?php echo l('description'); ?></th>
						<th class="text-left"><?php echo l('paying').' '.l('customer'); ?></th>
                        <th class="text-left"><?php echo l('charge_type'); ?></th>
                        <th class="text-right"><?php echo l('amount'); ?></th>
						<th class="text-right"><?php echo l('tax'); ?></th>
						<th class="text-right"><?php echo l('total'); ?></th>
						<?php if($single_booking_id): ?><th class="delete-td"></th><?php endif; ?> <!-- for x button -->
                    </tr>
				</thead>

				<tbody>
					<?php
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
								<td class="<?php echo 
                                  (isset($charge["charge_id"]) && ($single_booking_id))?"editable_td":""; ?>" onclick="">
									<span name="selling-date">
										<?php echo get_local_formatted_date($charge['selling_date']); ?>
									</span>
								</td>
                                <td><span name="booking_id"><?php echo $charge["booking_id"]; ?></span></td>
								<td><span name="room_name"><?php echo $charge["room_name"]; ?></span></td>
								<td><span name="room_type_name"><b><?php echo $charge["room_type_name"]; ?></b></span></td>
								<td class="<?php echo (isset($charge["charge_id"]) && ($single_booking_id))?"editable_td":""; ?>" onclick="">
									<span name="description">
										<?php
											// description
                                        echo $charge['description'];
										?>
									</span>
								</td>
								<td class="<?php echo (isset($charge['charge_id']) && ($single_booking_id))?"editable_td":""; ?>" onclick="">
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
                                <td class='<?php echo (isset($charge["charge_id"]) && ($single_booking_id)) ? "editable_td" : ""; ?>' onclick="">
									<span id="<?php echo $charge['charge_type_id']; ?>" name="charge-type">
										<?php echo $charge['charge_type_name']; ?>
									</span>
								</td>
								<td class='<?php echo (isset($charge["charge_id"]) && ($single_booking_id))?"editable_td":""; ?> text-right' onclick="">
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
														$tax_amount = (float)$tax['tax_rate'];
														$combined_tax = $combined_tax + $tax_amount;
													}
													
													echo '<div class="tax">'
														. '<span id="'.$tax['tax_type_id'].'"'
														.'class="'.($tax['is_tax_inclusive'] == 1 ? "hidden" : "").' tax-type">'.$tax['tax_type'].' </span>'
														. '<span data-real-taxes="'.$tax_amount.'"class="'.($tax['is_tax_inclusive'] == 1 ? "hidden" : "").' tax-amount">'. number_format($tax_amount, 2, ".", ",") . '</span>'
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
                                <?php if($single_booking_id): ?>
                                <td class="delete-td" width=30>
									<?php
										// only display X button if it's actual charge (not forecasted charge)
										if (isset($charge["charge_id"])):
									?>
											<!-- <i class="x-button hidden-print" title="Created by <?php echo $charge['user_name']; ?>"></i> -->
											<!--<i class="fa fa-caret-down hidden-print " title="Created by <?php echo $charge['user_name']; ?>"></i>-->
											<div class="dropdown hidden-print">
												<span class="dropdown-toggle" type="button" data-toggle="dropdown">
													<span class="caret"></span>
												</span>
												<ul class="dropdown-menu dropdown-menu-right">
													<li><a class="x-button" title="Created by <?php echo $charge['user_name']; ?>"><?php echo l('Delete', true); ?></a>
													</li>
												</ul>
											</div>
									<?php
										endif;
									?>
								</td> <?php endif; ?>
							</tr>
                        <?php
                        endforeach;
					endif;
					?>
				</tbody>
				<tfoot>
					<?php
						if ($menu_on === true):
							if($single_booking_id):
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
									>
										<?php echo l('add').' '.l('charge'); ?>
									</button>
								</td>
							</tr>
					<?php
						endif;
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
                                    <button
                                        id="button-save-invoice"
										class="hidden-print btn btn-primary form-control"
                                        data-loading-text="Loading..."
										style ="display:none;"
									>
										<?php echo l('save_changes'); ?>
                                    </button>
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
        <!-- /.panel -->

		<div class="panel panel-default">
			<div class="h2 text-left payment_div">
				<?php echo l('payments'); ?>
			</div>
			<table id="payment-table" class="table table-hover" >
				<thead>
					<tr>
                        <th class="text-left"><?php echo l('date'); ?></th>
                        <th class="text-left"><?php echo l('booking_id'); ?></th>
						<th class="text-left"><?php echo l('description'); ?></th>
                                                <th class="text-left"><?php echo l('paid_by'); ?></th>
                                                <th class="text-left"><?php echo l('payment_type'); ?></th>
                        <th class="text-left"><?php echo l('amount'); ?></th>
                        <?php if($single_booking_id): ?><th class="delete-td"></th><?php endif;?> <!-- for x button -->
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
                            data-remaining-amount="<?php echo ($payment['payment_status'] == 'charge' || $payment['payment_status'] == NULL) ? $payment['remaining_amount'] : ''; ?>"
                            >
                            <td>
                                <?php echo get_local_formatted_date($payment['selling_date']); ?>
                            </td>
                            <td><?php echo $payment['booking_id']; ?></td>

                            <td>
                                <?php echo $payment['description'];?>

                                <?php if ($payment['payment_gateway_used'] && !$read_only): ?>
                                    <?php
                                    if($payment['payment_status'] == 'partial'){
                                        printf(
                                            '%s %s',
                                            'Partial Refund ID:',
                                            $payment['gateway_charge_id']
                                        );
                                    }else{
                                        printf(
                                            '%s %s',
                                            $payment['amount'] > 0 ? 'Charge ID:' : 'Refund ID:',
                                            $payment['gateway_charge_id']
                                        ); 
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
                            <td class="text-left payment">
                                <?php
                                $rate = (float)$payment['amount'];
                                echo number_format($rate, 2, ".", ",");
                                ?>
                            </td>
                            <?php //if($single_booking_id): ?>
                             <td class="center delete-td" width="60">
                                <?php if (!$payment['read_only']): ?>
                                    <div class="dropdown pull-right delete-menu" style="display:none">
                                        <button aria-expanded="true" data-toggle="dropdown" id="dropdownMenu1" type="button" class="btn btn-default btn-xs dropdown-toggle">
                                            <span class="caret"></span>
                                        </button>
                                        <ul aria-labelledby="dropdownMenu1" role="menu" class="dropdown-menu">
                                            <li role="presentation">
                                                <a href="#" class="delete-payment" title="Created by <?php echo $payment['user_name']; ?>">
                                                    <?php if ($payment['payment_gateway_used']): echo l('refund'); ?>  <?php else: echo l('delete'); ?>  <?php endif; ?>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </td><?php //endif; ?>
                        </tr>
                    <?php
                    endforeach;
                endif;
					?>
				</tbody>
				<tfoot>
					<?php
						if ($menu_on === true):
							//if($single_booking_id):
					?>
						<tr class="hidden-print">
							<td colspan="6" class="text-right">
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
						//endif;
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

        </div> <!-- /.panel -->
		<div class="h2 text-muted" style="max-width: 100%; padding: 10px;">
			<div class="amount_due">
				<div class="text-right smaller_fonts payments_text_spacing">
					<?php echo l('amount', true).' '.l('due', true); ?>: 
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <span class="text-right smaller_fonts" id="amount_due">
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
			</div>  <!-- /. container.. -->

			<div class="col-md-3">
			</div>
		</div>
	<?php
		endif;
	?>
	</div>
</div>
</div>