<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/  
session_start();
unset($_SESSION["id"]);
unset($_SESSION["email"]);
unset($_SESSION["name"]);
unset($_SESSION["password"]);
unset($_SESSION["avatar"]);
unset($_SESSION["loggedin"]);
header("Location:login.php");
?>