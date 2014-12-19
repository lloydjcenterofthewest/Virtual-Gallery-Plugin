jQuery(document).ready( function($) {

	//this sets the direction and how much each slide should move
	$('#mygallery').scrollbox({ direction: 'h',distance: 140});
	//this is the backwards button	
	$('#mygallery-backward').click(function () {
	  $('#mygallery').trigger('backward');
	});	
	//this is the forwards button
	$('#mygallery-forward').click(function () {
		$('#mygallery').trigger('forward');
	});	
	//this allows for the hover effect giving you info about each object when u hover over them, like in netflix
	$('li#slidez').hover(
		function(){
			$(this).find('.bubble').toggle(0);			
		},
		function(){
			$(this).find('.bubble').toggle(0);
		}
	);
	$('li#slidez').mousemove(
		function( event ) 
		{		
			$(this).find('.bubble').css('left',event.pageX);
			$(this).find('.bubble').css('top',event.pageY);
		}
	);
});