	<div class="container-full top-background">
		
		<div id="main-content" class="max-width container">
		
			<div class="container-fluid section" >
				<h1 class="title">Location</h1>
				<div id="gmap1">
					<div style="width: 100%">
                        <?php $address = "{$company_data['name']}, {$company_data['address']}, {$company_data['city']}, {$company_data['region']}, {$company_data['country']}, {$company_data['postal_code']}"; ?>
						<iframe width="100%" height="600" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q=<?=urlencode($address);?>&amp;t=&amp;z=13&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"></iframe>
						<!-- <a href="http://www.gps.ie/">Find GPS coordinates</a> -->
					</div>
				</div> <!-- For maplace -->
			</div>
		
			<a class="anchor" id="location"></a>

		</div>

	</div>

	<?php
		//print_r($company_data);
    	if (isset($company_data['website_latitude']) && isset($company_data['website_longitude'])):
	?>
			<input id='website_latitude' value="<?php echo $company_data['website_latitude']; ?>" hidden />
			<input id='website_longitude' value="<?php echo $company_data['website_longitude']; ?>" hidden />
	<?php
		endif;
	?>


