<?php
include_once("php_includes/check_login_status.php");

// Initialize any variables that the page might echo
$u = "";
$sex = "Male";
$userlevel = "";
$profile_pic = "";
$profile_pic_btn = "";
$avatar_form = "";
$country = "";
$joindate = "";
$lastsession = "";
$s = "";
$m = "";
$date = "";
$i = "";

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

// Check to see if the viewer is the account owner
$isOwner = "no";

if($u == $log_username && $user_ok == true){
	$isOwner = "yes";
	$profile_pic_btn = '<a href="#" onclick="return false;" onmousedown="toggleElement(\'avatar_form\')">Edit Photo</a>';
	$avatar_form  = '<form id="avatar_form" enctype="multipart/form-data" method="post" action="php_parsers/photo_system.php">';
	$avatar_form .=   '<h4>Change your avatar</h4>';
	$avatar_form .=   '<input type="file" name="avatar" required>';
	$avatar_form .=   '<p><input type="submit" value="Upload"></p>';
	$avatar_form .= '</form>';
}

// Fetch the user row from the query above
while ($row = mysqli_fetch_array($user_query, MYSQLI_ASSOC)) {
	$profile_id = $row["id"];
	$gender = $row["gender"];
	$s = $row['schoolname'];
	$m = $row['major'];
	$date = $row['graddate'];
	$i = $row['interests'];
	$country = $row["country"];
	$userlevel = $row["userlevel"];
	$avatar = $row["avatar"];
	$signup = $row["signup"];
	$lastlogin = $row["lastlogin"];
	$joindate = strftime("%b %d, %Y", strtotime($signup));
	$lastsession = strftime("%b %d, %Y", strtotime($lastlogin));
}

if($gender == "f"){
	$sex = "Female";
}

$profile_pic = '<img src="user/'.$u.'/' . $avatar . '" alt="' . $u . '">';

if($avatar == NULL){
	$profile_pic = '<img src="assets/img/avatardefault.jpg" alt="' . $u . '">';
}
?>
<?php
$isFriend = false;
$ownerBlockViewer = false;
$viewerBlockOwner = false;

if($u != $log_username && $user_ok == true){
	$friend_check = "SELECT id FROM friends WHERE user1='$log_username' AND user2='$u' AND accepted='1' OR user1='$u' AND user2='$log_username' AND accepted='1' LIMIT 1";

	if(mysqli_num_rows(mysqli_query($db_connect, $friend_check)) > 0){
		$isFriend = true;
	}
	
	$block_check1 = "SELECT id FROM blockedusers WHERE blocker='$u' AND blockee='$log_username' LIMIT 1";
	
	if(mysqli_num_rows(mysqli_query($db_connect, $block_check1)) > 0){
		$ownerBlockViewer = true;
	}
	
	$block_check2 = "SELECT id FROM blockedusers WHERE blocker='$log_username' AND blockee='$u' LIMIT 1";
	
	if(mysqli_num_rows(mysqli_query($db_connect, $block_check2)) > 0){
		$viewerBlockOwner = true;
	}
}
?>
<?php 
$friend_button = '<button disabled>Request As Friend</button>';
$block_button = '<button disabled>Block User</button>';

// LOGIC FOR FRIEND BUTTON
if($isFriend == true){
	$friend_button = '<button onclick="friendToggle(\'unfriend\',\''.$u.'\',\'friendBtn\')">Unfriend</button>';
} else if($user_ok == true && $u != $log_username && $ownerBlockViewer == false){
	$friend_button = '<button onclick="friendToggle(\'friend\',\''.$u.'\',\'friendBtn\')">Request As Friend</button>';
}

// LOGIC FOR BLOCK BUTTON
if($viewerBlockOwner == true){
	$block_button = '<button onclick="blockToggle(\'unblock\',\''.$u.'\',\'blockBtn\')">Unblock User</button>';
} else if($user_ok == true && $u != $log_username){
	$block_button = '<button onclick="blockToggle(\'block\',\''.$u.'\',\'blockBtn\')">Block User</button>';
}
?>
<?php
$friendsHTML = '';
$friends_view_all_link = '';
$sql = "SELECT COUNT(id) FROM friends WHERE user1='$u' AND accepted='1' OR user2='$u' AND accepted='1'";
$query = mysqli_query($db_connect, $sql);
$query_count = mysqli_fetch_row($query);
$friend_count = $query_count[0];

if($friend_count < 1){
	$friendsHTML = $u." has no friends yet";
} else {
	$max = 18;
	$all_friends = array();
	$sql = "SELECT user1 FROM friends WHERE user2='$u' AND accepted='1' ORDER BY RAND() LIMIT ".$max;
	$query = mysqli_query($db_connect, $sql);
	
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		array_push($all_friends, $row["user1"]);
	}
	
	$sql = "SELECT user2 FROM friends WHERE user1='$u' AND accepted='1' ORDER BY RAND() LIMIT " .$max;
	$query = mysqli_query($db_connect, $sql);
	
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		array_push($all_friends, $row["user2"]);
	}
	
	$friendArrayCount = count($all_friends);
	
	if($friendArrayCount > $max){
		array_splice($all_friends, $max);
	}
	
	if($friend_count > $max){
		$friends_view_all_link = '<a href="view_friends.php?u='.$u.'">view all</a>';
	}
	
	$orLogic = '';
	
	foreach($all_friends as $key => $user){
		$orLogic .= "username='$user' OR ";
	}
	
	$orLogic = chop($orLogic, "OR ");
	$sql = "SELECT username, avatar FROM users WHERE $orLogic";
	$query = mysqli_query($db_connect, $sql);
	
	while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		$friend_username = $row["username"];
		$friend_avatar = $row["avatar"];
		
		if($friend_avatar != ""){
			$friend_pic = 'user/'.$friend_username.'/'.$friend_avatar.'';
		} else {
			$friend_pic = 'assets/img/avatardefault.jpg';
		}
		
		$friendsHTML .= '<a href="user.php?u='.$friend_username.'"><img class="pull-left friendpics" src="'.$friend_pic.'" alt="'.$friend_username.'" title="'.$friend_username.'"></a>';
	}
}
?>
<?php 
$coverpic = "";
$sql = "SELECT filename FROM photos WHERE user='$u' ORDER BY RAND() LIMIT 1";
$query = mysqli_query($db_connect, $sql);

if(mysqli_num_rows($query) > 0){
	$row = mysqli_fetch_row($query);
	$filename = $row[0];
	$coverpic = '<img src="user/'.$u.'/'.$filename.'" alt="pic">';
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">

	<title><?php echo $u; ?></title>

	<link rel="stylesheet" href="assets/css/bootstrap.min.css" />
	<link rel="stylesheet" href="assets/css/bootstrap-responsive.min.css" />

	<style type="text/css">
		html {
			background: url(assets/img/background_pond.jpg) no-repeat center center fixed ;
			-webkit-background-size: cover;
			-moz-background-size: cover;
			-o-background-size: cover;
			background-size: cover;
		}
		body{margin-top:40px; background: rgba(255,255,255,0.9);}
		#center{text-align:center; margin: 0 auto;}
		.hero-unit{font-size: small;}
		div#profile_pic_box{float:right; border:#ddd 1px solid; width:200px; height:200px; margin:20px 30px 0px 0px;
			overflow-y:hidden;}
		div#profile_pic_box > img{z-index:2000; width:200px;}
		div#profile_pic_box > a {
			display: none;
			position:absolute; 
			margin:140px 0px 0px 120px;
			z-index:4000;
			background:#D8F08E;
			border:#81A332 1px solid;
			border-radius:3px;
			padding:5px;
			font-size:12px;
			text-decoration:none;
			color:#60750B;
		}
		div#profile_pic_box > form{
			display:none;
			position:absolute; 
			z-index:3000;
			padding:10px;
			opacity:.8;
			background:#F0FEC2;
			width:180px;
			height:180px;
		}
		div#profile_pic_box:hover a {
			display: block;
		}
		/*div#photo_showcase{float:right; background:url(assets/img/photo_showcase_bg.png) no-repeat;
			width:136px;
			height:127px;	margin:20px 30px 0px 0px; cursor:pointer;}*/
		div#photo_showcase > img{width:74px; height:74px; margin:37px 0px 0px 9px;}
		img.friendpics{border:#000 1px solid; width:50px; height:50px; margin:1px;}
		img.stat_img{
			width: 50px;
			height: 50px;
		}
		#sidebar{padding-top: 10px;}
	</style>
<!--
	<style type="text/css">
		textarea#statustext{width:982px; height:80px; padding:8px; border:#999 1px solid; font-size:16px;}
		div.status_boxes{padding:12px; line-height:1.5em;}
		div.status_boxes > div{padding:8px; border:#99C20C 1px solid; background: #F4FDDF;}
		div.status_boxes > div > b{font-size:12px;}
		div.status_boxes > button{padding:5px; font-size:12px;}
		textarea.replytext{width:98%; height:40px; padding:1%; border:#999 1px solid;}
		div.reply_boxes{padding:12px; border:#999 1px solid; background:#F5F5F5;}
		div.reply_boxes > div > b{font-size:12px;}
	</style>
-->
	<script src="http://code.jquery.com/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
	<script src="assets/js/main.js"></script>
    <script src="assets/js/ajax.js"></script>

	<script type="text/javascript">
		function friendToggle(type,user,elem){
			var conf = confirm("Press OK to confirm the '"+type+"' action for user <?php echo $u; ?>.");

			if(conf != true){
				return false;
			}

			_(elem).innerHTML = 'please wait ...';
			var ajax = ajaxObj("POST", "php_parsers/friend_system.php");

			ajax.onreadystatechange = function() {
				if(ajaxReturn(ajax) == true) {
					if(ajax.responseText == "friend_request_sent"){
						_(elem).innerHTML = 'OK Friend Request Sent';
					} else if(ajax.responseText == "unfriend_ok"){
						_(elem).innerHTML = '<button onclick="friendToggle(\'friend\',\'<?php echo $u; ?>\',\'friendBtn\')">Request As Friend</button>';
					} else {
						alert(ajax.responseText);
						_(elem).innerHTML = 'Try again later';
					}
				}
			}

			ajax.send("type="+type+"&user="+user);
		}
		
		function blockToggle(type,blockee,elem){
			var conf = confirm("Press OK to confirm the '"+type+"' action on user <?php echo $u; ?>.");
			
			if(conf != true){
				return false;
			}
			
			var elem = document.getElementById(elem);
			elem.innerHTML = 'please wait ...';
			var ajax = ajaxObj("POST", "php_parsers/block_system.php");
			
			ajax.onreadystatechange = function() {
				if(ajaxReturn(ajax) == true) {
					if(ajax.responseText == "blocked_ok"){
						elem.innerHTML = '<button onclick="blockToggle(\'unblock\',\'<?php echo $u; ?>\',\'blockBtn\')">Unblock User</button>';
					} else if(ajax.responseText == "unblocked_ok"){
						elem.innerHTML = '<button onclick="blockToggle(\'block\',\'<?php echo $u; ?>\',\'blockBtn\')">Block User</button>';
					} else {
						alert(ajax.responseText);
						elem.innerHTML = 'Try again later';
					}
				}
			}

			ajax.send("type="+type+"&blockee="+blockee);
		}
	</script>
</head>

<body>
	<?php include_once("assets/header_template.php"); ?>

	<div id="container">
	    <div class="row-fluid">
            <div class="span2 offset2">
                <div id="profile_pic_box" >
                    <?php echo $profile_pic_btn; ?>
                    <?php echo $avatar_form; ?>
                    <?php echo $profile_pic; ?>
                </div>
            </div>
            
            <div class="span4">
                <h1><?php echo $u . '\'s Profile'; ?></h1>
				<br />
				<!--CHECK TO ENSURE PROFILE IS OWNER -->
				<a href="edit_profile.php?u=<?php echo $u; ?>">Edit Profile</a>
                <hr />
                <table class="table">
                	<tbody>
                		<tr>
                			<th>Name:</th>
                			<td><?php echo $u; ?></td>
                		</tr>
                		<tr>
                			<th>Gender:</th>
                			<td><?php echo $sex; ?></td>
                		</tr>
                		<tr>
                			<th>School:</th>
                			<td><?php echo $s; ?></td>
                		</tr>
                		<tr>
                			<th>Major:</th>
                			<td><?php echo $m; ?></td>
                		</tr>
                		<tr>
                			<th>Graduation Date:</th>
                			<td><?php echo $date; ?></td>
                		</tr>
                		<tr>
                			<th>Interests:</th>
                			<td><?php echo $i; ?></td>
                		</tr>
                	</tbody>
                </table>
                
                <p class="pull-right"><span id="friendBtn"><?php echo $friend_button; ?></span><span id="blockBtn"><?php echo $block_button; ?></span></p>
                <a href="#"><div onclick="window.location = 'photos.php?u=<?php echo $u; ?>';">Check My Photos!</div></a>
            </div>

            <div class="span4" id="sidebar">
                <?php echo $friendsHTML; ?>
                <br />
                <?php echo $u." has ".$friend_count." friends"; ?>
                
            </div>
        </div><!-- /row -->
        <hr />
        <div class="row-fluid">
            <div class="span6 offset3">
				<div class="hero-unit">
                	<?php include_once("template_status.php"); ?>
				</div>
            </div>
        </div>
	</div><!-- /container -->

	<?php include_once("assets/footer_template.php"); ?>
</body>
</html>