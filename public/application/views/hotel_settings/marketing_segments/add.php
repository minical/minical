<div class="settings integrations">
	<div class="page-header">
		<h2>
			<?php echo l('Marketing Segment'); ?>
		</h2>
	</div>

    <div class="panel panel-default ">
        
        <div class="panel-body form-horizontal ">
            <div id="configure-channex" >
            <h4>
			<?php echo l('Add Marketing Segment'); ?>
		</h4>

      
				 
              
                <div class="form-group rate-group ">
                   
                    <div class="col-sm-6">
					 <label for="debtor_name" class="control-label">
                        <span alt="debtor_name" title="debtor_name">Name</span>
                    </label>
                        <input type="text" name="name" class="form-control" value="<?php echo isset($debtor['name']) ? $debtor['name'] : ''; ?>">
                    </div>
					 <div class="col-sm-6">
					<label for="debtor_description" class="control-label">
                        <span alt="debtor_description" title="debtor_description">Description</span>
                    </label>
                        <input type="text" name="description" class="form-control" value="<?php echo isset($debtor['description']) ? $debtor['description'] : ''; ?>">
                    </div>
                </div>
               
                
                <!-- <div class="form-group rate-group text-center">
                    <label for="channex_password" class="col-sm-3 control-label">
                        <span alt="channex_password" title="channex_password"><?php//l("maximojo_integration/Channex Password");?></span>
                    </label>
                    <div class="col-sm-9">
                        <input type="password" name="password" class="form-control" value="<?php// echo isset($channex_data['password']) ? $channex_data['password'] : ''; ?>">
                    </div>
                </div> -->
<input type="hidden" name="segment_id" class="form-control" value="<?php echo isset($debtor['id']) ? $debtor['id'] : ''; ?>">
                  
                <div class="text-center">
                    <button type="button" class="btn btn-success settings-segment" >Submit</button>
                </div>
            </div>
            
        </div>

    </div>
</div>
