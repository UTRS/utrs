<?php

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

?>