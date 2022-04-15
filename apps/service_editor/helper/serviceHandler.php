<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/ 
if(isset($_POST['testService'])) {
	echo shell_exec("sudo systemctl status ".$_POST['serviceName'].".service");
}

if(isset($_POST['startService'])) {
	startService($_POST['serviceName'],$_POST['en']);
	echo shell_exec("sudo systemctl status ".$_POST['serviceName'].".service");
}

if(isset($_POST['stopService'])) {
	stopService($_POST['serviceName']);
	echo shell_exec("sudo systemctl status ".$_POST['serviceName'].".service");
}

function startService($serviceName,$en)
{
	if(enableService($serviceName,$en)) return shell_exec("sudo systemctl start ".$serviceName.".service");
}

function stopService($serviceName)
{
	return shell_exec("sudo systemctl stop ".$serviceName.".service");
}

function isServiceActive($serviceName)
{
	return shell_exec("systemctl is-active --quiet ".$serviceName.".service && echo 1 || echo 0");
}

function isServiceFileExist($serviceName)
{
	//return shell_exec("systemctl is-active --quiet ".$serviceName." && echo 1 || echo 0");
	return file_exists("/lib/systemd/system/".$serviceName.".service");
}

function removeService($serviceName)
{
	if(isServiceActive($serviceName))
	{
		shell_exec("sudo systemctl stop ".$serviceName.".service");
		shell_exec("sudo systemctl disable ".$serviceName.".service");
	
		if(isServiceFileExist($serviceName))
		{
			//shell_exec("sudo rm /lib/systemd/system/".$serviceName.".service");
			unlink("/lib/systemd/system/".$serviceName.".service");
		}
		if(isServiceActive($serviceName))
		{		
			//shell_exec("sudo systemctl daemon-reload");
			shell_exec("sudo systemctl reset-failed");
		}
	}
}

function editServiceFile($serviceName,$cmd_line,$unitOptions,$serviceOptions,$installOptions)
{
	$script = "[Unit]\nDescription=".$serviceName."\n".$unitOptions."\n\n[Service]\nExecStart=".$cmd_line.
	"\n".$serviceOptions."\n\n[Install]\n".$installOptions;
    $writing = fopen("/lib/systemd/system/".$serviceName.".service", 'w');
	fputs($writing, $script);
    fclose($writing);
}

function enableService($serviceName,$en)
{
	if(isServiceActive($serviceName))
	{
		if($en)
		{
			#shell_exec("sudo systemctl daemon-reload");
			shell_exec("sudo systemctl enable ".$serviceName.".service");	
			return 1;
		}
		else
		{
			#shell_exec("sudo systemctl daemon-reload");
			shell_exec("sudo systemctl disable ".$serviceName.".service");	
			return 0;
		}
	}
}

function demonReload()
{
	shell_exec("sudo systemctl daemon-reload");
}

function createService($serviceName, $en, $serviceNameOld, $cmd_line,$unitOptions,$serviceOptions,$installOptions)
{
	//create a new service
	removeService($serviceNameOld);
	editServiceFile($serviceName,$cmd_line,$unitOptions,$serviceOptions,$installOptions);
	startService($serviceName,$en);
	//demonReload();
}
?>