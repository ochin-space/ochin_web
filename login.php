<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/  
if(!is_dir("./db")) mkdir("./db"); //check if "db" folder exist and eventually create it
if(!is_dir("./tmp")) mkdir("./tmp"); //check if "tmp" folder exist and eventually create it

require 'helper/init.php';

// create new user and topbar tables if it doesn't already exist
$dbConstructor_main->createTableUsers();
$dbConstructor_main->createTableAddons();
 
if((isset($_POST['email']) || isset($_POST['name'])) && isset($_POST['password'])) {
    if($result = $dbConstructor_main->checkUsers_Credentials($_POST['email'],$_POST['name'],$_POST['password']))
    {
        if(!empty($_POST["remember-me"])) {
            if(isset($_POST['email']))
            {
                setcookie ("email",$_POST["email"],time()+ (10 * 365 * 24 * 60 * 60));
            } 
            if(isset($_POST['name']))
            {
                setcookie ("name",$_POST["name"],time()+ (10 * 365 * 24 * 60 * 60));
            }
            if(isset($_POST['password']))
            {
                setcookie ("password",$_POST["password"],time()+ (10 * 365 * 24 * 60 * 60));
            }
        } else {
            if(isset($_COOKIE["name"])) {
                setcookie ("name","");
            }
            if(isset($_COOKIE["email"])) {
                setcookie ("email","");
            }
            if(isset($_COOKIE["password"])) {
                setcookie ("password","");
            }
        }
        $_SESSION["loggedin"] = true;
        $_SESSION["id"] = $result['id'];
        $_SESSION["email"] = $result['email'];
        $_SESSION["name"] = $result['name'];
        if($result['avatar']==''){
            $_SESSION["avatar"] = Config_main::uploadImgPath."default_avatar.png";
        }
        else{
            $_SESSION["avatar"] = Config_main::uploadImgPath.$result['avatar'];
        }
        $_SESSION["password"] = $_POST['password'];
        header("location: index.php");
    } else {        
        $error = "Invalid Username or Password!";
    }
}
?>

<!doctype html>
<html lang="en">
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

    <title>öchìn Login</title>
    </head>
    <body style=" background-color:  #273746;">
        <div class="text-center mt-5">
            <div style="max-width: 500px; margin: auto; border-style: inset; border-radius: 16px; border-width: 1px; background-color: white;">
            <form method="post" style="max-width: 400px;margin: auto;">
                <img class="mt-4 mb-4" src="icons/ochin.png" height="72" alt="ochin logo">
                <h1 class="h3 mb-3 font-weight-normal">Please Sign In</h1>
                <input id="name" name="name" class="form-control border border-1 rounded-2 mt-3" placeholder="Your Name" value="<?php if(isset($_COOKIE["name"])) { echo $_COOKIE["name"]; } ?>" autofocus>
                <div class="mt-0 font-weight-normal">or</div>
                <input type="email" id="email" name="email" class="form-control border border-1 rounded-2 mt-0" placeholder="Email Address" value="<?php if(isset($_COOKIE["email"])) { echo $_COOKIE["email"]; } ?>" >
                <input type="password" id="password"  name="password" class="form-control border border-1 rounded-2 mt-5" placeholder="Password" value="<?php if(isset($_COOKIE["password"])) { echo $_COOKIE["password"]; } ?>" required>
                <div>
<?php
//check for any errors
if(isset($error)){
    echo '<p style="color: red;">'.$error.'</p>';
}
?>
                </div>
                <div class="checkbox mt-3">
                    <label>
                        <input type="checkbox" name="remember-me" value="remember-me" checked> Remember me
                    </label>
                </div>
                <div class="mt-3">
                    <button class="btn btn-lg btn-primary w-100">Sign In</button>
                </div>
                <div class="mt-5 mb-5">
                    <label>Don't have an account?  <a href="register.php">Register here</a></label>
                </div>
            </form>
        </div>
    </div>
    </body>
</html>
