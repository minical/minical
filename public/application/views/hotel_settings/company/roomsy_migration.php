

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-notebook text-success"></i>
            </div>
            <div><?php echo l('Import'); ?>

            </div>
        </div>
    </div>
</div>


<div class="main-card card">
    <div class="card-body">

        <form enctype="multipart/form-data" method="post" action="/settings/roomsy_migration/get_roomsy_data"  onsubmit="$('#loading').show();">

            <label for="old_data"><?php echo l('Comapny ID'); ?></label>  
            <input class="form-control" style="width: 20%;" type="text" name="company_id" required>
            <br>
            <input type="checkbox" id="old_data" name="removd_old_data" value="1">
            <label for="old_data"><?php echo l('Remove old data (Recommended)'); ?></label><br>
            <br>
            <div id="loading" style="display:none"><img src= "<?php echo base_url().'images/loading.gif'; ?>"></div>
            <br/>
            <input type="submit" name="submit" value="Start Import" class="btn btn-primary"> <br><br>

        </form>

    </div>
</div>
