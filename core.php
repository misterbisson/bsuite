<?php
/*
Plugin Name: bSuite
Plugin URI: http://maisonbisson.com/blog/bsuite
Description: It's okay for a few things, but you could probably do better. <a href="http://maisonbisson.com/blog/bsuite/core">Documentation here</a>.
Version: 3.00 
Author: Casey Bisson
Author URI: http://maisonbisson.com/blog/
*/


class bSuite {

	function bSuite(){
		$this->verso = 'a1';

		global $wpdb;
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
		add_filter('widget_text', array(&$this, 'tokens'), 0);
		
		//innerindex
		add_filter('content_save_pre', array(&$this, 'innerindex_nametags'));
		add_filter('save_post', array(&$this, 'innerindex_delete_cache'));
		add_filter('publish_post', array(&$this, 'innerindex_delete_cache'));
		add_filter('publish_page', array(&$this, 'innerindex_delete_cache'));

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
		
		// searchsmart
		add_filter('posts_request', array(&$this, 'searchsmart_query'), 10);
		add_filter('template_redirect', array(&$this, 'searchsmart_onsingle'), 8);
		add_filter('content_save_pre', array(&$this, 'searchsmart_upindex_onedit'));
		
		// CMS goodies
		add_action('dbx_page_advanced', array(&$this, 'insert_excerpt_form'));
		add_action('edit_form_advanced', array(&$this, 'edit_post_form'));
		add_action('edit_page_form', array(&$this, 'edit_page_form'));
		add_action('widgets_init', array(&$this, 'widgets_register'));

		// activation and menu hooks
		register_activation_hook(__FILE__, array(&$this, 'createtables'));
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

		if ( !$menu = wp_cache_get( 'bsuite_innerindex_'. $id, 'default' ) ) {
			$menu = $commented_posts = $this->innerindex_build($post_cache[1][$id]->post_content);
			wp_cache_add( 'bsuite_innerindex_'. $id, $menu, 'default', 864000 );
		}

		return('<div class="innerindex"><h1>'. $title .'</h1>'. $menu .'</div>');
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
		wp_cache_delete( 'bsuite_innerindex_'. $id, 'default' );
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
		$athooks['related']['title'] = 'Related';

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
	function athook_get_post($thing){
		// get related items
		global $wp;

		$query_vars = &$this->parse_request(str_replace($thing, '', $wp->request));
		return($query_vars['p']);
	}
	// end athook-related functions



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

	function searchsmart_upindex_onedit($content){
		// called when posts are saved
		if($_POST['post_ID'])
			$this->searchsmart_upindex(ereg_replace('[^0-9]', '', $_POST['post_ID']), $content);
		return($content);
	}
	// end Searchsmart


	// bSuggestive related functions
	function bsuggestive_tags($id = 0) {
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
	
	function bsuggestive_query($the_tags, $id) {
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
	
	function bsuggestive_getposts($id = 0) {
		global $post, $wpdb;
	
		$id = (int) $id;
		if ( !$id )
			$id = (int) $post->ID;


		if ( !$related_posts = wp_cache_get( 'bsuite_related_posts_'. $id, 'default' ) ) {
			if(($the_tags = $this->bsuggestive_tags($id)) && ($the_query = $this->bsuggestive_query($the_tags, $id))){
				$related_posts = $wpdb->get_col($the_query);
				wp_cache_add( 'bsuite_related_posts_'. $id, $related_posts, 'default', 864000 );
				return($related_posts); // if we have to go to the DB to get the posts, then this will get returned
			}
			return FALSE; // if there's nothing in the cache and we've got no tags, then we return false
		}
		return($related_posts); // if the cache is still warm, then we return this
	}

	function bsuggestive_delete_cache($id) {
		$id = (int) $id;
		wp_cache_delete( 'bsuite_related_posts_'. $id, 'default' );
	}

	function bsuggestive_postlist($before = '<li>', $after = '</li>') {
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



	// add tools to edit screens
	function edit_page_form() {
		$this->edit_insert_tag_form();
		$this->edit_insert_tools();
		$this->edit_insert_machinetag_form();
	}
	
	function edit_post_form() {
		$this->edit_insert_tools();
		$this->edit_insert_machinetag_form();
	}

	function edit_comment_form() {
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
			<legend>bSuite Machine Tags (separate multiple tags with newlines, <a href="http://maisonbisson.com/blog/bsuite/machine-tags" title="Machine Tag Documentation">about machine tags</a>) (READ-ONLY, for now)</legend>
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
		$title = empty($options['title']) ? __('Recently Commented Posts') : $options['title'];
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
			delete_recent_comments_cache();
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

	function widget_recently_commented_posts($args) {
		// this code pretty much directly rips off WordPress' native recent comments widget,
		// the difference here is that I'm displaying recently commented posts, not recent comments.
		global $wpdb, $commented_posts;
		extract($args, EXTR_SKIP);
		$options = get_option('bsuite_recently_commented_posts');
		$title = empty($options['title']) ? __('Recently Commented Posts') : $options['title'];
		if ( !$number = (int) $options['number'] )
			$number = 5;
		else if ( $number < 1 )
			$number = 1;
		else if ( $number > 15 )
			$number = 15;
	
		if ( !$commented_posts = wp_cache_get( 'recently_commented_posts', 'widget' ) ) {
			$commented_posts = $wpdb->get_results("SELECT comment_ID, comment_post_ID, COUNT(comment_post_ID) as comment_count FROM $wpdb->comments WHERE comment_approved = '1' GROUP BY comment_post_ID ORDER BY comment_date_gmt DESC LIMIT $number");
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
			delete_recent_comments_cache();
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
		wp_register_sidebar_widget('bsuite-recently-commented-posts', __('bSuite Recently Commented'), array($this, 'widget_recently_commented_posts'), $class);
		wp_register_widget_control('bsuite-recently-commented-posts', __('bSuite Recently Commented'), array($this, 'widget_recently_commented_posts_control'), $class, 'width=320&height=90');
	
		if ( is_active_widget('widget_recently_commented_posts') ){
			add_action('wp_head', 'wp_widget_recent_comments_style');
			add_action( 'comment_post', array(&$this, 'widget_recently_commented_posts_delete_cache' ));
			add_action( 'wp_set_comment_status', array(&$this, 'widget_recently_commented_posts_delete_cache' ));
		}
	}

	function widgets_register(){
		$this->widget_recently_commented_posts_register();

		wp_register_sidebar_widget('bsuite-related-posts', __('bSuite Related Posts'), array(&$this, 'widget_related_posts'), 'bsuite_related_posts');
		wp_register_widget_control('bsuite-related-posts', __('bSuite Related Posts'), array(&$this, 'widget_related_posts_control'), 'width=320&height=90');
	}
	// end widgets



	// administrivia
	function createtables() {
		require(ABSPATH . PLUGINDIR .'/'. plugin_basename(dirname(__FILE__)) .'/core_createtables.php');
	}

	function addmenus() {
		add_options_page('bSuite Settings', 'bSuite', 8, __FILE__, array(&$this, 'optionspage'));
	}

	function optionspage() {
		global $wpdb;
		require(ABSPATH . PLUGINDIR .'/'. plugin_basename(dirname(__FILE__)) .'/core_admin.php');
	}



	function rebuildmetatables() {
		// update search table with content from all posts
		global $wpdb; 
	
		set_time_limit(0);
		ignore_user_abort(TRUE);
		$interval = 50;

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
			<p><?php _e("If your browser doesn't start loading the next page automatically click this link:"); ?> <a href="?page=<?php echo plugin_basename(dirname(__FILE__)); ?>/core.php&Options=Rebuild+bsuite+metadata+index&n=<?php echo ($n + $interval) ?>"><?php _e("Next Posts"); ?></a> </p>
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
			echo '<p><strong>'. __('bSuite metdata index rebuilt.', 'bsuite') .'</strong></p>';
		}
	}
}

// now instantiate this object
$bsuite = & new bSuite;

function the_related(){
	global $bsuite;
	echo $bsuite->bsuggestive_postlist();
}

?>