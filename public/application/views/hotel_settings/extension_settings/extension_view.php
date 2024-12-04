<div>

    <?php
    //Columns must be a factor of 12 (1,2,3,4,6,12)
    $numOfCols = 3;
    $rowCount = 0;
    $bootstrapColWidth = 12 / $numOfCols;
    ?>

    <div class="row">
        <?php if(isset($extensions) && $extensions) :
            foreach ($extensions as $extension){
                ?>
                <div class="col-md-<?php echo $bootstrapColWidth; ?>" style="padding-right: 0px">
                    <div class="extension_block">
                        <div class="main-extension">
                            <div class="icon">
                                <img src="<?php  if(isset($extension['logo']) && $extension['logo']){
                                            if(strpos($extension['logo'], 'http')  !== false){
                                                echo $extension['logo'];
                                            } else{
                                                echo base_url().'application/extensions/'.$extension['extension_folder_name'].'/'.$extension['logo'];
                                            }
                                        
                                } elseif(isset($extension['image_name']) && $extension['image_name']){
                                    if(strpos($extension['image_name'], 'http')  !== false){
                                        echo $extension['image_name'];
                                    } else {
                                        echo base_url().'/images/'. $extension['image_name'];
                                    }
                                    
                                } else {
                                    echo '';
                                } ?>" style="width: 30px;height: 30px">
                            </div>
                            <div class="extension-content">
                                <b style="font-size: 12px;">
                                    <a href="<?php echo (isset($extension['marketplace_product_link']) && $extension['marketplace_product_link'] && $this->is_partner_owner == 1 ? $extension['marketplace_product_link']: "javascript:")?>" style="font-size: 14px">
                                        <?php
                                    $name = $extension['extension_name'];
                                    $extension_name = str_replace("_"," ",$name);
                                    echo ucwords(l($extension_name, true)); ?>
                                    </a>
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

                                    <?php if((isset($extension['supported_in_minimal']) && $extension['supported_in_minimal'] == 1) || $this->company_partner_id != 0) { ?>
                                    <?php } else { ?>
                                        <span style="font-size: 10px;color: red;font-weight: 600;padding: 0px 0 5px;">PREMIUM EXTENSION</span>
                                    <?php } ?>

                                    <p class="extension-discription" ><?php echo  strlen($extension['description']) > 150 ? substr($extension['description'],0,150)."..." : $extension['description']; ?>
                                        
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
                                    <?php if($this->user_permission != 'is_employee'){ 
                                            if($extension['extension_name'] != 'Vendor Core Features' && $extension['extension_name'] != 'Subscription' && $extension['extension_name'] != 'Vendor monthly report') { 
                                        ?>
                                        <span></span>
                                    <?php } }?>
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

<div class="modal fade" id="premium_extension">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="font-size: 15px;">
                To unlock all <b>PREMIUM</b> extensions.
                <a target="_blank" href="<?php echo base_url().'partners' ?>">
                    Select a Partner PMS Vendor
                </a>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
  $(document).ready(function(){

   // alert(is_current_user_superadmin);
   // alert(innGrid.isPartnerOwner);
    if((is_current_user_superadmin!= 1 && innGrid.isPartnerOwner !=1) && 
        (innGrid.companyID !=3417 && innGrid.companyID !=3219 && innGrid.companyID !=3545)){
    $('.checbox-switch').each(function(){
            if ($(this).find('a[name="nestpay_integration"]').length > 0) {
                $(this).find('.extension-box').hide();
            }
        });
    }
});  
    
</script>