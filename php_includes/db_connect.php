<?php
$db_connect = mysqli_connect('localhost', 'root', '', 'social');
//Eval Connection
if(mysqli_connect_errno()){
	echo mysqli_connect_error();
	exit();
}

?>