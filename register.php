<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/ 
require 'helper/init.php';

// create new tables
$dbConstructor_main->createTableUsers();

function isValidUsername($username){
	if (strlen($username) < 3) return false;
	if (strlen($username) > 17) return false;
	if (!ctype_alnum(str_replace(' ', '', $username))) return false;
	return true;
}

if(count($_POST)>0) {

    if (!isset($_POST['your_name'])) $error[] = "Please fill out the name field";
    if (!isset($_POST['emailAddress'])) $error[] = "Please fill out the email field";
    if (!isset($_POST['password']) or !isset($_POST['passwordConfirm'])) $error[] = "Please fill out the password fields";

	$name = $_POST['your_name'];
	//very basic validation
	if(!isValidUsername($name)){
		$error[] = 'Name field must be at least 3 Alphanumeric characters';
	} else {
		$row = $dbConstructor_main->checkUsers_UserExists($name);
		if(!empty($row['name'])){
			$error[] = 'Username provided is already in use.';
		}
	}

	if(strlen($_POST['password']) < 3){
		$error[] = 'Password is too short.';
	}
	if(strlen($_POST['passwordConfirm']) < 3){
		$error[] = 'Confirm password is too short.';
	}
	if($_POST['password'] != $_POST['passwordConfirm']){
		$error[] = 'Passwords do not match.';
	}

	//email validation
	$email = htmlspecialchars_decode($_POST['emailAddress'], ENT_QUOTES);
	if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
	    $error[] = 'Please enter a valid email address';
	} else {
		$row = $dbConstructor_main->checkUsers_EmailExists($email);
		if(!empty($row['email'])){
			$error[] = 'Email provided is already in use.';
		}
	}
	//if no errors have been created carry on
	if(!isset($error)){
		//hash the password
		$hashedpassword = md5($_POST['password']);
		try {
            $id = $dbConstructor_main->insertRowUsers($name, $email, $hashedpassword);
			//redirect to index page
            header("Location:index.php");
			exit();
		//else catch the exception and show the error.
		} catch(PDOException $e) {
		    $error[] = $e->getMessage();
		}
	}
}
?>

<!doctype html>
<html lang="en">
    <head>
    <script type="text/javascript" src="lib/jquery-3.6.0.min.js"></script> 
    <!-- Required meta tags for Bootstrap 5-->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="lib/bootstrap-5.0.1-dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <!-- Bootstrap js -->
    <script src="lib/bootstrap-5.0.1-dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>

    <title>öchìn Create Account</title>
    </head>
    <body style=" background-color:  #273746;">
        <div class="text-center mt-5">
            <div style="max-width: 500px; margin: auto; border-style: inset; border-radius: 16px; border-width: 1px; background-color: white;">
            <form method="post" style="max-width: 400px;margin: auto;">
                <img class="mt-4 mb-4" src="icons/ochin.png" height="72" alt="ochin logo">
                <h1 class="h3 mb-3 font-weight-normal">Create an Account</h1>
                <input id="your_name" name="your_name" class="form-control border border-1 rounded-2 mt-3" placeholder="Your Name" required autofocus>
                <input type="email" id="emailAddress" name="emailAddress" class="form-control border border-1 rounded-2 mt-3" placeholder="Your Email" required>
                <input type="password" id="password" name="password" class="form-control border border-1 rounded-2 mt-3" placeholder="Your Password" required>
                <input type="password" id="passwordConfirm" name="passwordConfirm"class="form-control border border-1 rounded-2 mt-3" placeholder="Repeat your Password" required>   
                <div>
<?php
//check for any errors
if(isset($error)){
    foreach($error as $error){
        echo '<p style="color: red;">'.$error.'</p>';
    }
}
?>
                </div>
                <div class="mt-5">
                    <button class="btn btn-lg btn-primary w-100">Sign Up</button>
                </div>
                <div class="mt-5 mb-5">
                    <label>Have already an account?  <a href="index.php">Login here</a> </label>
                </div>
            </form>
        </div>
    </body>
</html>