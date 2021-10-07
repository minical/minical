<div id="printable-container">

   <div class="app-page-title">
	<div class="page-title-wrapper">
		<div class="page-title-heading">
			<div class="page-title-icon">
				<i class="pe-7s-graph3 text-success"></i>
			</div>
			<?php echo l('Monthly Tax Report', true); ?>
			<div>
			
		</div>
	</div>
  </div>

    </div>

    <div class="main-card mb-3 card">
	<div class="card-body">

	<div class="panel panel-default hidden-print">
		<div class="panel-body h4">
			<div class="form-inline">
				<div class="form-group">
					<?php 
						$first_day_of_this_month = date('Y-m-01', strtotime($date));
						$today = strtotime($first_day_of_this_month);
						$month_before_today = date ( 'Y-m-d' , strtotime ( '-1 month' , $today ) );
						$month_after_today = date ( 'Y-m-d' , strtotime ( '+1 month' , $today ) );
						
						echo "<a href='".base_url()."reports/ledger/show_monthly_tax_report/".$month_before_today."'> << </a>";
						echo date("F, Y",strtotime($date)); 
						echo "<a href='".base_url()."reports/ledger/show_monthly_tax_report/".$month_after_today."'> >> </a>";			
					?>	  		
	
				</div> <!-- /.form-group -->
				<button id="printReportButton" class="btn btn-primary pull-right hidden-print"><span class="glyphicon glyphicon-print" title="Print Report"></span></button>
                <?php $url_date = ($this->uri->segment(4) != '') ? $this->uri->segment(4) : ""; ?>       
                <a style="margin: 0 10px;" href="<?php if($url_date != '//'){ echo base_url()."reports/ledger/download_taxes_csv_export/".$url_date; } else { echo base_url()."reports/ledger/download_taxes_csv_export/"; } ?>" class="btn btn-primary pull-right ">
                    <span title="Export to CSV" class="glyphicon glyphicon-download-alt"></span>
                </a>
            </div><!-- /.form-inline -->
		</div>
	</div>

	<?php
		if (isset($taxes)):
	?>
<div class="table-responsive">
		<table class='table table-hover table-condensed'>
			<thead>
				<tr>
					<th id='date_td'><?php echo l('Date', true); ?></th>
					<th class='text-right'><?php echo l('Charge Total (before taxes)', true); ?></th>
					<?php
						// Columns
						$total = array();
						foreach ($taxes as $date => $tax)
						{	// construct the table's headers
							foreach ($taxes[$date] as $column_key => $value)
							{
                                if($column_key != 'is_tax_exempt' && $column_key != 'tax_rate')
                                {
                                    echo '<th class="text-right">'.$column_key.'</th>';									
                                }
                                $total[$column_key] = 0;
							}
							break;
						}
					
					?>
					<th class="text-right"><?php echo l('Tax Total', true); ?></th>
                    <th class="text-right"><?php echo l('Tax Exempt Total', true); ?></th>
				</tr>
			</thead>

			<tbody>
							
				<?php
					// unset selling dates as we now only use 'date' column from database.
					// I was hoping to do this in database side of things, but mysql doesn't have a way to exclude one column		
					$ci = 0; // keep track of column index. To prevent making date/shift_type_name into currency format
					$charge_total = 0;
                    $tax_exempt_charge_total = 0;
					if (count($taxes) > 0)
					{                                                
						$ri = 0; // row index
							
						foreach ($taxes as $date => $r)  {
							if (isset($charges[$date]))
							{
								$charge = $charges[$date];
							}
							else
							{
								$charge = 0;
							}
                                                        
                            if (isset($tax_exempt_charges[$date]))
							{
								$tax_exempt_charge = $tax_exempt_charges[$date];
							}
							else
							{
								$tax_exempt_charge = 0;
							}
							echo "<tr>";
							echo "<td class='date_td'><a href='".base_url()."reports/ledger/show_daily_report/".$date."'>".$date."</a></td>";
							echo "<td class='text-right'>".number_format(($charge), 2, ".", ",")."</td>";
							
							$ci = 0; // keep track of column index. To prevent making date/shift_type_name into currency format
							$row_total[$ri] = 0;
							foreach ($r as $ci => $c)  {
                                if($ci != 'is_tax_exempt'  && $ci != 'tax_rate')
                                {
                                    echo '<td class="text-right">'.number_format($c, 2, ".", ",").'</td>';
                                    $total[$ci] += $c;
                                    $row_total[$ri] += $c;	
                                }								
							}
							$charge_total += $charge;                                                        
                            $tax_exempt_charge_total += $tax_exempt_charge;
							// column total
							echo '<td class="text-right">'.number_format($row_total[$ri], 2, ".", ",").'</td>';
                            echo "<td class='text-right'>".number_format($tax_exempt_charge, 2, ".", ",")."</td>";
							$ri++;
							echo '</tr>';
						}
						
						
					}
				?>
			</tbody>
			<tfoot>
				<tr>
					<td><?php echo l('Total', true); ?></td>
					<td class="text-right"><?php echo number_format($charge_total, 2, ".", ","); ?></td>
					<?php
						$row_total = array();
										
						// table footer
						$ci = 0; // keep track of column index. To prevent making date/shift_type_name into currency format			
						$column_total = 0;
                                                
						foreach ($taxes as $date => $tax)
						{
                                                    foreach ($taxes[$date] as $key => $value)
                                                    {                                                                                                                 
                                                            $column_total += $total[$key];
                                                            if($key != 'is_tax_exempt'  && $key != 'tax_rate')
                                                            {
                                                                echo '<td class="text-right">';
                                                                echo number_format($total[$key], 2, ".", ",");	
                                                                echo '</td class="text-right">';
                                                            }
                                                    }
                                                    break;
						}
						echo '<td class="text-right">'.number_format($column_total, 2, ".", ",").'</td>';                                               
					?>
                                        <td class="text-right"><?php echo number_format($tax_exempt_charge_total, 2, ".", ","); ?></td>
				</tr>
			</tfoot>
		</table>
</div>		
	<?php
		else:
			echo l("There's no tax set for this property. To Set your taxes, please click")." <a href='".base_url()."settings/accounting/tax_types'>".l('here')."</a>";
		endif;
	?>
</div></div></div>