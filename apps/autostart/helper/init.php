<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/ 
require 'helper/SQLiteConstructor.php';
require 'helper/Config.php';

//use App\SQLiteConstructor as SQLiteConstructor;
$dbConstructor = new SQLiteConstructor();
$ochin_db = $dbConstructor->connect(Config::autostart_db);
session_start();
?>