<?php
/*
Template Name: 9spot Title+Thumbnail LI
Wrapper: TRUE
*/
?>

<li>
	<a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
		<span class="entry thumbnail"><?php the_post_thumbnail('thumbnail-small'); ?></span>
		<span class="entry title"><?php the_title(); ?></span>
	</a>
</li>