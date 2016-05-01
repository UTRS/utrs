<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).'/Includes/lime.php';
 
$t = new lime_test();

$yaml = 'blue: red
yellow:
  orange: white
  0: blurple
  pink:
    - ping
    - pong
';

$php = array(
	'blue' => 'red',
	'yellow' => array(
		'orange' => 'white',
		'blurple',
		'pink' => array( 'ping', 'pong' )
	)
);

$t->info('1 - Checking various syntax options');
$t->is( YAML::load($yaml)->toArray(), $php, 'YAML::load($yaml)->toArray() converts to valid PHP array' );
$t->is( YAML::load($php)->toYaml(), $yaml, 'YAML::load($php)->toYaml() converts to valid YAML' );


$y = new YAML($yaml);
$t->is( $y->toArray(), $php, 'new YAML($yaml)->toArray() converts to valid PHP array' );
$y = new YAML($php);
$t->is( $y->toYaml(), $yaml, 'new YAML($yaml)->toArray() converts to valid PHP array' );

$y = new YAML($php);
$t->is( (string) $y, $yaml, '(string) new YAML($php) converts to valid YAML' );

$y = new YAML;
$t->is( $y($yaml), $php, '$y = new YAML; $y($yaml) converts to valid PHP array' );
$t->is( $y($php), $yaml, '$y = new YAML; $y($php) converts to valid YAML' );

$t->is( YAML::parse($yaml), $php, 'YAML::parse($yaml) converts to valid PHP array' );
$t->is( YAML::parse($php), $yaml, 'YAML::parse($yaml) converts to valid YAML' );



$t->info('2 - Checking for various indentations' );

$yaml = 'blue: red
yellow:
  orange: white
  0: blurple
  pink: [ping, pong]
';

$t->is( YAML::parse($php, 2), $yaml, 'YAML::parse($php, 2) converts to valid YAML' );

$yaml = 'blue: red
yellow: { orange: white, 0: blurple, pink: [ping, pong] }
';
$t->is( YAML::parse($php, 1), $yaml, 'YAML::parse($php, 1) converts to valid YAML' );
$t->is( YAML::parse($php, 1), $yaml, 'YAML::parse($php, 0) converts to the same YAML as parse( $php, 1 )' );


$t->info( '3 - Check for failing on tabs ' );
$yaml = array(
  "foo:\n	bar",
  "foo:\n 	bar",
  "foo:\n	 bar",
  "foo:\n 	 bar",
);

foreach ($yaml as $i => $yamlstr) {
	try {
		YAML::parse($yamlstr);
		$t->fail('YAML files must not contain tabs #' . ( $i + 1 ));
	}
	catch ( Exception $e ) {
		$t->pass('YAML files must not contain tabs #' . ( $i + 1 ));
	}
}


