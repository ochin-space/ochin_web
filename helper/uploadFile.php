<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/ 
function isClientLocal()
{
	$clientNet =  pathinfo($_SERVER['REMOTE_ADDR'],PATHINFO_FILENAME);
	$serverNet = pathinfo($_SERVER['SERVER_ADDR'],PATHINFO_FILENAME);
	$result=0;
	if(strcmp($serverNet,$clientNet)==0) $result=1; else $result=0;
	return $result;
}

if(isset($_FILES['file']['name']))
{
	if(isClientLocal())
	{
		// file name
		$filename = $_FILES['file']['name'];
		// Location
		$location = '../tmp/'.$filename;
		// file extension
		$file_extension = pathinfo($location, PATHINFO_EXTENSION);
		$file_extension = strtolower($file_extension);
		// Valid extensions
		$valid_ext = array("zip");
		$response = 0;
		if(in_array($file_extension,$valid_ext)){
			// Upload file
			if(move_uploaded_file($_FILES['file']['tmp_name'],$location)){
				$response = 1;
			} 
		}
		echo $response;
		exit;
	}
	else
	{
		echo 0;
		exit;
	}
}
?>