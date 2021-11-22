    <tr class="room-tr" id="<?php echo $floor['id'] ?>">
        <td>
            <input name="room-name" class="form-control" type="text" value="<?php echo $floor['floor_name']; ?>"/>
        </td>
        <td><button class="delete-floor-button btn btn-danger"><?php echo l('Delete', true); ?></button></td>
    </tr>
