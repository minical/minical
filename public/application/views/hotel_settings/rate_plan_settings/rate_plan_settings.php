
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-cash text-success"></i>
            </div>
            <?php  echo l('rate_plan')." ".l('settings'); ?>
        </div>
    </div>
</div>

<!-- <div class="page-header">
	<h2><?php echo l('rate_plan')." ".l('settings'); ?></h2>
</div> -->

<!-- Image Edit Modal -->
<div class="modal fade" id="image_edit_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?php echo l('edit_image'); ?></h4>
      </div>
      <div class="modal-body text-center" id="image_edit_modal_body">
      </div>
      <div class="modal-footer image-modal-footer">
      	<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo l('close'); ?></button>
      </div>
    </div>
  </div>
</div>

<div class="main-card mb-3 card m-014">
    <div class="card-body">
<div class="col-sm-12">
	<div class="col-sm-3">
		<strong><?php echo l('room_types'); ?></strong>
		<div id="room-type-list"></div>
	</div>
	<div class="col-sm-9">
		<div class="row">
			<button class="btn btn-primary add-rate-plan-button" style="margin-bottom:10px"><?php echo l('add_rate_plan'); ?></button>
		</div>
		<div class="rate-plans row">
		</div>
	</div>
</div>
</div>
</div>

<input type="hidden" name="subscription_level" class="subscription_level" value="<?php echo $this->company_subscription_level; ?>">
<input type="hidden" name="subscription_state" class="subscription_state" value="<?php echo $this->company_subscription_state; ?>">
<input type="hidden" name="limit_feature" class="limit_feature" value="<?php echo $this->company_feature_limit; ?>">

<div class="modal fade" id="new-rate-plan" data-backdrop="static"
     data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" style="text-align: center;">
                    <?php echo l('Add New Rate Plan', true); ?>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </h4>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <div class="col-md-12">

                    <div class="form-group">
                        <div class="col-sm-12 text-right" style="float: right">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">
                                <?php echo l('Cancel', true); ?>
                            </button>
                            <button class="btn btn-success add-new-rate-plan-button">
                                <?php echo l('Add Rate Plan', true); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->