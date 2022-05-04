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

if(isset($_POST['testService'])) {
	echo shell_exec("systemctl status ".$_POST['serviceName'].".service");
}

function startService($serviceName,$action,$serviceNameOld,$cmd_line,$unitOptions,$serviceOptions,$installOptions)
{	
	editServiceFile($serviceName,$serviceNameOld,$cmd_line,$unitOptions,$serviceOptions,$installOptions,$action);
	sleep(3);	#wait the module to be loaded
	#test
	$response = shell_exec("systemctl status ".$_POST['serviceName'].".service");
	if($response) 
	{
		echo($response);
		exit(0);
	}
	else
	{
		echo("The ".$cmd_line." service is not loaded!");
		exit(0);
	}
}

function isServiceActive($serviceName)
{
	return shell_exec("systemctl is-active --quiet ".$serviceName.".service && echo 1 || echo 0");
}

function removeService($serviceName,$serviceNameOld,$cmd_line,$unitOptions,$serviceOptions,$installOptions)
{
	editServiceFile($serviceName,$serviceNameOld,$cmd_line,$unitOptions,$serviceOptions,$installOptions,"remove");
}

function editServiceFile($serviceName,$serviceNameOld,$cmd_line,$unitOptions,$serviceOptions,$installOptions,$action)
{	
	if(isClientLocal())
	{
		$filename = $serviceName.".xml";
		$writing = fopen(Config::backgroundWorker_path."services/".$filename, 'w');
		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= "\r<services>\r";
		$xml .= "\t<item name='".$serviceName."'>\r";
		$xml .= "\t\t<serviceNameOld>".$serviceNameOld."</serviceNameOld>\r";
		$xml .= "\t\t<action>".$action."</action>\r";
		$xml .= "\t\t<cmd_line>".$cmd_line."</cmd_line>\r";
		$xml .= "\t\t<unitOptions>".$unitOptions."</unitOptions>\r";
		$xml .= "\t\t<serviceOptions>".$serviceOptions."</serviceOptions>\r";
		$xml .= "\t\t<installOptions>".$installOptions."</installOptions>\r";
		$xml .= "\t</item>\r";
		$xml .= "</services>";
		fputs($writing, $xml);
		fclose($writing);
		return 1;
	}
	else
	{
		return 0;
	}
}

?>