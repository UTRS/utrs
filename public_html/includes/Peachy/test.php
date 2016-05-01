<?php

require_once(dirname(__FILE__) . '/Init.php' );


if( in_array( 'test:one', $argv ) ) {
	list( $args, $opts ) = getArgs();
	
	if( !count( $args ) || isset( $opts['help'] ) ) peachyTestOneHelp(); 
	
	$files = array();

	foreach ( $args as $name ) {
		$file_name = dirname( __FILE__ ) . '/Tests/' . basename($name) . '.php';
		
		if( isset( $opts['peachy'] ) ) $file_name = dirname( __FILE__ ) . '/Includes/Tests/' . $name . '.php';
		
		if( file_exists( $file_name ) ) $files[] = $file_name;
	}
	
	if( !count( $files ) ) die( "Error: No tests found\n" );
	
	foreach ($files as $file) {
		include_once($file);
	}
}
elseif( in_array( 'test:all', $argv ) ) {
	
	list( $args, $opts ) = getArgs();
	
	if( isset( $opts['help'] ) ) peachyTestAllHelp(); 
	
	$h = new lime_harness(array(
		'force_colors' => isset($opts['color']) && $opts['color'],
		'verbose'      => isset($opts['trace']) && $opts['trace'],
	));
	
	$h->base_dir = dirname( __FILE__ ) . '/Tests';
	
	$files = rglob('*.php', 0, dirname( __FILE__ ) . '/Tests/' );
	if( isset( $opts['peachy'] ) ) $files = rglob('*.php', 0, dirname( __FILE__ ) . '/Includes/Tests/' );
	
	$h->register($files);
	
	$ret = $h->run() ? 0 : 1;
	
	if ($opts['xml']) {
		file_put_contents($opts['xml'], $h->to_xml());
	}
	
	return $ret;
}
else {
	echo "Usage:
	
For usage, type :
	php test.php test:all --help
	php test.php test:one --help

";
}

function getArgs() {
	global $argv;
	
	$params = $argv;
	array_shift($params);
	array_shift($params);
	
	$args = $opts = array();
	
	foreach( $params as $param ) {
		if( substr( $param, 0, 2 ) == '--' ) {
			$tmp = explode( '=', substr( $param, 2 ) );
			$opts[$tmp[0]] = ( isset( $tmp[1] ) ) ? $tmp[1] : true;
		}
		else {
			$args[] = $param;
		}
	}
	
	return array( $args, $opts );
}

function peachyTestOneHelp() {
	echo "Usage:
 php test.php test:one name1 [ ... nameN]

Arguments:
 name   The test name

Description:
 The test:one task launches unit tests:
 
   php test.php test:one someTest
 
 The task launches the Tests/someTest.php unit test.
 
 You can also launch unit tests for several names:
 
   php test.php test:one someTest someOtherTest

";
   die();
}

function peachyTestAllHelp() {
	echo "Usage:
 php test.php test:all [--xml=\"...\"] [--trace]

Options:
 --xml     The file name for the JUnit compatible XML log file
 --trace   Output more detailed information for each report

Description:
 The test:all task launches all the unit tests:
 
   php test.php test:all
 
 The task launches all the tests in the Tests/ folder.
 
 If some tests fail, you can use the --trace option to have more
 information about the failures:
 
   php test.php test:all -t
 
 The task can output a JUnit compatible XML log file with the --xml
 options:
 
   php test.php test:all --xml=log.xml
   
";
   die();
}