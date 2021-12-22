<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','GTM-MLXS7DC');</script>
<!-- End Google Tag Manager -->

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src=“https://www.googletagmanager.com/ns.html?id=GTM-MLXS7DC”
                  height=“0" width=“0” style=“display:none;visibility:hidden”></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->



<br/><br/>

<center>

	<a href="<?php echo base_url();?>">
		<?php
                    $whitelabelinfo = $this->session->userdata('white_label_information');
                    if($whitelabelinfo)
                    {
                        echo '<img src="'.base_url().'/images/'.$whitelabelinfo['logo'].'">';
                    }
                    else
                    {
                        echo '<img src="'.base_url().'/images/'.$this->config->item('branding_logo').'">';
                    }
                 ?>
	</a>

	<h3 class="form-signin-heading">Thank you for signing-up with us</h3>
	
	<br />
		
	<h1>We sent you a confirmation email</h1>
	<br />
        <h3>Please check your inbox and activate your <?php if($whitelabelinfo){ echo ucfirst($whitelabelinfo['name']); }else{ echo 'Minical';} ?> account</h3>
	<h3>If you need any assistance, please contact us at <? echo $support_email;?></h3>
	<br/>
	<br/>
	<h3>
		<a href="https://www.minical.io">Return to <?php if($whitelabelinfo){ echo ucfirst($whitelabelinfo['name']); }else{ echo 'Minical';} ?> homepage</a>
	</h3>
</center>