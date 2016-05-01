<?php

require_once dirname(dirname(dirname(__FILE__))).'/Includes/lime.php';

class UserTest extends User {
	function getParam($param) { return $this->$param; }
}

$t = new lime_test();

$site = Peachy::newWiki( 'Tests/enwikitest' );

$u1 = $site->initUser( 'Jimbo Wales' );
$u2 = initUser( 'Jimbo Wales' );
$u3 = new User( $site, 'Jimbo Wales' );
$u4 = new UserTest( $site, 'jimbo_Wales' );

$t->info('1 - Initialization');

$t->is( serialize($u1), serialize($u2), 'Wiki::initUser() and initUser() return the same value' );
$t->is( serialize($u1), serialize($u3), 'Wiki::initUser() and new User( $site ) return the same value' );
$t->is( serialize($u4->getParam('wiki')), serialize($site), 'initUser()->wiki and $site return the same value' );

$u1 = $u4;
unset( $u2, $u3, $u4 );

$t->info('2 - Construction');

$u2 = new UserTest( $site, '127.0.0.1' );

$t->is( $u1->getParam('username'), 'Jimbo Wales', '__construct() normalizes username' );
$t->is( $u2->getParam('ip'), true, '__construct() detects IPs' );

$u2 = new UserTest( $site, 'ThisUserDoesNotExist12345Peachy' ); //hopefully no one ever makes that
$t->is( $u2->exists(), false, '__construct() detects non-existent users' );

$u2 = new UserTest( $site, '<!@#$:%^&>' );
$t->is( $u2->exists(), false, '__construct() detects invalid usernames' );


$u2 = new UserTest( $site, 'Grawp' );
$t->info('3 - get*(), is*() functions');

$t->is( $u1->is_blocked(), false, 'is_blocked() correctly detects blocked editors #1' );
$t->is( $u2->is_blocked(), true, 'is_blocked() correctly detects blocked editors #2' );

$contribs = $u2->get_contribs( false );
$first = array_shift($contribs);
$last = array_pop($contribs);

$t->cmp_ok( $first['timestamp'], '<', $last['timestamp'], 'get_contribs(false) gets oldest first' );

$contribs = $u1->get_contribs( false, 40 );
$t->cmp_ok( count($contribs), '<=', 40, 'get_contribs(false,40) returns only 40 values' );

$contribs = $u1->get_contribs();
$t->cmp_ok( count($contribs), '>', 5000, 'get_contribs() works as expected' );


$u2 = new UserTest( $site, 'Example' );
$t->cmp_ok( $u2->get_editcount(), '>=', 1, 'get_editcount() returns correct values' );
$t->cmp_ok( count($u2->get_contribs()), '>=', 1, 'get_contribs() returns correct values' );

$u2 = new UserTest( $site, 'Emachman (usurped)' );
$t->is( $u2->has_email(), false, 'has_email() works correctly #1' );
$u2 = new UserTest( $site, 'X!' );
$t->is( $u2->has_email(), true, 'has_email() works correctly #1' );

$t->cmp_ok( strtotime($u2->get_registration()), '<', strtotime('01-01-2007'), 'get_registration() works correctly' );

$t->is( serialize($u2->getPageClass()), serialize(initPage('User:X!')), 'getPageClass() works correctly' );

$site2 = Peachy::newWiki( 'Tests/compwhiziitest' );
$site2_u = $site2->initUser( 'UserTestAccount' );

$t->info('4 - User action functions' );

$t->is_strict( $site2_u->block(), true, 'block() returns true' );
sleep(5);
$t->is( $site2_u->is_blocked(), true, 'is_blocked() returns true' );
$t->is_strict( $site2_u->unblock(), true, 'unblock() works as default' );
sleep(5);
$t->is( $site2_u->is_blocked(), false, 'is_blocked() returns false' );

$site2_u->block( "Because I can" );
sleep(5);
$blockinfo = $site2_u->get_blockinfo();

$t->is( $blockinfo['reason'], "Because I can", 'get_blockinfo() works as expected' );
$site2_u->unblock( "Because I can't" );

$t->is_strict( $site2_u->userrights( array( 'sysop' ), array(), 'Because I can' ), true, 'userrights() returns true' );
$t->is_strict( $site2_u->userrights( array(), array( 'sysop' ), 'Because I can' ), true, 'userrights() returns true' );
