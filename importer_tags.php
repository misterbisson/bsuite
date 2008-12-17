<?php 
/*
Plugin Name: bSuite Tag Importer
Plugin URI: http://maisonbisson.com/bsuite
Description: Import bSuite tags and metadata to WordPress tags and taxonomy. <a href="http://maisonbisson.com/bsuite/tag-importer">Documentation here</a>
Version: 4.0.3
Author: Casey Bisson
Author URI: http://maisonbisson.com/blog/
*/
/*  Copyright 2007 Casey Bisson 

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
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA  
*/ 

// The importer 
class bSuite_Import { 
	var $importer_code = 'bsuiteimporter'; 
	var $importer_name = 'bSuite Tag Importer'; 
	var $importer_desc = 'Import bSuite tags and metadata to WordPress tags and taxonomy. <a href="http://maisonbisson.com/blog/bsuite/tag-importer">Documentation here</a>.'; 
	 
	// Function that will handle the wizard-like behaviour 
	function dispatch() { 
		if (empty ($_GET['step'])) 
			$step = 0; 
		else 
			$step = (int) $_GET['step']; 


		// load the header
		$this->header();

		switch ($step) { 
			case 0 :
				$this->greet();
				break;
			case 1 : 
				//check_admin_referer('bsuiteimporter'); 
				$this->import(); 
				break; 
		} 

		// load the footer
		$this->footer();
	} 

	// Function that does the actual importing 
	function import() { 
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
	
		$posts = $wpdb->get_results("SELECT ID, post_content
			FROM $wpdb->posts
			WHERE post_type <> 'attachment'
			ORDER BY ID ASC
			LIMIT $n, $interval
			", ARRAY_A);
			
		if( is_array( $posts ) ) {
			echo '<p>Fetching each post, looking for tags, importing them, making coffee. Please be patient.<br /><br /></p>';
			print '<ul>';
			foreach( $posts as $post ) {
				$this->workit($post['ID'], 'post', $post['post_content']);
				echo '<li><a href="'. get_permalink($post['ID']) .'">updated post '. $post['ID'] ."</a></li>\n ";
				flush();
			}
			print '</ul>';
			?>
			<p><?php _e("If your browser doesn't start loading the next page automatically click this link:"); ?> <a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?import=<?php echo $this->importer_code; ?>&step=1&n=<?php echo ($n + $interval) ?>"><?php _e("Next Posts"); ?></a> </p>
			<script language='javascript'>
			<!--

			function nextpage() {
				location.href="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?import=<?php echo $this->importer_code; ?>&step=1&n=<?php echo ($n + $interval) ?>";
			}
			setTimeout( "nextpage()", 250 );

			//-->
			</script>
			<?php
		} else {
			echo '<p>That&#039;s all folks. kthnxbye.</p>';
			echo '<p><strong>Don&#039;t forget to deactivate this plugin now that you don&#039;t need it anymore.</strong></p>';
		}
	} 

	function header()  {
		echo '<div class="wrap">';
		echo '<h2>'.__('bSuite Tag Importer').'</h2>';
	}

	function footer() {
		echo '</div>';
	}

	function greet() {
		echo '<div class="narrow">';
		echo '<p>'.__('Howdy! This imports tags from posts in this blog to the new WordPress native tagging structure.').'</p>';
		echo '<p>'.__('This has not been tested much. Mileage may vary.').'</p>';
		echo '<p><strong>'.__('Don&#8217;t be stupid - backup your database before proceeding!').'</strong></p>';
		echo '<form action="admin.php?import='. $this->importer_code .'&amp;step=1" method="post">';
		echo '<p class="submit"><input type="submit" name="submit" value="'.__('Step 1 &raquo;').'" /></p>';
		echo '</form>';
		echo '</div>';
	}

	function workit($object_id, $object_type, $object_content){
		$tags = $this->gettags_from_content($object_content);		
		foreach($tags['tags'] as $tag){
			$tag = $this->parse_tag( $tag );
			if(!is_taxonomy( $tag['taxonomy'] ))
				register_taxonomy($tag['taxonomy'], $object_type);
			wp_set_object_terms($object_id, $tag['term'], $tag['taxonomy'], TRUE);
//			wp_set_object_terms($object_id, $tag['term'], 'post_tag', TRUE);
		}//end foreach
/*
		if( $object_type = 'post' ){
			$post = wp_get_single_post($object_id, ARRAY_A);
			if($post['post_content'] <> $tags['content']){
				$post['post_content'] = $tags['content'];
				$post = add_magic_quotes( $post );
				wp_update_post($post);
			}
		}
*/
	}

	function parse_tag( $tag ) {
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

	function get_tag_link( $tag ) {
		global $wp_rewrite;
		$taglink = $wp_rewrite->get_tag_permastruct();

		$tag_parsed = $this->parse_tag( $tag );
		$slug = sanitize_title($tag_parsed['term']);
	
		if ( empty($taglink) ) {
			$file = get_option('home') . '/';
			$taglink = $file . '?tag=' . $slug;
		} else {
			$taglink = str_replace('%tag%', $slug, $taglink);
			$taglink = get_option('home') . user_trailingslashit($taglink, 'category');
		}
		return $taglink;
	}
	
	function gettags_from_content($content) {
		// return an array of both the formatted content and the raw tags
		$loweredcontent = strtolower($content);
	
		// find any rel="tag" links
		$atags = array();
		$tag_pattern = '/(rel=[\'|"]tag[\'|"]\>(.*?)\<\/a\>)/i';
		preg_match_all($tag_pattern, $content, $atags);
	
		// find any <tag>Tag Name</tag> tags
		$btags = array();
		$tag_pattern = '/(<tag>(.*?)<\/tag>)/i';
		preg_match_all($tag_pattern, $content, $btags);
		// replace <tag>Tag Name</tag> with links to a tag resolver
		foreach($btags[2] as $btag){
			$content = str_replace("<tag>$btag</tag>", '<a href="'. $this->get_tag_link(trim($btag)) .'" rel="tag">' . trim($btag) . '</a>', $content);
		}

		// find any [tag]Tag Name[/tag] tags
		$bbtags = array();
		$tag_pattern = '/(\[tag\](.*?)\[\/tag\])/i';
		preg_match_all($tag_pattern, $content, $bbtags);
		// replace [tag]Tag Name[/tag] with links to a tag resolver
		foreach($bbtags[2] as $bbtag){
			$content = str_replace('[tag]'. $bbtag .'[/tag]', '<a href="'. $this->get_tag_link(trim($bbtag)) .'" rel="tag">' . trim($bbtag) . '</a>', $content);
		}

		// find any <tags>Tag 1,Tag 2,...</tags> tags
		$ctags = array();
		$tag_pattern = '/(<tags>(.*?)<\/tags>)/i';
		if (preg_match($tag_pattern, $content, $matches)) {
			$ctags = preg_split('/,[\s?]/', $matches[2]);
		}
	
		// remove the <tags></tags> text block 
		// (a block of links to a tag resolver will be inserted later)
		if($matches[2])
			$content = preg_replace($tag_pattern, '', $content);
	
		// find any [tags]Tag 1,Tag 2,...[/tags] tags
		$cctags = array();
		$tag_pattern = '/(\[tags\](.*?)\[\/tags\])/i';
		if (preg_match($tag_pattern, $content, $matches)) {
			$cctags = preg_split('/,[\s?]/', $matches[2]);
		}
	
		// remove the [tags][/tags] text block 
		// (a block of links to a tag resolver will be inserted later)
		if($matches[2])
			$content = preg_replace($tag_pattern, '', $content);
	
		// remove Ecto's tag block
		// Ecto is an XML-RPC client, more info at http://ecto.kung-foo.tv/
		$content = preg_replace('/<!-- technorati tags start -->(.*?)<!-- technorati tags end -->/i', '', $content);
	
		// concatenate all the tags in one array
		$post_tags = array();
		$post_tags = array_unique(array_merge($atags[2], $btags[2], $bbtags[2], $ctags, $cctags));

		$result['content'] = $content;
		$result['tags'] = $post_tags;
		return($result);
	}

	// Default constructor 
	function bSuite_Import() { 
		// Nothing. 
	} 
} 

// Instantiate and register the importer 
include_once(ABSPATH . 'wp-admin/includes/import.php'); 
if(function_exists('register_importer')) { 
	$bsuite_import = new bSuite_Import(); 
	register_importer($bsuite_import->importer_code, $bsuite_import->importer_name, $bsuite_import->importer_desc, array ($bsuite_import, 'dispatch')); 
} 

add_action('activate_'.plugin_basename(__FILE__), 'bsuite_importer_tags_activate'); 

function bsuite_importer_tags_activate() { 
	global $wp_db_version; 
	 
	// Deactivate on pre 2.3 blogs 
	if($wp_db_version<6075) { 
		$current = get_settings('active_plugins'); 
	array_splice($current, array_search( plugin_basename(__FILE__), $current), 1 ); 
	update_option('active_plugins', $current); 
	do_action('deactivate_'.plugin_basename(__FILE__));		 
	} 
} 

?>
