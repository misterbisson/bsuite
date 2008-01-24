<?php
/*
Plugin Name: bSuite
Plugin URI: http://maisonbisson.com/blog/bsuite/
Description: It's okay for a few things, but you could probably do better. <a href="http://maisonbisson.com/blog/bsuite/core">Documentation here</a>.
Version: 3.03 
Author: Casey Bisson
Author URI: http://maisonbisson.com/blog/
*/

$services_feed = array(
	'bloglines' => array(
		'name' => 'Bloglines'
		, 'url' => 'http://www.bloglines.com/sub/{url_raw}'
	)
	, 'google' => array(
		'name' => 'Google'
		, 'url' => 'http://fusion.google.com/add?feedurl={url}'
	)
	, 'rssfwd' => array(
		'name' => 'RSS:FWD Email'
		, 'url' => 'http://www.rssfwd.com/rssfwd/preview?url={url}'
	)
);

$services_translate = array(
	'french' => array(
		'name' => 'French'
		, 'url' => 'http://translate.google.com/translate?u={url}&langpair=en%7Cfr'
	)
	, 'spanish' => array(
		'name' => 'Spanish'
		, 'url' => 'http://translate.google.com/translate?u={url}&langpair=en%7Ces'
	)
	, 'german' => array(
		'name' => 'German'
		, 'url' => 'http://translate.google.com/translate?u={url}&langpair=en%7Cde'
	)
	, 'japanese' => array(
		'name' => 'Japanese'
		, 'url' => 'http://translate.google.com/translate?u={url}&langpair=en%7Cja'
	)
	, 'korean' => array(
		'name' => 'Korean'
		, 'url' => 'http://translate.google.com/translate?u={url}&langpair=en%7Cko'
	)
	, 'chineses' => array(
		'name' => 'Chinese (simplified)'
		, 'url' => 'http://translate.google.com/translate?u={url}&langpair=en%7Czh-CN'
	)
	, 'chineset' => array(
		'name' => 'Chinese (traditional)'
		, 'url' => 'http://translate.google.com/translate?u={url}&langpair=en%7Czh-TW'
	)
	, 'russian' => array(
		'name' => 'Russian'
		, 'url' => 'http://translate.google.com/translate?u={url}&langpair=en%7Cru'
	)
);

$services_bookmark = array(
	'delicious' => array(
		'name' => 'del.icio.us'
		, 'url' => 'http://del.icio.us/post?url={url}&title={title}'
	)
	, 'facebook' => array(
		'name' => 'Facebook'
		, 'url' => 'http://www.facebook.com/share.php?u={url}'
	)
	, 'digg' => array(
		'name' => 'Digg'
		, 'url' => 'http://digg.com/submit?phase=2&url={url}&title={title}'
	)
	, 'stumbleupon' => array(
		'name' => 'StumbleUpon'
		, 'url' => 'http://www.stumbleupon.com/submit?url={url}&title={title}'
	)
	, 'reddit' => array(
		'name' => 'reddit'
		, 'url' => 'http://reddit.com/submit?url={url}&title={title}'
	)
	, 'blinklist' => array(
		'name' => 'BlinkList'
		, 'url' => 'http://blinklist.com/index.php?Action=Blink/addblink.php&Url={url}&Title={title}'
	)
	, 'newsvine' => array(
		'name' => 'Newsvine'
		, 'url' => 'http://www.newsvine.com/_tools/seed&save?popoff=0&u={url}&h={title}'
	)
	, 'furl' => array(
		'name' => 'Furl'
		, 'url' => 'http://furl.net/storeIt.jsp?u={url}&t={title}'
	)
	, 'tailrank' => array(
		'name' => 'Tailrank'
		, 'url' => 'http://tailrank.com/share/?link_href={url}&title={title}'
	)
	, 'magnolia' => array(
		'name' => 'Ma.gnolia'
		, 'url' => 'http://ma.gnolia.com/bookmarklet/add?url={url}&title={title}'
	)
	, 'netscape' => array(
		'name' => 'Netscape'
		, 'url' => ' http://www.netscape.com/submit/?U={url}&T={title}'
	)
	, 'yahoo_myweb' => array(
		'name' => 'Yahoo! My Web'
		, 'url' => 'http://myweb2.search.yahoo.com/myresults/bookmarklet?u={url}&t={title}'
	)
	, 'google_bmarks' => array(
		'name' => 'Google Bookmarks'
		, 'url' => '  http://www.google.com/bookmarks/mark?op=edit&bkmk={url}&title={title}'
	)
	, 'technorati' => array(
		'name' => 'Technorati'
		, 'url' => 'http://www.technorati.com/faves?add={url}'
	)
	, 'blinklist' => array(
		'name' => 'BlinkList'
		, 'url' => 'http://blinklist.com/index.php?Action=Blink/addblink.php&Url={url}&Title={title}'
	)
	, 'windows_live' => array(
		'name' => 'Windows Live'
		, 'url' => 'https://favorites.live.com/quickadd.aspx?marklet=1&mkt=en-us&url={url}&title={title}&top=1'
	)
);


class bSuite {

	function bSuite(){

		global $wpdb;
		$this->search_table = $wpdb->prefix . 'bsuite3_search';

//		$this->options = get_option('bsuite3');

		//
		// register hooks
		//

		// machine tags
		add_action('save_post', array(&$this, 'machtag_save_post'), 2, 2);		
		
		// tokens
		add_filter('bsuite_tokens', array(&$this, 'tokens_default'));
		add_filter('the_content', array(&$this, 'tokens'), 0);
		add_filter('the_content_rss', array(&$this, 'tokens'), 0);
		add_filter('the_excerpt', array(&$this, 'tokens'), 0);
		add_filter('the_excerpt_rss', array(&$this, 'tokens'), 0);
		add_filter('get_the_excerpt ', array(&$this, 'tokens'), 0);
		add_filter('widget_text', array(&$this, 'tokens'), 0);
		
		//innerindex
		add_filter('content_save_pre', array(&$this, 'innerindex_nametags'));
		add_filter('save_post', array(&$this, 'innerindex_delete_cache'));
		add_filter('publish_post', array(&$this, 'innerindex_delete_cache'));
		add_filter('publish_page', array(&$this, 'innerindex_delete_cache'));
		$this->kses_allowedposttags(); // allow IDs on H1-6 tags

		// bsuggestive
		add_filter('save_post', array(&$this, 'bsuggestive_delete_cache'));
		add_filter('publish_post', array(&$this, 'bsuggestive_delete_cache'));
		add_filter('publish_page', array(&$this, 'bsuggestive_delete_cache'));
		
		// pagehooks
		add_filter('bsuite_pagehooks', array(&$this, 'pagehooks_default'));
		add_filter('template_redirect', array(&$this, 'pagehooks'), 8);
		
		// athooks
		add_filter('bsuite_athooks', array(&$this, 'athooks_default'));
		add_filter('template_redirect', array(&$this, 'athooks_onredirect'), 8);
		add_filter('posts_request', array(&$this, 'athooks_onsearch'), 8);
		add_filter('get_breadcrumb', array(&$this, 'athooks_breadcrumbs'), 5, 2);
		
		// sharelinks
		add_filter('template_redirect', array(&$this, 'sharelinks_redirect'), 8);

		// searchsmart
		add_filter('posts_request', array(&$this, 'searchsmart_query'), 10);
		add_filter('template_redirect', array(&$this, 'searchsmart_onsingle'), 8);
		add_filter('content_save_pre', array(&$this, 'searchsmart_upindex_onedit'));
		
		// CMS goodies
		add_action('dbx_page_advanced', array(&$this, 'insert_excerpt_form'));
		add_action('dbx_page_sidebar', array(&$this, 'insert_category_form'));
		add_action('edit_form_advanced', array(&$this, 'edit_post_form'));
		add_action('edit_page_form', array(&$this, 'edit_page_form'));
		add_action('widgets_init', array(&$this, 'widgets_register'));

		add_action('query_vars', array(&$this, 'set_query_vars'));

		// activation and menu hooks
		register_activation_hook(__FILE__, array(&$this, 'activate'));
		add_action('admin_menu', array(&$this, 'addmenus'));
		// end register WordPress hooks


		// set things up so authors can edit their own pages
		$role = get_role('author');
		if ( ! empty($role) ) {
			$role->add_cap('edit_pages');
			$role->add_cap('edit_published_pages');
		}
	}



	function set_query_vars($vars){
		return(array_unique(array_merge(array('bsuite_share'), $vars)));
	}

	//
	// token functions
	// tokens are [[token]] in the content of a post.
	//
	function tokens_get(){
		// establish list of tokens
		static $tokens = FALSE;	
		if($tokens)
			return($tokens);
	
		$tokens = array();
		$tokens = apply_filters('bsuite_tokens', $tokens);

		return($tokens);
	}

	function tokens_fill($thing) {
		// match tokens
		$return = $thing[0];
		$thing = explode('|', trim($thing[0], '[]'), 2);
		$tokens = &$this->tokens_get();

		if($tokens[$thing[0]])
			$return = call_user_func_array($tokens[$thing[0]], $thing[1]);

		return($return);
	}

	function tokens($content) {
		// find tokens in the page
		$content = preg_replace_callback(
			'/\[\[([^\]\]])*\]\]/',
			array(&$this, 'tokens_fill'),
			$content);
		return($content);
	}

	function tokens_default($tokens){
		// setup some default tokens
		$tokens['date'] = array(&$this, 'token_get_date');
		$tokens['pagemenu'] = array(&$this, 'token_get_pagemenu');
		$tokens['innerindex'] = array(&$this, 'innerindex');
		$tokens['feed'] = array(&$this, 'token_get_feed');
		$tokens['redirect'] = array(&$this, 'token_get_redirect');

		return($tokens);
	}
	
	function token_get_date($stuff = 'F j, Y, g:i a'){
		// [[date|options]]
		return(date($stuff));
	}
	
	function token_get_pagemenu($stuff = NULL){
		// [[pagemenu|depth|extra]]
		// [[pagemenu|1|sort_column=post_date&sort_order=DESC]]
		global $id;
		$stuff = explode('|', $stuff);
		return(wp_list_pages("child_of=$id&depth=1&echo=0&sort_column=menu_order&title_li=&$stuff[0]"));
	}
	
	function token_get_redirect($stuff){
		// [[redirect|$url]]
		if(!headers_sent())
			header("Location: $stuff");
		return('redirect: <a href="'. $stuff .'">'. $stuff .'</a>');
	}

	function token_get_feed($stuff){
		// [[feed|feed_url|count]]
		$stuff = explode('|', $stuff);
		if(!$stuff[1])
			$stuff[1] = 5;
		if(!$stuff[2])
			$stuff[2] = '<li><a href="%%link%%">%%title%%</a><br />%%content%%</li>';
		return($this->get_feed($stuff[0], $stuff[1], $stuff[2], TRUE));
	}
	// end token-related functions



	//innerindex
	function innerindex($title = 'Contents:'){
		global $id, $post_cache;

		if ( !$menu = wp_cache_get( $id, 'bsuite_innerindex' ) ) {
			$menu = $this->innerindex_build($post_cache[1][$id]->post_content);
			wp_cache_add( $id, $menu, 'bsuite_innerindex', 864000 );
		}

		return('<div class="innerindex"><h3>'. $title .'</h3>'. $menu .'</div>');
	}
	
	function innerindex_build($content){
		// find <h*> tags with IDs in the content and build an index of them
		preg_match_all(
			'|<h[^>]+>[^<]+</h[^>]+>|U',
			$content,
			$things
			);

		$menu = '<ol>';
		$closers = $count = 0;
		foreach($things[0] as $thing){
			preg_match('|<h([0-9])|U', $thing, $h);
			preg_match('|id="([^"]*)"|U', $thing, $anchor);

			if(!$last)
				$last = $low = $h[1];
			
			if($anchor[1]){
				if($h[1] > $last){
					$menu .= '<ol>';
					$closers++;
				}else if($count){
					$menu .= '</li>';
				}

				if(($h[1] < $last) && ($h[1] >= $low)){
					$menu .= '</ol></li>';
					$closers--;
				}
				
				$last = $h[1];
		
				$menu .= '<li><a href="'. substr(get_permalink($id), 0, strpos(get_permalink($id), '#')) .'#'. $anchor[1] .'">'. strip_tags($thing) .'</a>';
				$count++;
			}
		}
		$menu .= '</li>'. str_repeat('</ol></li>', $closers) . '</ol>';
		return($menu);
	}

	function innerindex_delete_cache($id) {
		$id = (int) $id;
		wp_cache_delete( $id, 'bsuite_innerindex' );
	}

	function innerindex_nametags($content){
		// find <h*> tags in the content
		$content = preg_replace_callback(
			"/(\<h([0-9])?([^\>]*)?\>)(.*?)(\<\/h[0-9]\>)/",
			array(&$this,'innerindex_nametags_callback'),
			$content
			);
		return($content);
	}

	function innerindex_nametags_callback($content){
		// receive <h*> tags and insert the ID
		static $slugs;
		$slugs[] = $slug = substr(sanitize_title_with_dashes($content[4]), 0, 20);
		$content = "<h{$content[2]} id=\"{$_POST['post_ID']}_{$slug}_". count(array_keys($slugs, $slug)) .'" '. trim(preg_replace('/id[^"]*"[^"]*"/', '', $content[3])) .">{$content[4]}{$content[5]}";
		return($content);
	}
	// end innerindex-related

	function feedlink(){
		return(strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'))) . '://' . $_SERVER['HTTP_HOST'] . add_query_arg('feed', 'rss', add_query_arg('bsuite_share')));
	}
	
	//
	// pagehook functions
	//
	function pagehooks_get(){
		// establish list of pagehooks
		static $pagehooks = FALSE;	
		if($pagehooks)
			return($pagehooks);
	
		$pagehooks = array();
		$pagehooks = apply_filters('bsuite_pagehooks', $pagehooks);

		return($pagehooks);
	}

	function pagehooks_default($pagehooks){
		// setup some default pagehooks, but there are none
		// $pagehooks['pagename'] = 'function_name';
		return($pagehooks);
	}

	function pagehooks(){
		// process pagehooks
		// the context of this hook is here:
		// http://wphooks.flatearth.org/hooks/template_redirect/
		// Pagehooks allow you to put a stub-page in WP, then do some action 
		// (like fetch the actual content from another server/script) when it's loaded
		global $wp_query;
		$pagehooks = &$this->pagehooks_get();

//print_r($wp_query);

		if(($wp_query->post->post_type == 'page') && $pagehooks[$wp_query->post->post_name])
			call_user_func($pagehooks[$wp_query->post->post_name]);
//call_user_func($pagehooks['wikipedia']);

		return;
	}
	// end pagehook-related functions



	//
	// athook functions
	//
	function athooks_get(){
		// establish list of athooks
		static $athooks = FALSE;	
		if($athooks)
			return($athooks);
	
		$athooks = array();
		$athooks = apply_filters('bsuite_athooks', $athooks);

		return($athooks);
	}

	function athooks_default($athooks){
		// setup some default athooks
		// $athooks[hook name][where to act (request or redirect)] = 'function name';

		$athooks['related']['request'] = array(&$this, 'athook_get_related');
		$athooks['related']['title'] = __('Related', 'bsuite');

		$athooks['share']['redirect'] = array(&$this, 'athook_sharelinks');
		$athooks['share']['title'] = __('Share This', 'bsuite');

		return($athooks);
	}

	function athooks_onredirect(){
		// process athooks
		// the context of this hook is here:
		// http://wphooks.flatearth.org/hooks/template_redirect/
		// Athooks allow you to change what WP does when a user loads a post
		// with an /$athook in the URL. 
		// Example: http://site.org/permalinkpath/athook
		global $wp_query;
		$athooks = &$this->athooks_get();

		if($wp_query->query_vars['attachment'] && $athooks[$wp_query->query_vars['attachment']]['redirect']){
			$wp_query->is_athook = TRUE;
			$wp_query->athook['name'] = $wp_query->query_vars['attachment'];
			$wp_query->athook['title'] = $athooks[$wp_query->query_vars['attachment']]['title'];
			$wp_query->athook['post'] = &$this->athook_get_post('/'. $wp_query->query_vars['attachment']);
			call_user_func($athooks[$wp_query->query_vars['attachment']]['redirect']);
		}
		return;
	}

	function athooks_onsearch($search){
		// process athooks
		// the context of this hook is here:
		// http://wphooks.flatearth.org/hooks/posts_request/
		// Athooks allow you to change what WP does when a user loads a post
		// with an /$athook in the URL. 
		// Example: http://site.org/permalinkpath/athook
		global $wp_query;
		$athooks = &$this->athooks_get();

		if($wp_query->query_vars['attachment'] && $athooks[$wp_query->query_vars['attachment']]['request']){
			$wp_query->is_athook = TRUE;
			$wp_query->athook['name'] = $wp_query->query_vars['attachment'];
			$wp_query->athook['title'] = $athooks[$wp_query->query_vars['attachment']]['title'];
			$wp_query->athook['post'] = &$this->athook_get_post('/'. $wp_query->query_vars['attachment']);
			call_user_func($athooks[$wp_query->query_vars['attachment']]['request'], $search);
		}
		return($search);
	}

	function athooks_breadcrumbs($breadcrumb, $params){
		// makes athooks play nice with Dan Peverill's breadcrumb plugin
		global $wp_query;

		if($wp_query->is_athook){
			
			$breadcrumb = array(array_shift($breadcrumb));
			
			if ((breadcrumb_is_paged() || $params["link_all"]) && !$params["link_none"])
				$breadcrumb[] = '<a href="'. get_permalink($wp_query->athook['post']) .'" title="'. get_the_title($wp_query->athook['post']) .'">'. get_the_title($wp_query->athook['post']) .'</a>';
			else
				$breadcrumb[] = get_the_title($wp_query->athook['post']);

			if ($params["link_all"] && !$params["link_none"])
				$breadcrumb[] = '<a href="'. get_permalink($wp_query->athook['post']) .'/'. $wp_query->athook['name'] .'" title="'. $wp_query->athook['title'] .'">'. $wp_query->athook['title'] .'</a>';
			else
				$breadcrumb[] = $wp_query->athook['title'];
		}


//print_r($breadcrumb);
		return $breadcrumb;
	}

	function athook_get_related($search){
		// get related items
		global $wp_query, $wpdb;

		$query_vars['p'] = $wp_query->athook['post'];

		if(($query_vars['p']) && ($the_tags = $this->bsuggestive_tags($query_vars['p']))){

			status_header(200);
			unset($wp_query->query_vars['attachment']);
			$wp_query->query['s'] = implode( ' ', $the_tags );
			$wp_query->query_vars['s'] = implode( ' ', $the_tags );
			$wp_query->is_404 = FALSE;
			$wp_query->is_attachment = FALSE;
			$wp_query->is_search = TRUE;
			$wp_query->is_page = FALSE;
			$wp_query->is_single = FALSE;
			$wp_query->is_singular = FALSE;
			$wp_query->is_archive = FALSE;
			$wp_query->is_category = FALSE;

			// re-add the searchsmart posts request filter to work around Scriblio
			add_filter('posts_request', array(&$this, 'searchsmart_query'), 20);
		}

		return($search);
	}

	function athook_sharelinks(){
		global $wp_query;
	
		$query_vars['p'] = $wp_query->athook['post'];
		if($query_vars['p']){
			$athook = $wp_query->athook;
			query_posts("p={$query_vars['p']}&bsuite_share=1");
			$wp_query->is_athook = TRUE;
			$wp_query->athook = $athook;
			status_header(200);
			$this->sharelinks_redirect();
		}
		return(TRUE);
	}

	function athook_get_post($thing){
		// get related items
		global $wp;

		$query_vars = &$this->parse_request(str_replace($thing, '', $wp->request));
		return($query_vars['p']);
	}
	// end athook-related functions



	//
	// sharelinks
	//
	function sharelinks(){
		global $wp_query;

		$this->sharelinks_nonce = TRUE;
	
		// exit if 404
		if($wp_query->is_404)
			return(FALSE);
	
		// identify the based post ID, if any, and establish some basics
		$post_id = FALSE;
		if(!empty($wp_query->is_singular) && !empty($wp_query->query_vars['p']))
			$post_id = $wp_query->query_vars['p'];
		else if(!empty($wp_query->is_singular) && !empty($wp_query->queried_object_id))
			$post_id = $wp_query->queried_object_id;
	
		if($post_id){
			$the_permalink = urlencode(get_permalink($post_id));
			$the_title = urlencode(get_the_title($post_id));
			$the_excerpt = apply_filters('the_excerpt', get_the_excerpt());
		}else{
			$the_permalink = strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'))) . '://' . $_SERVER['HTTP_HOST'] . add_query_arg('bsuite_share');
	
			unset($wp_query->query['bsuite_share']);
			unset($wp_query->query['attachment']);
			if(count($wp_query->query))
				$the_title = get_bloginfo('name') .' ('. wp_specialchars( implode(array_unique(explode('|', strtolower(implode(array_values($wp_query->query), '|')))), ', ')) .')';
			else
				$the_title = get_bloginfo('name');
	
			$the_excerpt = '';
		}
		$content = '';
	
		// the bookmark links 
		$content .= '<h3 id="bsuite_share_bookmark">Bookmark this at</h3><ul>';
		global $services_bookmark;
		foreach ($services_bookmark as $key => $data) {
			$content .= '<li><img src="' . get_settings('siteurl') .'/'. PLUGINDIR .'/'. plugin_basename(dirname(__FILE__))  . '/img/'. $key .'.gif" width="16" height="16" alt="'. attribute_escape($data['name']) .' sharing icon">&nbsp;<a href="'. str_replace(array('{title}', '{url}'), array($the_title, $the_permalink), $data['url']) .'">'. $data['name'] .'</a></li>';
		}
		$content .= '</ul>';
	
		// the email links
		$content .= '<h3 id="bsuite_share_email">Email this page</h3><ul><li><a href="mailto:?MIME-Version=1.0&Content-Type=text/html;&subject='. attribute_escape(urldecode($the_title)) .'&body=%0D%0AI found this at '.  attribute_escape(get_bloginfo('name')) .'%0D%0A'. attribute_escape(urldecode($the_permalink)) .'%0D%0A">Send this page using your computer&#039;s emailer</a></li></ul>';
	
		// the feed links
		$content .= '<h3 id="bsuite_share_feed">Stay up to date</h3><ul>';
		$feeds = array();
		if($wp_query->is_singular)
			$feeds[] = array('title' => 'Comments on this post', 'url' => get_post_comments_feed_link($post_id));
		if($wp_query->is_search)
			$feeds[] = array('title' => 'This Search', 'url' => $this->feedlink());
		$feeds[] = array('title' => 'All Posts', 'url' => get_bloginfo('atom_url'));
		$feeds[] = array('title' => 'All Comments', 'url' => get_bloginfo('comments_atom_url'));
	
		global $services_feed;
		foreach ($feeds as $feed) {
			$subscribe_links = array();
			foreach ($services_feed as $key => $data) {
				$subscribe_links[] = '<a href="'. str_replace(array('{url}', '{url_raw}'), array(urlencode($feed['url']), $feed['url']), $data['url']) .'">'. $data['name'] .'</a>';
			}
	
			$content .= '<li><img src="' . get_settings('siteurl') .'/'. PLUGINDIR .'/'. plugin_basename(dirname(__FILE__))  . '/img/icon-feed-16x16.png" width="16" height="16" alt="'. attribute_escape($feed['title']) .' feed icon">&nbsp;<a href="'. $feed['url'] .'">'. $feed['title'] .'</a>. Subscribe via '.  implode($subscribe_links, ', ') .'.</li>';
		}
		$content .= '</ul>';
	
		// the translation links
		$content .= '<h3 id="bsuite_share_translate">Automatically translate this to</h3><ul>';
		global $services_translate;
		foreach ($services_translate as $key => $data) {
			$content .= '<li><a href="'. str_replace('{url}', $the_permalink, $data['url']) .'">'. $data['name'] .'</a></li>';
		}
		$content .= '</ul>';
	
		// powered by
		$content .= '<p>Powered by <a href="http://maisonbisson.com/blog/bsuite">bSuite</a>.</p>';
	
		return(array('the_id' => $post_id, 'the_title' => urldecode($the_title), 'the_permalink' => urldecode($the_permalink), 'the_content' => $content, ));
	}

	function sharelinks_url(){
		global $post, $wp_query;
		if(($wp_query->in_the_loop && $post->post_type == 'post') || $wp_query->is_single && $post->post_type == 'post')
			return(get_permalink($post->ID). '/share');
		return(strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'))) . '://' . $_SERVER['HTTP_HOST'] . add_query_arg('bsuite_share', 1));
	}

	function sharelinks_link($title = 'bookmark, share, and feed links'){
		if($this->sharelinks_nonce)
			return(FALSE);

		return('<img src="' . get_settings('siteurl') .'/'. PLUGINDIR .'/'. plugin_basename(dirname(__FILE__))  . '/img/icon-share-16x16.gif" width="16" height="16" alt="bookmark, share, feed, and translate icon" />&nbsp;<a href="'. $this->sharelinks_url() .'" title="bookmark, share, feed, and translate links">'. $title .'</a>');
	}

	function sharelinks_redirect(){
		global $wp_query, $post_cache;
		if(!empty($wp_query->query_vars['bsuite_share'])){
			if(!$share = $this->sharelinks())
				return(FALSE);
	
			if(!$post_id = $share['the_id']){
				$GLOBALS['wp_query'] = unserialize('O:8:"WP_Query":39:{s:10:"query_vars";a:40:{s:1:"p";i:0;s:5:"error";s:0:"";s:1:"m";i:0;s:7:"subpost";s:0:"";s:10:"subpost_id";s:0:"";s:10:"attachment";s:0:"";s:13:"attachment_id";i:0;s:4:"name";s:0:"";s:4:"hour";s:0:"";s:6:"static";s:0:"";s:8:"pagename";s:0:"";s:7:"page_id";i:0;s:6:"second";s:0:"";s:6:"minute";s:0:"";s:3:"day";i:0;s:8:"monthnum";i:0;s:4:"year";i:0;s:1:"w";i:0;s:13:"category_name";s:0:"";s:3:"tag";s:0:"";s:6:"tag_id";s:0:"";s:11:"author_name";s:0:"";s:4:"feed";s:0:"";s:2:"tb";s:0:"";s:5:"paged";s:0:"";s:14:"comments_popup";s:0:"";s:7:"preview";s:0:"";s:12:"category__in";a:0:{}s:16:"category__not_in";a:0:{}s:13:"category__and";a:0:{}s:7:"tag__in";a:0:{}s:11:"tag__not_in";a:0:{}s:8:"tag__and";a:0:{}s:12:"tag_slug__in";a:0:{}s:13:"tag_slug__and";a:0:{}s:9:"post_type";s:4:"post";s:14:"posts_per_page";i:10;s:8:"nopaging";b:0;s:5:"order";s:4:"DESC";s:7:"orderby";s:14:"post_date DESC";}s:7:"request";s:116:" SELECT   test23_posts.* FROM test23_posts  WHERE 1=1  AND ID = 938 AND post_type = "post"  ORDER BY post_date DESC ";s:10:"post_count";i:1;s:12:"current_post";i:-1;s:11:"in_the_loop";b:0;s:4:"post";O:8:"stdClass":24:{s:2:"ID";i:0;s:11:"post_author";s:1:"1";s:9:"post_date";s:0:"";s:13:"post_date_gmt";s:0:"";s:12:"post_content";s:0:"";s:10:"post_title";s:10:"Share This";s:13:"post_category";s:1:"0";s:12:"post_excerpt";s:0:"";s:11:"post_status";s:7:"publish";s:14:"comment_status";s:4:"open";s:11:"ping_status";s:4:"open";s:13:"post_password";s:0:"";s:9:"post_name";s:10:"share-this";s:7:"to_ping";s:0:"";s:6:"pinged";s:0:"";s:13:"post_modified";s:0:"";s:17:"post_modified_gmt";s:0:"";s:21:"post_content_filtered";s:0:"";s:11:"post_parent";s:1:"0";s:4:"guid";s:0:"";s:10:"menu_order";s:1:"0";s:9:"post_type";s:4:"post";s:14:"post_mime_type";s:0:"";s:13:"comment_count";s:1:"0";}s:8:"comments";N;s:13:"comment_count";i:0;s:15:"current_comment";i:-1;s:7:"comment";N;s:11:"found_posts";i:0;s:13:"max_num_pages";i:0;s:9:"is_single";b:1;s:10:"is_preview";b:0;s:7:"is_page";b:0;s:10:"is_archive";b:0;s:7:"is_date";b:0;s:7:"is_year";b:0;s:8:"is_month";b:0;s:6:"is_day";b:0;s:7:"is_time";b:0;s:9:"is_author";b:0;s:11:"is_category";b:0;s:6:"is_tag";b:0;s:9:"is_search";b:0;s:7:"is_feed";b:0;s:15:"is_comment_feed";b:0;s:12:"is_trackback";b:0;s:7:"is_home";b:0;s:6:"is_404";b:0;s:17:"is_comments_popup";b:0;s:8:"is_admin";b:0;s:13:"is_attachment";b:0;s:11:"is_singular";b:1;s:9:"is_robots";b:0;s:13:"is_posts_page";b:0;s:8:"is_paged";b:0;s:5:"query";s:5:"p=938";s:5:"posts";a:1:{i:0;R:47;}}');
				$GLOBALS['wp_query']->post->post_date = $GLOBALS['wp_query']->post->post_date_gmt = $GLOBALS['wp_query']->post->post_modified = $GLOBALS['wp_query']->post->post_modified_gmt = date('Y-m-d H-i-s');
				$GLOBALS['wp_query']->post->post_title = $share['the_title'];
				update_post_caches($GLOBALS['wp_query']->posts);
			}
				
			if(!ereg('^'.__('Share This', 'bsuite'), $post_cache[1][$post_id]->post_title))
				$post_cache[1][$post_id]->post_title = __('Share This', 'bsuite') .': '. $post_cache[1][$post_id]->post_title;
			$post_cache[1][$post_id]->post_content = $share['the_content'];
			$post_cache[1][$post_id]->comment_status = 'closed';
			$posts = $wp_query->posts;
		}
	}
	// end sharelinks related functions


	//
	// link to me
	//
	function link2me_links( $post_id ){
		if( !$post_id )
			return( FALSE );

		//'<a href="'. get_permalink($post_id) .'" title="'. attribute_escape( strip_tags( get_the_title( $post_id ))) .'">'. strip_tags( get_the_title( $post_id )) .'</a>'; //not using this now

//echo '<h2>Hi!</h2>';
		return( apply_filters('bsuite_link2me', array( array('code' => get_permalink($post_id), 'name' => __( 'Permalink', 'bsuite' ))), $post_id));
	}

	function link2me( $post_id ){
		if( !$post_id ){
			global $id;
			$post_id = $id;
		}

		if( !$links = $this->link2me_links( $post_id ))
			return( FALSE );

		if( count( $links ) ){
	        $return = '<ul class="linktome">';
	        foreach( $links as $link ){
				$return .= '<li><h4>'. $link['name'] .'</h4><input class="linktome_input" type="text" value="'. htmlentities( $link['code'] ) .'" readonly="true" /></li>';
	        }
	        $return .= '</ul>';
			return( $return );
		}
		return( FALSE );
	}



	//
	// Searchsmart
	//
	function searchsmart_query($query){
	
		global $wp_query, $wpdb;

		if($wp_query->is_admin)
			return($query);

		if (!empty($wp_query->query_vars['s'])) {
			$limit = explode('LIMIT', $query);
			if(!$limit[1]){
				// $paged, $posts_per_page, and $limit are here for cases
				// where the query doesn't have an explicit LIMIT declaration
				$paged = $wp_query->query_vars['paged'];
				if(!$paged)
					$paged = 1;
	
				$posts_per_page = $wp_query->query_vars['posts_per_page'];
				if(!$posts_per_page)
					$posts_per_page = get_settings('posts_per_page');
	
				$limit = explode('LIMIT', $query);
				if(!$limit[1])
					$limit[1] = ($paged - 1) * $posts_per_page .', '. $posts_per_page;
			
			}

			$query = "SELECT SQL_CALC_FOUND_ROWS $wpdb->posts.*, MATCH (content, title) AGAINST ('". $wpdb->escape( stripslashes( $wp_query->query_vars['s'] )) ."') AS relevance 
				FROM $wpdb->posts 
				LEFT JOIN $this->search_table ON ( post_id = ID )  
				WHERE 1=1 
				AND (MATCH (content, title) AGAINST ('". $wpdb->escape( stripslashes( $wp_query->query_vars['s'] )) ."'))
				AND (post_type IN ('post', 'page') AND (post_status IN ('publish', 'private')))
				ORDER BY relevance DESC LIMIT $limit[1]
				";

//print_r($wp_query);			
//echo "<pre>$query</pre>";
		}
		return($query);
	}

	function searchsmart_onsingle(){
		// redirects the search to the single page if the search returns only one item
		global $wp_query;
		if($wp_query->is_search && $wp_query->post_count == 1){
			//$wp_query->is_search = NULL;
			//$wp_query->is_single = TRUE;
			header('Location: '. get_permalink($wp_query->post->ID));
		}
		return(TRUE);
	}

	function searchsmart_upindex($post_id, $content, $title = ''){
		// put content in the keyword search index
		global $wpdb;

		$content = preg_replace(
			'/\[\[([^\]])*\]\]/',
			'',
			strip_tags(
			str_ireplace(array('<br />', '<br/>', '<br>', '</p>', '</li>', '</h1>', '</h2>', '</h3>', '</h4>'), "\n", 
			stripslashes(
			html_entity_decode($content)))));
		$content = trim(preg_replace(
			'/([[:punct:]])*/',
			'',
			$content));

		$content = apply_filters('bsuite_searchsmart_content', $content);

		$title = preg_replace(
			'/([[:punct:]])*/',
			'',
			html_entity_decode($title, ENT_QUOTES, 'UTF-8'));

		$request = "REPLACE INTO $this->search_table
					(post_id, content, title) 
					VALUES ($post_id, '$content', '$title')";
		
		$wpdb->get_results($request);

		return(TRUE);
	}

	function searchsmart_upindex_onedit($content){
		// called when posts are saved
		if($_POST['post_ID'])
			$this->searchsmart_upindex(ereg_replace('[^0-9]', '', $_POST['post_ID']), $content);
		return($content);
	}
	// end Searchsmart


	// bSuggestive related functions
	function bsuggestive_tags($id = FALSE) {
		$id = (int) $id;
		if ( !$id )
			$id = (int) $post->ID;
		if ( !$id )
			return FALSE;
		
		$tags = get_the_tags($id);
		if($tags){
			foreach( $tags as $tag) {
				$the_tags[] = $tag->name;
			}
		}else{
			$the_tags[] = get_the_title($id);
		}
		
		if(!count(array_filter($the_tags)))
			return FALSE;

		return apply_filters('bsuite_suggestive_tags', $the_tags, $id);
	}
	
	function bsuggestive_query($the_tags, $id) {
		global $wpdb;

		$id = (int) $id;
		if ( !$id )
			$id = (int) $post->ID;
		if ( !$id )
			return FALSE;

		if($id && is_array($the_tags)){
			return apply_filters('bsuite_suggestive_query', 
				"SELECT post_id
						FROM $this->search_table 
						LEFT JOIN $wpdb->posts
						ON post_id = ID
						WHERE MATCH (content, title)
						AGAINST ('". ereg_replace('[^a-z|A-Z|0-9| ]', ' ', implode(' ', $the_tags)) ."') AND post_id <> $id
						AND post_status = 'publish'
						LIMIT 50
						", $post_id);
		}
		return FALSE;
	}
	
	function bsuggestive_getposts($id = FALSE) {
		global $post, $wpdb;
		
		$id = (int) $id;
		if ( !$id )
			$id = (int) $post->ID;
		if ( !$id )
			return FALSE;

		if ( !$related_posts = wp_cache_get( $id, 'bsuite_related_posts' ) ) {
			if(($the_tags = $this->bsuggestive_tags($id)) && ($the_query = $this->bsuggestive_query($the_tags, $id))){
				$related_posts = $wpdb->get_col($the_query);
				wp_cache_add( $id, $related_posts, 'bsuite_related_posts', 864000 );
				return($related_posts); // if we have to go to the DB to get the posts, then this will get returned
			}
			return FALSE; // if there's nothing in the cache and we've got no tags, then we return false
		}
		return($related_posts); // if the cache is still warm, then we return this
	}

	function bsuggestive_delete_cache($id) {
		$id = (int) $id;
		if ( !$id )
			return FALSE;

		wp_cache_delete( $id, 'bsuite_related_posts' );
	}

	function bsuggestive_the_related($before = '<li>', $after = '</li>') {
		global $post;
		$report = FALSE;

		$id = (int) $post->ID;
		if ( !$id )
			return FALSE;

		$posts = array_slice($this->bsuggestive_getposts($id), 0, 5);
		if($posts){
			$report = '';
			foreach($posts as $post_id){
//				$post = &get_post( $post_id );
				$url = get_permalink($post_id);
				$linktext = get_the_title($post_id);
				$report .= $before . "<a href='$url'>$linktext</a>". $after;
			}
		}
		return($report);
	}
	// end bSuggestive

	function parse_request($request){
		// parse the $request through the WP rewrite rules and returns query vars
		// 
		// code taken from inside parse_request() in /wp-includes/classes.php 
		// I would rather call it as a function from there, but it doesn't work
		global $wp_rewrite;

		$rewrite = $wp_rewrite->wp_rewrite_rules();
		$request_match = $request;
		foreach ($rewrite as $match => $query) {
			if (preg_match("!^$match!", $request_match, $matches) ||
				preg_match("!^$match!", urldecode($request_match), $matches)) {
		
				// Trim the query of everything up to the '?'.
				$query = preg_replace("!^.+\?!", '', $query);
		
				// Substitute the substring matches into the query.
				eval("\$query = \"$query\";");
				parse_str($query, $query_vars);
		
				break;
			}
		}

//print_r($query_vars);

		return($query_vars);
	}



	function pagetree(){
		// identify the family tree of a page, return an array
		global $wp_query;
		$tree = NULL;
		
		if ($wp_query->is_page){
			$object = $wp_query->get_queried_object();
		
			// cycle through the tree and build an array
			$parent_id = $object->post_parent;
			$tree[]  = $object->ID;
			while ($parent_id){
				$page = get_page($parent_id);
				$tree[]  = $page->ID;
				$parent_id  = $page->post_parent;
			}
		
			// the tree is in reverse order.
			$tree = array_reverse($tree);
		}
		return $tree;
	}



	//
	// loadaverage related functions
	//
	function get_loadavg(){
		static $result = FALSE;	
		if($result)
			return($result);
	
		if(function_exists('sys_getloadavg')){
			$load_avg = sys_getloadavg();
		}else{
			$load_avg = &$this->sys_getloadavg();
		}
		$result = (($load_avg[0] + $load_avg[1]) / 2);
		return($result);
	}

	function sys_getloadavg(){
		// the following code taken from tom pittlik's comment at
		// http://php.net/manual/en/function.sys-getloadavg.php
		$str = substr(strrchr(shell_exec('uptime'),':'),1);
		$avs = array_map('trim',explode(',',$str));
		return $avs;
	}
	// end load average related functions



	// set a cookie
	function cookie($name, $value = NULL) {
		if($value === NULL){
			if($_GET[$name]) return $_GET[$name];
			elseif($_POST[$name]) return $_POST[$name];
			elseif($_SESSION[$name]) return $_SESSION[$name];
			elseif($_COOKIE[$name]) return $_COOKIE[$name];
			else return false;
		}else{
			setcookie($name, $value, time()+60*60*24*30, '/', '.scriblio.net');
			return($value);
		}
	}
	// end 



	// get and display rss feeds
	function get_feed($feed, $count = 15, $template = '<li>%%title%%<br />%%content%%</li>', $return = FALSE){
		if(!function_exists('fetch_rss'))
			require_once (ABSPATH . WPINC . '/rss.php');
		$rss = fetch_rss($feed);
		//print_r($rss);
	
		$i = $list = NULL;
//print_r($rss);
		foreach($rss->items as $item){
			$i++;
			if($i > $count)
				break;
			$link = $item['link'];
			$title = '<a href="' . $item['link'] . '" title="' . $item['title'] . '">' . $item['title'] . '</a>';
			if($item['content']['encoded']){
				$content = $item['content']['encoded'];
			}else{
				$content = $item['description'];
			}
			$list .= str_replace(array('%%title%%','%%content%%','%%link%%'), array($title, $content, $link), $template);
//echo $template;

		}
		
		if($return)
			return($list);
		echo $list;
	}
	// end function get_rss


	// machine tags
	function machtag_save_post($post_id, $post) {
		// Passed machine tags overwrite existing if not empty
		if ( isset( $_REQUEST['bsuite-machine-tags-input'] ))

			foreach( $this->machtag_parse_tags( $_REQUEST['bsuite-machine-tags-input'] ) as $taxonomy => $tags ){

				if( 'post_tag' == $taxonomy ){
					wp_set_post_tags($post_id, $tags, true);
					continue;
				}
	
				if(!is_taxonomy( $taxonomy ))
					register_taxonomy($taxonomy, 'post');
				wp_set_object_terms($post_id, $tags, $taxonomy);
			}
	}

	function machtag_parse_tags( $tags_input ) {
		$tags = $tags_parsed = array();
		$tag_lines = explode( "\n", $tags_input );
		foreach($tag_lines as $tag_line)
			$tags_parsed[] = $this->machtag_parse_tag( $tag_line );

		foreach( $tags_parsed as $tag_parsed )
			$tags[$tag_parsed['taxonomy']][] = $tag_parsed['term'];

		return( $tags );
	}

	function machtag_parse_tag( $tag ) {
		$namespace = $taxonomy = $term = FALSE;
		$taxonomy = 'post_tag';

		$temp_a = explode(':', $tag, 2);

		if($temp_a[1]){
			$temp_b = explode('=', $temp_a[1], 2);

			if($temp_b[1]){
				// has namespace, fieldname, & value
				$namespace = $temp_a[0];
				$taxonomy = $temp_b[0];
				$term = $temp_b[1];
			}else{
				// has just fieldname & value
				$taxonomy = $temp_a[0];
				$term = $temp_b[0];
			}
		}else{
			$temp_b = explode('=', $temp_a[0], 2);

			if($temp_b[1]){
				// has just fieldname & value
				$taxonomy = $temp_b[0];
				$term = $temp_b[1];
			}else{
				// has just value
				$term = $temp_b[0];
			}
		}

		return(array('taxonomy' => $taxonomy, 'term' => $term));
	}

	// add tools to edit screens
	function edit_page_form() {
		$this->edit_insert_tag_form();
//		$this->edit_insert_tools();
		$this->edit_insert_machinetag_form();
	}
	
	function edit_post_form() {
//		$this->edit_insert_tools();
		$this->edit_insert_machinetag_form();
	}

	function edit_comment_form() {
		// there's no edit_comment_form hook!!!
		$this->edit_insert_tag_form();
		$this->edit_insert_tools();
		$this->edit_insert_machinetag_form();
	}
	
	function edit_insert_tag_form() {
		global $post_ID;
		?>
		<fieldset id="tagdiv">
			<legend>Tags (separate multiple tags with commas: cats, pet food, dogs)</legend>
			<div><input type="text" name="tags_input" class="tags-input" id="tags-input" size="30" tabindex="3" value="<?php echo get_tags_to_edit( $post_ID ); ?>" /></div>
		</fieldset>
		<?php
	}

	function edit_insert_tools() {
		global $post_ID;
		?>
		<fieldset id="bsuite_tools">
			<legend>bSuite Tools: <a id="bsuite_auto_tag_button">Auto Tag</a> <a id="bsuite_auto_excerpt_button">Auto Excerpt</a> (<a href="http://maisonbisson.com/blog/bsuite/auto-tag-excerpt">about these tools</a>) (NOT WORKING, for now)</legend>
		</fieldset>
		<?php
	}

	function edit_insert_machinetag_form() {
		global $post_ID;

		$tags = wp_get_object_terms($post_ID, get_object_taxonomies('post'));
		
		$tags_to_edit = array();
		foreach($tags as $tag){
			if($tag->taxonomy == 'post_tag' || $tag->taxonomy == 'category')
				continue;
			$tags_to_edit[] = $tag->taxonomy .'='. $tag->name;
		}
		natcasesort($tags_to_edit);
		?>
		<fieldset id="bsuite_machinetags">
			<legend>bSuite Machine Tags (separate multiple tags with newlines, <a href="http://maisonbisson.com/blog/bsuite/machine-tags" title="Machine Tag Documentation">about machine tags</a>)</legend>
			<div><textarea name="bsuite-machine-tags-input" id="bsuite-machine-tags-input" class="bsuite-machine-tags-input tags-input" style="width: 98%; height: 7em;"><?php echo implode($tags_to_edit, "\n"); ?></textarea></div>
		</fieldset>
		<?php
	}

	function edit_insert_excerpt_form() {
		global $post_ID, $post;
		?>
		<div class="dbx-b-ox-wrapper">
		<fieldset id="postexcerpt" class="dbx-box">
		<div class="dbx-h-andle-wrapper">
		<h3 class="dbx-handle">Optional Excerpt</h3>
		</div>
		<div class="dbx-c-ontent-wrapper">
		<div class="dbx-content"><textarea rows="1" cols="40" name="excerpt" tabindex="6" id="excerpt"><?php echo $post->post_excerpt ?></textarea></div>
		</div>
		</fieldset>
		</div>
		<?php
	}

	function insert_category_form() {
		?>
		<fieldset id="categorydiv" class="dbx-box">
		<h3 class="dbx-handle"><?php _e('Categories') ?></h3>
		<div class="dbx-content">
		<p id="jaxcat"></p>
		<ul id="categorychecklist"><?php dropdown_categories(); ?></ul></div>
		</fieldset>
		<?php
	}
	// end adding tools to edit screens




	// widgets
	function widget_related_posts($args) {
		global $post, $wpdb;
		
		if(!is_singular()) // can only run on single pages/posts
			return(NULL);
		
		$id = (int) $post->ID; // needs an ID of that page/post
		if(!id)
			return(NULL);
		
		extract($args, EXTR_SKIP);
		$options = get_option('bsuite_related_posts');
		$title = empty($options['title']) ? __('Related Posts', 'bsuite') : $options['title'];
		if ( !$number = (int) $options['number'] )
			$number = 5;
		else if ( $number < 1 )
			$number = 1;
		else if ( $number > 15 )
			$number = 15;

		if ( $related_posts = array_slice($this->bsuggestive_getposts(), 0, $number) ) {
?>
	
			<?php echo $before_widget; ?>
				<?php echo $before_title . $title . $after_title; ?>
				<ul id="relatedposts"><?php
				if ( $related_posts ) : foreach ($related_posts as $post_id) :
				echo  '<li class="relatedposts"><a href="'. get_permalink($post_id) . '">' . get_the_title($post_id) . '</a></li>';
				endforeach; endif;?></ul>
			<?php echo $after_widget; ?>
<?php
		}
	}
	
	function widget_related_posts_control() {
		$options = $newoptions = get_option('bsuite_related_posts');
		if ( $_POST['bsuite-related-posts-submit'] ) {
			$newoptions['title'] = strip_tags(stripslashes($_POST['bsuite-related-posts-title']));
			$newoptions['number'] = (int) $_POST['bsuite-related-posts-number'];
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('bsuite_related_posts', $options);
		}
		$title = attribute_escape($options['title']);
		if ( !$number = (int) $options['number'] )
			$number = 5;
	?>
				<p><label for="bsuite-related-posts-title"><?php _e('Title:'); ?> <input style="width: 250px;" id="bsuite-related-posts-title" name="bsuite-related-posts-title" type="text" value="<?php echo $title; ?>" /></label></p>
				<p><label for="bsuite-related-posts-number"><?php _e('Number of posts to show:'); ?> <input style="width: 25px; text-align: center;" id="bsuite-related-posts-number" name="bsuite-related-posts-number" type="text" value="<?php echo $number; ?>" /></label> <?php _e('(at most 15)'); ?></p>
				<input type="hidden" id="bsuite-related-posts-submit" name="bsuite-related-posts-submit" value="1" />
	<?php
	}

	function widget_sharelinks($args) {
		global $post, $wpdb;

		if(is_404() || $this->sharelinks_nonce) // no reason to run if it's a 404
			return(FALSE);

		extract($args, EXTR_SKIP);
		$options = get_option('bsuite_sharelinks');
		$title = empty($options['title']) ? __('Bookmark &amp; Feeds', 'bsuite') : $options['title'];

		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<ul id="sharelinks">';
		echo '<li><img src="' . get_settings('siteurl') .'/'. PLUGINDIR .'/'. plugin_basename(dirname(__FILE__))  . '/img/icon-share-16x16.gif" width="16" height="16" alt="bookmark and share icon" />&nbsp;<a href="'. $this->sharelinks_url() .'#bsuite_share_bookmark" title="bookmark and share links">Bookmark and Share</a></li>';
		echo '<li><img src="' . get_settings('siteurl') .'/'. PLUGINDIR .'/'. plugin_basename(dirname(__FILE__))  . '/img/icon-feed-16x16.png" width="16" height="16" alt="RSS and feeds icon" />&nbsp;<a href="'. $this->sharelinks_url() .'#bsuite_share_feed" title="RSS and feed links">RSS Feeds</a></li>';
		echo '<li><img src="' . get_settings('siteurl') .'/'. PLUGINDIR .'/'. plugin_basename(dirname(__FILE__))  . '/img/icon-translate-16x16.png" width="16" height="16" alt="RSS and feeds icon" />&nbsp;<a href="'. $this->sharelinks_url() .'#bsuite_share_translate" title="RSS and feed links">Translate</a></li>';
		echo '</ul>';
		echo $after_widget;
	}
	
	function widget_recently_commented_posts($args) {
		// this code pretty much directly rips off WordPress' native recent comments widget,
		// the difference here is that I'm displaying recently commented posts, not recent comments.
		global $wpdb, $commented_posts;
		extract($args, EXTR_SKIP);
		$options = get_option('bsuite_recently_commented_posts');
		$title = empty($options['title']) ? __('Recently Commented Posts', 'bsuite') : $options['title'];
		if ( !$number = (int) $options['number'] )
			$number = 5;
		else if ( $number < 1 )
			$number = 1;
		else if ( $number > 15 )
			$number = 15;
	
		if ( !$commented_posts = wp_cache_get( 'recently_commented_posts', 'widget' ) ) {
			$commented_posts = $wpdb->get_results("SELECT comment_ID, comment_post_ID, COUNT(comment_post_ID) as comment_count, MAX(comment_date_gmt) AS sort_order FROM $wpdb->comments WHERE comment_approved = '1' GROUP BY comment_post_ID ORDER BY sort_order DESC LIMIT $number");
			wp_cache_add( 'recently_commented_posts', $commented_posts, 'widget' );
		}
	?>
	
			<?php echo $before_widget; ?>
				<?php echo $before_title . $title . $after_title; ?>
				<ul id="recentcomments"><?php
				if ( $commented_posts ) : foreach ($commented_posts as $comment) :
				echo  '<li class="recentcomments"><a href="'. get_permalink($comment->comment_post_ID) . '#comment-' . $comment->comment_ID . '">' . get_the_title($comment->comment_post_ID) . '</a>&nbsp;('. $comment->comment_count .')</li>';
				endforeach; endif;?></ul>
			<?php echo $after_widget; ?>
	<?php
	}
	
	function widget_recently_commented_posts_delete_cache() {
		wp_cache_delete( 'recently_commented_posts', 'widget' );
	}

	function widget_recently_commented_posts_control() {
		$options = $newoptions = get_option('bsuite_recently_commented_posts');
		if ( $_POST['bsuite-recently-commented-posts-submit'] ) {
			$newoptions['title'] = strip_tags(stripslashes($_POST['bsuite-recently-commented-posts-title']));
			$newoptions['number'] = (int) $_POST['bsuite-recently-commented-posts-number'];
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('bsuite_recently_commented_posts', $options);
			$this->widget_recently_commented_posts_delete_cache();
		}
		$title = attribute_escape($options['title']);
		if ( !$number = (int) $options['number'] )
			$number = 5;
	?>
				<p><label for="bsuite-recently-commented-posts-title"><?php _e('Title:'); ?> <input style="width: 250px;" id="bsuite-recently-commented-posts-title" name="bsuite-recently-commented-posts-title" type="text" value="<?php echo $title; ?>" /></label></p>
				<p><label for="bsuite-recently-commented-posts-number"><?php _e('Number of posts to show:'); ?> <input style="width: 25px; text-align: center;" id="bsuite-recently-commented-posts-number" name="bsuite-recently-commented-posts-number" type="text" value="<?php echo $number; ?>" /></label> <?php _e('(at most 15)'); ?></p>
				<input type="hidden" id="bsuite-recently-commented-posts-submit" name="bsuite-recently-commented-posts-submit" value="1" />
	<?php
	}
	
	function widget_recently_commented_posts_register() {
		$class = array('classname' => 'bsuite_recently_commented_posts');
		wp_register_sidebar_widget('bsuite-recently-commented-posts', __('bSuite Recently Commented', 'bsuite'), array($this, 'widget_recently_commented_posts'), $class);
		wp_register_widget_control('bsuite-recently-commented-posts', __('bSuite Recently Commented', 'bsuite'), array($this, 'widget_recently_commented_posts_control'), $class, 'width=320&height=90');
	
		if ( is_active_widget('widget_recently_commented_posts') ){
			add_action('wp_head', 'wp_widget_recent_comments_style');
			add_action( 'comment_post', array(&$this, 'widget_recently_commented_posts_delete_cache' ));
			add_action( 'wp_set_comment_status', array(&$this, 'widget_recently_commented_posts_delete_cache' ));
		}
	}

	function widgets_register(){
		$this->widget_recently_commented_posts_register();

		wp_register_sidebar_widget('bsuite-related-posts', __('bSuite Related Posts', 'bsuite'), array(&$this, 'widget_related_posts'), 'bsuite_related_posts');
		wp_register_widget_control('bsuite-related-posts', __('bSuite Related Posts', 'bsuite'), array(&$this, 'widget_related_posts_control'), 'width=320&height=90');

		wp_register_sidebar_widget('bsuite-sharelinks', __('bSuite Share Links', 'bsuite'), array(&$this, 'widget_sharelinks'), 'bsuite_sharelinks');
	}
	// end widgets



	// administrivia
	function activate() {
		$this->createtables();

		// set some defaults for the widgets
		if(!get_option('bsuite_related_posts'))
			update_option('bsuite_related_posts', array('title' => 'Related Posts', 'number' => 7));

		if(!get_option('bsuite_recently_commented_posts'))
			update_option('bsuite_recently_commented_posts', array('title' => 'Recently Commented Posts', 'number' => 7));
	}

	function createtables() {
		global $wpdb;

		$charset_collate = '';
		if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') ) {
			if ( ! empty($wpdb->charset) )
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) )
				$charset_collate .= " COLLATE $wpdb->collate";
		}

		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		dbDelta("
			CREATE TABLE $this->search_table (
				post_id bigint(20) NOT NULL,
				content text,
				title text,
				PRIMARY KEY  (post_id),
				FULLTEXT KEY search (content, title)
			) ENGINE=MyISAM $charset_collate
			");
	}

	function addmenus() {
		add_options_page('bSuite Settings', 'bSuite', 8, __FILE__, array(&$this, 'optionspage'));
	}

	function optionspage() {
		global $wpdb;
		//require(ABSPATH . PLUGINDIR .'/'. plugin_basename(dirname(__FILE__)) .'/core_admin.php');

		//  apply new settings if form submitted
		if($_REQUEST['Options'] == __('Rebuild bSuite search index', 'bsuite')){		
			$this->rebuildmetatables();
		}else if($_REQUEST['Options'] == __('Flush WP object cache', 'bsuite')){
			wp_cache_flush();
			echo '<div class="updated"><p><strong>' . __('WordPress object cache flushed.', 'bsuite') . '</strong></p></div>';
		}else if($_REQUEST['Options'] == __('PHP Info', 'bsuite')){
			phpinfo();
		}


		//  output settings/configuration form
?>
<div class="wrap">
<h2><?php _e('Commands') ?></h2>
<form method="post">

<fieldset name="bsuite_general" class="options">
	<table width="100%" cellspacing="2" cellpadding="5" class="editform">
		<tr valign="top">
			<div class="submit"><input type="submit" name="Options" value="<?php _e('Rebuild bSuite search index', 'bsuite') ?>" /> &nbsp; 
			<input type="submit" name="Options" value="<?php _e('Flush WP object cache', 'bsuite') ?>" /> &nbsp; 
			<input type="submit" name="Options" value="<?php _e('PHP Info', 'bsuite') ?>" /> &nbsp; 
			</div>
		</tr>
	</table>
</fieldset>

</form>
</div>
<?php
	}

	function kses_allowedposttags() {
		global $allowedposttags;
		$allowedposttags['h1']['id'] = array();
		$allowedposttags['h1']['class'] = array();
		$allowedposttags['h2']['id'] = array();
		$allowedposttags['h2']['class'] = array();
		$allowedposttags['h3']['id'] = array();
		$allowedposttags['h3']['class'] = array();
		$allowedposttags['h4']['id'] = array();
		$allowedposttags['h4']['class'] = array();
		$allowedposttags['h5']['id'] = array();
		$allowedposttags['h5']['class'] = array();
		$allowedposttags['h6']['id'] = array();
		$allowedposttags['h6']['class'] = array();
		return(TRUE);
	}


	function rebuildmetatables() {
		// update search table with content from all posts
		global $wpdb; 
	
		set_time_limit(0);
		ignore_user_abort(TRUE);
		$interval = 50;


		if( !isset( $_GET[ 'n' ] ) ) {
			$n = 0;
			$wpdb->hide_errors();
			$this->createtables();		
			$wpdb->show_errors();
		} else {
			$n = (int) $_GET[ 'n' ] ;
		}
		$posts = $wpdb->get_results("SELECT ID, post_content, post_title
			FROM $wpdb->posts
			ORDER BY ID
			LIMIT $n, $interval
			", ARRAY_A);
		if( is_array( $posts ) ) {
			echo '<div class="updated"><p><strong>' . __('Rebuilding bSuite search index. Please be patient.', 'bsuite') . '</strong></p></div><div class="narrow">';
			print "<ul>";
			foreach( $posts as $post ) {
				$this->searchsmart_upindex($post['ID'], $post['post_content'],  $post['post_title']);
				echo '<li><a href="'. get_permalink($post['ID']) .'">updated post '. $post['ID'] ."</a></li>\n ";
				flush();
			}
			print "</ul>";
			?>
			<p><?php _e("If your browser doesn't start loading the next page automatically click this link:"); ?> <a href="?page=<?php echo plugin_basename(dirname(__FILE__)); ?>/core.php&Options=Rebuild+bsuite+metadata+index&n=<?php echo ($n + $interval) ?>"><?php _e("Next Posts"); ?></a> </p></div>
			<script language='javascript'>
			<!--

			function nextpage() {
				location.href="?page=<?php echo plugin_basename(dirname(__FILE__)); ?>/core.php&Options=Rebuild+bsuite+metadata+index&n=<?php echo ($n + $interval) ?>";
			}
			setTimeout( "nextpage()", 250 );

			//-->
			</script>
			<?php
		} else {
			echo '<div class="updated"><p><strong>'. __('bSuite metdata index rebuilt.', 'bsuite') .'</strong></p></div>';
			?>
			<script language='javascript'>
			<!--

			function nextpage() {
				location.href="?page=<?php echo plugin_basename(dirname(__FILE__)); ?>/core.php";
			}
			setTimeout( "nextpage()", 3000 );

			//-->
			</script>
			<?php
		}
	}
}

// now instantiate this object
$bsuite = & new bSuite;

function the_related(){
	global $bsuite;
	echo $bsuite->bsuggestive_the_related();
}

function paginated_links(){
	GLOBAL $wp_query;


	$page = 1;
	if( (int) $wp_query->query_vars['paged'] )
		$page = (int) $wp_query->query_vars['paged'];
	$total = (int) $wp_query->max_num_pages;
	
	$page_links = paginate_links( array(
		'base' => add_query_arg( 'paged', '%#%' ),
		'format' => '',
		'total' => $total,
		'current' => $page
	));
	
	if ( $page_links )
		echo "<p class='pagenav'>$page_links</p>";
}

function bsuite_feedlink() {
	global $bsuite;
	return( $bsuite->feedlink() );
}

function bsuite_link2me() {
	global $bsuite;
	echo $bsuite->link2me();
}


// php4 compatibility, argh
if(!function_exists('str_ireplace')){
function str_ireplace($a, $b, $c){
	return str_replace($a, $b, $c);
}
}


?>