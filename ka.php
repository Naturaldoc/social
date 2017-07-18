<!DOCTYPE html>
<html lang="en">
<head>
	<title>Khan videos</title>
	<script src="http://code.jquery.com/jquery.min.js"></script>
	<script src="assets/js/jquery.prettyPhoto.js"></script>
	<script src="assets/js/bootstrap.min.js"></script>

	<link rel="stylesheet" href="assets/css/prettyPhoto.css" type="text/css" />
	<link rel="stylesheet" href="assets/css/bootstrap.min.css" />

	<style type="text/css">
		/*body{margin-top: 60px;}*/
		html {
			background: url(assets/img/background_park.jpg) no-repeat center center fixed;
			-webkit-background-size: cover;
			-moz-background-size: cover;
			-o-background-size: cover;
			background-size: cover;
		}
		body {padding-top: 60px; padding-bottom: 40px; background-color: rgba(255,255,255,0.5);}
		#center{text-align: center; margin: 0 auto;}
		#topics,#sub-topics {
			line-height: 91px;
			border: 0;
			border-radius: 0px;
		}
	</style>
</head>
<body>
	<?php include_once'assets/header_template.php'; ?>

	<div class="container">
		<h1>Learn something new</h1>
		<hr />

		<div class="hero-unit">
			<select id="topics">
				<option>Please select a Topic</option>
			</select>

			<select id="sub-topics">
				<option>Please select a Sub-Topic</option>
			</select>
		</div>

		<div id="videos"></div>
		<script>
			var topics = [];
			var subtopics = [];
			var videos = [];

			//Function to construct the topics
			function constructTopic(topics)
			{
				var output_str = "";
				//Add a default option
				output_str += "<option>Please select a Topic</option>";
				//Loop through topics and construct each option
				//and append it to our output string
				topics.forEach(function(topic){
					output_str += "<option id='" + topic.id + "' name='" + topic.id + "'>" + topic.title + "</option>";
				});
				//return the completed output string
				return output_str;
			}
			//Function to construct the subtopics
			function constructSubTopic(sub_topics)
			{
				var output_str = "";
				//Add a default sub-topic option
				output_str += "<option>Please select a Sub-Topic</option>";
				//Loop through each subtopic and construct a string
				//and append that to our output string
				sub_topics.forEach(function(sub_topic){
					output_str += "<option id='" + sub_topic.id + "' name='" + sub_topic.id + "'>" + sub_topic.title + "</option>";
				});
				//return the output string
				return output_str;
			}

			//Function that fetches the videos of the specified topic
			function fetchVideo(topic,callback) {
				//Fetch the youtube JSON from khan academy and call "callback" with the returned data
				$.getJSON("http://www.khanacademy.org/api/v1/topic/" + topic + "/videos",function(data){
					callback(data);
				});
			}

			//Function to fetch the topic data from khan
			function fetchTopic(topic,callback) {
				//Fetch the topic JSON from khan academy and for every element in the children array
				//and call "callback" for each entry
				$.getJSON("http://www.khanacademy.org/api/v1/topic/" + topic,function(data) {
					//Loop over each child entry in the returned data
					data["children"].forEach(function(child){
						callback(child);
					});
				});
			}

			//Event fired when a user selects a topic
			$("#topics").change(function(e){
				//The topic select control
				var selected_topic = $("#topics");
				//get the text of the first select control
				var selected_val = $("#topics :selected").attr("name");
				//Clear the sub-topics control
				$("#sub-topics").html("");
				//Clear the videos element
				$("#videos").html("");
				//Clear the subtopics array
				subtopics = [];
				//Call fetchTopic on the select control text
				fetchTopic(selected_val,function(result){
					//For each sub-topic append it to our subtopics array
					subtopics.push(result);
					//set the sub-topics html to the constructed options html
					$("#sub-topics").html(constructSubTopic(subtopics));
				});

			});

			//Event fired when a user selects a sub-topic
			$("#sub-topics").change(function(e){
				//The sub-topic select control text
				var selection = $("#sub-topics :selected").attr("name");
				//Call fetchVideo on the selected sub-topic text
				fetchVideo(selection,function(ret){
					//Clear the videos html
					$("#videos").html("");
					//If we get any videos
					if(ret.length > 0)
					{
						//Loop over each video entry in the current sub-topic
						ret.forEach(function(v){
							//Build the div container
							var div = $("<div>",{"class":"well"});
							//Build the link that prettyPhoto uses
							var link = $("<a>",{ rel: "prettyPhoto", href: v.url,"class": "pull-left"});
							//Build the thumbnail
							var thumbnail = $("<img>",{ src: v.image_url,width: 100,height: 75,"class":"img-rounded"});
							//Set the description
							var descrption = $("<p>").text("Description: " + v.description);
							//Set the duration
							var duration = $("<p>").html("<small>Duration: " + Math.floor(v.duration / 60) + " mins</small>");

							//Construct the DOM Tree for each Entry
							thumbnail.appendTo(link);
							link.prettyPhoto();
							link.appendTo(div);
							descrption.appendTo(div);
							duration.appendTo(div);
							//Finally append the div to the videos element
							div.appendTo("#videos");
						});
					}
					//If the sub-topic does not contain videos
					else
					{
						$("#videos").html("No videos");
					}
				});
			});

			window.onload = function() {
				fetchTopic("science",function(topic){
					//These do not contain youtube videos
					if(topic.id == "lebron-asks" || topic.id == "cs-exercises" || topic.id == "cosmology-and-astronomy" || topic.id == "organic-chemistry" || topic.id == "core-finance"
						|| topic.id == "microeconomics" || topic.id == "macroeconomics" || topic.id == "computer-science")
					{

					}
					else
					{
						topics.push(topic);
					}
					$("#topics").html(constructTopic(topics));
				});

			};
		</script>
	</div>
	<?php include_once'assets/footer_template.php'; ?>
</body>
</html>