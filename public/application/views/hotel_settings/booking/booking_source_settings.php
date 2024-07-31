<!-- Hidden delete dialog-->		
<div id="confirm_delete_dialog" ></div>


<!-- Hidden delete dialog-->
<div id="confirm_delete_dialog" ></div>

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-notebook text-success"></i>
            </div>
            <?php echo l('Booking Source'); ?>
        </div>
    </div>
</div>

<div class="main-card card">
    <div class="card-body">


    <div class="table-responsive">
<table id="booking-source" class="table booking_source">
    <thead>
        <tr>
            <th> <?php echo l('Booking Source Name', true); ?></th>
            <th> <?php echo l('Commission Rate', true); ?></th>
            <!--<th> Sort Order</th>-->
            <th> <?php echo l('Hide', true); ?></th>
        </tr>
    </thead>
    <tbody id="sortable" >
	<?php if(isset($booking_sources)): ?>
	<?php 	foreach($booking_sources as $booking_source) : ?>
        
				<tr class="booking-source-tr" id="<?php echo $booking_source['id']; ?>">
                    <td class="glyphicon_icon"><span class="grippy"></span>
						<input name="booking-source-name" class="form-control" type="text"
                               <?php echo isset($booking_source['is_common_source']) && $booking_source['is_common_source'] ? 'disabled' : ''; ?>
                               value="<?php echo $booking_source['name']; ?>" maxlength="150" style="width:250px"/>
					</td>
<!--					<td>
						<div class="delete-booking-source-button btn btn-default">X</div>
					</td>-->
					<td>
						<input name="commission_rate" class="form-control" type="text" value="<?php echo $booking_source['commission_rate']; ?>" maxlength="45" style="width:200px"/>
					</td>
<!--                    <td>
                        <input type="text" name="booking-source-sort-order" class="form-control" value="<?=$booking_source['sort_order'];?>" maxlength="3" style="width:100px">
					</td>-->
                    <td>
                        <div class="checkbox">
                            <input type="checkbox" name="booking-source-hidden" class="hide-booking-source-button" <?php if($booking_source['is_hidden'] == '1') { ?> checked <?php } ?> style="margin-left: 0px!important;">
                        </div>
					</td> 
				</tr>
	<?php endforeach; ?>
    </tbody>
	<?php else : ?>	
	<h3><?php echo l('No Booking Source(s) have been found.', true); ?></h3>
	<?php endif; ?>
</table>
    </div>
<br />
<button id="add-booking-source-button" class="btn btn-light"><?php echo l('Add Booking Source', true); ?></button>
<button id="save-all-booking-source-button" class="btn btn-primary"><?php echo l('save_all', true); ?></button>
</div></div>