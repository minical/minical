<input id="submit" type="hidden" value="<?php echo (isset($submit))?$submit:''; ?>" />



<div class="app-page-title">
    <div class="page-title-wrapper head-fix-wep">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-users text-success"></i>
            </div>
            <?php echo l('accounting');?> 
            <!-- <a href=<?php echo base_url()."customer/create_new_customer"; ?> class="btn btn-primary">
			Create new customer 	-->
		<!-- </a> -->
         

        </div>
        <div class="page-title-actions">
        	<a id="create-new-customer" class="btn btn-primary"><?php echo l('create_new_customer'); ?></a>
        </div>
    </div>
  </div>




<div class="main-card mb-3 card">
    <div class="card-body">

<div class="panel panel-default hidden-print">
	<div class="panel-body">
		<div class="form-inline hidden-print">
			<form method="get" action="<?php 
					echo base_url()."customer/show_customers/";
				?>" autocomplete="off" class="form-group form-wep">
				
				<input class="form-control m-2" name='search_query' type='text' size='15' value="<?php echo isset($search_query)?$search_query:''; ?>" placeholder="<?=l('Search Keywords', true);?>"/>
				
				<select class="form-control m-2" name="customer_type_id">
					<option value=''><?php echo l('all_customer_types'); ?></option>
					<?php
						foreach ($customer_types as $customer_type):
					?>
							<option value="<?php echo $customer_type['id']; ?>" <?php if ($customer_type_id == $customer_type['id']) echo "SELECTED"; ?>>
								<?php echo $customer_type['name']; ?>
							</option>
					<?php
						endforeach;
					?>
				</select>
				

				<input type="checkbox" class="m-2"  name="show_deleted" id="show_deleted" value="checked"
				<?php 
					$show_deleted = isset($show_deleted)?$show_deleted:'';
					if ($show_deleted == 'checked') // if the room is paid by 3rd party
						echo "checked='true'";
				?> />
				<?php echo l('show_deleted_customers'); ?>
               
                <button type='submit' name='submit' value='search' id="search_submit" class="btn btn-light m-2"><?php echo l('search_customers'); ?></button>
				
                
                
                <div class="pull-right  pull-right-wep">
                   <span class="h-fix-wep"><?=l("Display");?>  <?=l("Results");?></span>
                    <select  class="form-control per_page m-2" id="search_submit" name="per_page">
                        <option value='30' <?php echo (isset($_GET['per_page']) && $_GET['per_page'] == 30) ? 'selected' : ''; ?>>30</option>
                        <option value='50' <?php echo (isset($_GET['per_page']) && $_GET['per_page'] == 50) ? 'selected' : ''; ?>>50</option>
                        <option value='100' <?php echo (isset($_GET['per_page']) && $_GET['per_page'] == 100) ? 'selected' : ''; ?>>100</option>
                    </select>
                   
                </div>
				<span class="h-fix-wep"><?=l("Selling Date");?>:</span> 
				<input class="form-control m-2 search_submit-wep" id="search_date" name='date' type='text' size='10' value="<?=$selling_date;?>" placeholder="<?=l('Selling Date', true);?>"/>
			</form>
			
		  		
				
			
		</div><!-- /.form-inline -->
	</div>
</div>
<div class="table-responsive">

<table class="table table-hover">
	<tr>
		<th><?php echo l('id'); ?></th>				
		<th ><a href="<?php echo base_url()."customer/set_order_by/customer_name"; ?>"><?php echo l('name'); ?></a></th>				
		<th><?php echo l('phone'); ?></th>
                <th><?php echo l('status'); ?></th>
		<th class="text-right"><a href="<?php echo base_url()."customer/set_order_by/last_check_out_date"; ?>"><?php echo l('last_check_out'); ?></a></th>	
		<th class="text-right"><a href="<?php echo base_url()."customer/set_order_by/charge_total"; ?>"><?php echo l('charge_total'); ?></a></th>	
		<th class="text-right"><a href="<?php echo base_url()."customer/set_order_by/payment_total"; ?>"><?php echo l('payment_total'); ?></a></th>	
		<th class="text-right"><a href="<?php echo base_url()."customer/set_order_by/balance"; ?>"><?php echo l('balance'); ?></a></th>	
		<th></th>
	</tr>

	<?php 
		if(isset($rows)) 
			foreach ($rows as $r) : 
				// if customer name is empty
				if (!$r->customer_name)
					$r->customer_name = '[not entered]';
	?>
				<tr class='customer-tr' name='<?php echo $r->customer_id; ?>'>
					<td>
						<?php
							echo $r->customer_id;									
						?>
					
					</td>
					<td>
						<a href="<?php echo base_url().'customer/history/'.$r->customer_id; ?>">
							<?php
								echo $r->customer_name;					
							?>
							<?php if ($r->is_deleted): ?>
								<span class="deleted-customer"> - <?php echo l('DELETED', true); ?></span>
							<?php endif; ?>
						</a>
					</td>
					<td>
						<?php echo $r->phone; ?>
					</td>
                    <td>
                        <?php                                             
                            if($r->customer_type_id == VIP)
                            {
                                echo l('VIP', true);
                            }
                            else if($r->customer_type_id == BLACKLIST)
                            {
                                echo l('Blacklist', true);
                            }
                            else
                            {
								foreach ($customer_types as $customer_type):
									if($customer_type['id'] == $r->customer_type_id){
										echo $customer_type['name'];
									}
								endforeach;
                            }
                        ?>
					</td>
					<td class='text-right' onclick="">
						<?php echo ($this->enable_hourly_booking == 1 ? ($r->last_check_out_date ? date('Y-m-d h:i A', strtotime($r->last_check_out_date)) : '') : ($r->last_check_out_date ? date('Y-m-d', strtotime($r->last_check_out_date)) : ''));  ?>
					</td>	
					<td class='text-right' onclick="">
						<?php echo number_format($r->charge_total, 2, ".", ",");  ?>
					</td>	
					<td class='text-right' onclick="">
						<?php echo number_format($r->payment_total, 2, ".", ",");  ?>
					</td>	
					<td class='text-right' onclick="">
						<?php 
							echo number_format($r->balance, 2, ".", ",");
						?>
					</td>
					<td class="center delete-td">
						<div class="dropdown pull-right">
							<button class="btn btn-light btn-xs dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
							<!-- 	<span class="caret"></span> -->
							</button>
							<ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
								<li role="presentation">
									<a role="menuitem" tabindex="-1" class="customer-profile" id="<?php echo $r->customer_id; ?>">
										<?php echo l('show_profile'); ?>
									</a>
								</li><li role="presentation">
									<a role="menuitem" tabindex="-1" href="<?php echo base_url().'customer/history/'.$r->customer_id; ?>">
										<?php echo l('show_history'); ?>
									</a>
								</li>
								<li role="presentation">
									<a role="menuitem" class="delete_customer" showdelete="<?php echo $show_deleted; ?>" name="<?php echo $r->customer_id; ?>" tabindex="-1" href="#">
										<?php echo l('delete_customer'); ?>
									</a>
								</li>
							</ul>
						</div>
					</td>	
				</tr>
	<?php 
			endforeach;				
	?> 
                <tr>
                    <?php $charge_total = $payment_total = $balance_total = 0; 
                    foreach ($rows as $key => $value)
                    {
                        $arr = json_decode(json_encode($value), true);
                        if (array_key_exists('charge_total', ($arr)))
                        {
                            $charge_total = $charge_total + $value->charge_total;
                        }
                        
                        if (array_key_exists('payment_total', ($arr)))
                        {
                            $payment_total = $payment_total + $value->payment_total;
                        }
                        
                        if (array_key_exists('balance', ($arr)))
                        {
                            $balance_total = $balance_total + $value->balance;
                        }
                    } 
                    ?>
                    <td colspan="5" style="text-align: right;"><?php echo l('Total', true); ?>:</td>
                    <td class="text-right"><?php echo number_format($charge_total, 2, ".", ",") ?></td>
                    <td class="text-right"><?php echo number_format($payment_total, 2, ".", ",") ?></td>
                    <td class="text-right"><?php echo number_format($balance_total, 2, ".", ",") ?></td>
                </tr>
</table>
</div>

<div class="panel panel-default">
	<div class="panel-body text-center">
		<h4>
			<?php echo $this->pagination->create_links(); ?>
		</h4>
		<br/>
		<?php $q_string = ($_SERVER["QUERY_STRING"] != '') ? ("?".$_SERVER["QUERY_STRING"]) : ''; ?>
		<a href="<?php echo base_url()."customer/download_csv_export".$q_string; ?>"><?php echo l('download_csv_export'); ?></a>
	</div>
</div>
</div></div>