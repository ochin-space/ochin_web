<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/ 
require 'helper/init.php';
if(isset($_SESSION["loggedin"]) && ($_SESSION["loggedin"]==true)) 
{
    $rows_Configuration = $dbConstructor_main->getRowsAddons_InTabs(array("Configuration"),'=','OR');
    $rows_Applications = $dbConstructor_main->getRowsAddons_InTabs(array("Application"),'=','OR');	
    $rows_Development = $dbConstructor_main->getRowsAddons_InTabs(array("Development"),'=','OR');	
    $rows_notInMenu = $dbConstructor_main->getRowsAddons_InTabs(array("Application","Configuration","Development"),'!=','AND');	
	
	function rrmdir($dir) 
	{ 
		if (is_dir($dir)) 
		{			
			$objects = scandir($dir);
			foreach ($objects as $object) 
			{ 
				if ($object != "." && $object != "..") 
				{ 
					if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
					rrmdir($dir. DIRECTORY_SEPARATOR .$object);
						else
					unlink($dir. DIRECTORY_SEPARATOR .$object); 
				} 
			}
			rmdir($dir); 
		} 	
	}
		
    if(isset($_POST['delete'])) {
        $dbConstructor_main->deleteRowAddons($_POST['id']);
		//delete folder $_POST['foldername']
		if($_POST['foldername']!=null) rrmdir("apps/".$_POST['foldername']);
		//shell_exec("sudo rm -r apps/".$_POST['foldername']);
    }
	
    if(isset($_POST['update'])) {
		$dbConstructor_main->updateRowAddons($_POST['id'], $_POST['tab'], $_POST['en'], SQLite3::escapeString($_POST['name']), SQLite3::escapeString($_POST['foldername']), SQLite3::escapeString($_POST['description']));
    }
	
	if(isset($_POST['installAddon']))
	{
		$zip = new ZipArchive;
		if ($zip->open("tmp/".$_POST['filename']) === TRUE) {
			$zip->extractTo('apps');
			$zip->close();
			
			$xml = simplexml_load_file("apps/".pathinfo($_POST['filename'], PATHINFO_FILENAME)."/install.xml");
			//$topbarpos = 0;
			//if($xml->addon->topbarpos == "Configuration") $topbarpos = 0;
			//elseif($xml->addon->topbarpos == "Application") $topbarpos = 1;
			$dbConstructor_main->insertRowAddons($xml->addon->topbarpos, $xml->addon->en, SQLite3::escapeString($xml->addon->attributes()->name),$xml->addon->foldername, SQLite3::escapeString($xml->addon->description));
		}
		
		if(is_file("tmp/".$_POST['filename'])) unlink("tmp/".$_POST['filename']); //delete file
	}	
?>

<!doctype html>
<html>
<head>
<link href="css/loader.css" rel="stylesheet">
<script type="text/javascript" src=<?php echo Config_main::jQueryPath;?>></script> 
<!-- Required meta tags for Bootstrap 5-->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- Bootstrap CSS -->
<link href=<?php echo Config_main::bootstrapCSSpath;?> rel="stylesheet">
<!-- Bootstrap js -->
<script src=<?php echo Config_main::bootstrapJSpath;?>></script>
<?php include Config_main::topbar_path;?>
<title>öchìn Web GUI</title>
</head>
    <body style="background-color:#f2f2f2;">
        <div class="container-xl">
			<div class="row">	
				<div class="col-sm-10">			
					<div class=" display-2 text-dark mt-2 mb-4 font-weight-normal text-center">Addons Manager</div>
				</div>
				<div class="col-sm-2 ">		
					<div class="d-flex justify-content-end mb-2 mt-5" >
						<button type="button" class="d-flex me-4" name="info" style="background-color: Transparent; border: none;"  title="Click for info about this page" data-bs-toggle='modal' data-bs-target='#infoModal'"><img  width="40" height="40" src="icons/info.png"></button>
					</div>
				</div>        
				<div id="loader" class=""></div>
				<div class="row p-3 rounded-2" style="background-color:white;">
					<div class="d-flex justify-content-end  mb-2 mt-2" >
						<div class="d-flex pe-3 pt-1">Upload a New Addon</div>
						<button type="button" class="d-flex me-4" name="add" value="add" style="background-color: Transparent; border: none;" data-bs-toggle='modal' title="Click to upload a new Addon" data-bs-target='#addModal' data-bs-name="add"><img  width="30" height="30" src="icons/add.png"></button>
					</div>
					<div class="row">
						<div class="fs-3 text-muted">Addons List - Configuration</div>
					</div>
					<div class="row">
						<div class="table-responsive" style="max-height:600px;">
							<table  class="table table-light table-striped" id="autoexecTable">
								<thead>
									<tr>
										<th scope="col">Enable</th>
										<th scope="col">Name</th>
										<th scope="col">Tab</th>
										<th scope="col">Folder Name</th>
										<th scope="col">Description</th>
										<th scope="col"></th>
										<th scope="col"></th>
									</tr>
								</thead>
								<tbody>
								<?php foreach ($rows_Configuration as $index=>$row) : ?>
									<tr>
										<td width='1%' white-space='nowrap'><?php if($row['en']=='true') { echo '<img src="icons/check.png" width="25" height="25"/>'; } else { echo '<img src="icons/uncheck.png" width="25" height="25"/>'; }?></td>
										<td value="<?php echo $row['name']; ?>"><?php echo $row['name']; ?></td>
										<td value="<?php echo $row['tab']; ?>"><?php echo $row['tab']; ?></td>
										<td value="<?php echo $row['foldername']; ?>"><?php echo $row['foldername']; ?></td>                                    
										<td value="<?php echo $row['description']; ?>"><?php echo $row['description']; ?></td>
										<td width='1%' white-space='nowrap'>
											<button type='button' class='btn btn-primary btn-sm' title="Click to edit the Service" data-bs-toggle='modal' data-bs-target='#editModal' data-bs-id="<?php echo $row['id'];?>"  
											data-bs-enable="<?php echo $row['en'];?>" data-bs-tab="<?php echo $row['tab'];?>" data-bs-name="<?php echo $row['name'];?>" data-bs-foldername="<?php echo $row['foldername'];?>" data-bs-description="<?php echo $row['description'];?>">
											Edit</button>
										</td>          
										<td width='1%' white-space='nowrap'>
											<button type='button' class='btn btn-primary btn-sm' title="Click to delete the Addon" data-bs-toggle='modal' data-bs-target='#deleteModal' data-bs-id="<?php echo $row['id'];?>" data-bs-foldername="<?php echo $row['foldername'];?>">
											Delete</button>
										</td> 
									</tr>
								<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="row p-3 rounded-2" style="background-color:white;">
					<div class="row">
						<div class="fs-3 text-muted">Addons List - Applications</div>
					</div>
					<div class="row">
						<div class="table-responsive" style="max-height:600px;">
							<table  class="table table-light table-striped" id="autoexecTable">
								<thead>
									<tr>
										<th scope="col">Enable</th>
										<th scope="col">Name</th>
										<th scope="col">Tab</th>
										<th scope="col">Folder Name</th>
										<th scope="col">Description</th>
										<th scope="col"></th>
										<th scope="col"></th>
									</tr>
								</thead>
								<tbody>
								<?php foreach ($rows_Applications as $index=>$row) : ?>
									<tr>
										<td width='1%' white-space='nowrap'><?php if($row['en']=='true') { echo '<img src="icons/check.png" width="25" height="25"/>'; } else { echo '<img src="icons/uncheck.png" width="25" height="25"/>'; }?></td>
										<td value="<?php echo $row['name']; ?>"><?php echo $row['name']; ?></td>
										<td value="<?php echo $row['tab']; ?>"><?php echo $row['tab']; ?></td>
										<td value="<?php echo $row['foldername']; ?>"><?php echo $row['foldername']; ?></td>                                    
										<td value="<?php echo $row['description']; ?>"><?php echo $row['description']; ?></td>
										<td width='1%' white-space='nowrap'>
											<button type='button' class='btn btn-primary btn-sm' title="Click to edit the Service" data-bs-toggle='modal' data-bs-target='#editModal' data-bs-id="<?php echo $row['id'];?>"  
											data-bs-enable="<?php echo $row['en'];?>" data-bs-tab="<?php echo $row['tab'];?>" data-bs-name="<?php echo $row['name'];?>" data-bs-foldername="<?php echo $row['foldername'];?>" data-bs-description="<?php echo $row['description'];?>">
											Edit</button>
										</td>          
										<td width='1%' white-space='nowrap'>
											<button type='button' class='btn btn-primary btn-sm' title="Click to delete the Addon" data-bs-toggle='modal' data-bs-target='#deleteModal' data-bs-id="<?php echo $row['id'];?>" data-bs-foldername="<?php echo $row['foldername'];?>">
											Delete</button>
										</td> 
									</tr>
								<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="row p-3 rounded-2" style="background-color:white;">
					<div class="row">
						<div class="fs-3 text-muted">Addons List - Development Tools</div>
					</div>
					<div class="row">
						<div class="table-responsive" style="max-height:600px;">
							<table  class="table table-light table-striped" id="autoexecTable">
								<thead>
									<tr>
										<th scope="col">Enable</th>
										<th scope="col">Name</th>
										<th scope="col">Tab</th>
										<th scope="col">Folder Name</th>
										<th scope="col">Description</th>
										<th scope="col"></th>
										<th scope="col"></th>
									</tr>
								</thead>
								<tbody>
								<?php foreach ($rows_Development as $index=>$row) : ?>
									<tr>
										<td width='1%' white-space='nowrap'><?php if($row['en']=='true') { echo '<img src="icons/check.png" width="25" height="25"/>'; } else { echo '<img src="icons/uncheck.png" width="25" height="25"/>'; }?></td>
										<td value="<?php echo $row['name']; ?>"><?php echo $row['name']; ?></td>
										<td value="<?php echo $row['tab']; ?>"><?php echo $row['tab']; ?></td>
										<td value="<?php echo $row['foldername']; ?>"><?php echo $row['foldername']; ?></td>                                    
										<td value="<?php echo $row['description']; ?>"><?php echo $row['description']; ?></td>
										<td width='1%' white-space='nowrap'>
											<button type='button' class='btn btn-primary btn-sm' title="Click to edit the Service" data-bs-toggle='modal' data-bs-target='#editModal' data-bs-id="<?php echo $row['id'];?>"  
											data-bs-enable="<?php echo $row['en'];?>" data-bs-tab="<?php echo $row['tab'];?>" data-bs-name="<?php echo $row['name'];?>" data-bs-foldername="<?php echo $row['foldername'];?>" data-bs-description="<?php echo $row['description'];?>">
											Edit</button>
										</td>          
										<td width='1%' white-space='nowrap'>
											<button type='button' class='btn btn-primary btn-sm' title="Click to delete the Addon" data-bs-toggle='modal' data-bs-target='#deleteModal' data-bs-id="<?php echo $row['id'];?>" data-bs-foldername="<?php echo $row['foldername'];?>">
											Delete</button>
										</td> 
									</tr>
								<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="row p-3 rounded-2" style="background-color:white;">
					<div class="row">
						<div class="fs-3 text-muted">Addons List - Topbar</div>
					</div>
					<div class="row">
						<div class="table-responsive" style="max-height:600px;">
							<table  class="table table-light table-striped" id="autoexecTable">
								<thead>
									<tr>
										<th scope="col">Enable</th>
										<th scope="col">Name</th>
										<th scope="col">Folder Name</th>
										<th scope="col">Description</th>
										<th scope="col"></th>
										<th scope="col"></th>
									</tr>
								</thead>
								<tbody>
								<?php foreach ($rows_notInMenu as $index=>$row) : ?>
									<tr>
										<td width='1%' white-space='nowrap'><?php if($row['en']=='true') { echo '<img src="icons/check.png" width="25" height="25"/>'; } else { echo '<img src="icons/uncheck.png" width="25" height="25"/>'; }?></td>
										<td value="<?php echo $row['name']; ?>"><?php echo $row['name']; ?></td>
										<td value="<?php echo $row['foldername']; ?>"><?php echo $row['foldername']; ?></td>                                    
										<td value="<?php echo $row['description']; ?>"><?php echo $row['description']; ?></td>
										<td width='1%' white-space='nowrap'>
											<button type='button' class='btn btn-primary btn-sm' title="Click to edit the Service" data-bs-toggle='modal' data-bs-target='#editModal' data-bs-id="<?php echo $row['id'];?>"  
											data-bs-enable="<?php echo $row['en'];?>" data-bs-tab="<?php echo $row['tab'];?>" data-bs-name="<?php echo $row['name'];?>" data-bs-foldername="<?php echo $row['foldername'];?>" data-bs-description="<?php echo $row['description'];?>">
											Edit</button>
										</td>          
										<td width='1%' white-space='nowrap'>
											<button type='button' class='btn btn-primary btn-sm' title="Click to delete the Addon" data-bs-toggle='modal' data-bs-target='#deleteModal' data-bs-id="<?php echo $row['id'];?>" data-bs-foldername="<?php echo $row['foldername'];?>">
											Delete</button>
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
                        <h5 class="modal-title" id="addModalLabel">Are you sure you want to upload an Addon?</h5>
                    </div>
                    <div class="modal-footer">
                        <button type='button' class="btn btn-primary" data-bs-dismiss="modal" onclick="loadAddon()">Yes</button> 
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button></div>
                    </div>
                </div>
            </div>
        </div>
		
		<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="editModalLabel">Edit the Addon parameters</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<form>
							<div class="mb-3">
								<input hidden type="text" class="form-control" id="recipient-id">
							</div>
							<div class="form-check mb-3">
								<input class="form-check-input" type="checkbox" value="" id="recipient-enable">
								<label class="form-check-label" for="flexCheckDefault">Enable</label>
							</div>
                            <div class="mb-3">
								<label for="tabSelect" class="col-form-label ">Select the Addon position in the topbar:</label>
                                <select id="tabSelect" class="form-select">
                                    <option selected>Configuration</option>
                                    <option>Application</option>
                                    <option>Development</option>
                                    <option>Topbar</option>
                                </select>
                            </div>
							<div class="mb-3">
								<label for="recipient-name" class="col-form-label">Name:</label>
								<input type="text" class="form-control" id="recipient-name" placeholder="Insert the name of the addon" value="">
							</div>
							<div class="mb-3">
								<label for="recipient-foldername" class="col-form-label ">Folder Name:</label>
								<input class="form-control" id="recipient-foldername" placeholder="Insert the folder name of the addon"></input>
							</div>
							<div class="mb-3">
								<label for="recipient-description" class="col-form-label">Description:</label>
								<textarea type="text" class="form-control" id="recipient-description"  placeholder="Insert the description" value=""></textarea>
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
                        <h5 class="modal-title" id="deleteModalLabel">Are you sure you want to delete this Addon?</h5>
                    </div>
					<input hidden type="text" class="form-control" id="deleteModal-id">
					<input hidden type="text" class="form-control" id="deleteModal-foldername">
                    <div class="modal-footer">
                        <button type='button' class="btn btn-primary" data-bs-dismiss="modal" onclick="deleteRow()">Yes</button> 
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button></div>
                    </div>
                </div>
            </div>
        </div>
		
		<div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg modal-dialog-centered" >
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="editModalLabel">Addons Manager</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"  style="height: 80vh; overflow-y: auto;">
						<p><?php include('addons_info.html'); ?></p>
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

function deleteRow()
{
	document.getElementById('loader').innerHTML = '<div class="loader"></div>';
    id = document.getElementById('deleteModal-id').value;
    foldername = document.getElementById('deleteModal-foldername').value;
    
    $.ajax({
        type : "POST",  //type of method
        url  : "addons_manager.php",  //your page
        data: "delete=1&id=" + id + "&foldername=" + foldername,
        success: function(msg)
        {
            location.reload(true);
        },
        error: function() {  }
    });
}

function updateRow()
{
	document.getElementById('loader').innerHTML = '<div class="loader"></div>';
    id = document.getElementById('recipient-id').value;
    en = document.getElementById('recipient-enable').checked;
    tab = document.getElementById('tabSelect').options[document.getElementById('tabSelect').selectedIndex].text;
    name = document.getElementById('recipient-name').value;
    descr = document.getElementById('recipient-description').value;
    foldername = document.getElementById('recipient-foldername').value; //this is the foldername before editing
    description = document.getElementById('recipient-description').value;
	$.ajax({
        type : "POST",  //type of method
        url  : "addons_manager.php",  //your page
        data: "update=1&id=" + id + "&tab=" + tab +"&en=" + en + "&name=" + name + "&foldername=" + foldername + "&description=" + description,
        success: function(msg)
        {
            location.reload(true);
        },
        error: function() {  }
    });
}

var deleteModal = document.getElementById('deleteModal');
deleteModal.addEventListener('show.bs.modal', function (event) {
  // Button that triggered the modal
  button = event.relatedTarget;
  // Extract info from data-bs-* attributes
  var id = button.getAttribute('data-bs-id');
  var foldername = decodeURIComponent(button.getAttribute('data-bs-foldername')).replace(/\+/g, ' ');  
  document.getElementById('deleteModal-id').value = id;
  document.getElementById('deleteModal-foldername').value = foldername;
})

var editModal = document.getElementById('editModal');
editModal.addEventListener('show.bs.modal', function (event) {
	// Button that triggered the modal
	button = event.relatedTarget;
	// Extract info from data-bs-* attributes
	var id = button.getAttribute('data-bs-id');
	var enable = button.getAttribute('data-bs-enable');
	var tab = decodeURIComponent(button.getAttribute('data-bs-tab')).replace(/\+/g, ' ');
	//console.log(document.getElementById('tabSelect').selectedIndex)
	var name = decodeURIComponent(button.getAttribute('data-bs-name')).replace(/\+/g, ' ');
	var foldername = decodeURIComponent(button.getAttribute('data-bs-foldername')).replace(/\+/g, ' ');  
	var description = decodeURIComponent(button.getAttribute('data-bs-description')).replace(/\+/g, ' ');
	// Update the modal's content.
	//updateModal.querySelector('.modal-title').textContent = 'Edit the tupla No ' + id;
	document.getElementById('recipient-id').value = id;
	var isTrueSet = (enable === 'true');
	document.getElementById('recipient-enable').checked = isTrueSet;
	document.getElementById('recipient-name').value = name;
	document.getElementById('recipient-foldername').value = foldername;
	document.getElementById('recipient-description').value = description;
	sel = document.getElementById('tabSelect');
	for(var i = 0, j = sel.options.length; i < j; ++i) {
		if(sel.options[i].innerHTML === tab) {
		   sel.selectedIndex = i;
		   break;
		}
	}
})

function loadAddon()
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
						url  : "addons_manager.php",  //your page
						processData: false,
						data: "installAddon=1&filename="+files[0].name,
						success: function(msg)
						{			
							location.reload(true);
						},
						error: function() { }
					});
				}else
				{
					alert("The file "+files[0].name+" hasn't been uploaded since the file extension is wrong,\r\nThe extension should be .zip");
					location.reload(true);
				}
			}
		};
		// Send request with data
		xhttp.send(formData);
	};
	input.click();
}
</script>
<?php } else header("Location:./login.php"); ?>
