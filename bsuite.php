<?php
/*
Plugin Name: bSuite
Plugin URI: http://maisonbisson.com/blog/bsuite/
Description: Stats tracking, improved sharing, related posts, CMS features, and a kitchen sink. <a href="http://maisonbisson.com/blog/bsuite/">Documentation here</a>.
Version: 4.0 beta1 
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
		$this->search_table = $wpdb->prefix . 'bsuite4_search';

		$this->hits_incoming = $wpdb->prefix . 'bsuite4_hits_incoming';
		$this->hits_terms = $wpdb->prefix . 'bsuite4_hits_terms';
		$this->hits_targets = $wpdb->prefix . 'bsuite4_hits_targets';
		$this->hits_searchphrases = $wpdb->prefix . 'bsuite4_hits_searchphrases';
//		$this->hits_searchwords = $wpdb->prefix . 'bsuite4_hits_searchwords';
		$this->hits_sessions = $wpdb->prefix . 'bsuite4_hits_sessions';
		$this->hits_shistory = $wpdb->prefix . 'bsuite4_hits_shistory';
		$this->hits_pop = $wpdb->prefix . 'bsuite4_hits_pop';

		$this->lock_migrator = $wpdb->prefix . 'bsuite4_lock_migrator';
		$this->lock_ftindexer = $wpdb->prefix . 'bsuite4_lock_ftindexer';
		
		$this->loadavg = $this->get_loadavg();

		// establish web path to this plugin's directory
		$this->path_web = '/'. PLUGINDIR .'/'. plugin_basename( dirname( __FILE__ ));

		// register and queue javascripts
		wp_register_script( 'bsuite', $this->path_web . '/js/bsuite.js', array('jquery'), '20080503' );
		wp_enqueue_script( 'bsuite' );	

		// jQuery text highlighting plugin http://johannburkard.de/blog/programming/javascript/highlight-javascript-text-higlighting-jquery-plugin.html
		wp_register_script( 'highlight', $this->path_web . '/js/jquery.highlight-1.js', array('jquery'), '1' );
		wp_enqueue_script( 'highlight' );	

		// is this wpmu?
		if( function_exists( 'is_site_admin' ))
			$this->is_mu = TRUE;
		else
			$this->is_mu = FALSE;



		//
		// register hooks
		//

		// shortcodes
		add_shortcode('pagemenu', array(&$this, 'shortcode_pagemenu'));
		add_shortcode('innerindex', array(&$this, 'shortcode_innerindex'));
		add_shortcode('feed', array(&$this, 'shortcode_feed'));
//		add_shortcode('redirect', array(&$this, 'shortcode_redirect'));

		// tokens
		// tokens are deprecated. please use shortcode functionality instead.
		add_filter('bsuite_tokens', array(&$this, 'tokens_default'));
		add_filter('the_content', array(&$this, 'tokens_the_content'), 0);
		add_filter('the_content_rss', array(&$this, 'tokens_the_content_rss'), 0);
		add_filter('the_excerpt', array(&$this, 'tokens_the_excerpt'), 0);
		add_filter('the_excerpt_rss', array(&$this, 'tokens_the_excerpt_rss'), 0);
		add_filter('get_the_excerpt ', array(&$this, 'tokens_the_excerpt'), 0);
		add_filter('widget_text', array(&$this, 'tokens'), 0);
		
		//innerindex
		add_filter('content_save_pre', array(&$this, 'innerindex_nametags'));
		add_filter('save_post', array(&$this, 'innerindex_delete_cache'));
		add_filter('publish_post', array(&$this, 'innerindex_delete_cache'));
		add_filter('publish_page', array(&$this, 'innerindex_delete_cache'));
		$this->kses_allowedposttags(); // allow IDs on H1-H6 tags

		// bsuggestive related posts
		add_filter('save_post', array(&$this, 'bsuggestive_delete_cache'));
		add_filter('publish_post', array(&$this, 'bsuggestive_delete_cache'));
		add_filter('publish_page', array(&$this, 'bsuggestive_delete_cache'));
		if( get_option( 'bsuite_insert_related' ))
			add_filter('the_content', array(&$this, 'bsuggestive_the_content'), 5);

		// sharelinks
		if( get_option( 'bsuite_insert_sharelinks' ))
			add_filter('the_content', array(&$this, 'sharelinks_the_content'), 6);

		// searchsmart
		if( get_option( 'bsuite_searchsmart' )){
			add_filter('posts_request', array(&$this, 'searchsmart_posts_request'), 10);
			add_filter('content_save_pre', array(&$this, 'searchsmart_edit'));
		}
		add_filter('template_redirect', array(&$this, 'searchsmart_direct'), 8);

		// default CSS
		if( get_option( 'bsuite_insert_css' ))
			add_action('wp_head', array(&$this, 'css_default' ));

		// bstat
		add_action('get_footer', array(&$this, 'bstat_js'));

		// cron
		add_filter('cron_schedules', array(&$this, 'cron_reccurences'));
		if( $this->loadavg < get_option( 'bsuite_load_max' )){ // only do cron if load is low-ish
			add_filter('bsuite_interval', array(&$this, 'bstat_migrator'));
			if( get_option( 'bsuite_searchsmart' ))
				add_filter('bsuite_interval', array(&$this, 'searchsmart_upindex_passive'));
		}

		// machine tags
		add_action('save_post', array(&$this, 'machtag_save_post'), 2, 2);		

		// cms goodies
		add_action('dbx_page_advanced', array(&$this, 'edit_insert_excerpt_form'));
		add_action('dbx_page_sidebar', array(&$this, 'edit_insert_category_form'));
		add_action('edit_form_advanced', array(&$this, 'edit_post_form'));
		add_action('edit_page_form', array(&$this, 'edit_page_form'));

		add_action('widgets_init', array(&$this, 'widgets_register'));

		add_filter( 'whitelist_options', array(&$this, 'mu_options' ));


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



	//
	// shortcode functions
	//
	function shortcode_pagemenu( $arg ){
		// [pagemenu ]
		global $id;

		$arg = shortcode_atts( array(
			'title' => 'Contents',
			'div_class' => 'contents pagemenu',
			'ul_class' => 'contents pagemenu',
			'ol_class' => FALSE,
			'echo' => 0,
			'child_of' => $id,
			'depth' => 1,
			'sort_column' => 'menu_order, post_title',
			'title_li' => '',
			'show_date'   => '',
			'date_format' => get_option('date_format'),
			'exclude'     => '',
			'authors'     => '',
		), $arg );

		$prefix = $suffix = '';
		if( $arg['div_class'] ){
			$prefix .= '<div class="'. $arg['div_class'] .'">';
			$suffix .= '</div>';
			if( $arg['title'] )
				$prefix .= '<h3>'. $arg['title'] .'</h3>';
			if( $arg['ul_class'] ){
				$prefix .= '<ul>';
				$suffix = '</ul>'. $suffix;
			}else if( $arg['ol_class'] ){
				$prefix .= '<ol>';
				$suffix = '</ol>'. $suffix;
			}
		}else{
			if( $arg['title'] )
				$prefix .= '<h3 class="'. $arg['ul_class'] . $arg['ol_class'] .'">'. $arg['title'] .'</h3>';
			if( $arg['ul_class'] ){
				$prefix .= '<ul class="'. $arg['ul_class'] .'">';
				$suffix = '</ul>'. $suffix;
			}else if( $arg['ol_class'] ){
				$prefix .= '<ol class="'. $arg['ol_class'] .'">';
				$suffix = '</ol>'. $suffix;
			}
		}

		return( $prefix . wp_list_pages( $arg ) . $suffix );
	}
	
	function shortcode_innerindex( $arg ){
		// [innerindex ]
		global $id;

		$arg = shortcode_atts( array(
			'title' => 'Contents',
			'div_class' => 'contents innerindex',
		), $arg );
		
		$prefix = $suffix = '';
		if( $arg['div_class'] ){
			$prefix .= '<div class="'. $arg['div_class'] .'">';
			$suffix .= '</div>';
			if( $arg['title'] )
				$prefix .= '<h3>'. $arg['title'] .'</h3>';
		}else{
			if( $arg['title'] )
				$prefix .= '<h3>'. $arg['title'] .'</h3>';
		}

		if ( !$menu = wp_cache_get( $id, 'bsuite_innerindex' )) {
			$menu = $this->innerindex_build( get_post_field( 'post_content', $id ));
			wp_cache_add( $id, $menu, 'bsuite_innerindex', 864000 );
		}

		return( $prefix . str_replace( '%%the_permalink%%', get_permalink( $id ), $menu ) . $suffix );
	}
	
	function shortcode_redirect($stuff){
		// [[redirect|$url]]
		if(!headers_sent())
			header("Location: $stuff");
		return('redirect: <a href="'. $stuff .'">'. $stuff .'</a>');
	}

	function shortcode_feed( $arg ){
		// [feed ]

		$arg = shortcode_atts( array(
			'title' => FALSE,
			'div_class' => FALSE,
			'ul_class' => 'feed',
			'ol_class' => FALSE,
			'feed_url' => FALSE,
			'count' => 5,
			'template' => '<li><h4><a href="%%link%%">%%title%%</a></h4><p>%%content%%</p></li>',
		), $arg );

		if( ! $arg[ 'feed_url' ] )
			return( FALSE );

		$prefix = $suffix = '';
		if( $arg['div_class'] ){
			$prefix .= '<div class="'. $arg['div_class'] .'">';
			$suffix .= '</div>';
			if( $arg['title'] )
				$prefix .= '<h3>'. $arg['title'] .'</h3>';
			if( $arg['ul_class'] ){
				$prefix .= '<ul>';
				$suffix = '</ul>'. $suffix;
			}else if( $arg['ol_class'] ){
				$prefix .= '<ol>';
				$suffix = '</ol>'. $suffix;
			}
		}else{
			if( $arg['title'] )
				$prefix .= '<h3 class="'. $arg['ul_class'] . $arg['ol_class'] .'">'. $arg['title'] .'</h3>';
			if( $arg['ul_class'] ){
				$prefix .= '<ul class="'. $arg['ul_class'] .'">';
				$suffix = '</ul>'. $suffix;
			}else if( $arg['ol_class'] ){
				$prefix .= '<ol class="'. $arg['ol_class'] .'">';
				$suffix = '</ol>'. $suffix;
			}
		}

		return( $prefix . $this->get_feed( $arg['feed_url'], $arg['count'], $arg['template'], TRUE) . $suffix );
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

	public function tokens_the_content($content) {
		$this->is_content = TRUE;
		$content = $this->tokens($content);
		$this->is_content = FALSE;
		return($content);
	}

	public function tokens_the_content_rss($content) {
		$this->is_content = TRUE;
		$this->is_rss = TRUE;
		$content = $this->tokens($content);
		$this->is_content = FALSE;
		$this->is_rss = FALSE;
		return($content);
	}

	public function tokens_the_excerpt($content) {
		$this->is_excerpt = TRUE;
		$content = $this->tokens($content);
		$this->is_excerpt = FALSE;
		return($content);
	}

	public function tokens_the_excerpt_rss($content) {
		$this->is_excerpt = TRUE;
		$this->is_rss = TRUE;
		$content = $this->tokens($content);
		$this->is_excerpt = FALSE;
		$this->is_rss = FALSE;
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

		if ( !$menu = wp_cache_get( $id, 'bsuite_innerindex' )) {
			$menu = $this->innerindex_build( get_post_field( 'post_content', $id ));
			wp_cache_add( $id, $menu, 'bsuite_innerindex', 864000 );
		}

		if($this->is_excerpt){
			return( str_replace( '%%the_permalink%%', get_permalink( $id ), $menu ));
		}else{
			return( '<div class="innerindex"><h3>'. $title .'</h3>'. str_replace( '%%the_permalink%%', get_permalink( $id ), $menu ) .'</div>' );
		}
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
		
				$menu .= '<li><a href="%%the_permalink%%#'. $anchor[1] .'">'. strip_tags($thing) .'</a>';
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



	//
	// sharelinks
	//
	function sharelinks(){
		global $wp_query;
	
		// exit if 404
		if($wp_query->is_404)
			return(FALSE);
	
		// identify the based post ID, if any, and establish some basics
		$post_id = FALSE;
		if(!empty($wp_query->is_singular) && !empty($wp_query->query_vars['p']))
			$post_id = $wp_query->query_vars['p'];
		else if(!empty($wp_query->is_singular) && !empty($wp_query->queried_object_id))
			$post_id = $wp_query->queried_object_id;
		else if( !empty( $this->bsuggestive_to ))
			$post_id = $this->bsuggestive_to;
	
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
		$content = '<ul class="bsuite_sharelinks">';
	
		// the embed links 
		if( $post_id && ( $embed = $this->link2me( $post_id ))){
			$content .= '<li class="bsuite_share_embed"><h3 id="bsuite_share_embed">Link or embed this</h3>' . $embed .'</li>';
		}
	
		// the bookmark links 
		$content .= '<li class="bsuite_share_bookmark"><h3 id="bsuite_share_bookmark">Bookmark this at</h3><ul>';
		global $services_bookmark;
		foreach ($services_bookmark as $key => $data) {
			$content .= '<li><img src="' . get_settings('siteurl') .'/'. PLUGINDIR .'/'. plugin_basename(dirname(__FILE__))  . '/img/'. $key .'.gif" width="16" height="16" alt="'. attribute_escape($data['name']) .' sharing icon">&nbsp;<a href="'. str_replace(array('{title}', '{url}'), array($the_title, $the_permalink), $data['url']) .'">'. $data['name'] .'</a></li>';
		}
		$content .= '</ul></li>';
	
		// the email links
		$content .= '<li class="bsuite_share_email"><h3 id="bsuite_share_email">Email this page</h3><ul><li><a href="mailto:?MIME-Version=1.0&Content-Type=text/html;&subject='. attribute_escape(urldecode($the_title)) .'&body=%0D%0AI found this at '.  attribute_escape(get_bloginfo('name')) .'%0D%0A'. attribute_escape(urldecode($the_permalink)) .'%0D%0A">Send this page using your computer&#039;s emailer</a></li></ul></li>';
	
		// the feed links
		$content .= '<li class="bsuite_share_feed"><h3 id="bsuite_share_feed">Stay up to date</h3><ul>';
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
		$content .= '</ul></li>';
	
		// the translation links
		$content .= '<li class="bsuite_share_translate"><h3 id="bsuite_share_translate">Automatically translate this to</h3><ul>';
		global $services_translate;
		foreach ($services_translate as $key => $data) {
			$content .= '<li><a href="'. str_replace('{url}', $the_permalink, $data['url']) .'">'. $data['name'] .'</a></li>';
		}
		$content .= '</ul></li>';

		$content .= '</ul>';
	
		// powered by
		$content .= '<p class="bsuite_share_bsuitetag">Powered by <a href="http://maisonbisson.com/blog/bsuite">bSuite</a>.</p>';
	
		return( $content );
		//return(array('the_id' => $post_id, 'the_title' => urldecode($the_title), 'the_permalink' => urldecode($the_permalink), 'the_content' => $content, ));
	}

	function sharelinks_the_content( $content ) {
		if( is_single() && $sharelinks = $this->sharelinks() )
			return( $content . $sharelinks);
		return( $content );
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
	// Stats Related
	//
	function bstat_js() {
		if( !$this->didstats ){
?>
<script type="text/javascript">
bsuite.api_location='<?php echo substr( get_settings( 'siteurl' ), strpos( get_settings( 'siteurl' ), ':' ) + 3 ) . $this->path_web . '/worker.php' ?>';
bsuite.log();
</script>
<noscript><img src="<?php echo substr( get_settings( 'siteurl' ), strpos( get_settings( 'siteurl' ), ':' ) + 3 ) . $this->path_web . '/worker.php' ?>" width="1" height="1" alt="stat counter" /></noscript>
<?php
		}
	}
	
	function bstat_get_term( $id ) {
		global $wpdb;

		if ( !$name = wp_cache_get( $id, 'bstat_terms' )) {
			$name = $wpdb->get_var("SELECT name FROM $this->hits_terms WHERE ". $wpdb->prepare( "term_id = %s", (int) $id ));
			wp_cache_add( $id, $name, 'bstat_terms', 0 );
		}
		return( $name );
	}
	
	function bstat_is_term( $term ) {
		global $wpdb;

		$cache_key = md5( substr( $term, 0, 255 ) );	
		if ( !$term_id = wp_cache_get( $cache_key, 'bstat_termids' )) {
			$term_id = (int) $wpdb->get_var("SELECT term_id FROM $this->hits_terms WHERE ". $wpdb->prepare( "name = %s", substr( $term, 0, 255 )));
			wp_cache_add( $cache_key, $term_id, 'bstat_termids', 0 );
		}
		return( $term_id );
	}
	
	function bstat_insert_term( $term ) {
		global $wpdb;
	
		if ( !$term_id = $this->bstat_is_term( $term )) {
			if ( false === $wpdb->insert( $this->hits_terms, array( 'name' => $term ))){
				new WP_Error('db_insert_error', __('Could not insert term into the database'), $wpdb->last_error);
				return( 1 );
			}
			$term_id = (int) $wpdb->insert_id;
		}
		return( $term_id );
	}

	function bstat_is_session( $session_cookie ) {
		global $wpdb;

		if ( !$sess_id = wp_cache_get( $session_cookie, 'bstat_sessioncookies' )) {
			$sess_id = (int) $wpdb->get_var("SELECT sess_id FROM $this->hits_sessions WHERE ". $wpdb->prepare( "sess_cookie = %s", $session_cookie ));
			wp_cache_add( $session_cookie, $sess_id, 'bstat_sessioncookies', 10800 );
		}
		return($sess_id);
	}
	
	function bstat_insert_session( $session ) {
		global $wpdb;

		$s = array();
		if ( !$session_id = $this->bstat_is_session( $session->in_session )) {
			$this->session_new = TRUE;
		
			$s['sess_cookie'] = $session->in_session;
			$s['sess_date'] = $session->in_time;

			$se = unserialize( $session->in_extra );
			$s['sess_ip'] = $se['ip'];
			$s['sess_br'] = $se['br'];
			$s['sess_bb'] = $se['bb'];
			$s['sess_bl'] = $se['bl'];
			$s['sess_ba'] = urldecode( $se['ba'] );
// could use INET_ATON and INET_NTOA to reduce storage requirements for the IP address,
// but it's not human readable when browsing the table

			if ( false === $wpdb->insert( $this->hits_sessions, $s )){
				new WP_Error('db_insert_error', __('Could not insert session into the database'), $wpdb->last_error);
				return( FALSE );
			}
			$session_id = (int) $wpdb->insert_id;
			
			wp_cache_add($session->in_session, $session_id, 'bstat_sessioncookies', 10800 );
		}
		return( $session_id );
	}

	function bstat_migrator(){
		global $wpdb;

		// use a named mysql lock to prevent simultaneous execution
		// locks automatically drop when the connection is dropped
		// http://dev.mysql.com/doc/refman/5.0/en/miscellaneous-functions.html#function_get-lock
		if( 0 == $wpdb->get_var( 'SELECT GET_LOCK("'. $this->lock_migrator .'", 2)' ))
			return( TRUE );

		// also use the options table
		if ( get_option( 'bsuite_doing_migration') > time() )
			return( TRUE );

		update_option( 'bsuite_doing_migration', time() + 250 );
		$status = get_option ( 'bsuite_doing_migration_status' );

		$getcount = get_option( 'bsuite_migration_count' );
		$since = date('Y-m-d H:i:s', strtotime('-1 minutes'));
		
		$res = $targets = $searchwords = $shistory = array();
		$res = $wpdb->get_results( "SELECT * 
			FROM $this->hits_incoming
			WHERE in_time < '$since'
			ORDER BY in_time ASC
			LIMIT $getcount" );
		
		$status['count_incoming'] = count( $res );
		update_option( 'bsuite_doing_migration_status', $status );

		foreach( $res as $hit ){
			$object_id = $object_type = $session_id = 0;

			if( !strlen( $hit->in_to ))
				$hit->in_to = get_option( 'siteurl' ) .'/';

			if( $hit->in_session )			
				$session_id = $this->bstat_insert_session( $hit );

			$object_id = url_to_postid( $hit->in_to );

			// determine the target
			if( ( 1 > $object_id ) || (('posts' <> get_option( 'show_on_front' )) && $object_id == get_option( 'page_on_front' )) ){
				$object_id = $this->bstat_insert_term( $hit->in_to );
				$object_type = 1;
			}
			$targets[] = "($object_id, $object_type, 1, '$hit->in_time')";
		
			// look for search words
			if( ( $referers = implode( $this->get_search_terms( $hit->in_from ), ' ') ) && ( 0 < strlen( $referers ))) {
				$term_id = $this->bstat_insert_term( $referers );
				$searchwords[] = "($object_id, $object_type, $term_id, 1)";
			}
			
			if( $session_id ){
				if( $referers )
					$shistory[] = "($session_id, $term_id, 2)";

				if( $this->session_new ){
					$in_from = $this->bstat_insert_term( $hit->in_from );
					if( $referers )
						$shistory[] = "($session_id, $in_from, 3)";
				}

				$shistory[] = "($session_id, $object_id, $object_type)";
			}
		}

		$status['count_targets'] = count( $targets );
		$status['count_searchwords'] = count( $searchwords );
		$status['count_shistory'] = count( $shistory );
		update_option( 'bsuite_doing_migration_status', $status );

		if( count( $targets ) && !$status['did_targets'] ){
			if ( false === $wpdb->query( "INSERT INTO $this->hits_targets (object_id, object_type, hit_count, hit_date) VALUES ". implode( $targets, ',' ) ." ON DUPLICATE KEY UPDATE hit_count = hit_count + 1;" ))
				return new WP_Error('db_insert_error', __('Could not insert bsuite_hits_target into the database'), $wpdb->last_error);

			$status['did_targets'] = 1 ;
			update_option( 'bsuite_doing_migration_status', $status );
		}
		
		if( count( $searchwords ) && !$status['did_searchwords'] ){
			if ( false === $wpdb->query( "INSERT INTO $this->hits_searchphrases (object_id, object_type, term_id, hit_count) VALUES ". implode( $searchwords, ',' ) ." ON DUPLICATE KEY UPDATE hit_count = hit_count + 1;" ))
				return new WP_Error('db_insert_error', __('Could not insert bsuite_hits_searchword into the database'), $wpdb->last_error);

			$status['did_searchwords'] = 1;
			update_option( 'bsuite_doing_migration_status', $status );
		}

		if( count( $shistory ) && !$status['did_shistory'] ){
			if ( false === $wpdb->query( "INSERT INTO $this->hits_shistory (sess_id, object_id, object_type) VALUES ". implode( $shistory, ',' ) .';' ))
				return new WP_Error('db_insert_error', __('Could not insert bsuite_hits_session_history into the database'), $wpdb->last_error);

			$status['did_shistory'] = count( $shistory );
			update_option( 'bsuite_doing_migration_status', $status );
		}

		if( count( $res )){
			if ( false === $wpdb->query( "DELETE FROM $this->hits_incoming WHERE in_time < '$since' ORDER BY in_time ASC LIMIT ". count( $res ) .';'))
				return new WP_Error('db_insert_error', __('Could not clean up the incoming stats table'), $wpdb->last_error);
			if( $getcount > count( $res ))
				$wpdb->query( "OPTIMIZE TABLE $this->hits_incoming;");
		}

		if ( get_option( 'bsuite_doing_migration_popr') < time() ){
			if ( get_option( 'bsuite_doing_migration_popd') < time() ){
				$wpdb->query( "TRUNCATE $this->hits_pop" );
				$wpdb->query( "INSERT INTO $this->hits_pop (post_id, date_start, hits_total)
					SELECT object_id AS post_id, MIN(hit_date) AS date_start, SUM(hit_count) AS hits_total
					FROM $this->hits_targets
					WHERE object_type = 0
					AND hit_date >= DATE_SUB( NOW(), INTERVAL 45 DAY )
					GROUP BY object_id" );
				update_option( 'bsuite_doing_migration_popd', time() + 64800 );
			}
			$wpdb->query( "UPDATE $this->hits_pop p
				LEFT JOIN (
					SELECT object_id, COUNT(*) AS hit_count
					FROM (
						SELECT sess_id, sess_date
						FROM (
							SELECT sess_id, sess_date
							FROM $this->hits_sessions
							ORDER BY sess_id DESC
							LIMIT 12500
						) a
						WHERE sess_date >= DATE_SUB( NOW(), INTERVAL 1 DAY )
					) s
					LEFT JOIN $this->hits_shistory h ON h.sess_id = s.sess_id
					WHERE h.object_type = 0
					GROUP BY object_id
				) h ON h.object_id = p.post_id
				SET hits_recent = h.hit_count" );
			update_option( 'bsuite_doing_migration_popr', time() + 1500 );
		}

/*		
		$posts = $wpdb->get_results("SELECT object_id, AVG(hit_count) AS hit_avg
				FROM $this->hits_targets
				WHERE hit_date >= DATE_SUB(CURDATE(),INTERVAL 30 DAY)
				AND object_type = 0
				GROUP BY object_id
				ORDER BY object_id ASC", ARRAY_A);
		$avg = array();
		foreach($posts as $post)
			$avg[$post['object_id']] = $post['hit_avg'];
		
		$posts = $wpdb->get_results("SELECT object_id, hit_count * (86400/TIME_TO_SEC(TIME(NOW()))) AS hit_now
				FROM $this->hits_targets
				WHERE hit_date = CURDATE()
				AND object_type = 0
				ORDER BY object_id ASC", ARRAY_A);
		$now = array();
		foreach($posts as $post)
			$now[$post['object_id']] = $post['hit_now'];
		
		$diff = array();
		foreach($posts as $post)
			$diff[$post['object_id']] = intval(($now[$post['object_id']] - $avg[$post['object_id']]) * 1000 );
		
		$win = count(array_filter($diff, create_function('$a', 'if($a > 0) return(TRUE);')));
		$lose = count($diff) - $win;
		
		$sort = array_flip($diff);
		ksort($sort);
		
		if(!empty($sort)){
			foreach(array_slice(array_reverse($sort), 0, $detail_lines) as $object_id){
				echo '<li><a href="'. get_permalink($object_id) .'">'. get_the_title($object_id) .'</a><br><small>Up: '. number_format($diff[$object_id] / 1000, 0) .' Avg: '. number_format($avg[$object_id], 0) .' Today: '. number_format($now[$object_id], 0) ."</small></li>\n";
			}
		}
*/
		
//print_r($wpdb->queries);

		update_option( 'bsuite_doing_migration', 0 );
		update_option( 'bsuite_doing_migration_status', array() );
		return(TRUE);
	}

	function get_search_engine( $ref ) {
		// a lot of inspiration and code for this function was taken from
		// Search Hilite by Ryan Boren and Matt Mullenweg
		global $wp_query;
		if( empty( $ref ))
			return false;

		$referer = urldecode( $ref );
		if (preg_match('|^http://(www)?\.?google.*|i', $referer))
			return('google');
	
		if (preg_match('|^http://search\.yahoo.*|i', $referer))
			return('yahoo');

		if (preg_match('|^http://search\.live.*|i', $referer))
			return('windowslive');

		if (preg_match('|^http://search\.msn.*|i', $referer))
			return('msn');

		if (preg_match('|^http://search\.lycos.*|i', $referer))
			return('lycos');

		$home = parse_url( get_settings( 'siteurl' ));
		$ref = parse_url( $referer );	
		if ( strpos( ' '. $ref['host'] , $home['host'] ))
			return('internal');
	
		return(FALSE);
	}

	function get_search_terms( $ref ) {
		// a lot of inspiration and code for this function was taken from
		// Search Hilite by Ryan Boren and Matt Mullenweg
//		if( !$engine = $this->get_search_engine( $ref ))
//			return(FALSE);

$engine = $this->get_search_engine( $ref );

		$referer = parse_url( $ref );
		parse_str( $referer['query'], $query_vars );

		$query_array = array();
		switch ($engine) {
		case 'google':
			if( $query_vars['q'] )
				$query_array = explode(' ', urldecode( $query_vars['q'] ));
			break;
	
		case 'yahoo':
			if( $query_vars['p'] )
				$query_array = explode(' ', urldecode( $query_vars['p'] ));
			break;
			
		case 'windowslive':
			if( $query_vars['q'] )
				$query_array = explode(' ', urldecode( $query_vars['q'] ));
			break;
	
		case 'msn':
			if( $query_vars['q'] )
				$query_array = explode(' ', urldecode( $query_vars['q'] ));
			break;
	
		case 'lycos':
			if( $query_vars['query'] )
				$query_array = explode(' ', urldecode( $query_vars['query'] ));
			break;
	
		case 'internal':
			if( $query_vars['s'] )
				$query_array = explode(' ', urldecode( $query_vars['s'] ));

			// also need to handle the case where a search matches the /search/ pattern
			break;
		}

		$query_array = array_filter( array_map( array(&$this, 'trimquotes') , $query_array ));

		return $query_array;
	}

	function post_hits( $args = '' ) {
		global $wpdb;

		$defaults = array(
			'return' => 'formatted',
			'days' => 0,
			'template' => '<li><a href="%%link%%">%%title%%</a>&nbsp;(%%hits%%)</li>'
		);
		$args = wp_parse_args( $args, $defaults );

		$post_id = (int) $args['post_id'] > 1 ? 'AND object_id = '. (int) $args['post_id'] : '';
	
		$date = '';
		if($args['days'] > 1)
			$date  = "AND hit_date > '". date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d") - $args['days'], date("Y"))) ."'";
	
		// here's the query, but let's try to get the data from cache first
		$request = "SELECT
			FORMAT(SUM(hit_count), 0) AS hits, 
			FORMAT(AVG(hit_count), 0) AS average
			FROM $this->hits_targets
			WHERE 1=1
			$post_id
			AND object_type = 0
			$date
			";

		if ( !$result = wp_cache_get( (int) $args['post_id'] .'_'. (int) $args['days'], 'bstat_post_hits' ) ) {
			$result = $wpdb->get_results($request, ARRAY_A);
			wp_cache_add( (int) $args['post_id'] .'_'. (int) $args['days'], $result, 'bstat_post_hits', 1800 );
		}

		if(empty($result))
			return(NULL);

		if($args['return'] == 'array')
			return($result);

		if($args['return'] == 'formatted'){
			$list = str_replace(array('%%avg%%','%%hits%%'), array($result[0]['average'], $result[0]['hits']), $args['template']);
			return($list);
		}
	}

	function pop_posts( $args = '' ) {
		global $wpdb, $bsuite;

		$defaults = array(
			'count' => 15,
			'return' => 'formatted',
			'template' => '<li><a href="%%link%%">%%title%%</a>&nbsp;(%%hits%%)</li>'
		);
		$args = wp_parse_args( $args, $defaults );
	
		$date = 'AND hit_date = DATE(NOW())';
		if($args['days'] > 1)
			$date  = "AND hit_date > '". date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d") - $args['days'], date("Y"))) ."'";
	
		$limit = 'LIMIT '. (0 + $args['count']);
	
	
		$request = "SELECT object_id, SUM(hit_count) AS hit_count
			FROM $this->hits_targets
			WHERE 1=1
			AND object_type = 0
			$date
			GROUP BY object_id
			ORDER BY hit_count DESC
			$limit";
		$result = $wpdb->get_results($request, ARRAY_A);

		if(empty($result))
			return(NULL);

		if($args['return'] == 'array')
			return($result);

		if($args['return'] == 'formatted'){
			$list = '';
			foreach($result as $post){
				$list .= str_replace(array('%%title%%','%%hits%%','%%link%%'), array(get_the_title($post['object_id']), $post['hit_count'], get_permalink($post['object_id'])), $args['template']);
			}
			return($list);
		}
	}

	function pop_refs( $args = '' ) {
		global $wpdb, $bsuite;

		$defaults = array(
			'count' => 15,
			'return' => 'formatted',
			'template' => '<li>%%title%%&nbsp;(%%hits%%)</li>'
		);
		$args = wp_parse_args( $args, $defaults );
	
		$limit = 'LIMIT '. (int) $args['count'];
	
		$request = "SELECT COUNT(*) AS hit_count, name
			FROM (
				SELECT object_id
				FROM $this->hits_shistory
				WHERE object_type = 2
				ORDER BY sess_id DESC
				LIMIT 1000
			) a
			LEFT JOIN $this->hits_terms t ON a.object_id = t.term_id
			GROUP BY object_id
			ORDER BY hit_count DESC
			$limit";

		$result = $wpdb->get_results($request, ARRAY_A);
		
		if(empty($result))
			return(NULL);

		if($args['return'] == 'array')
			return($result);

		if($args['return'] == 'formatted'){
			$list = '';
			foreach($result as $row){
				$list .= str_replace(array('%%title%%','%%hits%%'), array($row['name'], $row['hit_count']), $args['template']);
			}		
			return($list);
		}
	}
	// end stats functions



	//
	// Searchsmart
	//
	function searchsmart_posts_request( $query ){
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

//print_r($wp_query);			
//echo '<h2>'. $this->searchsmart_query( $wp_query->query_vars['s'], 'LIMIT '. $limit[1] ) .'</h2>';
			return( $this->searchsmart_query( $wp_query->query_vars['s'], 'LIMIT '. $limit[1] ));
		}
		return( $query );
	}

	function searchsmart_query( $searchphrase, $limit = 'LIMIT 0,5' ){
		global $wpdb;

		if( 3 < strlen( trim( $searchphrase ))){
			return("SELECT SQL_CALC_FOUND_ROWS $wpdb->posts.* 
				FROM (
					SELECT post_id, MATCH (content, title) AGAINST (". $wpdb->prepare( '%s', $searchphrase ) .") AS score 
					FROM $this->search_table
					WHERE MATCH (content, title) AGAINST (". $wpdb->prepare( '%s', $searchphrase ) .")
					ORDER BY score DESC
					LIMIT 1000
				) s
				LEFT JOIN $wpdb->posts ON ( s.post_id = $wpdb->posts.ID ) 
				WHERE 1=1 
				AND post_status IN ('publish', 'private')
				ORDER BY score DESC 
				$limit");
		}else{
			return("SELECT SQL_CALC_FOUND_ROWS $wpdb->posts.*
				FROM $wpdb->posts 
				WHERE 1=1 
				AND post_content LIKE ". $wpdb->prepare( '%s', '%'. $searchphrase .'%' ) ."
				AND post_status IN ('publish', 'private')
				ORDER BY post_date_gmt DESC $limit");
		}
	}

	function searchsmart_direct(){
		global $wp_query, $wp_rewrite;

		// redirect when there's a redirection order for the post
		if( $wp_query->is_singular && get_post_meta( $wp_query->post->ID, 'redirect', TRUE ))
			wp_redirect( get_post_meta( $wp_query->post->ID, 'redirect', TRUE ), '301');

		// redirects ?s={search_term} to /search/{search_term} if permalinks are working
		if( isset( $_GET['s'] ) && !empty( $wp_rewrite->permalink_structure ) )
			wp_redirect(get_option('siteurl') .'/'. $wp_rewrite->search_base .'/'. urlencode( $_GET['s'] ), '301');

		// redirects the search to the single page if the search returns only one item
		if( !$wp_query->is_singular && 1 === $wp_query->post_count )
			wp_redirect( get_permalink( $wp_query->post->ID ) , '302');

		return(TRUE);
	}

	function searchsmart_edit( $content ){
		// called when posts are edited or saved
		if( (int) $_POST['post_ID'] )
			$this->searchsmart_delpost( (int) $_POST['post_ID'] );
		return($content);
	}

	function searchsmart_delpost( $post_id ){		
		global $wpdb;
		$wpdb->get_results( "DELETE FROM $this->search_table WHERE post_id = $post_id" );
	}

	function searchsmart_content( $content ){

		// remove bsuite tokens and html formatting
		$content = preg_replace(
			'/\[\[([^\]])*\]\]/',
			'',
			strip_tags(
				str_ireplace(array('<br />', '<br/>', '<br>', '</p>', '</li>', '</h1>', '</h2>', '</h3>', '</h4>'), "\n", 
					stripslashes(
						html_entity_decode( $content )
					)
				)
			)
		);

		// shortcodes
		$content = preg_replace( '/\[(.*?)\]/', '', $content );

		// find words with accented characters, create transliterated versions of them
		$unaccented = array_diff( str_word_count( $content, 1 ), str_word_count( remove_accents( $content ), 1 ));

//		// remove punctuation
//		$content = trim(preg_replace(
//			'/([[:punct:]])*/',
//			'',
//			$content));

		// apply filters
		return( apply_filters('bsuite_searchsmart_content', $content .' '. implode( ' ', $unaccented )));

	}

	function searchsmart_upindex(){
		// put content in the keyword search index
		global $wpdb;

		update_option('bsuite_doing_ftindex', time() + 300 );

		$posts = $wpdb->get_results("SELECT a.ID, a.post_content, a.post_title
			FROM $wpdb->posts a
			LEFT JOIN $this->search_table b ON a.ID = b.post_id
			WHERE a.post_status = 'publish'
			AND b.post_id IS NULL
			LIMIT 25
			");

		if( count( $posts )) {
			$insert = array();
			foreach( $posts as $post ) {
				$insert[] = '('. (int) $post->ID .', "'. $wpdb->escape( $this->searchsmart_content( $post->post_title ."\n\n". $post->post_content )) .'", "'. $wpdb->escape( $post->post_title ) .'")';
			}
		}else{
			return( FALSE );
		}

		if( count( $insert )) {
			$wpdb->get_results( 'REPLACE INTO '. $this->search_table .'
						(post_id, content, title) 
						VALUES '. implode( ',', $insert ));
		}

		// diabled so that the update runs less often.
		//update_option('bsuite_doing_ftindex', 0 );

		return( count( $posts ));
	}

	function searchsmart_upindex_passive(){
		// finds unindexed posts and adds them to the fulltext index in groups of 10, runs via cron
		global $wpdb;

		// use a named mysql lock to prevent simultaneous execution
		// locks automatically drop when the connection is dropped
		// http://dev.mysql.com/doc/refman/5.0/en/miscellaneous-functions.html#function_get-lock
		if( 0 == $wpdb->get_var( 'SELECT GET_LOCK("'. $this->lock_ftindexer .'", 2)' ))
			return( TRUE );

		// also use the options table
		if ( get_option('bsuite_doing_ftindex') > time() )
			return( TRUE );

		$this->searchsmart_upindex();

		return(TRUE);
	}
	// end Searchsmart


	// bSuggestive related functions
	function bsuggestive_query( $id ) {
		global $wpdb;

		$id = (int) $id;

		if( $id ){
			$taxonomies = ( array_filter( apply_filters( 'bsuite_suggestive_taxonomies', array( 'post_tag', 'category' ))));
			
			if( is_array( $taxonomies ))
				return( apply_filters('bsuite_suggestive_query',
					"SELECT t_r.object_id AS post_id, COUNT(t_r.object_id) AS hits
					FROM ( SELECT t_ra.term_taxonomy_id
						FROM $wpdb->term_relationships t_ra
						LEFT JOIN $wpdb->term_taxonomy t_ta ON t_ta.term_taxonomy_id = t_ra.term_taxonomy_id
						WHERE t_ra.object_id  = $id
						AND t_ta.taxonomy IN ('". implode( $taxonomies, "','") ."')
					) ttid
					LEFT JOIN $wpdb->term_relationships t_r ON t_r.term_taxonomy_id = ttid.term_taxonomy_id
					LEFT JOIN $wpdb->posts p ON t_r.object_id  = p.ID
					WHERE p.ID != $id
					AND p.post_status = 'publish'
					GROUP BY p.ID
					ORDER BY hits DESC, p.post_date_gmt DESC
					LIMIT 150", $id)
				);
		}
		return FALSE;
	}
	
	function bsuggestive_getposts( $id ) {
		global $wpdb;

		if ( !$related_posts = wp_cache_get( $id, 'bsuite_related_posts' ) ) {
			if( $the_query = $this->bsuggestive_query( $id ) ){
				$related_posts = $wpdb->get_col($the_query);
				wp_cache_add( $id, $related_posts, 'bsuite_related_posts', 864000 );
				return($related_posts); // if we have to go to the DB to get the posts, then this will get returned
			}
			return( FALSE ); // if there's nothing in the cache and we've got no query
		}
		return($related_posts); // if the cache is still warm, then we return this
	}

	function bsuggestive_delete_cache( $id ) {
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
			return( FALSE ); // no ID, no service

		$posts = array_slice($this->bsuggestive_getposts( $id ), 0, 5);
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

	function bsuggestive_the_content( $content ) {
		if( $related = $this->bsuggestive_the_related() )
			return( $content . '<h3 class="bsuite_related">Related items</h3><ul class="bsuite_related">'. $related .'</ul>' );
		return( $content );
	}
	// end bSuggestive



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
	// cron utility functions
	//
	function cron_reccurences( $schedules ) {
		$schedules['bsuite_interval'] = array('interval' => get_option( 'bsuite_migration_interval' ), 'display' => __( 'bSuite interval. Set in bSuite options page.' ));
		return( $schedules );
	}

	function cron_register() {
		// take a look at Glenn Slaven's tutorial on WP's psudo-cron:
		// http://blog.slaven.net.au/archives/2007/02/01/timing-is-everything
		wp_clear_scheduled_hook('bsuite_interval');
		wp_schedule_event( time() + 120, 'bsuite_interval', 'bsuite_interval' );
	}
	// end cron functions



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
		return($load_avg[0]);
	}

	function sys_getloadavg(){
		// the following code taken from tom pittlik's comment at
		// http://php.net/manual/en/function.sys-getloadavg.php
		$str = substr(strrchr(shell_exec('uptime'),':'),1);
		$avs = array_map('trim',explode(',',$str));
		return( $avs );
	}
	// end load average related functions



	function trimquotes( $in ) {
		return( trim( trim( $in ), "'\"" ));
	}
	
	function feedlink(){
		return(strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'))) . '://' . $_SERVER['HTTP_HOST'] . add_query_arg('feed', 'rss', add_query_arg('bsuite_share')));
	}

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
			$list .= str_replace( array( '%%title%%','%%content%%','%%link%%' ), array( $title, $content, $link ), $template );
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
					wp_set_post_tags($post_id, $tags, TRUE);
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
				$namespace = trim( $temp_a[0] );
				$taxonomy = trim( $temp_b[0] );
				$term = trim( $temp_b[1] );
			}else{
				// has just fieldname & value
				$taxonomy = trim( $temp_a[0] );
				$term = trim( $temp_b[0] );
			}
		}else{
			$temp_b = explode('=', $temp_a[0], 2);

			if($temp_b[1]){
				// has just fieldname & value
				$taxonomy = trim( $temp_b[0] );
				$term = trim( $temp_b[1] );
			}else{
				// has just value
				$term = trim( $temp_b[0] );
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

	function edit_insert_category_form() {
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



	function autoksum_doapi( $text ){
		// api: http://api.scriblio.net/docs/summarize

		// The POST URL and parameters
		$request = 'http://api.scriblio.net/v01b/summarize/';
		$postargs = array( 
			'text' => strip_tags( 
				str_replace( array( '<','>' ), array( "\n\n<",">\n\n" ), 
					strip_tags( 
						preg_replace( '/\[(.*?)\]/', '', 
							preg_replace( '!(<(?:h[1-6])[^>]*>[^<]*<(?:\/h[1-6])[^>]*>)!', '', 
								$text )), 
						'<p><ul><ol><li><tr><td><table>' 
					)
				)
			), 
			'wordcount' => 35 , 
			'output' => 'php' 
		);
		
		// Get the curl session object
		$session = curl_init($request);
		
		// Set the POST options.
		curl_setopt ($session, CURLOPT_POST, TRUE);
		curl_setopt ($session, CURLOPT_POSTFIELDS, $postargs);
		curl_setopt($session, CURLOPT_HEADER, FALSE);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, TRUE);
		
		// Do the POST and then close the session
		$response = curl_exec($session);
		curl_close($session);

		// return
		if( $response = unserialize( substr( $response, strpos( $response, 'a:' ))))
			return( $response );
		else
			return( FALSE );
	}

//	function autoksum_excerpt_image(){
//		return( apply_filters( 'bsuite_excerpt', $api_result['summary'] ));
//	}

	function autoksum_backfill(){
		global $wpdb;

		$posts = $wpdb->get_results( 'SELECT ID, post_content
			FROM '. $wpdb->posts .'
			WHERE post_status = "publish"
			AND post_excerpt = ""
			LIMIT 5' );

		if( count( $posts )) {
			$insert = array();
			foreach( $posts as $post ) {
				$api_result = $this->autoksum_doapi( $post->post_content );
				if( $api_result['summary'] ){
					$insert[] = '('. (int) $post->ID .', "'. $wpdb->escape( $api_result['summary'] ) .'")';
					$post_tags[ $post->ID ] = array_merge( $api_result['caps'], $api_result['keywords'] );
				}else{
					$insert[] = '('. (int) $post->ID .', "'. $wpdb->escape( wp_trim_excerpt( get_post_field( 'post_content', $post->ID ))) .'")';
				}
			}
		}else{
			return( FALSE );
		}

		if( count( $insert )) {
			$wpdb->get_results( 'INSERT INTO '. $wpdb->posts .'
				(ID, post_excerpt) 
				VALUES '. implode( ',', $insert ) .'
				ON DUPLICATE KEY UPDATE post_excerpt = VALUES( post_excerpt )');

			foreach( $post_tags as $post_id => $tags )
				if( !get_the_terms( $post_id , 'post_tag' ))
					wp_set_post_tags( $post_id, $tags , FALSE);
		}

// need to delete any affected post caches here

		return( count( $posts ));
	}




	function css_default() {
?>
<style type="text/css">
/* 
** bSuite default styles
**
** more information at http://maisonbisson.com/blog/bsuite/
*/

/* the search word highlight */
.highlight { 
	background-color: #FFFF00;
	padding: .2em;
	border-top: 1px solid #FAFAD2;
	border-right: 1px solid #FF8C00;
	border-bottom: 1px solid #FF8C00;
	border-left: 1px solid #FAFAD2;
	-moz-border-radius: 5px;
	-khtml-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
	color: black;
}

/* related posts */
.bsuite_related h3 {
	margin-top: 1em;
	clear: both;
}

/* sharelinks */
.bsuite_sharelinks h3 {
	margin-top: 1em;
	clear: both;
}

.bsuite_sharelinks ul {
	margin:0 0 0 1em;
}

.bsuite_sharelinks ul li {
	float: left;
	list-style-image:none;
	list-style-position:outside;
	list-style-type:none;
	margin:0 1em 0 0;
}

.bsuite_sharelinks ul li:before{
	content: "";
}

.bsuite_sharelinks input {
	width: 10em;
}

.bsuite_sharelinks .bsuite_share_bsuitetag {
	padding: 1em 0 1em 0;
	clear: both;
	text-align: right;
	font-family:"lucida grande", verdana, arial, sans-serif;
	font-size: .6em
}
</style>
<?php
	}



	// widgets
	function widget_related_posts($args) {
		global $post, $wpdb;
		
		if(!is_singular()) // can only run on single pages/posts
			return(NULL);
		
		$id = (int) $post->ID; // needs an ID of that page/post
		if(!$id)
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

		if ( $related_posts = array_slice( $this->bsuggestive_getposts( $id ), 0, $number )) {
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

		if( is_404() ) // no reason to run if it's a 404
			return(FALSE);

		extract($args, EXTR_SKIP);
		$options = get_option('bsuite_sharelinks');
		$title = empty($options['title']) ? __('Bookmark &amp; Feeds', 'bsuite') : $options['title'];

		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<ul id="sharelinks">';
		echo '<li><img src="' . get_settings('siteurl') . $this->path_web .'/img/icon-share-16x16.gif" width="16" height="16" alt="bookmark and share icon" />&nbsp;<a href="#bsuite_share_bookmark" title="bookmark and share links">Bookmark and Share</a></li>';
		echo '<li><img src="' . get_settings('siteurl') . $this->path_web .'/img/icon-feed-16x16.png" width="16" height="16" alt="RSS and feeds icon" />&nbsp;<a href="#bsuite_share_feed" title="RSS and feed links">RSS Feeds</a></li>';
		echo '<li><img src="' . get_settings('siteurl') .'/'. $this->path_web .'/img/icon-translate-16x16.png" width="16" height="16" alt="RSS and feeds icon" />&nbsp;<a href="#bsuite_share_translate" title="RSS and feed links">Translate</a></li>';
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

	function widget_popular_posts($args) {
		global $post, $wpdb;

		extract($args, EXTR_SKIP);
		$options = get_option('bstat_pop_posts');
		$title = empty($options['title']) ? __('Popular Posts') : $options['title'];
		if ( !$number = (int) $options['number'] )
			$number = 5;
		else if ( $number < 1 )
			$number = 1;
		else if ( $number > 15 )
			$number = 15;

		if ( !$days = (int) $options['days'] )
			$days = 7;
		else if ( $days < 1 )
			$days = 1;
		else if ( $days > 30 )
			$days = 30;

		if ( !$pop_posts = wp_cache_get( 'bstat_pop_posts', 'widget' ) ) {
			$pop_posts = $this->pop_posts("limit=$number&days=$days");
			wp_cache_add( 'bstat_pop_posts', $pop_posts, 'widget', 3600 );
		}

		if ( !empty($pop_posts) ) {
?>
			<?php echo $before_widget; ?>
				<?php echo $before_title . $title . $after_title; ?>
				<ul id="bstat-pop-posts"><?php
				echo $pop_posts;
				?></ul>
			<?php echo $after_widget; ?>
<?php
		}
	}

	function widget_popular_posts_delete_cache() {
		wp_cache_delete( 'bstat_pop_posts', 'widget' );
	}

	function widget_popular_posts_control() {
		$options = $newoptions = get_option('bstat_pop_posts');
		if ( $_POST['bstat-pop-posts-submit'] ) {
			$newoptions['title'] = strip_tags(stripslashes($_POST['bstat-pop-posts-title']));
			$newoptions['number'] = (int) $_POST['bstat-pop-posts-number'];
			$newoptions['days'] = (int) $_POST['bstat-pop-posts-days'];
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('bstat_pop_posts', $options);
			$this->widget_popular_posts_delete_cache();
		}
		$title = attribute_escape($options['title']);
		if ( !$number = (int) $options['number'] )
			$number = 5;
		if ( !$days = (int) $options['days'] )
			$days = 7;
	?>
				<p><label for="bstat-pop-posts-title"><?php _e('Title:'); ?> <input style="width: 250px;" id="bstat-pop-posts-title" name="bstat-pop-posts-title" type="text" value="<?php echo $title; ?>" /></label></p>
				<p><label for="bstat-pop-posts-number"><?php _e('Number of posts to show:'); ?> <input style="width: 25px; text-align: center;" id="bstat-pop-posts-number" name="bstat-pop-posts-number" type="text" value="<?php echo $number; ?>" /></label> <?php _e('(at most 15)'); ?></p>
				<p><label for="bstat-pop-posts-days"><?php _e('In past x days (1 = today only):'); ?> <input style="width: 25px; text-align: center;" id="bstat-pop-posts-days" name="bstat-pop-posts-days" type="text" value="<?php echo $days; ?>" /></label> <?php _e('(at most 30)'); ?></p>
				<input type="hidden" id="bstat-pop-posts-submit" name="bstat-pop-posts-submit" value="1" />
	<?php
	}

	function widget_popular_refs($args) {
		global $post, $wpdb;

		extract($args, EXTR_SKIP);
		$options = get_option('bstat_pop_refs');
		$title = empty($options['title']) ? __('Recent Search Terms') : $options['title'];
		if ( !$number = (int) $options['number'] )
			$number = 5;
		else if ( $number < 1 )
			$number = 1;
		else if ( $number > 15 )
			$number = 15;

		if ( !$days = (int) $options['days'] )
			$days = 7;
		else if ( $days < 1 )
			$days = 1;
		else if ( $days > 30 )
			$days = 30;

		if ( !$pop_refs = wp_cache_get( 'bstat_pop_refs', 'widget' ) ) {
			$pop_refs = $this->pop_refs("limit=$number&days=$days");
			wp_cache_add( 'bstat_pop_refs', $pop_refs, 'widget', 3600 );
		}

		if ( !empty($pop_refs) ) {
?>
			<?php echo $before_widget; ?>
				<?php echo $before_title . $title . $after_title; ?>
				<ul id="bstat-pop-refs"><?php
				echo $pop_refs;
				?></ul>
			<?php echo $after_widget; ?>
<?php
		}
	}

	function widget_popular_refs_delete_cache() {
		wp_cache_delete( 'bstat_pop_refs', 'widget' );
	}

	function widget_popular_refs_control() {
		$options = $newoptions = get_option('bstat_pop_refs');
		if ( $_POST['bstat-pop-refs-submit'] ) {
			$newoptions['title'] = strip_tags(stripslashes($_POST['bstat-pop-refs-title']));
			$newoptions['number'] = (int) $_POST['bstat-pop-refs-number'];
			$newoptions['days'] = (int) $_POST['bstat-pop-refs-days'];
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('bstat_pop_refs', $options);
			$this->widget_popular_refs_delete_cache();
		}
		$title = attribute_escape($options['title']);
		if ( !$number = (int) $options['number'] )
			$number = 5;
		if ( !$days = (int) $options['days'] )
			$days = 7;
	?>
				<p><label for="bstat-pop-refs-title"><?php _e('Title:'); ?> <input style="width: 250px;" id="bstat-pop-refs-title" name="bstat-pop-refs-title" type="text" value="<?php echo $title; ?>" /></label></p>
				<p><label for="bstat-pop-refs-number"><?php _e('Number of refs to show:'); ?> <input style="width: 25px; text-align: center;" id="bstat-pop-refs-number" name="bstat-pop-refs-number" type="text" value="<?php echo $number; ?>" /></label> <?php _e('(at most 15)'); ?></p>
				<input type="hidden" id="bstat-pop-refs-submit" name="bstat-pop-refs-submit" value="1" />
	<?php
	}

	function widgets_register(){
		$this->widget_recently_commented_posts_register();

		wp_register_sidebar_widget('bsuite-related-posts', __('bSuite Related Posts', 'bsuite'), array(&$this, 'widget_related_posts'), 'bsuite_related_posts');
		wp_register_widget_control('bsuite-related-posts', __('bSuite Related Posts', 'bsuite'), array(&$this, 'widget_related_posts_control'), 'width=320&height=90');

		wp_register_sidebar_widget('bsuite-sharelinks', __('bSuite Share Links', 'bsuite'), array(&$this, 'widget_sharelinks'), 'bsuite_sharelinks');

		wp_register_sidebar_widget('bstat-pop-posts', __('bStat Posts'), array(&$this, 'widget_popular_posts'), 'bstat-pop-posts');
		wp_register_widget_control('bstat-pop-posts', __('bStat Posts'), array(&$this, 'widget_popular_posts_control'), 'width=320&height=90');

		wp_register_sidebar_widget('bstat-pop-refs', __('bStat Refs'), array(&$this, 'widget_popular_refs'), 'bstat-pop-refs');
		wp_register_widget_control('bstat-pop-refs', __('bStat Refs'), array(&$this, 'widget_popular_refs_control'), 'width=320&height=90');
	}
	// end widgets



	// administrivia
	function activate() {

		update_option('bsuite_doing_migration', time() + 7200 );

		$this->createtables();
		$this->cron_register();

		// set some defaults for the plugin
		if(!get_option('bsuite_insert_related'))
			update_option('bsuite_insert_related', TRUE);

		if(!get_option('bsuite_insert_sharelinks'))
			update_option('bsuite_insert_sharelinks', FALSE);

		if(!get_option('bsuite_searchsmart'))
			update_option('bsuite_searchsmart', TRUE);

		if(!get_option('bsuite_swhl'))
			update_option('bsuite_swhl', TRUE);

		if(!get_option('bsuite_insert_css'))
			update_option('bsuite_insert_css', TRUE);

		if(!get_option('bsuite_migration_interval'))
			update_option('bsuite_migration_interval', 90);

		if(!get_option('bsuite_migration_count'))
			update_option('bsuite_migration_count', 100);

		if(!get_option('bsuite_load_max'))
			update_option('bsuite_load_max', 4);


		// set some defaults for the widgets
		if(!get_option('bsuite_related_posts'))
			update_option('bsuite_related_posts', array('title' => 'Related Posts', 'number' => 7));

		if(!get_option('bsuite_recently_commented_posts'))
			update_option('bsuite_recently_commented_posts', array('title' => 'Recently Commented Posts', 'number' => 7));

		if(!get_option('bstat_pop_posts'))
			update_option('bstat_pop_posts', array('title' => 'Popular Posts', 'number' => 5, 'days' => 7));

		if(!get_option('bstat_pop_refs'))
			update_option('bstat_pop_refs', array('title' => 'Popular Searches', 'number' => 5));
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

		dbDelta("
			CREATE TABLE $this->hits_incoming (
				in_time timestamp NOT NULL default CURRENT_TIMESTAMP,
				in_type tinyint(4) NOT NULL default '0',
				in_session varchar(32) default '',
				in_to text NOT NULL,
				in_from text,
				in_extra text
			) ENGINE=MyISAM $charset_collate
			");

		dbDelta("
			CREATE TABLE $this->hits_terms (
				term_id bigint(20) NOT NULL auto_increment,
				name varchar(255) NOT NULL default '',
				PRIMARY KEY  (term_id),
				UNIQUE KEY name_uniq (name),
				KEY name (name(8))
			) ENGINE=MyISAM $charset_collate
			");

		dbDelta("
			CREATE TABLE $this->hits_targets (
				object_id bigint(20) unsigned NOT NULL default '0',
				object_type smallint(6) NOT NULL,
				hit_count smallint(6) unsigned NOT NULL default '0',
				hit_date date NOT NULL default '0000-00-00',
				PRIMARY KEY  (object_id,object_type,hit_date)
			) ENGINE=MyISAM $charset_collate
			");

		dbDelta("
			CREATE TABLE $this->hits_searchphrases (
				object_id bigint(20) unsigned NOT NULL default '0',
				object_type smallint(6) NOT NULL,
				term_id bigint(20) unsigned NOT NULL default '0',
				hit_count smallint(6) unsigned NOT NULL default '0',
				PRIMARY KEY  (object_id,object_type,term_id),
				KEY term_id (term_id)
			) ENGINE=MyISAM $charset_collate
			");

		dbDelta("
			CREATE TABLE $this->hits_sessions (
				sess_id bigint(20) NOT NULL auto_increment,
				sess_cookie varchar(32) NOT NULL default '',
				sess_date datetime default NULL,
				sess_ip varchar(16) NOT NULL default '',
				sess_bl varchar(8) default '',
				sess_bb varchar(24) default '',
				sess_br varchar(24) default '',
				sess_ba varchar(200) default '',
				PRIMARY KEY  (sess_id),
				UNIQUE KEY sess_cookie_uniq (sess_cookie),
				KEY sess_cookie (sess_cookie(2))
			) ENGINE=MyISAM $charset_collate
			");

		dbDelta("
			CREATE TABLE $this->hits_shistory (
				sess_id bigint(20) NOT NULL auto_increment,
				object_id bigint(20) NOT NULL,
				object_type smallint(6) NOT NULL,
				KEY sess_id (sess_id),
				KEY object_id (object_id,object_type)
			) ENGINE=MyISAM $charset_collate
			");

		dbDelta("
			CREATE TABLE $this->hits_pop (
				post_id bigint(20) NOT NULL,
				date_start date NOT NULL,
				hits_total bigint(20) NOT NULL,
				hits_recent int(10) NOT NULL
			) ENGINE=MyISAM $charset_collate
			");
	}

	function mu_options( $options ) {
		$added = array( 'bsuite' => array( 'bsuite_insert_related', 'bsuite_insert_sharelinks', 'bsuite_searchsmart', 'bsuite_swhl' ));

		$options = add_option_whitelist( $added, $options );
	
		return( $options );
	}

	function addmenus() {
		add_options_page('bSuite Settings', 'bSuite', 8, plugin_basename( dirname( __FILE__ )) .'/ui_options.php' );
		
		// the bstat reports are handled in a seperate file
		add_submenu_page('index.php', 'bSuite bStat Reports', 'bStat Reports', 2, plugin_basename( dirname( __FILE__ )) .'/ui_stats.php' );
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

	function makeXMLTree($data){
		// create parser
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1);
		xml_parse_into_struct($parser,$data,$values,$tags);
		xml_parser_free($parser);
		
		// we store our path here
		$hash_stack = array();
		
		// this is our target
		$ret = array();
		foreach ($values as $key => $val) {
	
			switch ($val['type']) {
				case 'open':
					array_push($hash_stack, $val['tag']);
					if (isset($val['attributes']))
						$ret = $this->composeArray($ret, $hash_stack, $val['attributes']);
					else
						$ret = $this->composeArray($ret, $hash_stack);
				break;
	
				case 'close':
					array_pop($hash_stack);
				break;
				
				case 'cdata':
					array_push($hash_stack, 'cdata');
					$ret = $this->composeArray($ret, $hash_stack, $val['value']);
					array_pop($hash_stack);
				break;
	
				case 'complete':
					array_push($hash_stack, $val['tag']);
					$ret = $this->composeArray($ret, $hash_stack, $val['value']);
					array_pop($hash_stack);
					
					// handle attributes
					if (isset($val['attributes'])){
						foreach($val['attributes'] as $a_k=>$a_v){
							$hash_stack[] = $val['tag'].'_attribute_'.$a_k;
							$ret = $this->composeArray($ret, $hash_stack, $a_v);
							array_pop($hash_stack);
						}
					}
				break;
			}
		}
		
		return($ret);
	} // end makeXMLTree
	
	function &composeArray($array, $elements, $value=array()){
		// function used exclusively by makeXMLTree to help turn XML into an array	
	
		// get current element
		$element = array_shift($elements);
		
		// does the current element refer to a list
		if(sizeof($elements) > 0){
			$array[$element][sizeof($array[$element])-1] = &$this->composeArray($array[$element][sizeof($array[$element])-1], $elements, $value);
		}else{ // if (is_array($value))
			$array[$element][sizeof($array[$element])] = $value;
		}
		
		return($array);
	} // end composeArray
	


	function command_rebuild_searchsmart() {
		// update search table with content from all posts
		global $wpdb; 
	
		set_time_limit(0);
		ignore_user_abort(TRUE);
		$interval = 25;


		if( !isset( $_REQUEST[ 'n' ] ) ) {
			$n = 0;
			$this->createtables();		
			$wpdb->get_results( 'TRUNCATE TABLE '. $this->search_table );
		} else {
			$n = (int) $_REQUEST[ 'n' ] ;
		}
		if( $count = $this->searchsmart_upindex() ) {
			echo '<div class="updated"><p><strong>' . __('Rebuilding bSuite search index.', 'bsuite') . '</strong> Already did '. ( $n + $count ) .', be patient already!</p></div><div class="narrow">';

			?>
			<p><?php _e("If your browser doesn't start loading the next page automatically click this link:"); ?> <a href="?page=<?php echo plugin_basename(dirname(__FILE__)); ?>/ui_options.php&Options=<?php echo urlencode( __( 'Rebuild bSuite search index', 'bsuite' )) ?>&n=<?php echo ($n + $interval) ?>"><?php _e("Next Posts"); ?></a> </p></div>
			<script language='javascript'>
			<!--

			function nextpage() {
				location.href="?page=<?php echo plugin_basename(dirname(__FILE__)); ?>/ui_options.php&Options=<?php echo urlencode( __( 'Rebuild bSuite search index', 'bsuite' )) ?>&n=<?php echo ($n + $interval) ?>";
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
				location.href="?page=<?php echo plugin_basename(dirname(__FILE__)); ?>/ui_options.php";
			}
			setTimeout( "nextpage()", 3000 );

			//-->
			</script>
			<?php
		}
	}

	function command_rebuild_autoksum() {
		// update search table with content from all posts
		global $wpdb; 
	
		set_time_limit(0);
		ignore_user_abort(TRUE);
		$interval = 5;


		if( !isset( $_REQUEST[ 'n' ] ) ) {
			$n = 0;
		} else {
			$n = (int) $_REQUEST[ 'n' ] ;
		}
		if( $count = $this->autoksum_backfill() ) {
			echo '<div class="updated"><p><strong>' . __('Generating excerpts.', 'bsuite') . '</strong> Already did '. ( $n + $count ) .', be patient already!</p></div><div class="narrow">';

			?>
			<p><?php _e("If your browser doesn't start loading the next page automatically click this link:"); ?> <a href="?page=<?php echo plugin_basename(dirname(__FILE__)); ?>/ui_options.php&Options=<?php echo urlencode( __( 'Add post_excerpt to all posts', 'bsuite' )) ?>&n=<?php echo ($n + $interval) ?>"><?php _e("Next Posts"); ?></a> </p></div>
			<script language='javascript'>
			<!--

			function nextpage() {
				location.href="?page=<?php echo plugin_basename(dirname(__FILE__)); ?>/ui_options.php&Options=<?php echo urlencode( __( 'Add post_excerpt to all posts', 'bsuite' )) ?>&n=<?php echo ($n + $interval) ?>";
			}
			setTimeout( "nextpage()", 250 );

			//-->
			</script>
			<?php
		} else {
			echo '<div class="updated"><p><strong>'. __('All posts now have excerpts! All done.', 'bsuite') .'</strong></p></div>';
			?>
			<script language='javascript'>
			<!--

			function nextpage() {
				location.href="?page=<?php echo plugin_basename(dirname(__FILE__)); ?>/ui_options.php";
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


class bSuite_sms {
	/*
	Based on Clickatell SMS API v. 1.6 by Aleksandar Markovic <mikikg@gmail.com>
	Code re-used under the terms of the GPL license
	http://sourceforge.net/projects/sms-api/ SMS-API Sourceforge project page

	get API ID and account at http://www.clickatell.com/ :
	Clickatell documentation at https://www.clickatell.com/developers/api_http.php

	it's worth knowing the Clickatell privacy policy too:
	http://www.clickatell.com/company/privacy.php

	example usage:
	$mysms = new bSuite_sms( $API_ID, $USERNAME, $PASSWORD );
	$mysms->send( $YOUR_MESSAGE, $TO_PHONE_NUMBER );
	*/

	var $use_ssl = FALSE;
	var $balace_limit = 0;
	var $balance = FALSE;
	var $sending_method = 'fopen';
	var $unicode = FALSE;
	var $curl_use_proxy = FALSE;
	var $curl_proxy = 'http://127.0.0.1:8080';
	var $curl_proxyuserpwd = 'login:secretpass';
	var $session;
	var $error;
	var $callback = 0;
	var $msgstatuscodes = array(
		'001' => 'Message unknown. The message ID is incorrect or reporting is delayed.',
		'002' => 'Message queued. The message could not be delivered and has been queued for attempted redelivery.',
		'003' => 'Delivered to gateway.Delivered to the upstream gateway or network (delivered to the recipient).',
		'004' => 'Received by recipient. Confirmation of receipt on the handset of the recipient.',
		'005' => 'Error with message. There was an error with the message, probably caused by the content of the message itself.',
		'006' => 'User cancelled message delivery. The message was terminated by an internal mechanism.',
		'007' => 'Error delivering message. An error occurred delivering the message to the handset.',
		'008' => 'OK. Message received by gateway.',
		'009' => 'Routing error. The routing gateway or network has had an error routing the message.',
		'010' => 'Message expired. Message has expired before we were able to deliver it to the upstream gateway. No charge applies.',
		'011' => 'Message queued for later delivery. Message has been queued at the gateway for delivery at a later time (delayed delivery).',
		'012' => 'Out of credit. The message cannot be delivered due to a lack of funds in your account. Please re-purchase credits.'
	); // codes come from clickatell docs https://www.clickatell.com/downloads/http/Clickatell_HTTP.pdf

	function bSuite_sms( $api_id = '', $user = '', $password = '' ) {

		/* authentication details */	
		if(( !$api_id ) || ( !$user ) || ( !$password )){
			$this->error = 'You must specify an api id, username, and password.';
			return( FALSE );
		}
		$this->api_id = $api_id;
		$this->user = $user;
		$this->password = $password;

		/* SSL? */
		if( $this->use_ssl ) {
			$this->base	  = 'http://api.clickatell.com/http';
			$this->base_s = 'https://api.clickatell.com/http';
		} else {
			$this->base	  = 'http://api.clickatell.com/http';
			$this->base_s = $this->base;
		}

		$this->_auth();
	}

	function _auth() {
		$comm = sprintf( '%s/auth?api_id=%s&user=%s&password=%s', $this->base_s, $this->api_id, $this->user, $this->password );
		$this->session = $this->_parse_auth( $this->_execgw( $comm ));
	}

	function getbalance() {
		$comm = sprintf( '%s/getbalance?session_id=%s', $this->base, $this->session );
		$this->balance = $this->_parse_getbalance( $this->_execgw( $comm ));
		return $this->balance;
	}

	/* check the status of a message by the ID returned from the API */
	function querymsg( $msgid ) {
		$comm = sprintf( '%s/querymsg?session_id=%s&apimsgid=%s',
			$this->base,
			$this->session,
			$msgid
		);
		$result = $this->_execgw( $comm );
		if( $this->msgstatuscodes[ substr( $result, stripos( $result, 'Status:' ) + 8 ) ] )
			return( $this->msgstatuscodes[ substr( $result, stripos( $result, 'Status:' ) + 8 ) ]);
		else
			return( $result );
	}

	function send( $text=null, $to=null, $from=null ) {

		/* Check SMS credits balance */
		if( $this->getbalance() < $this->balace_limit ) {
			$this->error = 'You have reach the SMS credit limit!';
			return( FALSE );
		};

		/* Check SMS $text length */
		if( $this->unicode == TRUE ) {
			$this->_chk_mbstring();
			if( mb_strlen( $text ) > 210 ) {
				$this->error = 'Your unicode message is too long! (Current lenght='.mb_strlen ( $text ).')';
				return( FALSE );
			}
			/* Does message need to be concatenate */
			if( mb_strlen( $text ) > 70 ) {
				$concat = '&concat=3';
			} else {
				$concat = '';
			}
		} else {
			if( strlen( $text ) > 459 ) {
				$this->error = 'Your message is too long! (Current lenght='.strlen( $text ).')';
				return( FALSE );
			}
			/* Does message need to be concatenate */
			if( strlen( $text ) > 160 ) {
				$concat = '&concat=3';
			} else {
				$concat = '';
			}
		}

		/* Check $to is not empty */
		if( empty( $to )) {
			$this->error = 'You not specify destination address (TO)!';
			return( FALSE );
		}
		/* $from is optional and not universally supported */

		/* Reformat $to number */
		$cleanup_chr = array( '+', ' ', '(', ')', '\r', '\n', '\r\n');
		$to = str_replace( $cleanup_chr, '', $to );

		/* Mark this for later */
		$this->last_to = $to;
		$this->last_from = $from;
		$this->last_message = $text;

		/* Send SMS now */
		$comm = sprintf( '%s/sendmsg?session_id=%s&to=%s&from=%s&text=%s&callback=%s&unicode=%s%s',
			$this->base,
			$this->session,
			rawurlencode( $to ),
			rawurlencode( $from ),
			$this->encode_message( $text ),
			$this->callback,
			$this->unicode,
			$concat
		);
		return $this->_parse_send( $this->_execgw( $comm ));
	}

	function encode_message( $text ) {
		if( $this->unicode != TRUE ) {
			//standard encoding
			return rawurlencode( $text );
		} else {
			//unicode encoding
			$uni_text_len = mb_strlen( $text, 'UTF-8' );
			$out_text = '';

			//encode each character in text
			for( $i=0; $i<$uni_text_len; $i++ ) {
				$out_text .= $this->uniord( mb_substr( $text, $i, 1, 'UTF-8' ));
			}

			return $out_text;
		}
	}

	function uniord( $c ) {
		$ud = 0;
		if( ord( $c{0} )>=0 && ord( $c{0} )<=127 )
			$ud = ord( $c{0} );
		if( ord( $c{0} )>=192 && ord( $c{0} )<=223 )
			$ud = ( ord( $c{0} )-192 )*64 + ( ord( $c{1} )-128 );
		if( ord( $c{0} )>=224 && ord( $c{0} )<=239 )
			$ud = ( ord( $c{0} )-224 )*4096 + ( ord( $c{1} )-128 )*64 + ( ord( $c{2} )-128 );
		if( ord( $c{0} )>=240 && ord( $c{0} )<=247 )
			$ud = ( ord( $c{0} )-240 )*262144 + ( ord( $c{1} )-128 )*4096 + ( ord( $c{2} )-128 )*64 + ( ord( $c{3} )-128 );
		if( ord( $c{0} )>=248 && ord( $c{0} )<=251 )
			$ud = ( ord( $c{0} )-248 )*16777216 + ( ord( $c{1} )-128 )*262144 + ( ord( $c{2} )-128 )*4096 + ( ord( $c{3} )-128 )*64 + ( ord( $c{4} )-128 );
		if( ord( $c{0} )>=252 && ord( $c{0} )<=253 )
			$ud = ( ord( $c{0} )-252 )*1073741824 + ( ord( $c{1} )-128 )*16777216 + ( ord( $c{2} )-128 )*262144 + ( ord( $c{3} )-128 )*4096 + ( ord( $c{4} )-128 )*64 + ( ord( $c{5} )-128 );
		if( ord( $c{0} )>=254 && ord( $c{0} )<=255 ) //error
			$ud = FALSE;
		return sprintf( '%04x', $ud );
	}

	function token_pay( $token ) {
		$comm = sprintf( '%s/http/token_pay?session_id=%s&token=%s',
		$this->base,
		$this->session,
		$token );

		return $this->_execgw( $comm );
	}

	function _execgw( $command ) {
		if( $this->sending_method == 'curl' )
			return $this->_curl( $command );
		if( $this->sending_method == 'fopen' )
			return $this->_fopen( $command );
		$this->error = 'Unsupported sending method!';
		return( FALSE );
	}

	function _curl( $command ) {
		$this->_chk_curl();
		$ch = curl_init( $command );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER,1 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER,0 );
		if( $this->curl_use_proxy ) {
			curl_setopt( $ch, CURLOPT_PROXY, $this->curl_proxy );
			curl_setopt( $ch, CURLOPT_PROXYUSERPWD, $this->curl_proxyuserpwd );
		}
		$result=curl_exec( $ch );
		curl_close( $ch );
		return $result;
	}

	function _fopen( $command ) {
		$result = '';
		$handler = @fopen( $command, 'r' );
		if( $handler ) {
			while( $line = @fgets( $handler,1024 )) {
				$result .= $line;
			}
			fclose( $handler );
			return $result;
		} else {
			$this->error = 'Error while executing fopen sending method!<br>Please check does PHP have OpenSSL support and is PHP version is greater than 4.3.0.';
			return( FALSE );
		}
	}

	function _parse_auth( $result ) {
		$session = substr( $result, 4 );
		$code = substr( $result, 0, 2 );
		if( $code!='OK' ) {
			$this->error = "Error in SMS authorization! ($result)";
			return( FALSE );
		}
		return $session;
	}

	function _parse_send( $result ) {
		if( 'ID' <> substr( $result, 0, 2 )) {
			$this->error = "Error sending SMS! ($result)";
			$this->last_status = 'ERROR';
			return( FALSE );
		} else {
			$this->last_id = trim( substr( $result, 3 ));
			$this->last_status = 'OK';
			return( 'OK' );
		}
	}

	function _parse_getbalance( $result ) {
		$result = substr( $result, 8 );
		return (int ) $result;
	}

	function _chk_curl() {
		if( !extension_loaded( 'curl' )) {
			$this->error = 'This SMS API class can not work without CURL PHP module! Try using fopen sending method.';
			return( FALSE );
		}
	}

	function _chk_mbstring() {
		if( !extension_loaded( 'mbstring' )) {
			$this->error = 'Error. This SMS API class is setup to use Multibyte String Functions module - mbstring, but module not found. Please try to set unicode=false in class or install mbstring module into PHP.';
			return( FALSE );
		}
	}
}










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

function bstat_hits($template = '%%hits%% hits, about %%avg%% daily', $post_id = NULL, $todayonly = 0, $return = NULL) {
	global $bsuite;
	if(!empty($return))
		return($bsuite->post_hits(array('post_id' => $post_id,'days' => $todayonly, 'template' => $template )));
	echo $bsuite->post_hits(array('post_id' => $post_id,'days' => $todayonly, 'template' => $template ));
}


// deprecated functions
function bstat_pulse($post_id = 0, $maxwidth = 400, $disptext = 1, $dispcredit = 1, $accurate = 4) {
	// this one isn't so much deprecated as, well, 
	// the code sucks and I haven't re-written it yet

	global $wpdb, $bstat;

	$post_id = (int) $post_id;

	$for_post_id = $post_id > 1 ? 'AND post_id = '. $post_id : '';
	
	// here's the query, but let's try to get the data from cache first
	$request = "SELECT
		SUM(hit_count) AS hits, 
		hit_date
		FROM $bstat->hits_table
		WHERE 1=1
		$for_post_id
		GROUP BY hit_date
		";

	if ( !$result = wp_cache_get( $post_id, 'bstat_post_pulse' ) ) {
		$result = $wpdb->get_results($request, ARRAY_A);
		wp_cache_add( $post_id, $result, 'bstat_post_pulse', 3600 );
	}

	if(empty($result))
		return(NULL);

	$tot = count($result);
	
	if(count($result)>0){
		$point = null;
		$point[] = 0;
		foreach($result as $row){
			$point[] = $row['hits'];
		}
		$sum = array_sum($point);
		$max = max($point);
		$avg = round($sum / $tot);

		if($accurate == 4){
			$graphaccurate = get_option('bstat_graphaccurate');
		}else{
			$graphaccurate = $accurate;
		}
		
		$minwidth = ($maxwidth / 8.1);
		if($graphaccurate) $minwidth = ($maxwidth / 4.1);
		
		while(count($point) <= $minwidth){
			$newpoint = null;
			for ($i = 0; $i < count($point); $i++) {
				if($i > 0){
					if(!$graphaccurate) $newpoint[] = ((($point[$i-1] * 2) + $point[$i]) / 3);
					$newpoint[] = (($point[$i-1] + $point[$i]) / 2);
					if(!$graphaccurate) $newpoint[] = (($point[$i-1] + ($point[$i-1] * 2)) / 3);
				}
				$newpoint[] = $point[$i];
			}
			$point = $newpoint;
		}


		$tot = count($point);
		$width = round($maxwidth / $tot);
		if($width > 3)
			$width = 4;

		if($width < 1)
			$width = 1;

		if(($width  * $tot) > $maxwidth)
			$skipstart = (($width  * $tot) - $maxwidth) / $width;

		$i = 1;
		$hit_chart = "";
		foreach($point as $row){
			if((!$skipstart) || ($i > $skipstart)){
				$hit_chart .= "<img src='" . get_settings('siteurl') .'/'. PLUGINDIR .'/'. plugin_basename(dirname(__FILE__))  . "/img/spacer.gif' width='$width' height='" . round((($row) / $max) * 100) . "' alt='graph element.' />";
				}
			$i++;
		}
			
		$pre = "<div id=\"bstat_pulse\">";
		$post = "</div>";
		$disptext = ($disptext == 1) ? (number_format($sum) .' total reads, averaging '. number_format($avg) .' daily') : ("");
		$dispcredit = ($dispcredit == 1) ? ("<small><a href='http://maisonbisson.com/blog/search/bsuite' title='a pretty good WordPress plugin'>stats powered by bSuite bStat</a></small>") : ("");
		$disptext = (($disptext) || ($dispcredit)) ? ("\n<p>$disptext\n<br />$dispcredit</p>") : ("");
		
		echo($pre . $hit_chart . "\n" . $disptext . $post);
	}
}



// php4 compatibility, argh
if(!function_exists('str_ireplace')){
	function str_ireplace($a, $b, $c){
		return str_replace($a, $b, $c);
	}
}


?>