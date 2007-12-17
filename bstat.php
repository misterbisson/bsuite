<?php
/*
Plugin Name: bSuite bStat
Plugin URI: http://maisonbisson.com/blog/bsuite/bstat
Description: Stats tracking, part of the bSuite collection of blog tools
Version: 3.02
Author: Casey Bisson
Author URI: http://maisonbisson.com/blog/
*/

/*  Copyright 2005 - 2007  Casey Bisson

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class bStat {

	function bStat(){
	
		// define the tables
		global $wpdb;
		$this->hits_table = $wpdb->prefix . 'bsuite3_hits';
		$this->rterms_table = $wpdb->prefix . 'bsuite3_refs_terms';

		// get the options
//		$this->options = unserialize(get_option('bStat3'));
		$this->ktnxbye = FALSE;

		// register hooks
		add_filter('the_content', array(&$this, 'hitit'));

		// activation and menu hooks
		register_activation_hook(__FILE__, array(&$this, 'activate'));
		add_action('widgets_init', array(&$this, 'widgets_register'));
		add_action('admin_menu', array(&$this, 'addmenus'));
		// end register WordPress hooks

		// register the taxonomy for search terms
		register_taxonomy( 'bsuite_search' , 'post' );
	}

	function addmenus() {
		add_submenu_page('index.php', 'bSuite bStat Reports', 'bStat Reports', 2, __FILE__, array(&$this, 'reports'));
	}

	function reports() {
		global $wpdb, $bsuite;
		require(ABSPATH . PLUGINDIR .'/'. plugin_basename(dirname(__FILE__)) .'/bstat_reports.php');
	}
	
	function activate() {
		$this->createtables();

		// set some defaults for the widgets
		if(!get_option('bstat_pop_posts'))
			update_option('bstat_pop_posts', array('title' => 'Popular Posts', 'number' => 5, 'days' => 7));

		if(!get_option('bstat_pop_refs'))
			update_option('bstat_pop_refs', array('title' => 'Popular Searches', 'number' => 5, 'days' => 7));
	}

	function hitit(&$content){

		// nonce this
		if($this->ktnxbye)
			return($content);
		
		global $wp_query;

		$id = 0; // if this hit can't be pinned on something else, we give it to post #0
		if(is_singular())
			$id = $wp_query->posts[0]->ID;
		
		$this->hit_post($id);
		$this->hit_ref($id);
		
		$this->ktnxbye = TRUE;

		// we're using the the_content hook because wp_head is theme dependent,
		// and template_redirect and loop_start could execute repeatedly 
		// and defied my nonce-ing attempts 
		return($content);		
	}


	function hit_post($post_id) {	
		global $wpdb;
	
		if($post_id = (int) $post_id){
			$request = "INSERT INTO $this->hits_table
						(post_id, hit_count, hit_date) 
						VALUES ($post_id, 1, NOW())
						ON DUPLICATE KEY UPDATE hit_count = hit_count + 1;";
			$wpdb->query($request);
			return(TRUE);
		}
		return(FALSE);
	}

	function hit_ref($post_id) {
		global $wpdb, $bsuite;

		$search = $this->get_search_terms($this->get_search_engine());

		if(empty($search))
			return(FALSE);
		
		// if we've got the full bsuite, then 
		// set the object var so everybody can use this data
		if(!empty($bsuite)){
			$bsuite->the_search_array = $search;
			$bsuite->the_search_terms = implode($search, ' ');
		}

		// check if this search is already in the terms table
		if(!is_term(implode($search, ' '), 'bsuite_search'))
			wp_insert_term(implode($search, ' '), 'bsuite_search');

		// it's in the terms table, what's the id?
		$term_id = is_term(implode($search, ' '));

		// write it to the bsuite3_refs table with date
		if(!empty($term_id) && $post_id = (int) $post_id){
			$request = "INSERT INTO $this->rterms_table
						(post_id, term_id, hit_count, hit_date) 
						VALUES ($post_id, $term_id, 1, NOW())
						ON DUPLICATE KEY UPDATE hit_count = hit_count + 1;";
			$wpdb->query($request);
		}

		// add it to the post's terms
		// disabled now because of memory problems it causes
		//if(is_singular())
		//	wp_set_object_terms($post_id, implode($search, ' '), 'bsuite_search', TRUE);

		return(TRUE);
	}

	function get_search_engine() {
		// a lot of inspiration and code for this function was taken from
		// Search Hilite by Ryan Boren and Matt Mullenweg
		global $wp_query;
		if( empty($_SERVER['HTTP_REFERER']) && empty($wp_query->query_vars['s']))
			return false;

		if ( is_search() )
			return('internal');

		$referer = urldecode($_SERVER['HTTP_REFERER']);
		if (preg_match('|^http://(www)?\.?google.*|i', $referer))
			return('google');
	
		if (preg_match('|^http://search\.yahoo.*|i', $referer))
			return('yahoo');

		if (preg_match('|^http://search\.lycos.*|i', $referer))
			return('lycos');
	
		$siteurl = get_option('home');
		if (preg_match("#^$siteurl#i", $referer))
			return('internal');
	
		return(FALSE);
	}

	function get_search_terms($engine = 'google') {
		// a lot of inspiration and code for this function was taken from
		// Search Hilite by Ryan Boren and Matt Mullenweg
		if(empty($engine))
			return(FALSE);

		$referer = urldecode($_SERVER['HTTP_REFERER']);
		$query_array = array();
		switch ($engine) {
		case 'google':
			// Google query parsing code adapted from Dean Allen's
			// Google Hilite 0.3. http://textism.com
			$query_terms = preg_replace('/^.*q=([^&]+)&?.*$/i','$1', $referer);
			$query_terms = preg_replace('/\'|"/', '', $query_terms);
			$query_array = preg_split ("/[\s,\+\.]+/", $query_terms);
			break;
	
		case 'lycos':
			$query_terms = preg_replace('/^.*query=([^&]+)&?.*$/i','$1', $referer);
			$query_terms = preg_replace('/\'|"/', '', $query_terms);
			$query_array = preg_split ("/[\s,\+\.]+/", $query_terms);
			break;
	
		case 'yahoo':
			$query_terms = preg_replace('/^.*p=([^&]+)&?.*$/i','$1', $referer);
			$query_terms = preg_replace('/\'|"/', '', $query_terms);
			$query_array = preg_split ("/[\s,\+\.]+/", $query_terms);
			break;
			
		case 'internal':
			$search = get_query_var('s');
			$search_terms = get_query_var('search_terms');
	
			if (!empty($search_terms)) {
				$query_array = $search_terms;
			} else if (!empty($search)) {
				$query_array = array($search);
			} else {
				$query_terms = preg_replace('/^.*s=([^&]+)&?.*$/i','$1', $referer);
				if(preg_match('|^http://|i', $query_terms))
					return(FALSE);
				$query_terms = preg_replace('/\'|"/', '', $query_terms);
				$query_array = preg_split ("/[\s,\+\.]+/", $query_terms);
			}
		}
		
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

		$post_id = (int) $args['post_id'] > 1 ? 'AND post_id = '. (int) $args['post_id'] : '';
	
		$date = '';
		if($args['days'] > 1)
			$date  = "AND hit_date > '". date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d") - $args['days'], date("Y"))) ."'";
	
		// here's the query, but let's try to get the data from cache first
		$request = "SELECT
			FORMAT(SUM(hit_count), 0) AS hits, 
			FORMAT(AVG(hit_count), 0) AS average
			FROM $this->hits_table
			WHERE 1=1
			$post_id
			$date
			";

		if ( !$result = wp_cache_get( (int) $args['post_id'] .'_'. (int) $args['days'], 'bstat_post_hits' ) ) {
			$result = $wpdb->get_results($request, ARRAY_A);
			wp_cache_add( (int) $args['post_id'] .'_'. (int) $args['days'], $result, 'bstat_post_hits', 300 );
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
		global $wpdb;

		$defaults = array(
			'count' => 15,
			'return' => 'formatted',
			'template' => '<li><a href="%%link%%">%%title%%</a>&nbsp;(%%hits%%)</li>'
		);
		$args = wp_parse_args( $args, $defaults );
	
		$date = 'AND hit_date = NOW()';
		if($args['days'] > 1)
			$date  = "AND hit_date > '". date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d") - $args['days'], date("Y"))) ."'";
	
		$limit = 'LIMIT '. (0 + $args['count']);
	
	
		$request = "SELECT post_id, SUM(hit_count) AS hit_count
			FROM $this->hits_table
			WHERE 1=1
			AND post_id <> 0
			$date
			GROUP BY post_id
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
				$list .= str_replace(array('%%title%%','%%hits%%','%%link%%'), array(get_the_title($post['post_id']), $post['hit_count'], get_permalink($post['post_id'])), $args['template']);
			}
			return($list);
		}
	}

	function pop_refs( $args = '' ) {
		global $wpdb;

		$defaults = array(
			'count' => 15,
			'return' => 'formatted',
			'template' => '<li>%%title%%&nbsp;(%%hits%%)</li>'
		);
		$args = wp_parse_args( $args, $defaults );
	
		$date = 'AND hit_date = NOW()';
		if($args['days'] > 1)
			$date  = "AND hit_date > '". date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d") - $args['days'], date("Y"))) ."'";
	
		$limit = 'LIMIT '. (0 + $args['count']);
	
	
		$request = "SELECT term_id, SUM(hit_count) AS hit_count
			FROM $this->rterms_table
			WHERE 1=1
			$date
			GROUP BY term_id
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
				$term = get_term($row['term_id'], 'bsuite_search');
				$list .= str_replace(array('%%title%%','%%hits%%'), array($term->name, $row['hit_count']), $args['template']);
			}		
			return($list);
		}
	}


	// widgets
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
			wp_cache_add( 'bstat_pop_posts', $pop_posts, 'widget', 300 );
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
			wp_cache_add( 'bstat_pop_refs', $pop_refs, 'widget', 300 );
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
				<p><label for="bstat-pop-refs-days"><?php _e('In past x days (1 = today only):'); ?> <input style="width: 25px; text-align: center;" id="bstat-pop-refs-days" name="bstat-pop-refs-days" type="text" value="<?php echo $days; ?>" /></label> <?php _e('(at most 30)'); ?></p>
				<input type="hidden" id="bstat-pop-refs-submit" name="bstat-pop-refs-submit" value="1" />
	<?php
	}

	function widgets_register(){
		wp_register_sidebar_widget('bstat-pop-posts', __('bStat Posts'), array(&$this, 'widget_popular_posts'), 'bstat-pop-posts');
		wp_register_widget_control('bstat-pop-posts', __('bStat Posts'), array(&$this, 'widget_popular_posts_control'), 'width=320&height=90');

		wp_register_sidebar_widget('bstat-pop-refs', __('bStat Refs'), array(&$this, 'widget_popular_refs'), 'bstat-pop-refs');
		wp_register_widget_control('bstat-pop-refs', __('bStat Refs'), array(&$this, 'widget_popular_refs_control'), 'width=320&height=90');
	}
	// end widgets

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
			CREATE TABLE $this->hits_table (
			  post_id bigint(20) unsigned NOT NULL default '0',
			  hit_count smallint(6) unsigned NOT NULL default '0',
			  hit_date date NOT NULL default '0000-00-00',
			  PRIMARY KEY  (post_id,hit_date)
			) $charset_collate
			");

		dbDelta("
			CREATE TABLE $this->rterms_table (
			  post_id bigint(20) unsigned NOT NULL default '0',
			  term_id bigint(20) unsigned NOT NULL default '0',
			  hit_count smallint(6) unsigned NOT NULL default '0',
			  hit_date date NOT NULL default '0000-00-00',
			  PRIMARY KEY  (hit_date,term_id,post_id)
			) $charset_collate
			");
	}
}

// now instantiate this object
$bstat = & new bStat;

// deprecated functions
function bstat_todaypop($limit, $before, $after, $return = 0) {
	global $bstat;
	if(!empty($return))
		return($bstat->pop_posts(array('limit' => $limit, 'days' => 0, 'template' => $before .'<a href="%%link%%">%%title%%</a>&nbsp;(%%hits%%)'. $after )));
	echo $bstat->pop_posts(array('limit' => $limit, 'days' => 0, 'template' => $before .'<a href="%%link%%">%%title%%</a>&nbsp;(%%hits%%)'. $after ));
}

function bstat_recentpop($limit, $days, $before, $after, $return = 0) {
	global $bstat;
	if(!empty($return))
		return($bstat->pop_posts(array('limit' => $limit, 'days' => $days, 'template' => $before .'<a href="%%link%%">%%title%%</a>&nbsp;(%%hits%%)'. $after )));
	echo $bstat->pop_posts(array('limit' => $limit, 'days' => $days, 'template' => $before .'<a href="%%link%%">%%title%%</a>&nbsp;(%%hits%%)'. $after ));
}

function bstat_todayrefs($maxresults, $before, $after, $return = 0) {
	global $bstat;
	if(!empty($return))
		return($bstat->pop_refs(array('limit' => $limit, 'days' => 0, 'template' => $before .'%%title%%&nbsp;(%%hits%%)'. $after )));
	echo $bstat->pop_refs(array('limit' => $limit, 'days' => 0, 'template' => $before .'%%title%%&nbsp;(%%hits%%)'. $after ));
}

function bstat_recentrefs($maxresults, $days, $before, $after, $return = 0) {
	global $bstat;
	if(!empty($return))
		return($bstat->pop_refs(array('limit' => $limit, 'days' => $days, 'template' => $before .'%%title%%&nbsp;(%%hits%%)'. $after )));
	echo $bstat->pop_refs(array('limit' => $limit, 'days' => $days, 'template' => $before .'%%title%%&nbsp;(%%hits%%)'. $after ));
}

function bstat_hits($template = '%%hits%% hits, about %%avg%% daily', $post_id = NULL, $todayonly = 0, $return = NULL) {
	global $bstat;
	if(!empty($return))
		return($bstat->post_hits(array('post_id' => $post_id,'days' => $todayonly, 'template' => $template )));
	echo $bstat->post_hits(array('post_id' => $post_id,'days' => $todayonly, 'template' => $template ));
}

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
		wp_cache_add( $post_id, $result, 'bstat_post_pulse', 300 );
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
				$hit_chart .= "<img src='" . get_settings('siteurl') .'/'. PLUGINDIR .'/'. plugin_basename(dirname(__FILE__))  . "/spacer.gif' width='$width' height='" . round((($row) / $max) * 100) . "' alt='graph element.' />";
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

function bstat_discussionbycomment($limit, $before, $after, $return = 0) {
	// this function (like the one below) is here only for people who refuse
	// to use widgets. If you do use widgets _and_ this function, you'll get
	// cache collisions, as these use the same cache name as the widgets.
	global $wpdb;
	$limit = (int) $limit;

	if ( !$comments = wp_cache_get( 'recent_comments', 'widget' ) ) {
		$comments = $wpdb->get_results("SELECT comment_author, comment_author_url, comment_ID, comment_post_ID FROM $wpdb->comments WHERE comment_approved = '1' ORDER BY comment_date_gmt DESC LIMIT $limit");
		wp_cache_add( 'recent_comments', $comments, 'widget' );
	}

	$comments = ''; 
	if( $commented_posts ) {
		foreach( $commented_posts as $comment ) {
			$comments .= $before . sprintf(__('%1$s on %2$s'), get_comment_author_link(), '<a href="'. get_permalink($comment->comment_post_ID) . '#comment-' . $comment->comment_ID . '">' . get_the_title($comment->comment_post_ID) . '</a>'). $after;
		}
	}

	if(!empty($return))
		return($comments);
	echo $comments;
}

function bstat_discussionbypost($limit, $before, $after, $return = 0) {
	// this one brought back from the dead specifically for cliffy.
	// please use the widgets instead.
	global $wpdb;
	$limit = (int) $limit;

	if ( !$commented_posts = wp_cache_get( 'recently_commented_posts', 'widget' ) ) {
		$commented_posts = $wpdb->get_results("SELECT comment_ID, comment_post_ID, COUNT(comment_post_ID) as comment_count, MAX(comment_date_gmt) AS sort_order FROM $wpdb->comments WHERE comment_approved = '1' GROUP BY comment_post_ID ORDER BY sort_order DESC LIMIT $limit");
		wp_cache_add( 'recently_commented_posts', $commented_posts, 'widget' );
	}

	$comments = ''; 
	if( $commented_posts ) {
		foreach( $commented_posts as $comment ) {
			$comments .= $before .'<a href="'. get_permalink($comment->comment_post_ID) . '#comment-' . $comment->comment_ID . '">' . get_the_title($comment->comment_post_ID) . '</a>&nbsp;('. $comment->comment_count .')'. $after;
		}
	}

	if(!empty($return))
		return($comments);
	echo $comments;
}

?>