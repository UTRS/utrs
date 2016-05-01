<?php
require_once dirname(dirname(dirname(__FILE__))).'/Includes/lime.php';

class PageTest extends Page {
	function getParam($param) { return $this->$param; }
}

$t = new lime_test();

$site = Peachy::newWiki( 'Tests/enwikitest' );

$p1 = $site->initPage( 'Foobar' );
$p2 = initPage( 'Foobar' );
$p3 = new Page( $site, 'Foobar' );
$p4 = new PageTest( $site, 'foobar' );

$t->info('1 - Initialization');

$t->is( serialize($p1), serialize($p2), 'Wiki::initPage() and initPage() return the same value' );
$t->is( serialize($p1), serialize($p3), 'Wiki::initPage() and new Page( $site ) return the same value' );
$t->is( serialize($p4->getParam('wiki')), serialize($site), 'initPage()->wiki and $site return the same value' );

unset( $p2, $p3, $p4 );

$site2 = Peachy::newWiki( 'Tests/compwhiziitest' );

$t->ok( !$p1->redirectFollowed(), 'Page::redirectFollowed() returns false' );

$p1 = $site2->initPage( 'TestRedirect' );

$t->ok( $p1->redirectFollowed(), 'Page::redirectFollowed() returns true' );

$t->info( '2 - Checking for nobots on non-english wikis' );

$p1 = $site->initPage( 'User:X!/nobotscheck' );
$p2 = $site2->initPage( 'Nobotscheck' );

$t->is_strict( is_numeric($p1->edit( '{{nobots}} ' . rand(), 'Testing nobots capabilities' )), false, '{{nobots}} is enabled on enwiki' );
$t->is_strict( is_numeric($p2->edit( '{{nobots}} ' . rand(), 'Testing nobots capabilities' )), true, '{{nobots}} is disabled off of enwiki' );


$t->info( '3 - Checking for special compatibility' );

$p4 = new PageTest( $site, 'Special:Version' );

$t->is( $p4->get_special(), true, 'initPage(Special:Version) is a special page' );


$t->info( '4 - Checking history()' );

$p4 = new PageTest( $site, 'Barack Obama' );

$t->is( count($p4->history()), 1, 'history() correctly gets 1 revision' );
$t->cmp_ok( count($p4->history( null )), '>', 5000, 'history(null) correctly gets multiple pages' );

$t->info( '5 - Checking for special compatibility' );

$p4 = new PageTest( $site, 'A&W' );

$t->is( $p4->get_exists(), true, 'initPage() works with ampersands' );



