<?php

require_once('status.php');
require_once('src/exceptions.php');
require_once('src/unblocklib.php');

function checkOnline() {
    if (loggedIn()) {
        if (verifyAccess($GLOBALS['DEVELOPER'])) {
            return;
        }
    } else if (strpos($_SERVER['REQUEST_URI'], 'login.php')) {
        return;
    }
    if (!online()) {
        skinHeader();
        echo "<center><h2>UTRS is down :(</h2>";
        if (expected()) {
            echo "<br />This is a scheduled maintence window in which UTRS is down. We hope to be live again as soon as possibe.</center>";
        } else {
            echo "<br />This is an unscheduled maintence in which UTRS is down. UTRS has died on us and we are working to fix it as soon as possible. <br />We hope to be live again as soon as possibe.</center>";
        }
        skinFooter();
        die();
    }
}
