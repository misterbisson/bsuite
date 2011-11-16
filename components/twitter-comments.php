<?php

function ingest_twitter_comments()
{
	global $wpdb;

	// get the ID of the last ingested tweet
	if( strlen( get_option( 'bsuite_twitter_comments' )))
		$most_recent_tweet = get_option( 'bsuite_twitter_comments' );
	else
		add_option( 'bsuite_twitter_comments' , '' , '' , 'no' ); // add an empty option with the autoload disabled

	// prime HyperDB with a small write so we can make subsequent reads from the mast and avoid problems resulting from replication lag
	add_comment_meta( 1 , 'bsuite_twitter_comments' , time() );

	$tz_offset = get_option('gmt_offset'); // get the timezone offset

	// get a new search object
	$twitter_search = new Twitter_Search;
	$home_url = preg_replace( '|https?://|' , '' , home_url() );

	// run with it
	foreach( (array) $twitter_search->search ( array( 
		'q' => urlencode( $home_url ) , 
		'rpp' => 100 , 
		'since_id' => $most_recent_tweet ,
	)) as $tweet )
	{
		if( ! isset( $tweet->from_user->name))
			continue; // give up if the username lookup failed

		if( comment_id_by_meta( $tweet->id_str, 'tweet_id' ))
			continue; // skip the tweet if we've already imported it
		
		// map the URLs in the tweet to local posts
		// a tweet with links to multiple posts will only be added as a comment to the post with the highest post_id
		$found_post_ids = array();
		foreach( (array) find_urls( $tweet->text ) as $url )
		{
			// resolve the URL to its final destination 
			$url = follow_url( $url );

			// try to resolve the URL to a post id
			$post_id = url_to_postid( follow_url( $url )); // returns 0 if no match

			// some links to the blog don't resolve to post IDs, think of the home or tag pages.
			// hackish: those tweets get inserted against post id 0

			// make a list of the matching post IDs
			// check if the URL is part of this blog
			if(
				( function_exists( 'url_to_blogid' ) && ( (int) $wpdb->blogid == (int) url_to_blogid( $url ))) // if we have the function to map links to blog IDs _and_ the link is for this blog
				|| ! function_exists( 'url_to_blogid' ) // or, if we don't have that function, just continue 
			)
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
//			'comment_author_url' => 'http://twitter.com/'. $tweet->from_user->screen_name .'/status/'. $tweet->id_str,
			'comment_author_url' => 'http://twitter.com/'. $tweet->from_user->screen_name,
			'comment_content' => $tweet->text,
			'comment_type' => 'tweet',
			'comment_date' => date('Y-m-d H:i:s', strtotime( $tweet->created_at ) + (3600 * $tz_offset)),
		);

		// insert the comment
		$comment_id = wp_insert_comment( $comment );
		add_comment_meta( $comment_id , 'tweet_id' , $tweet->id_str ); // record the ID of the tweet
		add_comment_meta( $comment_id , 'tweet_rank' , $tweet->from_user->followers_count ); // get the follower count of the twitter user as a means to sort tweets by rank of the user
		comment_id_by_meta_update_cache( $comment_id , $tweet->id_str , 'tweet_id' );

		// update the comment count
		if( 0 < $post_id )
			wp_update_comment_count( $post_id );
		
		if ( get_option('comments_notify') )
			wp_notify_postauthor( $comment_id , 'comment' ); // only works for comments, so we fudge

		// possibly useful for determining rank of a tweet: 
		// $tweet->metadata->recent_retweets & $tweet->from_user->followers_count
	}

	// update the option with the last ingested tweet
	update_option( 'bsuite_twitter_comments' , $twitter_search->api_response->max_id_str );

	// delete the dummy comment meta we used to prime HyperDB earlier
	add_comment_meta( 1 , 'bsuite_twitter_comments' );

}
add_action( 'ingest_twitter_comments' , 'ingest_twitter_comments' );

function schedule_twitter_comments()
{
	if ( ! wp_next_scheduled( 'ingest_twitter_comments' ) )
		wp_schedule_event( time() , 'hourly' , 'ingest_twitter_comments' );
}
add_action( 'admin_head' , 'schedule_twitter_comments' );

function twitter_comments_admin_comment_types_dropdown( $types )
{
	$types['tweet'] = __( 'Tweets' );
	return $types;
}
add_filter( 'admin_comment_types_dropdown' , 'twitter_comments_admin_comment_types_dropdown' );
