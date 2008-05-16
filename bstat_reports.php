<?php
/*  bstat_reports.php

	This file displays the report/status screens for bSuite bStat


	Copyright 2004 - 2008  Casey Bisson

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
require_once (ABSPATH . WPINC . '/rss.php');
?>

<div class="wrap">
<h2><?php _e('Quick Stats') ?></h2>
<?php

$best_num = 10;
$detail_lines = 25;
$bstat_period = 90;
$date  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d") - $bstat_period, date("Y")));

?>
<table><tr valign='top'>
<td><h4>Today's Page Loads</h4><ul><?php echo $wpdb->get_var("SELECT FORMAT(SUM(hit_count), 0) FROM $this->hits_targets WHERE hit_date = CURDATE() AND object_type IN (0,1)"); ?></ul></td>
<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>

<td><h4>Avg Daily Loads</h4><ul><?php echo $wpdb->get_var("SELECT FORMAT((SUM(hit_count)/ ((TO_DAYS(CURDATE()) - TO_DAYS(MIN(hit_date))) + 1)), 0) FROM $this->hits_targets WHERE hit_date > '$date' AND object_type IN (0,1)"); ?></ul></td>
<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>

<td><h4>Today's Prediction</h4><ul><?php echo $wpdb->get_var("SELECT FORMAT(SUM(hit_count) * (86400/TIME_TO_SEC(TIME(NOW()))), 0) FROM $this->hits_targets WHERE hit_date = CURDATE() AND object_type IN (0,1)"); ?></ul></td>
<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>

<td><h4>Total Page Loads</h4><ul><?php echo $wpdb->get_var("SELECT FORMAT(SUM(hit_count), 0) FROM $this->hits_targets WHERE object_type IN (0,1)"); ?></ul></td>
</tr></table>
<?php

$dates = $wpdb->get_col( "SELECT sess_date
	FROM (
		SELECT sess_id, sess_date AS sess_timestamp, DATE(sess_date) AS sess_date, HOUR(sess_date) AS sess_hour
		FROM $this->hits_sessions
		ORDER BY sess_id DESC
		LIMIT 7500
	) a
	WHERE sess_timestamp >= DATE_SUB( NOW(), INTERVAL 1 DAY )
	GROUP BY sess_date, sess_hour" );

$hours = $wpdb->get_col( "SELECT sess_hour
	FROM (
		SELECT sess_id, sess_date AS sess_timestamp, DATE(sess_date) AS sess_date, HOUR(sess_date) AS sess_hour
		FROM $this->hits_sessions
		ORDER BY sess_id DESC
		LIMIT 7500
	) a
	WHERE sess_timestamp >= DATE_SUB( NOW(), INTERVAL 1 DAY )
	GROUP BY sess_date, sess_hour" );

$sessions = $wpdb->get_col( "SELECT COUNT(*) AS hit_count
	FROM (
		SELECT sess_id, sess_date AS sess_timestamp, DATE(sess_date) AS sess_date, HOUR(sess_date) AS sess_hour
		FROM $this->hits_sessions
		ORDER BY sess_id DESC
		LIMIT 7500
	) a
	WHERE sess_timestamp >= DATE_SUB( NOW(), INTERVAL 1 DAY )
	GROUP BY sess_date, sess_hour" );

$pageloads = $wpdb->get_col( "SELECT COUNT(*) AS hit_count
	FROM (
		SELECT sess_id, sess_date, sess_hour
		FROM (
			SELECT sess_id, sess_date AS sess_timestamp, DATE(sess_date) AS sess_date, HOUR(sess_date) AS sess_hour
			FROM $this->hits_sessions
			ORDER BY sess_id DESC
			LIMIT 7500
		) a
		WHERE sess_timestamp >= DATE_SUB( NOW(), INTERVAL 1 DAY )
	) s
	LEFT JOIN $this->hits_shistory h ON h.sess_id = s.sess_id
	WHERE h.object_type IN (0,1)
	GROUP BY sess_date, sess_hour" );
?>
<h4><?php echo number_format( array_sum( $pageloads )) ?> page loads and <?php echo number_format( array_sum( $sessions )) ?> unique visitors in last 24 hours:</h4>
<?php
echo '<img src="http://chart.apis.google.com/chart?chs=550x150&cht=lc&chco=0077CC&chm=B,E6F2FA,0,0,0&chls=1,0,0&chds='. min( $sessions ) .','. max( $pageloads ) .'&chd=t:'. implode( $sessions, ',' ) .'|'. implode( $pageloads, ',' ) .'&chxt=x,x,y&chxl=0:|'. implode( $hours, '|' ) .'|1:| |'. $dates[0] .'| | | | | | | | | |'. array_pop( $dates ) .'| |2:|'. min( $sessions ) .'|'. max( $pageloads ) .'" width="550" height="150" alt="Graph of pageloads and unique visitors in last 24 hours.">';


$months = $wpdb->get_col( "SELECT DATE_FORMAT( MAKEDATE( YEAR( hit_date ) ,DAYOFYEAR( hit_date ) ) , '%M' )
	FROM (
		SELECT SUM(hit_count) AS hit_count, hit_date
		FROM $this->hits_targets
		GROUP BY hit_date DESC
		LIMIT 31
	) a" );

$days = $wpdb->get_col( "SELECT DAY( hit_date )
	FROM ( 
		SELECT SUM(hit_count) AS hit_count, hit_date
		FROM $this->hits_targets
		GROUP BY hit_date DESC
		LIMIT 31
	) a" );

$pageloads = $wpdb->get_col( "SELECT hit_count
	FROM ( 
		SELECT SUM(hit_count) AS hit_count, hit_date
		FROM $this->hits_targets
		GROUP BY hit_date DESC
		LIMIT 31
	) a" );
?>
<h4><?php echo number_format( array_sum( $pageloads )) ?> page loads in last month:</h4>
<?php
echo '<img src="http://chart.apis.google.com/chart?chs=550x150&cht=lc&chco=0077CC&chm=B,E6F2FA,0,0,0&chls=1,0,0&chds='. min( $pageloads ) .','. max( $pageloads ) .'&chd=t:'. implode( array_reverse( $pageloads ), ',' ) .'&chxt=x,x,y&chxl=0:|'. implode( array_reverse( $days ), '|' ) .'|1:| |'. array_pop( $months ) .'| | | | | | | | | |'. $months[0] .'| |2:|'. min( $pageloads ) .'|'. max( $pageloads ) .'" width="550" height="150" alt="Graph of pageloads in last 31 days.">';
?>

<h4>&nbsp;</h4>
<strong>Complied:</strong><?php echo date('F j, Y, g:i a'); ?> | <strong>System Load Average:</strong> <?php echo $bsuite->get_loadavg(); ?>

<h4>&nbsp;</h4>

</div>
<div class="wrap">
<h2><?php _e('Page Loads') ?></h2>
<table><tr valign='top'><td width="33%"><h4>Most Reads</h4><ol>
<?php

$request = "SELECT object_id, object_type, FORMAT(SUM(hit_count), 0) AS hit_count, FORMAT((SUM(hit_count)) / ((TO_DAYS(NOW()) - TO_DAYS(MIN(hit_date))) + 1), 0) AS hit_avg, FORMAT(MAX(hit_count), 0) AS hit_max, SUM(hit_count) AS sort_order 
	FROM $this->hits_targets
	WHERE 1=1
	AND object_type IN (0,1)
	GROUP BY object_id
	ORDER BY sort_order DESC
	LIMIT $detail_lines";
$result = $wpdb->get_results($request, ARRAY_A);

if(!empty($result)){
	foreach($result as $post){
		if( 0 == $post['object_type'] )
			echo '<li><a href="'. get_permalink($post['object_id']) .'">'. wordwrap( get_the_title($post['object_id']), 35, "\n", TRUE ).'</a><br><small>Tot: '. $post['hit_count'] .' Avg: '. $post['hit_avg'] .' Max: '. $post['hit_max'] ."</small></li>\n";
		else
			echo '<li><a href="'. $this->bstat_get_term($post['object_id']) .'">'. wordwrap( $this->bstat_get_term($post['object_id']), 35, "\n", TRUE ).'</a><br><small>Tot: '. $post['hit_count'] .' Avg: '. $post['hit_avg'] .' Max: '. $post['hit_max'] ."</small></li>\n";
	}
}else{
	echo '<li>No Data Yet.</li>';
}
?>
</ol></td>

<?php
$posts = $wpdb->get_results("SELECT object_id, object_type, AVG(hit_count) AS hit_avg
		FROM $this->hits_targets
		WHERE hit_date >= DATE_SUB(CURDATE(),INTERVAL 30 DAY)
		AND object_type IN (0,1)
		GROUP BY object_id
		ORDER BY object_id ASC", ARRAY_A);

$avg = array();
foreach($posts as $post)
	$avg[$post['object_type'] .'_'. $post['object_id']] = $post['hit_avg'];


$posts = $wpdb->get_results("SELECT object_id, object_type, hit_count * (86400/TIME_TO_SEC(TIME(NOW()))) AS hit_now
		FROM $this->hits_targets
		WHERE hit_date = CURDATE()
		AND object_type IN (0,1)
		ORDER BY object_id ASC;", ARRAY_A);
$now = array();
foreach($posts as $post)
	$now[$post['object_type'] .'_'. $post['object_id']] = $post['hit_now'];

$diff = array();
foreach($posts as $post)
	$diff[$post['object_type'] .'_'. $post['object_id']] = intval(($now[$post['object_type'] .'_'. $post['object_id']] - $avg[$post['object_type'] .'_'. $post['object_id']]) * 1000 );

$win = count(array_filter($diff, create_function('$a', 'if($a > 0) return(TRUE);')));
$lose = count($diff) - $win;

$sort = array_flip($diff);
ksort($sort);
?>

<td width="33%"><h4>Top Climbers<?php if($win) echo " ($win)" ?></h4><ol>
<?php

if(!empty($sort)){
	foreach(array_slice(array_reverse($sort), 0, $detail_lines) as $object){
		$post = explode('_', $object);
		if( 0 == $post[0] )
			echo '<li><a href="'. get_permalink($post[1]) .'">'. wordwrap( get_the_title($post[1]), 35, "\n", TRUE ) .'</a><br><small>Up: '. number_format($diff[$object] / 1000, 0) .' Avg: '. number_format($avg[$object], 0) .' Today: '. number_format($now[$object], 0) ."</small></li>\n";
		else
			echo '<li><a href="'. $this->bstat_get_term($post[1]) .'">'. wordwrap( $this->bstat_get_term($post[1]), 35, "\n", TRUE ) .'</a><br><small>Up: '. number_format($diff[$object] / 1000, 0) .' Avg: '. number_format($avg[$object], 0) .' Today: '. number_format($now[$object], 0) ."</small></li>\n";
	}
}else{
	echo '<li>No Data Yet.</li>';
}
?>
</ol></td>

<td width="33%"><h4>Biggest Losers<?php if($lose) echo " ($lose)" ?></h4><ol>
<?php

if(!empty($sort)){
	foreach(array_slice($sort, 0, $detail_lines) as $object){
		$post = explode('_', $object);
		if( 0 == $post[0] )
			echo '<li><a href="'. get_permalink($post[1]) .'">'. wordwrap( get_the_title($post[1]), 35, "\n", TRUE ) .'</a><br><small>Down: '. number_format($diff[$object] / 1000, 0) .' Avg: '. number_format($avg[$object], 0) .' Today: '. number_format($now[$object], 0) ."</small></li>\n";
		else
			echo '<li><a href="'. $this->bstat_get_term($post[1]) .'">'. wordwrap( $this->bstat_get_term($post[1]), 35, "\n", TRUE ) .'</a><br><small>Down: '. number_format($diff[$object] / 1000, 0) .' Avg: '. number_format($avg[$object], 0) .' Today: '. number_format($now[$object], 0) ."</small></li>\n";
	}
}else{
	echo '<li>No Data Yet.</li>';
}
?>
</ol></td></tr></table>

<p><strong>Note on climbers and losers:</strong> values for "today" are predicted totals for the day based on current data. They should not be mistaken to represent the actual number of page loads in a day, as they will fluctuate throughout the day.</p>

</div>

<div class="wrap">
<h2><?php _e('Referrers') ?></h2>

<table><tr valign='top'><td width="33%"><h4>Incoming Search Terms</h4><ol>
<?php
//
// Incoming Search Terms
//

$refs = $this->pop_refs("count=$detail_lines&days=$bstat_period");
if(!empty($refs))
	echo $refs;
else
	echo '<li>No Data Yet.</li>';
?>
</ol></td>

<?php
//
// Referrers from Google Blog Search
//
$rss_feed = 'http://blogsearch.google.com/blogsearch_feeds?hl=en&scoring=d&ie=utf-8&num='. $detail_lines .'&output=rss&partner=bsuite&q=link:' . trailingslashit( get_option('home') );
$more_link = apply_filters( 'dashboard_incoming_links_link', 'http://blogsearch.google.com/blogsearch?hl=en&scoring=d&partner=bsuite&q=link:' . trailingslashit( get_option('home') ) );

echo '<td width="33%"><h4>Referrers from <a href="'. htmlspecialchars( $more_link ) .'">Google Blog Search</a></h4><ol>';

$rss = @fetch_rss( $rss_feed );
if ( isset($rss->items) && 1 < count($rss->items) ) {
	$rss->items = array_slice($rss->items, 0, $detail_lines);
	foreach ($rss->items as $item ) {
?>
		<li><a href="<?php echo wp_filter_kses($item['link']); ?>"><?php echo wptexturize(wp_specialchars($item['title'])); ?></a></li>
<?php
	}
}else{
	echo '<li>No Data Yet.</li>';
}
?>
</ol></td>

<?php
//
// Referrers from Technorati
//
$rss_feed = 'http://feeds.technorati.com/cosmos/rss/?url='. trailingslashit(get_option('home')) .'&partner=bsuite';
$more_link = 'http://www.technorati.com/cosmos/search.html?url=' . urlencode(trailingslashit( get_option('home') ) ) .'&partner=bsuite';

echo '<td width="33%"><h4>Referrers from <a href="'. htmlspecialchars( $more_link ) .'">Technorati</a></h4><ol>';

$rss = @fetch_rss( $rss_feed );
if ( isset($rss->items) && 1 < count($rss->items) ) {
	$rss->items = array_slice($rss->items, 0, $detail_lines);
	foreach ($rss->items as $item ) {
?>
		<li><a href="<?php echo wp_filter_kses($item['link']); ?>"><?php echo wptexturize(wp_specialchars($item['title'])); ?></a></li>
<?php
	}
}else{
	echo '<li>No Data Yet.</li>';
}
?>
</ol></td></tr></table>
</div>