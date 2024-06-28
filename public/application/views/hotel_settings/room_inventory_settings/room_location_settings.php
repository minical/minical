
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-home text-success"></i>
            </div>
            <?php echo l($this->default_room_singular, true).' '.l('Location Settings',true); ?>
        </div>
        <div class="page-title-actions m-010">
          <button id="add-room-location-button" class="btn btn-primary"><?php echo l('Add',true).' '.l($this->default_room_singular).' '.l('Location',true); ?></button>
          <button id="save-all-rooms-locations-button" class="btn btn-light"><?php echo l('save_all'); ?></button>
        </div>
    </div>
</div>


<div class="main-card mb-3 card m-014">
    <div class="card-body">

<!-- Hidden delete dialog-->
<table class="table table-hover room_locations">
    <thead>
        <tr>
            <th colspan="2">
                <?php echo l($this->default_room_singular).' '.l('Location Name',true); ?>
            </th>
            
        </tr>
    </thead>
    <tbody>
        <?php if (isset($room_location)) : foreach ($room_location as $room) : ?>
        <tr class="room-tr" id="<?php echo $room['id'] ?>">
            <td>
                <input name="room-name" class="form-control" type="text" value="<?php echo $room['location_name']; ?>"/>
            </td>
            <td><button class="delete-room-location-button btn btn-danger"><?php echo l('Delete', true); ?></button></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <?php else : ?>
        <h3><?php echo l('No',true).' '.l($this->default_room_singular).' '.l('Location(s) have been recorded',true); ?>.</h3>
    <?php endif; ?>
</table>

</div></div>
