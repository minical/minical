
<div class="container" style="margin-top:160px">
	  
	<?php
		if (isset($room_types)):
	  		foreach ($room_types as $index => $room_type):
	  			if (isset($room_type['can_be_sold_online']) && $room_type['can_be_sold_online'] == "0")
	  				continue; // skip room types that cannot be sold online
	  			

	  				
	?>
				<div class="panel panel-default">
					<div class="panel-body">
						<!-- Picture will go here -->
						<div class="col-md-3">
									
							<?php
								// Display room type image
								if ($room_type['filename']):
									// foreach ($room_type as $image_index => $image):
							?>
										
										<a href="https://<?php echo getenv('AWS_S3_BUCKET'); ?>.s3.amazonaws.com/<?php echo $company_data['company_id']."/".$room_type['filename']; ?>" 
										class=" <?php 
													// if ($image_index === 0) 
														echo "col-md-12"; 
													// else 
														// echo "col-md-4 hidden-xs"; 
												?> thumbnail"  data-lightbox="<?php echo $room_type['id']; ?>" >
											<img src="https://<?php echo getenv('AWS_S3_BUCKET'); ?>.s3.amazonaws.com/<?php echo $company_data['company_id']."/".$room_type['filename']; ?>" />
										</a>
									
							<?php
									// endforeach;
								else:
							?>
									<div class="panel panel-default text-center">
										<div class="h4 text-muted">Photo not available</div>
									</div>
																						
							<?php
								endif;
							?>
						
						</div>

						<div class="col-md-9">
							<div class="panel panel-default">
								<div class="panel-heading">
									<div class="h4"><?php echo $room_type['name']; ?></div>
								</div>
								<div class="panel-body">
									<?php echo str_replace(PHP_EOL, '<br/>', $room_type['description']); ?>
								</div>
								<?php
									if ($company_data['website_is_taking_online_reservation'] == '1'):
								?>
										<div class="panel-footer text-right">
											<a 
												href="https://demo.minical.io/online_reservation/select_dates_and_rooms/<?php echo $company_data['company_id']; ?>" 
												class="text-right btn btn-success">
											<span class="glyphicon glyphicon-calendar"></span> Book Now!</a>
										</div>
								<?php
									endif;
								?>


							</div>
							
						</div>
					</div>
				</div>

					
	<?php
			endforeach;
		endif;
	?>


</div>
	
