<?php

$CONFIG = json_decode(@file_get_contents(dirname(__FILE__) . '/config.js'), true);

if (json_last_error() != JSON_ERROR_NONE || is_null($CONFIG)) {
    trigger_error('config.js does not exist or contains invalid JSON.', E_USER_ERROR);
}

?>
