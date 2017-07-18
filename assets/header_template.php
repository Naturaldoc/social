<?php
include_once('php_includes/check_login_status.php');

// It is important for any file that includes this file, to have
// check_login_status.php included at its very top.
$envelope = '<i class="icon-envelope icon-white"></i>';
$loginLink = '<li><a href="login.php">Log In</a></li> <li><a href="signup.php">Sign Up</a></li>';
if($user_ok == true) {
	$sql = "SELECT notescheck FROM users WHERE username='$log_username' LIMIT 1";
	$query = mysqli_query($db_connect, $sql);
	$row = mysqli_fetch_row($query);
	$notescheck = $row[0];
	$sql = "SELECT id FROM notifications WHERE username='$log_username' AND date_time > '$notescheck' LIMIT 1";
	$query = mysqli_query($db_connect, $sql);
	$numrows = mysqli_num_rows($query);
	if ($numrows == 0) {
		$envelope = '<i class="icon-envelope"></i>';
	} else {
		$envelope = '<i class="icon-envelope icon-white"></i>';
	}
	
	$loginLink = '<li><a href="user.php?u='.$log_username.'">'.$log_username.'</a></li><li><a href="logout.php">Log Out</a></li>';
}
?>
<div class="navbar navbar-inverse navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container-fluid">
			<button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="brand" href="index.php">Social Campus</a>
			<div class="nav-collapse collapse">
				<ul class="nav">
				    <li class="divider-vertical"></li>
					<li><a href="index.php"><i class="icon-home icon-white"></i></a></li>
					<li class="dropdown">
						<a class = "dropdown-toggle" data-toggle = "dropdown" href= "#">
							EduReach, Inc<b class="caret"></b>
						</a>
						<ul class="dropdown-menu" aria-labelledby="dLabel" role="menu">
							<li role="presentation">
								<a href="../about.php" tabindex="-1" role="menuitem">About EduReach</a>
							</li>
							<li role="presentation">
								<a href="../tos.php" tabindex="-1" role="menuitem">Terms of Use</a>
							</li>
							<li role="presentation">
								<a href="../privacy.php" tabindex="-1" role="menuitem">Our Privacy Policy</a>
							</li>
							<li role="presentation">
								<a href="../faq.php" tabindex="-1" role="menuitem">How It Works</a>
							</li>
						</ul>
					</li>
					<li>
						<a href="../ka.php">Learn Something New</a>
					</li>
				</ul>
				<ul class="nav pull-right">
					<li><a href="../notifications.php"><?php echo $envelope; ?></a></li>
					<?php echo $loginLink; ?>
				</ul>
			</div><!--/.nav-collapse -->
		</div><!-- /.container -->
	</div><!-- /.navbar-inner -->
</div><!-- /.navbar -->