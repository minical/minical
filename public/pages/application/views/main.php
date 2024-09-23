
<div class="container-fluid">
		<a class="anchor" id="home"></a>

		<!--
		<div class="container-fluid max-width" id="slider" 
			
		>
		-->

		<?php
			if ($company_data['website_tripadvisor_widget_type'] != "0" && $company_data['website_tripadvisor_location_id'] != ""):
				if ($company_data['website_tripadvisor_widget_type'] == '1'):
		?>
					<div class="tripadvisor hidden-xs">
						<div id="TA_rated842" class="TA_rated">
							<ul id="ASjeP388b" class="TA_links qavuyH5">
								<li id="PVaJVTSV" class="42IUqqB">
									<a target="_blank" href="http://www.tripadvisor.ca/"><img src="http://www.tripadvisor.ca/img/cdsi/img2/badges/ollie-11424-2.gif" alt="TripAdvisor"/></a>
								</li>
							</ul>
						</div>
						<script src="http://www.jscache.com/wejs?wtype=rated&amp;uniq=842&amp;locationId=<?php echo $company_data['website_tripadvisor_location_id']; ?>&amp;lang=en_CA&amp;display_version=2"></script>
					</div>
		<?php
				elseif ($company_data['website_tripadvisor_widget_type'] == '2'):
		?>
					<div class="tripadvisor hidden-xs">

						<div id="TA_excellent109" class="TA_excellent">
							<ul id="M3U8KTe1Fsq" class="TA_links TvtnOgfDIALH">
								<li id="d5on66JXd2" class="ilRmRDf">
									<a target="_blank" href="http://www.tripadvisor.ca/"><img src="http://c1.tacdn.com/img2/widget/tripadvisor_logo_115x18.gif" alt="TripAdvisor" class="widEXCIMG" id="CDSWIDEXCLOGO"/></a>
								</li>
							</ul>
						</div>
						<script src="http://www.jscache.com/wejs?wtype=excellent&amp;uniq=109&amp;locationId=<?php echo $company_data['website_tripadvisor_location_id']; ?>&amp;lang=en_CA&amp;display_version=2"></script>

					</div>
		<?php
				elseif ($company_data['website_tripadvisor_widget_type'] == '3'):
		?>
					<div class="tripadvisor hidden-xs">
						<div id="TA_selfserveprop308" class="TA_selfserveprop">
							<ul id="TPIsMCUn" class="TA_links dEt90ShQ1FYj">
								<li id="8V8FqE" class="TfDqKKld">
									<a target="_blank" href="http://www.tripadvisor.ca/"><img src="http://www.tripadvisor.ca/img/cdsi/img2/branding/150_logo-11900-2.png" alt="TripAdvisor"/></a>
								</li>
							</ul>
						</div>
						<script src="http://www.jscache.com/wejs?wtype=selfserveprop&amp;uniq=308&amp;locationId=<?php echo $company_data['website_tripadvisor_location_id']; ?>&amp;lang=en_CA&amp;rating=true&amp;nreviews=5&amp;writereviewlink=true&amp;popIdx=true&amp;iswide=false&amp;border=true&amp;display_version=2"></script>

					</div>
		<?php
				endif;
			endif;
		?>

			<div id="main-content" class="fw-flexslider-wrapper clearfix">
				<!-- slider start====================== -->
				
				<div class="container-fluid">
				<div id="myCarousel" class="carousel slide" data-ride="carousel">
					<!-- Indicators -->
					<ol class="carousel-indicators">
					<?php
						if (isset($slideshow_images)){
							foreach ($slideshow_images as $key => $imagePath){
							$class = ($key === 0) ? 'class="active"' : '';
							echo '<li data-target="#myCarousel" data-slide-to="' . $key . '" ' . $class . '></li>';
							}
						}
					?>
					</ol>

					<!-- Wrapper for slides -->
					<div class="carousel-inner">
					<?php 
						if (isset($slideshow_images)){
							foreach ($slideshow_images as $key => $imagePath){
								$class = ($key === 0) ? 'active' : '';
								echo '<div class="item ' . $class . '">';
								echo '<img src="http://' . getenv('AWS_S3_BUCKET') . '.s3.amazonaws.com/' .$company_data['company_id']."/". $imagePath['filename'] . '" alt="Slide ' . ($key + 1) . '" style="width:100%;">';
							    if (isset($slide['caption']))
					      		echo "<p class='carousel-caption'>".$slide['caption']."</p>";
								echo '</div>';
							}
						}
					?>
					</div>

					<!-- Left and right controls -->
					<a class="left carousel-control" href="#myCarousel" data-slide="prev">
					<span class="glyphicon glyphicon-chevron-left"></span>
					<span class="sr-only">Previous</span>
					</a>
					<a class="right carousel-control" href="#myCarousel" data-slide="next">
					<span class="glyphicon glyphicon-chevron-right"></span>
					<span class="sr-only">Next</span>
					</a>
				</div>
				</div>
				<!-- slider end====================== -->

				<!-- online booking engine -->
				<?php
					if ($company_data['website_is_taking_online_reservation'] == '1'):
				?>
						<div class="container-fluid" id="online-booking-widget">
							<div id="online-booking" style="visibility: hidden; position:absolute; top:400px;"></div>
						
							<form action="https://app.minical.io/online_reservation/select_dates_and_rooms/<?php echo $company_data['company_id']; ?>" method="post" target="_blank" id="booking-form"  role="form" class="form-horizontal">
								<div class="form-group">
									<div class="col-md-10">
										<div class="form-group col-md-3">
										<div class="col-md-6">
											<label for="check-in-date" class="control-label" >Check-in Date</label></div>
											<div class="col-md-6">
												<input class="form-control datepicker" id="check-in-date" name="check-in-date">
											</div>
											
											
										</div>
										<div class="form-group col-md-3">
										<div class="col-md-6">
											<label for="check-out-date" class="control-label">Check-out Date</label>	
											</div>
											<div class="col-md-6">
												<input class="form-control datepicker" id="check-out-date" name="check-out-date">
											</div>
										</div>
										<!--div>
											<input hidden name="adult_count" value="1">
											<input hidden name="children_count" value="1">
										</div-->
										<div class="form-group col-md-3">
											<label for="adult_count" class="col-md-6 control-label">Adults</label> 
											<div class="col-md-6">
												<select name="adult_count" class="form-control">
													<option value="1" selected="selected">1</option>
													<option value="2">2</option>
													<option value="3">3</option>
													<option value="4">4</option>
													<option value="5">5</option>
													<option value="6">6</option>
												</select>
											</div>
										</div>
										<div class="form-group col-md-3">
										
											<label for="children_count" class="col-md-6 control-label">Children</label> 
											<div class="col-md-6">
												<select name="children_count" class="form-control"> 
													<option value="0" selected="selected">0</option>
													<option value="1">1</option>
													<option value="2">2</option>
													<option value="3">3</option>
													<option value="4">4</option>
												</select>
											</div>
										</div>
									</div>
									
									<div class="form-group col-md-2">
										<button type="submit" name="submit" class="col-xs-12 btn btn-primary pull-right find_rooms">
											<span class="glyphicon glyphicon-search"></span> Find Rooms & Book Online
										</button>
									</div>
								</div>
								
							</form>
						</div>
				<?php
					endif;
				?>
				
		</div>

	</div>

	<div class="container-full top-background">
		
		<div id="main-content" class="max-width container">
	
			<div class="container-fluid section" >
				<div class="col-md-5">
					<?php
						if (isset($random_gallery_image['filename'])):
					?>
							<a href="http://<?php echo getenv('AWS_S3_BUCKET'); ?>.s3.amazonaws.com/<?php echo $company_data['company_id']."/".$random_gallery_image['filename']; ?>" class="thumbnail"  data-lightbox="gallery-set" data-title="<?php
							    		if (isset($random_gallery_image['caption']))
						    			{
						    				echo $random_gallery_image['caption'];
						    			}
							    	?>">
								<img  alt="" class="thumbnail" src="http://<?php echo getenv('AWS_S3_BUCKET'); ?>.s3.amazonaws.com/<?php echo $company_data['company_id']."/".$random_gallery_image['filename']; ?>" >
							</a>
					<?php
						endif;
					?>
					<br/>
					<h4>Features</h4>
					<table class="table">
					<?php
						if (count($company_features) != 0){
							foreach($company_features as $feature){
					?>
								<tr>
									<td class="col-md-2">
										<div class="glyphicon glyphicon-check"></div>
									</td>
									<td class="text-left col-md-10">
										<?php echo $feature['feature_name']?>
									</td>
							</tr>
						<?php
							} // end foreach
						} //endif
						?>
					</table>
					<?php
						if ($company_data['website_facebook_page_url']):
					?>
							<iframe
							 	class="col-md-12" 
								style="background: white; height: 600px; overflow: hidden;" 
								allowtransparency="true" 
								frameborder="0" 
								scrolling="yes" 
								src="http://www.facebook.com/plugins/likebox.php?href=<?php echo $company_data['website_facebook_page_url']; ?>&amp;&amp;colorscheme=light&amp;show_faces=true&amp;stream=true&amp;header=true&amp;height=600">
							</iframe>
					<?php
						endif;
					?>
				</div>
				<div class="col-md-7">
					<h3>
						<?php 
							$keywords = preg_split("/[.!]+/", str_replace(PHP_EOL, '<br/>', $company_data['website_about']));
							echo $keywords[0]."\n<br/>";
							unset($keywords[0]);
							echo "<small>".implode(".", $keywords)."</small>";
						?>
					</3>
					<br/><br/>
				</div>
			</div>
		</div>
	</div>
