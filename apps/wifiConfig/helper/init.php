<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/ 
require 'helper/SQLiteConstructor.php';
require 'helper/Config.php';
$dbConstructor = new SQLiteConstructor();
$ochin_db = $dbConstructor->connect(Config::networkConfig_db);
session_start();
?>