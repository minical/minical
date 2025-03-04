
<div id="printable-container">

 <div class="app-page-title">
	<div class="page-title-wrapper">
		<div class="page-title-heading">
			<div class="page-title-icon">
				<i class="pe-7s-graph2 text-success"></i>
			</div>
			<?php echo l('Payment Report', true); ?>
			
		</div>
	</div>
  </div>

    </div>

    <div class="main-card mb-3 card">
	<div class="card-body">

	<div class="panel panel-default hidden-print">
		<div class="panel-body h4">
			<div class="form-inline">
				<div class="form-group col-md-4 col-lg-3 col-xs-12 col-sm-6 monthselectpicker" style="padding: 0;">
					<?php 
                        if(empty($dateRange))
                        {
                            $today = strtotime(date('Y-m-1', strtotime($date)));
                            $month_before_today = date ( 'Y-m-d' , strtotime ( '-1 month' , $today ) );
                            $month_after_today = date ( 'Y-m-d' , strtotime ( '+1 month' , $today ) );
						
                            echo "<a href='".base_url()."reports/ledger/show_monthly_payment_report/".$month_before_today."'> << </a>";
                            echo date("F, Y",strtotime($date)); 
                            echo "<a href='".base_url()."reports/ledger/show_monthly_payment_report/".$month_after_today."'> >> </a>";			
                        }
                    ?>
				</div> <!-- /.form-group -->
                <div class="col-md-6 col-lg-6 col-xs-12 col-sm-6" style="padding: 0;">
                   <?php echo l('Date Range', true); ?> <span style="color:red;">*</span> <div class="form-group"><input name="date_range" class="form-control date_range_picker" value="01/01/2015 - 01/31/2015"  placeholder="<?php echo l('Start Date', true); ?>" ></div>
                   <button class="show_payment_report btn btn-sm btn-success" type="button"><?php echo l('Submit', true); ?></button>
                </div>

                <?php if($this->is_nestpay_enabled || $this->is_nestpaymkd_enabled || $this->is_nestpayalb_enabled || $this->is_nestpaysrb_enabled): ?>
                <div class="col-md-1 col-lg-1 col-xs-12 col-sm-1" id="m-012">
                	<?php $last_segment = $this->uri->segment($this->uri->total_segments()); ?>
					<select name="currency-id" class="form-control currency_name" style="width: 200%;">
						<option value=''><?php echo l('All', true); ?></option>
						<?php 
							foreach($currencies as $currency)
							{
								echo "<option value='".$currency['currency_id']."' ";
								if ($last_segment == $currency['currency_id'])
								{
									echo " SELECTED=SELECTED ";
								}
								echo ">".$currency['currency_code']." (".$currency['currency_name'].")</option>\n";
							}
						?>
                    </select>
                </div>
            <?php endif; ?>

                <div class="col-md-2 col-lg-2 col-xs-12 col-sm-2" id="m-012">
                    <button id="printReportButton" class="btn btn-primary pull-right hidden-print"><span class="glyphicon glyphicon-print" title="Print Report"></span></button>
                    <?php $url_date = ($this->uri->segment(4) != '') ? $this->uri->segment(4) : ""; ?>       
                    <a style="margin: 0 10px;" href="<?php if($url_date != '//'){ echo base_url()."reports/ledger/download_payments_csv_export/".$url_date; } else { echo base_url()."reports/ledger/download_payments_csv_export/"; } ?>" class="btn btn-primary pull-right ">
                        <span title="Export to CSV" class="glyphicon glyphicon-download-alt"></span>
                    </a>
                </div>
			</div><!-- /.form-inline -->
		</div>
	</div>

	<?php
	
	// unset selling dates as we now only use 'date' column from database.
	// I was hoping to do this in database side of things, but mysql doesn't have a way to exclude one column
	foreach ($result as $key => $row)
	{
		unset($result[$key]['selling_date']);
	}
	echo '<div class="payment_report_table">';
	//Construct table
	if (isset($result[0])) {
		echo "<div class='table-responsive'><table class='table table-hover table-condensed'>";
		// table head
		echo '<thead><tr>';			
		$pi = 0; // keep track of column index. To prevent making date/shift_type_name into currency format
		$total = array();

		$p_total = 0;
		$show = false;

		foreach ($result as $r) {
			foreach ($r as $pi => $p) {
				if($pi == 'PayPal' && $pi != 'Selling Date') {
					$p_total += $p;
				}
			}
		}

		if($p_total > 0) {
			$show = true;
		}
		
		// construct the table's headers
		foreach ($result[0] as $column_key => $value)
		{
			if($show) {
				echo '<th';
				if ($column_key == 'Selling Date') // first column is for the date
					echo " id='date_td'";
				else {
					echo ' class="text-right"';
				}
				echo '>';
				echo l($column_key, true).'</th>';	
				$total[$column_key] = 0;
			} else {
				if($column_key != 'PayPal') {
					echo '<th';
					if ($column_key == 'Selling Date') // first column is for the date
						echo " id='date_td'";
					else {
						echo ' class="text-right"';
					}
					echo '>';
					echo l($column_key, true).'</th>';	
					$total[$column_key] = 0;
				}
			}	
		}
		echo '<th class="text-right">'.l("Total").'</th></tr></thead>';
		
		echo '<tbody>';
		$row_total = array();
		
		$ri = 0; // row index
		foreach ($result as $r)  {
			//print_r($r);
			echo "<tr>";
			$pi = 0; // keep track of column index. To prevent making date/shift_type_name into currency format
			$row_total[$ri] = 0;
			foreach ($r as $pi => $p)  {
				if($show) {
					if ($pi == 'Selling Date') { // pi represents column index
						// generate link to daily sale report
						echo "<td class='date_td'><a href='".base_url()."reports/ledger/show_daily_report/".$p."'>".$p."</a></td>";							
					} else { // not include date in total
						echo '<td class="text-right">'.number_format($p, 2, ".", ",").'</td>';
						$total[$pi] += $p;
						$row_total[$ri] += $p;
					}
				} else {
					if($pi != 'PayPal') {
						if ($pi == 'Selling Date') { // pi represents column index
							// generate link to daily sale report
							echo "<td class='date_td'><a href='".base_url()."reports/ledger/show_daily_report/".$p."'>".$p."</a></td>";							
						} else { // not include date in total
							echo '<td class="text-right">'.number_format($p, 2, ".", ",").'</td>';
							$total[$pi] += $p;
							$row_total[$ri] += $p;
						}
					}
				}
			}
			// column total
			echo '<td class="text-right">'.number_format($row_total[$ri], 2, ".", ",").'</td>';
			$ri++;
			echo '</tr>';
		}
		echo '<tr><td><br /></td></tr>';
		echo '</tbody>';
		
		
		// table footer
		echo '<tfoot><tr><td>'.l("Total").'</td>';
		$pi = 0; // keep track of column index. To prevent making date/shift_type_name into currency format			
		$column_total = 0;
		foreach ($result[0] as $key => $value)
		{
			//if($show) {
				if ($key != 'Selling Date' && $key != 'PayPal')
				{
					$column_total += $total[$key];
					echo '<td class="text-right">';
					echo number_format($total[$key], 2, ".", ",");	
					echo '</td class="text-right">';
				}
			//} 
		}
		echo '<td class="text-right">'.number_format($column_total, 2, ".", ",").'</td>';
		echo '</tr></tfoot>';
		echo '</table>'; 
	}
        echo '</div></div>';
	?>

	<!--Google Graph-->
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript">
	  google.load('visualization', '1', {packages: ['corechart']});
       </script>
	<script type="text/javascript">
	  function drawVisualization() {
		// Some raw data (not necessarily accurate)
		var rowData = 

			<?php
				echo "[['Date',";
				$columns = array_keys((array)$result[0]);
				// add columns (keys)
				for ($i = 1; $i < sizeof($columns); $i++) {
					echo "'".$columns[$i]."'";	
					if ($i != sizeof($columns)-1)
						echo ",";						
				}
				echo ", 'Total'],";
				//print_r($columns);
				$ri = 0;
				foreach ($result as $row) {
					echo "['".$row['Selling Date']."',";
					//print_r($row);
					for ($i = 1; $i < sizeof($columns); $i++) {
						echo number_format($row[$columns[$i]], 2, ".", "");
						if ($i != sizeof($columns)-1)
							echo ",";
					}		
					echo ", ".number_format($row_total[$ri], 2, ".", "")."],";
					$ri++;
				}
				echo "];";
			?>
			
		// Create and populate the data table.
		var data = google.visualization.arrayToDataTable(rowData);
	  
		// Create and draw the visualization.
		var ac = new google.visualization.ComboChart(document.getElementById('visualization'));
		ac.draw(data, {
		  title : '',
		  width: 920,
		  height: 300,
		  vAxis: {title: ""},
		  hAxis: {title: ""},
		  seriesType: "line",
		  series: {5: {type: "line"}}
		});
	  }

	  google.setOnLoadCallback(drawVisualization);
	</script>		
	<div id="visualization" style="width: 600px; height: 400px;"></div>		
</div></div>