<nav class="navbar navbar-expand-lg navbar-dark" style="background-color:  #273746;">
  <div class="container-fluid">
    <a class="navbar-brand" href="/ochin/index.php">öchìn</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/ 
if(!$dbConstructor_main) require dirname(__FILE__)."/helper/init.php";
$rows_addons = $dbConstructor_main->getRowsAddons_InTabs(array("Application"),'=','OR');
if($rows_addons!=null)
{        
	echo '<li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Applications
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdown">';
	foreach ($rows_addons as $index=>$row_addons) :
		if($row_addons['en']=="true") { echo '<li><a class="dropdown-item" href="'."/ochin/apps/".$row_addons['foldername'].'">'.$row_addons['name'].'</a></li>'; }
	endforeach;	
	echo '</ul>
			</li>';
}
if(!$dbConstructor_main) require dirname(__FILE__)."/helper/init.php";
$rows_addons = $dbConstructor_main->getRowsAddons_InTabs(array("Development"),'=','OR');
if($rows_addons!=null)
{        
	echo '<li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Software Development
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdown">';
	foreach ($rows_addons as $index=>$row_addons) :
		if($row_addons['en']=="true") { echo '<li><a class="dropdown-item" href="'."/ochin/apps/".$row_addons['foldername'].'">'.$row_addons['name'].'</a></li>'; }
	endforeach;	
	echo '</ul>
			</li>';
}			
?>		
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Configuration
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="/ochin/editprofile.php">User Profile</a></li>
            <li><a class="dropdown-item" href="/ochin/addons_manager.php">Addons Manager</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/ochin/apps/wifiConfig">WiFi Config</a></li>
            <li><a class="dropdown-item" href="/ochin/apps/config_editor">Hardware Config</a></li>
            <li><a class="dropdown-item" href="/ochin/apps/modules_editor">Kernel Modules</a></li>
            <li><a class="dropdown-item" href="/ochin/apps/service_editor">Systemctl Services</a></li>
            <li><a class="dropdown-item" href="/ochin/apps/autostart">Autostart at boot</a></li>
            <li><hr class="dropdown-divider"></li>
<?php
$rows_addons = $dbConstructor_main->getRowsAddons_InTabs(array("Configuration"),'=','OR');
if($rows_addons!=null)
{
	foreach ($rows_addons as $index=>$row_addons) :
		if($row_addons['en']=="true") { echo '<li><a class="dropdown-item" href="'."/ochin/apps/".$row_addons['foldername'].'">'.$row_addons['name'].'</a></li>'; }
	endforeach;	
}
?>	
            <!--li><a class="dropdown-item" href="/ochin/apps/atlas">Local Atlas</a></li-->
          </ul>
        </li>
<?php
$rows_addons = $dbConstructor_main->getRowsAddons_InTabs(array("Configuration","Application","Development"),'!=','AND');
if($rows_addons!=null)
{
	foreach ($rows_addons as $index=>$row_addons) :
		if($row_addons['en']=="true") { echo '<li><a class="nav-item"><a class="nav-link" href="'."/ochin/apps/".$row_addons['foldername'].'">'.$row_addons['name'].'</a></li>'; }
	endforeach;	
}
?>			
        <li class="nav-item">
          <a class="nav-link" href="/ochin/logout.php">Log Out</a>
        </li>
      </ul>
        <li class="d-flex  me-2">
            <div class="d-flex me-4 fs-5 mt-1 text-white"><?php echo $_SESSION["name"]; ?></div>
            <a class="d-flex me-4 m-width" href="/ochin/editprofile.php"><img  style="width: 40px; height: 40px; object-fit:cover;" class="rounded-circle" src="/ochin/<?php if( $_SESSION["avatar"] != 'NULL') echo $_SESSION["avatar"]; ?>" height="40"></a>
            <div class="d-flex"><img src="/ochin/icons/ochin_wh.png" height="40" alt="ochin logo"></div>
        </li>
    </div>
  </div>
</nav>