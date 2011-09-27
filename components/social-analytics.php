<?php

class bSuite_Social_Analytics
{

	function __construct()
	{
		global $wpdb;
//		$this->activity	= ( empty( $wpdb->base_prefix ) ? $wpdb->prefix : $wpdb->base_prefix ) .'bsocial_activity';
		$this->urlmap	= ( empty( $wpdb->base_prefix ) ? $wpdb->prefix : $wpdb->base_prefix ) .'bsocial_urlmap';
//		$this->urlinfo	= ( empty( $wpdb->base_prefix ) ? $wpdb->prefix : $wpdb->base_prefix ) .'bsocial_urlinfo';
		$this->terms	= ( empty( $wpdb->base_prefix ) ? $wpdb->prefix : $wpdb->base_prefix ) .'bsocial_terms';
		$this->users	= ( empty( $wpdb->base_prefix ) ? $wpdb->prefix : $wpdb->base_prefix ) .'bsocial_users';
//		$this->pop		= ( empty( $wpdb->base_prefix ) ? $wpdb->prefix : $wpdb->base_prefix ) .'bsocial_pop';

		$this->type_array = array(
			1 => 'url',
			2 => 'cleanurl',
			3 => 'domain',
		);

		$this->createtables();
	}

	function get_type( $type )
	{
		return array_search( $type , $this->type_array );
	}

	function get_term( $id )
	{
		global $wpdb;

		if ( !$name = wp_cache_get( $id, 'bsocial_terms' ))
		{
			$name = $wpdb->get_var( "SELECT name FROM $this->terms WHERE ". $wpdb->prepare( "term_id = %s", (int) $id ));
			wp_cache_add( $id, $name, 'bsocial_terms', 0 );
		}
		return $name;
	}

	function is_term( $term )
	{
		global $wpdb;

		$cache_key = md5( substr( $term, 0, 255 ) );
		if ( !$term_id = wp_cache_get( $cache_key, 'bsocial_termids' ))
		{
			$term_id = (int) $wpdb->get_var( "SELECT term_id FROM $this->terms WHERE ". $wpdb->prepare( "name = %s", substr( $term, 0, 255 )));
			wp_cache_add( $cache_key, $term_id, 'bsocial_termids', 0 );
		}
		return $term_id;
	}

	function insert_term( $term )
	{
		global $wpdb;

		if ( !$term_id = $this->is_term( $term )) {
			if ( false === $wpdb->insert( $this->terms, array( 'name' => $term )))
			{
				new WP_Error( 'db_insert_error', __( 'Could not insert term into the database' ), $wpdb->last_error );
				return( 1 );
			}
			$term_id = (int) $wpdb->insert_id;
		}
		return $term_id;
	}

	function get_user( $user_id )
	{
		global $wpdb;

		if ( ! $user_name = wp_cache_get( $user_id, 'bsocial_users' ))
		{
			$user_name = $wpdb->get_var( "SELECT name FROM $this->users WHERE ". $wpdb->prepare( "user_id = %s", (int) $user_id ));
			wp_cache_add( $user_id, $user_name, 'bsocial_users', 0 );
		}
		return $user_name;
	}

	function is_user( $user_name )
	{
		global $wpdb;

		$cache_key = md5( substr( $user_name, 0, 128 ) );
		if ( ! $term_id = wp_cache_get( $cache_key, 'bsocial_userids' ))
		{
			$user_id = (int) $wpdb->get_var( "SELECT user_id FROM $this->users WHERE ". $wpdb->prepare( "user_name = %s", substr( $user_name, 0, 128 )));
			wp_cache_add( $cache_key, $user_id, 'bsocial_userids', 0 );
		}
		return $user_id;
	}

	function insert_user( $user_name )
	{
		global $wpdb;

		if ( ! $user_id = $this->is_user( $user_name ))
		{
			if ( FALSE === $wpdb->insert( $this->users, array( 'user_name' => $user_name )))
			{
				new WP_Error('db_insert_error', __('Could not insert term into the database'), $wpdb->last_error);
				return;
			}
			$user_id = (int) $wpdb->insert_id;
		}
		return $user_id;
	}






	function map_insert( $user_name , $date , $object )
	{
		$user_id = $this->insert_user( $user_name );
		$action_date = date( 'Y-m-d' , strtotime( $date ));

		// get the ID for the unmolested URL
		$object_id = $this->insert_term( $object );

		// parse the URL and get IDs for various components of it
		$url = parse_url( $object );
		$url['host'] = preg_replace( '/www[^\.]*\./i', '', $url['host'] ); // remove www and similar components from the hostname
		$clean_object_id = $this->insert_term( $url['scheme'] .'://'. $url['host'] . ( isset( $url['port'] ) ? ':'. $url['port'] : '' ) . ( isset( $url['path'] ) ? $url['path'] : '/' ));
		$domain_object_id = $this->insert_term( $url['host'] );

		if( empty( $user_id ) || empty( $action_date ) || empty( $object_id ))
			return FALSE;

		// insert the domain relationship
		$this->_map_insert( $user_id , $action_date , $domain_object_id , $this->get_type( 'domain' ) );

		// insert the cleaned URL
		if( $clean_object_id != $object_id ) // only insert the clean url if it differs from the regular URL
			$this->_map_insert( $user_id , $action_date , $clean_object_id , $this->get_type( 'cleanurl' ) );

		// insert the URL relationship
		return $this->_map_insert( $user_id , $action_date , $object_id , $this->get_type( 'url' ) );
	}

	function _map_insert( $user_id , $action_date , $object_id , $object_type )
	{
		global $wpdb;

		if( FALSE === $wpdb->insert( $this->urlmap, array( 
			'user_id' 		=> $user_id,
			'urlmap_date' 	=> $action_date,
			'object_id' 	=> $object_id,
			'object_type' 	=> $object_type,
		)))
		{
			$error = new WP_Error( 'db_insert_error' , __('Could not insert map item into the database') , $wpdb->last_error );
			return $error;
		}
		$map_id = (int) $wpdb->insert_id;

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

	}
}