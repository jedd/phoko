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
| 1. $config['name'] should be set to something relevant to YOUR gallery.
| 2. $config['repository'] must point to the KPA directory containing
|        your KPhotoAlbum index.xml file.
| 3. $config['publish_key_word'] must be set to whatever Keyword tag
|        you have set within KPA to denote images to publish.
| 4. $config['shoosh_tags'] should be set with any tags you wish
|        to keep hidden from the rest of the world.
|
| No other entries *need* to be changed in here.  Be sure not to change
| any of the settings that say 'DON'T MESS WITH THESE!' unless you
| making some code changes.  Modifying the non-modifiable items in is
| not something that I've tested.
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
 *  Cache xml file name
 *
 *  This is automatically calculated - there is nothing here for you to do.
 *  To clarify - DO NOT MODIFY THIS ITEM.
 **/
$config['cache_xml_file_name'] = "cache/index.kphp";


/**
 *  Image id size
 *
 *  This is the size of the md5sum that we consider unique enough
 *  to use as the key everywhere.  I've set it to 10 here, and I've
 *  tried to make it portable throughout the application (the only
 *  place we really use it is kxml / get_pictures()) but it's not
 *  something we should ever need to change.  If you have so many
 *  images you're getting collisions in a 16*10 space, you probably
 *  have already hit other problems by now.
 *
 *  NOTE If you do change this, all your cache items will need to be
 *  refreshed, and any URLs you have published will cease to exist.
 *
 *  To clarify - DO NOT MODIFY THIS ITEM.
 **/
$config['image_id_size'] = 10;


/**
 *  Image attributes
 *
 *  These are the image attributes within KPA, and the ones that we
 *  care about.  (We do not bother with angle - though probably
 *  should, at some point.)
 *
 *  Changes to this array will require programmatic changes - so
 *  you can't usefully modify this array yet.  It's here so that we
 *  have a single location for its definition only.
 *
 *  To clarify - DO NOT MODIFY THIS ITEM.
 **/
$config['image_attributes'] = array ("width", "description", "height", "startDate", "md5sum", "file", "endDate", "label");


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
 *  or want to tidy up your work-in-progress tags.
 *
 *  For instance, I use the 'OK' tag to denote images that I'm
 *  finished tagging, and use the negated version of that when
 *  searching for pictures I haven't finished working on in KPA.
 *  I also use some tags to alert my family that they have
 *  a set of pictures that they need to attend to.
 *
 *  You can nominate a tag OR a member group (super group) in here.
 *  If you have member groups that have the same name as tags, then
 *  I'm sorry - there's no way to denote that.  Having the same name
 *  in both cases is probably bad form as far as tagging goes, so I'll
 *  stick to that as an excuse.
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
								"0 - TEMP - stuff that Jedd should go through",
								),
				// "Persons"   =>  array ("foes"),
				"Persons"   =>
						array (	"scooby gang",
								"smithlets",
								),
				// "Locations"   =>  array ("Australia"),
				"Locations" =>  array (
								),
				// "My Category"  =>  array (),
				"The Farm"  =>  array ( "OK - vbo location tag"
								),
				);

/**
 *  Shoosh tags auto-modification
 *
 *  Publish key word is added, as we effectively shoosh it on
 *  load of the index.xml file - best to do it here automatically.
 *
 *  Also, KPA converts spaces in custom categories to underscores,
 *  for reasons that aren't clear (but it's VERY frustrating).  So
 *  here we convert any spaces in custom categories to underscores
 *  too - it's safer to do it here, rather than modding the XML
 *  input later.  And then we can s/_/ / later, at presentation
 *  time somewhere in our views.  Obviously there's no elegant
 *  answer to this, as we can't tell if the user intends underscores
 *  or spaces in their actual, on-screen custom category names.
 *  To make it EXTRA irritating, member groups show the name as
 *  it should be (using a space, not an underscore) - but we can't
 *  rely on member groups being in play, nor that 'The Farm' and
 *  'The_Farm' aren't intended, or exist, as separate groups.
 *
 *  In any case, DEFINITELY DO NOT MODIFY THESE ITEMS
 *
 **/
$config['shoosh_tags']['Keywords'][] = $config['publish_keyword'];

foreach ($config['shoosh_tags'] as $category=>$values)
	if (strstr ($category, " "))  {
		$new_name = str_replace (" ", "_", $category);
		$config['shoosh_tags'][$new_name] = $config['shoosh_tags'][$category];
		unset ($config['shoosh_tags'][$category]);
		}



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
$config['panorama_allow'] = TRUE;

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