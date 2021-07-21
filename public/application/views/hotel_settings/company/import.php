

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

        <form enctype="multipart/form-data" method="post" action="/settings/company/import_company_data">
            <h4>Import zip file: </h4>

            Download zip template <a target="_blank" href="/upload/import-template.zip">here.</a>
            <br/><br/><br/>
            <input type="file" name="file">
            <br><br>
            <input type="submit" name="submit" value="Start Import" class="btn btn-primary"> <br><br>
        </form>

    </div>
</div>
