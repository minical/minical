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
		
		array_unshift($js_files, base_url() . auto_version('js/template.js'));

		$files = get_asstes_files($this->module_assets_files, $this->router->fetch_module(), $this->controller_name, $this->function_name);
		
		foreach ($files['css_files'] as $key => $value) {
			$css_files[] = $value;
		}

		foreach ($files['js_files'] as $key => $value) {
			$js_files[] = $value;
		}
		
		//Load header
		$data = array ( 'css_files' => $css_files, 'js_files' => $js_files );		
		$this->load->view('includes/bootstrapped_header', $data);
        
        $permissions = $this->session->userdata("permissions");

        if(isset($permissions) && $permissions){
        	$is_salesperson = in_array("is_salesperson", $permissions);
        } else {
        	$is_salesperson = 0;
        }
        
	?>

	<body>
	
        <div class="modal fade"  id="company-info-modal" data-is_salesperson="<?=$is_salesperson;?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"></h4>
					</div>
					<div class="modal-body form-horizontal">
					</div>
					<div class="modal-footer">
                        <button type="button" class="btn btn-danger pull-left hidden" id="delete_company_button" data-dismiss="modal">
							Soft Delete
						</button>
                        <?php if(!$is_salesperson){ ?>
						<button type="button" class="btn btn-success" id="update_company_button" data-dismiss="modal">
							Update
						</button>
                        <?php } ?>
						<button type="button" class="btn btn-light" data-dismiss="modal">
							Close
						</button>
					</div>
				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div><!-- /.modal -->
                <div class="modal fade"  id="company-create-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                        <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <h4 class="modal-title"></h4>
                                        </div>
                                        <div class="modal-body form-horizontal">


                                        </div>
                                        <div class="modal-footer">
                                                <button type="button" class="btn btn-success" id="create_company_button" data-dismiss="modal">
												    Create
                                                </button>
                                                <button type="button" class="btn btn-default" data-dismiss="modal">
													Close  
                                                </button>
                                        </div>
                                </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->


	
		<?php
			$this->load->view('includes/admin_menu', $data);
		?>
			

		<div class="col-md-12 admin-main">
			<?php 
				$this->load->view($main_content);
				//Load footer
				$this->load->view('includes/bootstrapped_footer');
			?>
		</div>

        <input type="text" name="project_url" id="project_url" value="<?php echo base_url(); ?>">

	</body>

</html>
