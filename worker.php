<?php
// get prereqs
require('../../../wp-config.php');
global $wpdb, $bsuite;

// get or start a session
if( $_COOKIE["bsuite_session"] )
	$session = $_COOKIE["bsuite_session"];
else
	$session = md5( uniqid( rand(),Êtrue ));

// set or update the cookie to expire 30 minutes from now
setcookie ( 'bsuite_session', $session, time()+1800, '/' );

// create an array of 'extra' details
$in_extra = array(  'ip' => $_SERVER["REMOTE_ADDR"], 'br' => $_REQUEST['br'],  'bb' => $_REQUEST['bb'],  'bl' => $_REQUEST['bl'],  'bc' => $_REQUEST['bc']);

// insert the hit
$wpdb->insert( $bsuite->hits_incoming, array( 'in_type' => '0', 'in_session' => $session, 'in_to' => $_SERVER['HTTP_REFERER'] , 'in_from' => $_REQUEST['pr'], 'in_extra' => serialize( $in_extra )));

// output useful data
/*
if( strlen($_REQUEST['pr'] ))
	print_r( $bstat->get_search_terms( $_REQUEST['pr'] ));
*/
?>