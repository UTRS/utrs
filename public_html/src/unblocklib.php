<?php

function connectToDB(){
	$ts_pw = posix_getpwuid(posix_getuid());
	$ts_mycnf = parse_ini_file($ts_pw['dir'] . "/.my.cnf");
	$db = mysql_connect("sql-s1-user.toolserver.org", $ts_mycnf['user'], $ts_mycnf['password'], true);
	if($db == false){
		throw new ErrorException("Failed to connect to database cluster sql-s1-user!");
	}
	mysql_select_db("p_unblock", $db);
	return $db;
}

?>