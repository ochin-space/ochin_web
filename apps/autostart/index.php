<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/ 
require 'helper/init.php';
require 'helper/editFiles.php';
$dbConstructor->createTable_autostart();

if(isset($_SESSION["loggedin"]) && ($_SESSION["loggedin"]==true)) 
{
    $rows = $dbConstructor->getRows_autostart();
    if(isset($_POST['delete'])) 
	{
		$dbConstructor->deleteRow_autostart($_POST['id']);
		return delete_a_line(Config::file_folder.'rc.local', $_POST['cmd_line']);
    }
    if(isset($_POST['update'])) 
	{		
		$enable = ($_POST['en'] === 'true');
		$response = replace_a_line(Config::file_folder.'rc.local', $enable, $_POST['cmd_line'], $_POST['new_cmd_line']);
		if($response==1)
		{
			$dbConstructor->updateRow_autostart($_POST['id'], $_POST['en'], $_POST['name'], SQLite3::escapeString($_POST['new_cmd_line']), $_POST['description']);
		}
		echo $response; 
		exit();
    }
    if(isset($_POST['add'])) 
	{
		return $dbConstructor->insertRow_autostart('','','','');
    }
	
	function getSelectHTML()
	{
		$content = file_get_contents("helper/defaultautostart.json");
		$json = json_decode($content);	
		$select = "";
		for($i=0;$i<sizeof($json->defaultConfig);$i++)
		{
			$select = $select.'<option>'.$json->defaultConfig[$i]->name.'</option>';
		}
		return $select;
	}
		
	function getSelectJson()
	{
		$content = file_get_contents("helper/defaultautostart.json");
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
		$xml .= "\r<hwconfig>\r";
		foreach ($rows as $index=>$row) :
			$xml .= "\t<item name='".$row['name']."'>\r";
			$xml .= "\t\t<en>".$row['en']."</en>\r";
			$xml .= "\t\t<cmdline>".$row['cmd_line']."</cmdline>\r";
			$xml .= "\t\t<description>".$row['description']."</description>\r";
			$xml .= "\t</item>\r";
		endforeach;
		$xml .= "</hwconfig>";
		fwrite($myfile, $xml);
		fclose($myfile);
	}
	    
	if(isset($_POST['loadConfig']))
	{
		$xml = simplexml_load_file("tmp/".$_POST['filename']);
		foreach ($xml->item as $item) :		 
			if($dbConstructor->cmdlineCheck(SQLite3::escapeString($item->cmdline))==0)
			{		
				if($item->en == 'true') $enable = true;
				else $enable = false;
				$response = replace_a_line(Config::file_folder.'rc.local', $enable, SQLite3::escapeString($item->cmdline), SQLite3::escapeString($item->cmdline));
				if($response==1)
				{
					$dbConstructor->insertRow_autostart($item->en, SQLite3::escapeString($item->attributes()->name),
						SQLite3::escapeString($item->cmdline), SQLite3::escapeString($item->description));
				}				
			}
		endforeach;
		if(is_file("tmp/".$_POST['filename'])) unlink("tmp/".$_POST['filename']); //delete file
		return 1;
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
				<div class=" display-2 text-dark mt-2 mb-4 font-weight-normal text-center">Autostart SW at boot</div>
			</div>
			<div class="col-sm-2 ">		
				<div class="d-flex justify-content-end mb-2 mt-5" >
					<button type="button" class="d-flex me-4" name="info" style="background-color: Transparent; border: none;" data-bs-toggle='modal' title="Click for info about this page" data-bs-target='#infoModal'"><img  width="40" height="40" src="icons/info.png"></button>
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
				<button type='button' class='btn btn-sm btn-secondary ' title="Click to upload new autorun config" onclick="loadConfig()">Load autorun config file</button>
				<button type='button' class='btn btn-sm btn-secondary ' title="Click to save your autorun config to a file" data-bs-toggle='modal' data-bs-target="#ConfigsaveModal">Save autorun config to file</button>
			</div> 
		</div>
        <div id="loader" class=""></div>
        <div class="row p-3 rounded-2" style="background-color:white;">
            <div class="row">
                <div class="fs-3 text-muted">/etc/rc.local</div>
            </div>
            <div class="d-flex justify-content-end  mb-2 mt-2" >
                <div class="d-flex pe-3 pt-1">Add New Cmd Line</div>
                <button type="button" class="d-flex me-4" name="add" value="add" style="background-color: Transparent; border: none;" data-bs-toggle='modal' title="Click to add a new Command" data-bs-target='#addModal' data-bs-name="add"><img  width="30" height="30" src="icons/add.png"></button>
            </div>
            <div class="row">
                <div class="table-responsive" style="max-height:600px;">
                    <table  class="table table-light table-striped" id="autostart_Table">
                        <thead>
                            <tr>
                                <th scope="col">Enable</th>
                                <th scope="col">Name</th>
                                <th scope="col">Command line</th>
                                <th scope="col">Description</th>
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
									<td value="<?php echo $row['description']; ?>"><?php echo $row['description']; ?></td>
                                    <td width='1%' white-space='nowrap'>
                                        <button type='button' class='btn btn-primary btn-sm' title="Click to edit the Command" data-bs-toggle='modal' data-bs-target='#editModal' data-bs-id="<?php echo $row['id'];?>"  
										data-bs-enable="<?php echo $row['en'];?>" data-bs-name="<?php echo urlencode($row['name']);?>" data-bs-description="<?php echo urlencode($row['description']);?>"
										data-bs-cmd_line="<?php echo urlencode($row['cmd_line']);?>">
                                        Edit</button>
                                    </td>          
                                    <td width='1%' white-space='nowrap'>
                                        <button type='button' class='btn btn-primary btn-sm' title="Click to delete the Command" data-bs-toggle='modal' data-bs-target='#deleteModal' data-bs-id="<?php echo $row['id'];?>" data-bs-cmd_line="<?php echo $row['cmd_line'];?>">
                                        Delete</button>
                                    </td> 
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Compose Command</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="mb-3">
                                <input hidden type="text" class="form-control" id="recipient-id">
                            </div>
                            <div class="mb-3">
                                <select id="CmdLineSelect" class="form-select" onchange="CmdLineSelectOnChange(this)">
									<option selected>Select a command from the list</option>
									<?php echo getSelectHTML();?>
                                </select>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" value="" id="enableCmdLn">
                                <label class="form-check-label" for="flexCheckDefault">Enable Command Line</label>
                            </div>
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">Name:</label>
                                <input type="text" class="form-control" id="recipient-name" placeholder="Insert the name of the command">
                            </div>
                            <div class="mb-3">
                                <label for="recipient-cmd" class="col-form-label">Cmd Line:</label>
                                <input class="form-control" id="recipient-cmd" placeholder="Insert the command line to execute"></input>
                            </div>
                            <div class="mb-3">
                                <label for="recipient-description" class="col-form-label">Description:</label>
                                <textarea type="text" class="form-control" id="recipient-description" placeholder="Insert the description" value=""></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="updateRow()">Update</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Are you sure you want to delete this Command Line?</h5>
                    </div>
                    <input hidden type="text" class="form-control" id="deleteModal-id">
                    <div class="modal-footer">
                        <button type='button' class="btn btn-primary" data-bs-dismiss="modal" onclick="deleteRow()">Yes</button> 
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Are you sure you want to add a Command Line?</h5>
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
						<h5 class="modal-title" id="editModalLabel">Autostart SW guide</h5>
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

function deleteRow()
{
	if(<?php echo isClientLocal();?>)
	{
		document.getElementById('loader').innerHTML = '<div class="loader"></div>';
		id = document.getElementById('deleteModal-id').value;
		//cmd_line = document.getElementById('autostart_Table').rows[id].cells[2].innerHTML;
		cmd_line = decodeURIComponent(button.getAttribute('data-bs-cmd_line')).replace(/\+/g, ' ');
		
		$.ajax({
			type : "POST",  //type of method
			url  : "index.php",  //your page
			data: "delete=1&id=" + id + "&cmd_line=" + cmd_line,
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

function updateRow()
{
	if(<?php echo isClientLocal();?>)
	{
		document.getElementById('loader').innerHTML = '<div class="loader"></div>';
		id = document.getElementById('recipient-id').value;
		en = document.getElementById('enableCmdLn').checked;
		name = document.getElementById('recipient-name').value;
		descr = document.getElementById('recipient-name').value;
		new_cmd_line = document.getElementById('recipient-cmd').value; //this is the cmd_line after editing
		//cmd_line = document.getElementById('autostart_Table').rows[id].cells[2].innerHTML;
		cmd_line = decodeURIComponent(button.getAttribute('data-bs-cmd_line')).replace(/\+/g, ' '); //this is the cmd_line before editing. It's needed to find and replace it in the file
		description = document.getElementById('recipient-description').value;
		$.ajax({
			type : "POST",  //type of method
			url  : "index.php",  //your page
			data: "update=1&id=" + id + "&en=" + en + "&name=" + name + "&cmd_line=" + cmd_line + "&new_cmd_line=" + new_cmd_line + "&description=" + description,
			success: function(msg)
			{
				if(msg!=1) alert(msg);
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

var deleteModal = document.getElementById('deleteModal');
deleteModal.addEventListener('show.bs.modal', function (event) {
  // Button that triggered the modal
  button = event.relatedTarget;
  // Extract info from data-bs-* attributes
  var id = button.getAttribute('data-bs-id');
  document.getElementById('deleteModal-id').value = id;
})

var editModal = document.getElementById('editModal');
editModal.addEventListener('show.bs.modal', function (event) {
  // Button that triggered the modal
  button = event.relatedTarget;
  // Extract info from data-bs-* attributes
  var id = button.getAttribute('data-bs-id');
  var enableCmdLn = button.getAttribute('data-bs-enable');
  var name = decodeURIComponent(button.getAttribute('data-bs-name')).replace(/\+/g, ' ');
  //var cmd_line = document.getElementById('autostart_Table').rows[id].cells[2].innerHTML;
  var cmd_line = decodeURIComponent(button.getAttribute('data-bs-cmd_line')).replace(/\+/g, ' ');  
  var description = decodeURIComponent(button.getAttribute('data-bs-description')).replace(/\+/g, ' ');
  // Update the modal's content.
  //updateModal.querySelector('.modal-title').textContent = 'Edit the tupla No ' + id;
  document.getElementById('recipient-id').value = id;
  var isTrueSet = (enableCmdLn === 'true');
  document.getElementById('enableCmdLn').checked = isTrueSet;
  document.getElementById('recipient-name').value = name;
  document.getElementById('recipient-cmd').value = cmd_line;
  document.getElementById('recipient-description').value = description;
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

function CmdLineSelectOnChange(selectBox)
{
	var json = JSON.parse(<?php echo getSelectJson(); ?>);	
    document.getElementById('recipient-name').value = json.defaultConfig[selectBox.selectedIndex-1].name;
    document.getElementById('recipient-cmd').value = json.defaultConfig[selectBox.selectedIndex-1].cmd;	
    document.getElementById('recipient-description').value = json.defaultConfig[selectBox.selectedIndex-1].description;	
}

function saveConfig()
{	
	filename = document.getElementById('recipient-filename').value+"_autorun";
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
