<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-keypad text-success"></i>
            </div>
            <?php echo l('Partners'); ?>
        </div>
    </div>
    <hr>
</div>

<div class="main-card mb-3">
    <div class="extension-card">
        <?php $numOfCols = 3; $rowCount = 0; $bootstrapColWidth = 12 / $numOfCols; ?>
        <div class="extension_view" >
            <div class="row">
                <?php if(isset($partners) && $partners) :
                    foreach ($partners as $partner) { ?>
                        <div class="col-md-<?php echo $bootstrapColWidth; ?>" style="padding-right: 0px;">
                            <div class="extension_block">
                                <div class="main-extension">
                                    <div class="icon">
                                    	<?php $image_url = $this->image_url.$partner['logo']; ?>
                                        <img src="<?php echo $image_url; ?>" style="width: 50px;height: 50px" />
                                    </div>
                                    <div class="extension-content">
                                        <b style="font-size: 12px;"><?php
                                            $name = $partner['name'];
                                            $partner_name = str_replace("_"," ",$name);
                                            echo ucwords(l($partner_name, true)); ?>
                                        </b>
                                        <div>
                                            <p class="extension-discription" style= "margin-bottom: 0px; padding: 3px 0px;">
                                            	<label>Country:</label>  <?php echo $partner['location']; ?><br/>
                                            	<label>Timezone:</label>  <?php echo $partner['timezone']; ?>                                            	
                                        	</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="features-div-padding">
                                    <div class="checkbox checbox-switch switch-primary" style="margin-bottom: 5px;margin-top: 5px">
                                        <label class="extension-box" style="padding-right: 1.5rem !important;">
											<a class="btn btn-primary contact_partner" data-partner_name="<?php echo $partner['name']; ?>" data-partner_email="<?php echo $partner['email']; ?>" href="javascript:">Ask partner to contact me</a>                                                
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        $rowCount++;
                        	if($rowCount % $numOfCols == 0) echo '</div><div class="row">';
                    } ?>
                <?php else : ?>
                    <h4><?php echo l('No partners found!'); ?></h4>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

