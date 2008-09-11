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
});