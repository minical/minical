<tr class="booking-source-tr" id="<?php echo $id; ?>">
	<td class="glyphicon_icon">
        <span class="grippy"></span>
        <input name="booking-source-name" class="form-control" type="text" value="<?php echo $name; ?>" maxlength="150" style="width:250px"/>
    </td>
    <td>
        <input name = "commission_rate" class="form-control" type="text" value="" maxlength="45" style="width:200px"/>
    </td>

    <!--<td>
        <input type="text" name="booking-source-sort-order" class="form-control" value="<?=$sort_order;?>" maxlength="3" style="width:100px">
    </td>-->

    <td>
        <div class="checkbox" id="<?php echo $id; ?>">
            <input type = "checkbox"  class="hide-booking-source-button"  style="margin-left: 0px!important;">
        </div>
    </td>
</tr>