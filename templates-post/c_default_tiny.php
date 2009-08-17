<?php
/*
Template Name: Default Excerpt Tiny
*/
?>

<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
	<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
	<small><?php the_time('F jS, Y') ?></small>

	<div class="entry excerpt">
		<?php the_excerpt(); ?>
	</div>
</div>