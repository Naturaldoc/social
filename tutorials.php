<?php
include 'php_includes/tutorial_functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Tutorials</title>

	<link rel="stylesheet" href="assets/css/bootstrap.min.css" />

	<style>
		body{margin-top: 60px;}
		#center{margin: 0 auto; text-align: center;}
	</style>

	<script src="http://code.jquery.com/jquery.min.js"></script>
	<script src="assets/js/bootstrap.min.js"></script>
	<script src="assets/js/main.js"></script>
	<script src="assets/js/ajax.js"></script>

</head>
<body>
<?php include "assets/header_template.php"; ?>

<div class="container">
	<?php //getList(); ?>
	<?php getBadgeCats(); ?>
	<hr />
	<textarea class="well">
		<?php
			foreach ($ka_rtn as $key => $value) {
				# code...
				echo $ka_rtn[$key]->title . '<br />';
			}
		?>
	</textarea>
</div>

<?php include "assets/footer_template.php"; ?>
</body>
</html>