<?php

$raw = @file_get_contents(dirname(__FILE__) . '/config.js.php'
$file = str_replace("<?php","",$raw)
$CONFIG = json_decode($file, true);

if (json_last_error() != JSON_ERROR_NONE || is_null($CONFIG)) {
    trigger_error('config.js.php does not exist or contains invalid JSON.', E_USER_ERROR);
}

?>
