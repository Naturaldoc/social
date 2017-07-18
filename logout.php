<?php
session_start();

// Set Session data to an empty array
$_SESSION = array();

// Expire their cookie files
if(isset($_COOKIE["id"]) && isset($_COOKIE["user"]) && isset($_COOKIE["pass"])) {
	setcookie("id", '', strtotime( '-5 days' ), '/');
	setcookie("user", '', strtotime( '-5 days' ), '/');
	setcookie("pass", '', strtotime( '-5 days' ), '/');
}

// Destroy the session variables
session_destroy();

// Double check to see if their sessions exists
if(isset($_SESSION['username'])){
	header("Location: message.php?msg=Error:_Logout_Failed");
} else {
	header("Location: index.php");
	exit();
} 
?>