

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-key text-success"></i>
            </div>
            <div><?php echo l('API Management', true); ?>

        </div>
    </div>
  </div>
</div>


<div class="main-card  card">
    <div class="mb-3">


<form method="post" class="api_access_settings" autocomplete="off">

    <div style="margin:20px">
            <div class="form-group features-div-padding ">

                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <input type="checkbox" name="enable_api_access"
                               <?= $company_data['enable_api_access'] == 1 ? 'checked=checked' : ''; ?>/>
                        <span></span>
                    </label>
                    <label for="enable_api_access"><b><?= l("Enable " . $company_data['partner_name'] . " API access", true); ?></b></label>
                </div>
                <div class="form-group features-div-padding  form-inline api-key">
                    <label for="api_key"><?= l("API Key", true); ?></label>
                    <input type="text" class="form-control" name="api_key" id="api_key" value="<?php echo $company_data['api_key']; ?>" size=50 readonly/>
                </div>
            </div>
        </div>

    <div class="col-sm-12 ml-2 ">
        <input type="submit" class="btn btn-primary" style="width: 200px;" value="<?php echo l('Update', true); ?>"/>
    </div>
</form>
</div></div>