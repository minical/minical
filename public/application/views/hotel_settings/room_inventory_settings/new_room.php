<?php
if (isset($room_limit)) {
   ?>
<script> 
    var limitMsg = '<?php echo $room_limit; ?>';
    alert(limitMsg);
</script>
<?php
} else {
    ?>
    <tr class="room-tr" id="<?php echo $room['room_id'] ?>" style="background: #f2f9ff;">
        <td>
            <input name="room-name" class="form-control" type="text" value="<?php echo $room['room_name']; ?>"/>
        </td>
        <td>
            <?php
            if (isset($room_types)):
                ?>
                <select name="room-type" class="form-control">
                    <option><?php echo l('Not selected', true); ?></option>
                    <?php
                    foreach ($room_types as $room_type) {
                        echo "<option value='" . $room_type['id'] . "' ";
                        if(isset($room_type_id) && $room_type_id != '')
                        {
                            if ($room_type['id'] == $room_type_id) {
                                echo " SELECTED ";
                            }
                        }
                        else
                        {
                            if ($room_type['id'] == $room['room_type_id']) {
                                echo " SELECTED ";
                            }
                        }
                        
                        echo ">" . $room_type['name'] . "</option>\n";
                    }
                    ?>
                </select>
                    <?php
                endif;
                ?>
        </td>
        <td>
            <select name="room-group-id" class="form-control">
    <?php for ($i = 0; $i < 15; $i++): ?>
                    <option value="<?php echo $i ?>">
                    <?php echo $i ?>
                    </option>
                    <?php endfor; ?>
            </select>
        </td>
        <td>
			<?php
				if (isset($location)):
			?>
					<select name="room-location" class="form-control">
						<option><?php echo l('Not selected', true); ?></option>
                        <?php
							foreach($location as $key => $locations)
							{
								echo "<option value='".$locations['id']."' ";
								if ($locations['id'] == $room['location_id'])
								{
									echo " SELECTED=SELECTED ";
								}
								echo ">".$locations['location_name']."</option>\n";
                        	}
						?>
						
					</select>
			<?php
				endif;
			?>
		</td>
        <td>
			<?php
				if (isset($floor)):
			?>
					<select name="room-floor" class="form-control">
						<option><?php echo l('Not selected', true); ?></option>
                        <?php
							foreach($floor as $key => $floors)
							{
								echo "<option value='".$floors['id']."' ";
								if ($floors['id'] == $room['floor_id'])
								{
									echo " SELECTED=SELECTED ";
								}
								echo ">".$floors['floor_name']."</option>\n";
							}
						?>
						
					</select>
			<?php
				endif;
			?>
		</td>
        <td>
            <input type="text" class="form-control" name="sort_order" value="<?php if($room['sort_order'] != 'NULL') echo $room['sort_order']; ?>">
        </td>
        <td class="text-center">
            <div class="checkbox">
                <label>
                    <input type="checkbox" class="can-be-sold-online-checkbox" autocomplete="off"
                        <?php
                        if ($room['can_be_sold_online'] == 1) {
                            echo 'checked="checked"';
                        }
                        ?>
                    />
                </label>
            </div>
        </td>
        <td><button class="delete-room-button btn btn-danger" style="width:78px;"><?=l("Hide");?></button></td>
    </tr>
    <?php
}
?>