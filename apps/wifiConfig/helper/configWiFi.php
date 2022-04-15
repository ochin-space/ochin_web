<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/ 
function enableAdapter($en, $name)
{
	return true;
}

function mask2cidr($mask)
{
  $long = ip2long($mask);
  $base = ip2long('255.255.255.255');
  return 32-log(($long ^ $base)+1,2);

  /* xor-ing will give you the inverse mask,
      log base 2 of that +1 will return the number
      of bits that are off in the mask and subtracting
      from 32 gets you the cidr notation */
}

//remove the script from the dhcpcd file
function removeOldScript($name,$filename)
{
	$start = "#start script ".$name." from ochin web\n";
	$stop = "#end script ".$name." from ochin web\n";
	//shell_exec("sudo cp ".$filename." /tmp/dhcpcd.conf");	
	
	$drop = false;
	$reading = fopen($filename, 'r');
    $writing = fopen('/tmp/file.tmp', 'w');
	
	while(!feof($reading)) 
    {
        $line = fgets($reading);          
		if(strcmp($line,$start)==0)  $drop = true;
		if($drop == false) fputs($writing, $line);
		if(strcmp($line,$stop)==0)  $drop = false;
    }
    fclose($reading);
    fclose($writing);
	shell_exec("sudo mv /tmp/file.tmp ".$filename);	
}

//configure the static ip of the AP
function dhcpcd_conf($name,$ipaddress,$cidr)
{
	removeOldScript($name,"/etc/dhcpcd.conf");
	$start = "#start script ".$name." from ochin web\n";
	$stop = "#end script ".$name." from ochin web\n";
	$content = "interface ".$name."\n";
	$content = $content."\tstatic ip_address=".$ipaddress."/".$cidr."\n";
	$content = $content."\tnohook wpa_supplicant\n";
	$file = fopen("/etc/dhcpcd.conf", 'a');
	fputs($file,$start.$content.$stop);
	fclose($file);
}

//configure the DHCP server config or disable
function dnsmasq_conf($en,$name,$ipaddress,$netmask,$dhcpstart,$dhcpstop)
{
	if($en)	//for AP mode
	{
		$file = fopen("/etc/dnsmasq.conf", 'w');
		$content = "interface=".$name."\n";
		$content = $content."dhcp-range=".$dhcpstart.",".$dhcpstop.",".$netmask.",".mask2cidr($netmask)."h\n";
		$content = $content."domain=".substr($name, 0, -1)."\n";
		$content = $content."address=/gw.wlan/".$ipaddress;
		fputs($file,$content);
		fclose($file);
	}
	else	//STA mode
	{
		shell_exec("sudo rm /etc/dnsmasq.conf");	//remove file
	}
}

//configure AP ssid, passwd, ccode etc..  or disable
function hostapd_conf($en,$name,$ccode,$ssid,$passw)
{
	if($en)	//for AP mode
	{
		$file = fopen("/etc/hostapd/hostapd.conf", 'w');
		$content = "country_code=".$ccode."\n";
		$content = $content."interface=".$name."\n";
		$content = $content."ssid=".$ssid."\n";
		$content = $content."hw_mode=g\nchannel=7\nmacaddr_acl=0\nauth_algs=1\nignore_broadcast_ssid=0\n";
		$content = $content."wpa=2\nwpa_passphrase=".$passw."\nwpa_key_mgmt=WPA-PSK\n";
		$content = $content."wpa_pairwise=TKIP\nrsn_pairwise=CCMP";
		fputs($file,$content);
		fclose($file);
	}
	else	//STA mode
	{
		//shell_exec("sudo rm /etc/hostapd/hostapd.conf");	//remove file
	}
}

//get, if there is one, the AP associated interface name
function getAPdevice()
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
}

function wpa_supplicant_conf($name,$ssid,$passw)
{
	removeOldScript($name,"/etc/wpa_supplicant/wpa_supplicant.conf");
	$start = "#start script $name from ochin web\n";
	$stop = "#end script $name from ochin web\n";
	
	$file = fopen("/etc/wpa_supplicant/wpa_supplicant.conf", 'a');
	$content = "network={\n";
	$content = $content."\tssid=\"$ssid\"\n";
	$content = $content."\tpsk=\"$passw\"\n";
	$content = $content."\tid_str=\"$name\"\n}\n";
	fputs($file,$start.$content.$stop);
	fclose($file);
}

function hostapdStart($en)
{	
	if($en)	//for AP mode
	{
		shell_exec("sudo systemctl enable hostapd.service");	//start hostapd.service
		shell_exec("sudo systemctl reboot");	//reboot
	}
	else	//STA mode
	{
		shell_exec("sudo systemctl disable hostapd.service");	//disable hostapd service
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
			removeOldScript($name,"/etc/wpa_supplicant/wpa_supplicant.conf");
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
				removeOldScript($name,"/etc/dhcpcd.conf");
			}
			wpa_supplicant_conf($name,$ssid,$passw);
			dnsmasq_conf(false,$name,$ipaddress,$netmask,$dhcpstart,$dhcpstop);	//configure the DHCP server config or disable
			hostapd_conf(false,$name,$ccode,$ssid,$passw);	//configure AP ssid, passwd, ccode etc..  or disable
			shell_exec("sudo systemctl reboot");	//reboot
		}
	}
	else
	{
		removeOldScript($name,"/etc/dhcpcd.conf");
		removeOldScript($name,"/etc/wpa_supplicant/wpa_supplicant.conf");
	}	
}

?>
					