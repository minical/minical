
<!-- Hidden delete dialog-->		
<div id="confirm_delete_dialog" ></div>


<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-note2 text-success"></i>
            </div>
            <div><?php echo l('tax_types')." ".l('settings'); ?>

        </div>
    </div>
  </div>
</div>


<div class="main-card mb-3 card">
    <div class="card-body">




	<div class="table-responsive">

<table id="tax-types" class="table">
	<tr>
		<th><?php echo l('tax_name'); ?></th>
		<th><?php echo l('tax_rate'); ?></th>
		<th><?php echo l('unit'); ?></th>
		<th><?php echo l('Tax Inclusive'); ?></th>
		<th><?php echo l('delete'); ?></th>
	</tr>

	<?php if(isset($tax_types)):?>
	<?php 	foreach($tax_types as $tax_type) : ?>		
				<tr class="tax-type-tr" id="<?php echo $tax_type->tax_type_id; ?>">
					<td>
						<input name="tax-type-name" class="form-control" type="text" value="<?php echo $tax_type->tax_type; ?>" maxlength="50" style="width:250px"/>
					</td>
					<td>
                        <?php if($tax_type->is_brackets_active == '1'){ ?>
                            <input disabled name="tax-rate" class="form-control " type="text" value="<?php echo $tax_type->tax_rate; ?>" maxlength="50" style="width:150px;float: left;"/>	
							<i class="fa fa-sliders fa-2x open-price-brackets active m-115" data-toggle="modal" data-target="#price-brackets-modal"></i>
                        <?php }  
                        else { ?> 
                            <input name="tax-rate" class="form-control " type="text" value="<?php echo $tax_type->tax_rate; ?>" maxlength="50" style="width:150px;float: left;"/>   
							<i class="fa fa-sliders fa-2x open-price-brackets m-115" data-toggle="modal" data-target="#price-brackets-modal"></i>
                        <?php }?>
                    </td>
					<td>
						<select name="is-percentage" class="form-control m-116">
							<option <?php if ($tax_type->is_percentage == 1) echo "SELECTED = SELECTED"; ?> value="1">
								%
							</option>
							<option <?php if ($tax_type->is_percentage == 0) echo "SELECTED = SELECTED"; ?> value="0">
								<?php echo $currency_symbol; ?>
							</option>
						</select>
					</td>
					<td style="text-align: center;">
						<input type="checkbox" <?php if($tax_type->is_tax_inclusive == 1) echo "checked"; ?> name="is-tax-inclusive" class="form-check-input">
					</td>
					<td>
						<div class="delete-tax-type btn btn-light" id="<?php echo $tax_type->tax_type_id; ?>">X</div>
					</td>
				</tr>
	<?php endforeach; ?>

	<?php else : ?>	
	<h3><?php echo l('No tax records have been found.', true); ?></h3>
	<?php endif; ?>
</table>
	</div>
<br />
<button id="add-tax-type" class="btn btn-primary"><?php echo l('add_tax_type'); ?></button>
<button id="save-all-tax-types-button" class="btn btn-success"><?php echo l('save_all'); ?></button>

<!--add price brackets-->
<div class="modal fade" id="price-brackets-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
					<?php echo l('Price Brackets', true); ?>
					<button type="button" class="btn btn-success pull-right add-price-bracket" style="margin-right: 15px;">
						<?php echo l('Add Bracket', true); ?>
					</button>
				</h4>
            </div>
            <div class="modal-body">
                <form id="tax-price-form" class="tax-price-form">
					<input type="hidden" name="tax_type_id" value="" />
                    <div class="table-responsive">
						<table class="table table-bordered table-hover tax-price-brackets-table" data-currency="<?=$currency_symbol;?>">
							<thead>
								<tr>
									<th><?php echo l('Price From', true); ?></th>
									<th><?php echo l('Price To', true); ?></th>
									<th><?php echo l('Rate', true); ?></th>
									<th><?php echo l('Unit', true); ?></th>
									<th></th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
					<span class="btn btn-danger remove-all-price-brackets" style="margin-left: 9px;"><?php echo l('Remove All', true); ?></span>
					<span class="btn btn-light pull-right" data-dismiss="modal" aria-label="Cancel"><?php echo l('Cancel', true); ?></span>
					<button type="submit" class="btn btn-success pull-right save-price-brackets" style="margin-right: 10px;"><?php echo l('Save', true); ?></button>
				</form>
			</div>
		</div>
	</div>
</div>
</div></div>