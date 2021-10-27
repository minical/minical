<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-keypad text-success"></i>
            </div>
            <?php echo l('Extensions'); ?>
        </div>
    </div>
</div>

<?php $is_favourite = false; if(isset($extensions) && $extensions){ 
foreach ($extensions as $extension){
    if(isset($extension['is_favourite']) && $extension['is_favourite']){
        $is_favourite = true;
    }
} } if($is_favourite) { ?>
<div><h4><?php echo l('Favourites');?></h4></div>
<?php } ?>
<div class="main-card  <?php echo $is_favourite ? 'mb-5' : ''; ?> ">
    <div class="extension-card">

        <?php
        //Columns must be a factor of 12 (1,2,3,4,6,12)
        $numOfCols = 3;
        $rowCount = 0;
        $bootstrapColWidth = 12 / $numOfCols;
        ?>
        <div class="row">
            <?php if(isset($extensions) && $extensions) :
                foreach ($extensions as $extension){
                    if(isset($extension['is_favourite']) && $extension['is_favourite']){
                        ?>
                        <div class="col-md-<?php echo $bootstrapColWidth; ?>" style="padding-right: 0px">
                            <div class="extension_block">
                                <div class="main-extension">
                                    <div class="icon">
                                        <img src="<?php echo (isset($extension['image_name']) && $extension['image_name']) ?  base_url().'/images/'.$extension['image_name'] : ''?>" style="width: 30px;height: 30px">
                                    </div>
                                    <div class="extension-content">
                                        <b style="font-size: 12px;"><?php
                                            $name = $extension['extension_name'];
                                            $extension_name = str_replace("_"," ",$name);
                                            echo ucwords(l($extension_name, true)); ?>

                                            <span>
                                                <?php if(isset($extension['is_favourite']) && $extension['is_favourite'] == 1){?>
                                                    <a href="javascript:" data-value="0" class="fa fa-heart pull-right favourite-button" name="<?php echo $extension['extension_folder_name']; ?>" style="font-size: 15px;background-color: white;border: none;color:red; "></a>
                                                <?php }else{?>
                                                    <a href="javascript:" data-value= "1" class="fa fa-heart-o pull-right favourite-button" name="<?php echo $extension['extension_folder_name']; ?>" style="font-size: 15px;background-color: white;border: none; color: grey; "></a>
                                                <?php } ?>
                                            </span>
                                        </b>

                                        <div>
                                            <?php if(isset($extension['is_admin_module']) && $extension['is_admin_module']){ ?>
                                                <span style="font-size: 11px;color: gray;font-weight: 500;padding: 0px 0 5px;">VENDOR ONLY</span>
                                            <?php } ?>
                                            <p class="extension-discription" ><?php echo  strlen($extension['description']) > 200 ? substr($extension['description'],0,200)."..." : $extension['description']; ?>
                                                <?php if(isset($extension['marketplace_product_link']) && $extension['marketplace_product_link']){ ?>
                                                    <a href="<?php echo (isset($extension['marketplace_product_link']) && $extension['marketplace_product_link'] ? $extension['marketplace_product_link']: "")?>" style="font-size: 14px"><?php echo l('more');?></a>
                                                <?php } ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="features-div-padding">
                                    <div class="checkbox checbox-switch switch-primary" style="margin-bottom: 5px;margin-top: 5px">
                                        <a href="<?php if(isset( $extension['setting_link']) && $extension['setting_link'] ){
                                            echo $extension['setting_link'];
                                        } else {
                                            echo '';}?>"
                                           class="ml-4"
                                           style="font-size: 25px;"
                                           name="<?php echo $extension['extension_folder_name']; ?>"
                                           data-status="<?php echo $extension['is_active']; ?>">

                                            <?php if($extension['is_active'] == 1 && $extension['setting_link'] !=null && $this->user_permission != 'is_employee'){
                                                echo '<i class="pe-7s-config text-primary"></i>';
                                            }else{
                                                echo '';
                                            } ?>
                                        </a>

                                        <a href="<?php if(isset( $extension['view_link']) && $extension['view_link'] ){
                                            echo $extension['view_link'];
                                        }else{
                                            echo '';}?>"
                                           class=""
                                           style="font-size: 25px;"
                                           name="<?php echo $extension['extension_folder_name']; ?>"
                                           data-status="<?php echo $extension['is_active']; ?>">

                                            <?php if($extension['is_active'] == 1 && $extension['view_link'] !=null){
                                                echo '<i class="pe-7s-look  text-primary"></i>';
                                            }?>
                                        </a>
                                        <label class="extension-box" style="padding-right: 1.5rem !important;">
                                            <input type="checkbox" class="extension-status-button" data-status="<?php echo $extension['is_active']; ?>" name="<?php echo $extension['extension_folder_name']; ?>"
                                                <?= $extension['is_active'] ? 'checked=checked' : ''; ?>/>
                                            <?php if($this->user_permission != 'is_employee'){ ?>
                                                <span></span>
                                            <?php } ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        $rowCount++;
                        if($rowCount % $numOfCols == 0) echo '</div><div class="row">';
                    }}
                ?>
            <?php else : ?>
                <h3><?php echo l('No extensions have been found.', true); ?></h3>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row form-group">
    <div class="col-sm-3">
        <h4><?php echo l('Extensions');?></h4></div>
    <div class="col-sm-9">
        <div class="form-inline pull-right extension-filter">
            
                <label><?php echo l('status');?></label>
                <select name="status" id="status" class="form-control" onchange="getval(this);">
                    <option value="all"><?php echo l('All');?></option>
                    <option value="active"><?php echo l('Active');?></option>
                    <option value="not_active"><?php echo l('not-Active');?></option>
                </select>
                <label><?php echo l('categories');?></label>
                <select name="category" id="category" class="form-control" onchange="getcat(this);">
                    <option value="all"><?php echo l('All');?></option>
                    <option value="channel_manager"><?php echo l('Channel Manager');?></option>
                    <option value="check_in_automation"><?php echo l('Check-in Automation');?></option>
                    <option value="marketing"><?php echo l('Marketing');?></option>
                    <option value="online_booking_engine"><?php echo l('Online Booking Engine');?></option>
                    <option value="payment_process"><?php echo l('Payment Process');?></option>
                    <option value="pos"><?php echo l('POS');?></option>
                    <option value="promo_discount"><?php echo l('Promo/Discount');?></option>
                    <option value="yield_management"><?php echo l('Yield Management');?></option>
                </select>
               
               <!--  <input type="" name="" class="form-control" placeholder="search">
                <button type="submit" class="btn btn-light"><?php echo l('search');?></button> -->

                <input type="text" name="search_pos" placeholder="<?php echo l('Search Extensions', true); ?>" class="form-control search_ext" style="max-width: 200px">
            
        </div>
    </div>
</div>


<div class="main-card mb-3">
    <div class="extension-card">

        <?php
        //Columns must be a factor of 12 (1,2,3,4,6,12)
        $numOfCols = 3;
        $rowCount = 0;
        $bootstrapColWidth = 12 / $numOfCols;
        ?>
        <div class="extension_view" >
            <div class="row">
                <?php if(isset($extensions) && $extensions) :
                    foreach ($extensions as $extension){
                        ?>
                        <div class="col-md-<?php echo $bootstrapColWidth; ?>" style="padding-right: 0px">
                            <div class="extension_block">
                                <div class="main-extension">
                                    <div class="icon">

                                        <img src="<?php echo (isset($extension['image_name']) && $extension['image_name']) ?  base_url().'/images/'.$extension['image_name'] : ''?>" style="width: 30px;height: 30px">
                                    </div>
                                    <div class="extension-content">
                                        <b style="font-size: 12px;"><?php
                                            $name = $extension['extension_name'];
                                            $extension_name = str_replace("_"," ",$name);
                                            echo ucwords(l($extension_name, true)); ?>

                                            <span>
                                                <?php if(isset($extension['is_favourite']) && $extension['is_favourite'] == 1){?>
                                                    <a href="javascript:" data-value="0" class="fa fa-heart pull-right favourite-button" name="<?php echo $extension['extension_folder_name']; ?>" style="font-size: 15px;background-color: white;border: none;color:red; "></a>
                                                <?php }else{?>
                                                    <a href="javascript:" data-value= "1" class="fa fa-heart-o pull-right favourite-button" name="<?php echo $extension['extension_folder_name']; ?>" style="font-size: 15px;background-color: white;border: none; color: grey; "></a>
                                                <?php } ?>
                                            </span>
                                        </b>

                                        <div>
                                            <?php if(isset($extension['is_admin_module']) && $extension['is_admin_module']){ ?>
                                                <span style="font-size: 11px;color: gray;font-weight: 500;padding: 0px 0 5px;">VENDOR ONLY</span>
                                            <?php }
                                            //elseif(isset($extension['is_vendor_module']) && $extension['is_vendor_module']){ ?>
                                            <!-- <span style="font-size: 11px;color: gray;font-weight: 500;padding: 0px 0 5px;">VENDOR ONLY</span> -->
                                            <?php //}?>

                                            <p class="extension-discription" style= "margin-bottom: 0px"><?php echo 

                                            strlen($extension['description']) > 200 ? substr($extension['description'],0,200)."..." : $extension['description']; ?>
                                            <?php if(isset($extension['marketplace_product_link']) && $extension['marketplace_product_link']){ ?>

                                            <a href="<?php echo (isset($extension['marketplace_product_link']) && $extension['marketplace_product_link'] ? $extension['marketplace_product_link']: "")?>" style="font-size: 14px"><?php echo l('more');?></a>
                                        <?php }?>
                                        </p>
                                        </div>

                                    </div>
                                </div>

                                <div class="features-div-padding">

                                    <div class="checkbox checbox-switch switch-primary" style="margin-bottom: 5px;margin-top: 5px">
                                        <a href="<?php if(isset( $extension['setting_link']) && $extension['setting_link'] ){
                                            echo $extension['setting_link'];
                                        }else{
                                            echo '';}?>"
                                           class="ml-4"
                                           style="font-size: 25px;"
                                           name="<?php echo $extension['extension_folder_name']; ?>"
                                           data-status="<?php echo $extension['is_active']; ?>">

                                            <?php if($extension['is_active'] == 1 && $extension['setting_link'] !=null && $this->user_permission != 'is_employee'){
                                                echo '<i class="pe-7s-config text-primary"></i>';
                                            }else{
                                                echo '';
                                            } ?>
                                        </a>

                                        <a href="<?php if(isset( $extension['view_link']) && $extension['view_link'] ){
                                            echo $extension['view_link'];
                                        }else{
                                            echo '';}?>"
                                           class=""
                                           style="font-size: 25px;"
                                           name="<?php echo $extension['extension_folder_name']; ?>"
                                           data-status="<?php echo $extension['is_active']; ?>">

                                            <?php if($extension['is_active'] == 1 && $extension['view_link'] !=null){
                                                echo '<i class="pe-7s-look  text-primary"></i>';
                                            }?>
                                        </a>
                                        <label class="extension-box" style="padding-right: 1.5rem !important;">
                                            <input type="checkbox" class="extension-status-button" data-status="<?php echo $extension['is_active']; ?>" name="<?php echo $extension['extension_folder_name']; ?>"
                                                <?= $extension['is_active'] ? 'checked=checked' : ''; ?>/>
                                            <?php if($this->user_permission != 'is_employee'){ ?>
                                                <span></span>
                                            <?php } ?>
                                        </label>
                                    </div>
                                </div>


                            </div>

                        </div>
                        <?php
                        $rowCount++;
                        if($rowCount % $numOfCols == 0) echo '</div><div class="row">';
                    }
                    ?>
                <?php else : ?>
                    <h3><?php echo l('No extensions have been found.', true); ?></h3>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>