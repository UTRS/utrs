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
        ?>
        <center>
            <h2>UTRS 1.0 has reached the end of its life.</h2>
            <p>
                Due to multiple technical issues, UTRS 1.0 has been retired.
                All functionality has been moved over to
                <a href="https://utrs-beta.wmflabs.org">UTRS 2.0-beta</a>.
            </p>
            <p>
                If you have any issues using UTRS 2, please contact us
                at <a href="https://en.wikipedia.org/wiki/WT:UTRS">WT:UTRS</a>, add
                <a href="https://en.wikipedia.org/wiki/Template:UTRS_help_me">{{UTRS help me}}</a> to your talk page,
                or send an-email to the e-mail address shown at the bottom of this page.
            </p>
        </center>
        <?php
        skinFooter();
        die();
    }
}
