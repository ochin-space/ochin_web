<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/ 
if(isset($_POST['testModule'])) {
	$response = shell_exec("sudo lsmod | grep ".$_POST['cmd_line']);
	if($response) echo($response);
	else echo("The ".$_POST['cmd_line']." kernel module is not loaded!");
}

if(isset($_POST['loadModule'])) {
	loadModule($_POST['cmd_line'], $_POST['options']);
	$response = shell_exec("sudo lsmod | grep ".$_POST['cmd_line']);
	if($response) echo($response);
	else echo("The ".$_POST['cmd_line']." kernel module is not loaded!");
}

if(isset($_POST['unloadModule'])) {
	unloadModule($_POST['cmd_line'], $_POST['options']);
	$response = shell_exec("sudo lsmod | grep ".$_POST['cmd_line']);
	if($response) echo($response);
	else echo("The ".$_POST['cmd_line']." kernel module is not loaded!");
}

function isModuleFileExist($moduleName)
{
	return file_exists("/etc/modules-load.d/".$moduleName.".conf");
}

function isOptionsFileExist($moduleName)
{
	return file_exists("/etc/modprobe.d/".$moduleName.".conf");
}

function removeModuleFile($moduleName)
{
	if(isModuleFileExist($moduleName))
	{
		unlink("/etc/modules-load.d/".$moduleName.".conf");
	}
}

function removeOptionsFile($moduleName)
{
	if(isOptionsFileExist($moduleName))
	{
		unlink("/etc/modprobe.d/".$moduleName.".conf");
	}
}

function editModuleFile($moduleName,$cmd_line)
{
    $writing = fopen("/etc/modules-load.d/".$moduleName.".conf", 'w');
	fputs($writing, $cmd_line);
    fclose($writing);	
}

function editOptionsFile($moduleName,$cmd_line, $options)
{
	$script = "options ".$cmd_line." name=".$cmd_line." ".$options;
    $writing = fopen("/etc/modprobe.d/".$moduleName.".conf", 'w');
	fputs($writing, $script);
    fclose($writing);	
}

function loadModule($cmd_line, $options)
{
	shell_exec("sudo modprobe ".$cmd_line." ".$options);
}

function unloadModule($cmd_line, $options)
{
	shell_exec("sudo modprobe -r ".$cmd_line);
}

function createModule($moduleName, $en, $moduleNameOld, $cmd_line, $options) 
{
	unloadModule($cmd_line, $options);
	removeModule($moduleNameOld);
	if($en) //create a new service
	{
		editModuleFile($moduleName, $cmd_line);
		editOptionsFile($moduleName, $cmd_line, $options);
		loadModule($cmd_line, $options);
	}
}

function removeModule($moduleNameOld) 
{
	removeModuleFile($moduleNameOld);
	removeOptionsFile($moduleNameOld);
}

?>