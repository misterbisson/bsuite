<?php 
/*
Plugin Name: bStat Upgrader
Plugin URI: http://maisonbisson.com/blog/bsuite
Description: Upgrade from bSuite bStats. 3.x to bSuite 4
Version: 4.0 a 
Author: Casey Bisson
Author URI: http://maisonbisson.com/blog/
*/
/*  Copyright 2008 Casey Bisson 

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
				$this->get_targets(); 
				break; 
			case 2 : 
				$this->get_terms(); 
				break; 
			case 3 : 
				$this->get_searchwords(); 
				break; 
			case 4 : 
				$this->delete_taxonomies(); 
				break; 
			case 5 : 
				$this->delete_terms(); 
				break; 
			case 6 : 
				$this->optimize_wptables(); 
				break; 
			case 7 : 
				$this->delete_oldtables(); 
				break; 
			case 8 : 
				$this->show_queries(); 
				break; 
		} 

		// load the footer
		$this->footer();
	} 

	function header()  {
		echo '<div class="wrap">';
		echo '<h2>'.__('bSuite bStat Upgrader').'</h2>';
	}

	function footer() {
		echo '</div>';
	}

	function greet() {
		global $wpdb, $bsuite; 
		set_time_limit( 0 );
		
		echo '<div class="narrow">';
		echo '<p>'.__('If you used bSuite3 for stats collection, you&#8217;ll need this (or some manual MySQL queries) to move the data into the new bSuite4 tables.').'</p>';

		if( !count( $wpdb->get_col( $this->query_checktables ))){
			echo '<h3>'.__('Huh? You&#8217;ve got no tables to import.').'</h3>';
		}else if( ! 0 == ini_get( 'max_execution_time' ) ){
			echo '<h3>'.__('Ack! Failed to reset PHP&#8217;s <a href="http://php.net/set_time_limit">maximum execution time</a>.').'</h3>';
			echo '<p>'. __('Your server&#8217;s default time of ') . ini_get( 'max_execution_time' ) . __(' seconds may be too low to complete this upgrade. Some queries can take as long as 30 minutes to complete on a large data set.') .'</p>';
			echo '<p>'. __('You can try executing the MySQL commands manually if you&#8217;d like.') .'</p>';
		}else if( !$bsuite ){
			echo '<p>'.__('You must activate bSuite 4 before proceeding.').'</p>';
		}else{
			$bsuite->createtables(); // just to make sure
			echo '<p><strong>'.__('It&#8217;s worth mentioning that most people recommend backing up your database before doing things like this.').'</strong></p>';
			echo '<form action="admin.php?import='. $this->importer_code .'&amp;step=1" method="post">';
			echo '<p class="submit"><input type="submit" name="submit" value="'.__('Import hits stats &raquo;').'" /></p>';
			echo '<p>No thanks, <a href="admin.php?import='. $this->importer_code .'&amp;step=8">just show me the MySQL queries</a>.</p>';
			echo '</form>';
		}
		echo '</div>';

		// options we don't need anymore
		delete_option( 'bstat_import_refs' );
		delete_option( 'bstat_import_hits' );
	}

	function get_targets() { 
		set_time_limit( 0 );
		global $wpdb, $bsuite; 

		update_option('bsuite_doing_migration', time() + 3000 );

		echo '<div class="narrow">';
		echo '<h3>Step 1 of 7.</h3>';
		echo '<p>Importing post hits stats.</p>';
		echo '<p>Please be patient, this could take a long time.</p>';
		flush();

		$wpdb->get_results( $this->query_get_targets );

		echo '<p>Done!</p>';
		echo '<form action="admin.php?import='. $this->importer_code .'&amp;step=2" method="post">';
		echo '<p class="submit"><input type="submit" name="submit" value="'.__('Import search terms &raquo;').'" /></p>';
		echo '</form>';
	}

	function get_terms() { 
		set_time_limit( 0 );
		global $wpdb, $bsuite; 

		update_option('bsuite_doing_migration', time() + 3000 );

		echo '<div class="narrow">';
		echo '<h3>Step 2 of 7.</h3>';
		echo '<p>Importing old search terms.</p>';
		echo '<p>Please be patient, this could take a long time.</p>';
		flush();

		$wpdb->get_results( $this->query_get_terms );

		echo '<p>Done!</p>';
		echo '<form action="admin.php?import='. $this->importer_code .'&amp;step=3" method="post">';
		echo '<p class="submit"><input type="submit" name="submit" value="'.__('Import search targets &raquo;').'" /></p>';
		echo '</form>';
	}

	function get_searchwords() { 
		set_time_limit( 0 );
		global $wpdb, $bsuite; 

		update_option('bsuite_doing_migration', time() + 6000 );

		echo '<div class="narrow">';
		echo '<h3>Step 3 of 7.</h3>';
		echo '<p>Importing old search targets.</p>';
		echo '<p>Please be patient, this could take a long time.</p>';
		flush();

		$wpdb->get_results( $this->query_get_searchwords );

		echo '<p>Done!</p>';
		echo '<form action="admin.php?import='. $this->importer_code .'&amp;step=4" method="post">';
		echo '<p class="submit"><input type="submit" name="submit" value="'.__('Clean up taxonomies &raquo;').'" /></p>';
		echo '</form>';
	}

	function delete_taxonomies() { 
		set_time_limit( 0 );
		global $wpdb, $bsuite; 

		update_option('bsuite_doing_migration', time() + 3000 );

		echo '<div class="narrow">';
		echo '<h3>Step 4 of 7.</h3>';
		echo '<p>Cleaning up WordPress&#8217; term_taxonomy table.</p>';
		echo '<p>Please be patient, this could take a long time.</p>';
		
		$wpdb->get_results( $this->query_delete_taxonomies );

		echo '<p>Done!</p>';
		echo '<form action="admin.php?import='. $this->importer_code .'&amp;step=5" method="post">';
		echo '<p class="submit"><input type="submit" name="submit" value="'.__('Clean up terms &raquo;').'" /></p>';
		echo '</form>';
	}

	function delete_terms() { 
		set_time_limit( 0 );
		global $wpdb, $bsuite; 

		update_option('bsuite_doing_migration', time() + 3000 );

		echo '<div class="narrow">';
		echo '<h3>Step 5 of 7.</h3>';
		echo '<p>Cleaning up WordPress&#8217; terms table.</p>';
		echo '<p>Please be patient, this could take a long time.</p>';
		flush();

		$wpdb->get_results( $this->query_delete_terms );

		echo '<p>Done!</p>';
		echo '<form action="admin.php?import='. $this->importer_code .'&amp;step=6" method="post">';
		echo '<p class="submit"><input type="submit" name="submit" value="'.__('Optimize tables &raquo;').'" /></p>';
		echo '</form>';
	}

	function optimize_wptables() { 
		set_time_limit( 0 );
		global $wpdb, $bsuite; 

		update_option('bsuite_doing_migration', time() + 3000 );

		echo '<div class="narrow">';
		echo '<h3>Step 6 of 7.</h3>';
		echo '<p>Optimizing WordPress&#8217; terms and term_taxonomies tables.</p>';
		flush();

		foreach( explode( ';', $this->query_optimize_wptables ) as $query )
			$wpdb->get_results( $query  );

		echo '<p>Done!</p>';
		echo '<form action="admin.php?import='. $this->importer_code .'&amp;step=7" method="post">';
		echo '<p class="submit"><input type="submit" name="submit" value="'.__('Delete old tables &raquo;').'" /></p>';
		echo '</form>';
	}

	function delete_oldtables() { 
		set_time_limit( 0 );
		global $wpdb, $bsuite; 

		update_option('bsuite_doing_migration', time() + 3000 );

		echo '<div class="narrow">';
		echo '<h3>Step 7 of 7.</h3>';
		echo '<p>Deleting old bSuite3 tables.</p>';
		flush();

		foreach( explode( ';', $this->query_delete_oldtables ) as $query )
			$wpdb->get_results( $query  );

		echo '<p>Done!</p>';

		echo '<p>Fini!</p>';

		echo '<p>All set!</p>';

		echo '<h4>Don&#8217;t forget to disable this plugin.</h4>';
	}

	function show_queries() { 
		echo '<ol>';
		echo '<li><h3>Import page load stats:</h3><pre>'. $this->query_get_targets .'</pre></liu>';
		echo '<li><h3>Import search terms:</h3><pre>'. $this->query_get_terms .'</pre></liu>';
		echo '<li><h3>Import search targets:</h3><pre>'. $this->query_get_searchwords .'</pre></liu>';
		echo '<li><h3>Clean up taxonomies:</h3><pre>'. $this->query_delete_taxonomies .'</pre></liu>';
		echo '<li><h3>Clean up terms:</h3><pre>'. $this->query_delete_terms .'</pre></liu>';
		echo '<li><h3>Optimize tables:</h3><pre>'. $this->query_optimize_wptables .'</pre></liu>';
		echo '<li><h3>Delete old tables:</h3><pre>'. $this->query_delete_oldtables .'</pre></liu>';
		echo '</ol>';
	}


	// Default constructor 
	function bStat_Import() { 
		global $wpdb, $bsuite; 

		$home = $bsuite->bstat_insert_term( get_settings( 'siteurl' ));

echo '<h2>'. get_settings( 'siteurl' ) .'</h2>';
echo '<h2>'. $bsuite->bstat_is_term( get_settings( 'siteurl' )) .'</h2>';

		// the queries we use
		$this->query_checktables = 'SHOW TABLES LIKE "'. $wpdb->prefix .'bsuite3%"';

		$this->query_get_targets = 'INSERT IGNORE
INTO '. $bsuite->hits_targets .'
SELECT post_id AS object_id, 0 AS object_type, hit_count, hit_date
FROM '. $wpdb->prefix .'bsuite3_hits
WHERE post_id != 0 ;
INSERT IGNORE
INTO '. $bsuite->hits_targets .'
SELECT '. $home .' AS object_id, 1 AS object_type, hit_count, hit_date
FROM '. $wpdb->prefix .'bsuite3_hits
WHERE post_id = 0 ;';

		$this->query_get_terms = 'INSERT IGNORE
INTO '. $bsuite->hits_terms .' (name)
SELECT t.name AS name
FROM '. $wpdb->terms .' t
LEFT JOIN '. $wpdb->term_taxonomy .' tt ON t.term_id = tt.term_id
WHERE tt.taxonomy = "bsuite_search";';

		$this->query_get_searchwords = 'INSERT IGNORE
INTO '. $bsuite->hits_searchphrases .'
SELECT a.post_id AS object_id, 0 AS object_type, c.term_id AS term_id, SUM(a.hit_count) AS hit_count
FROM '. $wpdb->prefix .'bsuite3_refs_terms a
LEFT JOIN '. $wpdb->terms .' b ON a.term_id = b.term_id
LEFT JOIN '. $bsuite->hits_terms .' c ON b.name = c.name
WHERE a.post_id != 0
GROUP BY object_id, object_type, term_id;';

		$this->query_delete_taxonomies = 'DELETE QUICK
FROM '. $wpdb->term_taxonomy .'
WHERE taxonomy = "bsuite_search";';

		$this->query_delete_terms = 'DELETE QUICK
FROM '. $wpdb->terms .'
USING '. $wpdb->terms .'
LEFT JOIN '. $wpdb->term_taxonomy .' ON '. $wpdb->terms .'.term_id = '. $wpdb->term_taxonomy .'.term_id
WHERE '. $wpdb->term_taxonomy .'.term_id IS NULL;';

		$this->query_optimize_wptables = 'OPTIMIZE TABLE '. $wpdb->terms .';
OPTIMIZE TABLE '. $wpdb->term_taxonomy .';';

		$this->query_delete_oldtables = 'DROP TABLE '. $wpdb->prefix .'bsuite3_hits;
DROP TABLE '. $wpdb->prefix .'bsuite3_refs_terms;
DROP TABLE '. $wpdb->prefix .'bsuite3_search;';
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
