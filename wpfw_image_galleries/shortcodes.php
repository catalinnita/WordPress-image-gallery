<?php
if(!isset($wpfw_shortcodes)) {
	$wpfw_shortcodes = array();
}
$wpfw_shortcodes[] = array(
	'id' => 'wpfw_gallery',
	'name' => 'WPFW Galleries',
	'window_size' => 'medium',
	'window_title' => 'Insert a gallery',
	'button_title' => 'Insert a gallery',
	'button_img' => plugin_dir_url(__FILE__).'images/shortcodes_buttons/gallery_button.png',
	'settings' => array(
		'id' => array('name' => 'Select Gallery',
											 'type' => 'selectbox',
											 'values' => wpfw_get_all_galleries()),
		'type' => array('name' => 'Gallery Type',
											 'type' => 'selectbox',
											 'values' => array('Grid' => 'Grid',
											 									 'Slider' => 'Slider',
											 									 'Vertical' => 'Vertical')),
		'cols' => array('name' => 'Gallery Cols',
											 'type' => 'selectbox',
											 'parent' => 'type:Grid',
											 'values' => array('1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5', '6'=>'6', '7'=>'7', '8'=>'8'))
											 									 											 
	)
);
$wpfw_shortcodes[] = array(
	'id' => 'wpfw_album_gallery',
	'name' => 'WPFW Gallery Albums',
	'window_size' => 'medium',
	'window_title' => 'Insert an album',
	'button_title' => 'Insert an album',
	'button_img' => plugin_dir_url(__FILE__).'images/shortcodes_buttons/gallery_button.png',
	'settings' => array(
		'id' => array('name' => 'Select Album',
											 'type' => 'selectbox',
											 'values' => wpfw_get_all_albums()),
		'type' => array('name' => 'Gallery Type',
											 'type' => 'selectbox',
											 'values' => array('Grid' => 'Grid',
											 									 'Slider' => 'Slider',
											 									 'Vertical' => 'Vertical')),
		'cols' => array('name' => 'Gallery Cols',
											 'type' => 'selectbox',
											 'parent' => 'type:Grid',
											 'values' => array('1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5', '6'=>'6', '7'=>'7', '8'=>'8'))
	)
);

include('wpfw_shortcodes/shortcodes_core.php');
?>