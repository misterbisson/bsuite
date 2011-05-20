<?php
/*
Plugin Name: Facebook JS API
Plugin URI: 
Description: Includes the Facebook JS API,
Author: Casey Bisson
Author URI: http://MaisonBisson.com/
Version: 1.1
Text Domain: opengraph
*/

define('FBJS_NS_URI', 'http://www.facebook.com/2008/fbml');
$fbjs_ns_set = false;


function fbjs_opengraph_metadata( $properties )
{
	$properties['fb:admins'] = FBJS_ADMINS;
	$properties['fb:app_id'] = FBJS_APP_ID; 

	return $properties;
}
add_filter( 'opengraph_metadata' , 'fbjs_opengraph_metadata' );

function fbjs_add_namespace( $output )
{
	global $fbjs_ns_set;
	$fbjs_ns_set = true;

	$output .= ' xmlns:fb="' . esc_attr( FBJS_NS_URI ) . '"';

	return $output;
}
add_filter( 'language_attributes' , 'fbjs_add_namespace' );

function fbjs_include_js( $output )
{
	global $post;
?>
	<div id="fb-root"></div>
	<script>
		window.fbAsyncInit = function() {
			FB.init({appId: <?php echo FBJS_APP_ID; ?>, status: true, cookie: true, xfbml: true});
		};

		var e = document.createElement('script');
		e.type = 'text/javascript';
		e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
		e.async = true;
		document.getElementById('fb-root').appendChild(e);
	</script>
<?php
}
add_action( 'get_footer' , 'fbjs_include_js' );

function fbjs_add_like_button( $content )
{
	global $post;

	$button = '<p><fb:like href="'. get_permalink( $post->ID ) .'"></fb:like></p>';

	return $button . $content . $button;
}
add_filter( 'the_content' , 'fbjs_add_like_button' );
