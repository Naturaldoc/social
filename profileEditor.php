<?php
include_once("php_includes/check_login_status.php");
/*
*	username,
*	email,
*	gender,
*	website,
*	school,
*	major,
*	graddate,
*	interests
*/

$u = '';

// Make sure the _GET username is set, and sanitize it
if(isset($_GET["u"])){
	$u = preg_replace('#[^a-z0-9]#i', '', $_GET['u']);
} else {
	header("location: index.php");
	exit();
}

// Select the member from the users table
$sql = "SELECT * FROM users WHERE username='$u' AND activated='1' LIMIT 1";
$user_query = mysqli_query($db_connect, $sql);

// Now make sure that user exists in the table
$numrows = mysqli_num_rows($user_query);

if($numrows < 1){
	echo "That user does not exist or is not yet activated, press back";
	exit();
}

if(isset($_POST['email']) || isset($_POST['gender'])
	|| isset($_POST['website']) || isset($_POST['school'])
	|| isset($_POST['major']) || isset($_POST['graddate'])
	|| isset($_POST['interests']))
{
	$e = mysqli_real_escape_string($db_connect, $_POST['email']);
	$g = preg_replace('#[^a-z]#', '', $_POST['gender']);
	$w = $_POST['website'];
	$s = preg_replace('#[^a-z ]#i', '' , $_POST['school']);
	$m = preg_replace('#[^a-z ]#i', '' , $_POST['major']);
	$date = preg_replace('#[^0-9]#i', '', $_POST['graddate']);
	$i = $_POST['interests'];

/*	$debug_vars = array($user, $e, $g, $w, $s, $m, $date, $i, $log_id);
	foreach($debug_vars as $var)
	{
		echo $var . "<br />";
	}
*/
//echo 'Post var: ' . $_POST['interests'];
//echo 'Input var: ' . $i;
//die();
	$sql = $db_connect->stmt_init();
	$sql->prepare("UPDATE users SET email=?,
		gender=?,
		website=?,
		schoolname=?,
		major=?,
		graddate=?,
		interests=?
		WHERE id=?"
	);
	$sql->bind_param("sssssisi", $e, $g, $w, $s, $m, $date, $i, $log_id);
	$sql->execute();
	$sql->close();
	mysqli_close();
	header('Location: user.php?u='.$log_username);
}
else
{
	die(mysqli_error());
}
?>