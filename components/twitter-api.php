<?php

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
			$this->search( 'next' );
		else
			return FALSE;
	}

	function refresh()
	{
		if( ! empty( $this->api_response->refresh_url ))
			$this->search( 'refresh' );
		else
			return FALSE;
	}

	function search( $args , $method = 'search' )
	{
		switch( $method )
		{
			case 'next':
			case 'next_page':
				if( ! empty( $this->api_response->next_page ))
				{
					$query_url = 'http://search.twitter.com/search.json' . $this->search_api_response->next_page;
					break;
				}
			
			case 'refresh':
				if( ! empty( $this->api_response->refresh_url ))
				{
					$query_url = 'http://search.twitter.com/search.json' . $this->search_api_response->refresh_url;
					break;
				}

			case 'search':
			default:
				$defaults = array(
					'q' => urlencode( home_url()),
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

		if( ! empty( $user->error ))
		{
			$this->error = $this->search_api_response; 
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

function twitter_user_info( $screen_name , $by = 'screen_name' )
{
	//Look up info about the twitter user by their screen name (the from_user_id is only valid within the search API)
	//method docs: http://apiwiki.twitter.com/Twitter-REST-API-Method%3A-users%C2%A0show
	//useful: $user->name, $user->screen_name, $user->id_str, $user->followers_count 

	if ( ! $user = wp_cache_get( (string) $screen_name , 'twitter_screen_name' ))
	{
		$temp_results = wp_remote_get( 'http://api.twitter.com/1/users/show.json?screen_name='. urlencode( $screen_name ) );
		if ( is_wp_error( $temp_results ))
			return FALSE;

		$user = json_decode( wp_remote_retrieve_body( $temp_results ));

		if( empty( $user->error ))
			wp_cache_set( (string) $screen_name , $user, 'twitter_screen_name' , 604801 ); // cache for 7 days
	}

	return $user;
}

