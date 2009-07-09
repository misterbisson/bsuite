<?php
/*
Template Name: Default Title Only
*/
?>

<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
	<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a> <small><?php the_time('F jS, Y') ?></small></h2>
</div>