<?php
/**
 * Wijax class
 *
 */
class bSuite_Wijax {

	function bSuite_Wijax()
	{
		global $bsuite;

		$this->path_web = is_object( $bsuite ) ? $bsuite->path_web : get_template_directory_uri();

		add_action( 'init', array( &$this, 'init' ));
	}

	function init()
	{
		add_rewrite_endpoint( 'wijax' , EP_ALL );
		add_filter( 'request' , array( &$this, 'request' ));
	}

	public function request( $request )
	{
		if( isset( $request['wijax'] ))
			add_filter( 'template_redirect' , array( &$this, 'redirect' ), 0 );

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
	
			$widget_data['widget'] = $key;
	
			$widget_data['params'][0] = array(
				'name' => 'a',
				'id' => 'a',
				'before_widget' => '<div id="widget-%1$s" class="widget %2$s"><div class="widget-inner">'."\n",
				'after_widget'  => '</div></div>'."\n",
				'before_title'  => '<h2 class="widgettitle">',
				'after_title'   => "</h2>\n",
				'widget_id' => 'a',
				'widget_name' => 'a',
			);
	
			$widget_data['params'][1] = array(
				'number' => absint( $widget_data['callback'][0]->number ),
			);
	
			$widget_data['params'][0]['before_widget'] = sprintf($widget_data['params'][0]['before_widget'], $widget_data['widget'], 'grid_' . $widget_data['size'] . ' ' .$widget_data['class'] . ' ' . $widget_data['id'] . ' ' . $extra_classes);
			call_user_func_array( $widget_data['callback'], $widget_data['params'] );

		}//end foreach

/*	
		if($_GET['output'] == 'js')
		{
			$params = array(
				'callback' => '$.my.channelLoad',
				'channel_id' => $_GET['channel_id']
			);
			if($_GET['js_callback']) $params['js_callback'] = $_GET['js_callback'];
			Channel::out('callback', $params);
		}//end if
*/
		die;
	}

} //end bSuite_Wijax

// initialize that class
new bSuite_Wijax();
