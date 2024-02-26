






<div id="printable-container">

     <div class="app-page-title">
	<div class="page-title-wrapper">
		<div class="page-title-heading">
			<div class="page-title-icon">
				<i class="pe-7s-graph1 text-success"></i>
			</div>
			 <?php echo l('ledger_summary_report'); ?>  <span style="font-size: 19px; padding-left: 20px;display: inline-block;"><span class="visible-print-block" id="selected-customer-type"><?php echo l('Customer Type', true); ?>: <?php echo l('All', true); ?></span></span>
			<div>
			
		</div>
	</div>
  </div>

    </div>

    <div class="main-card mb-3 card">
	<div class="card-body">
    
	<div class="panel panel-default hidden-print">
		<div class="panel-body">
			<div class="h4 form-inline hidden-print">
				<div class="form-group">
			  		<?php echo l('from'); ?>
					
					<input type="text" class="form-control datepicker form-001" id="dateStart" value="<?php echo $start_date; ?>" style="width:100px;" />
					<?php echo l('to'); ?>
					<input type="text" class="form-control datepicker form-001" id="dateEnd" value="<?php echo $end_date; ?>" style="width:100px;"/>
					<select class="form-control form-001" id="groupBy">
						<option value="daily">
							<?php echo l('daily'); ?>
						</option>
						<option value="monthly">
							<?php echo l('monthly'); ?>
						</option>
					</select>
                    <?php echo l('Customer Type', true); ?>
                    <select class="form-control form-001" name="customer_type_id">
					<option value=''><?php echo l('All', true); ?></option>
					<?php
						foreach ($customer_types as $customer_type):
					?>
							<option value="<?php echo $customer_type['id']; ?>">
								<?php echo $customer_type['name']; ?>
							</option>
					<?php
						endforeach;
					?>
                    </select>
					<button id="generateReport" class="btn btn-success hidden-print"><?php echo l('generate_report'); ?></button>
				</div> <!-- /.form-group -->
				<button id="printReportButton" class="btn btn-primary pull-right hidden-print"><span class="glyphicon glyphicon-print" title="Print Report"></span></button>
                                
                <a id="downloadReport" target="_blank" style="margin: 0 10px;" href="javascript:" class="btn btn-primary pull-right ">
                    <span title="Export to CSV" class="glyphicon glyphicon-download-alt"></span>
                </a>
			</div><!-- /.form-inline -->
		</div>
	</div>
	
	<!--Google Graph-->
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript">
	  google.load('visualization', '1', {packages: ['corechart']});
	</script>
	
	<div id="visualization"></div>	

	<!-- Display ONLY when printing -->
	<div class="visible-print-block">
			<?php echo l('showing_data_between'); ?> <span id="dateStartPrint"><?php echo $start_date; ?></span> and <span id="dateEndPrint"><?php echo $end_date; ?></span>
	</div>
	<div class="table-responsive">
	<table class="table table-hover">
		<thead>
			<tr>
				<th class="text-left"><?php echo l('selling_date'); ?></th>
				<th class="text-center"><?php echo l('bookings'); ?><span class="hidden-print"> (<?php echo l('occupancy_rate'); ?>)</span></th>
				<?php if($this->company_id == 2242) { ?>
					<th class="text-center"><?php echo l('Billable bookings'); ?></th>
				<?php } ?>
				<th class="text-right"><a href="https://en.wikipedia.org/wiki/RevPAR"><?php echo l('revpar'); ?></a></th>
				<th class="text-right"><a href="https://en.wikipedia.org/wiki/Average_daily_rate"><?php echo l('adr'); ?></a></th>
				<th class="text-right"><?php echo l($this->default_room_singular).' '.l('Charges Before Taxes',true) ; ?></th>
				<th class="text-right"><?php echo l('all_charges'); ?><span class="hidden-print"> <?php echo l('including_taxes'); ?> </span></th>
				<th class="text-right"><?php echo l('all_payments'); ?></th>
				<th class="text-right"><?php echo l('balance'); ?></th>
			</tr>
		</thead>
		
		<tbody id="report-content">
			
		</tbody>

		<tfoot>
			<tr>
				<td class="text-right "><strong><?php echo l('total'); ?>:</strong></td>
				<td class="text-center">
					<span id='monthly-booking-count-total'></span>
					(<span id='monthly-occupancy-rate-total'></span>%)
					
				</td>
				<td class="text-right" id="monthly-revPAR-average"></td>
				<td class="text-right" id="monthly-ADR-average"></td>
				<td class="text-right" id='monthly-room-charge-total'></td>
				<td class="text-right" id='monthly-charge-total'></td>
				<td class="text-right" id="monthly-payment-total">
					
				</td>
				<td class="text-right" id='monthly-balance-total'>
					
				</td>
			</tr>
		</tfoot>
	</table>
	</div>
	<!--<a href="<?php echo base_url()."report/download_monthly_report_csv_export/$date.csv"; ?>">Download CSV Export</a>-->
</div></div></div>