<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| Phoko configuration
| -------------------------------------------------------------------
| This file specifies everything we need to know about the KPA instance
| and other files or directories that Phoko might want to use.
|
| -------------------------------------------------------------------
| Instructions
| -------------------------------------------------------------------
|
| On a new installation, you must modify the following
|
| 1.
| 2.
|
*/

/*
| -------------------------------------------------------------------
|
| -------------------------------------------------------------------
|
|
*/



/**
 *  Name
 *
 *  Visible name of the phoko album instance
 **/
// $config['name'] = "Phoko Album";
$config['name'] = "The Jedd Gallery";


/**
 *  Repository
 *
 *  Path to the base of your image collection.
 *
 *  This MUST match the directory that KPA knows about as
 *  the root dir of your image collection.
 *
 *  This MUST have a trailing slash.
 **/
// $config['repository'] = "/secret/path/to/picture/repository/";
$config['repository'] = "/home/garden/pictures/";


/**
 *  Index.xml
 *
 *  This is automatically calculated - there is nothing here for you to do.
 *  To clarify - DO NOT MODIFY THIS ITEM.
 **/
$config['index_xml_file'] = $config['repository'] . "index.xml";


/**
 *  Image key size
 *
 *  This is the size of the md5sum that we consider unique enough
 *  to use as the key everywhere.  I've set it to 10 here, and I've
 *  tried to make it portable throughout the application (the only
 *  place we really use it is kxml / get_pictures()) but it's not
 *  something we should ever need to change.  If you have so many
 *  images you're getting collisions in a 16*10 space, you probably
 *  have already hit other problems by now.

 *  To clarify - DO NOT MODIFY THIS ITEM.
 **/
$config['key_size'] = 10;


/**
 *  Thumbs per page
 *
 *  Until we get a javascript-enabled version up and going that
 *  can dynamically and intelligently determine the width of the
 *  the screen and the required number of images that can fit in
 *  that space ... we have to rely upon a hard-coded number that
 *  we will accept.
 *
 *  9 works well on small screens - specifically my MSI Wind netbook,
 *  with its 1024x600 LCD.
 **/
$config['thumbs_per_page'] = 9;


/**
 *  Publish key word
 *
 *  The KEYWORD (category is required, currently) that will trigger
 *  an image's appearance in the Phoko Gallery.
 *
 *  By default it's the word 'PUBLISH' (case is sensitive, remember).
 *
 *  Using this setting combined with the 'shoosh tags' setting, you
 *  can easily have multiple Phoko Album galleries attached to a single
 *  instance of your KPhotoAlbum repository - showing different sets of
 *  images based on keyword choice.  Noice, huh?
 *
 *  @todo Allow for place, person, custom category trigger words.
 **/
// $config['publish_keyword'] = "PUBLISH";
$config['publish_keyword'] = "PUBLISH - jedd";



/**
 *  Shoosh tags
 *
 *  This sets the tags that you want to ignore - they simply won't be
 *  shown within Phoko.  This is useful if you have some secret tags,
 *  or just to tidy up your work-in-progress tags.
 *
 *  For instance, I use the 'OK' tag to denote images that I'm
 *  finished tagging, and use the negated version of that when
 *  searching for pictures I haven't finished working on in KPA.
 *  I also use some tags to alert my colleagues that they have
 *  a set of pictures that they need to attend to.
 *
 *  You can extend this array with a custom category, if you have
 *  any, as shown in the example.  Because I'm lazy (elsewhere) you
 *  MUST have an empty sub-array here for any custom categories
 *  that you have in play.  Sorry about that.
 **/
 $config['shoosh_tags'] = array(
				// "Keywords"  =>  array ("OK"),
				"Keywords"  =>
						array (	"OK - jedd",
								"0 - TEMP - stuff that Jan should go through",
								"0 - TEMP - stuff that Jedd should go through"),
				// "Persons"   =>  array ("foes"),
				"Persons"   =>
						array (	"scooby gang",
								"smithlets"),
				"Locations" =>  array (),
				// "My Category"  =>  array (),
				"The Farm"  =>  array ( "OK - vbo location tag" ),
				);



/**
 *  Sizes for thumbs, medium, and large (full-screen) images.
 *
 *  Display sizes may change, so these are primarily to control
 *  the disk space (and consequently the network bandwidth) required
 *  to store and serve a given image.
 *
 *  This may change over time - as I work out better algorithms.  At
 *  the moment, very very large files such as panoramas get shrunk down
 *  quite savagely, because of the large width:height ratio.  Tall and
 *  thin images don't get shrunk anywhere near as much as they should.
 *
 *  In general the quality of the medium and large images aren't so
 *  good, and while this is primarily a reflection on the imagemagick
 *  parameters used to generate the cached image files, it does mean
 *  that the following may well end up being ignored in the long term.
 *
 *  On the upside, changing the set here should just result in cache
 *  files being over-written, over time, until the whole cache is
 *  refreshed.  You won't end up with duplicated cache files.
 *
 * ((( From the original documentation (not sure what I meant) :
 * * Note that these sizes apply ONLY to images with a ratio of < 2,
 * * that is, once (width / height) > 2, the width listed below is
 * * multipled by this ratio.  This is to make very wide, very short
 * * images look better when shown full screen, or spanning multiple
 * * monitors, or within a pano-viewer.
 * )))
 **/
//$config['image_sizes'] = array ("thumb" =>
//						array ("x" => 80,   "y" => 60),
//					"large"  =>
//						array ("x" => 640,  "y" => 480),
//					"huge"  =>
//						array ("x" => 1200,  "y" => 900));
$config['image_sizes'] = array ("thumb" =>
						array ("x" => 80,   "y" => 60),
					"large"  =>
						array ("x" => 640,  "y" => 480),
					"huge"  =>
						array ("x" => 1200,  "y" => 900));



/**
 *  Panorama options
 *
 *  Panoramas can be enabled - where they'll be automatically detected when an
 *  image's width:height ratio > 2.  Or disabled outright.
 *
 *  Secondly, you can set the on-screen size for panoramas that will be offered
 *  to a user.  This is a limitation of the ptviewer script I'm using, and I
 *  may try later to change this to be a bit more dynamic for the user, once
 *  I get some javascript magic worked out.
 **/
// $config['panorama_allow'] = TRUE;
$config['panorama_allow'] = true;

//$panoramasizes = array ("s" =>  // Small - MSI Wind size
//							array ("desc" => "Small", "x" => 950 ,  "y"  => 400),
//						"m" =>  // Medium - medium size screen
//							array ("desc" => "Medium", "x" => 1050 ,  "y"  => 680),
//						"l" =>  // Large - 22" monitor size
//							array ("desc" => "Large", "x" => 1420 ,  "y"  => 900) );
$panoramasizes = array ("s" =>  // Small - MSI Wind size
							array ("desc" => "Small", "x" => 950 ,  "y"  => 400),
						"m" =>  // Medium - medium size screen
							array ("desc" => "Medium", "x" => 1050 ,  "y"  => 680),
						"l" =>  // Large - 22" monitor size
							array ("desc" => "Large", "x" => 1420 ,  "y"  => 900) );





// ===============================================================================
// ===============================================================================
// ===============================================================================
//
// Following items are residual legacy items from the pre-CI instance, and are
// either being migrated / modified, or culled, as the system is developed.
//
// ===============================================================================
// ===============================================================================
// ===============================================================================

/** Scrollbar granularity - determines the number of chunks that the
 *  pseudo scrollbar (beneath the thumbnails) is broken into.  The
 *  default of 20 is a good number, and probably no reason to change this.
 *
 *  Note - must be divisible evenly into 100 (for the %age calculation),
 *  so this limits it to 10, 20, 25, or 50.
 **/
// $scrollbar_granularity = 50;




/* End of file phoko.php */
/* Location: ./app/config/phoko.php */