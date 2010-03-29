<?php

/**
 * PostLoops class
 *
 */
class bSuite_PostLoops {

	// instances
	var $instances;

	// posts matched by various instances of the widget
	var $posts; // $posts[ $loop_id ][ $blog_id ] = $post_id

	// terms from the posts in each instance
	var $tags; // $tags[ $loop_id ][ $blog_id ][ $term_taxonomy_id ] = $count

	function bSuite_PostLoops()
	{
		add_action( 'init', array( &$this, 'init' ));

		add_action( 'preprocess_comment' , array( &$this, 'preprocess_comment' ), 1 );
		add_action( 'bsuite_response_sendmessage' , array( &$this, 'sendmessage' ), 1, 2 );
	}

	function init()
	{
		$this->get_instances();

		$this->get_templates( 'post' );
		$this->get_templates( 'response' );
	}

	function get_instances()
	{
		global $blog_id;

		$options = get_option( 'widget_postloop' );

		// add an entry for the default conent
		$options[-1] = array( 
			'title' => 'The default content',
			'blog' => absint( $blog_id ),
		);

		foreach( $options as $number => $option )
		{
			if( is_integer( $number ))
			{
				$option['title'] = empty( $option['title'] ) ? 'Instance #'. $number : wp_filter_nohtml_kses( $option['title'] );
				$this->instances[ $number ] = $option;
			}
		}

		return $this->instances;
	}

	function get_instances_response()
	{
		global $blog_id;

		$options = get_option( 'widget_responseloop' );

		// add an entry for the default conent
		$options[-1] = array( 
			'title' => 'The default content',
			'blog' => absint( $blog_id ),
		);

		foreach( $options as $number => $option )
		{
			if( is_integer( $number ))
			{
				$option['title'] = empty( $option['title'] ) ? 'Instance #'. $number : wp_filter_nohtml_kses( $option['title'] );
				$this->instances_response[ md5( (string) $number . $option['template'] . $option['email'] ) ] = $option;
			}
		}

		return $this->instances_response;
	}

	function get_templates_readdir( $template_base )
	{
		$page_templates = array();
		$template_dir = @ dir( $template_base );
		if ( $template_dir ) {
			while ( ( $file = $template_dir->read() ) !== false ) {
				if ( preg_match('|^\.+$|', $file) )
					continue;
				if ( preg_match('|\.php$|', $file) ) {
					$template_data = implode( '', file( $template_base . $file ));
	
					$name = '';
					if ( preg_match( '|Template Name:(.*)$|mi', $template_data, $name ) )
						if( function_exists( '_cleanup_header_comment' ))
							$name = _cleanup_header_comment($name[1]);
						else
							$name = $name[1];
	
					if ( !empty( $name ) ) {
						$file = basename( $file );
						$page_templates[ $file ]['name'] = trim( $name );
						$page_templates[ $file ]['file'] = basename( $file );
						$page_templates[ $file ]['fullpath'] = $template_base . $file;
					}
				}
			}
			@ $template_dir->close();
		}

		return $page_templates;
	}
	
	function get_templates( $type = 'post' )
	{
		$type = sanitize_file_name( $type );
		$type_var = "templates_$type";

		$this->$type_var = array_merge( 
				(array) $this->get_templates_readdir( dirname( dirname( __FILE__ )) .'/templates-'. $type .'/' ),
				(array) $this->get_templates_readdir( TEMPLATEPATH . '/templates-'. $type .'/' ), 
				(array) $this->get_templates_readdir( STYLESHEETPATH . '/templates-'. $type .'/' ) 
			);

		return $this->$type_var;
	}

	function preprocess_comment( $comment )
	{
		$this->get_instances_response();

		do_action(
			'bsuite_response_'. sanitize_title_with_dashes( preg_replace( '/\.[^\.]*$/' , '', $this->instances_response[ $_REQUEST['bsuite_responsekey'] ]['template'] )),
			$comment,
			$this->instances_response[ $_REQUEST['bsuite_responsekey'] ]
		);

		return( $comment );
	}

	function sendmessage( $comment , $input )
	{
		add_action( 'comment_post', array( &$this, '_sendmessage' ));
		add_filter( 'pre_comment_approved', create_function( '$a', 'return \'message\';'), 1 );
	}

	function _sendmessage( $comment_id , $approved )
	{
		if ( 'spam' == $approved )
			return;

		$also_notify = sanitize_email( $this->instances_response[ $_REQUEST['bsuite_responsekey'] ]['email'] );

		$comment = get_comment( $comment_id );
		$post    = get_post( $comment->comment_post_ID );
		$user    = get_userdata( $post->post_author );
		$current_user = wp_get_current_user();
	
		if(( '' == $also_notify ) && ('' == $user->user_email )) return false; // If there's no email to send the comment to
	
		$comment_author_domain = @gethostbyaddr( $comment->comment_author_IP );
	
		$blogname = get_option('blogname');
	
		/* translators: 1: post id, 2: post title */
		$notify_message  = sprintf( __('New message on %2$s (#%1$s)'), $comment->comment_post_ID, $post->post_title ) . "\r\n\r\n";
   		$notify_message .= $comment->comment_content . "\r\n\r\n";
		/* translators: 1: comment author, 2: author IP, 3: author domain */
		$notify_message .= sprintf( __('Author : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
		$notify_message .= sprintf( __('E-mail : %s'), $comment->comment_author_email ) . "\r\n";
		$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "\r\n";
		$notify_message .=  __('Network location:') . "\r\nhttp://ws.arin.net/cgi-bin/whois.pl?queryinput=$comment->comment_author_IP\r\n\r\n";
//		$notify_message .= __('You can see all messages on this post here: ') . "\r\n";
//		$notify_message .= admin_url( '/wp-admin/edit-comments.php?p='. $post->ID ) ."\r\n\r\n";

		/* translators: 1: blog name, 2: post title */
		$subject = sprintf( __('[%1$s] Message on "%2$s"'), $blogname, $post->post_title );
	
	
		$wp_email = 'wordpress@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
	
		if ( '' == $comment->comment_author )
		{
			$from = "From: \"$blogname\" <$wp_email>";
			if ( '' != $comment->comment_author_email )
				$reply_to = "Reply-To: $comment->comment_author_email";
		} else {
			$from = "From: \"$comment->comment_author\" <$wp_email>";
			if ( '' != $comment->comment_author_email )
				$reply_to = "Reply-To: \"$comment->comment_author_email\" <$comment->comment_author_email>";
		}
	
		$message_headers = "$from\n"
			. "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";
	
		if ( isset( $reply_to ))
			$message_headers .= $reply_to . "\n";
	
		$notify_message = apply_filters('comment_notification_text', $notify_message, $comment_id);
		$subject = apply_filters('comment_notification_subject', $subject, $comment_id);
		$message_headers = apply_filters('comment_notification_headers', $message_headers, $comment_id);

		if( '' <> $also_notify )
			@wp_mail( $also_notify , $subject , $notify_message , $message_headers );

		if( $user->user_email )
			@wp_mail( $user->user_email , $subject , $notify_message , $message_headers );

		die( wp_redirect( get_comment_link( $comment_id )));
	}

	function restore_current_blog()
	{
		if ( function_exists('restore_current_blog') )
			return restore_current_blog();
		return TRUE;
	}

} //end bSuite_PostLoops

// initialize that class
$postloops = new bSuite_PostLoops();


/**
 * PostLoop widget class
 *
 */
class bSuite_Widget_PostLoop extends WP_Widget {

	function bSuite_Widget_PostLoop() {
		$widget_ops = array('classname' => 'widget_postloop', 'description' => __( 'Build your own post loop') );
		$this->WP_Widget('postloop', __('Post Loop'), $widget_ops);

		global $postloops;
//		if( ! is_array( $postloops->templates_post ))
//			$postloops->get_templates( 'post' );

		$this->post_templates = &$postloops->templates_post;
	}

	function widget( $args, $instance ) {
		global $postloops;

		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? '' : $instance['title']);
	
		if( 'normal' == $instance['what'] ){
			wp_reset_query();
			global $wp_query;

			$ourposts = &$wp_query;

		}else{
			$criteria['suppress_filters'] = TRUE;
	
			if( in_array( $instance['what'], array( 'post', 'page', 'attachment' )))
				$criteria['post_type'] = $instance['what'];

			if( $instance['what'] == 'attachment' )
				$criteria['post_status'] = 'inherit';
	
			if( !empty( $instance['categories_in'] ))
				$criteria['category__'. ( in_array( $instance['categoriesbool'], array( 'in', 'and', 'not_in' )) ? $instance['categoriesbool'] : 'in' ) ] = array_keys( $instance['categories_in'] );

			if( !empty( $instance['categories_not_in'] ))
				$criteria['category__not_in'] = array_keys( $instance['categories_not_in'] );
	
			if( !empty( $instance['tags_in'] ))
				$criteria['tag__'. ( in_array( $instance['tagsbool'], array( 'in', 'and', 'not_in' )) ? $instance['tagsbool'] : 'in' ) ] = $instance['tags_in'];

			if( !empty( $instance['tags_not_in'] ))
				$criteria['tag__not_in'] = $instance['tags_not_in'];
	
			if( !empty( $instance['post__in'] ))
				$criteria['post__in'] = $instance['post__in'];
	
			if( !empty( $instance['post__not_in'] ))
				$criteria['post__not_in'] = $instance['post__not_in'];
	
			$criteria['showposts'] = $instance['count'];
	
			switch( $instance['order'] ){
				case 'age_new':
					$criteria['orderby'] = 'post_date';
					$criteria['order'] = 'DESC';
					break;
				case 'age_old':
					$criteria['orderby'] = 'post_date';
					$criteria['order'] = 'ASC';
					break;
				case 'pop_most':
				case 'pop_least':
				case 'comment_recent':
				case 'rand':
					$criteria['orderby'] = 'rand';
					break;
				default:
					$criteria['orderby'] = 'post_date';
					$criteria['order'] = 'DESC';
					break;
			}

			if( 0 < $instance['blog'] )
				switch_to_blog( $instance['blog'] ); // switch to the other blog

			$ourposts = new WP_Query( $criteria );

	/*
	$options[$widget_number]['activity'] = in_array( $widget_var['activity'], array( 'pop_most', 'pop_least', 'pop_recent', 'comment_recent', 'comment_few') ) ? $widget_var['activity']: '';
	
	$options[$widget_number]['age'] = in_array( $widget_var['age'], array( 'after', 'before', 'around') ) ? $widget_var['age']: '';
	$options[$widget_number]['agestrtotime'] = strtotime( $widget_var['agestrtotime'] ) ? $widget_var['agestrtotime'] : '';
	
	$options[$widget_number]['relationship'] = in_array( $widget_var['relationship'], array( 'similar', 'excluding') ) ? $widget_var['relationship']: '';
	$options[$widget_number]['relatedto'] = array_filter( array_map( 'absint', $widget_var['relatedto'] ));



		global $blog_id;

print_r( reset( $postloops->posts[ $instance_id ] ));

	*/
		}

		if( $ourposts->have_posts() ){
			$postloops->current_postloop = $instance;


			echo str_replace( 'class="widget ', 'class="widget widget-post_loop-'. sanitize_title_with_dashes( $instance['title'] ) .' ' , $before_widget );

			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'] );
			if ( $instance['title_show'] && $title )
				echo $before_title . $title . $after_title;

			while( $ourposts->have_posts() ){

				unset( $GLOBALS['pages'] ); // to address ticket: http://core.trac.wordpress.org/ticket/12651

				$ourposts->the_post();
				global $id, $post;

				$instance['blog'] = absint( $instance['blog'] );

				// get the matching post IDs for the $postloops object
				$postloops->posts[ $this->number ][ $instance['blog'] ][] = $id;

				$terms = get_object_term_cache( $id, (array) get_object_taxonomies( $post->post_type ) );
				if ( empty( $terms ))
					$terms = wp_get_object_terms( $id, (array) get_object_taxonomies( $post->post_type ) );

				// get the term taxonomy IDs for the $postloops object
				foreach( $terms as $term )
					$postloops->terms[ $this->number ][ $instance['blog'] ][ $term->term_taxonomy_id ]++;

				if( empty( $instance['template'] ) || !include $this->post_templates[ $instance['template'] ]['fullpath'] )
				{
?><!-- ERROR: the required template file is missing or unreadable. A default template is being used instead. -->
<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
	<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
	<small><?php the_time('F jS, Y') ?> <!-- by <?php the_author() ?> --></small>

	<div class="entry">
		<?php the_content('Read the rest of this entry &raquo;'); ?>
	</div>

	<p class="postmetadata"><?php the_tags('Tags: ', ', ', '<br />'); ?> Posted in <?php the_category(', ') ?> | <?php edit_post_link('Edit', '', ' | '); ?>  <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?></p>
</div>
<?php
				}
			}
			echo $after_widget;
		}

		$postloops->restore_current_blog();

		unset( $postloops->current_postloop );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = wp_filter_nohtml_kses( $new_instance['title'] );
		$instance['title_show'] = absint( $new_instance['title_show'] );
		$instance['what'] = in_array( $new_instance['what'], array( 'normal', 'post', 'page', 'attachment', 'any') ) ? $new_instance['what']: '';

		if( $this->control_blogs( $instance , FALSE , FALSE ))
		{
			$instance['blog'] = absint( $new_instance['blog'] );
			$instance['categoriesbool'] = in_array( $new_instance['categoriesbool'], array( 'in', 'and', 'not_in') ) ? $new_instance['categoriesbool']: '';
			$instance['categories_in'] = array_filter( array_map( 'absint', $new_instance['categories_in'] ));
			$instance['categories_not_in'] = array_filter( array_map( 'absint', $new_instance['categories_not_in'] ));
			$instance['tagsbool'] = in_array( $new_instance['tagsbool'], array( 'in', 'and', 'not_in') ) ? $new_instance['tagsbool']: '';
			$tag_name = '';
			$instance['tags_in'] = array();
			foreach( array_filter( array_map( 'trim', array_map( 'wp_filter_nohtml_kses', explode( ',', $new_instance['tags_in'] )))) as $tag_name ){
				if( $temp = is_term( $tag_name, 'post_tag' ))
					$instance['tags_in'][] = $temp['term_id'];
			}
			$tag_name = '';
			$instance['tags_not_in'] = array();
			foreach( array_filter( array_map( 'trim', array_map( 'wp_filter_nohtml_kses', explode( ',', $new_instance['tags_not_in'] )))) as $tag_name ){
				if( $temp = is_term( $tag_name, 'post_tag' ))
					$instance['tags_not_in'][] = $temp['term_id'];
			}
			$instance['post__in'] = array_filter( array_map( 'absint', explode( ',', $new_instance['post__in'] )));
			$instance['post__not_in'] = array_filter( array_map( 'absint', explode( ',', $new_instance['post__not_in'] )));
		}
		$instance['activity'] = in_array( $new_instance['activity'], array( 'pop_most', 'pop_least', 'pop_recent', 'comment_recent', 'comment_few') ) ? $new_instance['activity']: '';
		$instance['age'] = in_array( $new_instance['age'], array( 'after', 'before', 'around') ) ? $new_instance['age']: '';
		$instance['agestrtotime'] = strtotime( $new_instance['agestrtotime'] ) ? $new_instance['agestrtotime'] : '';
		$instance['relationship'] = in_array( $new_instance['relationship'], array( 'similar', 'excluding') ) ? $new_instance['relationship']: '';
		$instance['relatedto'] = array_filter( (array) array_map( 'absint', (array) $new_instance['relatedto'] ));
		$instance['count'] = absint( $new_instance['count'] );
		$instance['order'] = in_array( $new_instance['order'], array( 'age_new', 'age_old', 'pop_most', 'pop_least', 'relevance_most', 'comment_recent', 'rand' ) ) ? $new_instance['order']: '';
		$instance['template'] = wp_filter_nohtml_kses( $new_instance['template'] );
		$instance['columns'] = absint( $new_instance['columns'] );

		return $instance;
	}

	function form( $instance ) {
		global $blog_id, $postloops;
		//Defaults

		$instance = wp_parse_args( (array) $instance, 
			array( 
				'what' => 'normal', 
				'template' => 'a_default_full.php',
				'blog' => $blog_id,
				) 
			);

		$title = esc_attr( $instance['title'] );
	?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
			<label for="<?php echo $this->get_field_id( 'title_show' ) ?>"><input id="<?php echo $this->get_field_id( 'title_show' ) ?>" name="<?php echo $this->get_field_name( 'title_show' ) ?>" type="checkbox" value="1" <?php echo ( $instance[ 'title_show' ] ? 'checked="checked"' : '' ) ?>/> Show Title?</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('what'); ?>"><?php _e( 'What to show:' ); ?></label>
			<select name="<?php echo $this->get_field_name('what'); ?>" id="<?php echo $this->get_field_id('what'); ?>" class="widefat">
				<option value="normal" <?php selected( $instance['what'], 'normal' ); ?>><?php _e('The default content'); ?></option>
				<option value="post" <?php selected( $instance['what'], 'post' ); ?>><?php _e('Posts'); ?></option>
				<option value="page" <?php selected( $instance['what'], 'page' ); ?>><?php _e('Pages'); ?></option>
				<option value="attachment" <?php selected( $instance['what'], 'attachment' ); ?>><?php _e('Attachments'); ?></option>
				<option value="any" <?php selected( $instance['what'], 'any' ); ?>><?php _e('Any content'); ?></option>
			</select>
		</p>

<?php
		// from what blog?
		if( $this->control_blogs( $instance )):
?>

		<div id="<?php echo $this->get_field_id('categories_in'); ?>-container" class="container">
		<p id="<?php echo $this->get_field_id('categories_in'); ?>-contents" class="contents">
			<label for="<?php echo $this->get_field_id('categoriesbool'); ?>"><?php _e( 'Categories:' ); ?></label>
			<select name="<?php echo $this->get_field_name('categoriesbool'); ?>" id="<?php echo $this->get_field_id('categoriesbool'); ?>" class="widefat">
				<option value="in" <?php selected( $instance['categoriesbool'], 'in' ); ?>><?php _e('Any of these categories'); ?></option>
				<option value="and" <?php selected( $instance['categoriesbool'], 'and' ); ?>><?php _e('All of these categories'); ?></option>
			</select>
			<ul><?php echo $this->control_categories( $instance , 'categories_in' ); ?></ul>
		</p>
		</div>

		<div id="<?php echo $this->get_field_id('categories_not_in'); ?>-container" class="container">
		<p id="<?php echo $this->get_field_id('categories_not_in'); ?>-contents" class="contents">
			<label for="<?php echo $this->get_field_id('categories_not_in'); ?>"><?php _e( 'Not in any of these categories:' ); ?></label>
			<ul><?php echo $this->control_categories( $instance , 'categories_not_in' ); ?></ul>
		</p>
		</div>

		<div id="<?php echo $this->get_field_id('post_tag'); ?>-container" class="container">
		<p id="<?php echo $this->get_field_id('post_tag'); ?>-contents" class="contents">
			<label for="<?php echo $this->get_field_id('tagsbool'); ?>"><?php _e( 'Tags:' ); ?></label>
			<select name="<?php echo $this->get_field_name('tagsbool'); ?>" id="<?php echo $this->get_field_id('tagsbool'); ?>" class="widefat">
				<option value="in" <?php selected( $instance['tagsbool'], 'in' ); ?>><?php _e('Any of these tags'); ?></option>
				<option value="and" <?php selected( $instance['tagsbool'], 'and' ); ?>><?php _e('All of these tags'); ?></option>
			</select>

			<?php
			$tags_in = array();
			foreach( (array) $instance['tags_in'] as $tag_id ){
				$temp = get_term( $tag_id, 'post_tag' );
				$tags_in[] = $temp->name;
			}
			?>
			<input type="text" value="<?php echo implode( ', ', (array) $tags_in ); ?>" name="<?php echo $this->get_field_name('tags_in'); ?>" id="<?php echo $this->get_field_id('tags_in'); ?>" class="widefat" />
			<br />
			<small><?php _e( 'Tags, separated by commas.' ); ?></small>
		</p>
		</div>

		<div id="<?php echo $this->get_field_id('tags_not_in'); ?>-container" class="container">
		<p id="<?php echo $this->get_field_id('tags_not_in'); ?>-contents" class="contents">
			<label for="<?php echo $this->get_field_id('tags_not_in'); ?>"><?php _e( 'With none of these tags:' ); ?></label>
			<?php
			$tags_not_in = array();
			foreach( (array) $instance['tags_not_in'] as $tag_id ){
				$temp = get_term( $tag_id, 'post_tag' );
				$tags_not_in[] = $temp->name;
			}
			?>
			<input type="text" value="<?php echo implode( ', ', (array) $tags_not_in ); ?>" name="<?php echo $this->get_field_name('tags_not_in'); ?>" id="<?php echo $this->get_field_id('tags_not_in'); ?>" class="widefat" />
			<br />
			<small><?php _e( 'Tags, separated by commas.' ); ?></small>
		</p>
		</div>

		<div id="<?php echo $this->get_field_id('post__in'); ?>-container" class="container">
		<p id="<?php echo $this->get_field_id('post__in'); ?>-contents" class="contents">
			<label for="<?php echo $this->get_field_id('post__in'); ?>"><?php _e( 'Matching any post ID:' ); ?></label> <input type="text" value="<?php echo implode( ', ', (array) $instance['post__in'] ); ?>" name="<?php echo $this->get_field_name('post__in'); ?>" id="<?php echo $this->get_field_id('post__in'); ?>" class="widefat" />
			<br />
			<small><?php _e( 'Page IDs, separated by commas.' ); ?></small>
		</p>
		</div>

		<div id="<?php echo $this->get_field_id('post__not_in'); ?>-container" class="container">
		<p id="<?php echo $this->get_field_id('post__not_in'); ?>-contents" class="contents">
			<label for="<?php echo $this->get_field_id('post__not_in'); ?>"><?php _e( 'Excluding all these post IDs:' ); ?></label> <input type="text" value="<?php echo implode( ', ', (array) $instance['post__not_in'] ); ?>" name="<?php echo $this->get_field_name('post__not_in'); ?>" id="<?php echo $this->get_field_id('post__not_in'); ?>" class="widefat" />
			<br />
			<small><?php _e( 'Page IDs, separated by commas.' ); ?></small>
		</p>
		</div>

<?php 
		// go back to the other blog
		endif;
		$postloops->restore_current_blog(); 
?>

		<?php if( $other_instances = $this->control_instances( $instance['relatedto'] )): ?>
			<div id="<?php echo $this->get_field_id('what'); ?>-container" class="container">
			<p id="<?php echo $this->get_field_id('what'); ?>-contents" class="contents">
				<label for="<?php echo $this->get_field_id('relationship'); ?>"><?php _e('Related to other posts:'); ?></label>
				<select id="<?php echo $this->get_field_id('relationship'); ?>" name="<?php echo $this->get_field_name('relationship'); ?>">
					<option value="excluding" <?php selected( $instance['relationship'], 'excluding' ) ?>>Excluding those</option>
					<option value="similar" <?php selected( $instance['relationship'], 'similar' ) ?>>Similar to</option>
				</select>
				<?php _e('items shown in:'); ?>
				<ul>
				<?php echo $other_instances; ?>
				</ul>
			</p>
			</div>
		<?php endif; ?>

		<p>
			<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Number of items to show:' ); ?></label>
			<select name="<?php echo $this->get_field_name('count'); ?>" id="<?php echo $this->get_field_id('count'); ?>" class="widefat">
			<?php for( $i = 1; $i < 51; $i++ ){ ?>
				<option value="<?php echo $i; ?>" <?php selected( $instance['count'], $i ); ?>><?php echo $i; ?></option>
			<?php } ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('order'); ?>"><?php _e( 'Ordered by:' ); ?></label>
			<select name="<?php echo $this->get_field_name('order'); ?>" id="<?php echo $this->get_field_id('order'); ?>" class="widefat">
					<option value="age_new" <?php selected( $instance['order'], 'age_new' ); ?>><?php _e('Newest first'); ?></option>
					<option value="age_old" <?php selected( $instance['order'], 'age_old' ); ?>><?php _e('Oldest first'); ?></option>
					<option value="rand" <?php selected( $instance['order'], 'rand' ); ?>><?php _e('Random'); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('template'); ?>"><?php _e( 'Template:' ); ?></label>
			<select name="<?php echo $this->get_field_name('template'); ?>" id="<?php echo $this->get_field_id('template'); ?>" class="widefat">
				<?php $this->control_template_dropdown( $instance['template'] ); ?>
			</select>
		</p>








<?php
	}


	function holding_area(){
?>
	
<!--	
<?php
//TODO: clean up these old fields
?>
		<fieldset class="bsuite-any-posts-activity">
		<p><label for="bsuite-any-posts-activity-<?php echo $number; ?>"><?php _e('Activity:'); ?>
		<select id="bsuite-any-posts-activity-<?php echo $number; ?>" name="bsuite-any-posts[<?php echo $number; ?>][activity]">
			<option value="pop_most" <?php selected( $options[$number]['activity'], 'pop_most' ) ?>>Most popular items</option>
			<option value="pop_least" <?php selected( $options[$number]['activity'], 'pop_least' ) ?>>Least popular</option>
	
			<option value="pop_recent" <?php selected( $options[$number]['activity'], 'pop_recent' ) ?>>Recently viewed</option>
	
			<option value="comment_recent" <?php selected( $options[$number]['activity'], 'comment_recent' ) ?>>Recently commented</option>
			<option value="comment_few" <?php selected( $options[$number]['activity'], 'comment_few' ) ?>>Fewest comments</option>
		</select>
		</label></p>
		<fieldset>

		<fieldset class="bsuite-any-posts-age">
		<p><label for="bsuite-any-posts-age-<?php echo $number; ?>"><?php _e('Post date:'); ?>
		<select id="bsuite-any-posts-age-<?php echo $number; ?>" name="bsuite-any-posts[<?php echo $number; ?>][age]">
			<option value="age_new" <?php selected( $options[$number]['age'], 'after' ) ?>>After</option>
			<option value="age_old" <?php selected( $options[$number]['age'], 'before' ) ?>>Before</option>
			<option value="age_from" <?php selected( $options[$number]['age'], 'around' ) ?>>Around</option>
		</select>
		</label> 
		<label for="bsuite-any-posts-agestrtotime-<?php echo $number; ?>"><input style="width: 150px" id="bsuite-any-posts-agestrtotime-<?php echo $number; ?>" name="bsuite-any-posts[<?php echo $number; ?>][agestrtotime]" type="text" value="<?php echo attribute_escape( $options[$number]['agestrtotime'] ); ?>" /></label></p>
		<fieldset>

		<?php if( $other_instances = $this->control_instances( $instance['relatedto']) ): ?>
			<fieldset class="bsuite-any-posts-relationship">
				<p><label for="bsuite-any-posts-relationship-<?php echo $number; ?>">
				<select id="bsuite-any-posts-relationship-<?php echo $number; ?>" name="bsuite-any-posts[<?php echo $number; ?>][relationship]">
					<option value="similar" <?php selected( $options[$number]['relationship'], 'similar' ) ?>>Similar to</option>
					<option value="excluding" <?php selected( $options[$number]['relationship'], 'excluding' ) ?>>Excluding those</option>
				</select>
				</label>
				<?php _e('items shown in:'); ?> <?php echo $other_instances; ?></p>
			<fieldset>
		<?php endif; ?>

		<p><label for="bsuite-any-posts-columns-<?php echo $number; ?>"><?php _e('Number of columns:'); ?> <input style="width: 25px; text-align: center;" id="bsuite-any-posts-columns-<?php echo $number; ?>" name="bsuite-any-posts[<?php echo $number; ?>][columns]" type="text" value="<?php echo attribute_escape( $options[$number]['columns'] ); ?>" /></label> <?php _e('(1 to 8)'); ?></p>
-->	
	


<?php
	}

	function control_blogs( $instance , $do_output = TRUE , $switch = TRUE ){
		// return of TRUE means the user either has permission to the selected blog, or this isn't MU

		global $current_user, $blog_id, $bsuite;

		if( !$bsuite->is_mu )
			return TRUE; // The user has permission by virtue of it not being MU

		$blogs = $this->get_blog_list( $current_user->ID );

		if( ! $blogs )
			return TRUE; // There was an error, but we assume the user has permission

		if( ! $instance['blog'] ) // the blog isn't set, so we assume it's the current blog
			$instance['blog'] = $blog_id;

		foreach( (array) $blogs as $item )
		{
			if( $item['blog_id'] == $instance['blog'] ) 
			{
				// The user has permisson in here, any return will be TRUE
				if( count( $blogs ) < 2 ) // user has permission, but there's only one choice
					return TRUE; // there's only one choice, and the user has permssion to it

				if( $do_output )
				{
					echo '<div id="'. $this->get_field_id('blog') .'-container" class="container"><p id="'. $this->get_field_id('blog') .'-contents" class="container"><label for="'. $this->get_field_id('blog') .'">'. __( 'From:' ) .'</label><select name="'. $this->get_field_name('blog') .'" id="'. $this->get_field_id('blog') .'" class="widefat">';
					foreach( $this->get_blog_list( $current_user->ID ) as $blog )
					{
							?><option value="<?php echo $blog['blog_id']; ?>" <?php selected( $instance['blog'], $blog['blog_id'] ); ?>><?php echo $blog['blog_id'] == $blog_id ? __('This blog') : $blog['blogname']; ?></option><?php
					}
					echo '</select></p></div>';
				}

				if( $switch && ( $instance['blog'] <> $blog_id ))
					switch_to_blog( $instance['blog'] ); // switch to the other blog

				return TRUE; // the user has permission, and many choices
			}
		}

?>
		<div id="<?php echo $this->get_field_id('blog'); ?>-container" class="container">
		<p id="<?php echo $this->get_field_id('blog'); ?>-contents" class="contents">
			<label for="<?php echo $this->get_field_id('blog'); ?>"><?php _e( 'From:' ); ?></label>
			<input type="text" value="<?php echo attribute_escape( get_blog_details( $instance['blog'] )->blogname ); ?>" name="<?php echo $this->get_field_name('blog'); ?>" id="<?php echo $this->get_field_id('blog'); ?>" class="widefat" disabled="disabled" />
		</p>
		</div>
<?php

		return FALSE; // the user doesn't have permission to the selected blog
	}

	function get_blog_list( $current_user_id ){
		global $current_site, $wpdb;

		if( isset( $this->bloglist ))
			return $this->bloglist;

		if( is_site_admin() )
		{
			// i have to do this because get_blog_list() doesn't allow me to select private blogs
			foreach( (array) $wpdb->get_results( $wpdb->prepare("SELECT blog_id, public FROM $wpdb->blogs WHERE site_id = %d AND archived = '0' AND mature = '0' AND spam = '0' AND deleted = '0' ORDER BY registered DESC", $wpdb->siteid), ARRAY_A ) as $k => $v )
			{
				$this->bloglist[ get_blog_details( $v['blog_id'] )->blogname . $k ] = array( 'blog_id' => $v['blog_id'] , 'blogname' => get_blog_details( $v['blog_id'] )->blogname . ( 1 == $v['public'] ? '' : ' ('. __('private') .')' ) );
			}
		}
		else
		{
			foreach( (array) get_blogs_of_user( $current_user_id ) as $k => $v )
			{
				$this->bloglist[ get_blog_details( $v->userblog_id )->blogname . $k ] = array( 'blog_id' => $v->userblog_id , 'blogname' => $v->blogname );
			}
		}

		ksort( $this->bloglist );
		return $this->bloglist;
	}

	function control_categories( $instance , $whichfield = 'categories_in' ){
		$items = get_categories( array( 'style' => FALSE, 'echo' => FALSE, 'hierarchical' => FALSE ));
		foreach( $items as $item ){
			$list[] = '<li>
				<label for="'. $this->get_field_id( $whichfield .'-'. $item->term_id) .'"><input id="'. $this->get_field_id( $whichfield .'-'. $item->term_id) .'" name="'. $this->get_field_name( $whichfield ) .'['. $item->term_id .']" type="checkbox" value="1" '. ( isset( $instance[ $whichfield ][ $item->term_id ] ) ? 'checked="checked"' : '' ) .'/> '. $item->name .'</label>
			</li>';
		}
	
		return implode( "\n", $list );
	}
	
	function control_instances( $selected = array() ){
		global $postloops;

		// reset the instances var, in case a new widget was added
		$postloops->get_instances();

		$list = array();
		foreach( $postloops->instances as $number => $instance ){
			if( $number == $this->number )
				continue;

			$list[] = '<li>
				<label for="'. $this->get_field_id( 'relatedto-'. $number ) .'"><input class="checkbox" type="checkbox" value="'. $number .'" '.( in_array( $number, (array) $selected ) ? 'checked="checked"' : '' ) .' id="'. $this->get_field_id( 'relatedto-'. $number) .'" name="'. $this->get_field_name( 'relatedto' ) .'['. $number .']" /> '. $instance['title'] .'<small> (id:'. $number .')</small></label>
			</li>';
		}
	
		return implode( "\n", $list );
	}
	
	function control_template_dropdown( $default = '' ) {
		foreach ( $this->post_templates as $template => $info ) :
			if ( $default == $template )
				$selected = " selected='selected'";
			else
				$selected = '';
			echo "\n\t<option value=\"" .$info['file'] .'" '. $selected .'>'. $info['name'] .'</option>';
		endforeach;
	}
}// end bSuite_Widget_Postloop




/**
 * PostLoop widget class
 *
 */
class bSuite_Widget_ResponseLoop extends WP_Widget {

	function bSuite_Widget_ResponseLoop() {
		$widget_ops = array('classname' => 'widget_responseloop', 'description' => __( 'Show comments and response tools') );
		$this->WP_Widget('responseloop', __('Comment/Response Loop'), $widget_ops);
/*
		global $postloops;
		if( ! is_array( $postloops->templates_response ))
			$postloops->get_templates( 'response' );

		$this->response_templates = &$postloops->templates_response;
*/
	}

	function widget( $args, $instance ) {
		global $wp_query, $postloops;

		$instance['id'] = absint( str_replace( 'responseloop-' , '' , $args['widget_id'] ));
		$instance['md5id'] = md5( $instance['id'] . $instance['template'] . $instance['email'] );

		$old_wp_query = clone $wp_query;

		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? '' : $instance['title']);

		if( -1 == $instance['relatedto'] )
		{
			if( ! $wp_query->is_singular )
				return;

			$ourposts = &$wp_query;
		}
		else if( is_array( $postloops->posts[ $instance['relatedto'] ] ))
		{
			$post_ids = reset( $postloops->posts[ $instance['relatedto'] ] );

			if( 1 <> count( $post_ids ))
				return;

			$criteria['post_type'] = 'any';
			$criteria['post__in'] = $post_ids;

			$wp_query = new WP_Query( $criteria );

			if( 'page' == $wp_query->post->post_type )
				$wp_query->is_page = TRUE;
			else
				$wp_query->is_single = TRUE;

			$wp_query->is_singular = TRUE;

			$ourposts = &$wp_query;
		}
		else
		{
			return;
		}
	
		if( $ourposts->have_posts() ){
			echo str_replace( 'class="widget ', 'class="widget widget-response_loop-'. sanitize_title_with_dashes( $instance['title'] ) .' ' , $before_widget );

			if( ! empty( $instance['template'] ));
				$comments_template_function = create_function( '$a', "return '{$postloops->templates_response[ $instance['template'] ]['fullpath']}';" );
//				$comments_template_function = create_function( '$a', "return bsuite_comments_template_filter( '{$postloops->templates_response[ $instance['template'] ]['fullpath']}' );" );

			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'] );
			if ( $instance['title_show'] && $title )
				echo $before_title . $title . $after_title;

			while( $ourposts->have_posts() ){
				$ourposts->the_post();
				global $id, $post;

				$postloops->current_responseloop = $instance;

				if( ! empty( $instance['template'] ));
					add_filter( 'comments_template' , $comments_template_function );

				comments_template();

				if( ! empty( $instance['template'] ));
					remove_filter( 'comments_template' , $comments_template_function );
			}
			echo $after_widget;
		}

		unset( $postloops->current_responseloop );
		$wp_query = clone $old_wp_query;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = wp_filter_nohtml_kses( $new_instance['title'] );
		$instance['relatedto'] = intval( $new_instance['relatedto'] );
		$instance['template'] = wp_filter_nohtml_kses( $new_instance['template'] );
		$instance['email'] = sanitize_email( $new_instance['email'] );

/*
echo "<pre>";
//print_r($old_instance);
//print_r($new_instance);
print_r($instance);
echo "</pre>";
//die;
*/
		return $instance;
	}

	function form( $instance ) {
		global $postloops;
		//Defaults

		$instance = wp_parse_args( (array) $instance, 
			array( 
				'title' => __('Comments'),
				'relatedto' => -1,
				'template' => 'a_default_full.php',
				'email' => '',
				) 
			);

		$title = esc_attr( $instance['title'] );
?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>


		<p>
			<label for="<?php echo $this->get_field_id('relatedto'); ?>"><?php _e( 'Show comments/response tools for:' ); ?></label>
			<select name="<?php echo $this->get_field_name('relatedto'); ?>" id="<?php echo $this->get_field_id('relatedto'); ?>" class="widefat">
				<?php $this->control_instances( $instance['relatedto'] ); ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('template'); ?>"><?php _e( 'Template:' ); ?></label>
			<select name="<?php echo $this->get_field_name('template'); ?>" id="<?php echo $this->get_field_id('template'); ?>" class="widefat">
				<?php $this->control_template_dropdown( $instance['template'] ); ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('email'); ?>"><?php _e('Email responses:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('email'); ?>" name="<?php echo $this->get_field_name('email'); ?>" type="text" value="<?php echo $instance['email']; ?>" />
		</p>

<?php
	}
	
	function control_instances( $default = -1 ){
		global $postloops, $blog_id;

		$blog_id = absint( $blog_id );

		// reset the instances var, in case a new widget was added
		$postloops->get_instances();

		$list = array();
		foreach( $postloops->instances as $number => $instance ){
			if( $instance['blog'] <> $blog_id )
				continue;

			if ( $default == $number )
				$selected = " selected='selected'";
			else
				$selected = '';

			$list[] = '<option value="'. $number .'" '. $selected .'>'. $instance['title'] .' (id:'. $number .')</option>';
		}
	
		echo implode( "\n\t", $list );
	}
	
	function control_template_dropdown( $default = '' )
	{
		global $postloops;
		$templates = $postloops->templates_response;
		array_unshift( $templates , 
			array( 
	            'name' => 'Default Comment Form',
	            'file' => '',
	            'fullpath' => '',
			)
		);

		foreach ( $templates as $template => $info ) :
			if ( $default == $template )
				$selected = " selected='selected'";
			else
				$selected = '';
			echo "\n\t<option value=\"" .$info['file'] .'" '. $selected .'>'. $info['name'] .'</option>';
		endforeach;
	}
}// end bSuite_Widget_ResponseLoop



/**
 * Pages widget class
 *
 */
class bSuite_Widget_Pages extends WP_Widget {

	function bSuite_Widget_Pages() {
		$widget_ops = array('classname' => 'widget_pages', 'description' => __( 'A buncha yo blog&#8217;s WordPress Pages') );
		$this->WP_Widget('pages', __('Pages'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );

		if( $instance['startpage'] == -1 )
		{
			if( is_singular() )
			{
				global $wp_query;
				setup_postdata( $wp_query->post );
				global $post;
			}
			else
			{
				return;
			}
		}

		if( is_404() )
			$instance['expandtree'] = 0;

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? FALSE : $instance['title']);
		$homelink = empty( $instance['homelink'] ) ? '' : $instance['homelink'];
		$sortby = empty( $instance['sortby'] ) ? 'menu_order' : $instance['sortby'];
		$exclude = empty( $instance['exclude'] ) ? '' : $instance['exclude'];
		$startpage = isset( $instance['startpage'] ) ? ( $instance['startpage'] == -1 ? $post->ID : absint( $instance['startpage'] )) : 0;
		$depth = isset( $instance['depth'] ) ? $instance['depth'] : 1;

		if ( $sortby == 'menu_order' )
			$sortby = 'menu_order, post_title';

		$out = wp_list_pages( array(
			'child_of' => $startpage, 
			'title_li' => '', 
			'echo' => 0, 
			'sort_column' => $sortby, 
			'exclude' => $exclude, 
			'depth' => $depth 
		));

		if( $instance['expandtree'] && ( $instance['startpage'] >= 0 ) && is_page() ){
			global $post;

			// get the ancestor tree, including the current page
			$ancestors = $post->ancestors;
			$ancestors[] = $post->ID;
			$pages = get_pages( array( 'include' => implode( ',', $ancestors )));

			if ( !empty( $pages )){
				$subtree .= walk_page_tree( $pages, 0, $post->ID, array() );

				// get any siblings, insert them into the tree
				if( count( $post->ancestors ) && ( $siblings = wp_list_pages( array( 'child_of' => array_shift( $ancestors ), 'title_li' => '', 'echo' => 0, 'sort_column' => $sortby, 'exclude' => $exclude, 'depth' => 1 )))){
					$subtree = preg_replace( '/<li.+?current_page_item.+?<\/li>/i', $siblings, $subtree );
				}

				// get any children, insert them into the tree
				if( $children = wp_list_pages( array( 'child_of' => $post->ID, 'title_li' => '', 'echo' => 0, 'sort_column' => $sortby, 'exclude' => $exclude, 'depth' => $depth ))){
					$subtree = preg_replace( '/current_page_item[^<]*<a([^<]*)/i', 'current_page_item"><a\1<ul>'. $children .'</ul>', $subtree );
				}

				// insert this extended page tree into the larger list
				if( !empty( $subtree )){
					$out = preg_replace( '/<li[^>]*page-item-'. ( count( $post->ancestors ) ? end( $post->ancestors ) : $post->ID ) .'[^0-9][^>]*.*?<\/li>.*?($|<li)/si', $subtree .'\1', $out );
					reset( $post->ancestors );
				}
			}
		}

		if ( !empty( $out ) ) {
			echo $before_widget;
			if ( $title )
				echo $before_title . $title . $after_title;
		?>
		<ul>
			<?php if ( $homelink )
				echo '<li class="page_item page_item-home"><a href="'. get_option('home') .'">'. $homelink .'</a></li>';
			?>
			<?php echo $out; ?>
		</ul>
		<?php
			echo $after_widget;
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['homelink'] = strip_tags( $new_instance['homelink'] );
		if ( in_array( $new_instance['sortby'], array( 'post_title', 'menu_order', 'ID' ))) {
			$instance['sortby'] = $new_instance['sortby'];
		} else {
			$instance['sortby'] = 'menu_order';
		}
		$instance['depth'] = absint( $new_instance['depth'] );
		$instance['startpage'] = intval( $new_instance['startpage'] );
		$instance['expandtree'] = absint( $new_instance['expandtree'] );
		$instance['exclude'] = strip_tags( $new_instance['exclude'] );

		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, 
			array( 
				'sortby' => 'post_title', 
				'title' => '', 
				'exclude' => '', 
				'depth' => 1, 
				'startpage' => 0,
				'expandtree' => 1,
				'homelink' => sprintf( __('%s Home', 'Bsuite ') , get_bloginfo('name') ),
			)
		);

		$title = esc_attr( $instance['title'] );
		$homelink = esc_attr( $instance['homelink'] );
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
				<option value="5"<?php selected( $instance['depth'], '5' ); ?>><?php _e( '5' ); ?></option>
				<option value="6"<?php selected( $instance['depth'], '6' ); ?>><?php _e( '6' ); ?></option>
				<option value="7"<?php selected( $instance['depth'], '7' ); ?>><?php _e( '7' ); ?></option>
				<option value="0"<?php selected( $instance['depth'], '0' ); ?>><?php _e( 'All' ); ?></option>
			</select>
		</p>

		<p><label for="<?php echo $this->get_field_id('homelink'); ?>"><?php _e('Link to blog home:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('homelink'); ?>" name="<?php echo $this->get_field_name('homelink'); ?>" type="text" value="<?php echo $homelink; ?>" /><br /><small><?php _e( 'Optional, leave empty to hide.' ); ?></small></p>

		<p>
			<label for="<?php echo $this->get_field_id('startpage'); ?>"><?php _e( 'Start page hierarchy at:' ); ?></label>
			<?php echo str_replace( 
				'<select name="page_id" id="page_id">',
				
				'<select name="'. $this->get_field_name('startpage') .'" id="'. $this->get_field_id('startpage') .'" class="widefat">
				<option value="0"'. selected( $instance['startpage'], '0', FALSE ) .'>'. __( 'Root' ) .'</option>
				<option value="-1"'. selected( $instance['startpage'], '-1', FALSE ) .'>'. __( 'Current Page' ) .'</option>
				<option value="0">---------------------</option>',
				
				wp_dropdown_pages( array( 'echo' => 0 , 'selected' => $instance['startpage'] > 0 ? absint( $instance['startpage'] ) : 0 ))); ?>
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
}// end bSuite_Widget_Pages



/**
 * Crumbs widget class
 *
 */
class bSuite_Widget_Crumbs extends WP_Widget {

	function bSuite_Widget_Crumbs() {
		$widget_ops = array('classname' => 'widget_breadcrumbs', 'description' => __( 'A breadcrumb navigation path') );
		$this->WP_Widget('breadcrumbs', __('Breadcrumbs'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );

		wp_reset_query();

		global $wp_query;

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? '' : $instance['title']);
		$maxchars = absint( $instance['maxchars'] ) > 10 ? absint( $instance['maxchars'] ) : 10;

		$crumbs = array();

		if( !empty( $instance['homelink'] ))
			$crumbs[] = '<li class="bloghome"><a href="'. get_option('home') .'">'. $instance['homelink'] .'</a></li>';

		if( is_singular() ){
			setup_postdata( $wp_query->post );
			global $post, $page, $multipage;

			// get the ancestor tree, if exists
			$ancestors = array();
			if( is_array( $post->ancestors )){
				foreach( array_reverse( $post->ancestors ) as $post_id ){
					$crumbs[] = '<li><a href="'. get_permalink( $post_id ) .'"
					rel="bookmark" title="'. sprintf( __('Permanent Link to %s') , esc_attr( strip_tags( get_the_title( $post_id )))) .' ">'. ( strlen( get_the_title( $post_id )) > $maxchars ? trim( substr( get_the_title( $post_id ), 0, $maxchars )) .'&#8230;' : get_the_title( $post_id ) ) .'</a></li>';
				}
			}

			// add the current page to the tree
			$crumbs[] = '<li class="'. $post->post_type .'_item '. $post->post_type .'-item-'. $post->ID .' current_'. $post->post_type .'_item" ><a href="'. get_permalink( $post->ID ) .'" rel="bookmark" title="'. sprintf( __('Permanent Link to %s') , esc_attr( strip_tags( get_the_title( $post->ID )))) .'">'. ( strlen( get_the_title( $post->ID )) > $maxchars ? trim( substr( get_the_title( $post->ID ), 0, $maxchars )) .'&#8230;' : get_the_title( $post->ID ) ) .'</a></li>';

			//if this is a multi-page post/page...
			if( $multipage ){

				// generate a permalink to this page
				if ( 1 == $page ) {
					$link = get_permalink( $post->ID );
				} else {
					if ( '' == get_option('permalink_structure') || in_array($post->post_status, array('draft', 'pending')) )
						$link = get_permalink( $post->ID ) . '&amp;page='. $page;
					else
						$link = trailingslashit( get_permalink( $post->ID )) . user_trailingslashit( $page, 'single_paged' );
				}

				// add it to the crumbs
				$crumbs[] = '<li class="'. $post->post_type .'_item '. $post->post_type .'-item-'. $post->ID .' current_'. $post->post_type .'_item" ><a href="'. $link .'" rel="bookmark" title="'. sprintf( __('Permanent Link to page %d of %s') , (int) $page , esc_attr( strip_tags( get_the_title( $post->ID ))) ) .'">'. sprintf( __('Page %d') , (int) $page ) .'</a></li>';
			}
		}else{

			if( is_search() )
				$crumbs[] = '<li><a href="'. $link .'">'. __('Search') .'</a></li>';

//			if( is_paged() && $wp_query->query_vars['paged'] > 1 )
//				$page_text = sprintf( __('Page %d') , $wp_query->query_vars['paged'] );
		}

		if ( count( $crumbs ) ) {
			echo $before_widget;
//			if ( $title )
//				echo $before_title . $title . $after_title;
		?>
			<ul>
				<?php echo implode( "\n", $crumbs ); ?>
			</ul>
			<div class="clear"></div>
		<?php
			echo $after_widget;
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['homelink'] = strip_tags( $new_instance['homelink'] );
		$instance['maxchars'] = absint( $new_instance['maxchars'] );

		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, 
			array( 
				'title' => '', 
				'homelink' => get_option('blogname'),
				'maxchars' => 35,
			)
		);

		$title = esc_attr( $instance['title'] );
		$homelink = esc_attr( $instance['homelink'] );
?>

		<p>
			<label for="<?php echo $this->get_field_id('homelink'); ?>"><?php _e('Link to blog home:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('homelink'); ?>" name="<?php echo $this->get_field_name('homelink'); ?>" type="text" value="<?php echo $homelink; ?>" /><br /><small><?php _e( 'Optional, leave empty to hide.' ); ?></small>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('maxchars'); ?>"><?php _e('Maximum crumb length:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('maxchars'); ?>" name="<?php echo $this->get_field_name('maxchars'); ?>" type="text" value="<?php echo absint( $instance['maxchars'] ); ?>" /><br /><small><?php _e( 'Maximum number of characters per crumb.' ); ?></small>
		</p>
<?php
	}
}// end bSuite_Widget_Crumbs



/**
 * Pagednav widget class
 *
 */
class bSuite_Widget_Pagednav extends WP_Widget {

	function bSuite_Widget_Pagednav() {
		$widget_ops = array('classname' => 'widget_pagednav', 'description' => __( 'Prev/Next page navigation') );
		$this->WP_Widget('pagednav', __('Paged Navigation Links'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );

		wp_reset_query();

		global $wp_query, $wp_rewrite;

		if( ! $wp_query->is_singular )
		{
			$urlbase = preg_replace( '#/page/[0-9]+?(/+)?$#' , '/', remove_query_arg( 'paged' ) );
			$prettylinks = ( $wp_rewrite->using_permalinks() && ( !strpos( $urlbase , '?' )));
			
			$page_links = paginate_links( array(
				'base' => $urlbase . '%_%',
				'format' => $prettylinks ? user_trailingslashit( trailingslashit( 'page/%#%' )) : ( strpos( $urlbase , '?' ) ? '&paged=%#%' : '?paged=%#%' ),
				'total' => absint( $wp_query->max_num_pages ),
				'current' => absint( $wp_query->query_vars['paged'] ) ? absint( $wp_query->query_vars['paged'] ) : 1,
			));
			
			if ( $page_links )
				echo $before_widget . $page_links .'<div class="clear"></div>'. $after_widget;
		}
		else
		{
			echo $before_widget;
?>
			<div class="alignleft"><?php previous_post_link('&laquo; %link') ?></div>
			<div class="alignright"><?php next_post_link('%link &raquo;') ?></div>
			<div class="clear"></div>
<?php
			echo $after_widget;
		}
	}

}// end bSuite_Widget_Pagednav


// register these widgets
function bsuite_widgets_init() {
	register_widget( 'bSuite_Widget_PostLoop' );
	register_widget( 'bSuite_Widget_ResponseLoop' );

	register_widget( 'bSuite_Widget_Crumbs' );

	register_widget( 'bSuite_Widget_Pagednav' );

	unregister_widget('WP_Widget_Pages');
	register_widget( 'bSuite_Widget_Pages' );
}
add_action('widgets_init', 'bsuite_widgets_init', 1);

/*
Reminder to self: the widget objects and their vars can be found in here:

global $wp_widget_factory;
print_r( $wp_widget_factory );

*/
