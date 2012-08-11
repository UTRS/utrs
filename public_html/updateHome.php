<?php

require_once("src/unblocklib.php");
require_once("src/userObject.php");

verifyLogin('home.php');

$user_id = User::getUserByUsername($_SESSION['user'])->getUserId();

$data = unserialize($_POST['data']);

$db = connectToDB();


try {

	//Delete old settings if there are any
	$db->exec("DELETE FROM hooks WHERE user_id = " . $user_id . ";");

	//Build insert statement
	$sql = "INSERT INTO hooks VALUES ";

	//Gather each column
	for ($column = 0; $column < count($data); $column++) {
		//Each element in each column
		for ($order = 0; $order < count($data[$column]); $order++) {
			// Userid, hook_name, zone, order.  Zone is column + 1 because the javascript array
			// goes from 0-2 but the database is 1-3 for readability
			$sql .= "(" . $user_id. ",'" . $data[($column)][$order] . "'," . ($column+1) . "," . $order . "),";
		}
	}

	//Rm trailing comma
	$sql = substr($sql, 0, -1);

	//Insert new order into database
	$db->exec($sql);

	//echo $sql;


} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
