<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-keypad text-success"></i>
            </div>
      <?php echo l('Extensions'); ?>
        </div>
    </div>

    <hr>

    <?php if((isset($is_vendor[0]) && $this->user_permission == 'is_admin') || $this->is_super_admin == 1){ ?>
        <div class="topnav mb-3">
            <ul>
                <li><a class="<?php if($this->uri->segment(2) == '') echo 'active'; ?>" href="<?php echo base_url().'extensions'?>"><?php echo l('Installed Extensions', true); ?></a></li>
                <li><a class="<?php if($this->uri->segment(2) == 'show_vendors_extensions') echo 'active'; ?>" href="<?php echo base_url().'extensions/show_vendors_extensions'?>"><?php echo l('All Extensions', true); ?></a></li>
            </ul>
        </div>
    <?php } ?>

</div>

<div class="main-card mb-3">
    <?php if($this->is_super_admin || $this->user_permission == 'is_admin'){ ?>
        <b style="font-size: 17px;">Installed Extensions :</b>
    <?php } ?>
    <div class="extension-card">

        <?php
            $numOfCols = 3;
            $rowCount = 0;
            $bootstrapColWidth = 12 / $numOfCols;
        ?>
        <div class="row">
            <?php if(isset($extensions) && $extensions) :
                foreach ($extensions as $extension) { 
                    if((isset($extension['is_installed']) && $extension['is_installed'])) { ?>  
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
                                        </b>
                                        <div>
                                            <?php if(isset($extension['is_admin_module']) && $extension['is_admin_module']){ ?>
                                            <span style="font-size: 11px;color: gray;font-weight: 500;padding: 0px 0 5px;">VENDOR ONLY</span>
                                            <?php } ?>
                                           
                                            <p class="extension-discription" ><?php echo strlen($extension['description']) > 200 ? substr($extension['description'],0,200)."..." : $extension['description']; ?>
                                                
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="features-div-padding">
                                    <?php //if($this->is_super_admin){ ?>
                                    <div class="checkbox checbox-switch switch-primary" style="margin-bottom: 5px;margin-top: 5px">
                                        
                                        
                                        <!-- <label class="extension-box" style="padding-right: 1.5rem !important;">
                                            <input type="checkbox" class="extension-status-button" data-status="<?php echo $extension['is_active']; ?>" name="<?php echo $extension['extension_folder_name']; ?>"
                                            <?= $extension['is_active'] ? 'checked=checked' : ''; ?>/>
                                        </label> -->
                                        <?php if($this->is_super_admin || $this->user_permission == 'is_admin'){ ?>
                                            <a href="javascript:" data-ext_name="<?php echo $extension['extension_folder_name']; ?>" class="uninstall_extension" >Uninstall</a>
                                        <?php } ?>
                                    </div>
                                    <?php //} ?>
                                </div>
                            </div>
                        </div>
                        <?php
                            $rowCount++;
                            if($rowCount % $numOfCols == 0) echo '</div><div class="row">';
                    }
                } ?>
                <?php else : ?> 
                <h4><?php echo l('No extensions found!', true); ?></h4>
            <?php endif; ?>
            
        </div>
    </div>

    <?php if($this->is_super_admin || $this->user_permission == 'is_admin'){ ?>
        <b style="font-size: 17px;">Uninstalled Extensions :</b>
        <div class="extension-card">

            <?php
                $numOfCols = 3;
                $rowCount = 0;
                $bootstrapColWidth = 12 / $numOfCols;
            ?>
            <div class="row">
                <?php if(isset($extensions) && $extensions) :
                    foreach ($extensions as $extension) { 
                        if(isset($extension['is_installed']) && !$extension['is_installed']) { ?>  
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
                                            </b>
                                            <div>
                                                <?php if(isset($extension['is_admin_module']) && $extension['is_admin_module']){ ?>
                                                <span style="font-size: 11px;color: gray;font-weight: 500;padding: 0px 0 5px;">VENDOR ONLY</span>
                                                <?php } ?>
                                               
                                                <p class="extension-discription" ><?php echo  strlen($extension['description']) > 200 ? substr($extension['description'],0,200)."..." : $extension['description']; ?>
                                                    
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="features-div-padding">
                                        <div class="checkbox checbox-switch switch-primary" style="margin-bottom: 5px;margin-top: 5px">
                                            <!-- <label class="extension-box" style="padding-right: 1.5rem !important;">
                                                <input type="checkbox" class="extension-status-button" data-status="<?php echo $extension['is_active']; ?>" name="<?php echo $extension['extension_folder_name']; ?>"
                                                <?= $extension['is_active'] ? 'checked=checked' : ''; ?>/>
                                            </label >-->
                                             <?php if($company_data['creation_date'] < '2024-01-01' || $this->user_email == SUPER_ADMIN ) {?>
                                            <a href="javascript:" data-ext_name="<?php echo $extension['extension_folder_name']; ?>" class="install_extension" >Install</a>
                                             <?php } else {?>
                                               <span style="float: right; margin: 8px 15px;" disabled >Install</span>
                                              <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                                $rowCount++;
                                if($rowCount % $numOfCols == 0) echo '</div><div class="row">';
                        }
                    }
                    ?>
                    <?php else : ?> 
                    <h4><?php echo l('No extensions found!', true); ?></h4>
                <?php endif; ?>
                
            </div>
        </div>
    <?php } ?>
</div>

<div class="modal fade" id="active_modules_modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <!-- <h4 class="modal-title"><?php echo l(''); ?></h4> -->
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="income_category">
                    <?php echo l("You can't uninstall this extension as it already activated in following properties", true); ?>
                    <hr>
                    <div class="form-group company_names">
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>