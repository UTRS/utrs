<?php

$ts_pw = posix_getpwuid(posix_getuid());
$ts_mycnf = parse_ini_file($ts_pw['dir'] . "/.my.cnf");


$host = "irc.freenode.net";
$port = "6667";
$ircBotNickServPassword = $ts_mycnf['ircBotPassword'];
$nick = "UTRSBot";
$ident = "UTRS Bot";
$chan = "#wikipedia-en-unblock-dev";
$toolserver_username = $ts_mycnf['user'];
$toolserver_password = $ts_mycnf['password'];
$toolserver_host = "sql-s1-user.toolserver.org";
$toolserver_database = "p_unblock";

?>