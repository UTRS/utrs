<?php
require_once dirname(dirname(dirname(__FILE__))).'/Includes/lime.php';

class ImageTest extends Image {
	function getParam($param) { return $this->$param; }
}

$t = new lime_test();

$site = Peachy::newWiki( 'Tests/enwikitest' );

$p1 = $site->initImage( 'Example.jpg' );
$p2 = initImage( 'Example.jpg' );
$p3 = new Image( $site, 'Example.jpg' );
$p4 = new ImageTest( $site, 'example.jpg' );

$t->info('1 - Initialization');

$t->is( serialize($p1), serialize($p2), 'Wiki::initImage() and initImage() return the same value' );
$t->is( serialize($p1), serialize($p3), 'Wiki::initImage() and new Image( $site ) return the same value' );
$t->is( serialize($p4->getParam('wiki')), serialize($site), 'initImage()->wiki and $site return the same value' );

unset( $p2, $p3, $p1 );

$t->info('2 - Construction');

$t->is( $p4->getParam('title'), 'File:Example.jpg', '__construct() normalizes title' );
$t->is( $p4->getParam('rawtitle'), 'Example.jpg', '__construct() normalizes raw title' );
$t->is( $p4->getParam('localname'), 'Example.jpg', '__construct() normalizes local filename' );
$t->is( serialize( $p4->getParam('page') ), serialize( initPage( 'File:Example.jpg' ) ), '__construct() gets page class' );
$t->is( $p4->getParam('local'), true, '__construct() gets shared status #1' );


$p4 = new ImageTest( $site, 'File:Elakala Waterfalls Swirling Pool Mossy Rocks.jpg' );

$t->is( $p4->getParam('local'), false, '__construct() gets shared status #2' );

$t->is( $p4->getParam('mime'), 'image/jpeg', '__construct() gets correct MIME type' );
$t->cmp_ok( $p4->getParam('bitdepth'), '>',  5, '__construct() gets correct bitdepth' );
$t->is( strlen($p4->getParam('hash')), 40, '__construct() gets valid sha1 hash' );
$t->cmp_ok( $p4->getParam('size'), '>', 1000000, '__construct() gets correct size' );
$t->cmp_ok( $p4->getParam('height'), '>', 1000, '__construct() gets correct height' );
$t->cmp_ok( $p4->getParam('width'), '>', 1000, '__construct() gets correct width' );
$t->is( $p4->getParam('url'), 'http://upload.wikimedia.org/wikipedia/commons/b/b3/Elakala_Waterfalls_Swirling_Pool_Mossy_Rocks.jpg', '__construct() gets correct image URL' );
$t->cmp_ok( count($p4->getParam('metadata')), '>', 5, '__construct() gets correct metadata' );


$t->info('3 - get*(), is*() functions');

$contribs = $p4->get_history( 'older', 1 );
$t->cmp_ok( count($contribs), '<=', 1, 'get_contribs(older,1) returns only 1 value' );

$contribs = $p4->get_history();
$t->cmp_ok( count($contribs), '>', 1, 'get_contribs() works as expected' );


