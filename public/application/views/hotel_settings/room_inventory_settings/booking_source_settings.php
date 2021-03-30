<div class="page-header">
	<h2><?php echo l('Booking Source', true); ?></h2>

<button id="add-booking-source-button" class="btn btn-primary"><?php echo l('Add Booking Source', true); ?></button>
<button id="save-all-booking-source-button" class="btn btn-light"><?php echo l('Save All', true); ?></button>
</div>

<!-- Hidden delete dialog-->
<table class="table table-hover booking_source">
    <thead>
        <tr>
            <th colspan="2">
                <?php echo l('Booking Source Name', true); ?>
            </th>
        </tr>   
    </thead>
    <tbody>
        <?php if (isset($booking_sources)) : foreach ($booking_sources as $booking_source) : ?>
        <tr class="booking-source-tr" id="<?php echo $booking_source['id'] ?>">
            <td>
                <input name="booking-source-name" class="form-control" type="text" value="<?php echo $booking_source['name']; ?>"/>
            </td>
            <td><button class="delete-booking-source-button btn btn-danger"><?php echo l('Delete', true); ?></button></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
	<?php else : ?>
        <h3><?php echo l('No Booking Source(s) have been recorded.', true); ?></h3>
	<?php endif; ?>
</table>

