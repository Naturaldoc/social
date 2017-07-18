<?php
session_start();
include_once("db_connect.php");

/*
 * This file eliminates all need to connect to the database
 * or use session_start().
 * Use this file cautiously.
 */


// Variable initialization
$user_ok = false;
$log_id = "";
$log_username = "";
$log_password = "";


/**
 * User verification.
 *
 * @param $db_connect
 * @param $id
 * @param $u
 * @param $p
 *
 * @return bool
 */function evalLoggedUser($db_connect,$id,$u,$p){
	$sql = "SELECT ip FROM users WHERE id='$id' AND username='$u' AND password='$p' AND activated='1' LIMIT 1";
	$query = mysqli_query($db_connect, $sql);
	$numrows = mysqli_num_rows($query);
	if($numrows > 0){
		return true;
	}
}

/**
 * Are the 'userid', 'username', and 'password' session (or cookie) variables set?
 * If they are set, verify that the user is ok.
 */
if(isset($_SESSION["userid"]) && isset($_SESSION["username"]) && isset($_SESSION["password"])) {
	$log_id = preg_replace('#[^0-9]#', '', $_SESSION['userid']);
	$log_username = preg_replace('#[^a-z0-9]#i', '', $_SESSION['username']);
	$log_password = preg_replace('#[^a-z0-9]#i', '', $_SESSION['password']);

	// Verify the user
	$user_ok = evalLoggedUser($db_connect,$log_id,$log_username,$log_password);
} else if(isset($_COOKIE["id"]) && isset($_COOKIE["user"]) && isset($_COOKIE["pass"])){
	$_SESSION['userid'] = preg_replace('#[^0-9]#', '', $_COOKIE['id']);
	$_SESSION['username'] = preg_replace('#[^a-z0-9]#i', '', $_COOKIE['user']);
	$_SESSION['password'] = preg_replace('#[^a-z0-9]#i', '', $_COOKIE['pass']);
	$log_id = $_SESSION['userid'];
	$log_username = $_SESSION['username'];
	$log_password = $_SESSION['password'];

	// Verify the user
	$user_ok = evalLoggedUser($db_connect,$log_id,$log_username,$log_password);

	if($user_ok == true){
		// Update the lastlogin field to the current date and time
		$sql = "UPDATE users SET lastlogin=now() WHERE id='$log_id' LIMIT 1";
		$query = mysqli_query($db_connect, $sql);
	}
}
?>