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
    <tr class="feature-tr" id="<?php echo $feature['feature_id'] ?>">
        <td>
            <input name="feature-name" class="form-control" type="text" value="<?php echo $feature['feature_name']; ?>"/>
        </td>
        <td  class="text-center">
            <div class="checkbox">
                <label>
                    <input type="checkbox" class="can-be-sold-online-checkbox" autocomplete="off"
                        <?php
                        if ($feature['show_on_website'] == 1) {
                            echo 'checked="checked"';
                        }
                        ?>
                    />
                </label>
            </div>
        </td>
        <td><button class="delete-feature-button btn btn-danger"><?php echo l('Delete', true); ?></button></td>
    </tr>
    <?php
}
?>