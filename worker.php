<?php
// get prereqs
require('../../../wp-config.php');
global $wpdb, $bsuite;

// send headers
//@header( 'Content-Type: application/json; charset='. get_option('blog_charset') );
nocache_headers();

// get or start a session
if( $_COOKIE["bsuite_session"] )
	$session = $_COOKIE["bsuite_session"];
else
	$session = md5( uniqid( rand(),�true ));

// set or update the cookie to expire 30 minutes from now
setcookie ( 'bsuite_session', $session, time()+1800, '/' );

// create an array of 'extra' details
$in_extra = array(  'ip' => $_SERVER["REMOTE_ADDR"], 'br' => $_REQUEST['br'],  'bb' => $_REQUEST['bb'],  'bl' => $_REQUEST['bl'],  'bc' => $_REQUEST['bc'],  'ba' => urlencode( $_SERVER['HTTP_USER_AGENT'] ) );

// insert the hit
$wpdb->insert( $bsuite->hits_incoming, array( 'in_type' => '0', 'in_session' => $session, 'in_to' => $_SERVER['HTTP_REFERER'] , 'in_from' => $_REQUEST['pr'], 'in_extra' => serialize( $in_extra )));

// output useful data
if(function_exists( 'json_encode' )){
	if( $searchterms = $bsuite->get_search_terms( $_REQUEST['pr'] )){
//		echo 'bsuite_highlight('. json_encode( array( 'history', 'book' ) ) .");\n";
		echo 'bsuite_highlight('. json_encode( $searchterms ) .");\n";

		foreach( $wpdb->get_col( $bsuite->searchsmart_query( implode( $searchterms, ' ' ))) as $post)
			$related_posts[] = '<a href="'. get_permalink( $post ) .'" title="Permalink to related post: '. get_the_title( $post ) .'">'.  get_the_title( $post ) .'</a>';
/*
		if( count( $related_posts ))
			echo 'bsuite_related_posts('. json_encode( $related_posts ) .");\n";
*/
	}

}
//phpinfo();
?>