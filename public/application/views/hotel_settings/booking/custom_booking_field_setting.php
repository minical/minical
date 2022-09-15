
<!-- Hidden delete dialog-->
<div id="confirm_delete_dialog" ></div>

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-notebook text-success"></i>
            </div>
            <?php echo l('Custom Booking Fields'); ?>
        </div>
    </div>
</div>

<div class="main-card card">
    <div class="card-body">
<div class="table-responsive">
<table id="booking-fields" class="table">
    <tr>
        <th><?php echo l('booking_field_name'); ?></th>
        <th class="text-center"><?php echo l('show_on_booking_form'); ?></th>
        <th class="text-center"><?php echo l('show_on_registration_form'); ?></th>
<!--        <th class="text-center">--><?php //echo l('show_on_inhouse_report'); ?><!--</th>-->
        <th class="text-center"><?php echo l('Show on Invoice'); ?></th>
        <th class="text-center"><?php echo l('Is a required field'); ?></th>
        <th class="text-center"><?php echo l('delete'); ?></th>
    </tr>

    <?php if(isset($booking_fields)): ?>
        <?php 	foreach($booking_fields as $booking_field) : ?>
            <tr class="booking-field-tr" id="<?php echo $booking_field['id']; ?>">
                <td>
                    <input name="name" class="form-control" type="text" value="<?php echo $booking_field['name']; ?>"
                        <?php echo isset($booking_field['is_common_field']) && $booking_field['is_common_field'] ? 'disabled' : ''; ?>
                           maxlength="45" style="width:250px"/>
                </td>
                <td class="text-center">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="show_on_booking_form" autocomplete="off"
                                <?php
                                if ($booking_field['show_on_booking_form'] == 1) {
                                    echo 'checked="checked"';
                                }
                                ?>
                            />
                        </label>
                    </div>
                </td>
                <td class="text-center">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="show_on_registration_card" autocomplete="off"
                                <?php
                                if ($booking_field['show_on_registration_card'] == 1) {
                                    echo 'checked="checked"';
                                }
                                ?>
                            />
                        </label>
                    </div>
                </td>
<!--                <td class="text-center">-->
<!--                    <div class="checkbox">-->
<!--                        <label>-->
<!--                            <input type="checkbox" name="show_on_in_house_report" autocomplete="off"-->
<!--                                --><?php
//                                if ($booking_field['show_on_in_house_report'] == 1 ) {
//                                    echo 'checked="checked"';
//                                }
//                                ?>
<!--                            />-->
<!--                        </label>-->
<!--                    </div>-->
<!--                </td>-->
                <td class="text-center">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="show_on_invoice" autocomplete="off"
                                <?php
                                if ($booking_field['show_on_invoice'] == 1 ) {
                                    echo 'checked="checked"';
                                }
                                ?>
                            />
                        </label>
                    </div>
                </td>
                <td class="text-center">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="is_required" autocomplete="off"
                                <?php
                                if ($booking_field['id'] == FIELD_NAME) {
                                    echo 'disabled checked="checked"';
                                }
                                if ($booking_field['is_required'] == 1) {
                                    echo 'checked="checked"';
                                }
                                ?>
                            />
                        </label>
                    </div>
                </td>
                <td class="text-center">
                    <?php if (!(isset($booking_field['is_common_field']) && $booking_field['is_common_field'])) { ?>
                        <div class="delete-booking-field btn btn-default">X</div>
                    <?php } ?>
                </td>
            </tr>
        <?php endforeach; ?>

    <?php else : ?>
        <h3><?php echo l('No custom booking fields found.', true); ?></h3>
    <?php endif; ?>
</table>
</div>
<br />
<button id="add_booking_field" class="btn btn-light"><?php echo l('add_booking_field'); ?></button>
<button id="save-all-booking-fields-button" class="btn btn-primary"><?php echo l('save_all'); ?></button>
</div></div>