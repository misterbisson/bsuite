<?php

class bSuite_Search
{
	function __construct()
	{
		if( get_option( 'bsuite_searchsmart' ))
			add_action( 'init' , array( $this , 'init' ));
	}

	function init()
	{
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->search_table = $wpdb->prefix . 'bsuite4_search';

		// delete posts from the search index when saving them
		add_filter( 'content_save_pre' , array( $this , 'content_save_pre' ));

		// update the search index via cron
		add_action( 'bsuite_interval' , array( $this, 'upindex_passive' ));

		// attach an action to apply filters to queries, except from the dashboard
		if( ! is_admin() )
			add_action( 'parse_query' , array( $this , 'parse_query' ) , 1 );
	}

	function parse_query( $query )
	{
		if( ! $query->is_search() )
			return $query;

//@TODO: also check the length of the query string and not apply filters if it's less than 4 chars (mysql default min ft word len)

		add_filter( 'posts_search' , array( $this , 'posts_search' ) , 5 , 2 );

		return $query;
	}

	function content_save_pre( $content )
	{
		// called when posts are edited or saved
		if( (int) $_POST['post_ID'] )
			$this->delete_post( (int) $_POST['post_ID'] );

		return $content;
	}

	function delete_post( $post_id )
	{
		$post_id = absint( $post_id );
		if( $post_id )
			return FALSE;

		$this->wpdb->get_results( "DELETE FROM $this->search_table WHERE post_id = $post_id" );
	}

	function filter_content_for_index( $content , $post_id )
	{
		// simple cleaning
		$content = html_entity_decode( stripslashes( $content ));

		// replace some html with newlines to prevent irrelevant proximity results
		$content = preg_replace( '|</?(p|br|li|h[1-9])[^>]*>|i' , "\n" , $content );

		// strip all html
		$content = wp_filter_nohtml_kses( $content );

		// strip shortcodes
		$content = preg_replace( '/\[.*?\]/', '', $content );

		// apply filters
		$content = apply_filters( 'bsuite_searchsmart_content' , $content , $post_id );

		// find words with accented characters, create transliterated versions of them
		$unaccented = array_diff( str_word_count( $content , 1 ), str_word_count( remove_accents( $content ) , 1 ));
		$content ."\n\n". implode( ' ', $unaccented );

		return $content;
	}

	function upindex()
	{

		// grab a batch of posts to work with
		$posts = $wpdb->get_results(
			"SELECT a.ID, a.post_content, a.post_title
				FROM $this->wpdb->posts a
				LEFT JOIN $this->search_table b ON a.ID = b.post_id
				WHERE a.post_status = 'publish'
				AND b.post_id IS NULL
				LIMIT 25"
		);

		// get the filtered content and construct an insert statement for each
		if( count( $posts ))
		{
			$insert = array();

			foreach( $posts as $post )
			{
				$insert[] = '('. (int) $post->ID .', "'. $this->wpdb->escape( $this->filter_content_for_index( $post->post_title ."\n\n". $post->post_content , $post->ID )) .'", "'. $this->wpdb->escape( $post->post_title ) .'")';
			}
		}
		else
		{
			return FALSE;
		}

		// insert into the search table
		if( count( $insert ))
		{
			$wpdb->get_results( 
				'REPLACE INTO '. $this->search_table .'
					(post_id, content, title) 
					VALUES '. implode( ',', $insert )
			);
		}

		return count( $posts );
	}

	function upindex_passive()
	{
		global $bsuite;

		if( ! $bsuite->get_lock( 'ftindexer' ))
			return;

		$this->upindex();

		return;
	}

	function posts_search( $search_sql , $wp_query )
	{
//echo "<h2>$search_sql</h2>";
/*
SELECT post_id, MATCH (content, title) AGAINST (". $wpdb->prepare( '%s', $searchphrase ) .") AS score 
FROM $this->search_table
WHERE MATCH (content, title) AGAINST (". $wpdb->prepare( '%s', $searchphrase ) .")
ORDER BY score DESC
LIMIT 0,1000
*/

	}

}
$bsuite_search = new bSuite_Search;
