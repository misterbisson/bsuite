<?php
/*
 * Facebook Comment Integration
 * Queries Facebook for comments on a particular post
 * and adds them into the WordPress comment loop for that
 * post. 
 * Author: Vasken Hauri
 */

function ingest_fb_comments( $post_id = NULL )
{

	if( ( ! $post_id ) || ( ! get_post( $post_id )))
		return;

	$last_check = get_post_meta( $post_id , '_fb_comment_ingestion_last', TRUE );
	$last_check = 1 > $last_check ? $last_check - 421 : 0;

	$api_root = 'https://api.facebook.com/method/fql.query?query=';
	$comment_limit = 25;

	// get the access token
	$url = 'https://graph.facebook.com/oauth/access_token?client_id=' . FBJS_APP_ID .
			'&client_secret=' . FBJS_APP_SECRET .
			'&grant_type=client_credentials';
	$token = fb_api_fetch( $url, $post_id );

	// get the link ID for this post URL
	$url = $api_root . urlencode( 'SELECT comments_fbid FROM link_stat WHERE url="' . get_permalink( $post_id )) .'"&format=json';
	$response = fb_api_fetch( $url , $post_id );
	$comments_fbid = json_decode( json_int_to_string( $response ));
	$comments_fbid = $comments_fbid[0]->comments_fbid;

	// Who are you? FB doesn't know about this URL
	if( empty( $comments_fbid ))
	{
		update_post_meta( $post_id , '_fb_comment_ingestion_last' , time() );
		return;
	}

	// get the top-level comments on this post	
	$comment_query = 'SELECT post_fbid , fromid , time , text , id , username FROM comment WHERE object_id = "' . $comments_fbid .'" ORDER BY time DESC LIMIT '. $comment_limit;
	$response = fb_api_fetch( $api_root . urlencode( $comment_query ) .'&format=json' , $post_id );
	$fb_comments = json_decode( json_int_to_string( $response ));	

	// get replies to those comments
	$reply_query = 'SELECT fromid , time , text , id , username FROM comment WHERE object_id in (SELECT post_fbid FROM comment WHERE object_id = "' . $comments_fbid . '" ORDER BY time DESC LIMIT '. $comment_limit .')';
	$response = fb_api_fetch( $api_root . urlencode( $reply_query ) .'&format=json' , $post_id );
	$replies = json_decode( json_int_to_string( $response ));

	// merge the comments and replies
	if( is_array( $replies ))
		$fb_comments = array_merge( (array) $fb_comments , (array) $replies );

	// these are not the comments you were looking for. duh, there are no comments
	if( ! count( $fb_comments ))
	{
		update_post_meta( $post_id , '_fb_comment_ingestion_last' , time() );
		return;
	}

	// get the user info for those comments
	// make an array of the user IDs from the comments
	foreach( (array) $fb_comments as $fb_comment )
		$uids[] = $fb_comment->fromid;
	$uids = implode( ',' , (array) $uids );

	// query the API for details on those IDs
	$url = 'https://api.facebook.com/method/users.getInfo?' . $token .
			'&uids=' . $uids .
			'&format=json&fields=name,pic_square';
	$response = fb_api_fetch( $url , $post_id );
	$names = json_decode( json_int_to_string( $response ));

	// make a happy array that maps user ID to details
	foreach( (array) $names as $name )
		$uids_to_names[ $name->uid ] = $name->name;

	// iterate over all the comments and insert them
	foreach( $fb_comments as $fb_comment )
	{
		if( ! comment_id_by_meta( $fb_comment->id , 'fb_comment_id' ))
		{	

			$fb_comment->username = $uids_to_names[ $fb_comment->fromid ];

			preg_match( '/[^_]*/' , $fb_comment->id , $fb_parent_comment_id );
			$fb_parent_comment_id = $fb_parent_comment_id[0];

			$wp_commment = array(
				'comment_post_ID' => $post_id,
				'comment_author' => $fb_comment->username,
				'comment_author_email' => $fb_comment->fromid . '@facebook.id',
				'comment_author_url' => 'http://facebook.com/profile.php?id=' . $fb_comment->fromid,
				'comment_content' => $fb_comment->text,
				'comment_type' => 'fbcomment',
				'comment_parent' => comment_id_by_meta( $fb_parent_comment_id , 'fb_comment_post_id' ),
				'comment_date' => date('Y-m-d H:i:s', $fb_comment->time + ( 3600 * $tz_offset )),
			);

			// insert the comment and return the comment ID
			$comment_id = wp_insert_comment( $wp_commment );

			// add the db comment id meta
			add_comment_meta( $comment_id, 'fb_comment_id', $fb_comment->id );
			comment_id_by_meta_update_cache( $comment_id , $fb_comment->id , 'fb_comment_id' );

			// add the fb comment post id meta, allows relating child comments to their parents
			add_comment_meta( $comment_id, 'fb_comment_post_id', $fb_comment->post_fbid );
			comment_id_by_meta_update_cache( $comment_id , $fb_comment->post_fbid , 'fb_comment_post_id' );

			if ( get_option('comments_notify') )
				wp_notify_postauthor( $comment_id , 'comment' ); //hardcoded to type 'comment'
		}	
	}

	// Vasken was here, buster
	update_post_meta( $post_id , '_fb_comment_ingestion_last' , time() );

	// update the comment count
	wp_update_comment_count( $post_id );
}
add_action('ingest_fb_comments', 'ingest_fb_comments');

function fb_api_fetch($url, $post_id)
{

	$response = wp_remote_get( $url );

	if( is_wp_error( $response ) ) {
		//schedule no more than one check per post in 3 minutes
		wp_schedule_single_event( time() + 181, 'ingest_fb_comments', array( $post_id ));
		die;
	}else{
		return $response['body'];
	}
}

function fb_comment_do_ajax()
{
	if( empty( $_REQUEST['post_id'] ))
		return;

	$post_id = (int) $_REQUEST['post_id'];

	//schedule no more than one check per post in 3 minutes
	wp_schedule_single_event( time() + 181, 'ingest_fb_comments', array( $post_id ));

	echo 'Scheduled FB comment update for ' . $post_id;	
	die;
}
add_action('wp_ajax_new_fb_comment', 'fb_comment_do_ajax');
add_action('wp_ajax_nopriv_new_fb_comment', 'fb_comment_do_ajax');

function fb_check_comments( $content )
{
	global $post;

	// check the last time the fb comments were checked
	// refresh the FB comments after 7 days
	$last_check = get_post_meta( $post->ID , '_fb_comment_ingestion_last', TRUE );
	if( is_single() && ( 604801 < ( time() - $last_check )) ) 
		wp_schedule_single_event( time() + 181, 'ingest_fb_comments', array( $post->ID ));

	return $content;
}
add_action( 'the_content' , 'fb_check_comments' );

function fb_comments_admin_comment_types_dropdown( $types )
{
	$types['fbcomment'] = __( 'Facebook Comments' );
	return $types;
}
add_filter( 'admin_comment_types_dropdown' , 'fb_comments_admin_comment_types_dropdown' );
