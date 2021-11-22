
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-home text-success"></i>
            </div>
            <?php echo l('features'); ?>
        </div>
        <div class="page-title-actions m-010">
           <button id="add-feature-button" class="btn btn-primary"><?php echo l('add_feature'); ?></button>
    <button id="save-all-features-button" class="btn btn-default"><?php echo l('save_all'); ?></button>
        </div>
    </div>
</div>


<div class="main-card mb-3 card m-014">
    <div class="card-body">

<!-- Hidden delete dialog-->
<table class="table table-hover rooms">
    <thead>
        <tr>
            <th>
                <?php echo l('feature_name'); ?>
            </th>
            <th  class="text-center">
                <input type="checkbox" class="all-can-be-sold-online-checkbox" autocomplete="off" style="margin-right: 10px"/>
                <?php echo l('share_on_website'); ?>
            </th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php if (isset($features)) : foreach ($features as $feature) : ?>
            <tr class="feature-tr" id="<?php echo $feature['feature_id'] ?>">
                <td>
                    <input name="feature-name" class="form-control" type="text" value="<?php echo $feature['feature_name']; ?>"/>
                </td>
                <td class="text-center">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" class="can-be-sold-online-checkbox" autocomplete="off"
                                <?php
                                if ($feature['show_on_website'] == 1) {
                                    echo 'checked="checked"';
                                }
                                ?>
                            />
                        </label>
                    </div>
                </td>
                <td><button class="delete-feature-button btn btn-danger"><?php echo l('Delete', true); ?></button></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <?php else : ?>
        <h3><?php echo l('No Feature(s) have been recorded', true); ?>.</h3>
    <?php endif; ?>
</table>

</div>
</div>