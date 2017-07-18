<?php
if(isset($_POST['email'])){
	$e_to = "admin@mousetech.org";
	$e_subj = "Social Campus Contact: ";

	function died($error){
		echo "Opps! Something has gone awry and we were unable to send your message.<br />";
		echo "Please go back and correct these items:<br />";
		echo "<ul>";
		echo "<li>$error</li>";
		echo "</ul>";
		die();
	}

	//validate data
	if(!isset($_POST['name']) || !isset($_POST['email']) || !isset($_POST['subject']) || !isset($_POST['msg'])){
		died('Oops! Something has gone awry and we were unable to send you message.');
	}

	$e = $_POST['email'];
	$m = $_POST['msg'];
	$n = preg_replace('#[^a-z0-9]#i', '', $_POST['name']);
	$s = preg_replace('#[^a-z0-9]#i', '', $_POST['subject']);

	$error_message = '';
	$email_exp = '/^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/';
	$str_exp = '/^[^a-za-z .-]+$/';

	if(!preg_match($email_exp, $e)){
		$error_message .= 'The Email Address you entered does not appear to be valid.';
	}
	if(strlen($m) < 1){
		$error_message = "Please be sure to type us a message.";
	}
	if(strlen($error_message) > 0){
		died($error_message);
	}
	$email_message = "The following message was sent from the Social Campus Website:\n\n";

	function sanitize($string){
		$bad = array('content-type', 'bcc:', 'to:', 'cc:', 'href');
		return str_replace($bad, '', $string);
	}

	/**
	 * create email message
	 */
	$email_message .= "Name: " . sanitize($n) . "\n";
	$email_message .= "Email: " . sanitize($e) . "\n";
	$email_message .= "Subject: " . sanitize($s) . "\n";
	$email_message .= "MESSAGE:\n\n" . sanitize($m) . "\n";

	//create email headers
	$headers = 'From: ' . $e . '\r\n'.
		'Reply-To: ' . $e . '\r\n'.
		'X-Mailer: PHP/' . phpversion();
	//send email
	@mail($e_to, $e_subj, $email_message, $headers);

	echo 'Thank you for contacting us. We will be in touch with you shortly.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Social Campus | Contact Us</title>

	<script src="http://code.jquery.com/jquery.min.js"></script>
	<script src="assets/js/bootstrap.min.js"></script>

	<link rel="stylesheet" href="assets/css/bootstrap.min.css" />
	<style>
		html {
			background: url(assets/img/background_park.jpg) no-repeat center center fixed;
			-webkit-background-size: cover;
			-moz-background-size: cover;
			-o-background-size: cover;
			background-size: cover;
		}
		body{
			margin-top: 60px;
			background: transparent;
		}
		#center{margin: 0 auto; text-align: center;}
	</style>
</head>
<body>
	<?php include 'assets/header_template.php'; ?>
	<div class="container">
		<div class="hero-unit">
			<h1>Contact Us</h1>
			<p>Please use the form to send us a message, comment, complaint, or suggestion. We are open to what our
					users have to say and look forward to hearing from you.</p>
			<form class="form-horizontal" action="#" method="POST">
				<div class="control-group">
					<label class="control-label" for="name">Name</label>
					<div class="controls">
						<input type="text" name="name" id="name" placeholder="Name" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="email">Email</label>
					<div class="controls">
						<input type="text" name="email" id="email" placeholder="Email" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="subject">Subject</label>
					<div class="controls">
						<input type="text" name="subject" id="subject" placeholder="Subject" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="msg">Message</label>
					<div class="controls">
						<textarea name="msg" id="msg"></textarea>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button type="submit" class="btn">Send</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<?php include 'assets/footer_template.php'; ?>
</body>
</html>