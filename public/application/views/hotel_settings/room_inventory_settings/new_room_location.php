    <tr class="room-tr" id="<?php echo $room_location['id'] ?>">
        <td>
            <input name="room-name" class="form-control" type="text" value="<?php echo $room_location['location_name']; ?>"/>
        </td>
        <td><button class="delete-room-button btn btn-danger"><?php echo l('Delete', true); ?></button></td>
    </tr>
