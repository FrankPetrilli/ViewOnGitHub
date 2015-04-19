<?php
/*
 * Frank Petrilli | frank@petril.li | frank.petril.li
 * Language: PHP
 * Generate a page with a "View on GitHub" button dynamically depending on given parameters.
 */
	if (isset($_GET['username'])) {
		$username = $_GET['username'];
	}
	if (isset($_GET['repository'])) {
		$repository = $_GET['repository'];
	}

	if (isset($_GET['live'])) {
		$live = true;
		if (exceeded_limits()) {
			$live = false;
		}
	} else {
		$live = false;
	}

	if (isset($_GET['text']) && !$live ) {
		$text = $_GET['text'];
	} else {
		$text = "View on GitHub";
	}


	function get_repo_data($username, $repository) {
		return github_api_request("repos/" . $username . "/" . $repository);
	}

	// Check if we've exceeded limits of GitHub API in timeframe.
	function exceeded_limits($username = null, $password = null) {
		$result = json_decode(github_api_request("rate_limit", false));
		return ($result->resources->core->remaining > $result->resources->core->limit);
	}

	function github_api_request($path, $check_limits = true) {
		if ($check_limits) {
			if (exceeded_limits()) {
				return "exceeded limits.";
			}
		}
		$options  = array('http' => array('user_agent'=> $_SERVER['HTTP_USER_AGENT']));
		$context  = stream_context_create($options);
		$api_path = "https://api.github.com/" . $path;
		return file_get_contents($api_path, false, $context);
	}

	// Live update from GitHub API.
	if ($live) {
		$data = json_decode(get_repo_data($username, $repository));
		$name = $data->name;
		$full_name = $data->full_name;
		$link = $data->html_url;
		$text = "View " . $name . " on GitHub";
	} else {
		$repo_path =  $username . "/" . $repository;
		$full_name = $repo_path;
		$link = "https://github.com/" . $repo_path;
	}

?>
<!DOCTYPE html>
<html
<head>
<meta charset="utf-8">
<link rel="stylesheet" type="text/css" href="github-button.css">
</head>
<body>
<a target="_blank" class="button-link" href="<?= $link ?>"><span class="github-button"><span class="icon"></span><span><?= $text ?></span></span></a>
</body>
</html>
