<!-- Hidden delete dialog-->
<div id="confirm_delete_dialog"></div>


<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-note2 text-success"></i>
            </div>
            <?php echo l('customer_fields'); ?>
        </div>
    </div>
</div>

<div class="main-card card">
    <div class="card-body">
        <div class="table-responsive">
        <table id="customer-fields" class="table">
            <tr>
                <th><?php echo l('customer_field_name'); ?></th>
                <th class="text-center"><?php echo l('show_on_customer_form'); ?></th>
                <th class="text-center"><?php echo l('show_on_registration_form'); ?></th>
                <th class="text-center"><?php echo l('show_on_inhouse_report'); ?></th>
                <th class="text-center"><?php echo l('Show on Invoice'); ?></th>
                <th class="text-center"><?php echo l('Is a required field'); ?></th>
                <th class="text-center"><?php echo l('delete'); ?></th>
            </tr>

            <?php if(isset($customer_fields)): ?>
            <?php 	foreach($customer_fields as $customer_field) : ?>
            <tr class="customer-field-tr" id="<?php echo $customer_field['id']; ?>">
                <td>
                    <input name="name" class="form-control" type="text" value="<?php echo $customer_field['name']; ?>"
                        <?php echo isset($customer_field['is_common_field']) && $customer_field['is_common_field'] ? 'disabled' : ''; ?>
                        maxlength="45" style="width:250px" />
                </td>
                <td class="text-center">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="show_on_customer_form" autocomplete="off" <?php
										if ($customer_field['show_on_customer_form'] == 1) {
											echo 'checked="checked"';
										}
									?> />
                        </label>
                    </div>
                </td>
                <td class="text-center">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="show_on_registration_card" autocomplete="off" <?php
										//echo (isset($customer_field['is_common_field']) && $customer_field['is_common_field']) ? 'disabled' : '';
                                        if ($customer_field['show_on_registration_card'] == 1) {
											echo 'checked="checked"';
										}
									?> />
                        </label>
                    </div>
                </td>
                <td class="text-center">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="show_on_in_house_report" autocomplete="off" <?php
                                        echo (isset($customer_field['name']) && ($customer_field['name'] == 'Name' || $customer_field['name'] == 'Customer Type' || $customer_field['name'] == 'Notes')) ? 'checked disabled ' : '';
										if ($customer_field['show_on_in_house_report'] == 1 ) {
											echo 'checked="checked"';
										}
									?> />
                        </label>
                    </div>
                </td>
                <td class="text-center">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="show_on_invoice" autocomplete="off" <?php
                                    echo (
                                            isset($customer_field['name']) && (
                                                    (
                                                        !(
                                                            $this->vendor_id == 3 || 
                                                            $this->vendor_id == 14
                                                        ) && 
                                                        $customer_field['name'] == 'Phone'
                                                    ) ||
                                                    (
                                                        !(
                                                            $this->vendor_id == 3 || 
                                                            $this->vendor_id == 14
                                                        ) && 
                                                        $customer_field['name'] == 'Email'
                                                    ) ||
                                                    $customer_field['name'] == 'Fax' ||
                                                    $customer_field['name'] == 'Address' ||
                                                    $customer_field['name'] == 'Address 2' ||
                                                    $customer_field['name'] == 'City' ||
                                                    $customer_field['name'] == 'Region' ||
                                                    $customer_field['name'] == 'Country' ||
                                                    $customer_field['name'] == 'Postal Code' ||
                                                    $customer_field['name'] == 'Name'
                                            )) ? 'checked disabled ' : '';
                                    if ($customer_field['show_on_invoice'] == 1 ) {
                                        echo 'checked="checked"';
                                    }
                                    ?> />
                        </label>
                    </div>
                </td>
                <td class="text-center">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="is_required" autocomplete="off" <?php
                                        if ($customer_field['id'] == FIELD_NAME) {
											echo 'disabled checked="checked"';
										}    
                                        if ($customer_field['is_required'] == 1) {
											echo 'checked="checked"';
										}
									?> />
                        </label>
                    </div>
                </td>
                <td class="text-center">
                    <?php if (!(isset($customer_field['is_common_field']) && $customer_field['is_common_field'])) { ?>
                    <div class="delete-customer-field btn btn-default">X</div>
                    <?php } ?>
                </td>
            </tr>
            <?php endforeach; ?>

            <?php else : ?>
            <h3><?php echo l('No customer field have been found.', true); ?></h3>
            <?php endif; ?>
        </table>
        </div>
        <br />
        <button id="add-customer-field" class="btn btn-light"><?php echo l('add_customer_field'); ?></button>
        <button id="save-all-customer-fields-button" class="btn btn-primary"><?php echo l('save_all'); ?></button>
    </div>
</div>