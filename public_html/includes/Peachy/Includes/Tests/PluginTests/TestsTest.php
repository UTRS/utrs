<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).'/Includes/lime.php';
 
$t = new lime_test();

$t->info('1 - Testing strict functions');

$t->is_strict( 1, 1, '1 === 1' );
$t->is_strict( '1', '1', '"1" === "1"' );
$t->is_strict( array(1), array(1), 'array(1) === array(1)' );

$t->isnt_strict( 1, '1', '"1" === "1"' );