
<div class="container-fluid section bg-image" id="footer" 
		<?php
			if (isset($company_data['website_theme_color']))
			{
				if ($company_data['website_theme_color'] != "")
				{
					$background_color = $company_data['website_theme_color'];
					echo "style='background-color:#".$background_color.";'";
				}
			}
			
		?>
	>
		<br/>

		<div class="col-md-4 form">
			<h3>
				<a href="#contact" data-toggle="modal" data-target="#myModal">
					<span class="glyphicon glyphicon-envelope"></span> Contact us
				</a>
			</h3>
			<br/>
			<h4>
				<abbr title="Phone">Phone:</abbr> <?php echo $company_data['phone']; ?>
			</h4>
			<h4>
				<abbr title="Fax">Fax:</abbr> <?php echo $company_data['fax']; ?>
			</h4>

		</div>
		<address class="text-left col-md-4">
			<h4>
				<strong><?php echo $company_data['name']; ?></strong><br>
			</h4>
			<?php echo $company_data['address']; ?><br>
			<?php echo $company_data['city'].", ".$company_data['region']; ?><br/>
			<?php echo $company_data['postal_code']; ?><br/>
			<?php echo $company_data['email']; ?>
			<br/><br/>
			
		</address>
		<div class="col-md-4">
				  
			  <?php
			  		$gallery_image_index = 0;
					if(isset($random_gallery_image)):
						foreach ($random_gallery_image as $gallery):
							if ($gallery_image_index >= 9)
								break;
							$gallery_image_index++;
				?>
							<div class="col-xs-6 col-md-4">
							    <a href="https://<?php echo getenv('AWS_S3_BUCKET'); ?>.s3.amazonaws.com/<?php echo $company_data['company_id']."/".$gallery['filename']; ?>" class="thumbnail"  data-lightbox="footer-gallery-set" data-title="<?php
							    		if (isset($gallery['caption']))
						    			{
						    				echo $gallery['caption'];
						    			}
							    	?>" height="0px">
							    	<img class="thumbnail" src="https://<?php echo getenv('AWS_S3_BUCKET'); ?>.s3.amazonaws.com/<?php echo $company_data['company_id']."/".$gallery['filename']; ?>" alt="image-<?php echo $gallery_image_index; ?>">
							    	
							    	<?php
							    		if (isset($gallery['caption'])):
							    	?>
							    			<!--<p class="caption"><?php echo $gallery['caption']; ?></p>-->
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
		<br/><br/>
		<div class="container text-center highlight col-md-12">
			powered by <a href="http://www.minical.io" target="_blank">Minical</a> 
			<br/><br/>
		</div>

		

	</div>
</body>

<?php
	// Google analytics
	if (isset($company_data['google_analytics_id'])):
		if ($company_data['google_analytics_id'] != ""):
?>
			<script>
			  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

			  ga('create', '<?php echo $company_data['google_analytics_id']; ?>', 'auto');
			  ga('send', 'pageview');

			</script>
<?php
		endif;
	endif;
?>

<script type="text/javascript" src="<?php echo getenv('BUILDER_URL');?>pages/js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="<?php echo getenv('BUILDER_URL');?>pages/js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo getenv('BUILDER_URL');?>pages/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?php echo getenv('BUILDER_URL');?>pages/js/jquery.flexslider-min.js"></script>
<script type="text/javascript" src="<?php echo getenv('BUILDER_URL');?>pages/js/lightbox.min.js"></script>
<script type="text/javascript" src="<?php echo getenv('BUILDER_URL');?>pages/js/validator.min.js"></script>
<script type="text/javascript" src="<?php echo getenv('BUILDER_URL');?>pages/js/helpers.js"></script>
<script type="text/javascript" src="<?php echo getenv('BUILDER_URL');?>pages/js/app.js"></script>
<script>
	// Get the current URL
// var currentUrl = window.location.href;

// // Replace the "/pages" segment with an empty string
// var modifiedUrl = currentUrl.replace('pages/', '');

// // Redirect to the modified URL
// // window.location.href = modifiedUrl;
// history.replaceState(null, '', modifiedUrl);
var modifiedUrl = window.location.href.replace('pages/','');
history.replaceState(null,'',modifiedUrl);
</script>

<?php 
	if (isset($js_files)) : 
		foreach ($js_files as $path) : 
?>
			<script type="text/javascript" src="<?php echo $path; ?>"></script>
<?php 
		endforeach;
	endif; 
?>

</html>
