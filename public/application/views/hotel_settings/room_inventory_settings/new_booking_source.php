     <tr class="booking-source-tr" id="<?php echo $booking_source['id'] ?>">
            <td>
                <input name="booking-source-name" class="form-control" type="text" value="<?php echo $booking_source['name']; ?>"/>
            </td>
            <td><button class="delete-booking-source-button btn btn-danger"><?php echo l('Delete', true); ?></button></td>
    </tr>