<?php
/*
Plugin Name: Virtual Gallery Plugin
Plugin URI: http://www.centerofthewest.org
Description: Buffalo Bill Center of the West Virtual Gallery Plugin for Online Collections.
Version: 1.0
Author: Lloyd Johnson
*/
defined('ABSPATH') or die("No script kiddies please!");//this prevents people from naviating to the file directly
add_shortcode( 'VirtualGallery', 'VGPlugin');//adds the shortcode that executes my funtion, this injects the html where i use the shortcode.
//https://gist.github.com/rxnlabs/10407269
add_action('media_buttons_context','add_my_gallery_button');
function add_my_gallery_button($context){
	return $context.=__("<a href=\"#TB_inline?width=480&inlineId=my_shortcode_popup&width=640&height=513\" class=\"button thickbox\" id=\"virtual_gallery_popup_button\" title=\"Virtual Gallery\"><span style='background: transparent url(".plugins_url( 'VirtualGalleryPlugin/assets/img/icon.png').") no-repeat scroll 0 0; width: 16px; height: 16px; display: inline-block; vertical-align: text-top;'></span>&nbsp;Add Virtual Gallery</a>");}
add_action('admin_footer','virtual_gallery_button_popup');
function virtual_gallery_button_popup(){?>
  <div id="my_shortcode_popup" style="display:none;">
    <--".wrap" class div is needed to make thickbox content look good-->
    <div class="wrap">
      <div>
        <h2>Insert Virtual Gallery</h2>
        <div class="my_shortcode_add">
          <input type="text" id="virtual_gallery_id" placeholder="Virutal Gallery ID"><br>
		<select id="virtual_gallery_slides">
			<option value="1">Number of Slides</option>
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
			<option value="5">5</option>
		</select><br>
		<select id="virtual_gallery_title">
			<option value="0">Display Title?</option>
			<option value="1">Yes</option>
			<option value="0">No</option>
		</select><br><br><br>
		  
		  <button class="button-primary" id="id_of_button_clicked">Insert Virtual Gallery</button>
        </div>
      </div>
    </div>
  </div>
<?php
}

add_action('admin_footer','my_shortcode_add_shortcode_to_editor');
function my_shortcode_add_shortcode_to_editor(){?>
<script>
jQuery('#id_of_button_clicked ').on('click',function(){
  
  var VG_id = jQuery('#virtual_gallery_id').val();
  var VG_slides = jQuery('#virtual_gallery_slides').val();
  var VG_title = jQuery('#virtual_gallery_title').val();
  
  var shortcode = '[VirtualGallery  id="'+VG_id+'" slides="'+VG_slides+'" title="'+VG_title+'"]';
  if( !tinyMCE.activeEditor || tinyMCE.activeEditor.isHidden()) {
    jQuery('textarea#content').val(shortcode);
  } else {
    tinyMCE.execCommand('mceInsertContent', false, shortcode);
  }
  //close the thickbox after adding shortcode to editor
  self.parent.tb_remove();
});
</script>
<?php
}


function VGPlugin( $atts ) {
	$plugins_url = plugins_url();
	wp_enqueue_script( 'MyGallery', $plugins_url . "/VirtualGalleryPlugin/assets/js/jquery.scrollbox.js");	
	wp_enqueue_script( 'MyScroll', $plugins_url . "/VirtualGalleryPlugin/assets/js/myScroll.js");
	wp_enqueue_style( 'MyScrollStyle', $plugins_url . "/VirtualGalleryPlugin/assets/css/style.css");
	//http://plugins.jquery.com/jquery-scrollbox/
	
	//this takes the data passed from the shortcode and assigns it to the object, if the space if left blank the values below are what they will default to
	$PassedData = shortcode_atts(array('id' => '25','slides'=>'1','title'=>'0'), $atts );
	//this gets all my data and decodes it but only if the api is available if it is not then it returns nothing instead of an error 
	if((int)$PassedData['id']==0)
		return;
	$strJson = @file_get_contents('http://collections.centerofthewest.org/usergals/view/'.(int)$PassedData['id'].'.json');
	//print_r($strJson);
	if (!$strJson) 
		return;
	$arrJson = json_decode($strJson,true);
	
	//this sets the width of the slider based on how many slides are asked for 
	if($PassedData['slides']>4)
		$galleryWidth=838;
	if($PassedData['slides']==4)
		$galleryWidth=674;
	if($PassedData['slides']==3)
		$galleryWidth=510;		
	if($PassedData['slides']==2)
		$galleryWidth=346;		
	if($PassedData['slides']<2)
		$galleryWidth=182;			
	//the wrapper is added, notice the width is set here also using the if statment above
	$gal_content='<div class="gallery-wrapper" style="width:'.$galleryWidth.'px;">';	
	
	//checks if the title was passed if it was, then the title and author info are displayed, by default does not show
	if((int)$PassedData['title']==1)
		{$gal_content.='<div class="gallery-info">Gallery: <a href="http://collections.centerofthewest.org/usergals/view/'.$arrJson['apivar']['Usergal']['Usergal']['id'].'">'.$arrJson['apivar']['Usergal']['Usergal']['name'].'</a> Created by:'.$arrJson['apivar']['Usergal']['Usergal']['creator'].'</div>';}
	
	$gal_content.='<div id="mygallery" class="scroll-img" style="width:'.$galleryWidth.'px;"><ul>';
	foreach($arrJson['apivar']['Items'] as $instance){				
		$gal_content .= '<li id="slidez">';
			$gal_content .='<a href="http://collections.centerofthewest.org/treasures/view/'.$instance['Treasure']['slug'].'/">';		
				$gal_content .= '<div class="the-objects">';					
					$gal_content .='<div class="img-block" style="background-image: url(//collections.centerofthewest.org/zoomify/1/'.$instance['Treasure']['img'].'/TileGroup0/0-0-0.jpg);"></div>';				
					$gal_content .='<div class="caption">';				
							if($instance['objtitle']!=null)
								$gal_content .=$instance['Treasure']['objtitle'];
							else
								$gal_content .=$instance['Treasure']['accnum'];
					$gal_content .='</div>';
					$gal_content .='<div class="bubble">';
					if($instance['Treasure']['gloss']!=null)
						$gal_content .=$instance['Treasure']['gloss'];
					else if($instance['Treasure']['remarks']!=null )
						$gal_content .=$instance['Treasure']['remarks'];
					else 
						$gal_content .=$instance['Treasure']['synopsis'];
				$gal_content .='</div>';
				$gal_content .='</div>';			
			$gal_content .= '</a>';
		$gal_content .='</li>';
					
	}
	$gal_content .='</ul></div>';
	$gal_content .='<div class="mygallery-controls"><button class="btn" id="mygallery-backward"><</button><button class="btn" id="mygallery-forward">></button></div>
	</div>';	
	return $gal_content;
}
?>