<?php

$url = isset($_GET['url']) ? sanitizeURL($_GET['url']) : ''; // the youtube video ID

if(!empty($url)) {
	$info = getVideoInfo($url);
	$title = isset($info[0]) ? $info[0] : '';
	$thumbnail = isset($info[1]) ? $info[1] : '';
	$cleanTitle = isset($info[2]) ? htmlspecialchars($info[2]) : '';
	$duration = isset($info[3]) ? timeToInt($info[3]) : '';
}

if(!empty($title) && $duration <= 600) {
	$error = false;
	
	if(file_exists("audio/$title.mp3")) {
		$exists = true;
	} else {
		$exists = false;
	}

} else {
	$error = true; // video not found

	if(isset($duration) && $duration > 600) {
		$durationError = true;
	}
}

function getVideoInfo($url) {
	$response = shell_exec("youtube-dl --get-title --get-thumbnail --get-filename --get-duration -o '%(title)s' -- " . escapeshellarg($url));
	return explode("\n", $response);
}

function timeToInt($str) {
	$arr = array_reverse(explode(":", $str));
	$len = 0;
	for($i = 0; $i < count($arr); $i++) {
		$len += $arr[$i] * pow(60, $i);
	}
	return $len;
}

function sanitizeURL($url) {
	// found at http://stackoverflow.com/questions/13476060/validating-youtube-url-using-regex
	$rx = '~^(?:https?://)?' .										# Optional protocol
	'(?:www[.])?' .																# Optional sub-domain
	'(?:youtube[.]com/watch[?]v=|youtu[.]be/)' .	# Mandatory domain name (w/ query string in .com)
	'([^&]{11})~';																# Video id of 11 characters as capture group 1

	$has_match = preg_match($rx, $url, $matches);

	// if matching succeeded, $matches[1] would contain the video ID
	return (isset($matches[1])) ? 'https://www.youtube.com/watch?v=' . $matches[1] : '';
}

?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>YouTube Audio Downloader</title>
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css"/>
	<style>
	.panel-inverse {
		background-color: #444;
	}
	.img-responsive {
		display: inline;
		max-height: 400px;
		border-top: 20px solid #000;
		border-bottom: 20px solid #000;
	}
	.transition-slow {
		transition: width 15s linear;
		-webkit-transition: width 15s linear;
	}
	.transition-fast {
		transition: width 1s linear;
		-webkit-transition: width 1s linear;
	}
	.almost-full {
		width: 90%;
	}
	.full {
		width: 100%;
	}
	@media(max-width: 768px) {
		.search {
			padding-left: 8.33333%;
			padding-right: 0;
		}
	}
	</style>
</head>
<body>

	<div class='container text-center'>
		<br>
		<div class='panel panel-default panel-inverse'>
			<br>
			<form id='form'>
				<div class='form-group row'>
					<div class='search col-xs-8 col-sm-offset-2 col-sm-7'>
						<input name='url' id='url' type='text' class='form-control' placeholder='Video URL' value='<?php echo $url; ?>'>
					</div>
					<div class='col-xs-1 col-sm-1 text-left'>
						<button type="submit" class='btn btn-default'>Go!</button>
					</div>
				</div>
			</form>
		</div>

		<?php
		if(!$error) { ?>
			<div class='panel panel-default'>
				<br>
				<img src="<?php echo $thumbnail; ?>" class='img-responsive'>
				<br>
				<h3><?php echo $title ?></h3>
				<br>
			</div>

			<div class='progress progress-striped active'>
				<div class='progress-bar transition-slow' id='progress'></div>
			</div>

			<a href="audio/<?php echo $cleanTitle; ?>.mp3" class='btn btn-info' id='download' download>Download MP3</a>
			<br>

			<?php
		} else {
			if(empty($url)) {
				echo "<h3>Please enter a video URL.</h3>";
			} else if(isset($durationError)) {
				echo "<h3>Video exceeds 10 minute limit.</h3>";
			} else {
				echo "<h3>Something went wrong...please enter a valid URL.</h3>";
			}
		} ?>

		<br>
	</div>

	<script src='js/script.js'></script>
	<script>
	$(document).ready(function() {
		var url = '<?php echo (!$error) ? $url : ''; ?>';
		if(url) {
			$('#download').hide();
			$('#progress').addClass('almost-full');
			var ajaxData = {
				'url': url,
				'title': "<?php echo $cleanTitle; ?>"
			};
			$.ajax({
				type: 'POST',
				url: 'handlers/downloadVideo.php',
				data: ajaxData,
				dataType: 'json',
				encode: true
			})
			.done(function(data) {
				$('#progress').toggleClass('transition-slow transition-fast');
				$('#progress').toggleClass('almost-full full');
				$("#progress").addClass('progress-bar-success');
				$('#download').show();
			});
		}
	});
	</script>
</body>
</html>
