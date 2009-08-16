<?php

$bsuite_cms_widgets = TRUE;

/**
 * Pages widget class
 *
 */
class bSuite_Widget_Pages extends WP_Widget {

	function bSuite_Widget_Pages() {
		$widget_ops = array('classname' => 'widget_pages', 'description' => __( 'A buncha yo blog&#8217;s WordPress Pages') );
		$this->WP_Widget('pages', __('Pages'), $widget_ops);

/*
		$widget_ops = array('classname' => 'bsuite_widget_pages', 'description' => __( 'A buncha yo blog&#8217;s WordPress Pages') );
		$this->WP_Widget('bsuite_pages', __('bSuite Pages'), $widget_ops);
*/
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Pages' ) : $instance['title']);
		$sortby = empty( $instance['sortby'] ) ? 'menu_order' : $instance['sortby'];
		$exclude = empty( $instance['exclude'] ) ? '' : $instance['exclude'];
		$depth = isset( $instance['depth'] ) ? $instance['depth'] : 1;

		if ( $sortby == 'menu_order' )
			$sortby = 'menu_order, post_title';

		$out = wp_list_pages( array('title_li' => '', 'echo' => 0, 'sort_column' => $sortby, 'exclude' => $exclude, 'depth' => $depth ));

		if( $instance['expandtree'] && is_page() ){
			global $post;

			// get the ancestor tree, including the current page
			$ancestors = $post->ancestors;
			$ancestors[] = $post->ID;
			$pages = get_pages( array( 'include' => implode( ',', $ancestors )));

			if ( !empty( $pages )){
				$subtree .= walk_page_tree( $pages, 0, $post->ID, array() );

				// get any children, insert them into the tree
				if( $children = wp_list_pages( array( 'child_of' => $post->ID, 'title_li' => '', 'echo' => 0, 'sort_column' => $sortby, 'exclude' => $exclude, 'depth' => $depth ))){
					$subtree = preg_replace( '/current_page_item[^<]*<a([^<]*)/i', 'current_page_item"><a\1<ul>'. $children .'</ul>', $subtree );
				}

				// insert this extended page tree into the larger list
				if( !empty( $subtree )){
					$out = preg_replace( '/<li[^>]*page-item-'. ( count( $post->ancestors ) ? end( $post->ancestors ) : $post->ID ) .'[^>]*.*?<\/li>.*?($|<li)/si', $subtree .'\1', $out );
					reset( $post->ancestors );
				}
			}
		}

		if ( !empty( $out ) ) {
			echo $before_widget;
			if ( $title)
				echo $before_title . $title . $after_title;
		?>
		<ul>
			<?php echo $out; ?>
		</ul>
		<?php
			echo $after_widget;
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		if ( in_array( $new_instance['sortby'], array( 'post_title', 'menu_order', 'ID' ))) {
			$instance['sortby'] = $new_instance['sortby'];
		} else {
			$instance['sortby'] = 'menu_order';
		}
		$instance['depth'] = absint( $new_instance['depth'] );
		$instance['expandtree'] = absint( $new_instance['expandtree'] );
		$instance['exclude'] = strip_tags( $new_instance['exclude'] );

		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'sortby' => 'post_title', 'title' => '', 'exclude' => '', 'depth' => 1, 'expandtree' => 1) );
		$title = esc_attr( $instance['title'] );
		$exclude = esc_attr( $instance['exclude'] );
	?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p>
			<label for="<?php echo $this->get_field_id('sortby'); ?>"><?php _e( 'Sort by:' ); ?></label>
			<select name="<?php echo $this->get_field_name('sortby'); ?>" id="<?php echo $this->get_field_id('sortby'); ?>" class="widefat">
				<option value="post_title"<?php selected( $instance['sortby'], 'post_title' ); ?>><?php _e('Page title'); ?></option>
				<option value="menu_order"<?php selected( $instance['sortby'], 'menu_order' ); ?>><?php _e('Page order'); ?></option>
				<option value="ID"<?php selected( $instance['sortby'], 'ID' ); ?>><?php _e( 'Page ID' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('depth'); ?>"><?php _e( 'Depth:' ); ?></label>
			<select name="<?php echo $this->get_field_name('depth'); ?>" id="<?php echo $this->get_field_id('depth'); ?>" class="widefat">
				<option value="1"<?php selected( $instance['depth'], '1' ); ?>><?php _e( '1' ); ?></option>
				<option value="2"<?php selected( $instance['depth'], '2' ); ?>><?php _e( '2' ); ?></option>
				<option value="3"<?php selected( $instance['depth'], '3' ); ?>><?php _e( '3' ); ?></option>
				<option value="4"<?php selected( $instance['depth'], '4' ); ?>><?php _e( '4' ); ?></option>
				<option value="0"<?php selected( $instance['depth'], '0' ); ?>><?php _e( 'All' ); ?></option>
			</select>
		</p>
		<p><input id="<?php echo $this->get_field_id('expandtree'); ?>" name="<?php echo $this->get_field_name('expandtree'); ?>" type="checkbox" value="1" <?php if ( $instance['expandtree'] ) echo 'checked="checked"'; ?>/>
		<label for="<?php echo $this->get_field_id('expandtree'); ?>"><?php _e('Expand current page tree?'); ?></label></p>
		<p>
			<label for="<?php echo $this->get_field_id('exclude'); ?>"><?php _e( 'Exclude:' ); ?></label> <input type="text" value="<?php echo $exclude; ?>" name="<?php echo $this->get_field_name('exclude'); ?>" id="<?php echo $this->get_field_id('exclude'); ?>" class="widefat" />
			<br />
			<small><?php _e( 'Page IDs, separated by commas.' ); ?></small>
		</p>
<?php
	}

}
function bsuite_widgets_init() {
	unregister_widget('WP_Widget_Pages');
	register_widget( 'bSuite_Widget_Pages' );
}

add_action('widgets_init', 'bsuite_widgets_init', 1);
