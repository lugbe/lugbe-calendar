<?php
// by resmo <mail@renemoser.net>
// $Id: index.php 16 2005-04-28 08:58:31Z moserre $

require_once 'lib/FeedCreator.php';
require_once '../../conf/events.conf';

/* Connect to DB, exit on failure */
$server=conn_db($DSN);

$daydiff    = 7; // holt alle events von heute bis in x tagen

if(isset($_GET['days']) && preg_match("/^(1|3|7|14|21|30)$/",$_GET['days'])) {
  $daydiff = $_GET['days'];
}

$rss = new UniversalFeedCreator();
$rss->useCached();
$rss->title             = "LugBE Events";
$rss->description       = "Die n&auml;chsten Events";
$rss->link              = "http://events.lugbe.ch/action/events/";
$rss->syndicationURL    = "http://www.lugbe.ch/action/events/rss/".$_SERVER['PHP_SELF'];

$image = new FeedImage();
$image->title           = "LugBE Logo";
$image->url             = "http://www.lugbe.ch/images/lugbe_logo.jpg";
$image->link            = "http://www.lugbe.ch";
$image->description     = "Die n&auml;chsten Events";
$rss->image             = $image;

$res = mysql_query("SELECT *, date_format(scheduled,'%Y-%m-%dT%H:%i:%s+00:00') as date,
DATE_FORMAT(scheduled,'%m') as month, DATE_FORMAT(scheduled,'%d') as day, 
DATE_FORMAT(scheduled,'%Y') as year FROM ".$DB.".EVENTS 
WHERE DATEDIFF(scheduled,CURDATE()) < ".$daydiff." 
AND DATEDIFF(scheduled,CURDATE()) >= ".'0'." 
ORDER BY scheduled DESC", $server);

if (empty($res)) {die("Keine Daten");}

while ($data = mysql_fetch_object($res)) {
    $item = new FeedItem();
    $item->title        = $data->title;
    $item->link         = "http://events.lugbe.ch/action/events/events_iframe.phtml?YEAR=".$data->year."&MONTH=".$data->month."&DAY=".$data->day;
    $item->description  = $data->description;
    $item->date         = $data->date;
    $item->source       = "http://www.lugbe.ch";
    $item->author       = "Tux";
    $rss->addItem($item);
}

$rss->saveFeed("RSS1.0", "feed".$daydiff.".xml");
?>
