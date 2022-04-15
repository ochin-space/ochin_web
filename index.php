<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/ 
require 'helper/init.php';
if(isset($_SESSION["loggedin"]) && ($_SESSION["loggedin"]==true)) { ?>
<!doctype html>
<html>
    <head>
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
    </body>
</html>
<?php } else header("Location:login.php"); ?>
