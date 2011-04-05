<?php

class Widget_FB_Activity extends WP_Widget
{

	function Go_Widget_FB_Activity()
	{
		$widget_ops = array('classname' => 'widget_fb_activity', 'description' => __( 'Displays Facebook activity for this domain') );
		$this->WP_Widget('fb_activity', __('GO Facebook Activity'), $widget_ops);
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
}// end Go_Widget_FB_Activity


// register these widgets
function fb_widgets_init()
{
	register_widget( 'Widget_FB_Activity' );
}
add_action( 'widgets_init' , 'fb_widgets_init', 1 );
