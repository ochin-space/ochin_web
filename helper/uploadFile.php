<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/ 
if(isset($_FILES['file']['name'])){
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
?>