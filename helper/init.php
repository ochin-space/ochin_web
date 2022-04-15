<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/ 
require 'SQLiteConstructor.php';
require 'Config.php';
//use App\SQLiteConstructor as SQLiteConstructor;
$dbConstructor_main = new SQLiteConstructor_main();
$ochin_db = $dbConstructor_main->connect(dirname(__DIR__, 1)."/".Config_main::ochin_db);
session_start();
?>