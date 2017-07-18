<?php
//verify the user
include_once('php_includes/check_login_status.php');

//get random usernames and avatars from database
$sql = "SELECT username, avatar FROM users ORDER BY RAND() LIMIT 32";
$query = mysqli_query($db_connect, $sql);
$userlist = "";
//create list of users to display on front page.
while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)){
	$u = $row['username'];
	$avatar = $row['avatar'];
	$profile_pic = 'user/' . $u . '/' . $avatar;
	$userlist .= '<ul class="pull-left unstyled">
					<li>
					<a href = "user.php?u='.$u.'" title="' . $u .'">
					<img src ="' . $profile_pic . '" alt="'.$u.'" style="width:50px; height:50px; margin:1px;" />
					</a><br /><small>
					'.$u.'
					</small>
					</li>
				</ul>';
}

/**
 * Count the total number of users
 */
$sql = "SELECT COUNT(id) FROM users WHERE activated = '1'";
$query = mysqli_query($db_connect, $sql);
$row = mysqli_fetch_row($query);
$usercount = $row[0];
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">

	<title>Social Campus</title>

	<!-- styles -->
	<link href="assets/css/bootstrap.min.css" rel="stylesheet">
	<link href="assets/css/bootstrap-responsive.min.css" rel="stylesheet">

	<style type="text/css">
		html {
			background: url(assets/img/background_park.jpg) no-repeat center center fixed;
			-webkit-background-size: cover;
			-moz-background-size: cover;
			-o-background-size: cover;
			background-size: cover;
		}
		body {padding-top: 60px; padding-bottom: 40px; background: transparent;}
		#center{text-align: center; margin: 0 auto;}
		.hero-unit{background: rgba(238, 238, 238, 0.82);}

		@media (max-width: 980px) {
		/* Enable use of floated navbar text */
			.navbar-text.pull-right {
				float: none;
				padding-left: 5px;
				padding-right: 5px;
			}
		}
	</style>

	<!-- Scripts -->
	<script src="http://code.jquery.com/jquery.min.js"></script>
	<script src="assets/js/bootstrap.min.js"></script>
	<script src="assets/js/main.js"></script>
	<script src="assets/js/ajax.js"></script>

	<script>
		/**
		 * Asynchronous login script.
		 * Checks users id and password, verifies user, and redirects them to the users profile page.
		 * @param x
		 */

		//empty selected element
		function emptyElement(x){
			_(x).innerHTML = "";
		}

		//log the user in
		function login(){
			var e = _("email").value;
			var p = _("password").value;

			if(e == "" || p == ""){
				_("status").innerHTML = "Fill out all of the form data";
			} else {
				_("loginbtn").style.display = "none";
				_("status").innerHTML = 'please wait ...';
				var ajax = ajaxObj("POST", "login.php");

				//ajax.onreadystatechange function to verify login without reloadin the page
				ajax.onreadystatechange = function() {
					if(ajaxReturn(ajax) == true) {
						//if login fails, force retry
						if(ajax.responseText == "login_failed"){
							_("status").innerHTML = "Login unsuccessful, please try again.";
							_("loginbtn").style.display = "block";
						} else {
							//if login is successful, send user to their profile page.
							window.location = "user.php?u="+ajax.responseText;
						}
					}
				}
				//send ajax data to the server script
				ajax.send("e="+e+"&p="+p);
			}
		}
	</script>
</head>

<body>
	<!-- display the header -->
	<?php include_once('assets/header_template.php'); ?>
	<div class="container">
		<div class="row">
			<div class="hero-unit span6">
				<h1>Social Campus</h1>
				<!-- Create the carousel for the images on the front page -->
			    <div id="myCarousel" class="carousel slide" data-interval="2000">
				    <ol class="carousel-indicators">
					    <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
					    <li data-target="#myCarousel" data-slide-to="1"></li>
					    <li data-target="#myCarousel" data-slide-to="2"></li>
					    <li data-target="#myCarousel" data-slide-to="3"></li>
					    <li data-target="#myCarousel" data-slide-to="4"></li>
				    </ol>
				    <!-- Carousel items -->
				    <div class="carousel-inner">
				    <div class="active item"><img src="assets/img/frats.jpg" alt="Fraternity and Sorority Life" /></div>
				    <div class="item"><img src="assets/img/honorSocieties.png" alt="Honor Societies" /></div>
				    <div class="item"><img src="assets/img/party.jpg" alt="Parties" /></div>
				    <div class="item"><img src="assets/img/sports.jpg" alt="Sports" /></div>
				    <div class="item"><img src="assets/img/studying.jpg" alt="Studying" /></div>
				    </div>
				    <!-- Carousel nav -->
				    <a class="carousel-control left" href="#myCarousel" data-slide="prev">&lsaquo;</a>
				    <a class="carousel-control right" href="#myCarousel" data-slide="next">&rsaquo;</a>
			    </div>
			</div>
			<div class="hero-unit span3">
				<div class="row">
					<div class="span4">
						<!-- Login form -->
						<form class="form-inline" id="loginform" action="login.php" method="post" onsubmit="return false;">
							<input type="text" class="input-small" id="email" onfocus="emptyElement('status')"
								   maxlength="88">
							<input type="password" class="input-small" id="password" onfocus="emptyElement('status')"
								   maxlength="100">
							<button class="btn btn-small" id="loginbtn" onclick="login()">Sign In</button>
							<p id="status"></p>
							<a href="forgot_pass.php"><small>Forgot Your Password?</small></a>
						</form>
					</div>
				</div>
				<div class="row">
					<div class="span3 well-small">
						<?php echo '<small>Total Current Users: ' . $usercount . '</small><br />'; ?>
						<?php echo $userlist; ?>
					</div>
				</div>
			</div>
		</div>
	<!-- create footer -->
	<?php include 'assets/footer_template.php'; ?>
</body>
</html>
