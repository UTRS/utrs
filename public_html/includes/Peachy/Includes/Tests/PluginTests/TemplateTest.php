<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).'/Includes/lime.php';
 
$t = new lime_test();

$t->info('1 - Testing the constructor');

$temp = new Template( 'foo. bar. {{template|param1=foo|param2=bar|tres}}. after. text.', 'template' );

$t->is( $temp->wholePage(), 'foo. bar. {{template|param1=foo|param2=bar|tres}}. after. text.', 'wholePage() returns the given content back verbatim' );
$t->is( (string)$temp, '{{template|param1=foo|param2=bar|tres}}', 'String casting returns the template' );


$t->is( $temp->fieldvalue('param1'), 'foo', 'fieldvalue() returns correct value #1' );
$t->is( $temp->fieldvalue('param2'), 'bar', '__get() returns correct value #2' );
$t->is( $temp->fieldvalue(1), 'tres', '__get() returns correct value #3' );

$t->info('2 - Modifying the template');

$temp->renamefield('param1','param3');
$t->is( $temp->fieldvalue('param3'), 'foo', 'renaming param1 works as expected' );

$temp->removefield('param2');
$t->is( $temp->fieldvalue('param2'), null, 'removing param2 works as expected' );

$temp->addfield('whee','param2');
$t->is( $temp->fieldvalue('param2'), 'whee', 'adding param2 works as expected' );

$t->is( $temp->fieldisset(2), false, 'param 2 is not set' );

$temp->addfield('whee2');
$t->is( $temp->fieldvalue(2), 'whee2', 'adding a numeric field works as expected' );

$t->is( $temp->fieldisset(2), true, 'param 2 is set' );


$t->is( $temp->fieldvalue('param3'), 'foo', 'updating param3 #1' );
$temp->updatefield('param3','bar');
$t->is( $temp->fieldvalue('param3'), 'bar', 'updating param3 #1' );

$t->is( (string)$temp, '{{template|param3=bar|tres|param2 = whee|whee2}}', 'Template setting has worked thus far' );
$temp->rename('template2');
$t->is( (string)$temp, '{{template2|param3=bar|tres|param2 = whee|whee2}}', 'Template setting has worked thus far' );

$t->info('3 - Multiple templates on one page');
$temp = new Template( 'foo. bar. {{template|param1=foo|param2=bar|tres}}. after. {{template|cuatro}}. text.', 'template' );
var_dump($temp);