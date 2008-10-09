// renumbers form names/ids in a sortable/editable list
// used some hints from here: http://bennolan.com/?p=35 http://bennolan.com/?p=21
jQuery.fn.renumber = function() {
	var i = 0;
	jQuery(jQuery(this).parent()).parent().find( 'li' ).each( function(){
		jQuery(this).find( 'input,select,textarea' ).attr("id", function(){
			return( jQuery(this).attr("id").replace(/\d+/, i) );
		});
		jQuery(this).find( 'input,select,textarea' ).attr("name", function(){
			return( jQuery(this).attr("name").replace(/\d+/, i) );
		});
		i++;
	})

};

// fetches the bsuite icon upload form and puts it in the iframe
function bsuite_icon_getuploadform() {
	if( 0 < jQuery('#post_ID').val()){
		jQuery('#bsuite_icon_iframe').contents().find('body').load('admin-ajax.php', { 
			action : 'bsuite_icon_form', 
			post_ID : ( jQuery('#post_ID').val() ) 
		});
	}
}

// forces WP to put a real post ID on any new drafts
function bsuite_icon_getrealpostid() {
	if( 0 > jQuery('#post_ID').val()){
		if( '' == jQuery('#title').val())
			jQuery('#title').val(' '); // put a nearly empty post title in so that there's something to save
	
		autosave(); // do an autosave to generate a real post_ID
	
		setTimeout( function(){ // pause for a moment to let things simmer
			bsuite_icon_getuploadform();
		}, 1000 )
	
		if( ' ' == jQuery('#title').val())
			jQuery('#title').val(''); // clear the dummy title

		if( 0 < jQuery('#post_ID').val()) {
			setTimeout( function(){ // pause for a moment to let things simmer
				bsuite_icon_getrealpostid();
			}, 5000 )
		}
	}
}

jQuery(document).ready(function(){

	// make the list sortable
	// http://docs.jquery.com/UI/Sortables
	jQuery("#bsuite_machine_tags").sortable({
		stop: function(){
			jQuery(this).renumber();
		}
	});

	// add a handle to the begining of each line 
	// http://docs.jquery.com/Manipulation/before
	jQuery("#bsuite_machine_tags .taxonomy").before("<span class='sortable'>&uarr;&darr;</span> ");

	// add a delete and clone button to the end of each line 
	// http://docs.jquery.com/Manipulation/after
	jQuery("#bsuite_machine_tags .term").after(" <button class='add' type='button'>+</button>");
	jQuery("#bsuite_machine_tags .term").after(" <button class='del' type='button'>-</button>");

 	// make that button clone the line
 	// http://docs.jquery.com/Manipulation/clone
	jQuery("button.add").click(function(){
		jQuery(this).parent().clone(true).insertAfter(jQuery(this).parent())
		jQuery(this).renumber();
	});

	jQuery("button.del").click(function(){ 
		jQuery(this).parent().remove();
		jQuery(this).renumber();
	});

	// prepares the bsuite icon upload/edit stuff
	setTimeout( function(){
		bsuite_icon_getuploadform(); //attempt to load the scrib upload form
		bsuite_icon_getrealpostid();
	}, 500 );

});