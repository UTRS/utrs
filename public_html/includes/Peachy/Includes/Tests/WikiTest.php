<?php
require_once dirname(dirname(dirname(__FILE__))).'/Includes/lime.php';
 
$t = new lime_test();

class WikiTest extends Wiki {
	function getParam($param) { return $this->$param; }
}

$site = Peachy::newWiki( 'Tests/enwikitest', null, null, null, 'WikiTest' );

$t->info('1 - Construction');

$t->is( $site->getParam('base_url'), 'http://en.wikipedia.org/w/api.php', '__construct() sets correct base url' );
$t->cmp_ok( count( $site->getParam('extensions')), '>', 10, '__construct() sets correct extensions' );

$t->is( $site->is_logged_in(), true, 'User is logged in' );
$site->logout();
$t->is( $site->is_logged_in(), false, 'User is logged out' );

$site = Peachy::newWiki( 'Tests/enwikitest', null, null, null, 'WikiTest' );

$t->is( $site->is_logged_in(), true, 'User is logged back in' );


$t->cmp_ok( $site->get_mw_version(), '>=', '1.15', 'get_mw_version() works as expected' );

$t->is( $site->get_api_limit(), 4999, 'While logged out, limit is 4999' );

$t->is( $site->purge( array( 'Main Page', 'Foobar' ) ), true, 'purge() returns true' );

$t->cmp_ok( $site->recentchanges(), '>=', 15, 'recentchanges() works as expected' );

$t->cmp_ok( $site->search('foo'), '>=', 15, 'search() works as expected' );

$t->cmp_ok( $site->logs(), '>=', 15, 'search() works as expected' );

$t->cmp_ok( $site->allimages(), '>=', 15, 'allimages() works as expected' );

$t->cmp_ok( $site->allpages(), '>=', 15, 'allpages() works as expected' );

$t->cmp_ok( $site->alllinks(), '>=', 15, 'alllinks() works as expected' );

$t->cmp_ok( $site->allusers(), '>=', 15, 'allusers() works as expected' );

$t->cmp_ok( $site->categorymembers( 'Living people' ), '>=', 15, 'categorymembers() works as expected' );

