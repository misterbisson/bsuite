<?php
/*  bstat_reports.php

	This file displays the report/status screens for bSuite bStat


	Copyright 2005 - 2007  Casey Bisson

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
<table><tr valign='top'><td><h4>Today's Page Loads</h4><ul><?php echo $wpdb->get_var("SELECT FORMAT(SUM(hit_count), 0) FROM $this->hits_targets WHERE hit_date = CURDATE() AND object_type IN (0,1)"); ?></ul>

<h4>Total Page Loads</h4><ul><?php echo $wpdb->get_var("SELECT FORMAT(SUM(hit_count), 0) FROM $this->hits_targets WHERE object_type IN (0,1)"); ?></ul>

<h4>Avg Daily Loads</h4><ul><?php echo $wpdb->get_var("SELECT FORMAT((SUM(hit_count)/ ((TO_DAYS(CURDATE()) - TO_DAYS(MIN(hit_date))) + 1)), 0) FROM $this->hits_targets WHERE hit_date > '$date' AND object_type IN (0,1)"); ?></ul>

<h4>Today's Prediction</h4><ul><?php echo $wpdb->get_var("SELECT FORMAT(SUM(hit_count) * (86400/TIME_TO_SEC(TIME(NOW()))), 0) FROM $this->hits_targets WHERE hit_date = CURDATE() AND object_type IN (0,1)"); ?></ul></td>

<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>

<?php $rows = $wpdb->get_results("SELECT FORMAT(SUM(hit_count), 0) AS hit_count, hit_date FROM $this->hits_targets WHERE hit_date < CURDATE() AND object_type IN (0,1) GROUP BY hit_date ORDER BY hit_date DESC LIMIT $best_num"); ?>

<td><h4>Previous <?php echo $best_num; ?> Days</h4><ul><?php foreach($rows as $row){ echo '<li>'. $row->hit_count .' ('. $row->hit_date .')</li>'; } ?></ul></td>

<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>

<?php $rows = $wpdb->get_results("SELECT FORMAT(SUM(hit_count), 0) AS hit_count, SUM(hit_count) AS sort_order, hit_date FROM $this->hits_targets WHERE hit_date < CURDATE() AND object_type IN (0,1) GROUP BY hit_date ORDER BY sort_order DESC LIMIT $best_num"); ?>

<td><h4>Best <?php echo $best_num; ?> Days</h4><ul><?php foreach($rows as $row){ echo '<li>'. $row->hit_count .' ('. $row->hit_date .')</li>'; } ?></ul></td>

</tr></table>

<strong>Complied:</strong> <?php echo date('F j, Y, g:i a'); ?> | <strong>System Load Average:</strong> <?php echo $bsuite->get_loadavg(); ?>

</div>

