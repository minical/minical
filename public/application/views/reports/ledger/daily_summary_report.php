<div id="printable-container">
	  <div class="app-page-title">
	<div class="page-title-wrapper">
		<div class="page-title-heading">
			<div class="page-title-icon">
				<i class="pe-7s-graph1 text-success"></i>
			</div>
			<?php echo l('Daily Summary Report'); ?>
			<div>
			
		</div>
	</div>
  </div>

    </div>

    <div class="main-card mb-3 card">
	<div class="card-body">

	<div class="form-inline">
		<label for="date">
		<?php echo l('Date:'); ?>
		</label>
		<input type="text" name="date" class="sellingDate form-control" value="<?php echo $date; ?>">	
		<button id="printReportButton" class="btn btn-primary hidden-print">
		<?php echo l('Print Report'); ?>
		</button>
		<?php $date = ($this->uri->segment(4) != '') ? $this->uri->segment(4) : ""; ?>
		<?php $param = $date; ?>
		<a id="downloaddailyreport" href="<?php if ($param != '/') {
			echo base_url() . "reports/ledger/download_daily_summary_report_csv_export/" . $param;
		} else {
			echo base_url() . "reports/ledger/download_daily_summary_report_csv_export/";
		} ?>" class="btn btn-primary" style="margin-right: 10px;" target="blank">
			<span title="Export to CSV" class="glyphicon glyphicon-download-alt"></span>
		</a>

	</div><!-- /.form-inline -->
	<div class="h4">
	<?php echo l('Charge Summary'); ?>
      </div>
	<table class="table table-hover" >
		<thead>
			<tr>
				<th><?php echo l('Category'); ?></th>
				<th class="text-center"><?php echo l($this->default_room_singular).' '.l('Charge',true) ; ?></th>
				<th class="text-right"><?php echo l('Sub-total'); ?></th>
				<th class="text-right"><?php echo l('Tax'); ?></th>
				<th class="text-right"><?php echo l('Total Charge'); ?></th>
			</tr>
		</thead>

		<?php
		$totalBeforeTax = 0;
		$totalAfterTax = 0;
		$subtotal = $taxamount = $summary_array = $tax_amount_array = array();
		$subTotal = $taxAmount = $totalAmount = 0; 
		if ($row['sales_detail'] != null){
			foreach ($row['sales_detail'] as $r){ 
//				if($r['charge']['state'] < '3')
//				{
					$taxAmountInclusive = $taxAmountExclusive = 0;
					$amount = $r['charge']['amount'];
					if (isset($r['tax_type'])) { 
						foreach ($r['tax_type'] as $tax) { 
							if ($tax['is_percentage'] == 1) {
								if ($tax['is_tax_inclusive'] == 1) { 
										$subTotal = $amount / (1 + ((float) $tax['tax_rate'] * 0.01));
										$taxAmountInclusive += $amount - ($amount / (1 + ((float) $tax['tax_rate'] * 0.01)));
									} else { 
										$subTotal = $amount;
										$taxAmountExclusive += (float) $tax['tax_rate'] * $amount * 0.01;
									}
							} else {
								$subTotal = (float) $tax['tax_rate'];
							}
						}
					} else {
						$subTotal = $amount;
					}
					$taxAmount = $taxAmountInclusive + $taxAmountExclusive;
					$subtotal[$r['charge']['charge_type_id']] = ((isset($subtotal[$r['charge']['charge_type_id']]) && $subtotal[$r['charge']['charge_type_id']]) ? $subtotal[$r['charge']['charge_type_id']] : 0) + (isset($subTotal) && $subTotal ? $subTotal : 0);
					$taxamount[$r['charge']['charge_type_id']] = ((isset($taxamount[$r['charge']['charge_type_id']]) && $taxamount[$r['charge']['charge_type_id']]) ? $taxamount[$r['charge']['charge_type_id']] : 0) + (isset($taxAmount) && $taxAmount ? $taxAmount : 0);

					$summary_array['charge_type'][$r['charge']['charge_type_id']] = $r['charge']['charge_type'];
					if ($r['charge']['is_room_charge_type'] == '1')
					{
						$summary_array['room_charge'][$r['charge']['charge_type_id']] = "<span class='glyphicon glyphicon-ok' aria-hidden='true'></span>";
					}else
					{
						$summary_array['room_charge'][$r['charge']['charge_type_id']] = '';
					}

					$totalBeforeTax += $subTotal;
					$totalAfterTax += $taxAmount;
				//}
			}
			$summary_array['subtotal'] = $subtotal;
			$summary_array['taxamount'] = $taxamount;
			$summary_array['subtotal']['total'] = $totalBeforeTax;
			$summary_array['taxamount']['total'] = $totalAfterTax;
			$c = array_map(function () {
				return array_sum(func_get_args());
			}, $subtotal, $taxamount);

			$summary_array['Total'] = (isset($c) && $c) ? $c : 0;
		}
		
		$newArray = (isset($summary_array['Total']) && $summary_array['Total']) ? $summary_array['Total'] : 0;
		
		$summary_array['Total'] = array();
		$i = 0;
		if(isset($summary_array['subtotal']) && $summary_array['subtotal'])
		{
			foreach ($summary_array['subtotal'] as $key => $value) {
				if(isset($key) && is_numeric($key))
					$summary_array['Total'][$key] = $newArray[$i];
				$i++;
			}
		}
		
		$summary_array['Total']['total'] = array_sum($summary_array['Total']); ?>
		
		<tbody>
		<?php
			$charge_summary_array = array();
			if (isset($summary_array) && isset($summary_array['room_charge'])){
				foreach ($summary_array as $key => $summary){
					$count = count($summary_array['room_charge']);
					$i = 0;
					foreach ($summary_array[$key] as $key1 => $s){
						if ($key1 != 'total')
							$charge_summary_array[$i][] = $summary_array[$key][$key1];
						$i++;
					}
				}
			}
			foreach ($charge_summary_array as $key => $summary) { ?>
				<tr class="salesrow">
					<?php for ($j = 0; $j < count($summary); $j++) { ?>
						<td <?php if (is_numeric($summary[$j])) { ?> class="text-right" <?php } elseif ($summary[$j] == "<span class='glyphicon glyphicon-ok' aria-hidden='true'></span>")  { ?> class="text-center" <?php } ?>><?php echo is_numeric($summary[$j]) ? number_format($summary[$j], 2, ".", ",") : $summary[$j]; ?></td>
					<?php } ?>
				</tr>
			<?php } ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan=2 class="text-right"><b><?php echo l('Total'); ?>:</b></td>
				<td class="text-right"><?php echo number_format($totalBeforeTax, 2, ".", ","); ?></td>					
				<td class="text-right"><?php echo number_format($totalAfterTax, 2, ".", ","); ?></td>
				<td class="text-right">
					<?php
					echo number_format($totalAfterTax + $totalBeforeTax, 2, ".", ",") . "<br/>";
					?>		
				</td>
			</tr>				
		</tfoot>
	</table>

	<?php
		$totalBeforeTax = 0;
		$totalAfterTax = 0;
		$subtotal = $taxamount = $summary_array = $tax_amount_array = array();
		$subTotal = $taxAmount = $totalAmount = 0; 
		if ($row['cancelled_noshow_bookings_sales_detail'] != null){
			foreach ($row['cancelled_noshow_bookings_sales_detail'] as $r){ 
//				if($r['charge']['state'] == '4' || $r['charge']['state'] == '5')
//				{
					$taxAmountInclusive = $taxAmountExclusive = 0;
					$amount = $r['charge']['amount'];
					if (isset($r['tax_type'])) { 
						foreach ($r['tax_type'] as $tax) { 
							if ($tax['is_percentage'] == 1) {
								if ($tax['is_tax_inclusive'] == 1) { 
										$subTotal = $amount / (1 + ((float) $tax['tax_rate'] * 0.01));
										$taxAmountInclusive += $amount - ($amount / (1 + ((float) $tax['tax_rate'] * 0.01)));
									} else { 
										$subTotal = $amount;
										$taxAmountExclusive += (float) $tax['tax_rate'] * $amount * 0.01;
									}
							} else {
								$subTotal = (float) $tax['tax_rate'];
							}
						}
					} else {
						$subTotal = $amount;
					}
					$taxAmount = $taxAmountInclusive + $taxAmountExclusive;
					$subtotal[$r['charge']['charge_type_id']] = ((isset($subtotal[$r['charge']['charge_type_id']]) && $subtotal[$r['charge']['charge_type_id']]) ? $subtotal[$r['charge']['charge_type_id']] : 0) + (isset($subTotal) && $subTotal ? $subTotal : 0);
					$taxamount[$r['charge']['charge_type_id']] = ((isset($taxamount[$r['charge']['charge_type_id']]) && $taxamount[$r['charge']['charge_type_id']]) ? $taxamount[$r['charge']['charge_type_id']] : 0) + (isset($taxAmount) && $taxAmount ? $taxAmount : 0);

					$summary_array['charge_type'][$r['charge']['charge_type_id']] = $r['charge']['charge_type'];
					if ($r['charge']['is_room_charge_type'] == '1')
					{
						$summary_array['room_charge'][$r['charge']['charge_type_id']] = "<span class='glyphicon glyphicon-ok' aria-hidden='true'></span>";
					}else
					{
						$summary_array['room_charge'][$r['charge']['charge_type_id']] = '';
					}

					$totalBeforeTax += $subTotal;
					$totalAfterTax += $taxAmount;
				//}
			}
			$summary_array['subtotal'] = $subtotal;
			$summary_array['taxamount'] = $taxamount;
			$summary_array['subtotal']['total'] = $totalBeforeTax;
			$summary_array['taxamount']['total'] = $totalAfterTax;
			$c = array_map(function () {
				return array_sum(func_get_args());
			}, $subtotal, $taxamount);

			$summary_array['Total'] = (isset($c) && $c) ? $c : 0;
		}
		
		$newArray = (isset($summary_array['Total']) && $summary_array['Total']) ? $summary_array['Total'] : 0;
		
		$summary_array['Total'] = array();
		$i = 0;
		if(isset($summary_array['subtotal']) && $summary_array['subtotal'])
		{
			foreach ($summary_array['subtotal'] as $key => $value) {
				if(isset($key) && is_numeric($key))
					$summary_array['Total'][$key] = $newArray[$i];
				$i++;
			}
		}
		
		$summary_array['Total']['total'] = array_sum($summary_array['Total']); 
		
		$charge_summary_array = array();
		if (isset($summary_array) && isset($summary_array['room_charge'])){
			foreach ($summary_array as $key => $summary){
				$count = count($summary_array['room_charge']);
				$i = 0;
				foreach ($summary_array[$key] as $key1 => $s){
					if ($key1 != 'total')
						$charge_summary_array[$i][] = $summary_array[$key][$key1];
					$i++;
				}
			}
		}
	?>
	
	<?php $charge_summary_cancelled_noshow_bookings = (isset($row['cancelled_noshow_bookings_sales_detail']) && $row['cancelled_noshow_bookings_sales_detail']) ? true : false; ?>
	
	<div class="h4 <?=$charge_summary_cancelled_noshow_bookings ? '' : 'hidden';?>"> <?php echo l('Charge Summary (Cancelled/No show Bookings)'); ?></div>
	<table class="table table-hover <?=$charge_summary_cancelled_noshow_bookings ? '' : 'hidden';?>" >
		<thead>
			<tr>
			<th><?php echo l('Category'); ?></th>
				<th class="text-center"><?php echo l($this->default_room_singular).' '.l('Charge',true) ; ?></th>
				<th class="text-right"><?php echo l('Sub-total'); ?></th>
				<th class="text-right"><?php echo l('Tax'); ?></th>
				<th class="text-right"><?php echo l('Total Charge'); ?></th>
			</tr>
		</thead>

		
		
		<tbody>
		<?php
			
			foreach ($charge_summary_array as $key => $summary) { ?>
				<tr class="salesrow">
					<?php for ($j = 0; $j < count($summary); $j++) { ?>
						<td <?php if (is_numeric($summary[$j])) { ?> class="text-right" <?php } elseif ($summary[$j] == "<span class='glyphicon glyphicon-ok' aria-hidden='true'></span>")  { ?> class="text-center" <?php } ?>><?php echo is_numeric($summary[$j]) ? number_format($summary[$j], 2, ".", ",") : $summary[$j]; ?></td>
					<?php } ?>
				</tr>
			<?php } ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan=2 class="text-right"><b><?php echo l('Total')?>:</b></td>
				<td class="text-right"><?php echo number_format($totalBeforeTax, 2, ".", ","); ?></td>					
				<td class="text-right"><?php echo number_format($totalAfterTax, 2, ".", ","); ?></td>
				<td class="text-right">
					<?php
					echo number_format($totalAfterTax + $totalBeforeTax, 2, ".", ",") . "<br/>";
					?>		
				</td>
			</tr>				
		</tfoot>
	</table>
	
	<div class="h4"><?php echo l('Payment Summary'); ?></div>
	<!-- Table containing totals by charge types -->
	<table class="table table-hover">
		<thead>
			<tr>
				<th><?php echo l('Payment Type'); ?></th>						
				<th class="text-right"><?php echo l('Amount'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
//			$payment_summary_cancelled_noshow = false;
			$total_payment = 0;
			if ($row['payment_total'] != null) :
				foreach ($row['payment_total'] as $r):
//					if($r->state < '3'):
					?>
					<tr class="salesRow">    
						<td><?php echo $r->payment_type; ?></td>						
						<td class="text-right">
							<?php
							// sale amount
							echo number_format($amount = $r->amount, 2, ".", ",");
							$total_payment = $total_payment + $amount;
							?>
						<?php 
//						elseif($r->state == '4' || $r->state == '5'):
//							$payment_summary_cancelled_noshow = true;
//						endif; 
						endforeach; ?>
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>						
				<td><b><?php echo l('Total'); ?>:</b></td>
				<td class="text-right"><?php echo number_format($total_payment, 2, ".", ","); ?></td>					
			</tr>
		</tfoot>
	</table>
	<?php $payment_summary_cancelled_noshow = (isset($row['cancelled_noshow_bookings_payment_total']) && $row['cancelled_noshow_bookings_payment_total']) ? true : false; ?>
	<div class="h4 <?=$payment_summary_cancelled_noshow ? '' : 'hidden';?>"><?php echo l('Payment Summary (Cancelled/No show Bookings)'); ?></div>
	<!-- Table containing totals by charge types -->
	<table class="table table-hover <?=$payment_summary_cancelled_noshow ? '' : 'hidden';?>">
		<thead>
			<tr>
				<th><?php echo l('Payment Type'); ?></th>						
				<th class="text-right"><?php echo l('Amount'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$total_payment = 0;
			if ($row['cancelled_noshow_bookings_payment_total'] != null) :
				foreach ($row['cancelled_noshow_bookings_payment_total'] as $r):
					//if($r->state == '4' || $r->state == '5'):
					?>
					<tr class="salesRow">    
						<td><?php echo $r->payment_type; ?></td>						
						<td class="text-right">
							<?php
							// sale amount
							echo number_format($amount = $r->amount, 2, ".", ",");
							$total_payment = $total_payment + $amount;
							?>
					<?php  //endif; 
					endforeach; ?>
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>						
				<td><b><?php echo l('Total'); ?>:</b></td>
				<td class="text-right"><?php echo number_format($total_payment, 2, ".", ","); ?></td>					
			</tr>
		</tfoot>
	</table>
	
	<div class="h4"><?php echo l('Balance by Customer Types'); ?></div>
	<table class="table table-hover">
		<thead>
			<tr>
				<th><?php echo l('Customer Type'); ?></th>
				<th class="text-right"><?php echo l('Previous Balance'); ?></th>
				<th class="text-right"><?php echo l("Today's Balance Change"); ?></th>
				<th class="text-right"><?php echo l('Current'); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php
			$total_to_date = 0;
			$total_today = 0;

			foreach ($customer_type_totals as $customer_type):
				?>
				<tr>
					<td><?php echo $customer_type['name']; ?></td>
					<td class="text-right"><?php echo number_format($customer_type['to_date'], 2, ".", ","); ?></td>
					<td class="text-right"><?php echo number_format($customer_type['today'], 2, ".", ","); ?></td>
					<td class="text-right"><?php echo number_format($customer_type['to_date'] + $customer_type['today'], 2, ".", ","); ?></td>
				</tr>
				<?php
				$total_to_date += $customer_type['to_date'];
				$total_today += $customer_type['today'];

			endforeach;
			?>
		</tbody>
		<tfoot>
			<tr>
				<td><b><?php echo l('Total'); ?>:</br></td>
				<td class="text-right"><?php echo number_format($total_to_date, 2, ".", ","); ?></td>
				<td class="text-right"><?php echo number_format($total_today, 2, ".", ","); ?></td>
				<td class="text-right"><?php echo number_format($total_to_date + $total_today, 2, ".", ","); ?></td>
			</tr>
		</tfoot>
	</table>

	<div class="h4"><?php echo l('Ocupancy & Average Rate'); ?></div>
	<?php
	$total_count_mtd = array();
	$total_count_ytd = array();
	$total_rate_mtd = 0;
	$total_rate_ytd = 0;
	$today_count = isset($booking_count[$date]) ? $booking_count[$date] : 0;
	$today_rate = 0;

	foreach ($total_data as $t) {
		if (strtotime($t->date) <= strtotime($date)) {
			if (isset($t->total_rate)) {
				$total_rate_ytd += $t->total_rate;
			}
			if (date('n', strtotime($t->date)) == date('n', strtotime($date))) {
				if (isset($t->total_rate)) {
					$total_rate_mtd += $t->total_rate;
				}
			}
		}
	}

	$mtd_days = 0;
	$ytd_days = 0;
	foreach ($booking_count as $d => $count) {
		if ($d >= Date("Y-01-01", strtotime($date)) && $d <= $date) {
			$total_count_ytd[] = $booking_count[$d];
			$ytd_days++;
		}

		if ($d >= Date("Y-m-01", strtotime($date)) && $d <= $date) {
			$total_count_mtd[] = $booking_count[$d];
			$mtd_days++;
		}
	}
	?>
	<table class="table table-hover">
		<thead>
			<tr>
				<th><?php echo l('Occupancy'); ?></th>						
				<th><?php echo l('M.T.D'); ?></th>						
				<th><?php echo l('Y.T.D'); ?></th>
				<th><?php echo l('ADR'); ?></th>
				<th><?php echo l('M.T.D'); ?></th>
				<th class="text-right"><?php echo l('Y.T.D'); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr class="salesRow">    
				<td>
				<?php
					echo number_format($today_count / $total_rooms * 100, 2, ".", ",") . "% (" . $today_count . ")";
				?>
				</td>
				<td>
					<?php
					if ($mtd_days != 0)
						echo number_format(array_sum($total_count_mtd) / $total_rooms / $mtd_days * 100, 2, ".", ",") . '% (' . array_sum($total_count_mtd) . ')';
					?>
				</td>
				<td>
					<?php
					if ($ytd_days != 0)
						echo number_format(array_sum($total_count_ytd) / $total_rooms / $ytd_days * 100, 2, ".", ",") . '% (' . array_sum($total_count_ytd) . ')';
					?>
				</td>
				<td>
					<?php
					if ($today_count > 0)
						echo number_format($room_charges / $today_count, 2, ".", ",");
					?>
				</td>
				<td>
					<?php
					$mtd_adr_array = array();
					foreach ($room_charges_mtd as $d => $room_charge) {
						if (isset($booking_count[$d])) {
							$mtd_adr_array[] = $room_charge / $booking_count[$d];
						}
					}
					//echo array_sum($room_charges_mtd)." / ".count($room_charges_mtd)." / ".$today_count."<br/>";
					if (count($mtd_adr_array) > 0)
						echo number_format(array_sum($mtd_adr_array) / count($mtd_adr_array), 2, ".", ",");
					else
						echo 0;
					?>
				</td>
				<td class="text-right">
					<?php
					$ytd_adr_array = array();
					foreach ($room_charges_ytd as $d => $room_charge) {
						if (isset($booking_count[$d])) {
							$ytd_adr_array[] = $room_charge / $booking_count[$d];
						}
					}
					if (count($ytd_adr_array) > 0)
						echo number_format(array_sum($ytd_adr_array) / count($ytd_adr_array), 2, ".", ",");
					else
						echo 0;
					?>
				</td>
			</tr>
		</tbody>
	</table>

	<div class="h4"><?php echo l('Charge Details'); ?></div>
	<!-- Table showing detailed charges -->
	<table class="table table-hover">
		<thead>
			<tr>
				<th>
					<?php
					if ($this->session->userdata('property_type') == 0) {
						echo $this->lang->line('room');
					} else {
						echo $this->lang->line('bed');
					}
					?>
				</th>
				<th><?php echo l('Booking Customer'); ?></th>
				<th><?php echo l('Customer Type'); ?></th>
				<th><?php echo l('Charge Type'); ?></th>
				<th class="text-right"><?php echo l('Sub-total'); ?></th>
				<th class="text-right"><?php echo l('Tax'); ?></th>
				<th class="text-right"><?php echo l('Total Sales'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		//$charge_details_cancelled_noshow = false;
		
		$totalBeforeTax = $totalAfterTax = 0;
		$tax_array = array();
		if ($row['sales_detail'] != null) :
			foreach ($row['sales_detail'] as $r):
				//if($r['charge']['state'] < '3'):
				$totalTax = 0;
				?>
					<tr class="salesRow">    
						<td>
							<a href="<?php echo base_url() . "invoice/show_invoice/" . $r['charge']['booking_id']; ?>">
					<?php echo $r['charge']['room_name'] ? $r['charge']['room_name'] : 'Not Assigned'; ?>
							</a>
						</td>
						<td><?php echo $r['charge']['customer_name']; ?></td>
						<td><?php echo $r['charge']['customer_type']; ?></td>
						<td><?php echo $r['charge']['charge_type']; ?></td>								
						<td class="text-right">
								<?php
								// sale amount
								$amount = $r['charge']['amount'];
//					$totalBeforeTax = $totalBeforeTax + $amount;
//					echo number_format($amount, 2, ".", ",");
								?>
								<?php

									$subTotal = $amount;
									if (isset($r['tax_type'])) {
										foreach ($r['tax_type'] as $tax) {
											if ($tax['is_percentage'] == 1) {
												if ($tax['is_tax_inclusive'] == 1) {
													$subTotal = $amount / (1 + ((float) $tax['tax_rate'] * 0.01));
												} else {
													$subTotal = $amount;
												}
											} else {
												$subTotal = (float) $tax['tax_rate'];
											}
										}
									}
									echo number_format($subTotal, 2, ".", ",");
									$totalBeforeTax += $subTotal;

								?>
						</td>
						<td class="small text-right">
							<?php
							$totalAmount = $amount;
							if (isset($r['tax_type'])) {
								foreach ($r['tax_type'] as $tax) {
									if ($tax['is_percentage'] == 1) {
										if ($tax['is_tax_inclusive'] == 1) {
											$taxAmount = $amount - ($amount / (1 + ((float) $tax['tax_rate'] * 0.01)));
											$totalAmount = $totalAmount;
										} else {
											$taxAmount = (float) $tax['tax_rate'] * $amount * 0.01;
											$totalAmount = $totalAmount + $taxAmount;
										}
									} else {
										$taxAmount = (float) $tax['tax_rate'];
									}
                                    $tax_array[$tax['tax_type']] = ((isset($tax_array[$tax['tax_type']]) && $tax_array[$tax['tax_type']]) ? $tax_array[$tax['tax_type']]: 0) + $taxAmount; 
									echo $tax['tax_type'] . " <span class='taxTypeID' id='" . $tax['tax_type_id'] . "'>" . number_format($taxAmount, 2, ".", ",") . "</span><br/>";
								}
							}
						    ?>					
						</td>
						<td class="text-right">
							<?php
							$charge = $totalAmount;
							//$charge = round($totalTax + $amount, 2);
							echo number_format($charge, 2, ".", ",");
							$totalAfterTax += $charge;
							?>
						</td>
					</tr>
						
			<?php 
//				elseif($r['charge']['state'] == '4' || $r['charge']['state'] == '5'):
//					$charge_details_cancelled_noshow = true;
//				endif; 
			endforeach; ?>
		<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan=3></td>
				<td><b><?php echo l('Total'); ?>:</b></td>

				<td class="text-right"><?php echo number_format($totalBeforeTax, 2, ".", ","); ?></td>					
				<td class="small text-right"><?php 
					if (isset($tax_array)) {
						foreach ($tax_array as $t => $value) {
							echo $t . " " . number_format($value, 2, ".", ",") . "<br/>";
					}
				}
				?>	
				</td>	
				<td class="text-right">
					<?php echo number_format($totalAfterTax, 2, ".", ","); ?>
				</td>
			</tr>				
		</tfoot>
	</table>
	
	<?php $charge_details_cancelled_noshow = (isset($row['cancelled_noshow_bookings_sales_detail']) && $row['cancelled_noshow_bookings_sales_detail']) ? true : false; ?>

	<div class="h4 <?=$charge_details_cancelled_noshow ? '' : 'hidden';?>"><?php echo l('Charge Details (Cancelled/No show Bookings)'); ?></div>
	<!-- Table showing detailed charges -->
	<table class="table table-hover <?=$charge_details_cancelled_noshow ? '' : 'hidden';?>">
		<thead>
			<tr>
				<th>
					<?php
					if ($this->session->userdata('property_type') == 0) {
						echo $this->lang->line('room');
					} else {
						echo $this->lang->line('bed');
					}
					?>
				</th>
				<th><?php echo l('Booking Customer'); ?></th>
				<th><?php echo l('Customer Type'); ?></th>
				<th><?php echo l('Charge Type'); ?></th>
				<th class="text-right"><?php echo l('Sub-total'); ?></th>
				<th class="text-right"><?php echo l('Tax'); ?></th>
				<th class="text-right"><?php echo l('Total Sales'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		$totalBeforeTax = $totalAfterTax = 0;
		$tax_array = array();
		if ($row['cancelled_noshow_bookings_sales_detail'] != null) :
			foreach ($row['cancelled_noshow_bookings_sales_detail'] as $r):
				//if($r['charge']['state'] == '4' || $r['charge']['state'] == '5'):
				$totalTax = 0;
				?>
					<tr class="salesRow">    
						<td>
							<a href="<?php echo base_url() . "invoice/show_invoice/" . $r['charge']['booking_id']; ?>">
								<?php echo $r['charge']['room_name'] ? $r['charge']['room_name'] : 'Not Assigned'; ?>
							</a>
						</td>
						<td><?php echo $r['charge']['customer_name']; ?></td>
						<td><?php echo $r['charge']['customer_type']; ?></td>
						<td><?php echo $r['charge']['charge_type']; ?></td>								
						<td class="text-right">
							<?php
								// sale amount
								$amount = $r['charge']['amount'];
								// $totalBeforeTax = $totalBeforeTax + $amount;
								// echo number_format($amount, 2, ".", ",");
							?>
							<?php

								$subTotal = $amount;
								if (isset($r['tax_type'])) {
									foreach ($r['tax_type'] as $tax) {
										if ($tax['is_percentage'] == 1) {
											if ($tax['is_tax_inclusive'] == 1) {
												$subTotal = $amount / (1 + ((float) $tax['tax_rate'] * 0.01));
											} else {
												$subTotal = $amount;
											}
										} else {
											$subTotal = (float) $tax['tax_rate'];
										}
									}
								}
								echo number_format($subTotal, 2, ".", ",");
								$totalBeforeTax += $subTotal;

							?>
						</td>
						<td class="small text-right">
							<?php
							$totalAmount = $amount;
							if (isset($r['tax_type'])) {
								foreach ($r['tax_type'] as $tax) {
									if ($tax['is_percentage'] == 1) {
										if ($tax['is_tax_inclusive'] == 1) {
											$taxAmount = $amount - ($amount / (1 + ((float) $tax['tax_rate'] * 0.01)));
											$totalAmount = $totalAmount;
										} else {
											$taxAmount = (float) $tax['tax_rate'] * $amount * 0.01;
											$totalAmount = $totalAmount + $taxAmount;
										}
									} else {
										$taxAmount = (float) $tax['tax_rate'];
									}
                                    $tax_array[$tax['tax_type']] = ((isset($tax_array[$tax['tax_type']]) && $tax_array[$tax['tax_type']]) ? $tax_array[$tax['tax_type']]: 0) + $taxAmount; 
									echo $tax['tax_type'] . " <span class='taxTypeID' id='" . $tax['tax_type_id'] . "'>" . number_format($taxAmount, 2, ".", ",") . "</span><br/>";
								}
							}
						    ?>					
						</td>
						<td class="text-right">
							<?php
							$charge = $totalAmount;
							//$charge = round($totalTax + $amount, 2);
							echo number_format($charge, 2, ".", ",");
							$totalAfterTax += $charge;
							?>
						</td>
					</tr>
						<?php //endif; 
						endforeach; ?>
					<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan=3></td>
				<td><b><?php echo l('Total'); ?>:</b></td>

				<td class="text-right"><?php echo number_format($totalBeforeTax, 2, ".", ","); ?></td>					
				<td class="small text-right"><?php 
					if (isset($tax_array)) {
						foreach ($tax_array as $t => $value) {
							echo $t . " " . number_format($value, 2, ".", ",") . "<br/>";
					}
				}
				?>	
				</td>	
				<td class="text-right">
					<?php echo number_format($totalAfterTax, 2, ".", ","); ?>
				</td>
			</tr>				
		</tfoot>
	</table>
	
	<div class="h4"><?php echo l('Payment Details'); ?></div>
	<!-- Table showing detailed charges -->
	<table class="table table-hover">
		<thead>
			<tr>
				<th>
					<?php
					if ($this->session->userdata('property_type') == 0) {
						echo $this->lang->line('room');
					} else {
						echo $this->lang->line('bed');
					}
					?>
				</th>
				<th><?php echo l('Customer'); ?></th>
				<th><?php echo l('Customer Type'); ?></th>
				<th><?php echo l('Payment Type'); ?></th>
				<th><?php echo l('Description'); ?></th>
				<th class="text-right"><?php echo l('Amount'); ?></th>						
			</tr>
		</thead>
		<tbody>
		<?php
		$total_payment = 0;
//		$payment_details_cancelled_noshow = false;
		//print_r($row['detail']);
		if ($row['payment_detail'] != null) :
			foreach ($row['payment_detail'] as $r):
//				if($r->state < '3'):
					$totalTax = 0;
					?>
					<tr class="salesRow">    
						<td>
							<a href="<?php echo base_url() . "invoice/show_invoice/" . $r->booking_id; ?>">
							<?php echo $r->room_name ? $r->room_name : 'Not Assigned'; ?>
							</a>
						</td>
						<td><?php echo $r->customer_name; ?></td>
						<td><?php echo $r->customer_type; ?></td>
						<td><?php echo $r->payment_type; ?></td>
						<td><?php echo $r->description; ?></td>
						<td class="text-right"><?php
					// sale amount
					$amount = $r->amount;
					$total_payment = $total_payment + $amount;
					echo number_format($amount, 2, ".", ",");
							?>
						</td>								
					</tr>
				<?php 
//				elseif($r->state == '4' || $r->state == '5'):
//					$payment_details_cancelled_noshow = true;
//				endif; 
				endforeach; ?>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan=4></td>	
				<td><b><?php echo l('Total')?>:</b></td>
				<td class="text-right">
				<?php
				echo number_format($total_payment, 2, ".", ",");
				?>
				</td>
			</tr>				
		</tfoot>
	</table>	
	
	<?php $payment_details_cancelled_noshow = (isset($row['cancelled_noshow_bookings_payment_detail']) && $row['cancelled_noshow_bookings_payment_detail']) ? true : false; ?>
	
	<div class="h4 <?=$payment_details_cancelled_noshow ? '' : 'hidden';?>"><?php echo l('Payment Details (Cancelled/No show Bookings)'); ?></div>
	<!-- Table showing detailed charges -->
	<table class="table table-hover <?=$payment_details_cancelled_noshow ? '' : 'hidden';?>">
		<thead>
			<tr>
				<th>
					<?php
					if ($this->session->userdata('property_type') == 0) {
						echo $this->lang->line('room');
					} else {
						echo $this->lang->line('bed');
					}
					?>
				</th>
				<th><?php echo l('Customer'); ?></th>
				<th><?php echo l('Customer Type'); ?></th>
				<th><?php echo l('Payment Type'); ?></th>
				<th><?php echo l('Description'); ?></th>
				<th class="text-right"><?php echo l('Amount'); ?></th>						
			</tr>
		</thead>
		<tbody>
		<?php
		$total_payment = 0;

		//print_r($row['detail']);
		if ($row['cancelled_noshow_bookings_payment_detail'] != null) :
			foreach ($row['cancelled_noshow_bookings_payment_detail'] as $r):
//				if($r->state == '4' || $r->state == '5'):
				$totalTax = 0;
				?>
					<tr class="salesRow">    
						<td>
							<a href="<?php echo base_url() . "invoice/show_invoice/" . $r->booking_id; ?>">
							<?php echo $r->room_name ? $r->room_name : 'Not Assigned'; ?>
							</a>
						</td>
						<td><?php echo $r->customer_name; ?></td>
						<td><?php echo $r->customer_type; ?></td>
						<td><?php echo $r->payment_type; ?></td>
						<td><?php echo $r->description; ?></td>
						<td class="text-right"><?php
					// sale amount
					$amount = $r->amount;
					$total_payment = $total_payment + $amount;
					echo number_format($amount, 2, ".", ",");
							?>
						</td>								
					</tr>
				<?php //endif; 
				endforeach; ?>
			<?php endif; ?>
		</tbody>

		<tfoot>
			<tr>
				<td colspan=4></td>	
				<td><b><?php echo l('Total')?>:</b></td>
				<td class="text-right">
				<?php
				echo number_format($total_payment, 2, ".", ",");
				?>
				</td>
			</tr>				
		</tfoot>
	</table>	
</div></div></div>
<!--	<a href="<?php echo base_url() . 'report/download_daily_report_csv_export/' . $date . '.csv'; ?>" class="hidden-print">Download Booking CSV Export</a>-->

