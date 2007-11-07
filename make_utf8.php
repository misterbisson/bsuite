<?php

bsuite_make_utf8_duh("Open this file in an editor, READ the description, and take note of the warnings.<br />If all that makes sense to you and you want to continue, then remove line #3 of this file and try again.");

/*
ABOUT:
This script will create a duplicate of all tables in the current database,
set their character encoding to UTF-8, and then transcode the contents of
the old tables into the new (converting latin1 characters to utf8 along the way).

New tables will be prefixed 'conv_'. Check the new tables to make sure it worked,
then rename the old tables and drop these converted tables into place.

BEWARES:
This works by moving huge amounts of data around. Only a fool would go 
without the simple protection of a backup.

This may break PHP serialized arrays (as are common in WordPress options tables).

The script is written to ignore a lot of errors. It's up to you to 
make sure the copies are consitent and sane.

You might fill your MySQL data disk if you're doing this on a large 
set of tables stored on an under-spec'd server.

Demons might rise up from the ground and taunt you and your mother 
mercilessly before burning your data in a scene that looks like the 
climax of Raiders Of The Lost Ark. 

You have been warned. See additional disclaimers in license.

LICENSE:
Copyright 2005 - 2007  Casey Bisson  http://MaisonBisson.com/

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

error_reporting(E_ERROR);
set_time_limit(0);
ignore_user_abort(TRUE);

require_once('../../../wp-config.php');

function bsuite_make_utf8_duh( $message ){
	echo "<h2>$message</h2>";
	exit( $message );
}

function bsuite_make_utf8_clean( $content ){
	global $wpdb;
	error_reporting(E_ERROR);
	if( function_exists( 'mb_convert_encoding' ))
		$content = mb_convert_encoding($content, "utf-8", "auto");
	return("'". $wpdb->escape($content) ."'");
}

function bsuite_make_utf8_refresh( $stuff ){
	$url = $PHP_SELF .'?'. http_build_query($stuff);	
	?>
	<p><?php _e("If your browser doesn't start loading the next page automatically click this link:"); ?> <a href="<?php echo $url; ?>"><?php _e("Next"); ?></a></p></div>
	<script language='javascript'>
	<!--

	function nextpage() {
		location.href="<?php echo $url; ?>";
	}
	setTimeout( "nextpage()", 25 );

	//-->
	</script>
	<?php
	exit();
}

// identify which tables remain to be processed
if( isset( $_GET[ 't_rem' ] ))
	if(!$t_rem = explode(',', ereg_replace("[^_|,|a-z|A-Z|0-9]", '', $_GET[ 't_rem' ])))
		bsuite_make_utf8_duh("Can't get list of tables from get vars.");

if( empty( $t_rem ) && !isset( $_GET[ 't' ] ))
	if( !$t_rem = $wpdb->get_col( 'SHOW TABLES;' )) // get a list of tables from the db
		bsuite_make_utf8_duh("Can't get list of tables from DB.");
	else
		bsuite_make_utf8_refresh(array('t_rem' => implode($t_rem, ',')));

// identify the table we're working on now
if( isset( $_GET[ 't' ] ))
	if( !$t = ereg_replace( "[^_|a-z|A-Z|0-9]", '', $_GET[ 't' ] ))
		bsuite_make_utf8_duh("Can't get current table from get vars.");

if( empty( $t ) )
	if(!$t = array_shift( $t_rem )) // get the current table from the array of tables
		bsuite_make_utf8_duh("Can't get new table from remaining tables.");

// identify how many rows we're going to process in this run
if( isset( $_GET[ 'interval' ] ))
	$interval = (int) $_GET[ 'interval' ] ;

if( empty( $interval ) ){
	// get the table info, check the avg row size, use a big interval for small rows
	if( ( $t_info = $wpdb->get_row( "show table status  LIKE '$t'" )) && ( $t_info->Data_length / $t_info->Rows < 100 ))
		$interval = 1000;
	else
		$interval = 100;
}

// identify where in that table we're working
if( !isset( $_GET[ 'n' ] ) && !empty( $t )) {
	$n = 0;

	// if we're just getting started, create a copy of the structure, make it utf8
	$wpdb->get_results("CREATE TABLE `conv_$t` LIKE `$t`");
	$wpdb->get_results("ALTER TABLE `conv_$t` DEFAULT CHARACTER SET=utf8");

	// make sure that table got created
	if(!$junk = $wpdb->get_var("DESCRIBE `conv_$t`"))
		bsuite_make_utf8_duh("Couldn't make conversion table in DB.");

} else if( !empty( $t ) ) {
	$n = (int) $_GET[ 'n' ] ;
} else {
	bsuite_make_utf8_duh("No current table specified.");
}

// now let's work a table, 
// get some rows as latin1, then put em in the copy table as utf8.
$wpdb->get_results('SET NAMES "latin1"'); // latin1 to get the data
if($get = $wpdb->get_results("SELECT * FROM $t LIMIT $n,$interval", ARRAY_A)){
	$put = array();
	foreach($get as $row){
		$row = array_map('bsuite_make_utf8_clean', $row);
		$put[] = implode( $row , ',');
	}
	$wpdb->get_results('SET NAMES "utf8"'); // utf8 to put it in
	$wpdb->get_results("INSERT IGNORE INTO `conv_$t` \nVALUES(". implode($put, "),\n(") .")");

	echo "<h2>Working on <code>$t</code> street, $n block.</h2><p>Attempted to insert ". count( $put ) ." rows, starting with #$n. $wpdb->rows_affected rows affected.</p>";

	if($wpdb->rows_affected <> count( $get ))
		echo("<h2>Alert: number of rows attempted does not match actual rows affected.</h2>");

	if( $interval == count( $get ))
		bsuite_make_utf8_refresh(array('t' => $t, 'n' => $n + $interval, 'interval' => $interval, 't_rem' => implode($t_rem, ',')));
	else if( count( $t_rem ) > 1)
		bsuite_make_utf8_refresh(array('t_rem' => implode($t_rem, ',')));
	else
		bsuite_make_utf8_duh("No more tables to process, looks like we're done.");
}else if( count( $t_rem ) > 1){
	bsuite_make_utf8_refresh(array('t_rem' => implode($t_rem, ',')));
}else{
	bsuite_make_utf8_duh("No more tables to process, looks like we're done. YOU MUST DELETE THIS FILE NOW.");
}

?>