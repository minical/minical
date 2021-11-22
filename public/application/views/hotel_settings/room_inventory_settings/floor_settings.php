


<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-home text-success"></i>
            </div>
            <?php echo l('floor')."   ".l('settings'); ?>
        </div>
        <div class="page-title-actions m-010">
          <button id="add-floor-button" class="btn btn-primary"><?php echo l('add_floor_location'); ?></button>
          <button id="save-all-floor-button" class="btn btn-default"><?php echo l('save_all'); ?></button>
        </div>
    </div>
</div>


<div class="main-card mb-3 card m-014">
    <div class="card-body">

<!-- Hidden delete dialog-->
<table class="table table-hover floor">
    <thead>
        <tr>
            <th colspan="2">
                <?php echo l('floor_name'); ?>
            </th>
            
        </tr>
    </thead>
    <tbody>
        <?php if (isset($room_location)) : foreach ($room_location as $room) : ?>
        <tr class="room-tr" id="<?php echo $room['id'] ?>">
            <td>
                <input name="room-name" class="form-control" type="text" value="<?php echo $room['floor_name']; ?>"/>
            </td>
            <td><button class="delete-floor-button btn btn-danger"><?php echo l('Delete'); ?></button></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
	<?php else : ?>
		<h3><?php echo l('No Floor(s) have been recorded.'); ?></h3>
	<?php endif; ?>
</table>

</div></div>
