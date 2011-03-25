#!/usr/bin/perl -w

# events-notify.pl

# check registrations in event database and notify registered emails
# copyright markus@wernig.net 2005

# This is free software. You may use it under the terms of the GPL.
# See http://www.gnu.org/licenses/gpl.html

use strict;
use DBI;

my ($config, $line, $DSN, $DB, $tmp, %REGEV);

my $MAILER="/usr/lib/sendmail -t -i -f lugbed\@lugbe.ch";
my $PATH=$ENV{'PATH'}="/bin:/usr/bin";

$config = "/data/web/lugbe/conf/events.conf";

open(CONF, "< $config") or die "Can't open config file";

while ($line = <CONF>) {
	if($line =~ /^\$DSN *= */) {
		chomp($line);
		eval "$line";
		next;
	}
	elsif($line =~ /^\$DB *= */) {
		eval $line;
		next;
	}
	last if ($DSN && $DB);	
}
close(CONF);

my ($dbusr, $dbpw, $dbhost) = split(":",$DSN);

my $tm = time();
my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime($tm);
$year += 1900;
$mon += 1;
my $today = "${year}-${mon}-${mday}";

$tm += 86400;

($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime($tm);
$year += 1900;
$mon += 1;
my $tomorrow = "${year}-${mon}-${mday}";

# database parameters
my $driver = "mysql";
my $dsn = "DBI:$driver:database=$DB;host=$dbhost";

# connect to db
my $dbh=DBI->connect($dsn, "$dbusr", "$dbpw") or die "Can't connect to database";

my $sql = "select IDreg, IDevent, FKIDevent, r.eml, e.scheduled, e.title from REGISTRATIONS as r, EVENTS as e where e.scheduled > now() and (r.notified is null or r.notified < \"$today\") and e.scheduled <= \"$tomorrow 23:59:59\" and r.FKIDevent = e.IDevent";

my $sqlh = $dbh->prepare($sql);
$sqlh->execute();
my $res;
while($res=$sqlh->fetchrow_hashref) {
	unless($REGEV{$res->{"IDevent"}}) {
		$REGEV{$res->{"IDevent"}}->{"registered"} = ();
		$REGEV{$res->{"IDevent"}}->{"title"} = $res->{"title"};
		$REGEV{$res->{"IDevent"}}->{"scheduled"} = $res->{"scheduled"};
	}
	push(@{$REGEV{$res->{"IDevent"}}->{"registered"}}, $res->{'eml'});
}

foreach my $eventid(keys %REGEV) {
	my ($d, $h) = split(" ",$REGEV{$eventid}->{"scheduled"});
	my $t = $REGEV{$eventid}->{'title'};
	my $subject = "LugBE Kalender Erinnerung: $REGEV{$eventid}->{'title'}";


	foreach my $eml(@{$REGEV{$eventid}->{'registered'}}) {
		my $text = "Automatische Erinnerung:

Folgender Anlass findet am $d um $h Uhr statt:
* $t *

Dieses Mail wurde automatisch geschickt, da wir eine Anmeldung 
zu diesem Anlass unter der Email-Adresse $eml
auf http://www.lugbe.ch/action/events/ erhalten haben.
Wir freuen uns auf Deinen Besuch!

Herzlich: Linux User Group Bern

PS: Falls diese Anmeldung nicht von Dir stammt, melde das bitte kurz unter
info\@lugbe.ch.
";
		open(MAIL, "| $MAILER $eml");

#		print "
		print MAIL "To: $eml
From: LugBE Kalender <lugbed\@lugbe.ch>
Subject: $subject
X-Mailer: LugBE Unix Perl Sendmail

$text
";
		close MAIL;
	}
#	my $lst = join("\n",@{$REGEV{$eventid}->{"registered"}});
#	print "Event $eventid: $lst\n";	
}

my $IDevents = join(",",keys (%REGEV));

$sql = "update REGISTRATIONS set notified = now() where FKIDevent in ($IDevents)";
$dbh->do($sql) if($IDevents);
$dbh->disconnect;
