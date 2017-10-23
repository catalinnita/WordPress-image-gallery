<?php
if(!function_exists("add_shortcode_button")) {
function add_shortcode_button() {
   if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
     	return;
     	
   if ( get_user_option('rich_editing') == 'true') {
     add_filter('mce_external_plugins', 'add_tinymce_plugin');
     add_filter('mce_buttons', 'register_tinymce_button');
   }
}
add_action('init', 'add_shortcode_button');
}

if(!function_exists("add_tinymce_plugin")) {
	function add_tinymce_plugin($plugin_array) {
		global $wpfw_shortcodes;
		
		foreach($wpfw_shortcodes as $wpfw_shortcode) {
	  	$plugin_array[$wpfw_shortcode['id']] = plugins_url('shortcodes.php?bn='.$wpfw_shortcode['id'].'&butt_img='.urlencode($wpfw_shortcode['button_img']).'&butt_title='.urlencode($wpfw_shortcode['button_title']), __FILE__);
	  }
	   
	  return $plugin_array;
	}
}

if(!function_exists("register_tinymce_button")) {
function register_tinymce_button($buttons) {
	 global $wpfw_shortcodes;
	 
	 foreach($wpfw_shortcodes as $wpfw_shortcode) {
   	array_push($buttons, "|", $wpfw_shortcode['id']);
   }
   
   return $buttons;
}
}


if(!function_exists("my_refresh_mce")) {
function my_refresh_mce($ver) {
  $ver += 3;
  return $ver;
}
add_filter( 'tiny_mce_version', 'my_refresh_mce');
}

if(!function_exists("build_shortcodes_window")) {
function build_shortcodes_window($content) {
	global $wpdb, $wpfw_shortcodes;
	
	$content = $content;
	foreach($wpfw_shortcodes as $wpfw_shortcode) {

	if (!isset($wpfw_shortcode['window_title'])) $wpfw_shortcode['window_title'] = ''; 
	if (!isset($wpfw_shortcode['window_size'])) $wpfw_shortcode['window_size'] = ''; 
	
	?>	
	<div id="<?php echo $wpfw_shortcode['id'].'_window'; ?>" class="wpfw_window_bg">
	<div class="wpfw_window <?php echo $wpfw_shortcode['window_size']; ?>">
	<div class="wpfw_window_title"><div class="close">&#10060;</div><?php echo $wpfw_shortcode['window_title']; ?></div>
	<div id="<?php echo $wpfw_shortcode['id']; ?>" class="wpfw_window_container">
	<?php
		wpfw_shortcode_settings($wpfw_shortcode);
	?>
	</div>
	<div class="wpfw_window_buttons">
		<button class="btn">Insert Shortcode</button>
	</div>
	</div>
	</div>
	<?php
	}

}
add_filter('admin_footer', 'build_shortcodes_window');
}

if(!function_exists("wpfw_shortcode_settings")) {
function wpfw_shortcode_settings($wpfw_shortcode) {
	global $wpdb;
	
		$fields = $wpfw_shortcode['settings'];
		
		foreach($fields as $key => $field) {
			$default_value = $field['def'];
			if (isset($field['parent'])) { $parent = 'data-parent="'.$field['parent'].'"'; } else { $parent = ''; }
			switch($field['type']) {
				case 'textfield':
					?>
						<fieldset>
							<label><?php echo $field['name']; ?></label>
							<input <?php echo $parent; ?> type="text" name="<?php echo $key; ?>" value="<?php echo $default_value; ?>" style="width: 100%;" />
						</fieldset>
					<?php
				break;
				case 'halftextfield':
					?>
						<fieldset>
							<label style="width: 150px; display: block; float: left; clear: left;"><?php echo $field['name']; ?></label>
							<input <?php echo $parent; ?> type="text" name="<?php echo $key; ?>" value="<?php echo $default_value; ?>" style="width: 50%;" />
						</fieldset>
					<?php
				break;			
				case 'textarea':
					?>
						<fieldset>
							<label><?php echo $field['name']; ?></label>
							<textarea <?php echo $parent; ?> name="<?php echo $key; ?>" style="width: 100%;  height: 150px;"><?php echo $default_value; ?></textarea>
						</fieldset>
					<?php
				break;		
				case 'textarealist':
					?>
						<fieldset>
							<label><?php echo $field['name']; ?></label>
							<textarea <?php echo $parent; ?> name="<?php echo $key; ?>" style="width: 100%;"><?php echo $default_value; ?></textarea>
						</fieldset>
					<?php
				break;				
				case 'selectbox':
					?>
						<fieldset>
							<label style="width: 150px; display: block; float: left; clear: left;"><?php echo $field['name']; ?></label>
							<select <?php echo $parent; ?> name="<?php echo $key; ?>">
								<?php
								foreach($field['values'] as $key => $value) {
								?>
									<option value="<?php echo $key; ?>" <?php if ($default_value == $key) { echo ' selected="selected"'; } ?>><?php echo $value; ?></option>
								<?php
								}
								?>
							</select>
						</fieldset>
					<?php
				break;			
			}
		}
		if (isset($field['help'])) {
		?>
		<p class="description"><?php echo $field['help']; ?></p>
		<?php
		}	
	
}
}

?>