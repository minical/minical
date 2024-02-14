
	
		<div id="main-content" class="max-width container">
		
		<a class="anchor" id="gallery"></a>

		<div class="container-fluid section" >
			<h1 class="title">Gallery</h1>
			<div class="row">
			  
			  <?php
					  if (isset($random_gallery_image)):
						  $gallery_image_index = 0;
					foreach ($random_gallery_image as $image):
						$gallery_image_index++;
					?>
							<div class="col-xs-6 col-md-3">
								<a href="https://<?php echo getenv('AWS_S3_BUCKET'); ?>.s3.amazonaws.com/<?php echo $company_data['company_id']."/".$image['filename']; ?>" class="thumbnail"  data-lightbox="gallery-set" data-title="<?php
										if (isset($image['caption']))
										{
											echo $image['caption'];
										}
									?>">
									<img class="thumbnail" src="https://<?php echo getenv('AWS_S3_BUCKET'); ?>.s3.amazonaws.com/<?php echo $company_data['company_id']."/".$image['filename']; ?>" alt="image-<?php echo $gallery_image_index; ?>">
									
									<?php
										if (isset($image['caption'])):
									?>
											<!--<p class="caption"><?php echo $image['caption']; ?></p>-->
									<?php
										endif;	
									?>
								</a>
							</div>
				<?php
						endforeach;
					endif;
				?>

		   
			</div>
			
		</div>
	
		<a class="anchor" id="location"></a>


</div>
