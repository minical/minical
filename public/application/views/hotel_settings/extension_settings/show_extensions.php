


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
<div class="col-sm-12">

    <?php if(isset($extensions) && $extensions) :
            foreach ($extensions as $extension) : ?>
    <div class="row extension_block <?php echo $extension['is_active'] == 1 ? 'enabled-extension' : '' ?>">
        <div class="col-sm-12">
            <div class="extension_status">
                <b><?php
                $name = $extension['extension_name'];
                $extension_name = str_replace("_"," ",$name);
                echo ucwords($extension_name); ?></b>
            </div>
            
            <div class="extension_action">
                <button class="extension-status-button btn <?php echo $extension['is_active'] == 1 ? 'btn-danger' : 'btn-success' ?>" name="<?php echo $extension['extension_folder_name']; ?>" data-status="<?php echo $extension['is_active']; ?>"><?php echo $extension['is_active'] == 1 ? 'Deactivate' : 'Activate'; ?></button>
            </div>
            <div class="extension_desc">
                <?php echo $extension['description']; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php else : ?> 
    <h3><?php echo l('No extensions have been found.', true); ?></h3>
    <?php endif; ?>

</div>
</div>
</div>