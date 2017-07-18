<?php
include_once("php_includes/check_login_status.php");

// If user is already logged in, header that weenis away
if($user_ok == true){
	header("location: user.php?u=".$_SESSION["username"]);
	exit();
}
?>

<?php
// Ajax calls this NAME CHECK code to execute
if(isset($_POST["usernamecheck"])){
	include_once("php_includes/db_connect.php");
	$username = preg_replace('#[^a-z0-9]#i', '', $_POST['usernamecheck']);
	$sql = "SELECT id FROM users WHERE username='$username' LIMIT 1";
	$query = mysqli_query($db_connect, $sql); 
	$uname_check = mysqli_num_rows($query);
	
	if (strlen($username) < 3 || strlen($username) > 16) {
		echo '<strong style="color:#F00;">3 - 16 characters please</strong>';
		exit();
	}

	if (is_numeric($username[0])) {
		echo '<strong style="color:#F00;">Usernames must begin with a letter</strong>';
		exit();
	}

	if ($uname_check < 1) {
		echo '<strong style="color:#009900;">' . $username . ' is OK</strong>';
		exit();
	} else {
		echo '<strong style="color:#F00;">' . $username . ' is taken</strong>';
		exit();
	}
}
?>
<?php
// Ajax calls this REGISTRATION code to execute
if(isset($_POST["u"])){
	// CONNECT TO THE DATABASE
	include_once("php_includes/db_connect.php");

	// GATHER THE POSTED DATA INTO LOCAL VARIABLES
	$u = preg_replace('#[^a-z0-9]#i', '', $_POST['u']);
	$e = mysqli_real_escape_string($db_connect, $_POST['e']);
	$p = $_POST['p'];
	$g = preg_replace('#[^a-z]#', '', $_POST['g']);
	$c = preg_replace('#[^a-z ]#i', '', $_POST['c']);
	$s = preg_replace('#[^a-z ]#i', '' , $_POST['s']);
	$m = preg_replace('#[^a-z ]#i', '' , $_POST['m']);
	$date = preg_replace('#[^0-9]#i', '', $_POST['date']);
	$i = preg_replace('#[a-z0-9]#i', '', $_POST['i']);

	// GET USER IP ADDRESS
	$ip = preg_replace('#[^0-9.]#', '', getenv('REMOTE_ADDR'));

	// DUPLICATE DATA CHECKS FOR USERNAME AND EMAIL
	$sql = "SELECT id FROM users WHERE username='$u' LIMIT 1";
	$query = mysqli_query($db_connect, $sql); 
	$u_check = mysqli_num_rows($query);

	// -------------------------------------------
	$sql = "SELECT id FROM users WHERE email='$e' LIMIT 1";
	$query = mysqli_query($db_connect, $sql); 
	$e_check = mysqli_num_rows($query);

	// FORM DATA ERROR HANDLING
	if($u == "" || $e == "" || $p == "" || $g == "" || $c == "" || $s == "" || $m == ""){
		echo "The form submission is missing values.";
		exit();
	} else if ($u_check > 0){ 
		echo "The username you entered is alreay taken";
		exit();
	} else if ($e_check > 0){ 
		echo "That email address is already in use in the system";
		exit();
	} else if (strlen($u) < 3 || strlen($u) > 16) {
		echo "Username must be between 3 and 16 characters";
		exit(); 
	} else if (is_numeric($u[0])) {
		echo 'Username cannot begin with a number';
		exit();
	} else if ($s == ""){
		echo 'Please fill out the name of you school.';
		exit();
	} else if ($m == ""){
		echo 'Please fill out your major.';
		exit();
	} else {
		// END FORM DATA ERROR HANDLING
		// Begin Insertion of data into the database
		// Hash the password and apply your own mysterious unique salt
		//************************************************************
		//USES BASIC MD5 HASHING!!!!!! NOT COMPLIANT WITH CURRENT STANDARD! CHANGE TO blowfish
		//************************************************************
		$p_hash = md5($p);
		//************************************************************
		// Add user info into the database table for the main site table
		$sql = "INSERT INTO users (username, email, password, gender, schoolname, major, graddate, interests, country, ip, signup, lastlogin, notescheck)       
				VALUES('$u','$e','$p_hash','$g', '$s', '$m', '$date', '$i', '$c','$ip',now(),now(),now())";
		$query = mysqli_query($db_connect, $sql); 
		$uid = mysqli_insert_id($db_connect);

		// Establish their row in the useroptions table
		$sql = "INSERT INTO useroptions (id, username, background) VALUES ('$uid','$u','original')";
		$query = mysqli_query($db_connect, $sql);

		// Create directory(folder) to hold each user's files(pics, MP3s, etc.)
		if (!file_exists("user/$u")) {
			mkdir("user/$u", 0755);
		}
		
		// Email the user their activation link
		$to = "$e";
		$from = "auto_responder@mousetech.org";
		$subject = 'Social Campus Account Activation';
		$message = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>College Social Message</title></head>
					<body style="margin:0px; font-family:Tahoma, Geneva, sans-serif;"><div style="padding:10px;
					background:#333; font-size:24px; color:#CCC;"><a href="http://school.mousetech.org">
					<img src="http://school.mousetech.org/assets/img/logo.png" width="36" height="30" alt="College Social" 
					style="border:none; float:left;"></a>College Social Account Activation</div><div style="padding:24px; 
					font-size:17px;">Hello '.$u.',<br /><br />Click the link below to activate your account when ready:
					<br /><br /><a href="http://school.mousetech.org/activation.php?id='.$uid.'&u='.$u.'&e='.$e.'&p='.$p_hash.'">
					Click here to activate your account now</a><br /><br />Login after successful activation using your:
					<br />* E-mail Address: <b>'.$e.'</b></div></body></html>';
		$headers = "From: $from\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\n";
		
		mail($to, $subject, $message, $headers);
		echo "signup_success";
		exit();
	}
	exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title>Sign Up</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">
	
	<!-- Le styles -->
	<link href="assets/css/bootstrap.min.css" rel="stylesheet" />
	<link href="assets/css/bootstrap-responsive.min.css" rel="stylesheet" />
	<style type="text/css">
		html {
			background: url(assets/img/background_park.jpg) no-repeat center center fixed;
			-webkit-background-size: cover;
			-moz-background-size: cover;
			-o-background-size: cover;
			background-size: cover;
		}
		body {
			padding-top: 40px;
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
	
	<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
		<script src="assets/js/html5shiv.js"></script>
	<![endif]-->
	
	<!-- Fav and touch icons -->
	<link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/ico/apple-touch-icon-144-precomposed.png" />
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png" />
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png" />
	<link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png" />
	<link rel="shortcut icon" href="assets/ico/favicon.png" />
	
	<script src="http://code.jquery.com/jquery.min.js"></script>
	<script src="assets/js/ajax.js"></script>
	<script src="assets/js/main.js"></script>
	
	<script type="text/javascript">
		function restrict(elem){
			var tf = _(elem);
			var rx = new RegExp;
			if(elem == "email"){
				rx = /[' "]/gi;
			} else if(elem == "username"){
				rx = /[^a-z0-9]/gi;
			}
			tf.value = tf.value.replace(rx, "");
		}

		function emptyElement(x){
			_(x).innerHTML = "";
		}

		function checkusername(){
			var u = _("username").value;
			if(u != ""){
				_("unamestatus").innerHTML = 'checking ...';
				var ajax = ajaxObj("POST", "signup.php");
				
				ajax.onreadystatechange = function() {
				
					if(ajaxReturn(ajax) == true) {
						_("unamestatus").innerHTML = ajax.responseText;
					}
				}
				
				ajax.send("usernamecheck="+u);
			}
		}

		function signup(){
			var u = _("username").value;
			var e = _("email").value;
			var p1 = _("pass1").value;
			var p2 = _("pass2").value;
			var c = _("country").value;
			var g = _("gender").value;
			var s = _("schoolname").value;
			var m = _("major").value;
			var date = _("graddate").value;
			var i = _("interests").value;
			var status = _("status");
			
			
			if(u == "" || e == "" || p1 == "" || p2 == "" || c == "" || g == "" || s == "" || m == ""){
				status.innerHTML = "Fill out all of the form data";
			} else if(p1 != p2){
				status.innerHTML = "Your password fields do not match";
			} else if( _("terms").style.display == "none"){
				status.innerHTML = "Please view the terms of use";
			} else {
				_("signupbtn").style.display = "none";
				status.innerHTML = 'please wait ...';
				var ajax = ajaxObj("POST", "signup.php");
				
				ajax.onreadystatechange = function() {
					if(ajaxReturn(ajax) == true) {
						if(ajax.responseHTML != "signup_success"){
							// alert(ajax.responseHTML);
							// window.scrollTo(0,0);
							_("signupform").innerHTML = "Thanks, <strong>"+ u + "</strong>! Please check your inbox at <u>"+e+"</u> to complete the sign up process by activating your account.";
						} else {
							status.innerHTML = ajax.responseText;
							_("signupbtn").style.display = "block";
							
						}
					}
				}
				ajax.send("u="+u+"&e="+e+"&p="+p1+"&c="+c+"&g="+g+"&s="+s+"&m="+m+"&date="+date+"&i="+i);
			}
		}

		function openTerms(){
			//MAY CHANGE TO HAVE USER AGREE TO TOS BEFORE BUTTON BECOMES ACTIVE.
			//BUTTON SHOULD BE DEACTIVATED UNTIL USER AGREES TO TOS.
			_("terms").style.display = "block";
			emptyElement("status");
		}
		
		/* function addEvents(){
			_("elemID").addEventListener("click", func, false);
		}
		
		window.onload = addEvents; */
	</script>
</head>

<body>
	<?php include_once('assets/header_template.php'); ?>
	
	<div class="container">
		
		
		<!-- CHANGE THIS TO BOOTSTRAP COMPAT -->
		<form name="signupform" class="form-signin" id="signupform" onsubmit="return false;">
			<h3 class="form-signin-heading">Sign Up Here</h3>
			<div>Username: </div>
			<input class="input-block-level" id="username" type="text" onblur="checkusername()" onkeyup="restrict('username')" maxlength="16">
			<span id="unamestatus"></span>
			
			<div>Email Address:</div>
			<input class="input-block-level" id="email" type="text" onfocus="emptyElement('status')" onkeyup="restrict('email')" maxlength="88">
			
			<div>Create Password:</div>
			<input class="input-block-level" id="pass1" type="password" onfocus="emptyElement('status')" maxlength="100">
			
			<div>Confirm Password:</div>
			<input class="input-block-level" id="pass2" type="password" onfocus="emptyElement('status')" maxlength="100">
			
			<div>Gender:</div>
			<select id="gender" onfocus="emptyElement('status')">
				<option value=""></option>
				<option value="m">Male</option>
				<option value="f">Female</option>
			</select>
			
			<div>School:</div>
			<input class="input-block-level" id="schoolname" type="text" onblur="emptyElement('status')" maxlength="100">
			
			<div>Major:</div>
			<input class="input-block-level" id="major" type="text" onblur="emptyElement('status')" maxlength="100" />
			
			<div>Date of Graduation:</div>
			<input class="input-block-level" id="graddate" type="text" onblur="emptyElement('status')" />
			
			<div>Interests: (Separate by comma (,))</div>
			<textarea class="input-block-level" id="interests" onblur="emptyElement('status')"></textarea>
			
			<div>Country:</div>
			<select id="country" onfocus="emptyElement('status')">
				<?php include_once("php_includes/countries_list.php"); ?>
			</select>
		    
			<div>
				<a href="#" onclick="return false" onmousedown="openTerms()">
				Click here to agree with our Terms Of Use
				</a>
			</div>
		
			<div id="terms" style="display:none;">
				<h4>Thank you!</h4>
				<p>You may now proceed to submit your account.</p>
			</div>
		
			<br /><br />
		
			<button class="btn btn-large btn-primary" id="signupbtn" onclick="signup()">Create Account</button>
			
			<span id="status"></span>
		</form>
	</div> <!-- /container -->

	<?php include_once('assets/footer_template.php'); ?>

</body>
</html>