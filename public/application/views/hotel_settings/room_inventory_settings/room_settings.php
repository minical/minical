

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-home text-success"></i>
            </div>
            <?php echo l($this->default_room_singular).' '.l('Settings',true); ?>
        </div>
        <div class="page-title-actions m-010">
          <button id="add-room-button" class="btn btn-primary btn-height"><?php echo l('Add').' '.l($this->default_room_singular); ?></button>
          <button id="save-all-rooms-button" class="btn btn-success btn-height"><?php echo l('save_all'); ?></button>

          <div class="custom-checkbox custom-control custom-control-inline">
              <input style="margin-left: 12px;" id="show-hidden-rooms" type="checkbox">
              <label for="show-hidden-rooms" class="control-label"><?php echo l('Show Hidden').' '.l($this->default_room_plural); ?></label>
          </div>

          
            <!--   <div class="form-group form-inline pull-right">
               
            </div> -->
        </div>
    </div>
</div>







<div class="main-card mb-3 card m-014">
    <div class="card-body">

        <!-- Hidden delete dialog-->
        <div class="table-responsive">
        <table class="table table-hover rooms">
            <thead>
                <tr>
                    <th>
                        <?php echo l($this->default_room_singular).' '.l('Name',true); ?>
                    </th>
                    <th>
                        <?php echo l($this->default_room_singular).' '.l('Type',true); ?>
                    </th>
            <th>
                <?php  if( isset($this->is_housekeeper_manage_enabled) && $this->is_housekeeper_manage_enabled == true){
                            echo l('Housekeeper Name');
                    }else{ echo l('housekeeping_group'); }

                     ?>
            </th>
            <th>
                <?php echo l('locations'); ?>
            </th>
            <th>
                <?php echo l('floors'); ?>
            </th> 
            <th width="100px">
                <?php echo l('sort_order'); ?>
            </th>
            <th class="text-center">
                <input type="checkbox" class="all-can-be-sold-online-checkbox" autocomplete="off" style="margin-right: 10px"/>
                <?php echo l('can_be_sold_online'); ?>
            </th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php if (isset($rooms)) : foreach ($rooms as $room) : ?>
            <tr class="room-tr <?php if($room['is_hidden'] == 1): echo 'hidden'; endif; ?>" id="<?php echo $room['room_id'] ?>" data-is-hidden="<?php echo $room['is_hidden']; ?>" >
                <td>
                    <input name="room-name" class="form-control" type="text" value="<?php echo $room['room_name']; ?>"/>
                </td>
                <td>
                    <?php
                    if (isset($room_types)):
                        ?>
                        <select name="room-type" class="form-control">
                            <option value="0"><?php echo l('Not selected', true); ?></option>
                            <?php
                            foreach($room_types as $room_type)
                            {
                                echo "<option value='".$room_type['id']."' ";
                                if ($room_type['id'] == $room['room_type_id'])
                                {
                                    echo " SELECTED=SELECTED ";
                                }
                                echo ">".$room_type['name']."</option>\n";
                            }
                            ?>
                            <option>--------------------</option>
                            <option value="create_new">[<?php echo l('Add New',true).' '.l($this->default_room_singular).' '.l('Type',true); ?>]</a></option>
                        </select>
                        <?php
                    endif;
                    ?>
                </td>
             <td>
                 <?php if( isset($this->is_housekeeper_manage_enabled) && $this->is_housekeeper_manage_enabled == true){?>
                <select name="room-group-id" class="form-control">
                    <?php foreach ($housekeeper_list as $housekeeper){ ?>
                        <option
                            value="<?php echo $housekeeper['id'] ?>"
                            <?php if ($room['group_id'] == $housekeeper['id']): ?>selected="selected" <?php endif ?>
                            >
                            <?php echo $housekeeper['first_name']." ".$housekeeper['last_name'] ?>
                        </option>
                    <?php } ?>
                <?php }else{ ?>
                        <select name="room-group-id" class="form-control">
                    <?php for ($i = 0; $i < 15; $i++): ?>
                        <option
                            value="<?php echo $i ?>"
                            <?php if ($room['group_id'] == $i): ?>selected="selected" <?php endif ?>
                            >
                            <?php echo $i ?>
                        </option>
                    <?php endfor; ?>
                    <?php } ?>
                </select>
            </td>
            <td>
                <?php
                    if (isset($location)):
                ?>
                        <select name="room-location" class="form-control">
                            <option value="0"><?php echo l('Not selected', true); ?></option>
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
                            <option value="0"><?php echo l('Not selected', true); ?></option>
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
            <?php if($room['is_hidden'] == 0){ ?>
               <td><button style="width:78px;" class="delete-room-button btn btn-danger"><?php echo l('Hide', true); ?></button></td>
           <?php } 
           else{ ?>
            <td><button class="restore-room-button btn btn-success"><?php echo l('Restore', true); ?></button></td>
        <?php  }
        ?>
    </tr>
<?php endforeach; ?>
<?php else : ?>
    <h3><?= l('No',true).' '.l($this->default_room_singular)."(s)".' '.l('have been recorded',true);?> </h3>
<?php endif; ?>
</tbody>
</table>
</div>
</div></div>

<div class="modal fade" id="add-multiple-rooms" tabindex="-1" role="dialog" 
aria-labelledby="myModalLabel" aria-hidden="true">
<div class="modal-dialog">
    <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header">
            <button type="button" class="close" 
            data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
            <span class="sr-only"><?=l("Close");?></span>
        </button>
        <h4 class="modal-title" id="myModalLabel">
            <?=l('Add',true).' '.l($this->default_room_plural);?>
        </h4>
    </div>

    <!-- Modal Body -->
    <div class="modal-body">                
        <form role="form">
            <div class="form-group">
                <label for="room_types"><?=l($this->default_room_singular).' '.l('Type',true);?>:</label>
                <?php
                if (isset($room_types)):
                   ?>
                   <select name="room_type" class="form-control">
                      <!--<option>Not selected</option>-->
                      <?php
                      foreach($room_types as $room_type)
                      {
                                echo "<option value='".$room_type['id']."' ";//                             
                                echo ">".$room_type['name']."</option>\n";
                            }
                          ?>
                      </select>
                      <?php
                  endif;
                  ?>
              </div>
              <div class="form-group">
                  <label for="room_count"><?=l('Number of',true).' '.l($this->default_room_plural);?></label>
                  <input type="number" class="form-control" name="room_count"
                  id="room_count" placeholder="<?=$this->default_room_singular.' '.l('Count',true);?>" value="1"/>
              </div> 
          </form>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer">                
        <button type="button" class="btn btn-primary" id="add_multiple_rooms">
            <?=l('Add',true).' '.l($this->default_room_plural);?>
        </button>
    </div>
</div>
</div>
</div>
