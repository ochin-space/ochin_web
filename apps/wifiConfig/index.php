<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/ 
session_start();
require 'helper/configWiFi.php';
require 'helper/Config.php';

if(isset($_SESSION["loggedin"]) && ($_SESSION["loggedin"]==true)) 
{
    if(isset($_POST['update'])) 
	{
		configWiFi($_POST['en'],$_POST['name'],$_POST['ccode'],$_POST['mode'],$_POST['ssid'],$_POST['passw'],$_POST['staticipSw'],$_POST['ipaddress'],$_POST['netmask'],$_POST['dhcpstart'],$_POST['dhcpstop']);
    }
	
	function getAdapters()
	{
		$result = shell_exec("ip -br link show");
		$result = str_replace(array("<",">"), " ", $result);	
		$result1 = shell_exec("ip -br addr show");	
		
		$rows  = preg_split("/(\r\n|\n|\r)/",$result); 
		foreach($rows  as &$row) {
			$row = preg_split('/\s+/', $row, -1, PREG_SPLIT_NO_EMPTY);
		}
		
		$rows1 = explode("\n", $result1); 
		foreach($rows1 as &$row1) {
			$row1 = preg_split('/\s+/', $row1, -1, PREG_SPLIT_NO_EMPTY);
		}
		$out = array();
		for($i=0; $i<sizeof($rows)-1; $i++)
		{
		  $out[$i] = array('name' => $rows[$i][0], 'status' => $rows[$i][1], 'IP'=>$rows1[$i][2],'MAC'=> $rows[$i][2], 'info'=> $rows[$i][3]);
		}
		return $out;
	}
	$rows = getAdapters();
?>

<!DOCTYPE html>
<html>
<head>
<link href="css/loader.css" rel="stylesheet">
<script type="text/javascript" src=<?php echo Config::jQueryPath;?>></script> 
<!-- Required meta tags for Bootstrap 5-->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- Bootstrap CSS -->
<link href=<?php echo Config::bootstrapCSSpath;?> rel="stylesheet">
<!-- Bootstrap js -->
<script src=<?php echo Config::bootstrapJSpath;?>></script>
<?php include Config::topbar_path;?>
<title>öchìn Web GUI</title>
</head>
    <body style="background-color:#f2f2f2;">
	<div class="row">	
		<div id="banner" class="fs-5 p-2 mb-1 bg-warning text-dark text-center">This client is not connected locally. For security reasons, all functions that require advanced access to the operating system are inhibited. To use this web page it is necessary to be connected to the same subnet of the server.</div>
	</div>
        <div class="container-xl">
			<div class="row">	
				<div class="col-sm-10">			
					<div class=" display-2 text-dark mt-2 mb-4 font-weight-normal text-center">Network Configuration</div>
				</div>
				<div class="col-sm-2 ">		
					<div class="d-flex justify-content-end mb-2 mt-5" >
						<button type="button" class="d-flex me-4" name="info" style="background-color: Transparent; border: none;"  title="Click for info about this page" data-bs-toggle='modal' data-bs-target='#infoModal'"><img  width="40" height="40" src="icons/info.png"></button>
					</div>
				</div>        
				<div id="loader" class=""></div>
				<div class="row p-3 rounded-2" style="background-color:white;">
					<div class="row">
						<div class="fs-3 text-muted">List of Network Adapters</div>
					</div>
					<div class="row">
						<div class="table-responsive" style="max-height:600px;">
							<table  class="table table-light table-striped" id="autoexecTable">
								<thead>
									<tr>
										<th scope="col">Name</th>
										<th scope="col">Status</th>
										<th scope="col">IP Address</th>
										<th scope="col">MAC Address</th>
										<th scope="col">Info</th>
										<th scope="col"></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($rows as $index=>$row) : ?>
										<tr>
											<td value="<?php echo $row['name']; ?>"><?php echo $row['name']; ?></td>
											<td value="<?php echo $row['status']; ?>"><?php echo $row['status']; ?></td>
											<td value="<?php echo $row['IP']; ?>"><?php echo $row['IP']; ?></td>
											<td value="<?php echo $row['MAC']; ?>"><?php echo $row['MAC']; ?></td>
											<td value="<?php echo $row['info']; ?>"><?php echo $row['info']; ?></td>
											<td width='1%' white-space='nowrap'>
												<button type='button' class='btn btn-primary btn-sm' title="Click to edit the config" data-bs-toggle='modal' data-bs-target='#editModal' 
												data-bs-status="<?php echo $row['status'];?>" data-bs-name="<?php echo $row['name'];?>" data-bs-ip="<?php echo $row['IP'];?>">
												Edit</button>
											</td> 
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
        </div>


        <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Are you sure you want to add a line?</h5>
                    </div>
                    <div class="modal-footer">
                        <button type='button' class="btn btn-primary" data-bs-dismiss="modal" onclick="insertRow()">Yes</button> 
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button></div>
                    </div>
                </div>
            </div>
        </div>
		
		<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<div class="modal-title mb-5">
							<label for="adapter-name" class="col-form-label fs-4">Adapter Name: </label>
							<label id="adapter-name" class="fs-4" value="">
						</div>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<form>
							<div class="mb-3">
								<input hidden type="text" class="form-control" id="recipient-id">
							</div>
							<div class="mb-3">
								<div class="form-check form-switch">
								  <label class="form-check-label" for="enableAdapter" id="enableAdapterLabel"></label>
								  <input class="form-check-input" type="checkbox" id="enableAdapter" onchange="enAdapter();">
								</div
							<div class="mb-3">
								<div class="form-check form-switch" id="modeBox">
								  <label class="form-check-label" for="mode" id="modeSwitchLabel"></label>
								  <input class="form-check-input" type="checkbox" id="modeSwitch" onchange="modeSet();">
								</div>
							</div>
							<div class="mb-3">
								<div class="container" id="credBox">
									<div class="row">
										<div class="col" >
											<label for="recipient-name" class="col-form-label" >SSID:</label>
											<input type="text" class="form-control" id="ssid" placeholder="Insert the SSID" value="">
										</div>
										<div class="col">
											<label for="recipient-name" class="col-form-label">Password:</label>
											<input type="password" class="form-control" id="passwd" placeholder="Insert the Password" value="">
										</div>
									</div>
								</div>
							</div>
							<div class="mb-3 visually-hidden" id="ccodeBox">
							  <label class="col-form-label" for="ccode">Country Code</label>
							  <input class="form-control col me-2 " style="width:60px;" placeholder="IT" id="ccode">
							</div>
							<div class="form-check form-switch visually-hidden" id="staticIpSwitchBox">
							  <label class="form-check-label" for="staticIp">Static IP</label>
							  <input class="form-check-input" type="checkbox" id="staticIpSwitch" onchange="staticIP();">
							</div>
							<div class="mb-3 visually-hidden" id="ipaddressBox">
								<label for="ipAddr" class="col-form-label" id="ipAddrLabel"></label>
								<div class="container" id="ipAddr">
									<div class="row">
										<input class="form-control col me-2" id="ipAddr0" placeholder="192" value="192" onchange="ipAddr0change();"></input>
										<input class="form-control col me-2" id="ipAddr1" placeholder="168" value="168" onchange="ipAddr1change();"></input>
										<input class="form-control col me-2" id="ipAddr2" placeholder="1" value="1"  onchange="ipAddr2change();"></input>
										<input class="form-control col" id="ipAddr3" placeholder="..."  value="1"  onchange="ipAddr3change();" ></input>
									</div>
								</div>
							</div>
							<div class="mb-3 visually-hidden" id="netmaskBox">
								<label for="recipient-nm" class="col-form-label ">NetMask:</label>
								<div class="container" id="recipient-nm">
									<div class="row">
										<input class="form-control col me-2" id="nm0" placeholder="255"  value="255" ></input>
										<input class="form-control col me-2" id="nm1" placeholder="255"  value="255" ></input>
										<input class="form-control col me-2" id="nm2" placeholder="255"  value="255" ></input>
										<input class="form-control col" id="nm3" placeholder="0"  value="0" ></input>
									</div>
								</div>
							</div>
							<div class="mb-3 visually-hidden" id="dhcp_startIpBox">
								<label for="recipient-dhcps" class="col-form-label ">DHCP start Address:</label>
								<div class="container" id="recipient-dhcps">
									<div class="row">
										<input class="form-control col me-2" id="dhcps0" placeholder="192" value="192" readonly></input>
										<input class="form-control col me-2" id="dhcps1" placeholder="168" value="168" readonly></input>
										<input class="form-control col me-2" id="dhcps2" placeholder="1" value="1" readonly></input>
										<input class="form-control col" id="dhcps3" placeholder="..."  value="2" ></input>
									</div>
								</div>
							</div>
							<div class="mb-3 visually-hidden" id="dhcp_stopIpBox">
								<label for="recipient-dhcpe" class="col-form-label ">DHCP end Address:</label>
								<div class="container" id="recipient-dhcpe">
									<div class="row">
										<input class="form-control col me-2" id="dhcpe0" placeholder="192" value="192" readonly ></input>
										<input class="form-control col me-2" id="dhcpe1" placeholder="168" value="168" readonly ></input>
										<input class="form-control col me-2" id="dhcpe2" placeholder="1" value="1" readonly ></input>
										<input class="form-control col" id="dhcpe3" placeholder="..."  value="127" ></input>
									</div>
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary" data-bs-dismiss="modal" data-bs-toggle='modal' data-bs-target='#confirmModal'>Update</button>
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					</div>
				</div>
			</div>
		</div>

        <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">The changes will take effect after the next system reboot.<br>Are you sure you want to update the network configuration?</h5>
                    </div>
                    <div class="modal-footer">
                        <button type='button' class="btn btn-primary" data-bs-dismiss="modal" onclick="update()">Yes</button> 
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

		<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="editModalLabel">Attention!</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body" id="alertModalbody">
					</div>  
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg modal-dialog-centered" >
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="editModalLabel">Network Configuration guide</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"  style="height: 80vh; overflow-y: auto;">
						<p><?php include('info.html'); ?></p>
					</div>					
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
    </body>
</html>

<script>
isClientLocal();

function isClientLocal()
{ 
	var banner = document.getElementById("banner");
	if(<?php echo isClientLocal();?>)
	{
		banner.style.display = "none";
	}
	else
	{
		banner.style.display = "block";
	}
}

function check_TCPIP_Quartet(a,b,c,d)
{
	if((a>=0&&a<=255)&&(b>=0&&b<=255)&&(c>=0&&c<=255)&&(d>=0&&d<=255)) return true
	else return false
}

function update()
{
	if(<?php echo isClientLocal();?>)
	{		
		en = document.getElementById('enableAdapter').checked;
		mode = document.getElementById('modeSwitch').checked;
		name = document.getElementById('adapter-name').innerHTML;
		if(document.getElementById('ccode').value.length>=2 || mode==false) ccode = document.getElementById('ccode').value;
		else
		{
			document.getElementById('alertModalbody').innerHTML = "<p>Please enter a valid Country Code</p>";
			$('#alertModal').modal('show');
			return;
		}	
		if(document.getElementById('ssid').value) ssid = document.getElementById('ssid').value;
		else if(en==true)
		{
			document.getElementById('alertModalbody').innerHTML = "<p>Please enter a valid SSID</p>";
			$('#alertModal').modal('show');
			return;
		}
		else ssid = "";
		if(document.getElementById('passwd').value.length>=8) passw = document.getElementById('passwd').value;
		else if(en==true)
		{
			document.getElementById('alertModalbody').innerHTML = "<p>Please enter a valid Password (8 char min)</p>";
			$('#alertModal').modal('show');
			return;
		}
		else passw = "";
		staticipSw = document.getElementById('staticIpSwitch').checked;
		if(staticipSw || mode)
		{
			a = document.getElementById('ipAddr0').value;
			b = document.getElementById('ipAddr1').value;
			c =document.getElementById('ipAddr2').value;
			d = document.getElementById('ipAddr3').value;
			if(check_TCPIP_Quartet(a,b,c,d)) ipaddress = a + "\." + b + "\." + c + "\." + d; 
			else 
			{
				document.getElementById('alertModalbody').innerHTML = "<p>Please enter a valid IP Address</p>";
				$('#alertModal').modal('show');
				return;
			}
			
			a = document.getElementById('nm0').value;
			b = document.getElementById('nm1').value;
			c =document.getElementById('nm2').value;
			d = document.getElementById('nm3').value;
			if(check_TCPIP_Quartet(a,b,c,d)) netmask = a + "\." + b + "\." + c + "\." + d; 
			else 
			{
				document.getElementById('alertModalbody').innerHTML = "<p>Please enter a valid NetMask</p>";
				$('#alertModal').modal('show');
				return;
			}
		}
		else
		{
			ipaddress="";
			netmask="";
		}
		
		if(mode)
		{
			a = document.getElementById('dhcps0').value;
			b = document.getElementById('dhcps1').value;
			c =document.getElementById('dhcps2').value;
			d = document.getElementById('dhcps3').value;
			if(check_TCPIP_Quartet(a,b,c,d)) dhcpstart = a + "\." + b + "\." + c + "\." + d; 
			else 
			{
				document.getElementById('alertModalbody').innerHTML = "<p>Please enter a valid DHCP Start Ip Address</p>";
				$('#alertModal').modal('show');
				return;
			}
			
			a = document.getElementById('dhcpe0').value;
			b = document.getElementById('dhcpe1').value;
			c =document.getElementById('dhcpe2').value;
			d = document.getElementById('dhcpe3').value;
			if(check_TCPIP_Quartet(a,b,c,d)) dhcpstop = a + "\." + b + "\." + c + "\." + d; 
			else 
			{
				document.getElementById('alertModalbody').innerHTML = "<p>Please enter a valid DHCP Stop Ip Address</p>";
				$('#alertModal').modal('show');
				return;
			}
		}
		else
		{
			dhcpstart="";
			dhcpstop="";
		}
		//alert("en:"+en+" name:"+name+" mode:"+mode+" ssid:"+ssid+" passw:"+passw+" staticipSw:"+staticipSw);
		//alert("ipaddress:"+ipaddress+" netmask:"+netmask+" dhcpstart:"+dhcpstart+" dhcpstop:"+dhcpstop);
		document.getElementById('loader').innerHTML = '<div class="loader"></div>';
		$.ajax({
			type : "POST",  //type of method
			url  : "index.php",  //your page
			data: "update&en=" + en + "&name=" + name+ "&ccode=" + ccode + "&mode=" + mode + "&ssid=" + ssid + "&passw=" + passw 
			+ "&staticipSw=" + staticipSw + "&ipaddress=" + ipaddress + "&netmask=" + netmask + "&dhcpstart=" + dhcpstart 
			+ "&dhcpstop=" + dhcpstop,
			success: function(msg)
			{
				location.reload(true);
			},
			error: function() {  }
		});
	}
	else
	{
		alert("The client is not connected locally. The operation is denied!");
	}
}

function STAmode()
{
	document.getElementById('modeBox').classList.remove("visually-hidden");
	document.getElementById('credBox').classList.remove("visually-hidden");
	document.getElementById('staticIpSwitchBox').classList.remove("visually-hidden");
	document.getElementById('dhcp_startIpBox').classList.add("visually-hidden");
	document.getElementById('dhcp_stopIpBox').classList.add("visually-hidden");
	document.getElementById('ccodeBox').classList.add("visually-hidden");
	document.getElementById('modeSwitchLabel').innerHTML = "STA mode Enabled";
	document.getElementById('ipAddr2').placeholder = "1";
	staticIP();
}

function APmode()
{
	document.getElementById('modeBox').classList.remove("visually-hidden");
	document.getElementById('credBox').classList.remove("visually-hidden");
	document.getElementById('staticIpSwitchBox').classList.add("visually-hidden");
	document.getElementById('ipaddressBox').classList.remove("visually-hidden");
	document.getElementById('netmaskBox').classList.remove("visually-hidden");
	document.getElementById('dhcp_startIpBox').classList.remove("visually-hidden");
	document.getElementById('dhcp_stopIpBox').classList.remove("visually-hidden");
	document.getElementById('ccodeBox').classList.remove("visually-hidden");
	document.getElementById('modeSwitchLabel').innerHTML = "AP mode Enabled";
	document.getElementById('ipAddrLabel').innerHTML = "AP IP Address:";
	document.getElementById('ipAddr2').placeholder = "1";
}

function staticIP()
{
	if(document.getElementById('staticIpSwitch').checked)
	{
		document.getElementById('ipaddressBox').classList.remove("visually-hidden");
		document.getElementById('netmaskBox').classList.remove("visually-hidden");
		document.getElementById('ipAddrLabel').innerHTML = "IP Address:";
		
	}
	else
	{
		document.getElementById('ipaddressBox').classList.add("visually-hidden");
		document.getElementById('netmaskBox').classList.add("visually-hidden");
	}
}

function modeSet()
{
	if(document.getElementById('modeSwitch').checked)
	{
		APmode();
	}
	else
	{
		STAmode();
	}
}

function adaptDisabled()
{
	document.getElementById('modeBox').classList.add("visually-hidden");
	document.getElementById('credBox').classList.add("visually-hidden");
	document.getElementById('ccodeBox').classList.add("visually-hidden");
	document.getElementById('staticIpSwitchBox').classList.add("visually-hidden");
	document.getElementById('ipaddressBox').classList.add("visually-hidden");
	document.getElementById('netmaskBox').classList.add("visually-hidden");
	document.getElementById('dhcp_startIpBox').classList.add("visually-hidden");
	document.getElementById('dhcp_stopIpBox').classList.add("visually-hidden");
}

function enAdapter()
{
	if(document.getElementById('enableAdapter').checked)
	{
		document.getElementById('enableAdapterLabel').innerHTML = "Adapter Enabled";
		modeSet();
	}
	else
	{
		document.getElementById('enableAdapterLabel').innerHTML = "Adapter Disabled";
		adaptDisabled();
	}
}

function ipAddr0change()
{
	document.getElementById('dhcps0').value = document.getElementById('ipAddr0').value;
	document.getElementById('dhcpe0').value = document.getElementById('ipAddr0').value;
}

function ipAddr1change()
{
	document.getElementById('dhcps1').value = document.getElementById('ipAddr1').value;
	document.getElementById('dhcpe1').value = document.getElementById('ipAddr1').value;
}

function ipAddr2change()
{
	document.getElementById('dhcps2').value = document.getElementById('ipAddr2').value;
	document.getElementById('dhcpe2').value = document.getElementById('ipAddr2').value;
}

function ipAddr3change()
{
	document.getElementById('dhcps3').value = parseInt(document.getElementById('ipAddr3').value)+1;
}

var editModal = document.getElementById('editModal');
editModal.addEventListener('show.bs.modal', function (event) {
	// Button that triggered the modal
	button = event.relatedTarget;
	var adapterStat = button.getAttribute('data-bs-status');
	var name = button.getAttribute('data-bs-name');
	document.getElementById('adapter-name').innerHTML = name;
	document.getElementById('modeSwitch').checked = false;
	if(adapterStat == 'UP')
	{
		document.getElementById('enableAdapter').checked = true;
		document.getElementById('enableAdapterLabel').innerHTML = "Adapter Enabled";
		STAmode();
	}
	else
	{
		document.getElementById('enableAdapter').checked = false;
		document.getElementById('enableAdapterLabel').innerHTML = "Adapter Disabled";
		adaptDisabled();
	}
})

</script>
<?php } else header("Location:../../login.php"); ?>
