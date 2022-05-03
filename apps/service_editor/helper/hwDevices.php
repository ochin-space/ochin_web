<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/ 
if(isset($_POST['video'])) {
	#echo shell_exec("v4l2-ctl --list-devices");
	echo shell_exec("ls /dev/video*");
}

if(isset($_POST['usb'])) {
	echo shell_exec("lsusb");
}

if(isset($_POST['services'])) {
	echo shell_exec("systemctl --type=service --state=active");
} 

?>