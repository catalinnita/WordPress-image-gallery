<?php
/*
Plugin Name: WPFW - Image Galleries
Plugin URI: http://www.WordpressForward.com
Description: Manage wordpress albums and galleries
Version: 1.0.1
Author: Catalin Nita
Author URI: http://www.WordpressForward.com
License: GNU General Public License v2 or later
*/
include('settings.php');
include('functions.php');
include('data.php');
include('optionspage.php');

function wpfw_get_all_galleries() {
	global $wpdb;
	
	$galleries = $wpdb->get_results("SELECT * FROM es_photo_galleries");
	$out = array();
	foreach($galleries as $gallery) {
		$out[$gallery->ID] = $gallery->GalleryTitle;
	}
	
	return $out;
	
}

function wpfw_get_all_albums() {
	global $wpdb;
	
	$albums = $wpdb->get_results("SELECT * FROM es_photo_albums");
	$out = array();
	foreach($albums as $album) {
		$out[$album->ID] = $album->AlbumTitle;
	}
	
	return $out;
	
}

include('shortcodes.php');

// import from old version
if (isset($_GET['importgalleries']) && $_GET['importgalleries'] == 1) {
$calbums = $wpdb->get_results("SELECT * FROM es_photo_galleries");
foreach($calbums as $calbum) {
	if($calbum->AlbumID != 0) {
		$wpdb->query("INSERT INTO es_galleries_albums (AlbumID, GalleryID) VALUES (".$calbum->AlbumID.", ".$calbum->ID.")");
		$wpdb->query("UPDATE es_photo_galleries SET AlbumID = 0");
	}
}
$cgalleries = $wpdb->get_results("SELECT * FROM es_photos");
foreach($cgalleries as $cgallery) {
	if($cgallery->GalleryID != 0) {
		$wpdb->query("INSERT INTO es_photos_galleries (GalleryID, PhotoID) VALUES (".$cgallery->GalleryID.", ".$cgallery->ID.")");
		$wpdb->query("UPDATE es_photos SET GalleryID = 0");
	}
}
}

// check and insert default album and gallery
$checkalbum = $wpdb->get_var($wpdb->prepare("SELECT ID FROM es_photo_albums", "" ));					
$checkgallery = $wpdb->get_var($wpdb->prepare("SELECT ID FROM es_photo_galleries", "" ));

if (!$checkalbum) {
	$wpdb->query("INSERT INTO es_photo_albums (AlbumTitle, AlbumDescription) VALUES ('Uncategorized', 'Here will be displayed all unattached galleries.')");
}

if (!$checkgallery) {
	$AlbumID = $wpdb->get_var($wpdb->prepare("SELECT ID FROM es_photo_albums WHERE AlbumTitle = 'Uncategorized'" ));	
	$wpdb->query("INSERT INTO es_photo_galleries (GalleryTitle, GalleryDescription) 
									VALUES ('Uncategorized', 'Here will be displayed all unattached pictures.')");
	$GalleryID = $wpdb->insert_id;
	$wpdb->query("INSERT INTO es_galleries_albums (GalleryID, AlbumID)
									VALUES (".$GalleryID.", ".$AlbumID.")"); 									
}

// add admin menu link
function manage_photos_menu() {
	add_menu_page('Photos', 'Photo Gallery', 'administrator', 'Photos', 'manage_photos', '');
}
add_action('admin_menu', 'manage_photos_menu');  

// admin manage photos functions
function manage_photos() {
	global $wpdb;
	
	if (isset($_GET['Album']) && !isset($_GET['Gallery'])) {
		manage_galleries();	
	}
	if (isset($_GET['Gallery'])) {
		manage_pictures();
	}
	if (!isset($_GET['Album']) && !isset($_GET['Gallery'])) {
		manage_albums();
	}
	
}

function manage_albums() {
	global $wpdb;
	
	if (isset($_POST['AddAlbum']) && $_POST['AddAlbum'] == 1) {
		add_album();
	}
	
	if (isset($_POST['UpdateAlbum']) && $_POST['UpdateAlbum'] == 1) {
		update_albums();
	}	
	
	$content = '<div class="wrap">';
	$content .= '<div id="icon-themes" class="icon32"><br /></div>';
	$content .= '<h2>Photo albums</H2><br/>';
	
	$albums = $wpdb->get_results("SELECT * FROM es_photo_albums ORDER BY SortOrder ASC");
	
	$content .= '<form id="WPFWManageForm" name="Update" action="admin.php?page=Photos" method="post">';
	$content .= '<input type="hidden" name="UpdateAlbum" value="1" />';
	$content .= '<div id="SortableElementsContainer">';
	$content .= '<ul id="SortableElements">';
	$nr = 0;
	foreach($albums as $album) {
	
	$gallery = $wpdb->get_results("SELECT GalleryID FROM es_galleries_albums WHERE AlbumID = ".$album->ID." ORDER BY SortOrder ASC LIMIT 0,1");
	if(isset($gallery[0]->GalleryID)) {
	$photo = $wpdb->get_results("SELECT es_photos.PostID 
																		FROM es_photos, es_photos_galleries 
																			WHERE es_photos_galleries.GalleryID = ".$gallery[0]->GalleryID." 
																				AND es_photos_galleries.PhotoID = es_photos.ID  
																					ORDER BY es_photos_galleries.SortOrder ASC LIMIT 0,1");

	}
	$content .= '<li id="SO'.$album->ID.'" class="photo_album">';
	$content .= '<input type="hidden" name="SO'.$album->ID.'P" id="SO'.$album->ID.'P" value="'.$nr.'" />';
	$gcount = $wpdb->get_var( "SELECT COUNT(*) FROM es_galleries_albums WHERE AlbumID = ".$album->ID);
	$pcount = $wpdb->get_var( "SELECT COUNT(es_photos_galleries.ID) FROM es_photos_galleries, es_galleries_albums 
																	WHERE es_galleries_albums.AlbumID = ".$album->ID."
																		AND es_photos_galleries.GalleryID = es_galleries_albums.GalleryID");
	$content .= '<div class="typcn typcn-arrow-move move"></div>';
	$content .= '<div class="galleryInfo">'.$gcount.' Galleries&nbsp;&nbsp;&nbsp;'.$pcount.' Photos</div>';

	if (isset($photo[0]->PostID)) {
		$photo = wp_get_attachment_image_src($photo[0]->PostID , 'gallery-admin'); 
		$photo = $photo[0];
	}
	else {
		$photo = plugins_url( 'images/admin/default_photo.png' , __FILE__ );
	}
	$content .= '<a href="admin.php?page=Photos&Album='.$album->ID.'"><img src="'.$photo.'" border="0" width="200" height="150" /></a>';
	$content .= '<input class="pg-title" type="text" name="album-title-'.$album->ID.'" value="'.$album->AlbumTitle.'" placeholder="Album Title" />';
	$content .= '<textarea class="pg-desc" name="album-description-'.$album->ID.'"  title="album description" placeholder="Album Description">'.$album->AlbumDescription.'</textarea>';
	$content .= '<div>';
	$content .= '<input type="hidden" id="del-'.$album->ID.'" name="del-'.$album->ID.'" value="off" />';
	$content .= '<a id="D'.$album->ID.'" class="del-button del-albums" href="#">Remove</a>';
	$content .= '</div>';
	$content .= '</li>';
	$nr++;
	}
	$content .= '</ul>';
	$content .= '</div>';
	$content .= '</form>';
	
	$content .= '<div id="WPFWAddButton" class="add_new">';
	$content .= '<form id="WPFWAddForm" name="add" action="admin.php?page=Photos" method="post">';
	$content .= '<input type="hidden" name="AddAlbum" value="1" />';
	$content .= '<div class="plus typcn typcn-folder-add">';
	$content .= '<div class="loading_bar"></div>';
	$content .= '</div>';
	$content .= '</form>';
	$content .= '</div>';	
	//$content .= '<input type="button" value="Update Albums Info" class="button-secondary" onclick="document.Update.submit();">';
	
	$content .= '</div>';			
	
	echo $content;
	
}

function manage_galleries() {
	global $wpdb;
	
	if (isset($_POST['AddGallery']) && $_POST['AddGallery'] == 1) {
		add_gallery();
	}	
	if (isset($_POST['UpdateGallery']) && $_POST['UpdateGallery'] == 1) {
		update_galleries();
	}	
	if(isset($_POST['ImportGallery']) && $_POST['ImportGallery'] == 1) {
		import_gallery();
	}
	
	
	$album = $wpdb->get_results("SELECT * FROM es_photo_albums WHERE ID = ".$_GET['Album']);
	
	$content = '<div class="wrap">';
	$content .= '<div id="icon-themes" class="icon32"><br /></div>';
	$content .= '<h2><a href="admin.php?page=Photos">Photo albums</a> > Album '.$album[0]->AlbumTitle.'</H2><br/>';
	
	$galleries = $wpdb->get_results("SELECT es_photo_galleries.* FROM es_photo_galleries, es_galleries_albums
																			 WHERE es_galleries_albums.AlbumID = ".$_GET['Album']."
																			 		AND es_photo_galleries.ID = es_galleries_albums.GalleryID
																			 			ORDER BY es_galleries_albums.SortOrder ASC");
	

	$content .= '<form id="WPFWManageForm" name="Update" action="admin.php?page=Photos&Album='.$_GET['Album'].'" method="post">';
	$content .= '<input type="hidden" name="UpdateGallery" value="1" />';
	$content .= '<div id="SortableElementsContainer">';
	$content .= '<ul id="SortableElements">';	
	$nr = 0;
	foreach($galleries as $gallery) {
	
	$content .= '<li id="SO'.$gallery->ID.'" class="photo_gallery">';
	$content .= '<input type="hidden" name="SO'.$gallery->ID.'P" id="SO'.$gallery->ID.'P" value="'.$nr.'" />';		
	$pcount = $wpdb->get_var( "SELECT COUNT(*) FROM es_photos_galleries
															WHERE GalleryID = ".$gallery->ID);
	$content .= '<div class="typcn typcn-arrow-move move"></div>';
	$content .= '<div class="galleryInfo">'.$pcount.' Photos</div>';
		
	$photo = $wpdb->get_results("SELECT es_photos.PostID 
																		FROM es_photos, es_photos_galleries 
																			WHERE es_photos_galleries.GalleryID = ".$gallery->ID." 
																				AND es_photos_galleries.PhotoID = es_photos.ID  
																					ORDER BY es_photos_galleries.SortOrder ASC LIMIT 0,1");

	if (isset($photo[0]->PostID)) {
		$photo = wp_get_attachment_image_src($photo[0]->PostID , 'gallery-admin'); 
		$photo = $photo[0];
	}
	else {
		$photo = plugins_url( 'images/admin/default_photo.png' , __FILE__ );
	}	
	
	
	
	$content .= '<a href="admin.php?page=Photos&Album='.$_GET['Album'].'&Gallery='.$gallery->ID.'"><img src="'.$photo.'" border="0"></a>';
	$content .= '<input class="pg-title" type="text" name="gallery-title-'.$gallery->ID.'" value="'.$gallery->GalleryTitle.'" placeholder="Gallery Title" />';
	$content .= '<textarea class="pg-desc" name="gallery-description-'.$gallery->ID.'"  placeholder="Gallery Description">'.$gallery->GalleryDescription.'</textarea>';
	$content .= '<div class="buttons">';
	$content .= '<input class="delinput" type="hidden" id="del-'.$gallery->ID.'" name="del-'.$gallery->ID.'" value="off" />';
	$content .= '<input class="delfinput" type="hidden" id="delf-'.$gallery->ID.'" name="delf-'.$gallery->ID.'" value="off" />';
	$content .= '<a id="DF'.$gallery->ID.'" class="del-button-f" href="#" title="Remove this gallery FOREVER">&#59177;</a>';
	$content .= '<a id="D'.$gallery->ID.'" class="del-button" href="#">Remove From Album</a>';
	$content .= '</div>';
	
	$content .= '</li>';
	
	$nr++;
	
	}
	$content .= '</ul>';
	$content .= '</div>';
	$content .= '</form>';
	
	$content .= '<div class="add_import">';
	
	$content .= '<div id="WPFWAddButton" class="add_new half top">';
	$content .= '<form id="WPFWAddForm" name="add" action="admin.php?page=Photos&Album='.$_GET['Album'].'" method="post">';
	$content .= '<input type="hidden" name="AddGallery" value="1" />';
	$content .= '<div class="plus typcn typcn-folder-add"><div class="loading_bar"></div></div>';
	$content .= '</form>';
	$content .= '</div>';	
	
	$content .= '<div id="WPFWImportButton" class="add_new half bottom">';
	$content .= '<div class="plus typcn typcn-folder"></div>';
	$content .= '</div>';	
	

	$content .= '</div>';			
	$content .= '<div id="ImportWindow">';
	$content .= '<div id="ImportWindowContainer">';
	$content .= '<div id="ImportWindowTitle"><div class="close">&#10060;</div>Import galleries in Album '.$album[0]->AlbumTitle.'</div>';
	$content .= '<div id="ImportGalleriesContainer">';
	$content .= '<div class="loading_bar"></div>';
	$content .= '<div id="ImportGalleries">';
	$content .= '<form id="WPFWImportForm" name="import" action="admin.php?page=Photos&Album='.$_GET['Album'].'" method="post">';
	$content .= '<input type="hidden" name="ImportGallery" value="1" />';
	$importgalleries = $wpdb->get_results("SELECT * FROM es_photo_galleries ORDER BY GalleryTitle ASC");
	$content .= '<ul id="ImportList">';
	foreach ($importgalleries as $gallery) {
		$check = $wpdb->get_results("SELECT ID FROM es_galleries_albums WHERE GalleryID = ".$gallery->ID." AND AlbumID = ".$_GET['Album']);
		if (!isset($check[0]->ID)) {
		$content .= '<li class="photo_gallery">';
		$pcount = $wpdb->get_var( "SELECT COUNT(*) FROM es_photos_galleries
																WHERE GalleryID = ".$gallery->ID);
		$content .= '<div class="galleryInfo">'.$pcount.' Photos</div>';
			
		$photo = $wpdb->get_results("SELECT es_photos.PostID 
																			FROM es_photos, es_photos_galleries 
																				WHERE es_photos_galleries.GalleryID = ".$gallery->ID." 
																					AND es_photos_galleries.PhotoID = es_photos.ID  
																						ORDER BY es_photos_galleries.SortOrder ASC LIMIT 0,1");
	
		if (isset($photo[0]->PostID)) {
			$photo = wp_get_attachment_image_src($photo[0]->PostID , 'gallery-admin'); 
			$photo = $photo[0];
		}
		else {
			$photo = plugins_url( 'images/admin/default_photo.png' , __FILE__ );
		}	
		
		
		
		$content .= '<a href="admin.php?page=Photos&Album='.$_GET['Album'].'&Gallery='.$gallery->ID.'"><img src="'.$photo.'" border="0" width="200" height="150" /></a>';
		$content .= '<input disabled="disabled" class="pg-title disabled" type="text" name="gallery-title-'.$gallery->ID.'" value="'.$gallery->GalleryTitle.'" placeholder="Gallery Title" />';
		$content .= '<textarea disabled="disabled" class="pg-desc disabled" name="gallery-description-'.$gallery->ID.'"  placeholder="Gallery Description">'.$gallery->GalleryDescription.'</textarea>';
		$content .= '<div class="buttons">';
		$content .= '<input class="addinput" type="hidden" id="add-'.$gallery->ID.'" name="add-'.$gallery->ID.'" value="off" />';
		$content .= '<input class="delfiinput" type="hidden" id="delfi-'.$gallery->ID.'" name="delfi-'.$gallery->ID.'" value="off" />';
		$content .= '<a id="DFI'.$gallery->ID.'" class="del-button-fi" href="#" title="Remove this gallery FOREVER">&#59177;</a>';		
		$content .= '<a id="A'.$gallery->ID.'" class="add-button" href="#">Add To Album</a>';
		$content .= '</div>';
		
		$content .= '</li>';		
		}
	}
	$content .= '</ul>';
	$content .= '</form>';
	$content .= '</div>';
	$content .= '</div>';
	$content .= '</div>';
	$content .= '</div>';
	
	echo $content;
	
}

function import_gallery() {
	global $wpdb;
	
	$allgalleries = $wpdb->get_results("SELECT * FROM es_photo_galleries ORDER BY GalleryTitle ASC");
	foreach ($allgalleries as $allgal) {
		if(isset($_POST['add-'.$allgal->ID]) && $_POST['add-'.$allgal->ID] == 'on') {
			$check = $wpdb->get_results("SELECT ID FROM es_galleries_albums WHERE GalleryID = ".$allgal->ID." AND AlbumID = ".$_GET['Album']);
			if(!isset($check[0]->ID)) {
				$max_so = $wpdb->get_results("SELECT SortOrder FROM es_galleries_albums WHERE AlbumID = ".$_GET['Album']." ORDER BY SortOrder DESC limit 0,1");
				if(isset($max_so[0]->SortOrder)) {
					$nso = $max_so[0]->SortOrder+1;
				}
				else {
					$nso = 0;
				}	
				
				$wpdb->query("INSERT INTO es_galleries_albums (GalleryID, AlbumID, SortOrder)
													VALUES (".$allgal->ID.", ".$_GET['Album'].", ".$nso.")");
			}
			
		}
		if(isset($_POST['delfi-'.$allgal->ID]) && $_POST['delfi-'.$allgal->ID] == 'on') {
			$wpdb->query("DELETE FROM es_galleries_albums WHERE GalleryID = ".$allgal->ID);
			$wpdb->query("DELETE FROM es_photo_galleries WHERE ID = ".$allgal->ID);
			$wpdb->query("DELETE FROM es_photos_galleries WHERE GalleryID = ".$allgal->ID);
			
		}
		
	}
	
	
}

function add_album() {
	global $wpdb;
	
	$max_so = $wpdb->get_results("SELECT SortOrder FROM es_photo_albums ORDER BY SortOrder DESC limit 0,1");
	if(isset($max_so[0]->SortOrder)) {
		$nso = $max_so[0]->SortOrder+1;
	}
	else {
		$nso = 0;
	}
	
	$wpdb->query("INSERT INTO es_photo_albums (AlbumTitle, AlbumDescription, SortOrder) VALUES ('', '', ".$nso.")");
	
}

function update_albums() {
	global $wpdb;
	
	$albums = $wpdb->get_results("SELECT * FROM es_photo_albums");
	if ($albums[0]->ID) {
		foreach($albums as $album)	{
			$wpdb->query("UPDATE es_photo_albums SET
											AlbumTitle = '".$_POST['album-title-'.$album->ID]."',
											AlbumDescription = '".$_POST['album-description-'.$album->ID]."',
											SortOrder = ".$_POST['SO'.$album->ID.'P']."
												WHERE ID = ".$album->ID);		
		}
		
		foreach($albums as $album)	{
			if (isset($_POST['del-'.$album->ID]) && $_POST['del-'.$album->ID] == 'on') {
				$wpdb->query("DELETE FROM es_photo_albums WHERE ID = ".$album->ID);
				$wpdb->query("DELETE FROM es_galleries_albums WHERE AlbumID = ".$album->ID);
			}
		}			
	}
		
}

function add_gallery() {
	global $wpdb;
	
	$max_so = $wpdb->get_results("SELECT SortOrder FROM es_galleries_albums WHERE AlbumID = ".$_GET['Album']." ORDER BY SortOrder DESC limit 0,1");
	if(isset($max_so[0]->SortOrder)) {
		$nso = $max_so[0]->SortOrder+1;
	}
	else {
		$nso = 0;
	}
	
	$wpdb->query("INSERT INTO es_photo_galleries 
										(GalleryTitle, GalleryDescription) 
										VALUES ('', '')");
	$GalleryID = $wpdb->insert_id;
	$wpdb->query("INSERT INTO es_galleries_albums
										(AlbumID, GalleryID, SortOrder)
											VALUES (".$_GET['Album'].", ".$GalleryID.", ".$nso.")");
	
}

function update_galleries() {
	global $wpdb;
	
	$galleries = $wpdb->get_results("SELECT es_photo_galleries.* FROM es_photo_galleries, es_galleries_albums 
																				WHERE es_galleries_albums.AlbumID = ".$_GET['Album']."
																						AND es_galleries_albums.GalleryID = es_photo_galleries.ID");
	
	if ($galleries[0]->ID) {
		foreach($galleries as $gallery)	{
			$wpdb->query("UPDATE es_photo_galleries SET
											GalleryTitle = '".$_POST['gallery-title-'.$gallery->ID]."',
											GalleryDescription = '".$_POST['gallery-description-'.$gallery->ID]."'
												WHERE ID = ".$gallery->ID);		
												
			$wpdb->query("UPDATE es_galleries_albums SET 
											SortOrder = ".$_POST['SO'.$gallery->ID.'P']." 
												WHERE AlbumID = ".$_GET['Album']."
													AND GalleryID = ".$gallery->ID);
		
			if (isset($_POST['del-'.$gallery->ID]) && $_POST['del-'.$gallery->ID] == 'on') {
				$wpdb->query("DELETE FROM es_galleries_albums WHERE AlbumID = ".$_GET['Album']." AND GalleryID = ".$gallery->ID);
			}
			
			if(isset($_POST['delf-'.$gallery->ID]) && $_POST['delf-'.$gallery->ID] == 'on') {
				$wpdb->query("DELETE FROM es_galleries_albums WHERE GalleryID = ".$gallery->ID);
				$wpdb->query("DELETE FROM es_photo_galleries WHERE ID = ".$gallery->ID);
				$wpdb->query("DELETE FROM es_photos_galleries WHERE GalleryID = ".$gallery->ID);
				
			}			
			
		}		
	}
	
	
		
}

function manage_pictures() {
	global $wpdb;
	if (isset($_POST['AddImage']) && $_POST['AddImage'] == 1) {
		add_pictures();
	}	
	if (isset($_POST['UpdatePictures']) &&$_POST['UpdatePictures'] == 1) {
		update_pictures();
	}	
	if(isset($_POST['ImportPictures']) &&$_POST['ImportPictures'] == 1) {
		import_photos();
	}
	
	$album = $wpdb->get_results("SELECT * FROM es_photo_albums WHERE ID = ".$_GET['Album']);
	$gallery = $wpdb->get_results("SELECT * FROM es_photo_galleries WHERE ID = ".$_GET['Gallery']);
	
	$content = '<div class="wrap">';
	$content .= '<div id="icon-themes" class="icon32"><br /></div>';
	$content .= '<h2><a href="admin.php?page=Photos">Photo albums</a> > <a href="admin.php?page=Photos&Album='.$album[0]->ID.'">Album '.$album[0]->AlbumTitle.'</a> > Gallery '.$gallery[0]->GalleryTitle.'</H2><br/>';
	
	$pictures = $wpdb->get_results("SELECT es_photos.* FROM es_photos, es_photos_galleries 
																			WHERE es_photos_galleries.GalleryID = ".$_GET['Gallery']."
																				AND es_photos_galleries.PhotoID = es_photos.ID
																					ORDER BY es_photos_galleries.SortOrder ASC");
	

	$content .= '<form id="WPFWManageForm" name="Update" action="admin.php?page=Photos&Album='.$_GET['Album'].'&Gallery='.$_GET['Gallery'].'" method="post">';
	$content .= '<input type="hidden" name="UpdatePictures" value="1" />';
	$content .= '<div id="SortableElementsContainer">';
	$content .= '<ul id="SortableElements">';
	$nr = 0;
	foreach($pictures as $picture) {
	
	$image_attributes = wp_get_attachment_image_src( $picture->PostID , 'gallery-admin');
	
	$content .= '<li id="SO'.$picture->ID.'" class="photo_gallery">';
	$content .= '<input type="hidden" name="SO'.$picture->ID.'P" id="SO'.$picture->ID.'P" value="'.$nr.'" />';
	$content .= '<div class="typcn typcn-arrow-move move"></div>';
	
	$content .= '<img src="'.$image_attributes[0].'" border="0" width="200" height="150" />';
	$content .= '<input class="pg-title" type="text" name="picture-title-'.$picture->ID.'" value="'.$picture->PhotoTitle.'" placeholder="Picture Title" />';
	$content .= '<textarea class="pg-desc" name="picture-description-'.$picture->ID.'" placeholder="Picture Description">'.$picture->PhotoDescription.'</textarea>';
	$content .= '<textarea class="pg-video" name="picture-video-'.$picture->ID.'"  placeholder="Video Link">'.$picture->VideoLink.'</textarea>';
	
	$content .= '<div class="buttons">';
	$content .= '<input type="hidden" id="del-'.$picture->ID.'" name="del-'.$picture->ID.'" value="off" />';
	$content .= '<input class="delfinput" type="hidden" id="delf-'.$picture->ID.'" name="delf-'.$picture->ID.'" value="off" />';
	$content .= '<a id="DF'.$picture->ID.'" class="del-button-f" href="#" title="Remove this picture FOREVER">&#59177;</a>';	
	$content .= '<a id="D'.$picture->ID.'" class="del-button" href="#">Remove From Gallery</a>';
	$content .= '</div>';
	
	$content .= '</li>';
		
	$nr++;

	}
	$content .= '</ul>';
	$content .= '</div>';
	$content .= '</form>';

	
	$content .= '<div class="add_import">';
	
	$content .= '<div id="WPFWAddButton" class="add_new half top na">';
	$content .= '<form id="WPFWAddForm" name="add" action="admin.php?page=Photos&Album='.$_GET['Album'].'&Gallery='.$_GET['Gallery'].'" method="post">';
	$content .= '<input type="hidden" name="AddImage" value="1" />';
	$content .= '<input id="ImageName" type="hidden" name="ImageName" value="" />';
	
	$content .= '<div class="drop_area">';
	$content .= '<div class="text">DROP YOUR FILES HERE <br/>or<br/></div>';
	$content .= '<div id="WPFWBrowseButton">';
	$content .= '<span>Browse</span>';
	$content .= '<input id="fileupload" type="file" name="files[]" multiple />';
	$content .= '</div>';
	$content .= '</div>';	
	$content .= '<div class="loading_bar_container"><div class="loading_bar"></div></div>';
	$content .= '</form>';	
	$content .= '</div>';	
	
	$content .= '<div id="WPFWImportButton" class="add_new half bottom">';
	$content .= '<div class="plus typcn typcn-folder"></div>';
	$content .= '</div>';	
	$content .= '</div>';	
	

	echo $content;
	$url = plugins_url("wpfw_uploads/upload.php?Gallery=".$_GET['Gallery'] , __FILE__ );
	?>
		<script>
		jQuery(function ($) {
    'use strict';
    var url = '<?php echo $url; ?>';
    $('#fileupload').fileupload({
        maxChunkSize: 40000000,
        url: url,
        acceptFileTypes: '/(\.|\/)(gif|jpe?g|png)$/i',
        dataType: 'json',
        dropZone: $("#WPFWAddButton"),
        drop: function(e, data) { $("#WPFWAddButton").removeClass("over"); },
        start: function (e, data) {
        	$("#WPFWAddButton").removeClass("error").addClass("loading");  
        },
        done: function (e, data) {
            console.log(data);
            $.each(data.result.files, function (index, file) {
                $("#ImageName").val(file.name);
                $("#WPFWAddForm").submit();
                //$('<p/>').text(file.name).appendTo('#FilesList');
            });
            //$("#WPFWAddButton").removeClass("loading");
        },
        fail: function (e, data) {
        	console.log(data);
        	$("#WPFWAddButton").addClass("error");  
        }
    });
    
    $('#WPFWAddButton').on("dragover", function(e) { 
    	$("#WPFWAddButton").addClass("over"); 
    });
    $('#WPFWAddButton').on("dragleave", function(e) { 
    	$("#WPFWAddButton").removeClass("over"); 
    });
	
	});
	</script>	
	<?php
	
		


	$content = '<div id="ImportWindow">';
	$content .= '<div id="ImportWindowContainer">';
	$content .= '<div id="ImportWindowTitle"><div class="close">&#10060;</div>Import pictures in Gallery '.$gallery[0]->GalleryTitle.'</div>';
	$content .= '<div id="ImportGalleriesContainer">';
	$content .= '<div class="loading_bar"></div>';
	$content .= '<div id="ImportGalleries">';
	$content .= '<form id="WPFWImportForm" name="import" action="admin.php?page=Photos&Album='.$_GET['Album'].'&Gallery='.$_GET['Gallery'].'" method="post">';
	$content .= '<input type="hidden" name="ImportPictures" value="1" />';
	$importpictures = $wpdb->get_results("SELECT * FROM es_photos ORDER BY ID DESC");
	$content .= '<ul id="ImportList">';
	foreach ($importpictures as $picture) {
		$check = $wpdb->get_results("SELECT PhotoID FROM es_photos_galleries WHERE GalleryID = ".$_GET['Gallery']." AND PhotoID = ".$picture->ID);
		if (!isset($check[0]->ID)) {
		$content .= '<li class="photo_gallery">';
			
		$photo = wp_get_attachment_image_src($picture->PostID , 'gallery-admin'); 
		$photo = $photo[0];
		if (!isset($photo)) {
			$photo = plugins_url( 'images/admin/default_photo.png' , __FILE__ );
		}			
		
		$content .= '<img src="'.$photo.'" border="0" width="200" height="150" />';
		$content .= '<input disabled="disabled" class="pg-title disabled" type="text" name="picture-title-'.$picture->ID.'" value="'.$picture->PhotoTitle.'" placeholder="Picture Title" />';
		$content .= '<textarea disabled="disabled" class="pg-desc disabled" name="picture-description-'.$picture->ID.'" placeholder="Picture Description">'.$picture->PhotoDescription.'</textarea>';
		
		$content .= '<div class="buttons">';
		$content .= '<input class="addinput" type="hidden" id="add-'.$picture->ID.'" name="add-'.$picture->ID.'" value="off" />';
		$content .= '<input class="delfiinput" type="hidden" id="delfi-'.$picture->ID.'" name="delfi-'.$picture->ID.'" value="off" />';
		$content .= '<a id="DFI'.$picture->ID.'" class="del-button-fi" href="#" title="Remove this picture FOREVER">&#59177;</a>';		
		$content .= '<a id="A'.$picture->ID.'" class="add-button" href="#">Add To Gallery</a>';
		$content .= '</div>';
		
		$content .= '</li>';		
		}
	}
	$content .= '</ul>';
	$content .= '</form>';
	$content .= '</div>';
	$content .= '</div>';
	$content .= '</div>';
	$content .= '</div>';
	
	$content .= '</div>';		
	
	echo $content;	
	
}

function add_pictures() {
	global $wpdb;
	
	$name = $_POST['ImageName'];
	$upload_dir = wp_upload_dir();
	$abs_uploads_dir = $upload_dir['basedir'].$upload_dir['subdir'];
	
	$wp_filetype = wp_check_filetype(basename($name), null );
			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title' => preg_replace('/\.[^.]+$/', '', basename($name)),
				'post_content' => '',
				'post_status' => 'inherit'
	);
			
	$attach_id = wp_insert_attachment( $attachment, substr($upload_dir['subdir'],1).'/'.$name);
		
	$attach_data = wp_generate_attachment_metadata($attach_id, $abs_uploads_dir.'/'.$name);
	wp_update_attachment_metadata($attach_id, $attach_data);
	
	$wpdb->query("INSERT INTO es_photos 
										(PostID) 
											VALUES (".$attach_id.")");
											
	$wpdb->query("INSERT INTO es_photos_galleries 
										(GalleryID, PhotoID, SortOrder) 
											VALUES (".$_GET['Gallery'].", ".$wpdb->insert_id.", 0)");
	
}

function import_photos() {
	global $wpdb;
	
	$allphotos = $wpdb->get_results("SELECT * FROM es_photos");
	foreach ($allphotos as $allphoto) {
		if(isset($_POST['add-'.$allphoto->ID]) && $_POST['add-'.$allphoto->ID] == 'on') {
			$check = $wpdb->get_results("SELECT ID FROM es_photos_galleries WHERE GalleryID = ".$_GET['Gallery']." AND PhotoID = ".$allphoto->ID);
			if(!isset($check[0]->ID)) {
				$max_so = $wpdb->get_results("SELECT SortOrder FROM es_photos_galleries WHERE GalleryID = ".$_GET['Gallery']." ORDER BY SortOrder DESC limit 0,1");
				if(isset($max_so[0]->SortOrder)) {
					$nso = $max_so[0]->SortOrder+1;
				}
				else {
					$nso = 0;
				}	
				
				$wpdb->query("INSERT INTO es_photos_galleries (PhotoID, GalleryID, SortOrder)
													VALUES (".$allphoto->ID.", ".$_GET['Gallery'].", ".$nso.")");
			}
			
		}
		if(isset($_POST['delfi-'.$allphoto->ID]) && $_POST['delfi-'.$allphoto->ID] == 'on') {
			$wpdb->query("DELETE FROM es_photos WHERE ID = ".$allphoto->ID);
			$wpdb->query("DELETE FROM es_photos_galleries WHERE PhotoID = ".$allphoto->ID);
			delete_picture($allphoto->PostID);
		}
		
	}
	
	
}

function delete_picture($attachmentID) {
	global $_wp_additional_image_sizes;
	
	foreach($_wp_additional_image_sizes as $key => $value) {
		removefromftp(wp_get_attachment_image_src($attachmentID, $key));
	}
	removefromftp(wp_get_attachment_image_src($attachmentID, 'full'));
	removefromftp(wp_get_attachment_image_src($attachmentID, 'large'));
	removefromftp(wp_get_attachment_image_src($attachmentID, 'medium'));
	removefromftp(wp_get_attachment_image_src($attachmentID, 'thumbnail'));
}

function removefromftp($attarray) {
	
	$upload_dir = wp_upload_dir();
	
	unlink(str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $attarray[0]));
	
}

function update_pictures() {
	global $wpdb;
	
	$pictures = $wpdb->get_results("SELECT es_photos.* FROM es_photos, es_photos_galleries
																		WHERE es_photos_galleries.GalleryID = ".$_GET['Gallery']."
																			AND es_photos.ID = es_photos_galleries.PhotoID");
	if ($pictures[0]->ID) {
		

		
		foreach($pictures as $picture)	{
			
			if ($_POST['picture-video-'.$picture->ID]) {
				$vmcheck = explode("http://vimeo.com/", $_POST['picture-video-'.$picture->ID]);
				if (count($vmcheck) > 1) {
					$videolink = 'http://player.vimeo.com/video/'.$vmcheck[1];
				}
				else {
					$videolink = $_POST['picture-video-'.$picture->ID];
				}		
			}	
			else {
				$videolink = '';
			}	
			
				
			$wpdb->query("UPDATE es_photos SET
											PhotoTitle = '".$_POST['picture-title-'.$picture->ID]."',
											PhotoDescription = '".$_POST['picture-description-'.$picture->ID]."',
											VideoLink = '".$videolink."'
												WHERE ID = ".$picture->ID);	
						
			$wpdb->query("UPDATE es_photos_galleries SET 
											SortOrder = ".$_POST["SO".$picture->ID."P"]."
												WHERE GalleryID = ".$_GET['Gallery']." 
													AND PhotoID = ".$picture->ID);
			
			if (isset($_POST['del-'.$picture->ID]) && $_POST['del-'.$picture->ID] == 'on') {
				$wpdb->query("DELETE FROM es_photos_galleries WHERE PhotoID = ".$picture->ID." AND GalleryID = ".$_GET['Gallery']);
			}
			
			if (isset($_POST['delf-'.$picture->ID]) && $_POST['delf-'.$picture->ID] == 'on') {
				$wpdb->query("DELETE FROM es_photos_galleries WHERE PhotoID = ".$picture->ID);
				$wpdb->query("DELETE FROM es_photos WHERE ID = ".$picture->ID);
				delete_picture($picture->PostID);
			}
		}		
	}
	
	
		
}



function get_p_page() {
	
	if (isset($_GET['page'])) { 
		return $_GET['page']; 
	}
	else {
		$url = explode("/", curPageURL());
		if (is_numeric($url[count($url)-1]) || is_numeric($url[count($url)-2])) {
			if (is_numeric($url[count($url)-1]))
				return $url[count($url)-1];
			
			if (is_numeric($url[count($url)-2]))
				return $url[count($url)-2];
			
		}
		else {
			return false;
		}
	}
	
	
	
}

function wpfw_gallery($atts) {
	global $wpdb;
	extract(shortcode_atts(array(
		'id' => '',
		'type' => 'Grid',
		'cols' => 4
	), $atts));			
	
	$allphotos = $wpdb->get_results("SELECT es_photos.* FROM es_photos, es_photos_galleries 
																			WHERE es_photos_galleries.GalleryID = ".$id." 
																				AND es_photos.ID = es_photos_galleries.PhotoID
																					ORDER BY es_photos_galleries.SortOrder ASC");
	$photos_nr = count($allphotos);
	$posts_per_page = 999; //of_get_option('pppage');
	if (get_p_page()) { $page = get_p_page(); } else { $page = 1; }
	
	$limit = ($page-1)*$posts_per_page.', '.$page*$posts_per_page;
	$nextp = $page+1;
	$prevp = $page-1;
	$total_pages = ceil($photos_nr/$posts_per_page);
	$photos = $wpdb->get_results("SELECT es_photos.* FROM es_photos, es_photos_galleries 
																			WHERE es_photos_galleries.GalleryID = ".$id." 
																				AND es_photos.ID = es_photos_galleries.PhotoID 
																					ORDER BY es_photos_galleries.SortOrder ASC
																						LIMIT ".$limit);
	
	if ($photos[0]->ID) {
		
		$content = '';

	// ********************************** GRID ************************************* //		
	if ($type == 'Grid') {
		$col = 12/$cols;
		$content .= '
		<div id="WPFW-TopFilters" class="QSFilter">
			<ul class="gallery-cats alignright btn-group">
				<li class="btn btn-primary segment-0 selected-1"><a class="active " data-qs="wpfw-all" href="#">All</a></li>
				<li class="btn btn-default segment-1"><a class="" data-qs="wpfw-photo" href="#">Images</a></li>
				<li class="btn btn-default segment-2"><a class="" data-qs="wpfw-video" href="#">Videos</a></li>
			</ul>';
		if (isset($_GET['gallery'])) {
			$content .= '<a href="'.get_permalink(get_the_ID()).'" class="btn btn-default">Back to Galleries</a>';
		}		
		$content .= '</div>
		<div class="cleaner"></div>';		
	
		$content .= '
		<div id="WPFW-Grid-Gallery" class="WPFW-Grid-Gallery QSContainer">
			';
		
		$nr = 1;
		foreach($photos as $photo) {
			
			//if($nr%$cols == 1 && $nr > 1) 
				
			
			$bigimage = wp_get_attachment_image_src( $photo->PostID, 'gallery_popup'); 
			$image = wp_get_attachment_image_src( $photo->PostID , 'gallery1'); 
	  	
	  	if ($photo->VideoLink) {
		  	$content .= '
				<div data-id="id-'.$nr.'" class="col-md-'.$col.' WPFW-GalleryShadow QS wpfw-video">
					<div class="WPFW-GalleryFrame">				
						<a class="WPFW-Frame VIDEO" data-gallery="gallery-'.$id.'" href="'.$photo->VideoLink.'" title="'.$photo->PhotoTitle.'">
							<img class="grayscale" src="'.str_replace(" ", "%20", $image[0]).'" alt="'.$photo->PhotoTitle.'" />
							<span class="hover"><span class="hover_text">PLAY</span></span>
						</a>';
						if ($photo->PhotoTitle) { $content .= '<h2>'.$photo->PhotoTitle.'</h2>'; }
						if ($photo->PhotoDescription) { $content .= '<p>'.$photo->PhotoDescription.'</p>'; }
				$content .= '
					</div>
				</div>';  		
	  	}
	  	else {
		  	$content .= '
				<div data-id="id-'.$nr.'" class="col-md-'.$col.' WPFW-GalleryShadow QS wpfw-photo">
						<div class="WPFW-GalleryFrame">			
						<a class="WPFW-Frame PIC" data-gallery="gallery-'.$id.'" href="'.str_replace(" ", "%20", $bigimage[0]).'" title="'.$photo->PhotoTitle.'">
							<img class="grayscale" src="'.str_replace(" ", "%20", $image[0]).'" alt="'.$photo->PhotoTitle.'" />
							<span class="hover"><span class="hover_text">ZOOM</span></span>
						</a>';
						if ($photo->PhotoTitle) { $content .= '<h2>'.$photo->PhotoTitle.'</h2>'; }
						if ($photo->PhotoDescription) { $content .= '<p>'.$photo->PhotoDescription.'</p>'; }
				$content .= '
					</div>
				</div>
				';  		
			}
			$nr++;
		}
		$content .= '
				<div class="cleaner"></div>
			</div>
		</div>';
	
	}
		
	// ********************************** SLIDER ************************************* //
	if ($type == 'Slider') {
		
		if (isset($_GET['gallery'])) {
			$content .= '<div id="WPFW-TopFilters"><a href="'.get_permalink(get_the_ID()).'" class="button">Back to Galleries</a></div><div class="cleaner"></div>';
		}		

		
		$content .= '<div id="WPFW-Slider-Gallery">
		<ul id="WPFW-Gallery-'.$id.'" class="AS">';
		$nr = 1;
		foreach($photos as $photo) {
			$bigimage = wp_get_attachment_image_src( $photo->PostID, 'gallery_popup'); 
			$image = wp_get_attachment_image_src( $photo->PostID , 'gallery2_big'); 
			
			$content .= '<li><img src="'.str_replace(" ", "%20", $image[0]).'" alt="'.$photo->PhotoTitle.'" />';
											if($photo->PhotoTitle || $photo->PhotoDescription) {
												$content .= '<span class="PictureInfo">';
												
												if($photo->PhotoTitle) $content .= '<span class="PictureTitle">'.$photo->PhotoTitle.'</span>';
												if($photo->PhotoDescription) $content .= '<span class="PictureDesc">'.$photo->PhotoDescription.'</span>';
												
												$content .= '</span><div class="cleaner"></div>';
											}
											$content .= '</li>';
		}
		
		$content .= '
		</ul>
		</div>

		<div id="ThumbsWPFW-Gallery-'.$id.'" class="AST">
		<div class="BottomThumbsContainer">
			<ul class="BottomThumbs JCO">';
					$nr = 1;
					foreach($photos as $photo) {
					$thumb = wp_get_attachment_image_src( $photo->PostID , 'gallery2_thumb'); 
					$content .= '<li><a href="#'.$nr.'"><img class="grayscale" src="'.str_replace(" ", "%20", $thumb[0]).'" alt="'.$photo->PhotoTitle.'" />'.$photo->PhotoTitle.'</a></li>';
					$nr++;
					}
		$content .= '	
			</ul>
		</div>
		</div>';

		}
		// ********************************** VERTICAL ************************************* //
		
		if ($type == 'Vertical') {
			
		if (isset($_GET['gallery'])) {
			$content .= '<div id="WPFW-TopFilters"><a href="'.get_permalink(get_the_ID()).'" class="button">Back to Galleries</a></div><div class="cleaner"></div>';
		}		
			
		$content .= '
		<div class="WPFW-Vertical-Gallery-Container">
			<div id="WPFW-Vertical-Gallery-'.$id.'" class="WPFW-Vertical-Gallery">
				<ul id="WPFW-Gallery-'.$id.'" class="VerticalGallery AS">';
				$nr = 1;
				foreach($photos as $photo) {
					$bigimage = wp_get_attachment_image_src($photo->PostID, 'gallery3_big'); 
					$content .= '<li class="panel'.$nr.' panel '; if ($nr == 1) { $content .= 'active'; } $content .= '"><img src="'.str_replace(" ", "%20", $bigimage[0]).'"  alt="'.$photo->PhotoTitle.'" />';
					if($photo->PhotoTitle || $photo->PhotoDescription) {
							$content .= '<span class="PictureInfo">';
												
							if($photo->PhotoTitle) $content .= '<span class="PictureTitle">'.$photo->PhotoTitle.'</span>';
							if($photo->PhotoDescription) $content .= '<span class="PictureDesc">'.$photo->PhotoDescription.'</span>';
												
							$content .= '</span><div class="cleaner"></div>';
						}
					$content .= '</li>';
					$nr++;
				}
				$content .= '					
			</ul>
			</div>
			<ul id="ThumbsWPFW-Gallery-'.$id.'" class="RightThumbsContainer AST">
				<li class="RightThumbs SP">';
						$nr = 1;
						foreach($photos as $photo) {
						$thumb = wp_get_attachment_image_src( $photo->PostID , 'gallery3_thumb'); 				
						$content .= '<a href="#'.$nr.'" class="WPFW-Vertical-Thumb"><img src="'.str_replace(" ", "%20", $thumb[0]).'" alt="'.$photo->PhotoTitle.'" class="grayscale" /><span></span></a>';
						$nr++;
						}					
						$content .= '
						<div class="cleaner"></div>
				</li>
			</ul>
			<div class="cleaner"></div>
		</div>';
		}

		if ($photos_nr > $posts_per_page) {
		$content .= '
		<div class="cleaner"></div>
		<div class="pagination nomob">
			<div class="divider"></div>';
			
			if ($page < $total_pages) {
			$content .= '<div class="next"><a href="?page='.$nextp.'">Next</a></div>';
			}
			
			if ($page > 1) {
				$content .= '<div class="prev"><a href="?page='.$prevp.'">Back</a></div>'; 
			}
		
	
		}
		}

	
	return $content;
}

add_shortcode("wpfw_gallery", "wpfw_gallery");



function wpfw_small_gallery($atts) {
	global $wpdb;
	extract(shortcode_atts(array(
		'id' => '',
		'container' => 'ul',
		'container_id' => '',
		'container_class' => 'small_gallery',
		'delimiter' => '',
		'nav' => 'false'
	), $atts));			

	$photos = $wpdb->get_results("SELECT es_photos.* FROM es_photos, es_photos_galleries 
																			WHERE es_photos_galleries.GalleryID = ".$id." 
																				AND es_photos.ID = es_photos_galleries.PhotoID");
	
	if ($photos[0]->ID) {

		$content = '';
		$content .= '<'.$container.' id="'.$container_id.'" class="'.$container_class.'">';
		$nr = 1;
		foreach($photos as $photo) {
			$imagesrc = wp_get_attachment_image_src( $photo->PostID, 'gallery_medium'); 
			
			if (!$delimiter) {
				$content .= '<img src="'.$imagesrc[0].'" border="0">';
			}
			else {
				$content .= '<'.$delimiter.'><img src="'.$imagesrc[0].'" border="0"></'.$delimiter.'>';
			}
		}
		$content .= '</'.$container.'>';
		if ($nav == 'true') {
			$content .= '<div id="HomepageGalleryControls">';
				$content .= '<div id="Controls">';
				$nr = 1;
				foreach($photos as $photo) {
					$content .= '<a href="#'.$nr.'" class="nav '; if ($nr == 1) { $content .= 'active'; } $content .= '"></a>';
					$nr++;
				}
				$content .= '</div>';
			$content .= '</div>';
		}
	}
	
	return $content;
}


add_shortcode("wpfw_small_gallery", "wpfw_small_gallery");



function wpfw_album_gallery($atts) {
	global $wpdb;
	extract(shortcode_atts(array(
		'id' => '',
		'type' => 'Grid',
		'cols' => 4
	), $atts));			
	
	$col = 12/$cols;
	
	if (isset($_GET['gallery'])) {
		$content = do_shortcode("[wpfw_gallery id='".$_GET['gallery']."' type='".$type."' cols='".$cols."']");
	}
	else {
		$galleries = $wpdb->get_results("SELECT es_photo_galleries.* FROM es_photo_galleries, es_galleries_albums 
																				WHERE es_galleries_albums.AlbumID = ".$id."
																					AND es_galleries_albums.GalleryID = es_photo_galleries.ID
																						ORDER BY es_galleries_albums.SortOrder ASC");
		$galleries_nr = count($galleries);
		$posts_per_page = 999; //of_get_option('pppage');
		if (get_p_page()) { $page = get_p_page(); } else { $page = 1; }
	
		$limit = ($page-1)*$posts_per_page.', '.$page*$posts_per_page;
		$nextp = $page+1;
		$prevp = $page-1;
		$total_pages = ceil($galleries_nr/$posts_per_page);
	
		$galleries = $wpdb->get_results("SELECT es_photo_galleries.* FROM es_photo_galleries, es_galleries_albums 
																				WHERE es_galleries_albums.AlbumID = ".$id."
																					AND es_galleries_albums.GalleryID = es_photo_galleries.ID
																						ORDER BY es_galleries_albums.SortOrder ASC 
																							LIMIT ".$limit);
	
		if ($galleries[0]->ID) {
	
		$content = '';
		$content .= '
			<div>
				<div id="WPFW-Albums" class="WPFW-Albums">
					<div class="row">';	
				$nr = 1;
				foreach($galleries as $gallery) {
					$photo = $wpdb->get_results("SELECT es_photos.* FROM es_photos, es_photos_galleries 
																				WHERE es_photos_galleries.GalleryID = ".$gallery->ID." 
																					AND es_photos.ID = es_photos_galleries.PhotoID
																						ORDER BY es_photos_galleries.SortOrder ASC");
		
					$image = wp_get_attachment_image_src( $photo[0]->PostID , 'gallery1'); 
  		if($nr%$cols == 1 && $nr > 1) 
					$content .= '</div><div class="row">';
					
				  $content .= '
						<div class="col-md-'.$col.' WPFW-AlbumShadow">
							<div class="WPFW-AlbumFrame">
								<a class="WPFW-Frame" href="'.get_permalink(get_the_ID()).'?gallery='.$gallery->ID.'" >
									<img class="grayscale" src="'.str_replace(" ", "%20", $image[0]).'" alt="'.$gallery->GalleryTitle.'" />
									<span class="hover"><span class="hover_text">BROWSE</span></span>
								</a>
								<h2><a href="'.get_permalink(get_the_ID()).'?gallery='.$gallery->ID.'">'.$gallery->GalleryTitle.'</a></h2>
								<p>'.$gallery->GalleryDescription.'</p>
							</div>
						</div>'; 			
	
					
					$nr++;
	
				}

				if ($galleries_nr > $posts_per_page) {
				$content .= '
					<div class="cleaner"></div>
						<div class="pagination">
							<div class="divider"></div>';
								if ($page < $total_pages) {
									$content .= '<div class="next"><a href="?page='.$nextp.'">Next</a></div>';
								}
								if ($page > 1) {
									$content .= '<div class="prev"><a href="?page='.$prevp.'">Back</a></div>'; 
								}
				}
				$content .='
					</div>
				</div>
			</div>';	
		
		}
	}
	
	return $content;
}

add_shortcode("wpfw_album_gallery", "wpfw_album_gallery");



?>