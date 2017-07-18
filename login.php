<?php
include_once("php_includes/check_login_status.php");

// If user is already logged in, header away to Profile page
if($user_ok == true){
	header("location: user.php?u=".$_SESSION["username"]);
	exit();
}
?>
<?php
// AJAX CALLS THIS LOGIN CODE TO EXECUTE
if(isset($_POST["e"])){
	// CONNECT TO THE DATABASE
	include_once("php_includes/db_connect.php");

	// GATHER THE POSTED DATA INTO LOCAL VARIABLES AND SANITIZE
	$e = mysqli_real_escape_string($db_connect, $_POST['e']);
	$p = md5($_POST['p']);

	// GET USER IP ADDRESS
	$ip = preg_replace('#[^0-9.]#', '', getenv('REMOTE_ADDR'));

	// FORM DATA ERROR HANDLING
	if($e == "" || $p == ""){
		echo "login_failed";
		exit();
	} else {
		// END FORM DATA ERROR HANDLING
		$sql = "SELECT id, username, password FROM users WHERE email='$e' AND activated='1' LIMIT 1";
		$query = mysqli_query($db_connect, $sql);
		$row = mysqli_fetch_row($query);
		$db_id = $row[0];
		$db_username = $row[1];
		$db_pass_str = $row[2];
		
		if($p != $db_pass_str){
			echo "login_failed";
			exit();
		} else {
			// CREATE THEIR SESSIONS AND COOKIES
			$_SESSION['userid'] = $db_id;
			$_SESSION['username'] = $db_username;
			$_SESSION['password'] = $db_pass_str;
			setcookie("id", $db_id, strtotime( '+30 days' ), "/", "", "", TRUE);
			setcookie("user", $db_username, strtotime( '+30 days' ), "/", "", "", TRUE);
			setcookie("pass", $db_pass_str, strtotime( '+30 days' ), "/", "", "", TRUE); 
			
			// UPDATE THEIR "IP" AND "LASTLOGIN" FIELDS
			$sql = "UPDATE users SET ip='$ip', lastlogin=now() WHERE username='$db_username' LIMIT 1";
			$query = mysqli_query($db_connect, $sql);
			echo $db_username;
			exit();
		}
	}
	exit();
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Log In</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon">
	<link rel="stylesheet" href="assets/css/bootstrap.min.css" />
	<link rel="stylesheet" href="assets/css/bootstrap-responsive.css">
	
	<style type="text/css">
		html {
			background: url(assets/img/background_park.jpg) no-repeat center center fixed;
			-webkit-background-size: cover;
			-moz-background-size: cover;
			-o-background-size: cover;
			background-size: cover;
		}
		body {
			padding-top: 60px;
			padding-bottom: 40px;
			background: transparent;
		}
		.form-signin {
			max-width: 300px;
			padding: 19px 29px 29px;
			margin: 0 auto 20px;
			background-color: #fff;
			border: 1px solid #e5e5e5;
			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
			border-radius: 5px;
			-webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
			-moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
			box-shadow: 0 1px 2px rgba(0,0,0,.05);
		}
		.form-signin .form-signin-heading,
		.form-signin .checkbox {
			margin-bottom: 10px;
		}
		.form-signin input[type="text"],
		.form-signin input[type="password"] {
			font-size: 16px;
			height: auto;
			margin-bottom: 15px;
			padding: 7px 9px;
		}
	</style>

	<script src="assets/js/main.js"></script>
	<script src="assets/js/ajax.js"></script>
	
	<script>
	function emptyElement(x){
		_(x).innerHTML = "";
	}
	
	function login(){
		var e = _("email").value;
		var p = _("password").value;

		if(e == "" || p == ""){
			_("status").innerHTML = "Fill out all of the form data";
		} else {
			_("loginbtn").style.display = "none";
			_("status").innerHTML = 'please wait ...';
			var ajax = ajaxObj("POST", "login.php");
			
			ajax.onreadystatechange = function() {
				if(ajaxReturn(ajax) == true) {
					if(ajax.responseText == "login_failed"){
						_("status").innerHTML = "Login unsuccessful, please try again.";
						_("loginbtn").style.display = "block";
					} else {
						window.location = "user.php?u="+ajax.responseText;
					}
				}
			}
			ajax.send("e="+e+"&p="+p);
		}
	}
	</script>
</head>
<body>
	<?php include_once("assets/header_template.php"); ?>
	
	<div class="container">
		<!-- LOGIN FORM -->
		<form class="form-signin" id="loginform" onsubmit="return false;">
			<h3 class="form-signin-heading">Log In Here</h3>
			<div>Email Address:</div>
			<input type="text" class="input-block-level" id="email" onfocus="emptyElement('status')" maxlength="88">
			<div>Password:</div>
			<input type="password" class="input-block-level" id="password" onfocus="emptyElement('status')" maxlength="100">
			<br /><br />
			<button class="btn btn-large btn-primary" id="loginbtn" onclick="login()">Log In</button> 
			<p id="status"></p>
			<a href="forgot_pass.php">Forgot Your Password?</a>
		</form>
		<!-- /LOGIN FORM -->
	</div>

	<?php include_once("assets/footer_template.php"); ?>
</body>
</html>


