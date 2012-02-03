<?php

$ts_pw = posix_getpwuid(posix_getuid());
$ts_mycnf = parse_ini_file($ts_pw['dir'] . "/.my.cnf");


$ircBotNetworkHost = "irc.freenode.net";
$ircBotNetworkPort = "6667";
$ircBotNickServPassword = $ts_mycnf['ircBotPassword'];
$ircBotNickname = "UTRSBot";
$ident = "UTRS Bot";
$ircBotChannel = "#wikipedia-en-unblock";
$toolserver_username = $ts_mycnf['user'];
$toolserver_password = $ts_mycnf['password'];
$toolserver_host = "sql-s1-user.toolserver.org";
$toolserver_database = "p_unblock";

?>