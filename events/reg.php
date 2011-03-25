<?php
ini_set("display_errors","On");
/* Read in the base configuration */

if (!isset($BASEDIR)) {
	$BASEDIR = "..";
}

require_once("$BASEDIR/conf/events.conf");

/* Connect to DB, exit on failure */
if(!isset($server) || !$server) {
       	$server=conn_db($DSN);
}

/* set session if cookie is present */
if(isset($_COOKIE[$COOKIE_NAME])) {
        $session = &clean($_COOKIE[$COOKIE_NAME]);
}
else {
        $session = 0;
}

if(isset($session) && isAdmin($session) && isset($_REQUEST['IDEvent']) && (INT)($_REQUEST['IDEvent'])) {
	$idevent = $_REQUEST['IDEvent'];

	$sql = "select r.eml, title from $DB.REGISTRATIONS as r, $DB.EVENTS as e where r.FKIDevent = e.IDevent and r.FKIDevent = \"$idevent\"";

	if($result = mysql_query($sql,$server)) {
		$arr=array();
		while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
			array_push($arr, $line['eml']);
			$tit=$line['title'];
		}
		if(!isset($tit)) {
			$tit="";
		}
		print "
<html>
<head>
<title>Anmeldungen f&uuml;r Event Nr. $idevent: $tit</title>
</head>
<body bgcolor=#ffffff>
<p><b>Anmeldungen f&uuml;r Event Nr. $idevent:<br>
$tit</b></p>
<p>\n";
		foreach($arr as $e) {
			print "$e<br>\n";
		}
	}
	else {
		$err = mysql_error();
		print "
<html>
<head>
<title>Abfrage fehlgeschlagen</title>
</head>
<body bgcolor=#ffffff>
<p><b>Fehler bei der Abfrage:<br>
$err</p>
</html>\n";
	}
}
else {
	print "
<html>
<head>
<title>Abfrage fehlgeschlagen</title>
</head>
<body bgcolor=#ffffff>
<p><b>F&uuml;r diese Abfrage<br>
muss man als Administrator<br>
angemeldet sein!</p>
</html>\n";
	}
?>
