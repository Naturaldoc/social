<?php
/**
 * verify user is logged in
 */
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

// Allow member to edit profile pic, but not other members.
if($u == $log_username && $user_ok == true){
	$isOwner = "yes";
	$profile_pic_btn = '<a href="#" onclick="return false;" onmousedown="toggleElement(\'avatar_form\')">Edit Photo</a>';
	$avatar_form  = '<form id="avatar_form" enctype="multipart/form-data" method="post" action="php_parsers/photo_system.php">';
	$avatar_form .=   '<h4>Change your avatar</h4>';
	$avatar_form .=   '<input type="file" name="avatar" required>';
	$avatar_form .=   '<p><input type="submit" value="Upload"></p>';
	$avatar_form .= '</form>';
}else{
	header('Location: user.php?u='.$u);
}

// Fetch the user row from the query above
while ($row = mysqli_fetch_array($user_query, MYSQLI_ASSOC)) {
	$profile_id = $row["id"];
	$email = $row['email'];
	$gender = $row["gender"];
	$s = $row['schoolname'];
	$m = $row['major'];
	$date = $row['graddate'];
	$i = $row['interests'];
	$w = $row['website'];
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

$profile_pic = '<img src="user/'.$u.'/'.$avatar.'" alt="'.$u.'">';

if($avatar == NULL){
	$profile_pic = '<img src="assets/img/avatardefault.jpg" alt="'.$u.'">';
}
?>
<?php
/**
 * create friends list and display thumbnail images linking to friend's profile
 */
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
	$sql = "SELECT user1 FROM friends WHERE user2='$u' AND accepted='1' ORDER BY RAND() LIMIT $max";
	$query = mysqli_query($db_connect, $sql);

	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		array_push($all_friends, $row["user1"]);
	}

	$sql = "SELECT user2 FROM friends WHERE user1='$u' AND accepted='1' ORDER BY RAND() LIMIT $max";
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
/**
 * create profile image
 */
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

	<title><?php echo $u; ?> | Edit Profile</title>

	<link rel="icon" href="favicon.ico" type="image/x-icon" />
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
		#center{
			text-align:center;
			margin: 0 auto;
		}
		div#profile_pic_box{
			float:right;
			border:#ddd 1px solid;
			width:200px;
			height:200px;
			margin:20px 30px 0px 0px;
			overflow-y:hidden;
		}
		div#profile_pic_box > img{
			z-index:2000;
			width:200px;
		}
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
		div#photo_showcase{float:right;
			background:url(style/photo_showcase_bg.jpg) no-repeat;
			width:136px;
			height:127px;
			margin:20px 30px 0px 0px;
			cursor:pointer;
		}
		div#photo_showcase > img{
			width:74px;
			height:74px;
			margin:37px 0px 0px 9px;

		}
		img.friendpics{
			border:#000 1px solid;
			width:50px;
			height:50px;
			margin:1px;
		}
	</style>

	<script src="http://code.jquery.com/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/ajax.js"></script>
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
			<h1><?php echo $u . '\'s Profile'; ?></h1>
			<hr />
			<div class="hero-unit span4">
				<form class="form-horizontal" action="profileEditor.php?u=<?php echo $u; ?>" method="post">
					<div class="control-group">
						<label>Username</label>
						<input type="text" name="user" id="user" value="<?php echo $u; ?>" disabled>
					</div>
					<div class="control-group">
						<label>Email</label>
						<input type="text" name="email" id="email" value="<?php echo $email; ?>">
					</div>
					<div class="control-group">
						<label>Gender</label>
						<select class="span2" name="gender" id="gender">
							<option id="gender" value="m">Male</option>
							<option id="gender" value="f">Female</option>
						</select>
					</div>
					<div class="control-group">
						<label>Website</label>
						<input type="text" name="website" id="website" value="<?php echo $w; ?>">
					</div>
					<div class="control-group">
						<label>School</label>
						<input type="text" name="school" id="school" value="<?php echo $s; ?>">
					</div>
					<div class="control-group">
						<label>Major</label>
						<input type="text" name="major" id="major" value="<?php echo $m; ?>">
					</div>
					<div class="control-group">
						<label>Graduation Date</label>
						<input type="text" name="graddate" id="graddate" value="<?php echo $date; ?>">
					</div>
					<div class="control-group">
						<label>Interests</label>
						<textarea name="interests" id="interests"><?php echo $i; ?></textarea>
					</div>
					<div class-"form-actions">
						<button type="submit" class="btn btn-primary">Edit Profile</button>
						<button type="button" class="btn">Cancel</button>
					</div>
				</form>
			</div>
			<div class="span4">
				<h3><?php echo $u.","; ?></h3>
				<?php echo $friendsHTML; ?>
				<br />
				<?php echo "You have ".$friend_count." friends"; ?>
			</div>
		</div>
	</div>
	<?php include_once("assets/footer_template.php"); ?>
</body>
</html>