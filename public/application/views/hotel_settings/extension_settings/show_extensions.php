


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



<div class="main-card mb-3">
    <div class="extension-card">

<?php
//Columns must be a factor of 12 (1,2,3,4,6,12)
$numOfCols = 3;
$rowCount = 0;
$bootstrapColWidth = 12 / $numOfCols;
?>
<div class="row">
<?php if(isset($extensions) && $extensions) :
    foreach ($extensions as $extension){ ?>  
        <div class="col-md-<?php echo $bootstrapColWidth; ?>" style="padding-right: 0px">
             <div class="extension_block">
     <div class="main-extension">
             <div class="icon">

            <img src="<?php echo (isset($extension['image_name']) && $extension['image_name']) ?  base_url().'/images/'.$extension['image_name'] : '';?>" style="width: 30px;height: 30px">
            </div>
            <div class="extension-content">
                <b style="font-size: 12px;"><?php
                $name = $extension['extension_name'];
                $extension_name = str_replace("_"," ",$name);
                echo ucwords(l($extension_name, true)); ?></b>
                 <div >
                <p class="extension-discription" ><?php echo substr($extension['description'], 0,60).'...  '; ?><a href="<?php echo (isset($extension['marketplace_product_link']) && $extension['marketplace_product_link'] ? $extension['marketplace_product_link']: "")?>" style="font-size: 14px">more</a></p>
            </div>
            </div>
    </div>

            <div class="features-div-padding">
            
                <div class="checkbox checbox-switch switch-primary" style="margin-bottom: 5px;margin-top: 5px">
                    <a href="<?php  if(isset( $extension['setting_link']) && $extension['setting_link'] ){echo $extension           ['setting_link']; }else{
                        echo '';}?>" 
                        class="ml-4"
                        style="font-size: 25px;"
                        name="<?php echo $extension['extension_folder_name']; ?>"
                        data-status="<?php echo $extension['is_active']; ?>">
                        
                        <?php if($extension['is_active'] == 1 && $extension['setting_link'] !=null){
                            echo '<i class="pe-7s-config text-primary"></i>';
                        }else{
                            echo '';
                        } ?>
                            
                        </a> 
                    <label class="extension-box" style="padding-right: 1.5rem !important;">
                     <input type="checkbox" class="extension-status-button" data-status="<?php echo $extension['is_active']; ?>" name="<?php echo $extension['extension_folder_name']; ?>"
                               <?= $extension['is_active'] ? 'checked=checked' : ''; ?>/>
                        <span></span>
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