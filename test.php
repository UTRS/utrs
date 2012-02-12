<?php
require_once("public_html/src/unblocklib.php");

echo "user@domain.com --> " . validEmail("user@domain.com") . "\n";
echo "imnotasockpuppet@imasockpuppet.org --> " . validEmail("imnotasockpuppet@imasockpuppet.org") . "\n";
echo "email.with.dots@dots.email.com --> " . validEmail("email.with.dots@dots.email.com") . "\n";
echo "foo@[127.0.0.1] --> " . validEmail("foo@[127.0.0.1]") . "\n";
echo "\"user\"@domain.com --> " . validEmail("\"user\"@domain.com") . "\n";


?>