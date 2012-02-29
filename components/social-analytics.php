<?php

class bSuite_Social_Analytics
{

	function __construct()
	{
		// set table names
		global $wpdb;
//		$this->activity	= ( empty( $wpdb->base_prefix ) ? $wpdb->prefix : $wpdb->base_prefix ) .'bsocial_activity';
		$this->urlmap	= ( empty( $wpdb->base_prefix ) ? $wpdb->prefix : $wpdb->base_prefix ) .'bsocial_urlmap';
		$this->urlinfo	= ( empty( $wpdb->base_prefix ) ? $wpdb->prefix : $wpdb->base_prefix ) .'bsocial_urlinfo';
		$this->terms	= ( empty( $wpdb->base_prefix ) ? $wpdb->prefix : $wpdb->base_prefix ) .'bsocial_terms';
		$this->users	= ( empty( $wpdb->base_prefix ) ? $wpdb->prefix : $wpdb->base_prefix ) .'bsocial_users';
//		$this->pop		= ( empty( $wpdb->base_prefix ) ? $wpdb->prefix : $wpdb->base_prefix ) .'bsocial_pop';

		// get a wpdb object to work with, can be the default or a custom instance
		$this->get_db();

		// create the tables
//		$this->createtables();

		$this->type_array = array(
			0 => 'url_local',
			1 => 'url',
			2 => 'url_clean',
			3 => 'domain',
		);

	}

	function get_db()
	{
		if( ! empty( $this->db ))
			return $this->db;

		global $wpdb;

		if( defined( 'BSUITE_DB_NAME' ))
			$this->db = new wpdb( BSUITE_DB_USER, BSUITE_DB_PASSWORD, BSUITE_DB_NAME, BSUITE_DB_HOST );
		else
			$this->db = $wpdb;

		return $this->db;
	}

	function get_type( $type )
	{
		return array_search( $type , $this->type_array );
	}

	function get_term( $id )
	{
		if ( !$name = wp_cache_get( $id, 'bsocial_terms' ))
		{
			$name = $this->db->get_var( "SELECT name FROM $this->terms WHERE ". $this->db->prepare( "term_id = %s", (int) $id ));
			wp_cache_add( $id, $name, 'bsocial_terms', 0 );
		}
		return $name;
	}

	function is_term( $term )
	{
		$cache_key = md5( substr( $term, 0, 255 ) );
		if ( !$term_id = wp_cache_get( $cache_key, 'bsocial_termids' ))
		{
			$term_id = (int) $this->db->get_var( "SELECT term_id FROM $this->terms WHERE ". $this->db->prepare( "name = %s", substr( $term, 0, 255 )));
			wp_cache_add( $cache_key, $term_id, 'bsocial_termids', 0 );
		}
		return $term_id;
	}

	function insert_term( $term )
	{
		if ( !$term_id = $this->is_term( $term )) {
			if ( false === $this->db->insert( $this->terms, array( 'name' => $term )))
			{
				new WP_Error( 'db_insert_error', __( 'Could not insert term into the database' ), $this->db->last_error );
				return( 1 );
			}
			$term_id = (int) $this->db->insert_id;
		}
		return $term_id;
	}

	function get_user( $user_id )
	{
		if ( ! $user_name = wp_cache_get( $user_id, 'bsocial_users' ))
		{
			$user_name = $this->db->get_var( "SELECT name FROM $this->users WHERE ". $this->db->prepare( "user_id = %s", (int) $user_id ));
			wp_cache_add( $user_id, $user_name, 'bsocial_users', 0 );
		}
		return $user_name;
	}

	function is_user( $user_name )
	{
		$cache_key = md5( substr( $user_name, 0, 128 ) );
		if ( ! $user_id = wp_cache_get( $cache_key, 'bsocial_userids' ))
		{
			$user_id = (int) $this->db->get_var( "SELECT user_id FROM $this->users WHERE ". $this->db->prepare( "user_name = %s", substr( $user_name, 0, 128 )));
			wp_cache_add( $cache_key, $user_id, 'bsocial_userids', 0 );
		}
		return $user_id;
	}

	function insert_user( $user_name )
	{
		if ( ! $user_id = $this->is_user( $user_name ))
		{
			if ( FALSE === $this->db->insert( $this->users, array( 'user_name' => $user_name )))
			{
				new WP_Error('db_insert_error', __('Could not insert user into the database'), $this->db->last_error);
				return;
			}
			$user_id = (int) $this->db->insert_id;
		}
		return $user_id;
	}



	function get_urlinfo( $url_or_id )
	{
		// validate the input as either an object ID or URL
		if( is_numeric( $url_or_id ) && $this->get_term( $url_or_id ))
			$object_id = (int) $url_or_id;
		else if ( ! $object_id = $this->is_term( $url_or_id ))
			return FALSE;

		if ( ! $info = wp_cache_get( $object_id, 'bsocial_urlinfo' ))
		{
			$info = $this->db->get_results( "SELECT * FROM $this->urlinfo WHERE ". $this->db->prepare( "object_id = %s", (int) $object_id ));
			wp_cache_set( $object_id, $info, 'bsocial_urlinfo', 0 );
		}
		return $info;
	}

	function insert_urlinfo( $url_or_id )
	{
		if( ! $info = $this->get_urlinfo( $url_or_id ))
		{
			// validate the input as either an object ID or URL
			if( is_numeric( $url_or_id ) && ( $object_id = $url_or_id ) && ( ! $url = $this->get_term( $url_or_id )))
				return FALSE;
			else if( ! is_numeric( $url_or_id ) && ( $object_id = $this->insert_term( $url_or_id )))
				$url = $url_or_id;

			// scrape the data from the page
			if( ! $info = $this->scrape_url( $url ))
				return FALSE;

			// insert the data
			if( false === $this->db->insert( $this->urlinfo , array(
				'object_id' => $object_id,
				'url_date' => $info->url_date,
				'title' => $info->title,
				'description' => $info->description,
				'author_name' => $info->author_name,
				'author_url' => $info->author_url,
				'image_url' => $info->image_url,
			)))
			{
				$error = new WP_Error( 'db_insert_error', __( 'Could not insert urlinfo into the database' ), $this->db->last_error );
				return $error;
			}
		}
		return $info;
	}

	function lazy_insert_urlinfo( $url_or_id )
	{
		// validate the input as either an object ID or URL
		if( is_numeric( $url_or_id ) && $this->get_term( $url_or_id ))
			$object_id = (int) $url_or_id;
		else if ( ! $object_id = $this->insert_term( $url_or_id ))
			return FALSE;

		// hand the fetch off to a external process via HTTP request
		wp_remote_post( site_url( '/wp-admin/admin-ajax.php?action=bsocial_urlinfo&object_id='. $object_id ) , 
			array( 
				'timeout' => 5,
				'body' => array(
					'action' => 'bsocial_urlinfo',
					'screenname' => $object_id,
				),
		));
	}

	function scrape_url( $url )
	{
		$temp_results = wp_remote_get( $url );
		if ( is_wp_error( $temp_results ))
			return $temp_results;

		$html = wp_remote_retrieve_body( $temp_results );

		preg_match_all( '#<meta property="og:([^"]*)" content="([^"]*)"#' , $html , $matches );
		foreach( $matches[1] as $k => $v )
		{
			switch( $v )
			{
				case 'title':
					$data['title'] = $matches[2][ $k ];
					break;

				case 'description':
					$data['description'] = $matches[2][ $k ];
					break;

				case 'image':
					$data['image_url'] = $matches[2][ $k ];
					break;
			}
		}

		preg_match_all( "#<meta name='DC.date' content='([^']*)'#" , $html , $matches );
		$data['url_date'] = $matches[1][0];

		preg_match_all( '#<a rel="author" href="([^"]*)"[^>]*>([^<]*)</a>#' , $html , $matches );
		$data['author_name'] = $matches[2][0];
		$data['author_url'] = $matches[1][0];

		return (object) $data;
	}

	function user_history( $user_name )
	{
		$user_id = $this->is_user( $user_name );
		if( ! (int)  $user_id )
			return FALSE;

		$query = 
			"
			SELECT i.* , t.name AS url , `date`
			FROM
			(
				SELECT s.object_id , s.urlmap_date AS `date`
				FROM
				(
					SELECT object_id, urlmap_date
					FROM wp_bsocial_urlmap
					WHERE user_id = $user_id
					AND object_type IN (0)
					LIMIT 15
				) s
				ORDER BY s.urlmap_date DESC
			) h
			JOIN $this->terms t ON t.term_id = h.object_id
			LEFT JOIN $this->urlinfo i ON i.object_id = h.object_id
			";

		$object_ids = $this->db->get_results( $query );

		if( empty( $object_ids ))
			return FALSE;

		return apply_filters( 'bsocial_link_info' , $object_ids );
	}


	function get_related_by_user( $user_name )
	{
		$user_id = $this->is_user( $user_name );
		if( ! (int)  $user_id )
			return FALSE;

		$old_popular = $this->get_old_popular();
		$the_date = date( 'Y-m-d' , strtotime( '-11 days' ));

		$query = 
			"SELECT object_id
			FROM wp_bsocial_urlmap
			WHERE user_id = $user_id
			AND object_type IN (0,1,2)";
		$object_ids = $this->db->get_col( $query );

		$query = 
			"
			SELECT i.* , t.name AS url , users , hits
			FROM
			(
				SELECT ha.object_id , MIN(urlmap_date) AS the_date , GROUP_CONCAT( u.user_name ) as users , COUNT(*) AS hits
				FROM
				(
					SELECT user_id
					FROM $this->urlmap
					WHERE object_id IN ( ". implode( ',' , $object_ids ) ." )
					GROUP BY user_id DESC
					LIMIT 250
				) s
				JOIN $this->urlmap ha ON ha.user_id = s.user_id
				JOIN $this->users u ON u.user_id = ha.user_id
				WHERE 1=1
				AND ha.object_id NOT IN ( ". implode( ',' , array_merge( (array) $object_ids , (array) array_slice( $old_popular , 0 , 3 ))) ." )
				AND ha.object_type = 0
				GROUP BY ha.object_id
				ORDER BY the_date DESC
				LIMIT 0,50
			) h
			JOIN $this->terms t ON t.term_id = h.object_id
			LEFT JOIN $this->urlinfo i ON i.object_id = h.object_id
			ORDER BY hits DESC , url_date DESC
			";

		$urls = $this->db->get_results( $query );

		return apply_filters( 'bsocial_link_info' , $urls );
	}



	function get_related( $objects , $ignore = FALSE )
	{
		foreach( (array) $objects as $object )
		{
			// get the ID for the unmolested URL
			$object_ids[] = $this->insert_term( $object );

			// parse the URL and get IDs for various components of it
			$url = parse_url( $object );
			$url['host'] = preg_replace( '/www[^\.]*\./i', '', $url['host'] ); // remove www and similar components from the hostname
			$object_ids[] = $this->insert_term( $url['scheme'] .'://'. $url['host'] . ( isset( $url['port'] ) ? ':'. $url['port'] : '' ) . ( isset( $url['path'] ) ? $url['path'] : '/' ));
//			$object_ids[] = $this->insert_term( $url['host'] );
		}
		$object_ids = array_unique( $object_ids );

		if( $ignore )
			$ignore_sql = 'AND h.object_id NOT IN( '. implode( ',' , $object_ids ) .' )';
		else
			$ignore_sql = '';

		$query = 
			"
			SELECT i.* , t.name AS url , users , hits
			FROM
			(
				SELECT ha.object_id , MIN(urlmap_date) AS the_date , GROUP_CONCAT( u.user_name ) as users , COUNT(*) AS hits
				FROM
				(
					SELECT user_id
					FROM $this->urlmap
					WHERE object_id IN ( ". implode( ',' , $object_ids ) ." )
					GROUP BY user_id DESC
					LIMIT 250
				) s
				JOIN $this->urlmap ha ON ha.user_id = s.user_id
				JOIN $this->users u ON u.user_id = ha.user_id
				WHERE 1=1
				$ignore_sql
				AND ha.object_type = 0
				GROUP BY ha.object_id
				ORDER BY the_date DESC
				LIMIT 0,50
			) h
			JOIN $this->terms t ON t.term_id = h.object_id
			LEFT JOIN $this->urlinfo i ON i.object_id = h.object_id
			ORDER BY hits DESC , url_date DESC
			";

		$urls = $this->db->get_results( $query );

		return apply_filters( 'bsocial_link_info' , $urls );
	}

	function get_popular()
	{

		if ( $urls = wp_cache_get( 'popular', 'bsocial_analytics' ))
			return $urls;

		$the_date = date( 'Y-m-d' , strtotime( '-2 days' ));

		$query = 
			"
			SELECT i.* , t.name AS url , users , hits
			FROM
			(
				SELECT ha.object_id , MIN(urlmap_date) AS the_date , GROUP_CONCAT( u.user_name ) as users , COUNT(*) AS hits
				FROM $this->urlmap ha
				JOIN $this->users u ON u.user_id = ha.user_id
				WHERE 1=1
				AND urlmap_date >= '$the_date'
				AND ha.object_type = 0
				GROUP BY ha.object_id
				ORDER BY the_date DESC
				LIMIT 0,50
			) h
			JOIN $this->terms t ON t.term_id = h.object_id
			LEFT JOIN $this->urlinfo i ON i.object_id = h.object_id
			ORDER BY hits DESC , url_date DESC
			";

		$urls = $this->db->get_results( $query );
		$urls = apply_filters( 'bsocial_link_info' , $urls );

		wp_cache_add( 'popular' , $urls , 'bsocial_analytics' , 600 );

		return $urls;
	}

	function get_old_popular()
	{
		$the_date = date( 'Y-m-d' , strtotime( '-3 days' ));

		$query = 
			"
			SELECT ha.object_id , COUNT(*) AS hits
			FROM $this->urlmap ha
			WHERE 1=1
			AND urlmap_date <= '$the_date'
			AND ha.object_type = 0
			GROUP BY ha.object_id
			ORDER BY hits DESC
			LIMIT 0,25
			";

		$ids = $this->db->get_col( $query );

		return $ids;
	}


	function map_insert( $user_name , $date , $object )
	{
		$user_id = $this->insert_user( $user_name );
		$action_date = date( 'Y-m-d' , strtotime( $date ));

		// parse the URL and get IDs for various components of it
		$url = parse_url( $object );

		if( ! isset( $url['host'] ))
			return FALSE;

		$url['host'] = preg_replace( '/www[^\.]*\./i', '', $url['host'] ); // remove www and similar components from the hostname
		$clean_url = $url['scheme'] .'://'. $url['host'] . ( isset( $url['port'] ) ? ':'. $url['port'] : '' ) . ( isset( $url['path'] ) ? $url['path'] : '/' );
		$clean_object_id = $this->insert_term( $clean_url );
		$domain_object_id = $this->insert_term( $url['host'] );

		// get the ID for the unmolested URL
		$object_id = $this->insert_term( $object );

		if( empty( $user_id ) || empty( $action_date ) || empty( $object_id ))
			return FALSE;

		// insert the domain relationship
		$this->_map_insert( $user_id , $action_date , $domain_object_id , $this->get_type( 'domain' ) );

		// insert the cleaned URL
		if( $clean_object_id != $object_id ) // only insert the clean url if it differs from the regular URL
			$this->_map_insert( $user_id , $action_date , $clean_object_id , $this->get_type( 'url_clean' ) );

		// insert the URL relationship
		$local_domain = apply_filters( 'bsocial_local_domain' , parse_url( site_url() , PHP_URL_HOST ));
		if( preg_match( '/'. preg_quote( $local_domain , '/' ) .'/' , $url['host'] ))
		{
			return $this->_map_insert( $user_id , $action_date , $clean_object_id , $this->get_type( 'url_local' ) );
//			$this->lazy_insert_urlinfo( $clean_object_id );
			$this->insert_urlinfo( $clean_object_id );
		}
		else
		{
			return $this->_map_insert( $user_id , $action_date , $object_id , $this->get_type( 'url' ) );
		}
	}

	function _map_insert( $user_id , $action_date , $object_id , $object_type )
	{
		if( FALSE === $this->db->insert( $this->urlmap, array( 
			'user_id' 		=> $user_id,
			'urlmap_date' 	=> $action_date,
			'object_id' 	=> $object_id,
			'object_type' 	=> $object_type,
		)))
		{
			$error = new WP_Error( 'db_insert_error' , __('Could not insert map item into the database') , $this->db->last_error );
			return $error;
		}
		$map_id = (int) $this->db->insert_id;

		return $map_id;
	}






	function createtables()
	{
		global $wpdb;

		$charset_collate = '';
		if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') ) {
			if ( ! empty($wpdb->charset) )
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) )
				$charset_collate .= " COLLATE $wpdb->collate";
		}

		$old_wpdb = $wpdb;
		$wpdb = $this->db;

		require_once( ABSPATH . 'wp-admin/upgrade-functions.php' );

		dbDelta("
			CREATE TABLE $this->urlmap (
				urlmap_id BIGINT NOT NULL AUTO_INCREMENT ,
				user_id BIGINT NULL ,
				urlmap_date DATE NULL ,
				object_id BIGINT NULL ,
				object_type BIGINT NULL ,
				PRIMARY KEY (urlmap_id),
				UNIQUE INDEX the_unique (user_id ASC, urlmap_date ASC, object_type ASC, object_id ASC),
				KEY user_id (user_id ASC),
				KEY object_id (object_id ASC)
			) ENGINE=MyISAM $charset_collate
		");

		dbDelta("
			CREATE TABLE $this->urlinfo (
				object_id BIGINT NOT NULL ,
				url_date DATE NULL ,
				title text NULL ,
				description text NULL ,
				author_name text NULL ,
				author_url text NULL ,
				image_url text NULL ,
				PRIMARY KEY (object_id)
			) ENGINE=MyISAM $charset_collate
		");

		dbDelta("
			CREATE TABLE $this->terms (
				term_id bigint(20) NOT NULL auto_increment,
				name varchar(255) NOT NULL default '',
				status varchar(40) NOT NULL,
				PRIMARY KEY  (term_id),
				UNIQUE KEY name_uniq (name),
				KEY name (name(8)),
				KEY status (status(1))
			) ENGINE=MyISAM $charset_collate
		");

		dbDelta("
			CREATE TABLE $this->users (
				user_id bigint(20) NOT NULL auto_increment,
				user_name varchar(128) NOT NULL default '',
				PRIMARY KEY  (user_id),
				UNIQUE KEY name_uniq (user_name),
				KEY user_name (user_name(3))
			) ENGINE=MyISAM $charset_collate
		");

		$wpdb = $old_wpdb;

	}
}

// a function to return an instance of the bSuite_Social_Analytics class
function bsuite_sa()
{
	global $bsa;

	if( ! is_object( $bsa ))
		$bsa = new bSuite_Social_Analytics;

	return $bsa;
}

// map tweets matching a search query, queue the individual tweeters for more thorough mapping
function bsuite_sa_map_tweets_from_search( $search )
{

	if( empty( $search ))
		return FALSE;

	if( strlen( get_option( 'bsa-search-'. $search )))
		$most_recent_tweet = get_option( 'bsa-search-'. $search );
	else
		add_option( 'bsa-search-'. $search , '' , '' , 'no' ); // add an empty option with the autoload disabled

	$bsa = bsuite_sa();

	$twitter_search = new Twitter_Search;
	$twitter_search->get_user_info = FALSE; // suppress user lookups to avoid rate limits
	$twitter_search->search( array( 
		'q' => urlencode( $search ) , 
		'rpp' => 100 , 
		'since_id' => $most_recent_tweet ,
	));

	foreach( (array) $twitter_search->tweets() as $tweet )
	{
		// exit on api failure
		if( isset( $tweet->error ))
			return FALSE;

		// map any urls that exist in the tweet
		if( ! empty( $tweet->entities->urls ))
		{
			foreach( (array) $tweet->entities->urls as $url )
			{
				// allow some time for each URL (follow_url() can be time-consuming)
				set_time_limit( 30 );

				if( ! empty( $url->url ))
				{
					$resolved_url = follow_url( $url->url );
					if( ! empty( $resolved_url ))
						$bsa->map_insert( $tweet->from_user_id_str .'@twitter.id' , $tweet->created_at , $resolved_url );
				}
			}
		}

		// allow some more time to queue the user search
		set_time_limit( 90 );

		// queue the user who tweeted this for a more thorough index
		// fork an external process via HTTP request
		if( empty( $done[ $tweet->from_user ] ))
			wp_remote_post( site_url( '/wp-admin/admin-ajax.php?action=bsocial_map_twitter_user&screenname='. urlencode( $tweet->from_user )) , 
				array( 
					'timeout' => 5,
					'body' => array(
						'action' => 'bsocial_map_twitter_user',
						'screenname' => urlencode( $tweet->from_user ),
					),
			));

		// create a temporary log so that we don't re-requeue users who we've already queued within this cycle
		$done[ $tweet->from_user ] = TRUE;

	}

	// update the option for the most recent tweet
	update_option( 'bsa-search-'. $search , $twitter_search->api_response->max_id_str );

	return $twitter_search->api_response->max_id_str;
}


// map the most recent 200 tweets from a user (or most recent since we last checked)
function bsuite_sa_map_tweets_from_user( $screenname )
{

	if( empty( $screenname ))
		return FALSE;

	if( strlen( get_option( 'bsa-stream-'. $screenname )))
		$most_recent_tweet = get_option( 'bsa-stream-'. $screenname );
	else
		add_option( 'bsa-stream-'. $screenname , '' , '' , 'no' ); // add an empty option with the autoload disabled

	$bsa = bsuite_sa();
	$twitter_feed = new Twitter_User_Stream;

	$twitter_feed->stream( array( 
		'screen_name' => urlencode( $screenname ) , 
		'count' => 200 ,
		'since_id' => $most_recent_tweet ,
	));

	foreach( (array) $twitter_feed->tweets() as $tweet )
	{
		// exit on api failure
		if( isset( $tweet->error ))
			return FALSE;

		// allow some time for each URL (follow_url() can be time-consuming)
		set_time_limit( 30 );

		// iterate over each url in the tweet, if any
		if( ! empty( $tweet->entities->urls ))
		{
			foreach( (array) $tweet->entities->urls as $url )
			{
				if( ! empty( $url->url ))
				{
					$resolved_url = follow_url( $url->url );
					if( ! empty( $resolved_url ))
						$bsa->map_insert( $tweet->user->id_str .'@twitter.id' , $tweet->created_at , $resolved_url );
				}
			}
		}
	}

	// update the option for the most recent tweet
	update_option( 'bsa-stream-'. $screenname , $twitter_feed->max_id_str );

	return $twitter_feed->max_id_str;
}

add_action( 'wp_ajax_bsocial_map_twitter_user' , 'bsuite_sa_map_twitter_user' ); 
add_action( 'wp_ajax_nopriv_bsocial_map_twitter_user' , 'bsuite_sa_map_twitter_user' ); 
function bsuite_sa_map_twitter_user()
{
	ignore_user_abort( TRUE );
	nocache_headers();

	bsuite_sa_map_tweets_from_user( $_REQUEST['screenname'] );

	die;
}


add_action( 'wp_ajax_bsocial_urlinfo' , 'bsuite_sa_insert_urlinfo' ); 
add_action( 'wp_ajax_nopriv_bsocial_urlinfo' , 'bsuite_sa_insert_urlinfo' ); 
function bsuite_sa_insert_urlinfo()
{
	$bsa = bsuite_sa();

	if( empty( $_REQUEST['object_id'] ) || ! $bsa->get_term( $_REQUEST['object_id'] ))
		die;

	ignore_user_abort( TRUE );
	nocache_headers();

	$bsa->insert_urlinfo( $_REQUEST['object_id'] );

	die;
}





