<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/ 
require 'helper/init.php';

function isValidUsername($username){
	if (strlen($username) < 3) return false;
	if (strlen($username) > 17) return false;
	if (!ctype_alnum(str_replace(' ', '', $username))) return false;
	return true;
}    

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
	if (isset($_FILES['files'])) {
		$errors = [];
		$path = 'uploads/imgs/';
		$extensions = ['jpg', 'jpeg', 'png', 'gif'];
			
		$file_name = $_SESSION["id"];
		$file_tmp = $_FILES['files']['tmp_name'];
		$file_type = $_FILES['files']['type'];
		$file_size = $_FILES['files']['size'];
		$tmp = explode('.', $_FILES['files']['name']);
		$file_ext = strtolower(end($tmp));

		$file = $path . $file_name;
		if (!in_array($file_ext, $extensions)) {
			$errors[] = 'Extension not allowed: ' . $file_name . ' ' . $file_type;
		}

		if ($file_size > 2000000) {
			$errors[] = 'File size exceeds limit: ' . $file_name . ' ' . $file_type;
		}

		if (empty($errors)) {
			move_uploaded_file($file_tmp, $file.'.'.$file_ext);
			try {
				$dbConstructor_main->updateUsers_avatar($_SESSION["id"], $file_name.'.'.$file_ext);
				$_SESSION["avatar"] = Config_main::uploadImgPath.$file_name.'.'.$file_ext;
				
				//header("Location:editprofile.php");
				//exit();
			//else catch the exception and show the error.
			} catch(PDOException $e) {
				$error[] = $e->getMessage();
			}
		}
	}

	if (empty($_POST['your_name']) == false) 
	{
		$name = $_POST['your_name'];
		//very basic validation
		if(!isValidUsername($name)){
			$nameerror[] = 'Name field must be at least 3 Alphanumeric characters';
		} else {
			$row = $dbConstructor_main->checkUsers_UserExists($name);
			if(!empty($row['name'])){
				$nameerror[] = 'Username provided is already in use.';
			}
		}

		if(!isset($nameerror)){
			try {
				$dbConstructor_main->updateUsers_name($_SESSION["id"], $name);
				$_SESSION["name"] =$name;
				header("Location:editprofile.php");
				exit();
			//else catch the exception and show the error.
			} catch(PDOException $e) {
				$error[] = $e->getMessage();
			}
		} else { 
			if(!isset($error)) $error[]="";
			$error = array_merge($error, $nameerror); 
		}
	}

	if (empty($_POST['emailAddress']) == false)
	{
		//email validation
		$email = htmlspecialchars_decode($_POST['emailAddress'], ENT_QUOTES);
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			$emailerror[] = 'Please enter a valid email address';
		} else {
			$row = $dbConstructor_main->checkUsers_EmailExists($email);
			if(!empty($row['email'])){
				$emailerror[] = 'Email provided is already in use.';
			}
		}

		if(!isset($emailerror)){
			try {
				$dbConstructor_main->updateUsers_email($_SESSION["id"], $email);
				$_SESSION["email"] =$email;
				header("Location:editprofile.php");
				exit();
			//else catch the exception and show the error.
			} catch(PDOException $e) {
				$error[] = $e->getMessage();
			}
		} else {
			if(!isset($error)) $error[]="";
			$error = array_merge($error, $emailerror); 
		}
	}

	if ((empty($_POST['password']) == false) && (empty($_POST['passwordConfirm']) == false))
	{
		if(strlen($_POST['password']) < 3){
			$pswerror[] = 'Password is too short.';
		}
		if(strlen($_POST['passwordConfirm']) < 3){
			$pswerror[] = 'Confirm password is too short.';
		}
		if($_POST['password'] != $_POST['passwordConfirm']){
			$pswerror[] = 'Passwords do not match.';
		}

		if(!isset($pswerror)){
			//hash the password
			$hashedpassword = md5($_POST['password']);
			try {
				$dbConstructor_main->updateUsers_password($_SESSION["id"], $hashedpassword);
				$_SESSION["password"] =$hashedpassword;
				header("Location:editprofile.php");
				exit();
			//else catch the exception and show the error.
			} catch(PDOException $e) {
				$error[] = $e->getMessage();
			}
		} else {
			if(!isset($error)) $error[]="";
			$error = array_merge($error, $pswerror); 
		}
	} else if ((empty($_POST['password']) == false) || (empty($_POST['passwordConfirm']) == false)){
		$error[] = "If you want to change passwords, the two passwords fields should be filled and equal"; 
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
		<title>öchìn - Edit Account</title>
    </head>
	<?php include Config_main::topbar_path;?>
	<body style="background-color:#f2f2f2;">
		<div class="container-xl">
			<div class="row">
				<div class="display-2 text-dark mt-2 mb-4 font-weight-normal text-center"><?php echo $_SESSION["name"]; ?>'s User Profile</div>
			</div>
			<div class="row p-3 rounded-2" style="background-color:white;">
				<div class="container mt-5">
					<form method="post" id="avatarUpl" style="max-width: 400px; margin: auto;">
						<div class="row justify-content-md-center">	
								<img  class="mt-3 mb-3 rounded-circle" id="pic" style="max-width: 350px; max-height: 350px; object-fit:cover;" src="<?php echo $_SESSION["avatar"];?>">
						</div>	
						<div class="row justify-content-md-center">	
								<input class="form-control" type="file" name="AvatarSelect">
						</div>	
						<div class="row justify-content-md-center">	
								<button class="btn btn-sm btn-primary w-20 mt-3" name="submit">Upload Avatar</button>
						</div>	
					</form>					
				</div>
				<div class="container mt-5 mb-5">			
					<form method="post" style="max-width: 400px; margin: auto;">	
						<div class="row justify-content-md-center">		
							<div class="col">		
								<label for="your_name" class="form-label mt-3">Your Name</label>
							</div>	
							<div class="col-6">			
								<input id="your_name" name="your_name" class="form-control border border-1 rounded-2" placeholder="<?php echo $_SESSION["name"]; ?>" autofocus>
							</div>
						</div>	
						<div class="row justify-content-md-center">	
							<div class="col">		
								<label for="emailAddress" class="form-label mt-3">Your email</label>
							</div>	
							<div class="col-6">		
								<input type="email" id="emailAddress" name="emailAddress" class="form-control border border-1 rounded-2" placeholder="<?php echo $_SESSION["email"]; ?>" autofocus>
							</div>
						</div>	
						<div class="row justify-content-md-center">	
							<div class="col">					
								<label for="password" class="form-label mt-3">Your Password</label>
							</div>	
							<div class="col-6">		
								<input type="password" id="password" name="password" class="form-control border border-1 rounded-2" placeholder="Your Password">
							</div>
						</div>	
						<div class="row justify-content-md-center">		
							<div class="col">				
								<label for="passwordConfirm" class="form-labelb mt-3">Your Password</label>	
							</div>	
							<div class="col-6">		
								<input type="password" id="passwordConfirm" name="passwordConfirm"class="form-control border border-1 rounded-2" placeholder="Repeat your Password">  
							</div>
						</div>	
						<div class="row justify-content-md-center">	 
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
						</div>	
						<div class="row justify-content-md-center">	
							<button class="btn btn-sm btn-primary w-100 mt-3">Update Personal Data</button>
						</div>	
					</form>		
				</div>
			</div>
		</div>
	</body>
</html>

<script>
const url = 'editprofile.php';
const form = document.querySelector('form');

document.getElementById('avatarUpl').addEventListener('submit', e => {
    e.preventDefault();
    const files = document.querySelector('[name=AvatarSelect]').files;
    const formData = new FormData();

    for (let i = 0; i < files.length; i++) {
        let file = files[i];

        formData.append('files', files[0]);
    }

    fetch(url, {
        method: 'POST',
        body: formData
    }).then(response => {
        location.reload(true);
        return response.text();
    }).then(data => {
        console.log(data);
    });
});
</script>