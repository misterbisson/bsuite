<?php
/*  bsuite_admin.php

	This file displays the administration/options screens


	Copyright 2005 - 2006  Casey Bisson

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

//echo 'wp-content/plugins/'.plugin_basename(dirname(__FILE__));
//phpinfo();

//  apply new settings if form submitted
if($_POST['Options'] == __('Re-initialize Tables', 'bsuite')){
	$results = $wpdb->get_results("DROP TABLE $this->search_table;");
	$results = $wpdb->get_results("DROP TABLE $this->cache_table;");
	$this->createtables();
	
	echo '<div class="updated"><p><strong>' . __('bSuite tables deleted and re-initialized.', 'bsuite') . '</strong></p></div>';

}else if($_REQUEST['Options'] == __('Rebuild bsuite metadata index', 'bsuite')){		
	echo '<div class="updated"><p><strong>' . __('Rebuilding bsuite metadata index.', 'bsuite') . '</strong></p>';
	$this->rebuildmetatables();
	echo '</div>';
}

//  output settings/configuration form
?>

<div class="wrap">
<h2><?php _e('Commands') ?></h2>
<form method="post">

<fieldset name="bsuite_general" class="options">
	<table width="100%" cellspacing="2" cellpadding="5" class="editform">
		<tr valign="top">
			<div class="submit"><input type="submit" name="Options" value="<?php _e('Re-initialize Tables', 'bsuite') ?>" /> &nbsp; 
			<input type="submit" name="Options" value="<?php _e('Rebuild bsuite metadata index', 'bsuite') ?>" /></div>
		</tr>
	</table>
</fieldset>

</form>
</div>