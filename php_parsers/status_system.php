<?php
include_once("../php_includes/check_login_status.php");

if($user_ok != true || $log_username == "") {
	exit();
}
?>
<?php
if (isset($_POST['action']) && $_POST['action'] == "status_post"){
	// Make sure post data is not empty
	if(strlen($_POST['data']) < 1){
		mysqli_close($db_connect);
		echo "data_empty";
		exit();
	}

	// Make sure type is either a or c
	if($_POST['type'] != "a" && $_POST['type'] != "c"){
		mysqli_close($db_connect);
		echo "Unknown type: " . $_POST['type'];
		exit();
	}
	
	// Clean all of the $_POST vars that will interact with the database
	$type = preg_replace('#[^a-z]#', '', $_POST['type']);
	$account_name = preg_replace('#[^a-z0-9]#i', '', $_POST['user']);
	$data = htmlentities($_POST['data']);
	$data = mysqli_real_escape_string($db_connect, $data);
	
	// Make sure account name exists (the profile being posted on)
	$sql = "SELECT COUNT(id) FROM users WHERE username='$account_name' AND activated='1' LIMIT 1";
	$query = mysqli_query($db_connect, $sql);
	$row = mysqli_fetch_row($query);

	if($row[0] < 1){
		mysqli_close($db_connect);
		echo "$account_no_exist";
		exit();
	}

	// Insert the status post into the database now
	$sql = "INSERT INTO status(account_name, author, type, data, postdate) VALUES('$account_name','$log_username','$type','$data',now())";
	$query = mysqli_query($db_connect, $sql);
	$id = mysqli_insert_id($db_connect);
	mysqli_query($db_connect, "UPDATE status SET osid='$id' WHERE id='$id' LIMIT 1");

	// Count posts of type "a" for the person posting and evaluate the count
	$sql = "SELECT COUNT(id) FROM status WHERE author='$log_username' AND type='a'";
	$query = mysqli_query($db_connect, $sql); 
	$row = mysqli_fetch_row($query);

	///////////////////////////////////////////////////////////////////////////////
	// If they have 10 or more posts of type a                                   //
	// Delete their oldest post if you want a system that auto flushes the oldest//
	// (you can auto flush for post types c and b if you wish to also)           //
	///////////////////////////////////////////////////////////////////////////////
	if ($row[0] > 9) { 
		$sql = "SELECT id FROM status WHERE author='$log_username' AND type='a' ORDER BY id ASC LIMIT 1";
		$query = mysqli_query($db_connect, $sql); 
		$row = mysqli_fetch_row($query);
		$oldest = $row[0];
		mysqli_query($db_connect, "DELETE FROM status WHERE osid='$oldest'");
	}
	
	// Insert notifications to all friends of the post author
	// COULD BE USED FOR ALL TYPES OF NOTIFICATIONS. NEED TO IMPORT TO PROPER AREAS OF CODE.
	$friends = array();
	$query = mysqli_query($db_connect, "SELECT user1 FROM friends WHERE user2='$log_username' AND accepted='1'");

	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) { array_push($friends, $row["user1"]); }
	
	$query = mysqli_query($db_connect, "SELECT user2 FROM friends WHERE user1='$log_username' AND accepted='1'");

	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) { array_push($friends, $row["user2"]); }
	
	for($i = 0; $i < count($friends); $i++){
		$friend = $friends[$i];
		$app = "Status Post";
		$note = $log_username.' posted on: <br /><a href="user.php?u='.$account_name.'#status_'.$id.'">'.$account_name.'&#39;s Profile</a>';
		mysqli_query($db_connect, "INSERT INTO notifications(username, initiator, app, note, date_time) VALUES('$friend','$log_username','$app','$note',now())");	
	}
	
	mysqli_close($db_connect);
	echo "post_ok|$id";
	exit();
}
?>
<?php 
//action=status_reply & osid=" + osid + " &user= " + user + "&data= " + data
if (isset($_POST['action']) && $_POST['action'] == "status_reply"){
	// Make sure data is not empty
	if(strlen($_POST['data']) < 1){
		mysqli_close($db_connect);
		echo "data_empty";
		exit();
	}

	// Clean the posted variables
	$osid = preg_replace('#[^0-9]#', '', $_POST['sid']);
	$account_name = preg_replace('#[^a-z0-9]#i', '', $_POST['user']);
	$data = htmlentities($_POST['data']);
	$data = mysqli_real_escape_string($db_connect, $data);

	// Make sure account name exists (the profile being posted on)
	$sql = "SELECT COUNT(id) FROM users WHERE username='$account_name' AND activated='1' LIMIT 1";
	$query = mysqli_query($db_connect, $sql);
	$row = mysqli_fetch_row($query);

	if($row[0] < 1){
		mysqli_close($db_connect);
		echo "$account_no_exist";
		exit();
	}

	// Insert the status reply post into the database now
	$sql = "INSERT INTO status(osid, account_name, author, type, data, postdate) VALUES('$osid','$account_name','$log_username','b','$data',now())";
	$query = mysqli_query($db_connect, $sql);
	$id = mysqli_insert_id($db_connect);

	// Insert notifications for everybody in the conversation except this author
	$sql = "SELECT author FROM status WHERE osid='$osid' AND author!='$log_username' GROUP BY author";
	$query = mysqli_query($db_connect, $sql);

	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		$participant = $row["author"];
		$app = "Status Reply";
		$note = $log_username.' commented here:<br /><a href="user.php?u='.$account_name.'#status_'.$osid.'">Click here to view the conversation</a>';
		mysqli_query($db_connect, "INSERT INTO notifications(username, initiator, app, note, date_time) VALUES('$participant','$log_username','$app','$note',now())");
	}

	mysqli_close($db_connect);
	echo "reply_ok|$id";
	exit();
}
?>
<?php
if (isset($_POST['action']) && $_POST['action'] == "delete_status"){
	if(!isset($_POST['statusid']) || $_POST['statusid'] == ""){
		//echo 'ID: '. $_POST['statusid'] .' Action: '. $_POST['action'];
		mysqli_close($db_connect);
		echo "status id is missing";
		exit();
	}

	$statusid = preg_replace('#[^0-9]#', '', $_POST['statusid']);

	// Check to make sure this logged in user actually owns that comment
	$query = mysqli_query($db_connect, "SELECT account_name, author FROM status WHERE id='$statusid' LIMIT 1");

	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		$account_name = $row["account_name"];
		$author = $row["author"];
	}

	if ($author == $log_username || $account_name == $log_username) {
		mysqli_query($db_connect, "DELETE FROM status WHERE osid='$statusid'") or die(mysqli_errno());
		mysqli_close($db_connect);
		echo "delete_ok";
		exit();
	}
}
?>
<?php
if (isset($_POST['action']) && $_POST['action'] == "delete_reply"){
	if(!isset($_POST['replyid']) || $_POST['replyid'] == ""){
		mysqli_close($db_connect);
		exit();
	}

	$replyid = preg_replace('#[^0-9]#', '', $_POST['replyid']);

	// Check to make sure the person deleting this reply is either the account owner or the person who wrote it
	$query = mysqli_query($db_connect, "SELECT osid, account_name, author FROM status WHERE id='$replyid' LIMIT 1");

	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		$osid = $row["osid"];
		$account_name = $row["account_name"];
		$author = $row["author"];
	}

	if ($author == $log_username || $account_name == $log_username) {
		mysqli_query($db_connect, "DELETE FROM status WHERE id='".$replyid."'");
		mysqli_close($db_connect);
		echo "delete_ok";
		exit();
	}
}
?>