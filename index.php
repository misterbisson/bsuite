<?php
/*
Plugin Name: bSuite
Plugin URI: http://maisonbisson.com/blog/bsuite
Description: It's okay for a few things, but you could probably do better. <a href="http://maisonbisson.com/blog/bsuite/core">Documentation here</a>.
Version: 3.00 
Author: Casey Bisson
Author URI: http://maisonbisson.com/blog/
*/


class bsuite {

	function __construct(){
		$this->verso = 'a1';

		global $wpdb;
		$this->cache_table = $wpdb->prefix . 'bsuite3_speedcache';
		$this->search_table = $wpdb->prefix . 'bsuite3_search';
		
		if(!$wpdb->blogid)
			$wpdb->blogid = 1;

		$this->options = unserialize(get_option('bsuite3'));

		//
		// register hooks
		//
		
		// tokens
		add_filter('bsuite_tokens', array(&$this, 'tokens_default'));
		add_filter('the_content', array(&$this, 'tokens'), 0);
		add_filter('the_content_rss', array(&$this, 'tokens'), 0);
		add_filter('the_excerpt', array(&$this, 'tokens'), 0);
		add_filter('the_excerpt_rss', array(&$this, 'tokens'), 0);
		add_filter('get_the_excerpt ', array(&$this, 'tokens'), 0);
		
		//innerindex
		add_filter('content_save_pre', array(&$this, 'innerindex_nametags'));
		
		// pagehooks
		add_filter('bsuite_pagehooks', array(&$this, 'pagehooks_default'));
		add_filter('template_redirect', array(&$this, 'pagehooks'), 8);
		
		// athooks
		add_filter('bsuite_athooks', array(&$this, 'athooks_default'));
		add_filter('template_redirect', array(&$this, 'athooks_onredirect'), 8);
		add_filter('posts_request', array(&$this, 'athooks_onsearch'), 8);
		add_filter('get_breadcrumb', array(&$this, 'athooks_breadcrumbs'), 5, 2);
		
		// searchsmart
		add_filter('posts_request', array(&$this, 'searchsmart_query'), 10);
		add_filter('template_redirect', array(&$this, 'searchsmart_onsingle'), 8);
		add_filter('content_save_pre', array(&$this, 'searchsmart_upindex_onedit'));
		
		//
		add_action('activate_bsuite_core/index.php', array(&$this, 'createtables'));
		add_action('admin_menu', array(&$this, 'addmenus'));
		// end register WordPress hooks

		// set things up so authors can edit their own pages
		$role = get_role('author');
		if ( ! empty($role) ) {
			$role->add_cap('edit_pages');
			$role->add_cap('edit_published_pages');
		}
	}



	//
	// token functions
	// tokens are [[token]] in the content of a post.
	//
	public function tokens_get(){
		// establish list of tokens
		static $tokens = FALSE;	
		if($tokens)
			return($tokens);
	
		$tokens = array();
		$tokens = apply_filters('bsuite_tokens', $tokens);

		return($tokens);
	}

	public function tokens_fill($thing) {
		// match tokens
		$return = $thing[0];
		$thing = explode('|', trim($thing[0], '[]'), 2);
		$tokens = &$this->tokens_get();

		if($tokens[$thing[0]])
			$return = call_user_func_array($tokens[$thing[0]], $thing[1]);

		return($return);
	}

	public function tokens($content) {
		// find tokens in the page
		$content = preg_replace_callback(
			'/\[\[([^\]\]])*\]\]/',
			array(&$this, 'tokens_fill'),
			$content);
		return($content);
	}

	public function tokens_default($tokens){
		// setup some default tokens
		$tokens['date'] = array(&$this, 'token_get_date');
		$tokens['pagemenu'] = array(&$this, 'token_get_pagemenu');
		$tokens['innerindex'] = array(&$this, 'innerindex');
		$tokens['feed'] = array(&$this, 'token_get_feed');
		$tokens['redirect'] = array(&$this, 'token_get_redirect');

		return($tokens);
	}
	
	public function token_get_date($stuff = 'F j, Y, g:i a'){
		// [[date|options]]
		return(date($stuff));
	}
	
	public function token_get_pagemenu($stuff = NULL){
		// [[pagemenu|depth|extra]]
		// [[pagemenu|1|sort_column=post_date&sort_order=DESC]]
		global $id;
		$stuff = explode('|', $stuff);
		return(wp_list_pages("child_of=$id&depth=1&echo=0&sort_column=menu_order&title_li=&$stuff[0]"));
	}
	
	public function token_get_redirect($stuff){
		// [[redirect|$url]]
		if(!headers_sent())
			header("Location: $stuff");
		return('redirect: <a href="'. $stuff .'">'. $stuff .'</a>');
	}

	public function token_get_feed($stuff){
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
	public function innerindex($title = 'Contents:'){
		// !!!
		// need to look at expiring the innerindex cache when 
		// the permalink structure changes
		//
		global $id, $post_cache;
		$menu = &$this->cachefetch($id, 99999, TRUE, 'innerindex');
		if($menu){
			return('<div class="innerindex"><h1>'. $title .'</h1>'. $menu .'</div>');
		}else{
			return('<div class="innerindex"><h1>'. $title .'</h1>'. $this->cacheput($id, $this->innerindex_build($post_cache[1][$id]->post_content), FALSE, 'innerindex') .'</div>');
		}		
	}
	
	public function innerindex_build($content){
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

	public function innerindex_nametags($content){
		// find <h*> tags in the content
		$content = preg_replace_callback(
			"/(\<h([0-9])?([^\>]*)?\>)(.*?)(\<\/h[0-9]\>)/",
			array(&$this,'innerindex_nametags_callback'),
			$content
			);
		return($content);
	}

	public function innerindex_nametags_callback($content){
		// receive <h*> tags and insert the ID
		static $slugs;
		$slugs[] = $slug = substr(sanitize_title_with_dashes($content[4]), 0, 20);
		$content = "<h{$content[2]} id=\"{$_POST['post_ID']}_{$slug}_". count(array_keys($slugs, $slug)) .'" '. trim(preg_replace('/id[^"]*"[^"]*"/', '', $content[3])) .">{$content[4]}{$content[5]}";
		return($content);
	}
	// end innerindex-related
	
	
	//
	// pagehook functions
	//
	public function pagehooks_get(){
		// establish list of pagehooks
		static $pagehooks = FALSE;	
		if($pagehooks)
			return($pagehooks);
	
		$pagehooks = array();
		$pagehooks = apply_filters('bsuite_pagehooks', $pagehooks);

		return($pagehooks);
	}

	public function pagehooks_default($pagehooks){
		// setup some default pagehooks, but there are none
		// $pagehooks['pagename'] = 'function_name';
		return($pagehooks);
	}

	public function pagehooks(){
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
	public function athooks_get(){
		// establish list of athooks
		static $athooks = FALSE;	
		if($athooks)
			return($athooks);
	
		$athooks = array();
		$athooks = apply_filters('bsuite_athooks', $athooks);

		return($athooks);
	}

	public function athooks_default($athooks){
		// setup some default athooks
		// $athooks[hook name][where to act (request or redirect)] = 'function name';

		$athooks['related']['request'] = array(&$this, 'athook_get_related');
		$athooks['related']['title'] = 'Related';

		return($athooks);
	}

	public function athooks_onredirect(){
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

	public function athooks_onsearch($search){
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

	public function athooks_breadcrumbs($breadcrumb, $params){
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

	public function athook_get_related($search){
		// get related items
		global $wp_query, $wpdb;

		$query_vars['p'] = $wp_query->athook['post'];

		if(($query_vars['p']) && ($the_tags = $this->bsuggestive_tags($query_vars['p']))){

			unset($wp_query->query_vars['attachment']);
			$wp_query->query = 's='. implode('|', $the_tags);		
			$wp_query->query_vars['s'] = implode('|', $the_tags);		
			$wp_query->is_404 = FALSE;
			$wp_query->is_attachment = FALSE;
			$wp_query->is_search = TRUE;
			$wp_query->is_page = FALSE;
			$wp_query->is_single = FALSE;
			$wp_query->is_404 = FALSE;
			$wp_query->is_attachment = FALSE;
			$wp_query->is_archive = FALSE;
			$wp_query->is_category = FALSE;

		}

		return($search);
	}
	public function athook_get_post($thing){
		// get related items
		global $wp;

		$query_vars = &$this->parse_request(str_replace($thing, '', $wp->request));
		return($query_vars['p']);
	}
	// end athook-related functions



	//
	// Searchsmart
	//
	public function searchsmart_query($query){
	
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

			$query = "SELECT SQL_CALC_FOUND_ROWS $wpdb->posts.*, MATCH (content, title) AGAINST ('{$wp_query->query_vars['s']}') AS relevance 
				FROM $wpdb->posts 
				LEFT JOIN $this->search_table ON ( post_id = ID )  
				WHERE 1=1 
				AND (MATCH (content, title) AGAINST ('{$wp_query->query_vars['s']}'))
				AND (post_type IN ('post', 'page') AND (post_status IN ('publish', 'private')))
				ORDER BY relevance DESC LIMIT $limit[1]
				";
			
//echo "<pre>$query</pre>";
		}
		return($query);
	}

	public function searchsmart_onsingle(){
		// redirects the search to the single page if the search returns only one item
		global $wp_query;
		if($wp_query->is_search && $wp_query->post_count == 1){
			//$wp_query->is_search = NULL;
			//$wp_query->is_single = TRUE;
			header('Location: '. get_permalink($wp_query->post->ID));
		}
		return(TRUE);
	}

	public function searchsmart_upindex($post_id, $content, $title = ''){
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

		$title = preg_replace(
			'/([[:punct:]])*/',
			'',
			html_entity_decode($title, ENT_QUOTES, 'UTF-8'));

		$request = "REPLACE INTO $this->search_table
					(post_id, content, title) 
					VALUES ($post_id, '". $content ."', '$title')";
		
		$wpdb->get_results($request);

		return(TRUE);
	}

	public function searchsmart_upindex_onedit($content){
		// called when posts are saved
		if($_POST['post_ID'])
			$this->searchsmart_upindex(ereg_replace('[^0-9]', '', $_POST['post_ID']), $content);
		return($content);
	}
	// end Searchsmart


	// bSuggestive related functions
	public function bsuggestive_tags($id = 0) {
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
		
		if(empty($the_tags[0]))
			return FALSE;

		return apply_filters('bsuite_suggestive_tags', $the_tags);
	}
	
	public function bsuggestive_query($the_tags, $id) {
		global $wpdb;
		$id = (int) $id;
		if($id && is_array($the_tags)){
			return apply_filters('bsuite_suggestive_query', 
				"SELECT post_id
						FROM $this->search_table 
						LEFT JOIN $wpdb->posts
						ON post_id = ID
						WHERE MATCH (content, title)
						AGAINST ('". ereg_replace('[^a-z|A-Z|0-9| ]', ' ', implode(' ', $the_tags))) ."') AND post_id <> $id
						AND post_status = 'publish'
						LIMIT 50
						";
		}
		return FALSE;
	}
	
	public function bsuggestive_getposts($id = 0) {
		global $post, $wpdb;
	
		$id = (int) $id;
		if ( !$id )
			$id = (int) $post->ID;

		if(($id) && ($the_tags = $this->bsuggestive_tags($id)) && ($the_query = $this->bsuggestive_query($the_tags, $id))){
			$rows = $wpdb->get_col($the_query);
			return($rows);
		}
		return FALSE;
	}

	public function bsuggestive_postlist($before = '<li>', $after = '</li>') {
		$report = FALSE;

		$posts = array_slice($this->bsuggestive_getposts(), 0, 5);
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

	public function parse_request($request){
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



	public function pagetree(){
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
	public function get_loadavg(){
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

	public function sys_getloadavg(){
		// the following code taken from tom pittlik's comment at
		// http://php.net/manual/en/function.sys-getloadavg.php
		$str = substr(strrchr(shell_exec('uptime'),':'),1);
		$avs = array_map('trim',explode(',',$str));
		return $avs;
	}
	// end load average related functions

	
	
	//
	// speedcache related functions
	//
	public function cachefetch($obj, $minutes = 30, $return = FALSE, $lib = 'user') {
		// fetch items from the cache
		if($_REQUEST['bsuite_cachekill'] == 1)
			return(FALSE);
	
		global $wpdb;
		
		$date  = date("Y-m-d H:i:s", mktime( date("H"), date("i") - $minutes, date("s"), date("m"), date("d"), date("Y")));

		$rows = $wpdb->get_results("SELECT * FROM $this->cache_table
			WHERE cache_item = '". addslashes($obj) ."'
			AND cache_bank = '". addslashes($lib) ."'
			LIMIT 1");
		
		if(count($rows)){
			if(strtotime('+ '. $minutes .' minutes') > strtotime($rows[0]->cache_date)){
				$cachedata = '<!-- start bsuite_speedcache object ' . $rows[0] -> cache_bank . '/'. $rows[0] -> cache_item . ' on ' . $rows[0] -> cache_date . '-->' . $rows[0] -> cache_content . '<!-- end bsuite_speedcache object -->';
			}else{
				return(FALSE);	
			}
	
			if($return){
				return($cachedata);
			}else{
				echo $cachedata;
				return(TRUE);
			}
		}else{
			return(FALSE);
		}
	}

	public function cacheput($obj, $content, $echo = TRUE, $lib = 'user') {
		// put objects into the cache
		global $wpdb;
	
		$wpdb->query("REPLACE INTO $this->cache_table
			(cache_date, cache_bank, cache_item, cache_content) VALUES ('". date("Y-m-d H:i:s") ."', '". addslashes($lib) ."', '". addslashes($obj) ."', '". addslashes($content) ."')");
		if($echo){
			echo '<!-- start bsuite_speedcache object ' . $lib . '/'. $obj . ' refreshed -->' . $content . '<!-- end bsuite_speedcache object -->';
		}else{
			return($content);
		}
	}
	// end speedcache related functions



	// set a cookie
	public function cookie($name, $value = NULL) {
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
	public function get_feed($feed, $count = 15, $template = '<li>%%title%%<br />%%content%%</li>', $return = FALSE){
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



	public function createtables() {
		require(ABSPATH . PLUGINDIR .'/bsuite_core/core_createtables.php');
	}

	public function addmenus() {
		add_options_page('bsuite Settings', 'bsuite', 8, __FILE__, array(&$this, 'optionspage'));
	}

	public function optionspage() {
		global $wpdb;
		require(ABSPATH . PLUGINDIR .'/bsuite_core/core_admin.php');
	}



	public function rebuildmetatables() {
		// update search table with content from all posts
		global $wpdb; 
	
		set_time_limit(0);
		ignore_user_abort(TRUE);
		$interval = 500;

		if( isset( $_GET[ 'n' ] ) == false ) {
			$n = 0;
		} else {
			$n = intval( $_GET[ 'n' ] );
		}
	
		$posts = $wpdb->get_results("SELECT ID, post_content, post_title
			FROM $wpdb->posts
			ORDER BY ID
			LIMIT $n, $interval
			", ARRAY_A);
		if( is_array( $posts ) ) {
			print "<ul>";
			foreach( $posts as $post ) {
				$this->searchsmart_upindex($post['ID'], $post['post_content'],  $post['post_title']);
				echo '<li><a href="'. get_permalink($post['ID']) .'">updated post '. $post['ID'] ."</a></li>\n ";
				flush();
			}
			print "</ul>";
			?>
			<p><?php _e("If your browser doesn't start loading the next page automatically click this link:"); ?> <a href="?page=bsuite_core/index.php&Options=Rebuild+bsuite+metadata+index&n=<?php echo ($n + $interval) ?>"><?php _e("Next Posts"); ?></a> </p>
			<script language='javascript'>
			<!--

			function nextpage() {
				location.href="?page=bsuite_core/index.php&Options=Rebuild+bsuite+metadata+index&n=<?php echo ($n + $interval) ?>";
			}
			setTimeout( "nextpage()", 250 );

			//-->
			</script>
			<?php
		} else {
			echo '<p><strong>'. __('bSuite metdata index rebuilt.', 'bsuite') .'</strong></p>';
		}
	}







}

// now instantiate this object
$bsuite = & new bsuite;

function the_related(){
	global $bsuite;
	echo $bsuite->bsuggestive_postlist();
}

?>