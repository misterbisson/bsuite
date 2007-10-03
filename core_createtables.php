<?php

global $wpdb, $bsuite;

require_once(ABSPATH . 'wp-admin/upgrade-functions.php');

$newversion = FALSE;
if($bsuite->options['verso'] <> $bsuite->verso)
	$newversion = TRUE;
	
// get list of tables and look for our tables in it
$request = "SHOW TABLES;";
$rows = $wpdb->get_results($request);
foreach($rows as $table) {
	foreach($table as $value) if($value==$bsuite->search_table) $search_table = true;
	foreach($table as $value) if($value==$bsuite->cache_table) $cache_table = true;
}

if(!$search_table || $newversion){
	$request = "
		CREATE TABLE $bsuite->search_table (
			post_id mediumint(9) NOT NULL,
			content text,
			title text,
			PRIMARY KEY  (post_id),
			FULLTEXT KEY search (content, title)
		)
		";
	dbDelta($request);
}

if($cache_table && $newversion){
	$request = "
		DROP TABLE bsuite->cache_table
		";
	dbDelta($request);
}
if(!$cache_table || $newversion){
	$request = "
		CREATE TABLE $bsuite->cache_table (
		cache_date timestamp NOT NULL default CURRENT_TIMESTAMP,
		cache_bank enum('user','bsuggestive','refsforpost','innerindex') NOT NULL default 'user',
		cache_item mediumint(9) NOT NULL default '0',
		cache_content text NOT NULL,
		PRIMARY KEY  (cache_bank,cache_item)
		)
		";
	dbDelta($request);
}

if(!$hits_table || !$search_table || !$meta_table || !$cache_table){
}

?>