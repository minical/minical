<div class="settings integrations">
	<div class="page-header">
		<h2>
			<?php echo l('Marketing Segments'); ?>
		</h2>
	</div>

    <div class="panel panel-default ">
        
        <div class="panel-body form-horizontal ">
            <div id="configure-channex" >
            <h4 style="text-align:center">
			<?php echo l('Marketing Segments List'); ?>
		</h4>
<div class="page-title-actions">
        	<a href="<?php echo base_url().'settings/marketing_segments/add'; ?>" class="btn btn-primary">Add New</a>
        </div>
     <div class="table-responsive">

<table class="table table-hover">
	<tr>
		<th><?php echo l('id'); ?></th>				
		<th >Name</th>				
			
		<th></th>
	</tr>

	<?php 
		if(isset($rows)) 
			foreach ($rows as $r) : 
				// if customer name is empty
				//print_r($r);
				
	?>
				<tr class='customer-tr' name='<?php echo $r['post_id']; ?>'>
					<td>
						<?php
							echo $r['post_id'];									
						?>
					
					</td>
					<td>
						
							<?php
								echo $r['post_title'];					
							?>
						
						
					</td>
					
					<td class="center delete-td">
						<div class="dropdown pull-right">
							<button class="btn btn-light btn-xs dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
							<!-- 	<span class="caret"></span> -->
							</button>
							<ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
								<li role="presentation">
									<a role="menuitem" tabindex="-1" class="customer-profile" id="<?php echo $r['post_id']; ?>" href="<?php echo base_url().'settings/marketing_segments/edit/'.$r['post_id']; ?>">
										Edit
									</a>
								</li><li role="presentation">
								<?php if($r['is_deleted']==0){ ?>
									<a role="menuitem" style="color:red;" tabindex="-1" onclick="EnableDisable('<?php echo $r['post_id']; ?>', 'disable')" href="javascript:void(0);">
										Disable
									</a>
								<?php } if($r['is_deleted']==1){ ?>
									<a role="menuitem" style="color:green;" tabindex="-1" onclick="EnableDisable('<?php echo $r['post_id']; ?>', 'enable')" href="javascript:void(0);">
										Enable
									</a>
									<?php } ?>
								</li>
								
							</ul>
						</div>
					</td>	
				</tr>
	<?php 
			endforeach;				
	?> 
                
</table>
</div>

<div class="panel panel-default">
	<div class="panel-body text-center">
		<h4>
			<?php echo $this->pagination->create_links(); ?>
		</h4>
		<br/>
		</div>
</div>
   </div>
            
        </div>

    </div>
</div>
