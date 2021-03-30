<?php
// Set the whitelabel information
$whitelabel_detail = $this->session->userdata('white_label_information');
?>
<head>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-MLXS7DC');</script>
    <!-- End Google Tag Manager -->

	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
	<!-- <title><?php echo (isset($whitelabel_detail) && isset($whitelabel_detail['name'])) ? ucfirst($whitelabel_detail['name']) : $this->config->item('branding_name'); ?> - Hotel software made by hotelier</title> -->
    <title><?php echo isset($this->company_name) && $this->company_name ? ucfirst($this->company_name) : ((isset($whitelabel_detail) && isset($whitelabel_detail['name'])) ? ucfirst($whitelabel_detail['name']) : $this->config->item('branding_name')); ?></title>
	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/bootstrap.min.css"  />	
	<!-- <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/bootstrap-theme.min.css"  />	 -->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url() . auto_version('css/bootstrap-override.css');?>"  />	

	<?php if(end($this->uri->segments) == 'room_types'){ ?>
		<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/smoothness/jquery-ui-1.12.1/jquery-ui.min.css" />	
	<?php } else { ?>
		<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/smoothness/jquery-ui.min.css" />
	<?php } ?>

	<link rel="shortcut icon" href="<?php echo base_url();?>images/favicon.ico" type="image/x-icon" />
        <!-- Link font-awesome css -->
	<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        
<link rel="stylesheet" href="<?php echo base_url();?>fonts/pe-icon-7-stroke/css/pe-icon-7-stroke.css">

<!-- Optional - Adds useful class to manipulate icon font display -->
<link rel="stylesheet" href="<?php echo base_url();?>fonts/pe-icon-7-stroke/css/helper.css">


	<?php if (isset($css_files)) : foreach ($css_files as $path) : ?>
	<link rel="stylesheet" type="text/css" href="<?php echo $path; ?>" />
	<?php endforeach; ?>
	<?php endif; ?>

    <?php 
	echo "<script>
            var base_url = '".$this->config->item('base_url')."';
            isTokenizationEnabled = '".(isset($this->is_tokenization_enabled) ? $this->is_tokenization_enabled : '')."';
            COMMON_BOOKING_SOURCES = JSON.parse('".(COMMON_BOOKING_SOURCES)."');
        </script>";
    ?>
    <script src='https://www.google.com/recaptcha/api.js'></script>
	
	<!--script type="text/javascript">
		var appInsights=window.appInsights||function(a){
		  function b(a){c[a]=function(){var b=arguments;c.queue.push(function(){c[a].apply(c,b)})}}var c={config:a},d=document,e=window;setTimeout(function(){var b=d.createElement("script");b.src=a.url||"https://az416426.vo.msecnd.net/scripts/a/ai.0.js",d.getElementsByTagName("script")[0].parentNode.appendChild(b)});try{c.cookie=d.cookie}catch(a){}c.queue=[];for(var f=["Event","Exception","Metric","PageView","Trace","Dependency"];f.length;)b("track"+f.pop());if(b("setAuthenticatedUserContext"),b("clearAuthenticatedUserContext"),b("startTrackEvent"),b("stopTrackEvent"),b("startTrackPage"),b("stopTrackPage"),b("flush"),!a.disableExceptionTracking){f="onerror",b("_"+f);var g=e[f];e[f]=function(a,b,d,e,h){var i=g&&g(a,b,d,e,h);return!0!==i&&c["_"+f](a,b,d,e,h),i}}return c    
		}({
		  instrumentationKey:"<?=INSTRUMENTATION_KEY;?>"
		});
		var PAGE_NAME = '<?=$_SERVER["REQUEST_URI"]?>';
		var PAGE_URL = '<?=$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];?>';
		
		window.appInsights=appInsights,appInsights.queue&&0===appInsights.queue.length&&appInsights.trackPageView(PAGE_NAME, PAGE_URL);
	</script-->
	
</head>
