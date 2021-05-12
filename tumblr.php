#!/usr/bin/php
<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

function usage($name)
{
	echo "\nUsage: " . $name . " [--skip-cert-verify] -u username -p password -b blog [-c conversation] [-f filename] [-s] [-d YYYYMMDD] [-r [req/sec]]\n\n";
	echo "\tFirst run script only with username, password and blog to get list of conversations.\n";
	echo "\tThen run script again specifying conversation you want to download.\n\n";
	echo "\t-u, --username (required)\n\t\ttumblr username or E-mail\n\n";
	echo "\t-p, --password (required)\n\t\ttumblr password\n\n";
	echo "\t--skip-cert-verify\n\t\tskip SSL verification - INSECURE!\n\n";
	echo "\t-b, --blog (required)\n\t\ttumblr blog without .tumblr.com (required)\n\n";
	echo "\t-c, --conversation (optional|required)\n\t\tconversation id from the list\n\n";
	echo "\t-r, --rate-limit [requests] (optional [optional=1000])\n\t\tset rate limit per minute - default 1000 if no value specified\n\n";
	echo "\t-d, --date YYYYMMDD (optional)\n\t\toutput only log for specified date\n\n";
	echo "\t-f, --file filename (optional)\n\t\toutput file name\n\n";
	echo "\t-s, --split (optional) (require -f)\n\t\tput output in separete files for each day: filename-YYYYMMDD.ext\n\n";
	echo "\n";

	exit;
}

$username = "";
$password = "";
$skip_ssl = 0;
$blog = "";
$conversation = "";
$rate = 0;
$file = "";
$split = 0;
$date = "";
$a = getopt("u:p:b:c:f:sdr:", array("username::", "password::", "blog::", "conversation:", "file:", "split", "date:", "rate:", "skip-cert-verify"));

if (!isset($a['u']) && !isset($a['username'])) usage($argv[0]); else {
	if (isset($a['u'])) $username = $a['u'];
	if (isset($a['username'])) $username = $a['username'];
}
if (!isset($a['p']) && !isset($a['password'])) usage($argv[0]); else {
	if (isset($a['p'])) $password = $a['p'];
	if (isset($a['password'])) $password = $a['password'];
}
if (isset($a['skip-cert-verify'])) {
	$skip_ssl = 1;
}
if (!isset($a['b']) && !isset($a['blog'])) usage($argv[0]); else {
	if (isset($a['b'])) $blog = $a['b'] . ".tumblr.com";
	if (isset($a['blog'])) $blog = $a['blog'] . ".tumblr.com";
}
if (isset($a['c'])) $conversation = $a['c'];
if (isset($a['conversation'])) $conversation = $a['conversation'];
if (isset($a['r'])) {
	if ((int)$a['r'] > 0) $rate = (int)$a['r']; else $rate = 1000;
}
if (isset($a['rate-limit'])) {
	if ((int)$a['rate-limit'] > 0) $rate = (int)$a['rate-limit']; else $rate = 1000;
}
if (isset($a['f'])) $file = $a['f'];
if (isset($a['file'])) $file = $a['file'];
if (isset($a['s'])) $split = 1;
if (isset($a['split'])) $split = 1;
if (isset($a['d'])) $date = $a['d'];
if (isset($a['date'])) $date = $a['date'];
if (isset($a['d']) || isset($a['date']))
	if (strlen($date) != 8) usage($agrv[0]);

$post = array(
    "authentication"	=> "oauth2_cookie",
    "email"		=> $username,
);

// land to main page
echo "Get main page to fetch auth token, ";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.tumblr.com/login");
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_COOKIESESSION, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, "tumbltcookiefile");
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
if ($skip_ssl) {
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
}
$r = curl_exec($ch);

preg_match('#"API_TOKEN":"(.*?)"#', $r, $matches);
$auth_token = $matches[1];

echo "send username, ";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.tumblr.com/api/v2/login/mode");
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_COOKIESESSION, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, "tumbltcookiefile");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Authorization: Bearer " . $auth_token
));
if ($skip_ssl) {
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
}
$r = curl_exec($ch);

$post = array(
    "grant_type"	=> "password",
    "password"		=> $password,
    "username"		=> $username,
);

echo "send password: ";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.tumblr.com/api/v2/oauth2/token");
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_COOKIESESSION, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, "tumbltcookiefile");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Authorization: Bearer " . $auth_token
));
if ($skip_ssl) {
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
}
$r = curl_exec($ch);

$r = @(array)json_decode($r);
if ($r['error'] != "")
{
    echo $r['error_description'] . "\n";
    exit;
}
echo "OK\n";

/*echo "\nFetch: login, ";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.tumblr.com/login");
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_COOKIESESSION, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, "tumbltcookiefile");
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
if ($skip_ssl) {
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
}
$r = curl_exec($ch);

preg_match('#<meta name="tumblr-form-key" id="tumblr_form_key" content="(.*?)">#', $r,$matches);
$key = $matches[1];

$post = array(
	'tracking_url'		=> '/login',
	'determine_email'	=> $username,
	'user[age]'			=> '',
	'user[email]'		=> $username,
	'user[password]'	=> $password,
	'version'			=> 'STANDARD',
	'form_key'			=> $key,
);

echo "home, ";
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
$r = curl_exec($ch);

preg_match('#polling_token&quot;:&quot;(.*?)&quot;,&quot;#', $r, $matches);
$token = @$matches[1];
preg_match('#mention_key&quot;:&quot;(.*?)&quot;,&quot;#', $r, $matches);
$mention = @$matches[1];

if (($token == "") || ($mention == ""))
{
	echo "done.\n\nInvlid username or password!\n\n";

	exit;
}*/

if ($conversation == "")
{
	echo "conversations, ";

	$conv = array();
	$next = "xxx";
	$q = "https://www.tumblr.com/svc/conversations?participant=" . $blog . "&_=" . time() . "000";
	while ($next != "")
	{
		curl_setopt($ch, CURLOPT_URL, $q);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'X-Requested-With: XMLHttpRequest',
		));
		curl_setopt($ch, CURLOPT_POST, false);
		$r = curl_exec($ch);
		$r = json_decode($r);

		$next = @$r->response->_links->next->href;

		foreach ($r->response->conversations as $c)
		{
			foreach ($c->participants as $p)
			{
				$conv[$c->id][] = $p->name;
			}
		}

		$q = "https://www.tumblr.com" . $next;
	}

	echo "done.\n\n";

	echo "\nConversations: \n";
	foreach ($conv as $i => $c)
	{
		echo $i . " " . join(" <=> ", $c) . "\n";
	}
	echo "\n";

	exit;
}

$messages = array();
$next = "xxx";
$q = "https://www.tumblr.com/svc/conversations/messages?conversation_id=" . $conversation . "&participant=" . $blog . "&_=" . time() . "000";
while ($next != "")
{
	$t = 0;

	curl_setopt($ch, CURLOPT_URL, $q);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'X-Requested-With: XMLHttpRequest',
	));
	curl_setopt($ch, CURLOPT_POST, false);
	$y = 5;
	while ($y > 0)
	{
		$r = curl_exec($ch);
		$r = json_decode($r);
		if (is_object($r)) break;
		$y--;
		echo "retry, ";
	}
	$next = @$r->response->messages->_links->next->href;
	$r = $r->response->messages->data;

	if (count($r))
	foreach ($r as $i)
	{
		if ($t == 0)
		{
			$t = $i->ts;

			echo date("d/m/Y, H:i:s", $t / 1000) . ", ";
		}

		if ($date != "")
		{
			$d = date("Ymd", $i->ts / 1000);
			if ((int)$d < (int)$date) break 2;
		} else $d = "";

		if (($d == "") || ($d == $date))
		{
			$user = @array_shift(explode(".", $i->participant));

			if ($i->type == "TEXT")
			{
				$messages[$i->ts] = date("d/m/Y, H:i:s", $i->ts / 1000) . " " . $user . ": " . $i->message;
			} else
			if ($i->type == "IMAGE")
			{
				$images = array();
				foreach ($i->images as $img) $images[] = $img->original_size->url;

				$messages[$i->ts] = date("d/m/Y, H:i:s", $i->ts / 1000) . " " . $user . ": " . join(" , ", $images);
			} else
			if ($i->type == "POSTREF")
			{
				$messages[$i->ts] = date("d/m/Y, H:i:s", $i->ts / 1000) . " " . $user . ": " . $i->post->post_url;
			} else {
				echo "\nUNKNOWN\n";
				print_r($i);
			}
		}
	}
	
	$q = "https://www.tumblr.com" . $next;

	if ($rate > 0)
	{
		usleep((60 / $rate) * 1000000);
	}
}

curl_close($ch);

echo "done.\n\n";

ksort($messages);

if ($file == "")
{
	$messages = join("\n", array_values($messages));
	echo $messages . "\n\n";
} else {
	if ($split == 0)
	{
		$messages = join("\n", array_values($messages));
		file_put_contents($file, $messages . "\n");
	} else {
		$a = explode(".", $file);
		if (count($a) > 1) $e = "." . array_pop($a); else $e = "";

		$m = array();
		if (count($messages))
		{
			foreach ($messages as $i => $s) $m[date("Ymd", $i / 1000)][] = $s;

			foreach ($m as $d => $s)
			{
				$messages = join("\n", array_values($s));
				file_put_contents(join($a) . "-" . $d . $e, $messages . "\n");
			}
		}
	}
}

?>
