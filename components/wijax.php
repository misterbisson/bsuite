<?php
/**
 * Wijax class
 *
 */
class bSuite_Wijax {
	var $ep_name = 'wijax';

	function bSuite_Wijax()
	{
		global $bsuite;

		$this->path_web = is_object( $bsuite ) ? $bsuite->path_web : get_template_directory_uri();

		add_action( 'init', array( &$this, 'init' ));
		add_action( 'widgets_init', array( &$this , 'widgets_init' ) , 1 );
	}

	function init()
	{
		add_rewrite_endpoint( $this->ep_name , EP_ALL );
		add_filter( 'request' , array( &$this, 'request' ));

		if( ! is_admin())
		{
			// http://plugins.jquery.com/project/md5
			// wp_register_script( 'jquery-md5', $this->path_web . '/components/js/jquery.md5.js', array('jquery'), TRUE );
			// http://urldecoderonline.com/javascript-url-decode-jquery-plugin.htm
			// wp_register_script( 'jquery-urldecoder', $this->path_web . '/components/js/jquery.urldecoder.min.js', array('jquery'), TRUE );
			wp_register_script( 'wijax', $this->path_web . '/components/js/wijax-library.js', array('jquery'), TRUE );
			wp_enqueue_script( 'wijax' );
			add_filter( 'print_footer_scripts', array( &$this, 'print_js' ));
		}
	}

	function varname( $url = '' )
	{
		if( $url )
		{
			// trim the host component from the given url
			$home_path = parse_url( home_url() , PHP_URL_PATH );
			$home_host = str_replace( $home_path , '' , home_url() ); // easier to get the host by subtraction than reconstructing it from parse_url()
			$base = '/'. ltrim( str_replace( $home_host , '' , $url ) , '/' );
		}
		else
		{
			$base = $_SERVER['REQUEST_URI'];
		}
		return 'wijax_'. md5( $base );
	}

	function widgets_init()
	{
		register_widget( 'Wijax_Widget' );

		register_sidebar( array(
			'name' => __( 'Wijax Widgets', 'Bsuite' ),
			'id' => 'wijax-area',
			'description' => __( 'Place widgets here to configure them for lazy loading using the Wijax widget.', 'Bsuite' ),
		) );
	}

	public function request( $request )
	{
		if( isset( $request['wijax'] ))
		{
			add_filter( 'template_redirect' , array( &$this, 'redirect' ), 0 );
			define( 'IS_WIJAX' , TRUE );
			do_action( 'do_wijax' );
		}

		return $request;
	}

	function redirect()
	{
		global $wp_registered_widgets;

		$requested_widgets = array_filter( array_map( 'trim' , (array) explode( ',' , get_query_var('wijax') )));

		if( 1 > count( $requested_widgets ))
			return;

		foreach( $requested_widgets as $key )
		{
			if( ! $widget_data = $wp_registered_widgets[ $key ] )
				continue;

			preg_match( '/\-([0-9]+)$/' , $key , $instance_number );
			$instance_number = absint( $instance_number[1] );
			if( ! $instance_number )
				continue;

			$widget_data['widget'] = $key;
	
			$widget_data['params'][0] = array(
				'name' => $wp_registered_widgets[ $key ]['name'],
				'id' => $key,
				'before_widget' => '<span id="widget-%1$s" class="wijax-widgetclasses %2$s"></span>'."\n",
				'after_widget'  => '',
				'before_title'  => '<span class="wijax-widgettitle">',
				'after_title'   => "</span>\n",
				'widget_id' => $key,
				'widget_name' => $wp_registered_widgets[ $key ]['name'],
			);

//print_r( $widget_data['callback'][0]->number );

			$widget_data['params'][1] = array(
				'number' => absint( $instance_number ),
			);
	
			$widget_data['params'][0]['before_widget'] = sprintf($widget_data['params'][0]['before_widget'], $widget_data['widget'], ( isset( $widget_data['size'] ) ? 'grid_' . $widget_data['size'] .' ' : '' ) .$widget_data['class'] . ' ' . $widget_data['id'] . ' ' . $extra_classes);

			ob_start();			
			call_user_func_array( $widget_data['callback'], $widget_data['params'] );
			Wijax_Encode::out( ob_get_clean() , $this->varname() );

		}//end foreach
		die;
	}

	function print_js(){
?>
<script type="text/javascript">	
	;(function($){
		$(window).load(function(){
			$('a.wijax-source').each(function()
			{
				var widget_source = $(this).attr('href');
				var widget_area = $(this).parent();
				var widget_parent = $(this).parent().parent();
				var widget_wrapper = $(this).parents('.widget_wijax');
				var opts = $.parseJSON( $(widget_parent).find('span.wijax-opts').text() );
				var varname = opts.varname;
				$.getScript( widget_source , function() {
					// insert the fetched markup
					$( widget_area ).replaceWith( window[varname] );
			
					// find the widget title, add it to the DOM, remove the temp span
					var widget_title = $(widget_parent).find('span.wijax-widgettitle').text();
					$( widget_parent ).prepend('<'+opts.title_element+' class="'+ opts.title_class +'">'+ widget_title +'</'+opts.title_element+'>');
					$(widget_parent).find('span.wijax-widgettitle').remove();
			
					// find and set the widget ID and classes
					var widget_attr_el = $( widget_parent ).find( 'span.wijax-widgetclasses' );
					var widget_id = $( widget_attr_el ).attr( 'id' );
					var widget_classes = $( widget_attr_el ).attr( 'class' );
					$( widget_wrapper ).attr( 'id' , widget_id );
					$( widget_wrapper ).addClass( widget_classes );
					$( widget_wrapper ).removeClass( 'widget_wijax' );
					$(widget_attr_el).remove();
				});
			});
		});
	})(jQuery);
</script>
<?php
	}

} //end bSuite_Wijax

// initialize that class
$wijax = new bSuite_Wijax();



/**
 * Wijax widget class
 *
 */
class Wijax_Widget extends WP_Widget
{

	function Wijax_Widget()
	{
		$widget_ops = array('classname' => 'widget_wijax', 'description' => __( 'Lazy load widgets after DOMDocumentReady') );
		$this->WP_Widget('wijax', __('Wijax Widget Lazy Loader'), $widget_ops);

		add_filter( 'wijax-base-current' , array( $this , 'base_current' ) , 5 );
		add_filter( 'wijax-base-home' , array( $this , 'base_home' ) , 5 );
	}

	function widget( $args, $instance )
	{
		global $wijax;

		extract( $args );

		$base = apply_filters( 'wijax-base-'. $instance['base'] , '' );
		if( ! $base )
			return;
		$wijax_source = $base . $instance['widget'];

		echo $before_widget;

		preg_match( '/<([\S]*)/' , $before_title , $title_element );
		$title_element = (string) $title_element[1];


		preg_match( '/class.*?=.*?(\'|")(.+?)(\'|")/' , $before_title , $title_class );
		$title_class = (string) $title_class[2];
?>
		<span class="wijax-loading">
			<img src="<?php echo $wijax->path_web  .'/components/img/loading-gray.gif'; ?>" alt="loading external resource" />
			<a href="<?php echo $wijax_source; ?>" class="wijax-source"></a>
			<span class="wijax-opts" style="display: none;">
				<?php echo json_encode(array( 'varname' => $wijax->varname( $wijax_source ) ,  'title_element' => $title_element ,  'title_class' => $title_class )); ?>
			</span>
		</span>
<?php
		echo $after_widget;
	}

	function base_home()
	{

		return trailingslashit( home_url() ) .'wijax/';
	}

	function base_current()
	{

		$home_path = parse_url( home_url() , PHP_URL_PATH );
		return esc_url_raw( trailingslashit( home_url() . str_replace( $home_path , '' , $_SERVER['REQUEST_URI'] )) .'wijax/' );
	}

	function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['widget'] = sanitize_title( $new_instance['widget'] );
		$instance['widget-custom'] = esc_url_raw( $new_instance['widget-custom'] );
		$instance['base'] = sanitize_title( $new_instance['base'] );

		return $instance;
	}

	function form( $instance )
	{
		//Defaults
		$instance = wp_parse_args( (array) $instance, 
			array( 
				'title' => '', 
				'homelink' => get_option('blogname'),
				'maxchars' => 35,
			)
		);

		$title = esc_attr( $instance['title'] );
?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /><br />
			<small>For convenience, not shown publicly</small
		</p>
<?php
		echo $this->control_widgets( $instance );
		echo $this->control_base( $instance );
	}

	function control_widgets( $instance , $whichfield = 'widget' )
	{
		// get the available widgets
		$sidebars_widgets = wp_get_sidebars_widgets();
		$list = '';
		foreach( (array) $sidebars_widgets['wijax-area'] as $item )
		{
			if( $number == $this->number )
				continue;

			$list .= '<option value="'. $item .'" '. selected( $instance[ $whichfield ] , $item , FALSE ) .'>'. $item .'</option>';
		}
		$list .= '<option value="custom" '. selected( $instance[ $whichfield ] , 'custom' , FALSE ) .'>Custom</option>';

		return '<p><label for="'. $this->get_field_id( $whichfield ) .'">Widget</label><select name="'. $this->get_field_name( $whichfield ) .'" id="'. $this->get_field_id( $whichfield ) .'" class="widefat">'. $list . '</select></p><p><label for="'. $this->get_field_id( $whichfield .'-custom' ) .'">Custom Widget Path</label><input name="'. $this->get_field_name( $whichfield .'-custom' ) .'" id="'. $this->get_field_id( $whichfield .'-custom' ) .'" class="widefat" type="text" value="'. esc_url( $instance[ $whichfield .'-custom' ] ).'"></p>';
	}

	function control_base( $instance , $whichfield = 'base' )
	{

		$bases = apply_filters( 'wijax-bases' , array(
			'current' => 'The currently requested URL',
			'home' => 'The blog home URL',
		));

		foreach( (array) $bases as $k => $v )
			$list .= '<option value="'. $k .'" '. selected( $instance[ $whichfield ] , $k , FALSE ) .'>'. $v .'</option>';

		return '<p><label for="'. $this->get_field_id( $whichfield ) .'">Base URL</label><select name="'. $this->get_field_name( $whichfield ) .'" id="'. $this->get_field_id( $whichfield ) .'" class="widefat">'. $list . '</select><br /><small>The base URL affects widget content and caching</small></p>';
	}

}// end Wijax_Widget



class Wijax_Encode
{
	public static function out( $content , $varname )
	{
		header('Content-type: text/javascript');
		echo self::encode( $content , $varname );
	}//end out

	public static function encode( $content , $varname )
	{
		//create a variable to put the page content into
		$output='var varname = "'. $varname .'"; window[varname]='. json_encode( $content ) .";\n";

		return $output;
	}//end out
}//end class Channel


/*
jQuery('a.wijax-source').each(function()
{
	var widget_source = jQuery(this).attr('href');
	var widget_area = jQuery(this).parent();
	var widget_parent = jQuery(this).parent().parent();
	var widget_wrapper = jQuery(this).parents('.widget_wijax');
	var opts = jQuery.parseJSON( jQuery(widget_parent).find('span.wijax-opts').text() );
	var varname = opts.varname;
	jQuery.getScript( widget_source , function() {
		// insert the fetched markup
		jQuery( widget_area ).replaceWith( window[varname] );

		// find the widget title, add it to the DOM, remove the temp span
		var widget_title = jQuery(widget_parent).find('span.wijax-widgettitle').text();
		jQuery( widget_parent ).prepend('<'+opts.title_element+' class="'+ opts.title_class +'">'+ widget_title +'</'+opts.title_element+'>');
		jQuery(widget_parent).find('span.wijax-widgettitle').remove();

		// find the widget classes & ID
		var widget_attr_el = jQuery( widget_parent ).find( 'span.wijax-widgetclasses' );
		var widget_id = jQuery( widget_attr_el ).attr( 'id' );
		var widget_classes = jQuery( widget_attr_el ).attr( 'class' );
		jQuery( widget_wrapper ).attr( 'id' , widget_id );
		jQuery( widget_wrapper ).addClass( widget_classes );
		jQuery( widget_wrapper ).removeClass( 'widget_wijax' );
		jQuery(widget_attr_el).remove();
	});
});
*/