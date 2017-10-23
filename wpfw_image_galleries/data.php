<?php
$sql = "CREATE TABLE IF NOT EXISTS `es_photo_albums` (
	  				`ID` bigint(20) NOT NULL AUTO_INCREMENT,
	  				`AlbumTitle` varchar(255) NOT NULL,
	  				`AlbumDescription` text NOT NULL,
	  				`SortOrder` bigint(20) NOT NULL,
						PRIMARY KEY (`ID`) 
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
$wpdb->query($sql);
					
$sql = "CREATE TABLE IF NOT EXISTS `es_photo_galleries` (
	  				`ID` bigint(20) NOT NULL AUTO_INCREMENT,
	  				`AlbumID` bigint(20) NOT NULL,
	  				`GalleryTitle` varchar(255) NOT NULL,
	  				`GalleryDescription` text NOT NULL,
	  				`PhotosWidth` varchar(255) NOT NULL,
	  				`PhotosHeight` varchar(255) NOT NULL,
	  				`SortOrder` bigint(20) NOT NULL,
						PRIMARY KEY (`ID`) 
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
$wpdb->query($sql);
					
$sql = "CREATE TABLE IF NOT EXISTS `es_photos` (
	  				`ID` bigint(20) NOT NULL AUTO_INCREMENT,
	  				`GalleryID` bigint(20) NOT NULL,
	  				`PostID` bigint(20) NOT NULL,
						`PhotoTitle` varchar(255) NOT NULL,
						`PhotoDescription` text NOT NULL,
						`VideoLink` varchar(255) NOT NULL,
						`FeaturedGallery` int(2) NOT NULL,	  				
						`FeaturedAlbum` int(2) NOT NULL,
	  				`SortOrder` bigint(20) NOT NULL,
						PRIMARY KEY (`ID`) 
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
$wpdb->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `es_photos_galleries` (
	  				`ID` bigint(20) NOT NULL AUTO_INCREMENT,
	  				`GalleryID` bigint(20) NOT NULL,
	  				`PhotoID` bigint(20) NOT NULL,
	  				`SortOrder` bigint(20) NOT NULL,
						PRIMARY KEY (`ID`) 
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
$wpdb->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `es_galleries_albums` (
	  				`ID` bigint(20) NOT NULL AUTO_INCREMENT,
	  				`AlbumID` bigint(20) NOT NULL,
	  				`GalleryID` bigint(20) NOT NULL,
	  				`SortOrder` bigint(20) NOT NULL,
						PRIMARY KEY (`ID`) 
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
$wpdb->query($sql);

?>