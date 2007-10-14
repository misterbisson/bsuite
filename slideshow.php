<?php
/*
Plugin Name: bSuite Slideshow
Plugin URI: http://maisonbisson.com/blog/bsuite/slideshow
Description: Animates a series of photos as a slideshow (requires <a href="http://maisonbisson.com/blog/bsuite/">bSuite v.3</a> or greater). Insert the following in your post content to create the slideshow (accepts any number of photos, seperate photo URLs with space or newline): <code>[[slideshow|height=375px|http://path/to/photo/1.jpg http://path/to/photo/2.jpg]]</code>
Version: .01
Author: Casey Bisson
Author URI: http://maisonbisson.com/blog/
*/

add_filter('bsuite_tokens', 'bsuite_slideshow_token_register');

function bsuite_slideshow_token_register($tokens){
	$tokens['slideshow'] = 'bsuite_slideshow_token_replace';
	return($tokens);
}

function bsuite_slideshow_token_replace($args){
	list($args, $images) = explode('|', $args, 2);
	$defaults = array(
		'height' => '357px', 'width' => '100%'
	);
	$args = wp_parse_args( $args, $defaults );

	$images = array_filter(preg_split('/[\s|\n|\r|\t]/', trim($images)));
	if(!is_array($images)) 
		return NULL;

	add_action('wp_footer', 'bsuite_slideshow_footer');
	$return = $script . '<div id="slideshow" style="width: '. $args['width'] .'; height: '. $args['height'] .'; overflow: hidden;">';	
	foreach($images as $image)
		$return .= '<img src="'. $image .'" alt="slideshow image" />';
	$return .= '</div>';
	return $return;
}

function bsuite_slideshow_footer(){
//wp_print_scripts( 'jquery' );
?>
<script src="/blog/javascript/jquery-1.2.1.min.js" type="text/javascript"></script>
<script type="text/javascript">
$.fn.slideshow = function(options) {
	var settings = {
		timeout: '2000',
		type: 'sequence'
	}
	if(options)
		$.extend(settings, options);
	
	this.css('position', 'relative');
	var slides = this.find('img').get();
	for ( var i = 0; i < slides.length; i++ ) {
		$(slides[i]).css('zIndex', slides.length - i).css('position', 'absolute').css('top', '0').css('left', '0');
	}
	if ( settings.type == 'sequence' ) {
		setTimeout(function(){
			$.slideshow.next(slides, settings, 1, 0);
		}, settings.timeout);
	}
	else if ( settings.type == 'random' ) {
		setTimeout(function(){
			do { current = Math.floor ( Math.random ( ) * ( slides.length ) ); } while ( current == 0 )
			$.slideshow.next(slides, settings, current, 0);
		}, settings.timeout);
	}
	else {
		alert('type must either be \'sequence\' or \'random\'');
	}
};
$.slideshow = function() {}
$.slideshow.next = function (slides, settings, current, last) {
	for (var i = 0; i < slides.length; i++) {
		$(slides[i]).css('display', 'none');
	}
	$(slides[last]).css('display', 'block').css('zIndex', '0');
	$(slides[current]).css('zIndex', '1').fadeIn('slow');
	
	if ( settings.type == 'sequence' ) {
		if ( ( current + 1 ) < slides.length ) {
			current = current + 1;
			last = current - 1;
		}
		else {
			current = 0;
			last = slides.length - 1;
		}
	}
	else if ( settings.type == 'random' ) {
		last = current;
		while (	current == last ) {
			current = Math.floor ( Math.random ( ) * ( slides.length ) );
		}
	}
	else {
		alert('type must either be \'sequence\' or \'random\'');
	}
	setTimeout((function(){$.slideshow.next(slides, settings, current, last);}), settings.timeout);
}</script>

</script><script type="text/javascript">
	$(document).ready(function() {
		$('#slideshow').slideshow({
			timeout: 1000,
			type: 'sequence'
		});
		});
</script>
<?php
}