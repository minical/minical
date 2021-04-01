


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



<div class="main-card mb-3 card">
    <div class="card-body">

<?php
//Columns must be a factor of 12 (1,2,3,4,6,12)
$numOfCols = 3;
$rowCount = 0;
$bootstrapColWidth = 12 / $numOfCols;
?>
<div class="row">
<?php if(isset($extensions) && $extensions) :
    foreach ($extensions as $extension){ ?>  
        <div class="col-md-<?php echo $bootstrapColWidth; ?>">
             <div class="extension_block">
     <div class="main-extension">
             <div class="icon">

            <img src="<?php echo base_url().'/images/'.$extension['image_name'];?>" style="width: 40px;height: 40px">
            </div>
            <div class="content">
                <b style="font-size: 12px;"><?php
                $name = $extension['extension_name'];
                $extension_name = str_replace("_"," ",$name);
                echo ucwords($extension_name); ?></b>
                 <div >
            <p class="extension-discription" ><?php echo substr($extension['description'], 0,60).'...'; ?></p>
            </div>
            </div>
    </div>

            <div class="features-div-padding">

                <div class="checkbox checbox-switch switch-primary">
                    <!--  <button class=" btn btn-sm ml-2 <?php echo $extension['is_active'] == 1 ? 'btn-primary' : '' ?>" name="<?php echo $extension['extension_folder_name']; ?>" data-status="<?php echo $extension['is_active']; ?>"><?php echo $extension['is_active'] == 1 ? 'Setting' : ''; ?></button> -->
                    <label class="extension-box" style="padding-bottom: 8px">
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