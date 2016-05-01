<?php
require_once dirname(dirname(dirname(__FILE__))).'/Includes/lime.php';
 
$t = new lime_test();

$t->info( '1 - Testing compatibility with various versions' );

try {
	peachyCheckPHPVersion();
	$t->pass( 'Current version doesn\'t throw exception' );
	} catch (Exception $e) {
	$t->fail( 'Current version doesn\'t throw exception' );
}

$passers = array(
	'5.2.4',
	'5.3.3',
	'5.4.6',
	'6.4.5',
	'5.2.1',
	'5.3.0',
	'5.2.11',
	'5.3.5',
	'5.2.7',
	'5.3.9',
);

foreach( $passers as $version ) {
	try {
		peachyCheckPHPVersion( $version );
		$t->pass( $version . ' does not throw exception' );
		} catch (Exception $e) {
		$t->fail( $version . ' does not throw exception' );
	}
}

$failers = array(
	'5.2.0',
	'5.1.3',
	'5.0.4',
	'5.1.9',
	'5.1.0',
	'4.3.3',
	'4.2.4',
	'4.9.9',
	'4.0.0',
	'3.3.3',
);
foreach( $failers as $version ) {
	try {
		peachyCheckPHPVersion( $version );
		$t->fail( $version . ' throws exception' );
		} catch (Exception $e) {
		$t->pass( $version . ' throws exception' );
	}
}
