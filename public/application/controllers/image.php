<?php
class Image extends MY_Controller
{
	function __construct()
	{
        parent::__construct();
        $this->load->model("Company_model");
        $this->load->model('User_model');
        $this->load->model('Image_model');
        
        $this->load->library('form_validation');			
		$this->load->library('S3'); // load Amazon S3 library

        $this->load->helper("guid"); // for generating UUID
	}	
	

	function save_to_file()
	{
           
		$allowedExts = array("gif", "jpeg", "jpg", "png", "GIF", "JPEG", "JPG", "PNG");
		$temp = explode(".", $_FILES["img"]["name"]);
		$extension = end($temp);
               

		if ( in_array($extension, $allowedExts))
		{
                    if ($_FILES["img"]["error"] > 0)
			{
                        
				$response = array(
					"status" => 'error',
					"message" => 'ERROR Return Code: '. $_FILES["img"]["error"],
					);
			}
			else
			{
                $filename = $_FILES["img"]["tmp_name"];
                list($width, $height) = getimagesize( $filename );
				
				$this->_upload_to_s3($_FILES["img"]["tmp_name"], "temp_image.".$extension);
				$response = array(
					"status" => 'success',
					"url" => $this->image_url.$this->company_id."/temp_image.".$extension,
					"width" => $width,
					"height" => $height
				);
							  
			}
		}
		else
		{
		$response = array(
				"status" => 'error',
				"message" => 'something went wrong',
				);
		}
		  
		print json_encode($response);

	}

	/**
	* This is where image gets cropped via Croppic javascript, then
	* gets uploaded to Amazon S3. Also updated into minical DB's images table.
	*/

	// Image compression using Imagick

	//image compression using GD
	function crop_to_file()
	{
		$imgUrl = $_POST['imgUrl'];
		// original sizes
		$imgInitW = $_POST['imgInitW'];
		$imgInitH = $_POST['imgInitH'];
		// resized sizes
		$imgW = $_POST['imgW'];
		$imgH = $_POST['imgH'];
		// offsets
		$imgY1 = $_POST['imgY1'];
		$imgX1 = $_POST['imgX1'];
		// crop box
		$cropW = $_POST['cropW'];
		$cropH = $_POST['cropH'];
		// rotation angle
		$angle = $_POST['rotation'];

		$jpeg_quality = 100;

		$output_filename = generate_guid();
		$image_group_id = $_POST['imageGroupID'];
		
		// uncomment line below to save the cropped image in the same location as the original image.
		//$output_filename = dirname($imgUrl). "/croppedImg_".rand();

		$what = getimagesize($imgUrl);

		switch(strtolower($what['mime']))
		{
		    case 'image/png':
		        $img_r = imagecreatefrompng($imgUrl);
				$source_image = imagecreatefrompng($imgUrl);
				$type = '.png';
		        break;
		    case 'image/jpeg':
		        $img_r = imagecreatefromjpeg($imgUrl);
				$source_image = imagecreatefromjpeg($imgUrl);
				error_log("jpg");
				$type = '.jpeg';
		        break;
		    case 'image/gif':
		        $img_r = imagecreatefromgif($imgUrl);
				$source_image = imagecreatefromgif($imgUrl);
				$type = '.gif';
		        break;
		    default: die('image type not supported');
		}


		//Check write Access to Directory
		// resize the original image to size of editor
	    $resizedImage = imagecreatetruecolor($imgW, $imgH);
		imagecopyresampled($resizedImage, $source_image, 0, 0, 0, 0, $imgW, $imgH, $imgInitW, $imgInitH);
	    // rotate the rezized image
	    $rotated_image = imagerotate($resizedImage, -$angle, 0);
	    // find new width & height of rotated image
	    $rotated_width = imagesx($rotated_image);
	    $rotated_height = imagesy($rotated_image);
	    // diff between rotated & original sizes
	    $dx = $rotated_width - $imgW;
	    $dy = $rotated_height - $imgH;
	    // crop rotated image to fit into original rezized rectangle
		$cropped_rotated_image = imagecreatetruecolor($imgW, $imgH);
		imagecolortransparent($cropped_rotated_image, imagecolorallocate($cropped_rotated_image, 0, 0, 0));
		imagecopyresampled($cropped_rotated_image, $rotated_image, 0, 0, $dx / 2, $dy / 2, $imgW, $imgH, $imgW, $imgH);
		// crop image into selected area
		$final_image = imagecreatetruecolor($cropW, $cropH);
		imagecolortransparent($final_image, imagecolorallocate($final_image, 0, 0, 0));
		imagecopyresampled($final_image, $cropped_rotated_image, 0, 0, $imgX1, $imgY1, $cropW, $cropH, $cropW, $cropH);
		
		//finally output png image
		//imagepng($final_image, $output_filename.$type, $png_quality);
		$filename = tempnam(sys_get_temp_dir(), "foo");
		imagejpeg($final_image, $filename, $jpeg_quality);
		
		if ($this->_upload_to_s3($filename, $output_filename))
		{
			$response = array(
				"status" => 'success',
				"url" => $this->image_url.$this->company_id."/".$output_filename,
			  );

			$image_data = Array(
				"image_group_id" => $image_group_id,
				"filename" => $output_filename
				);

			$this->Image_model->insert_image($image_data);
		}
		else
		{
			$response = array(
				"status" => 'fail!'
			  );
		}



		echo json_encode($response);

	}

	function delete_file_AJAX()
	{
		$src = isset($_POST['src']) ? $_POST['src'] : null;
		$image_group_id = isset($_POST['image_group_id']) ? $_POST['image_group_id'] : null;
		$filename = basename($src);
		$this->_delete_in_s3($filename);
		$this->Image_model->delete_image($filename, $image_group_id);
		$response = array(
		  'response'=>'success!'
		);
		echo json_encode($response);

	}

	function get_dimensions_AJAX()
	{
		$image_group_id = isset ($_POST['image_group_id']) ? $_POST['image_group_id'] : null;
		$dimensions = $this->Image_model->get_dimensions($image_group_id);
		if($dimensions && isset($dimensions['width']) && isset($dimensions['height']))
		{
			$width = $dimensions['width'];
			$height = $dimensions['height'];
		}
		else
		{
			$width = 200;
			$height = 150;
		}
		$response = array(
		  'response'=>'success!',
		  'width' => $width ,
		  'height' => $height
		);
		echo json_encode($response);
	}

	function _uri_exists($str = '', $uri)
	{
		if ($this->Company_model->uri_exists($uri, $this->company_id))
		{
			$this->form_validation->set_message('_uri_exists', "'$uri' is already taken");
        	return false;
		}	
		else
		{
			return true;
		}
	}

	// $output_filename should be:
	// "slide#" for slideshow
	// "gallery#" for gallery
	function _upload_to_s3($source_image, $output_filename) {
		
		// remove the s3 file first
		// Remember, _delete_in_s3 function also initializes s3->putBucket function.
		$this->_delete_in_s3($output_filename);
		
		// upload/update the s3 file with new GUID
		if ($this->s3->putObjectFile($source_image, getenv("AWS_S3_BUCKET"), $this->company_id."/".$output_filename, S3::ACL_PUBLIC_READ)) 
		{
			return true;
		}
		return false;
	
	}

	function _delete_in_s3($filename) {
		$this->s3->putBucket(getenv("AWS_S3_BUCKET"), S3::ACL_PUBLIC_READ);
		$this->s3->deleteObject(getenv("AWS_S3_BUCKET"), $this->company_id."/".$filename);					
	}

    function upload_to_s3 ($myId) {

        $output_filename = generate_guid();

        $filename = $_FILES["file"]["tmp_name"];
        list($width, $height) = getimagesize($filename);

        if ($this->_upload_to_s3($_FILES["file"]["tmp_name"], $output_filename))
        {
            $image_data = Array(
                "image_group_id" => $myId,
                "filename" => $output_filename
            );

            $this->Image_model->insert_image($image_data);

            $response = array(
                "status" => 'success',
                "url" => $this->image_url.$this->company_id."/".$output_filename,
                "width" => $width,
                "height" => $height
            );
        }
        else
        {
            $response = array(
                "status" => 'fail!'
            );
        }

        print json_encode($response);
    }

    function vendor_upload_to_s3 ($myId) {

        $output_filename = generate_guid();

        $filename = $_FILES["file"]["tmp_name"];
        list($width, $height) = getimagesize($filename);

        if ($this->_upload_to_s3($_FILES["file"]["tmp_name"], $output_filename))
        {
            $image_data = Array(
                "image_group_id" => $myId,
                "filename" => $output_filename
            );

            if($this->Image_model->get_images($myId)) {
            	$this->Image_model->update_image($image_data);
            } else {
            	$this->Image_model->insert_image($image_data);
            }

            $response = array(
                "status" => 'success',
                "url" => $this->image_url.$this->company_id."/".$output_filename,
                "width" => $width,
                "height" => $height
            );
        }
        else
        {
            $response = array(
                "status" => 'fail!'
            );
        }

        print json_encode($response);
    }
}