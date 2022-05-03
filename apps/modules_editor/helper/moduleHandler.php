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
	echo $result;
}

if(isset($_POST['testModule'])) 
{
	$response = shell_exec("lsmod | grep ".$_POST['cmd_line']);
	if($response) echo($response);
	else echo("The ".$_POST['cmd_line']." kernel module is not loaded!");
}

function ModuleManage($action, $moduleName, $moduleNameOld, $cmd_line, $options) 
{	
	if($action==1)
	{
		editModuleFile($moduleName, $moduleNameOld, $cmd_line, $options, "load");
	}
	else if($action==0) 
	{
		editModuleFile($moduleName, $moduleNameOld, $cmd_line, $options, "unload");
	}
	else if($action==-1)
	{
		editModuleFile($moduleName, $moduleNameOld, $cmd_line, $options, "remove");
	}
	sleep(3);	#wait the module to be loaded
	#test
	$response = shell_exec("lsmod | grep ".$cmd_line);
	if($response) echo($response);
	else echo("The ".$cmd_line." kernel module is not loaded!");
}

function editModuleFile($moduleName, $moduleNameOld, $cmd_line, $options, $action)
{	
	if(isClientLocal())
	{
		$filename = $moduleName.".xml";
		$writing = fopen(Config::backgroundWorker_path."modules/".$filename, 'w');
		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= "\r<modules>\r";
		$xml .= "\t<item name='".$moduleName."'>\r";
		$xml .= "\t\t<action>".$action."</action>\r";
		$xml .= "\t\t<moduleNameOld>".$moduleNameOld."</moduleNameOld>\r";
		$xml .= "\t\t<cmd_line>".$cmd_line."</cmd_line>\r";
		$xml .= "\t\t<options>".$options."</options>\r";
		$xml .= "\t</item>\r";
		$xml .= "</modules>";
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