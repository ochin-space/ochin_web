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

function enableAdapter($en, $name)
{
	return true;
}

function mask2cidr($mask)
{
  $long = ip2long($mask);
  $base = ip2long('255.255.255.255');
  return 32-log(($long ^ $base)+1,2);

  // xor-ing will give you the inverse mask,
  //    log base 2 of that +1 will return the number
  //    of bits that are off in the mask and subtracting
  //    from 32 gets you the cidr notation 
}

#remove the file from the system 
function removeFile($filename)
{	
	if(isClientLocal())
	{
		$writing = fopen(Config::backgroundWorker_path."files2remove/".str_replace("/", "@", $filename), 'w');
		fputs($writing, "remove");
		fclose($writing);	
		return 1;
	}
	else
	{
		return 0;
	}
}

#update the content of the filename in the system 
function updateFile($filename, $lines)
{	
	if(isClientLocal())
	{
		$writing = fopen(Config::backgroundWorker_path."files2update/".str_replace("/", "@", $filename), 'w');
		fputs($writing, $lines);
		fclose($writing);	
		return 1;
	}
	else
	{
		return 0;
	}
}

//remove the script from the dhcpcd file
function removeOldScript($name,$filename)
{
	if(isClientLocal())
	{
		$start = "#start script ".$name." from ochin web\n";
		$stop = "#end script ".$name." from ochin web\n";
		
		$drop = false;
		$reading = fopen($filename, 'r');
		$writing = "";
		
		while(!feof($reading)) 
		{
			$line = fgets($reading);          
			if(strcmp($line,$start)==0)  $drop = true;
			if($drop == false) $writing = $writing.$line;
			if(strcmp($line,$stop)==0)  $drop = false;
		}
		fclose($reading);
		return $writing;
		return 1;
	}
	else
	{
		return 0;
	}
}

//configure the static ip of the AP
function dhcpcd_conf($name,$ipaddress,$cidr)
{
	if(isClientLocal())
	{
		$content = removeOldScript($name,"/etc/dhcpcd.conf");
		if($name!="" && $ipaddress!="" && $cidr!="")
		{
			$content = $content."#start script ".$name." from ochin web\n";
			$content = $content."interface ".$name."\n";
			$content = $content."\tstatic ip_address=".$ipaddress."/".$cidr."\n";
			$content = $content."\tnohook wpa_supplicant\n";
			$content = $content."#end script ".$name." from ochin web\n";
		}
		updateFile("/etc/dhcpcd.conf", $content);
		return 1;
	}
	else
	{
		return 0;
	}
}

//configure the DHCP server config or disable
function dnsmasq_conf($en,$name,$ipaddress,$netmask,$dhcpstart,$dhcpstop)
{
	if(isClientLocal())
	{
		if($en)	//for AP mode
		{
			$content = "interface=".$name."\n";
			$content = $content."dhcp-range=".$dhcpstart.",".$dhcpstop.",".$netmask.",".mask2cidr($netmask)."h\n";
			$content = $content."domain=".substr($name, 0, -1)."\n";
			$content = $content."address=/gw.wlan/".$ipaddress;
			updateFile("/etc/dnsmasq.conf", $content);
		}
		else	//STA mode
		{
			removeFile("/etc/dnsmasq.conf");	//remove file
		}
		return 1;
	}
	else
	{
		return 0;
	}
}

//configure AP ssid, passwd, ccode etc..  or disable
function hostapd_conf($en,$name,$ccode,$ssid,$passw)
{
	if(isClientLocal())
	{
		if($en)	//for AP mode
		{
			$lines = removeOldScript($name,"/etc/hostapd/hostapd.conf");
			$content = "country_code=".$ccode."\n";
			$content = $content."interface=".$name."\n";
			$content = $content."ssid=".$ssid."\n";
			$content = $content."hw_mode=g\nchannel=7\nmacaddr_acl=0\nauth_algs=1\nignore_broadcast_ssid=0\n";
			$content = $content."wpa=2\nwpa_passphrase=".$passw."\nwpa_key_mgmt=WPA-PSK\n";
			$content = $content."wpa_pairwise=TKIP\nrsn_pairwise=CCMP";
			updateFile("/etc/hostapd/hostapd.conf", $content);
		}
		else	//STA mode
		{
			removeFile("/etc/hostapd/hostapd.conf");	//remove file
		}
		return 1;
	}
	else
	{
		return 0;
	}
}

//get, if there is one, the AP associated interface name
function getAPdevice()
{
	if(isClientLocal())
	{
		if($file = fopen("/etc/hostapd/hostapd.conf", 'r'))
		{
			while (!feof($file)) 
			{
				$line = fgets($file);  
				if(strstr($line,"interface=")!=False)  //find cmd_line
				{
					return substr($line, 10);
					break;
				}
			}
			fclose($file);
		}
		return false;
		return 1;
	}
	else
	{
		return 0;
	}
}

function wpa_supplicant_conf($name,$ssid,$passw)
{
	if(isClientLocal())
	{
		$content = removeOldScript($name,"/etc/wpa_supplicant/wpa_supplicant.conf");
		if($name!="" && $ssid!="" && $passw!="")
		{
			$start = "#start script ".$name." from ochin web\n";
			$stop = "#end script ".$name." from ochin web\n";
			
			$content = $content.$start."network={\n";
			$content = $content."\tssid=\"".$ssid."\"\n";
			$content = $content."\tpsk=\"".$passw."\"\n";
			$content = $content."\tid_str=\"".$name."\"\n}\n".$stop;
		}
		updateFile("/etc/wpa_supplicant/wpa_supplicant.conf", $content);
		return 1;
	}
	else
	{
		return 0;
	}
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

function hostapdStart($en)
{	
	if($en)	//for AP mode
	{
		//shell_exec("sudo systemctl enable hostapd.service");	//start hostapd.service
		//shell_exec("sudo systemctl reboot");	//reboot
		editServiceFile("hostapd","","","","","","sysLoad");
	}
	else	//STA mode
	{
		editServiceFile("hostapd","","","","","","unload");
		//shell_exec("sudo systemctl disable hostapd.service");	//disable hostapd service
	}
}

function configWiFi($en,$name,$ccode,$mode,$ssid,$passw,$staticipSw,$ipaddress,$netmask,$dhcpstart,$dhcpstop)
{	
	//encrypt password
	//$wpa_passphrase = shell_exec("wpa_passphrase $ssid $passw");
	//$startpos = strpos($wpa_passphrase, "\n\tpsk=",0)+6;
	//$endpos = strpos($wpa_passphrase, "\n",$startpos);
	//$passw = substr($wpa_passphrase,$startpos,$endpos-$startpos);
	
	if($en == 'true')	//adapter enabled
	{
		if($mode == 'true') //AP mode
		{			
			wpa_supplicant_conf("","","");	//remove the ochin_web script from the wpa_supplicant file
			dhcpcd_conf($name,$ipaddress,mask2cidr($netmask));	//configure the static ip of the AP
			dnsmasq_conf(true,$name,$ipaddress,$netmask,$dhcpstart,$dhcpstop);	//configure the DHCP server config or disable
			hostapd_conf(true,$name,$ccode,$ssid,$passw);	//configure AP ssid, passwd, ccode etc..  or disable
			hostapdStart(true);	//start hostapd service and reboot
		}
		else	//sta mode
		{
			if(strstr(getAPdevice(),$name)!=False) hostapdStart(false);	//stop hostapd service only if this int was used as AP
			if($staticipSw == 'true')
			{
				dhcpcd_conf($name,$ipaddress,mask2cidr($netmask));	//configure the static IP of the client or leave it in auto (DHCP) mode	
			}
			else
			{
				dhcpcd_conf("","","");
			}
			wpa_supplicant_conf($name,$ssid,$passw);
			dnsmasq_conf(false,$name,$ipaddress,$netmask,$dhcpstart,$dhcpstop);	//remove the DHCP server config 
			hostapd_conf(false,$name,$ccode,$ssid,$passw);	//remove the AP ssid, passwd, ccode etc..  
		}
	}
	else
	{
		removeFile("/etc/dhcpcd.conf");
		removeFile("/etc/wpa_supplicant/wpa_supplicant.conf");
	}	
}

?>
					