<?php

$url = isset($_POST['url']) ? sanitizeURL($_POST['url']) : ''; // the youtube video ID
$title = isset($_POST['title']) ? htmlspecialchars_decode($_POST['title']) : '';

if(!empty($title)) {
	if(file_exists("../audio/$title.mp3")) {
		$exists = true;
	} else {
		$exists = false;
	}

	if(!$exists) {
		downloadVideo($url);
	}
}

function downloadVideo($url) {
	exec("youtube-dl -x --audio-format mp3 -o '../audio/%(title)s.%(ext)s' --match-filter 'duration <= 900' -- " . escapeshellarg($url));
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

$data = array('url' => $url, 'title' => $title);

echo json_encode($data);

?>
