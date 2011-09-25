<?php

class Widget_FB_Activity extends WP_Widget
{

	function Widget_FB_Activity()
	{
		$widget_ops = array('classname' => 'widget_fb_activity', 'description' => __( 'Displays Facebook activity for this domain') );
		$this->WP_Widget('fb_activity', __('Facebook Activity'), $widget_ops);
	}

	function widget( $args, $instance )
	{
		extract( $args );


		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'] );

		echo $before_widget . $before_title . $title . $after_title;
?>
		<fb:activity width="300" height="270" header="false" font="segoe ui" border_color="#fff" recommendations="true"></fb:activity>
<?php
		echo $after_widget;
	}

	function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = wp_filter_nohtml_kses( $new_instance['title'] );

		return $instance;
	}

	function form( $instance )
	{
		//Defaults
		$instance = wp_parse_args( (array) $instance, 
			array( 
				'title' => 'Recent Activity', 
			)
		);

		$title = esc_attr( $instance['title'] );
?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
<?php
	}
}// end Widget_FB_Activity


class Widget_FB_Like extends WP_Widget
{

	function Widget_FB_Like()
	{
		$widget_ops = array('classname' => 'widget_fb_like', 'description' => __( 'Displays a Facebook like button and facepile') );
		$this->WP_Widget('fb_like', __('Facebook Like'), $widget_ops);
	}

	function widget( $args, $instance )
	{
		extract( $args );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'] );
		
		echo $before_widget . $before_title . $title . $after_title;
?>
		<span id="fb_activity_like">
			<fb:like ref="top_activity" width="50" show_faces="false" send="false" layout="box_count" href="<?php echo trailingslashit( site_url() ); ?>" font="segoe ui"></fb:like>
			<fb:facepile href="<?php echo trailingslashit( site_url() ); ?>" width="225" max_rows="1"  font="segoe ui"></fb:facepile>
		</span>
<?php
		echo $after_widget;
	}

	function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = wp_filter_nohtml_kses( $new_instance['title'] );

		return $instance;
	}

	function form( $instance )
	{
		//Defaults
		$instance = wp_parse_args( (array) $instance, 
			array( 
				'title' => 'Find Us On Facebook', 
			)
		);

		$title = esc_attr( $instance['title'] );
?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
<?php
	}
}// end Widget_FB_Like



// register these widgets
function fb_widgets_init()
{
	register_widget( 'Widget_FB_Activity' );
	register_widget( 'Widget_FB_Like' );
}
add_action( 'widgets_init' , 'fb_widgets_init', 1 );
