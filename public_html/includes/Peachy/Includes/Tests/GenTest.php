<?php

require_once dirname(dirname(dirname(__FILE__))).'/Includes/lime.php';

$t = new lime_test();

$site = Peachy::newWiki( null, null, null, 'http://en.wikipedia.org/w/api.php' );


class FauxWiki { function get_nobots() {return true;}}

$t->info( '1 - iin_array() testing' );

$t->is( iin_array( 'no', array( 'No', 'non', 'dhhd' ) ), true, '"no" in array( "No" )' );
$t->is( iin_array( 'noN', array( 'No', 'no', 'dhhd' ) ), false, '"non" not in array' );
$t->is( iin_array( array(1), array( 'No', 'no', 'dhhd', array(1) ) ), true, 'needle is an array' );

$t->info( '2 - in_string() testing' );

$t->ok( in_string( 'foo', '123foo456' ), 'foo in 123foo456' );
$t->ok( in_string( 'foo', 'foo456' ), 'foo in foo456' );
$t->ok( !in_string( 'Foo', 'foo456' ), 'Foo not in foo456' );
$t->ok( in_string( 'Foo', 'foo456', true ), 'Foo in foo456, with case insensitivity' );


$t->info( '3 - in_array_recursive() testing' );

$t->is( in_array_recursive( 'no', array( 'No', 'non', 'dhhd', array( 'no' ) ) ), true );
$t->is( in_array_recursive( 'noN', array( 'No', 'no', 'dhhd', array( 'Non' ) ), true ), true, 'Insensitive search' );
$t->is( in_array_recursive( array( 'noN' ), array( 'No', 'no', 'dhhd', array( 'noN' ) ) ), true, 'Recursive search' );
$t->is( in_array_recursive( array( 'noN' ), array( 'No', 'no', 'dhhd', array( 'NoN' ) ), true ), true, 'Recursive insensitive search' );

$t->is( in_array_recursive( 'no', array( 'nno', 'non', 'dhhd', array( 'non' ) ) ), false );
$t->is( in_array_recursive( 'noN', array( 'No', 'no', 'dhhd', array( 'Nonn' ) ), true ), false, 'Insensitive search' );
$t->is( in_array_recursive( array( 'noN' ), array( 'No', 'no', 'dhhd', array( 'nonn' ) ) ), false, 'Recursive search' );
$t->is( in_array_recursive( array( 'noN' ), array( 'No', 'no', 'dhhd', array( 'NonN' ) ), true ), false, 'Recursive insensitive search' );

$t->info( '5 - checkExclusion() testing' );

$wiki = new FauxWiki;
$t->ok( checkExclusion( $wiki, '{{nobots}}', 'TestBot', 'optoutTest' ), '{{nobots}} trips the bot' );
$t->ok( checkExclusion( $wiki, '{{bots|allow=none}}', 'TestBot', 'optoutTest' ), '{{bots|allow=none}} trips the bot' );
$t->ok( checkExclusion( $wiki, '{{bots|deny=all}}', 'TestBot', 'optoutTest' ), '{{bots|deny=all}} trips the bot' );
$t->ok( checkExclusion( $wiki, '{{bots|allow=SomeOtherBot}}', 'TestBot', 'optoutTest' ), '{{bots|allow=SomeOtherBot}} trips the bot' );
$t->ok( checkExclusion( $wiki, '{{bots|deny=SomeOtherBot,TestBot}}', 'TestBot', 'optoutTest' ), '{{bots|deny=TestBot}} trips the bot' );
$t->ok( checkExclusion( $wiki, '{{bots|optout=all}}', 'TestBot', 'optoutTest' ), '{{bots|optout=all}} trips the bot' );
$t->ok( checkExclusion( $wiki, '{{bots|optout=optoutTest}}', 'TestBot', 'optoutTest' ), '{{bots|optout=optoutTest}} trips the bot' );

$t->ok( !checkExclusion( $wiki, '{{bots}}', 'TestBot', 'optoutTest' ), '{{bots}} doesn\'t trip the bot' );
$t->ok( !checkExclusion( $wiki, '{{bots|allow=all}}', 'TestBot', 'optoutTest' ), '{{bots|allow=all}} doesn\'t trip the bot' );
$t->ok( !checkExclusion( $wiki, '{{bots|deny=none}}', 'TestBot', 'optoutTest' ), '{{bots|deny=none}} doesn\'t trip the bot' );
$t->ok( !checkExclusion( $wiki, '{{bots|allow=SomeOtherBot,TestBot}}', 'TestBot', 'optoutTest' ), '{{bots|allow=TestBot}} doesn\'t trip the bot' );
$t->ok( !checkExclusion( $wiki, '{{bots|deny=SomeOtherBot}}', 'TestBot', 'optoutTest' ), '{{bots|deny=SomeOtherBot}} doesn\'t trip the bot' );
$t->ok( !checkExclusion( $wiki, '{{bots|optout=none}}', 'TestBot', 'optoutTest' ), '{{bots|optout=none}} doesn\'t trip the bot' );
$t->ok( !checkExclusion( $wiki, '{{bots|optout=someOther}}', 'TestBot', 'optoutTest' ), '{{bots|optout=SomeOther}} doesn\'t trip the bot' );