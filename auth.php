<pre>
<?php

error_reporting(0);

if(!$wpdb){
	require_once("../../../wp-config.php");
}

$post_id = preg_replace('/[^0-9]*/', '', $_GET['p']);


if($user_ID){
	$return['user']['name'] = wp_specialchars($user_identity, true);
	$return['user']['profile'] = get_option('siteurl') . '/wp-admin/profile.php';
	$return['user']['logout'] = get_option('siteurl') . '/wp-login.php?action=logout&amp;redirect_to=' . get_permalink($post_id);
	$return['user']['message'] = __('Logged in as <a href="'. $return['user']['profile'] .'" title="Logged in as '. $return['user']['name'] .'">'. $return['user']['name'] .'</a>. <a href="'. $return['user']['logout'] .'" title="Log out of this account">Log out?</a>', 'sandbox') ;

	if (current_user_can('edit_post', $post_id) ){
		$return['tools']['post_edit_url'] = get_settings('siteurl') . '/wp-admin/post.php?action=edit&amp;post=$post_id';
		$return['tools']['post_edit_link'] = '<a href="'. $return['tools']['post_edit_url'] .'">'. __('Edit this entry.', 'sandbox') .'</a>';

		$return['tools']['comment_edit_url'] = get_settings('siteurl') . '/wp-admin/post.php?action=editcomment&amp;comment=';
		$return['tools']['comment_edit_link'] = '<a href="'. $return['tools']['comment_edit_url'] .'">'. __('Edit this comment.', 'sandbox') .'</a>';

		$return['tools']['comment_del_url'] = get_settings('siteurl') . '/wp-admin/post.php?action=confirmdeletecomment&noredir=true&p='.$post_id .'&comment=';
		$return['tools']['comment_del_link'] = '<a href="'. $return['tools']['comment_del_url'] .'">'. __('Delete this comment.', 'sandbox') .'</a>';

		$return['tools']['comment_spam_url'] = get_settings('siteurl') . '/wp-admin/post.php?action=I_WANT_A_MARK_AS_SPAM_URL&noredir=true&p='.$post_id .'&comment=';
		$return['tools']['comment_spam_link'] = '<a href="'. $return['tools']['comment_spam_url'] .'">'. __('Mark comment as spam.', 'sandbox') .'</a>';

	}

}else{
	sanitize_comment_cookies();
	$temp = wp_get_current_commenter();
	$return['comment_author']['name'] = $temp['comment_author'];
	$return['comment_author']['email'] = $temp['comment_author_email'];
	$return['comment_author']['url'] = $temp['comment_author_url'];
}

print_r($return);
	
?>
