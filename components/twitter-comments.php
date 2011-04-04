<?php

function ingest_twitter_comments()
{
	global $wpdb;

	$tz_offset = get_option('gmt_offset'); // get the timezone offset

	$twitter_search = new Twitter_Search;
	$home_url = preg_replace( '|https?://|' , '' , home_url() );

	// TODO: record the last ingested tweet id and only get tweets since then

	foreach( (array) $twitter_search->search ( array( 'q' => urlencode( $home_url ) , 'rpp' => 100 )) as $tweet )
	{
		if( ! isset( $tweet->from_user->name))
			continue; // give up if the username lookup failed

		if( comment_id_by_meta( $tweet->id, 'tweet_id'))
			continue; // skip the tweet if we've already imported it
		
		// map the URLs in the tweet to local posts
		$found_post_ids = array();
		foreach( (array) find_urls( $tweet->text ) as $url )
		{
			// resolve the URL to its final destination 
			$url = follow_url( $url );

			// try to resolve the URL to a post id
			$post_id = url_to_postid( follow_url( $url )); // returns 0 if no match

			// some links to the blog don't resolve to post IDs, think of the home or tag pages.
			// hackish: those tweets get inserted against post id 0

			// make a list of the match post IDs
			// check if the URL is part of this blog
			if( (int) $wpdb->blogid == (int) url_to_blogid( $url ))
			{
				$found_post_ids[] = (int) $post_id;
			}
		}

		// do any of the links point to this blog?
		if( ! count( $found_post_ids ))
			continue; // no matching links

		// get the highest found post id
		sort( $found_post_ids ); 
		$post_id = array_pop( $found_post_ids );

		// create the comment array
		$comment = array(
			'comment_post_ID' => $post_id,
			'comment_author' => $tweet->from_user->name,
			'comment_author_email' => $tweet->from_user->id_str . '@twitter.id',
			'comment_author_url' => 'http://twitter.com/'. $tweet->from_user->screen_name,
			'comment_content' => $tweet->text,
			'comment_type' => 'tweet',
			'comment_date' => date('Y-m-d H:i:s', strtotime( $tweet->created_at ) + (3600 * $tz_offset)),
		);

		// insert the comment
		$comment_id = wp_insert_comment( $comment );
		add_comment_meta($comment_id, 'tweet_id', $tweet->id);
		comment_id_by_meta_update_cache( $comment_id , $tweet->id , 'tweet_id' );

		// update the comment count
		if( 0 < $post_id )
			wp_update_comment_count( $post_id );

		// possibly useful for determining rank of a tweet: 
		// $tweet->metadata->recent_retweets & $tweet->from_user->followers_count
	}
}
add_action( 'ingest_twitter_comments' , 'ingest_twitter_comments' );

function schedule_twitter_comments()
{
	if ( ! wp_next_scheduled( 'twitter_comments' ) )
		wp_schedule_event( time() , 'hourly' , 'ingest_twitter_comments' );
}
add_action( 'admin_head' , 'schedule_twitter_comments' );

function twitter_comments_admin_comment_types_dropdown( $types )
{
	$types['tweet'] = __( 'Tweets' );
	return $types;
}
add_filter( 'admin_comment_types_dropdown' , 'twitter_comments_admin_comment_types_dropdown' );
