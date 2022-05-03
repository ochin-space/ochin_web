<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/ 
require 'helper/init.php';
require 'helper/serviceHandler.php';
$dbConstructor->createTable_services();

if(isset($_SESSION["loggedin"]) && ($_SESSION["loggedin"]==true))
{	
    $rows = $dbConstructor->getRows_services();
    if(isset($_POST['delete'])) 
	{        
        $dbConstructor->deleteRow_services($_POST['id']);
		return removeService($_POST['serviceName'], $_POST['serviceNameOld'], SQLite3::escapeString($_POST['cmd_line']), SQLite3::escapeString($_POST['unitOptions']),
			SQLite3::escapeString($_POST['serviceOptions']), SQLite3::escapeString($_POST['installOptions']));	//remove the service from systemctl		
    }
	
    if(isset($_POST['test'])) 
	{		
		startService($_POST['serviceName'], $_POST['action'], $_POST['serviceNameOld'], $_POST['cmd_line'], SQLite3::escapeString($_POST['unitOptions']),
			SQLite3::escapeString($_POST['serviceOptions']), SQLite3::escapeString($_POST['installOptions']));
    }
	
    if(isset($_POST['update'])) 
	{
		if($_POST['action']=="enable") $en="true";
		else $en="false";
		
		$dbConstructor->updateRow_services($_POST['id'], $en, $_POST['serviceName'], SQLite3::escapeString($_POST['cmd_line']), SQLite3::escapeString($_POST['unitOptions']),
			SQLite3::escapeString($_POST['serviceOptions']), SQLite3::escapeString($_POST['installOptions']), SQLite3::escapeString($_POST['description']));
		
		startService($_POST['serviceName'], $_POST['action'], $_POST['serviceNameOld'], $_POST['cmd_line'], SQLite3::escapeString($_POST['unitOptions']),
			SQLite3::escapeString($_POST['serviceOptions']), SQLite3::escapeString($_POST['installOptions']));
    }
	
    if(isset($_POST['add'])) 
	{
        $dbConstructor->insertRow_services('','','','','','','');
    }
	
	function getSelectHTML()
	{
		$content = file_get_contents("helper/defaultServices.json");
		$json = json_decode($content);	
		$select = "";
		for($i=0;$i<sizeof($json->defaultServices);$i++)
		{
			$select = $select.'<option>'.$json->defaultServices[$i]->name.'</option>';
		}
		return $select;
	}
		
	function getSelectJson()
	{
		$content = file_get_contents("helper/defaultServices.json");
		return json_encode($content);
	}
	    
	if(isset($_POST['saveConfig']))
	{		
		$files = glob('tmp/*'); 
		//delete old files and keep the last one
		foreach($files as $file){
			if(is_file($file))
			unlink($file); //delete file
		}
		
		$myfile = fopen("./tmp/".$_POST['filename'].".xml", "w") or die("Unable to open file!");
		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= "\r<services>\r";
		foreach ($rows as $index=>$row) :
			$xml .= "\t<item name='".$row['name']."'>\r";
			$xml .= "\t\t<en>".$row['en']."</en>\r";
			$xml .= "\t\t<cmdline>".$row['cmd_line']."</cmdline>\r";
			$xml .= "\t\t<unit_options>".$row['unitOptions']."</unit_options>\r";
			$xml .= "\t\t<service_options>".$row['serviceOptions']."</service_options>\r";
			$xml .= "\t\t<install_options>".$row['installOptions']."</install_options>\r";
			$xml .= "\t\t<description>".$row['description']."</description>\r";
			$xml .= "\t</item>\r";
		endforeach;
		$xml .= "</services>";
		fwrite($myfile, $xml);
		fclose($myfile);
	}
	    
	if(isset($_POST['loadConfig']))
	{
		$xml = simplexml_load_file("tmp/".$_POST['filename']);
		foreach ($xml->item as $item) :		 
			$name = $dbConstructor->nameCheck(SQLite3::escapeString($item->attributes()->name));   
			$lastid = $dbConstructor->insertRow_services($item->en, $name, SQLite3::escapeString($item->cmdline),
			SQLite3::escapeString($item->unit_options), SQLite3::escapeString($item->service_options),
			SQLite3::escapeString($item->install_options), SQLite3::escapeString($item->description));
			
			if($item->en == "true") $action="enable";
			else $action="disable"; 
			
			editServiceFile($name,$name,$item->cmdline,SQLite3::escapeString($item->unit_options),
			SQLite3::escapeString($item->service_options), SQLite3::escapeString($item->install_options),$action);
		endforeach;
		if(is_file("tmp/".$_POST['filename'])) unlink("tmp/".$_POST['filename']); //delete file		
	}		
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
				<div class=" display-2 text-dark mt-2 mb-4 font-weight-normal text-center">Services to run at boot</div>
			</div>
			<div class="col-sm-2 ">		
				<div class="d-flex justify-content-end mb-2 mt-5" >
					<button type="button" class="d-flex me-4" name="info" style="background-color: Transparent; border: none;" title="Click for info about this page" data-bs-toggle='modal' data-bs-target='#infoModal'"><img  width="40" height="40" src="icons/info.png"></button>
				</div>
			</div>
        </div>
		<div class="row justify-content-between">
			<div class="col-8 d-grid gap-2 d-md-block mb-4">
				<button type='button' class='btn btn-sm col btn-outline-primary me-4' title="Click to view the list of running services" data-bs-toggle='modal' data-bs-target='#DevModal' data-bs-type="services" data-bs-title="List of Running Services">List Running Services</button>
				<button type='button' class='btn btn-sm col btn-outline-primary me-4' title="Click to view the list of video devices" data-bs-toggle='modal' data-bs-target='#DevModal' data-bs-type="video" data-bs-title="List of \dev\video* Devices">List Video Devices</button>
				<button type='button' class='btn btn-sm col btn-outline-primary me-4' title="Click to view the list of USB devices" data-bs-toggle='modal' data-bs-target='#DevModal' data-bs-type="usb" data-bs-title="List of connected USB Devices">List USB Devices</button>
			</div>   
			<div class="col-4 d-grid gap-2 d-md-block mb-4">
				<button type='button' class='btn btn-sm btn-secondary ' title="Click to upload new services" onclick="loadConfig()">Load a services config file</button>
				<button type='button' class='btn btn-sm btn-secondary ' title="Click to save your services to a config file" data-bs-toggle='modal' data-bs-target="#ConfigsaveModal">Save a services config file</button>
			</div> 
		</div>
        <div id="loader" class=""></div>
        <div class="row p-3 rounded-2" style="background-color:white;">
            <div class="row">
                <div class="fs-3 text-muted">Systemctl Services</div>
            </div>
            <div class="d-flex justify-content-end  mb-2 mt-2" >
                <div class="d-flex pe-3 pt-1">Add New Service</div>
                <button type="button" class="d-flex me-4" name="add" value="add" style="background-color: Transparent; border: none;" data-bs-toggle='modal' title="Click to add a new Service" data-bs-target='#addModal' data-bs-name="add"><img  width="30" height="30" src="icons/add.png"></button>
            </div>
            <div class="row">
                <div class="table-responsive" style="max-height:600px;">
                    <table  class="table table-light table-striped" id="autoexecTable">
                        <thead>
                            <tr>
                                <th scope="col">Enable</th>
                                <th scope="col">Name</th>
                                <th scope="col">Command line</th>
                                <th scope="col">[Unit] Options</th>
                                <th scope="col">[Service] Options</th>
                                <th scope="col">[Install] Options</th>
                                <th scope="col">Description</th>
                                <th scope="col"></th>
                                <th scope="col"></th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $index=>$row) : ?>
                                <tr>
                                    <td width='1%' white-space='nowrap'><?php if($row['en']=='true') { echo '<img src="icons/check.png" width="25" height="25"/>'; } else { echo '<img src="icons/uncheck.png" width="25" height="25"/>'; }?></td>
                                    <td value="<?php echo $row['name']; ?>"><?php echo $row['name']; ?></td>
                                    <td value="<?php echo $row['cmd_line']; ?>"><?php echo $row['cmd_line']; ?></td> 
                                    <td value="<?php echo $row['unitOptions']; ?>"><?php echo $row['unitOptions']; ?></td>  
                                    <td value="<?php echo $row['serviceOptions']; ?>"><?php echo $row['serviceOptions']; ?></td>                                     
									<td value="<?php echo $row['installOptions']; ?>"><?php echo $row['installOptions']; ?></td>                                     
									<td value="<?php echo $row['description']; ?>"><?php echo $row['description']; ?></td>        
                                    <td width='1%' white-space='nowrap'>
                                        <button type='button' class='btn btn-primary btn-sm' title="Click to test the Service" data-bs-toggle='modal' data-bs-target='#testModal' data-bs-id="<?php echo $row['id'];?>"  
										data-bs-enable="<?php echo $row['en'];?>" data-bs-name="<?php echo urlencode($row['name']);?>" data-bs-description="<?php echo urlencode($row['description']);?>"
										data-bs-cmd_line="<?php echo urlencode($row['cmd_line']);?>" data-bs-unitOptions="<?php echo urlencode($row['unitOptions']);?>" data-bs-serviceOptions="<?php echo urlencode($row['serviceOptions']);?>" 
										data-bs-installOptions="<?php echo urlencode($row['installOptions']);?>">
                                        Manual</button>
                                    </td>
                                    <td width='1%' white-space='nowrap'>
                                        <button type='button' class='btn btn-primary btn-sm' title="Click to edit the Service" data-bs-toggle='modal' data-bs-target='#editModal' data-bs-id="<?php echo $row['id'];?>"  
										data-bs-enable="<?php echo $row['en'];?>" data-bs-name="<?php echo urlencode($row['name']);?>" data-bs-description="<?php echo urlencode($row['description']);?>"
										data-bs-cmd_line="<?php echo urlencode($row['cmd_line']);?>" data-bs-unitOptions="<?php echo urlencode($row['unitOptions']);?>" data-bs-serviceOptions="<?php echo urlencode($row['serviceOptions']);?>" 
										data-bs-installOptions="<?php echo urlencode($row['installOptions']);?>">  
                                        Edit</button>
                                    </td>           
                                    <td width='1%' white-space='nowrap'>
                                        <button type='button' class='btn btn-primary btn-sm' title="Click to delete the Service" data-bs-toggle='modal' data-bs-target='#deleteModal' data-bs-id="<?php echo $row['id'];?>" data-bs-name="<?php echo urlencode($row['name']);?>" 
										data-bs-description="<?php echo urlencode($row['description']);?>" data-bs-cmd_line="<?php echo urlencode($row['cmd_line']);?>" data-bs-unitOptions="<?php echo urlencode($row['unitOptions']);?>" 
										data-bs-serviceOptions="<?php echo urlencode($row['serviceOptions']);?>" data-bs-installOptions="<?php echo urlencode($row['installOptions']);?>">
                                        Delete</button>
                                    </td> 
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="testModal" tabindex="-1" aria-labelledby="testModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="testModalLabel">Service Manual Control</h5>
                    </div>
                    <input hidden type="text" class="form-control" id="test-id"></input>
                    <input hidden type="text" class="form-control" id="test-en"></input>
                    <input hidden type="text" class="form-control" id="test-name"></input>
                    <input hidden type="text" class="form-control" id="test-description"></input>
                    <input hidden type="text" class="form-control" id="test-cmd_line"></input>
                    <input hidden type="text" class="form-control" id="test-unitOptions"></input>
                    <input hidden type="text" class="form-control" id="test-serviceOptions"></input>
                    <input hidden type="text" class="form-control" id="test-installOptions"></input>
					<div class="mb-3">
						<label for="test-status" class="col-form-label"></label>
						<textarea type="text" class="form-control bg-dark text-info fs-5" id="test-status" placeholder="Press to test the service" rows="10"></textarea>
					</div>
                    <div class="modal-footer">
                        <button type='button' class="btn btn-primary" onclick="startService()">Start</button> 
                        <button type='button' class="btn btn-primary" onclick="stopService()">Stop</button> 
                        <button type='button' class="btn btn-primary" onclick="testService()">Test</button> 
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Service</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="mb-3">
                                <input hidden type="text" class="form-control" id="recipient-id"></input>
                            </div>
                            <div class="mb-3">
                                <select id="CmdLineSelect" class="form-select" onchange="CmdLineSelectOnChange(this)">
                                    <option selected>Select a Service from the list</option>
									<?php echo getSelectHTML();?>
                                </select>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" value="" id="enableCmdLn">
                                <label class="form-check-label" for="flexCheckDefault">Enable Service</label>
                            </div>
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">Name:</label>
                                <input type="text" class="form-control" id="recipient-name" placeholder="Insert the name of the service">
                            </div>
                            <div class="mb-3">
                                <label for="recipient-cmd" class="col-form-label">Cmd Line:</label>
                                <input class="form-control" id="recipient-cmd" placeholder="Insert the command line to execute"></input>
                            </div>
                            <div class="mb-3">
                                <label for="recipient-unitOptions" class="col-form-label">[Unit] options:</label>
                                <textarea class="form-control" id="recipient-unitOptions" placeholder="Insert the [Unit] section options">After=multi-user.target</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="recipient-serviceOptions" class="col-form-label">[Service] options:</label>
                                <textarea class="form-control" id="recipient-serviceOptions" placeholder="Insert the [Service] section options"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="recipient-installOptions" class="col-form-label">[Install] options:</label>
                                <textarea class="form-control" id="recipient-installOptions" placeholder="Insert the [Install] section options">WantedBy=multi-user.target</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="recipient-description" class="col-form-label">Description:</label>
                                <textarea type="text" class="form-control" id="recipient-description" placeholder="Insert the description of the service"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="updateService()">Update</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Are you sure you want to delete this Service?</h5>
                    </div>
                    <input hidden type="text" class="form-control" id="deleteModal-id"></input>
                    <input hidden type="text" class="form-control" id="deleteModal-name"></input>
                    <input hidden type="text" class="form-control" id="deleteModal-cmd"></input>
                    <input hidden type="text" class="form-control" id="deleteModal-unitOptions"></input>
                    <input hidden type="text" class="form-control" id="deleteModal-serviceOptions"></input>
                    <input hidden type="text" class="form-control" id="deleteModal-installOptions"></input>
                    <input hidden type="text" class="form-control" id="deleteModal-description"></input>
                    <div class="modal-footer">
                        <button type='button' class="btn btn-primary" data-bs-dismiss="modal" onclick="deleteService()">Yes</button> 
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Are you sure you want to add a new Service?</h5>
                    </div>
                    <div class="modal-footer">
                        <button type='button' class="btn btn-primary" data-bs-dismiss="modal" onclick="insertRow()">Yes</button> 
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="DevModal" tabindex="-1" aria-labelledby="DevModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="devModalTitle"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div> 
					<div class="mb-3">
						<label for="recipient-status" class="col-form-label"></label>
						<textarea type="text" class="form-control bg-dark text-info fs-5" id="recipient-dev" rows="15"></textarea>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					</div>
                </div>
            </div>
        </div>
		
        <div class="modal fade" id="ConfigsaveModal" tabindex="-1" aria-labelledby="ConfigsaveModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ConfigsaveModalLabel">Please insert the name of the Config file</h5>
                    </div>
					<div class="modal-body">
						<form>
							<label for="recipient-filename" class="col-form-label ">Filename:</label>
							<input class="form-control" id="recipient-filename" placeholder="Insert the name of the Config file" value="">
						</form>
					</div>
                    <div class="modal-footer">
                        <button type='button' class="btn btn-primary" data-bs-dismiss="modal" onclick="saveConfig()">Save</button> 
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button></div>
                    </div>
                </div>
            </div>
        </div>
		
		<div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg modal-dialog-centered" >
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="editModalLabel">Service Editor guide</h5>
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
    </div>
</body>
</html>

<!-- js end functions-->
<script>
var button;
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

function insertRow()
{
	if(<?php echo isClientLocal();?>)
	{	
		document.getElementById('loader').innerHTML = '<div class="loader"></div>';
		$.ajax({
			type : "POST",  //type of method
			url  : "index.php",  //your page
			data: "add=1",
			success: function(msg)
			{
				if(msg==0) alert("The client is not connected locally. The operation is denied!");
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

function testService()
{
	document.getElementById('test-status').value = "";
    serviceName = document.getElementById('test-name').value;
    $.ajax({
        type : "POST",  //type of method
        url  : "helper/serviceHandler.php",  //your page
        data: "testService=1&serviceName=" + serviceName,
        success: function(msg)
        {			
			document.getElementById('test-status').value = msg;
        },
        error: function() { }
    });
}

function startService()
{
	if(<?php echo isClientLocal();?>)
	{		
		document.getElementById('loader').innerHTML = '<div class="loader"></div>';
		id = document.getElementById('test-id').value;
		serviceName = document.getElementById('test-name').value.replace(/\s/g, "_");	//remove the spaces
		cmd_line = document.getElementById('test-cmd_line').value; //this is the cmd_line after editing
		unitOptions = document.getElementById('test-unitOptions').value;
		serviceOptions = document.getElementById('test-serviceOptions').value;
		installOptions = document.getElementById('test-installOptions').value;
		description = document.getElementById('test-description').value;
		$.ajax({
			type : "POST",  //type of method
			url  : "index.php",  //your page
			data: "test=1&action=load&serviceName=" + serviceName + "&serviceNameOld=" + serviceName + "&cmd_line=" + cmd_line + "&unitOptions=" + unitOptions +
			"&serviceOptions=" + serviceOptions + "&installOptions=" + installOptions + "&description=" + description,
			success: function(msg)
			{
				document.getElementById('loader').innerHTML = '';
				document.getElementById('test-status').value = msg;
			},
			error: function() {  }
		});
	}
	else
	{
		alert("The client is not connected locally. The operation is denied!");
	}
}

function stopService()
{
	if(<?php echo isClientLocal();?>)
	{		
		document.getElementById('loader').innerHTML = '<div class="loader"></div>';
		id = document.getElementById('test-id').value;
		serviceName = document.getElementById('test-name').value.replace(/\s/g, "_");	//remove the spaces
		cmd_line = document.getElementById('test-cmd_line').value; //this is the cmd_line after editing
		unitOptions = document.getElementById('test-unitOptions').value;
		serviceOptions = document.getElementById('test-serviceOptions').value;
		installOptions = document.getElementById('test-installOptions').value;
		description = document.getElementById('test-description').value;
		$.ajax({
			type : "POST",  //type of method
			url  : "index.php",  //your page
			data: "test=1&action=unload&serviceName=" + serviceName + "&serviceNameOld=" + serviceName + "&cmd_line=" + cmd_line + "&unitOptions=" + unitOptions +
			"&serviceOptions=" + serviceOptions + "&installOptions=" + installOptions + "&description=" + description,
			success: function(msg)
			{
				document.getElementById('loader').innerHTML = '';
				document.getElementById('test-status').value = msg;
			},
			error: function() {  }
		});
	}
	else
	{
		alert("The client is not connected locally. The operation is denied!");
	}
}

function deleteService()
{
	if(<?php echo isClientLocal();?>)
	{		
		document.getElementById('loader').innerHTML = '<div class="loader"></div>';
		id = document.getElementById('deleteModal-id').value;
		serviceName = document.getElementById('deleteModal-name').value;
		cmd_line = document.getElementById('deleteModal-cmd').value; //this is the cmd_line after editing
		unitOptions = document.getElementById('deleteModal-unitOptions').value;
		serviceOptions = document.getElementById('deleteModal-serviceOptions').value;
		installOptions = document.getElementById('deleteModal-installOptions').value;
		serviceNameOld = decodeURIComponent(button.getAttribute('data-bs-name')).replace(/\+/g, ' '); 
		description = document.getElementById('deleteModal-description').value;
		
		$.ajax({
			type : "POST",  //type of method
			url  : "index.php",  //your page
			data: "delete=1&id=" + id + "&serviceName=" + serviceName + "&cmd_line=" + cmd_line 
			+ "&unitOptions=" + unitOptions + "&serviceOptions=" + serviceOptions + "&installOptions=" + installOptions + "&description=" + description,
			success: function(msg)
			{
				location.reload(true);
			},
			error: function() { }
		});
	}
	else
	{
		alert("The client is not connected locally. The operation is denied!");
	}
}

function updateService()
{
	if(<?php echo isClientLocal();?>)
	{		
		document.getElementById('loader').innerHTML = '<div class="loader"></div>';
		id = document.getElementById('recipient-id').value;
		if(document.getElementById('enableCmdLn').checked) action="enable";
		else action="disable";
		serviceName = document.getElementById('recipient-name').value.replace(/\s/g, "_");	//remove the spaces
		cmd_line = document.getElementById('recipient-cmd').value; //this is the cmd_line after editing
		unitOptions = document.getElementById('recipient-unitOptions').value;
		serviceOptions = document.getElementById('recipient-serviceOptions').value;
		installOptions = document.getElementById('recipient-installOptions').value;
		serviceNameOld = decodeURIComponent(button.getAttribute('data-bs-name')).replace(/\+/g, ' '); 
		description = document.getElementById('recipient-description').value;
		$.ajax({
			type : "POST",  //type of method
			url  : "index.php",  //your page
			data: "update=1&id=" + id + "&action=" + action + "&serviceName=" + serviceName + "&serviceNameOld=" + serviceNameOld
			+ "&cmd_line=" + cmd_line + "&unitOptions=" + unitOptions + "&serviceOptions=" + serviceOptions + "&installOptions=" + installOptions + "&description=" + description,
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

var testModal = document.getElementById('testModal');
testModal.addEventListener('show.bs.modal', function (event) {  
  // Button that triggered the modal
  button = event.relatedTarget;
  // Extract info from data-bs-* attributes
  var id = button.getAttribute('data-bs-id');
  var enableCmdLn = button.getAttribute('data-bs-enable');
  var name = decodeURIComponent(button.getAttribute('data-bs-name')).replace(/\+/g, ' ');
  var cmd_line = decodeURIComponent(button.getAttribute('data-bs-cmd_line')).replace(/\+/g, ' '); 
  var unitOptions = decodeURIComponent(button.getAttribute('data-bs-unitOptions')).replace(/\+/g, ' '); 
  var serviceOptions = decodeURIComponent(button.getAttribute('data-bs-serviceOptions')).replace(/\+/g, ' '); 
  var installOptions = decodeURIComponent(button.getAttribute('data-bs-installOptions')).replace(/\+/g, ' '); 
  var description = decodeURIComponent(button.getAttribute('data-bs-description')).replace(/\+/g, ' ');
  // Update the modal's content.
  //updateModal.querySelector('.modal-title').textContent = 'Edit the tupla No ' + id;
  document.getElementById('test-id').value = id;
  var isTrueSet = (enableCmdLn === 'true');
  document.getElementById('enableCmdLn').checked = isTrueSet;
  if(name) document.getElementById('test-name').value = name;
  if(cmd_line) document.getElementById('test-cmd_line').value = cmd_line;
  if(unitOptions) document.getElementById('test-unitOptions').value = unitOptions;
  if(serviceOptions) document.getElementById('test-serviceOptions').value = serviceOptions;
  if(installOptions) document.getElementById('test-installOptions').value = installOptions;
  if(description) document.getElementById('test-description').value = description;
})

var DevModal = document.getElementById('DevModal');
DevModal.addEventListener('show.bs.modal', function (event) {
	button = event.relatedTarget;
	var type = button.getAttribute('data-bs-type');
	var title = button.getAttribute('data-bs-title');
	document.getElementById('devModalTitle').innerHTML = title;
	switch(type)
	{
		case 'video':
			cmd = "video";
			break;
		case 'usb':
			cmd = "usb";
			break;
		case 'services':
			cmd = "services";
			break;
		default:
			break;
	}
    $.ajax({
        type : "POST",  //type of method
        url  : "helper/hwDevices.php",  //your page
        data: cmd,
        success: function(msg)
        {			
			document.getElementById('recipient-dev').value = msg;
            //location.reload(true);
        },
        error: function() { }
    });
})

var deleteModal = document.getElementById('deleteModal');
deleteModal.addEventListener('show.bs.modal', function (event) {
  // Button that triggered the modal
  button = event.relatedTarget;
  // Extract info from data-bs-* attributes
  var id = button.getAttribute('data-bs-id');
  var name = decodeURIComponent(button.getAttribute('data-bs-name')).replace(/\+/g, ' ');
  var cmd_line = decodeURIComponent(button.getAttribute('data-bs-cmd_line')).replace(/\+/g, ' '); 
  var unitOptions = decodeURIComponent(button.getAttribute('data-bs-unitOptions')).replace(/\+/g, ' '); 
  var serviceOptions = decodeURIComponent(button.getAttribute('data-bs-serviceOptions')).replace(/\+/g, ' '); 
  var installOptions = decodeURIComponent(button.getAttribute('data-bs-installOptions')).replace(/\+/g, ' '); 
  var description = decodeURIComponent(button.getAttribute('data-bs-description')).replace(/\+/g, ' ');
  if(id) document.getElementById('deleteModal-id').value = id;
  if(name) document.getElementById('deleteModal-name').value = name;
  if(cmd_line) document.getElementById('deleteModal-cmd').value = cmd_line;
  if(unitOptions) document.getElementById('deleteModal-unitOptions').value = unitOptions;
  if(serviceOptions) document.getElementById('deleteModal-serviceOptions').value = serviceOptions;
  if(installOptions) document.getElementById('deleteModal-installOptions').value = installOptions;
  if(description) document.getElementById('deleteModal-description').value = description;
})

var editModal = document.getElementById('editModal');
editModal.addEventListener('show.bs.modal', function (event) {
  // Button that triggered the modal
  button = event.relatedTarget;
  // Extract info from data-bs-* attributes
  var id = button.getAttribute('data-bs-id');
  var enableCmdLn = button.getAttribute('data-bs-enable');
  var name = decodeURIComponent(button.getAttribute('data-bs-name')).replace(/\+/g, ' ');
  var cmd_line = decodeURIComponent(button.getAttribute('data-bs-cmd_line')).replace(/\+/g, ' '); 
  var unitOptions = decodeURIComponent(button.getAttribute('data-bs-unitOptions')).replace(/\+/g, ' '); 
  var serviceOptions = decodeURIComponent(button.getAttribute('data-bs-serviceOptions')).replace(/\+/g, ' '); 
  var installOptions = decodeURIComponent(button.getAttribute('data-bs-installOptions')).replace(/\+/g, ' '); 
  var description = decodeURIComponent(button.getAttribute('data-bs-description')).replace(/\+/g, ' ');
  // Update the modal's content.
  //updateModal.querySelector('.modal-title').textContent = 'Edit the tupla No ' + id;
  document.getElementById('recipient-id').value = id;
  var isTrueSet = (enableCmdLn === 'true');
  document.getElementById('enableCmdLn').checked = isTrueSet;
  if(name) document.getElementById('recipient-name').value = name;
  if(cmd_line) document.getElementById('recipient-cmd').value = cmd_line;
  if(unitOptions) document.getElementById('recipient-unitOptions').value = unitOptions;
  if(serviceOptions) document.getElementById('recipient-serviceOptions').value = serviceOptions;
  if(installOptions) document.getElementById('recipient-installOptions').value = installOptions;
  if(description) document.getElementById('recipient-description').value = description;
})

function CmdLineSelectOnChange(selectBox)
{
	var json = JSON.parse(<?php echo getSelectJson(); ?>);	
    document.getElementById('recipient-name').value = json.defaultServices[selectBox.selectedIndex-1].name;
    document.getElementById('recipient-cmd').value = json.defaultServices[selectBox.selectedIndex-1].cmd;
    document.getElementById('recipient-unitOptions').value = json.defaultServices[selectBox.selectedIndex-1].unitOptions;
    document.getElementById('recipient-serviceOptions').value = json.defaultServices[selectBox.selectedIndex-1].serviceOptions;		
    document.getElementById('recipient-installOptions').value = json.defaultServices[selectBox.selectedIndex-1].installOptions;		
    document.getElementById('recipient-description').value = json.defaultServices[selectBox.selectedIndex-1].description;		
}

function saveConfig()
{	
	filename = document.getElementById('recipient-filename').value+"_services";
	document.getElementById('loader').innerHTML = '<div class="loader"></div>';
    $.ajax({
        type : "POST",  //type of method
        url  : "index.php",  //your page
		processData: false,
        data: "saveConfig=1&filename="+filename,
        success: function(msg)
        {			
			var filePath = "./tmp/"+filename+".xml";	
			var link=document.createElement('a');
			link.href = filePath;
			link.download = filePath.substr(filePath.lastIndexOf('/') + 1);
			link.click();
			location.reload(true);
        },
        error: function() { }
    });
}

async function uploadFile(files, location)
{
	let formData = new FormData(); 
	formData.append("file", files);
	formData.append("location", location);
	await fetch('helper/uploadFile.php', 
		{
			method: "POST", 
			body: formData
		}
	); 
}

function loadConfig()
{
	if(<?php echo isClientLocal();?>)
	{		
		// Upload file
		let input = document.createElement('input');
		input.type = 'file';
		input.onchange = _ => 
		{
			document.getElementById('loader').innerHTML = '<div class="loader"></div>';
			let files =   Array.from(input.files);
			var formData = new FormData();
			formData.append("file", files[0]);
			formData.append("location", "../tmp");
			var xhttp = new XMLHttpRequest();
			// Set POST method and ajax file path
			xhttp.open("POST", "helper/uploadFile.php", true);
			// call on request changes state
			xhttp.onreadystatechange = function() 
			{
				if (this.readyState == 4 && this.status == 200) 
				{
					var response = this.responseText;
					if(response == 1)
					{			
						$.ajax({
							type : "POST",  //type of method
							url  : "index.php",  //your page
							processData: false,
							data: "loadConfig=1&filename="+files[0].name,
							success: function(msg)
							{			
								location.reload(true);
							},
							error: function() { }
						});
					}else
					{
						alert("The file "+files[0].name+" hasn't been uploaded since the file extension is wrong,\r\nThe extension should be .xml");
					}
				}
			};
			// Send request with data
			xhttp.send(formData);
		};
		input.click();
	}
	else
	{
		alert("The client is not connected locally. The operation is denied!");
	}
}
</script>
<?php } else header("Location:../../login.php"); ?>
