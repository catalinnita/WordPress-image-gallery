<?php
$external = 'true';
include('../functions.php');
require('UploadHandler.php');

set_time_limit(1800);

	$upload_dir = wp_upload_dir();
	$abs_uploads_dir = $upload_dir['basedir'].$upload_dir['subdir'];

	$options = array(
		'upload_dir' => $abs_uploads_dir.'/',
		'upload_url' => $upload_dir['url'].'/',
		'mkdir_mode' => 755,
		'readfile_chunk_size' => 41943040
	);

	$files = new UploadHandler($options);
	

?>