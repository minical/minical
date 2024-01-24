<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<?php
		//Generate css and js file arrays if they don't already exist.
		//This prevents clobbering of the variables caused by multiple loading of this file (ie. iframes).
		if (!isset($css_files))
			$css_files = array();
		if (!isset($js_files))
			$js_files = array();
		
		//Load header
		$data = array ( 'css_files' => $css_files, 'js_files' => $js_files );		
		$this->load->view('includes/header', $data);
	?>

	<body>
		
			<?php $this->load->view($main_content); ?>
			
		<?php
			
			//Load footer
			$this->load->view('includes/footer');
		
		?>
	</body>
</html>