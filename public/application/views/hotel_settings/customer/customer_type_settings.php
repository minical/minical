
<!-- Hidden delete dialog-->		
<div id="confirm_delete_dialog" ></div>

<div id="save_all_dialog" ></div>


<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-notebook text-success"></i>
            </div>
            <?php echo l('customer_types'); ?>
        </div>
    </div>
</div>

<div class="main-card card">
    <div class="card-body">

<div class="table-responsive">
<table id="customer-types" class="table">
	<tr>
		<th><?php echo l('customer_type_name'); ?></th>
		<?php if($this->is_loyalty_program): ?>
			<th><?php echo l('Room Types'); ?></th>
			<th><?php echo l('Rate Plans'); ?></th>
		<?php endif; ?>
		<th><?php echo l('delete'); ?></th>
	</tr>
    <tbody id="sortable" >
	<?php if(isset($customer_types)): ?>
	<?php 	foreach($customer_types as $customer_type) : ?>	
				<tr class="customer-type-tr" id="<?php echo $customer_type['id']; ?>">
					<td class="glyphicon_icon"><span class="grippy"></span>
						<input name="customer-type-name" class="form-control" type="text"
                        <?php echo isset($customer_type['is_common_type']) && $customer_type['is_common_type'] ? 'disabled' : ''; ?>
                        value="<?php echo $customer_type['name']; ?>" maxlength="45" style="width:250px;"/>
                                                
					</td>
					<?php if($this->is_loyalty_program && !(isset($customer_type['is_common_type']) && $customer_type['is_common_type'])): ?>
						<td>
		                    <?php
		                    if (isset($room_types)):
		                        ?>
		                        <select name="room-type" class="form-control room-type">
		                            <option value="0"><?php echo l('Not selected', true); ?></option>
		                            <?php
		                            foreach($room_types as $room_type)
		                            {
		                            	echo "<option value='".$room_type['id']."' ";
		                            	if (isset($room_rates)):
			                            	foreach ($room_rates as $key => $value) {

			                            		$option_value = json_decode($value['option_value'], true);

				                                
				                                if ($room_type['id'] == $option_value['room_type_id'] && $customer_type['id'] == $option_value['customer_type_id'])
				                                {
				                                    echo " SELECTED=SELECTED ";
				                                }
				                            }
				                        endif;
	                                	echo ">".$room_type['name']."</option>\n";
		                            }
		                            ?>
		                        </select>
		                        <?php
		                    endif;
		                    ?>
		                </td>
		                <td>
		                    <?php
		                    if (isset($rate_plans)):
		                        ?>
		                        <select name="rate-plan" class="form-control rate-plan">
		                            <option value="0"><?php echo l('Not selected', true); ?></option>
		                            <?php
		                            foreach($rate_plans as $rate_plan)
		                            {
		                                echo "<option value='".$rate_plan['rate_plan_id']."' ";
		                                if (isset($room_rates)):
			                                foreach ($room_rates as $key => $value) {

			                            		$option_value = json_decode($value['option_value'], true);

				                                
				                                if ($rate_plan['rate_plan_id'] == $option_value['rate_plan_id'] && $customer_type['id'] == $option_value['customer_type_id'])
				                                {
				                                    echo " SELECTED=SELECTED ";
				                                }
				                            }
				                        endif;
		                                echo " data-room_type_id='".$rate_plan['room_type_id']."'>".$rate_plan['rate_plan_name'] . "</option>\n";
		                            }
		                            ?>
		                        </select>
		                        <?php
		                    endif;
		                    ?>
		                </td>
	            	<?php endif; ?>
					<td>
						<?php  if(!(isset($customer_type['is_common_type']) && $customer_type['is_common_type'])) { ?>
						<div class="delete-customer-type btn btn-light">X</div>
					<?php } ?>
					</td>
				</tr>
	<?php endforeach; ?>

	<?php else : ?>	
    </tbody>
	<h3><?php echo l('No customer_types have been found.', true); ?></h3>
	<?php endif; ?>
</table>
</div>
<br />
<button id="add-customer-type" class="btn btn-light"><?php echo l('add_customer_type'); ?></button>
<button id="save-all-customer-types-button" class="btn btn-primary"><?php echo l('save_all'); ?></button>
</div></div>