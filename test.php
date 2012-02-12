<?php
require_once("public_html/src/unblocklib.php");

echo "Valid emails: \n";
echo "user@domain.com --> " . testWrapper("user@domain.com") . "\n";
echo "imnotasockpuppet@imasockpuppet.org --> " . testWrapper("imnotasockpuppet@imasockpuppet.org") . "\n";
echo "email.with.dots@dots.email.com --> " . testWrapper("email.with.dots@dots.email.com") . "\n";
echo "foo@[127.0.0.1] --> " . testWrapper("foo@[127.0.0.1]") . "\n";
echo "\"user\"@domain.com --> " . testWrapper("\"user\"@domain.com") . "\n\n";
echo "0@a --> " . testWrapper("0@a") . "\n\n";
echo "!#$%&'*+-/=?^_`{}|~@example.org --> " . testWrapper("!#$%&'*+-/=?^_`{}|~@example.org") . "\n\n";

echo "Invalid emails:\n";
echo "not.an.email --> " . testWrapper("not.an.email") . "\n";
echo "Invalid.@email.com --> " . testWrapper("Invalid.@email.com") . "\n";
echo "Invalid..123@email.com --> " . testWrapper("Invalid..123@email.com") . "\n";
echo "invalid@123@email.com --> " . testWrapper("invalid@123@email.com") . "\n";
echo "invalid<email>addy@email.com --> " . testWrapper("invalid<email>addy@email.com") . "\n";
echo "invalid\"addy@email.com --> " . testWrapper("invalid\"addy@email.com") . "\n";
echo "invalid\"addy@email.com --> " . testWrapper("invalid\"addy@email.com") . "\n";
echo "invalid\\\"addy@email.com --> " . testWrapper("invalid\\\"addy@email.com") . "\n";


function testWrapper($email){
	if(validEmail($email)){
		return "VALID";
	}
	return "NO";
}
?>