<?php

function url_to_blogid( $url )
{
	if( ! is_multisite() )
		return FALSE;

	global $wpdb , $base;
	$url = parse_url( $url );

	if ( is_subdomain_install() )
	{
		return get_blog_id_from_url( $url['host'] , '/' );
	}
	else
	{
		// get the likely blog path
		$path = explode( '/' , ltrim( substr( $url['path'] , strlen( $base )) , '/' ));
		$path = empty( $path[0] ) ? '/' : '/'. $path[0] .'/';

		// get all blog paths for this domain
		if( ! $paths = wp_cache_get( $url['host'] , 'paths-for-domain' ))
		{
			$paths = $wpdb->get_col( "SELECT path FROM $wpdb->blogs WHERE domain = '". $wpdb->escape( $url['host'] ) ."' /* url_to_blogid */" );
			wp_cache_set( $url['host'] , $paths , 'paths-for-domain' , 3607 ); // cache it for an hour
		}

		// chech if the given path is among the known paths
		// allows us to differentiate between paths of the main blog and those of sub-blogs
		$path = in_array( $path , $paths ) ? $path : '/';

		return get_blog_id_from_url( $url['host'] , $path );
	}
}

function find_urls( $text )
{
	// nice regex thanks to John Gruber http://daringfireball.net/2010/07/improved_regex_for_matching_urls
	preg_match_all( '#(?i)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?гхрсту]))#', $text, $urls );

	return $urls[0];
}

function follow_url( $location , $verbose = FALSE , $refresh = FALSE )
{

	if ( $refresh || ( ! $trail = wp_cache_get( (string) $location , 'follow_url' )))
	{
		$headers = get_headers( $location );
	
		$trail = array();
		$destination = $location;

		foreach( (array) $headers as $header )
		{
			if( 0 === stripos( $header , 'HTTP' ))
			{
				preg_match( '/ [1-5][0-9][0-9] /' , $header , $matches );
				$trail[] = array( 'location' => $destination , 'response' => trim( $matches[0] ));
			}
	
			if( 0 === stripos( $header , 'Location' ))
				$destination = array_pop( find_urls( $header ));
		}

		wp_cache_set( (string) $location , $trail, 'follow_url' , 3607); // cache for an hour
	}

	if( $verbose )
		return $trail;
	else
		return $trail[ count( $trail ) - 1 ]['location'];
}

function comment_id_by_meta( $metavalue , $metakey )
{
	global $wpdb;

	if( ! $comment_id = wp_cache_get( (string) $metakey .':'. (string) $metavalue , 'comment_id_by_meta' ) )
	{
		$comment_id = $wpdb->get_var( $wpdb->prepare( 'SELECT comment_id FROM ' . $wpdb->commentmeta . ' WHERE meta_key = %s AND meta_value = %s',  $metakey, $metavalue ));

		wp_cache_set( (string) $metakey .':'. (string) $metavalue , $comment_id , 'comment_id_by_meta' );
	}

	return $comment_id; 
}

function comment_id_by_meta_update_cache( $comment_id , $metavalue , $metakey )
{
	if( 0 < $comment_id )
		return;

	if( ( ! $metavalue ) && ( ! $metakey ))
		return;

	wp_cache_set( (string) $metakey .':'. (string) $metavalue , (int) $comment_id , 'comment_id_by_meta' );
}

function comment_id_by_meta_delete_cache( $comment_id )
{
	foreach( (array) get_metadata( 'comment' , $comment_id ) as $metakey => $metavalues )
	{
		foreach( $metavalues as $metavalue )
			wp_cache_delete( (string) $metakey .':'. (string) $metavalue , 'comment_id_by_meta' );
	}
}
add_action( 'delete_comment' , 'comment_id_by_meta_delete_cache' );

function json_int_to_string( $string )
{
	//32-bit PHP doesn't play nicely with the large ints FB returns, so we
	//encapsulate large ints in double-quotes to force them to be strings
	//http://stackoverflow.com/questions/2907806/handling-big-user-ids-returned-by-fql-in-php
	return preg_replace( '/:(\d+)/' , ':"${1}"' , $string );
}

// Show cron array for debugging
function show_cron()
{
	if (current_user_can('manage_options')) {
		echo '<pre>' .  print_r(_get_cron_array(), true) . '</pre>';  
	};
	exit; 
}
add_action( 'wp_ajax_show_cron', 'show_cron' ); 

