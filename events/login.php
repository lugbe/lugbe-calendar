<?php

/* Read in the base configuration */

/* ADJUST THIS !!! */

if (!isset($BASEDIR)) {
	$BASEDIR = "..";
}

if(isset($_SERVER['HTTP_REFERER'])) {
	$ref= $_SERVER['HTTP_REFERER'];
}
if(isset($_REQUEST['REFER'])) {
	$ref= $_REQUEST['REFER'];
}
if(isset($ref) && preg_match("/events_iframe.phtml/", $ref)) {
	$follow = "events_iframe.phtml";
}
else {
	$follow = "events.phtml";
}

require_once("$BASEDIR/conf/events.conf");

if(!isset($COOKIE_DOMAIN)) {
	$COOKIE_DOMAIN = $_SERVER['SERVER_NAME'];
}

if(isset($_REQUEST['user']) && $_REQUEST['user'] && isset($_REQUEST['pw']) && $_REQUEST['pw']) {
	$user = &clean($_REQUEST['user']);
	$password = &clean($_REQUEST['pw']);

	/* Connect to DB, exit on failure */
	if(!isset($server) || !$server) {
        	$server=conn_db($DSN);
	}

	$sql = "select * from $DB.PEOPLE where alias = \"$user\" and password = password(\"$password\")";

	if(($result = mysql_query($sql,$server)) && ($line = mysql_fetch_array($result, MYSQL_ASSOC))) {

		error_log("User $user logged in");

		$r=sendCookie($COOKIE_NAME,$COOKIE_DOMAIN,3600);

		if($r) {
			$sql = "update $DB.PEOPLE set session = \"$r\" where alias = \"$user\"";
			if(mysql_query($sql,$server)) {
				$session = $r;
				$_COOKIE[$COOKIE_NAME] = $r;
				$topmsg = "<div class=ok>Benutzer $user angemeldet. Ihre Session ist eine Stunde g&uuml;ltig.</div>";
			}
			else {
				$err = mysql_error();
				$topmsg = "<div class=warn>Authentication successful, but failed to store session.</div>";
				echo "$err";
			}
		}
	}
	else {
		$err = mysql_error();
		$topmsg = "<div class=warn>Authentication failed</div>";
				echo "$err";
	}
}
$_SERVER['SCRIPT_NAME'] = "/action/events/$follow";
include ($follow);
?>
