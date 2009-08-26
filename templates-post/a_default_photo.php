<?php
/*
Template Name: 9spot Photo
*/

global $post;
?>

<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
	<div class="entry">
		<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
		<?php the_content('Read the rest of this page &raquo;'); ?>
	</div>
</div>