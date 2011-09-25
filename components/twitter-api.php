<?php

/*
 * Twitter_Search class
 * 
 * Search Twitter with a given term or phrase
 * Example: $twitter_search->search ( array( 'q' => 'search phrase' )) 
 * 
 * @author Casey Bisson
 */
class Twitter_Search
{
	function tweets()
	{
		if( ! empty( $this->api_response->results ))
			return $this->api_response->results;
		else
			return FALSE;
	}

	function next()
	{
		if( ! empty( $this->api_response->next_page ))
			$this->search( $this->args , 'next' );
		else
			return FALSE;
	}

	function refresh()
	{
		if( ! empty( $this->api_response->refresh_url ))
			$this->search( $this->args , 'refresh' );
		else
			return FALSE;
	}

	function search( $args , $method = 'search' )
	{
		// parse the method
		switch( $method )
		{
			case 'next':
			case 'next_page':
				if( ! empty( $this->api_response->next_page ))
				{
					$query_url = 'http://search.twitter.com/search.json' . $this->api_response->next_page;
					break;
				}
			
			case 'refresh':
				if( ! empty( $this->api_response->refresh_url ))
				{
					$query_url = 'http://search.twitter.com/search.json' . $this->api_response->refresh_url;
					break;
				}

			case 'search':
			default:
				$defaults = array(
					'q' => urlencode( home_url() ),
					'rpp' => 10,
					'result_type' => 'recent',
					'page' => 1,
					'since_id' => FALSE,
					'lang' => FALSE,
					'locale' => FALSE,
					'until' => FALSE,
					'geocode' => FALSE,
					'show_user' => FALSE,
				);
				$args = wp_parse_args( $args, $defaults );

				// save the args
				$this->args = $args;

				$query_url = add_query_arg( $args , 'http://search.twitter.com/search.json' );
		}

		$temp_results = wp_remote_get( $query_url );
		if ( is_wp_error( $temp_results ))
		{
			$this->error = $temp_results; 
			return FALSE;
		}

		$this->api_response = json_decode( wp_remote_retrieve_body( $temp_results ));
		unset( $temp_results );

		if( ! empty( $this->api_response->error ))
		{
			$this->error = $this->api_response; 
			unset( $this->api_response );
			return FALSE;
		}

		foreach( $this->api_response->results as $result )
		{
			// we can't rely on the user_ids in the result, so we do a name lookup and unset the unreliable data.
			// http://code.google.com/p/twitter-api/issues/detail?id=214
			$result->from_user = twitter_user_info( $result->from_user );
			unset( $result->from_user_id_str , $result->from_user_id , $result->to_user_id_str , $result->to_user_id , $result->from_user->status );

			$this->api_response->min_id = $result->id;
			$this->api_response->min_id_str = $result->id_str;
		}

		return $this->api_response->results;
	}
}

/*
 * twitter_user_info
 * 
 * Get the public information for a given user
 * Example: twitter_user_info( 'misterbisson' ) 
 * 
 * @author Casey Bisson
 */
function twitter_user_info( $screen_name , $by = 'screen_name' )
{
	// Look up info about the twitter user by their screen name or ID
	// Note: the ID here is not compatible with the user ID returned from the search API. This is a Twitter limitation.
	// method docs: http://apiwiki.twitter.com/Twitter-REST-API-Method%3A-users%C2%A0show
	// useful: $user->name, $user->screen_name, $user->id_str, $user->followers_count 

	// are we searching by screen name or ID?
	$by = in_array( $by , array( 'screen_name' , 'id' )) ? $by : 'screen_name';

	// check the cache for the user info
	if ( ! $user = wp_cache_get( (string) $screen_name , 'twitter_'. $by ))
	{
		// check Twitter for the user info
		$temp_results = wp_remote_get( 'http://api.twitter.com/1/users/show.json?'. $by .'='. urlencode( $screen_name ) );
		if ( is_wp_error( $temp_results ))
			return FALSE;

		$user = json_decode( wp_remote_retrieve_body( $temp_results ));

		if( empty( $user->error ))
			wp_cache_set( (string) $screen_name , $user, 'twitter_screen_name' , 604801 ); // cache for 7 days
	}

	return $user;
}

/*
 * Twitter_User_Stream class
 * 
 * Get the public Twitter history for a given user
 * Example: $twitter_search->search ( array( 'q' => 'search phrase' )) 
 * 
 * @author Casey Bisson
 */
class Twitter_User_Stream
{
	function tweets()
	{
		if( ! empty( $this->api_response->results ))
			return $this->api_response->results;
		else
			return FALSE;
	}

	function next()
	{
		if( ! empty( $this->api_response->results ))
			$this->stream( $this->args , 'next' );
		else
			return FALSE;
	}

	function refresh()
	{
		if( ! empty( $this->api_response->results ))
			$this->stream( $this->args , 'refresh' );
		else
			return FALSE;
	}

	function stream( $args , $method = 'stream' )
	{
		switch( $method )
		{
			case 'next':
			case 'next_page':
				if( ! empty( $this->api_response->next_page ))
				{
					$query_url = 'http://search.twitter.com/search.json' . $this->api_response->next_page;
					break;
				}
			
			case 'refresh':
				if( ! empty( $this->api_response->refresh_url ))
				{
					$query_url = 'http://search.twitter.com/search.json' . $this->api_response->refresh_url;
					break;
				}

			case 'stream':
			default:
				$defaults = array(
					'user_id' => '',
					'screen_name' => '',
					'since_id' => '',
					'max_id' => '',
					'count' => 10,
					'page' => '',
					'trim_user' => TRUE,
					'contributor_details' => FALSE,
					'include_entities' => TRUE,
					'exclude_replies' => FALSE,
					'include_rts' => TRUE,
				);
				$args = wp_parse_args( $args, $defaults );

				// save the args
				$this->args = $args;

				$query_url = add_query_arg( $args , 'http://api.twitter.com/1/statuses/user_timeline.json' );
		}

		$temp_results = wp_remote_get( $query_url );
		if ( is_wp_error( $temp_results ))
		{
			$this->error = $temp_results; 
			return FALSE;
		}

		$this->api_response = json_decode( wp_remote_retrieve_body( $temp_results ));
		unset( $temp_results );

		if( ! empty( $this->api_response->error ))
		{
			$this->error = $this->api_response; 
			unset( $this->api_response );
			return FALSE;
		}
/*
		foreach( $this->api_response->results as $result )
		{
			$this->api_response->min_id = $result->id;
			$this->api_response->min_id_str = $result->id_str;
		}
*/
		return $this->api_response->results;
	}
}

/**
 * Author: Vasken Hauri
 * Prints JS to load Twitter JS SDK in a deferred manner
 */

function print_twitter_js(){
?>
	<script type="text/javascript">	
<?php 
	if( defined( 'TWTTR_APP_ID' ) )
	{
?>
		setTimeout(function() {
			var bstwittera = document.createElement('script'); bstwittera.type = 'text/javascript'; bstwittera.async = true;
			bstwittera.src = 'http://platform.twitter.com/anywhere.js?id=<?php echo TWTTR_APP_ID ; ?>&v=1';
			var z = document.getElementsByTagName('script')[0]; z.parentNode.insertBefore(bstwittera, z);      
		}, 1);

<?php 
	}
?>
		setTimeout(function() {
			var bstwitterb = document.createElement('script'); bstwitterb.type = 'text/javascript'; bstwitterb.async = true;
			bstwitterb.src = 'http://platform.twitter.com/widgets.js';
			var z = document.getElementsByTagName('script')[0]; z.parentNode.insertBefore(bstwitterb, z);      
		}, 1);
	</script>
<?php
}

if(!is_admin())
	add_filter( 'print_footer_scripts', 'print_twitter_js' );
