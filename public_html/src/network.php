<?php


$online = False;
$expected = False;
if (!$online) {
  skinHeader();
  echo "<center><h2>UTRS is down :(</h2>";
  if ($expected) {
    echo "<br />This is a scheduled maintence window in which UTRS is down. We hope to be live again as soon as possibe.</center>";
  }
  else {
    echo "<br />This is a unscheduled maintence in which UTRS is down. UTRS has died on us and we are working to fix it as soon as possible. <br />We hope to be live again as soon as possibe.</center>";
  }
  skinFooter();
  die();
} 

// Borrowed from ACC/Simon Walker: https://phabricator.stwalkerster.co.uk/P27

function isHttps()
{
	if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
		if ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
			// Client <=> Proxy is encrypted
			return true;
		}
		else {
			// Proxy <=> Server link is encrypted, but not Client <=> Proxy.
			return false;
		}
	}

	if (isset($_SERVER['HTTPS'])) {
		if ($_SERVER['HTTPS'] === 'off') {
			// ISAPI on IIS breaks the spec. :(
			return false;
		}

		if ($_SERVER['HTTPS'] !== '') {
			// Set to a non-empty value
			return true;
		}
	}

	return false;
}
function forceHTTPS() {
	if (isHttps()) {
		// Client can clearly use HTTPS, so let's enforce it for all connections.
		header("Strict-Transport-Security: max-age=15768000");
	}
	else {
		$path = 'https://' . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		header("Location: " . $path);
		exit;
	}
}

?>
