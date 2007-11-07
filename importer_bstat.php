<?php 
/*
Plugin Name: bStat Upgrader
Plugin URI: http://maisonbisson.com/blog/bsuite
Description: Upgrade from previous versions of bSuite stats/bStat to bSuite bStats.
Version: 0.1
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
class bStat_Import { 
	var $importer_code = 'bstatimporter'; 
	var $importer_name = 'bStat Upgrader'; 
	var $importer_desc = 'Upgrade from previous versions of bSuite stats/bStat to bSuite bStats.'; 
	 
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
				$this->get_dates(); 
				break; 
			case 2 : 
				$this->get_hits(); 
				break; 
		} 

		// load the footer
		$this->footer();
	} 

	function header()  {
		echo '<div class="wrap">';
		echo '<h2>'.__('bStat Upgrader').'</h2>';
	}

	function footer() {
		echo '</div>';
	}

	function greet() {
		echo '<div class="narrow">';
		echo '<p>'.__('Yeah baby! This imports hit counts and search engine terms from older versions of bSuite or bStat into the new bSuite bStat.').'</p>';
		echo '<p>'.__('This has not been tested much. Mileage may vary.').'</p>';
		
		if(!bstat){
			echo '<p>'.__('You must activate bStat before proceeding.').'</p>';
		}else{
			echo '<p><strong>'.__('Don&#8217;t be stupid - backup your database before proceeding!').'</strong></p>';
			echo '<form action="admin.php?import='. $this->importer_code .'&amp;step=1" method="post">';
			echo '<p class="submit"><input type="submit" name="submit" value="'.__('Step 1 &raquo;').'" /></p>';
			echo '</form>';
		}
		echo '</div>';
	}

	function get_dates() { 
		global $wpdb; 
		$dates_hits = $wpdb->get_col('SELECT bstat_date 
			FROM '. $wpdb->prefix .'bstat_hits
			GROUP BY bstat_date
			');
		update_option('bstat_import_hits', $dates_hits);

		$dates_refs = $wpdb->get_col('SELECT bstat_date 
			FROM '. $wpdb->prefix .'bstat_refs
			WHERE issearchengine = 1
			GROUP BY bstat_date
			');
		update_option('bstat_import_refs', $dates_refs);

		echo '<div class="narrow">';
		echo '<p>You&#039;ve got '. count($dates_hits) .' days of hits and '. count($dates_refs) .' days of referrers (yes, they&#039;re often different).</p>';

		echo '<form action="admin.php?import='. $this->importer_code .'&amp;step=2" method="post">';
		echo '<p class="submit"><input type="submit" name="submit" value="'.__('Next &raquo;').'" /></p>';
		echo '</form>';
	}

	function get_hits() { 
		echo '<div class="narrow">';
		// update search table with content from all posts
		global $wpdb, $bstat; 
	
		set_time_limit(0);
		ignore_user_abort(TRUE);

		$dates_hits = get_option('bstat_import_hits');
		$date_hit = array_shift($dates_hits);
		$hits = $wpdb->get_results('
			SELECT post_id, hits_reads 
			FROM '. $wpdb->prefix .'bstat_hits 
			WHERE bstat_date = "'. $date_hit .'"
			', ARRAY_A);
		update_option('bstat_import_hits', $dates_hits);

		if( is_array( $hits ) ) {
			echo '<p>You&#039;ve got '. count($hits) .' entries in the hits table for '. $date_hit .'. Just '. count($dates_hits).' more days to go, please be patient.</p>';

			foreach( $hits as $hit ) {
				$wpdb->get_results("
					INSERT INTO $bstat->hits_table
					(post_id, hit_count, hit_date)
					VALUES ({$hit['post_id']}, {$hit['hits_reads']}, '{$date_hit}')
					ON DUPLICATE KEY UPDATE hit_count = hit_count + {$hit['hits_reads']}
					");
				echo '. ';
			}
		}

		$dates_refs = get_option('bstat_import_refs');
		$date_ref = array_shift($dates_refs);
		$refs = $wpdb->get_results('
			SELECT post_id, hits, ref
			FROM '. $wpdb->prefix .'bstat_refs 
			WHERE bstat_date = "'. $date_ref .'"
			AND issearchengine = 1
			', ARRAY_A);
		update_option('bstat_import_refs', $dates_refs);

		if( is_array( $refs ) ) {
			echo '<p>You&#039;ve got '. count($refs) .' entries in the refs table for '. $date_ref .'. Only '. count($dates_refs).' more days to go, please be patient.</p>';

			foreach( $refs as $ref ) {
				// check if this search is already in the terms table
				if(!is_term(urldecode($ref['ref']), 'bsuite_search'))
					wp_insert_term(urldecode($ref['ref']), 'bsuite_search');
		
				// it's in the terms table, what's the id?
				$term_id = is_term(urldecode($ref['ref']));
		
				// write it to the bsuite3_refs table with date
				if(!empty($term_id)){
					$wpdb->query("
						INSERT INTO $bstat->rterms_table
							(post_id, term_id, hit_count, hit_date) 
							VALUES ({$ref['post_id']}, $term_id, {$ref['hits']}, '$date_ref')
							ON DUPLICATE KEY UPDATE hit_count = hit_count + {$ref['hits']};
						");
					// the following is disabled now because it causes memory problems
					//wp_set_object_terms($ref['post_id'], urldecode($ref['ref']), 'bsuite_search', TRUE);
				}
				echo '. ';
			}
		}

		if(count($dates_hits) == 0 && count($dates_refs) == 0){
			echo '<p><strong>Yep. We&#039;re done.</strong></p>';
			echo '<p>Now, go deactivate this plugin. You don&#039;t need it anymore. (Running it again will inflate your stats, but thats cheating.)</p>';
		} else {
			?>
			<p><?php _e("If your browser doesn't start loading the next page automatically click this link:"); ?> <a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?import=<?php echo $this->importer_code; ?>&step=2"><?php _e("Next Page"); ?></a> </p>
			<script language='javascript'>
			<!--

			function nextpage() {
				location.href="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?import=<?php echo $this->importer_code; ?>&step=2";
			}
			setTimeout( "nextpage()", 250 );

			//-->
			</script>
			<?php
		}
	} 

	// Default constructor 
	function bStat_Import() { 
		// Nothing. 
	} 
} 

// Instantiate and register the importer 
include_once(ABSPATH . 'wp-admin/includes/import.php'); 
if(function_exists('register_importer')) { 
	$bstat_import = new bStat_Import(); 
	register_importer($bstat_import->importer_code, $bstat_import->importer_name, $bstat_import->importer_desc, array (&$bstat_import, 'dispatch')); 
} 

add_action('activate_'.plugin_basename(__FILE__), 'bstat_importer_activate'); 

function bstat_importer_activate() { 
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
