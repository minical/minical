<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Image_lib extends CI_Image_lib {
	
	function make_thumbnail($tempimage)
	{
		$configthumb['image_library'] = 'gd2';
		$configthumb['source_image']    = $tempimage;
		$configthumb['maintain_ratio'] = TRUE;
		$configthumb['create_thumb'] = TRUE;
		$configthumb['thumb_marker'] = "_thumb";
		$configthumb['quality'] = '100';
		$configthumb['width']     = 120;
		$configthumb['height']    = 80;

		$this->initialize($configthumb);
		if ( ! $this->resize())
		{
			echo $this->display_errors();
		}
		unset($configthumb);
	} 

	function make_logo($tempimage)
	{
		$configthumb['image_library'] = 'gd2';
		$configthumb['source_image']    = $tempimage;
		$configthumb['maintain_ratio'] = TRUE;
		$configthumb['create_thumb'] = TRUE;
		$configthumb['thumb_marker'] = "";
		$configthumb['quality'] = '100';
		$configthumb['width']     = 250;
		$configthumb['height']    = 100;

		$this->initialize($configthumb);
		if ( ! $this->resize())
		{
			echo $this->display_errors();
		}
		unset($configthumb);
	} 
	
	function get_thumbnail_name($file_name)
	{
		
		//if there is an extension and a period(.)
		//because on localhost, the file_name looks like "C:\temp\blah.tmp"
		//while on servers (staging/production), the file_name looks like "WTF\WTFFF"
		if (strcspn($file_name, '.') < strlen($file_name))
		{
			$dir = strstr($file_name, '.', true);
			$extension = substr(strrchr($file_name,'.'),1);
			return $dir."_thumb.".$extension;
		}
		else //otherwise, there's no period(.), so just add _thumb at the end
		{
			return $file_name."_thumb";
		}
	}

}
