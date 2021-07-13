

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-notebook text-success"></i>
            </div>
            <div><?php echo l('Import '). $this->company_name .l(' data'); ?>

            </div>
        </div>
    </div>
</div>


<div class="main-card card">
    <div class="card-body">

        <form enctype="multipart/form-data" method="post" action="/settings/company/import_company_data">
            <h4>Import Zip File: </h4>
            <input type="file" name="file">
            <br><br>
            <input type="submit" name="submit" value="Import" class="btn btn-primary"> <br><br>
        </form>

    </div>
</div>
