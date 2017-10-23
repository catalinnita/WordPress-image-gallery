<?php
// styles and scripts for front-end
if (!is_admin()) {
	
	$css_library['wpfw-grid-gallery-css'] = array(plugins_url( 'styles/gallery-grid.css' , __FILE__ ), '1.0');
	$css_library['wpfw-horizontal-gallery-css'] = array(plugins_url( 'styles/gallery-horizontal.css' , __FILE__ ), '1.0');
	$css_library['wpfw-vertical-gallery-css'] = array(plugins_url( 'styles/gallery-vertical.css' , __FILE__ ), '1.0');
	add_action('wp_enqueue_scripts', 'wpfw_p_enqueue_core_scripts');
	add_action('wp_enqueue_scripts', 'wpfw_p_enqueue_core_styles');
	
}

// styles and scripts for admin
if (is_admin()) {
	$wp_library[] = 'jquery-form';
	$wp_library[] = 'jquery-ui-core';
	$wp_library[] = 'jquery-ui-sortable';
	$wp_library[] = 'jquery-ui-widget';
	$js_library['wpfw-admin-gallery-js'] = array(plugins_url( 'js/admin_gallery.js' , __FILE__ ), '1.0');
	$js_library['wpfw-admin-transport-js'] = array(plugins_url( 'js/jquery.iframe-transport.js' , __FILE__ ), array( 'jquery' ), 1.0, true);
	$js_library['wpfw-admin-fileupload-js'] = array(plugins_url( 'js/jquery.fileupload.js' , __FILE__ ), array( 'jquery' ), 1.0, true);
	$css_library['wpfw-admin-icons'] = array(plugins_url( 'styles/fonts/typicons.min.css' , __FILE__ ), '1.0');
	$css_library['wpfw-admin-gallery-css'] = array(plugins_url( 'styles/gallery-admin.css' , __FILE__ ), '1.0');
	$css_library['wpfw-window-css'] = array(plugins_url( 'styles/wpfw-window.css' , __FILE__ ), '1.0');
	
	add_action('admin_enqueue_scripts', 'wpfw_p_enqueue_core_scripts');
	add_action('admin_enqueue_scripts', 'wpfw_p_enqueue_core_styles');	
}

add_image_size('gallery-admin', 200, 150, true);

?>
