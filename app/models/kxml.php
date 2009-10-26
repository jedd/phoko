<?php
/**
 * Phoko
 *
 * Web gallery for a KPhotoAlbum repository
 *
 * @package		phoko
 * @author		jedd
 * @version		v1
 * @copyright	Copyright (c) 2009, jedd
 * @license		GPL 2 or later
 * @link		http://dingogully.com.au/trac/phoko
 **/

// ------------------------------------------------------------------------

/**
 * Kxml
 *
 * Back-end to the KPhotoAlbum XML store.
 *
 * Provides all primities for dealing with the index.xml file that
 * Kphotoalbum stores all tags in.  We may end up with a Kmysql()
 * model later, once the MySQL store for KPA becomes stable.
 *
 * @package     phoko
 * @version     v1
 * @author      jedd
 * @link        http://dingogully.com.au/trac/phoko
 **/

// ------------------------------------------------------------------------

class  Kxml extends  Model {

	// ------------------------------------------------------------------------
	/**
	 *   Attributes
	 **/


	// ------------------------------------------------------------------------
	/**
	 *   Constructor
	 **/

	function  __construct ()  {
		parent::Model();
		} // end-constructor


	// ------------------------------------------------------------------------
	/**
	 * Get Pictures
	 *
	 * Prepares an array containing information on every picture we have in
	 * the collection.
	 *
	 * If the cached version (serialised array in a file) exists and is more recent
	 * than KPA's index.xml file, we use that.
	 *
	 * Otherwise, we use the index.xml to generate the serialised-array file.
	 *
	 * Format of returned array:
	 * $kpa_db
	 *     ['images']
	 *         ['3f66e0ba7a']
	 *              ['width'] = 789
	 *              ['startDate'] = 1999-08-23T12:32:00
	 *              ...
	 *         ...
	 *     ['tags']
	 *         ['Locations']
	 *              ['London'] = 27           // value is occurence count
	 *              ...
	 *         ...
	 *     ['member_groups']
	 *         ['Locations']
	 *              ['China']
	 *                   ['beijing']
	 *                   ...
	 *              ...
	 *
	 * 'tags' refers to all the tags *that we care about* - the tags
	 *        that are refered by any image in 'images' only.
	 *
	 * NOTE - this is the function that gets re-written if we move to a
	 *        MySQL backend for the KPA repository.  So long as it returns
	 *        an array of pictures that looks like the one we're returning
	 *        here, everything will be peachy.
	 *
	 * NOTE - this function is extraordinarily heavy, especially on large XML
	 *        files.  Because we do it so irregularly I'm less concerned about
	 *        the memory footprint it uses - the SimpleXML suite of functions
	 *        is generally readily available, and its peculiarities are well-
	 *        known - hence we're sticking with it here.
	 *
	 * @param	string		index.xml file (fully pathed)
	 * @return	array of pictures
	 **/
	function  get_pictures  ( )  {
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		// Config items we use in a few places
		$shoosh_tags         = $this->config->item('shoosh_tags');
		$image_id_size       = $this->config->item('image_id_size');
		$publish_keyword     = $this->config->item('publish_keyword');
		$image_attributes    = $this->config->item('image_attributes');
		$cache_xml_file_name = $this->config->item('cache_xml_file_name');
		$index_xml_file_name = $this->config->item('index_xml_file');

		// Get file timestamps
		$index_xml_file_time = $this->_get_index_xml_file_time ($index_xml_file_name);
		$cache_xml_file_time = $this->_get_cache_xml_file_time ($cache_xml_file_name);

		// If both times are set to zero, neither file is visible, so bomb out.
		if ( ($index_xml_file_time == 0) AND ($cache_xml_file_time == 0) )
			return FALSE;

		// If cache file is newer, we use it immediately.
		if ($cache_xml_file_time > $index_xml_file_time)
			return ( unserialize (file_get_contents ($cache_xml_file_name) ) );

		// If we get here, we know we're going to use index.xml
		$xml_content = simplexml_load_file($index_xml_file_name);
		if (! $xml_content)  {
			echo "Failed to read or comprehend index.xml file";
			return FALSE;
			}

		// Now we cycle through the entire XML object - actually a mix of object and
		// array types - with xml attributes (such as a picture's width, date, etc)
		// being array elements, but almost everything else coming in as as objects.
		//
		// I'm loathe to separate these early stages out into sub-functions, as it
		// would require passing by reference - otherwise we chew up some serious
		// memory.  Not bad in itself, but I don't see much gain in shifting much
		// of this into functionettes.  It's ugly code, I agree, but we come through
		// here so irregularly that I'm not hugely fussed.  See earlier comments
		// regarding (justifying) ugliness.  I think clarity and documentation are
		// more important than sub-dividing the task up and having to pass lots of
		// config and setting information around, and effectively having $the-xml-file
		// as a kind of global variable for the duration.


		// Stage 1 - find all the images that have the PUBLISH TAG as a Keyword
		foreach ($xml_content->images->image  as  $image)  {
			if  ($image->options)  {
				foreach ($image->options->option  as $option)  {
					if ($option['name'] == "Keywords")  {
						foreach ($option->value  as  $value)  {
							if  ($value['value'] == $publish_keyword)  {
								// Our picture-array[] contains two sub-arrays.  This builds the first
								// section - picturearray['images'] - by retrieving all picture attributes,
								// such as height, width, filename, etc.

								// Create our image_id (eg. 325f77a90f) that we'll use everywhere from now on.
								$image_id = substr ($image['md5sum'], 0, $image_id_size);

								// We must cast as (string) here, otherwise we end up with Objects.
								foreach ($image_attributes as $attr)
									$kpa_db['images'][$image_id][$attr] = (string)$image[$attr];

								// HERE we have to 'step back' to an earlier nested loop, to get ALL the image's data.

								// Cycle through outer array of tag categories, treating the three default category
								// types (Locations/Persons/Keywords) equally as any custom categories we find.
								foreach ($image->options->option as $revisted_option)  {
									$tag_category = (string)$revisted_option['name'];

									// Go through inner array, picking up tags within this tag_category.
									$x = 0;
									foreach ($revisted_option->value as $tagset)  {
										$tag_value = (string)$tagset['value'];

										// Publish-tag is added to shoosh_tags in the config, so we just use that.
										if (! (in_array ($tag_value, $shoosh_tags[$tag_category])) ) {
											$kpa_db['images'][$image_id]['tags'][$tag_category][$x++] = $tag_value;

											// We keep a counter of occurences of each tag.
											if (isset ($kpa_db['tags'][$tag_category][$tag_value]))
												$kpa_db['tags'][$tag_category][$tag_value] += 1;
											else
												$kpa_db['tags'][$tag_category][$tag_value] = 1;
											}  // end-if (picture not in shoosh tags)
										}  // end-foreach ($revisted_option->value as $tagset)
									}  // end-foreach ($image->options->option as $revisted_option)
								}  // end-if image-is-to-be-published
							}  // end-foreach ($option->value  as  $value)
						} // end-if ($option['name'] == "Keywords")
					}  // end-foreach
				}  // end-if ($image->options)
			}  // end-foreach

		// HERE we have $kpa_db[] with two sub-arrays: ['images'] and ['tags']

		// Sort the contents of each of the $kpa_db['tags'] sub-arrays.
		foreach ($kpa_db['tags'] as $y => $z)
			ksort (&$kpa_db['tags'][$y]);


		// Stage 2 - calculate member groups - only note groups that contain tags that we care about, of course.

		// Have to do this, because you can't -> to a variable with a hyphen.
		$mg_string = "member-groups";
		$member_groups = $xml_content->$mg_string;
		$kpa_db['member_groups'] = $this->_massage_member_groups ($member_groups, $kpa_db['tags'] );

		// Create/overwrite the cached xml output for next time
		file_put_contents ($cache_xml_file_name, serialize($kpa_db));

		return $kpa_db;
		}  // end-method  get_pictures ()







	// ========================================================================
	// ------------------------------------------------------------------------
	// P R I V A T E   F U N C T I O N S  -- nothing to see here.
	// ------------------------------------------------------------------------
	// ========================================================================

	/**
	 * Get index xml filetime
	 *
	 * Returns the date stamp on the index.xml file, in mtime format.
	 *
	 * If the file can not be accessed, return a time of 0 (so that it will
	 * appear older than any extant cache file later).
	 *
	 * @param	string		index.xml file (fully pathed)
	 * @return	integer
	 **/
	function  _get_index_xml_file_time ($index_xml_file_name)  {
		if (file_exists ($index_xml_file_name)) {
			$file_stat = stat ($index_xml_file_name);
			$file_time = $file_stat['mtime'];
			}
		else
			$file_time = 0;

		return $file_time;
		}  //  end-method  _get_index_xml_file_time ()


	// ------------------------------------------------------------------------
	/**
	 * Get cache xml filetime
	 *
	 * Returns the date stamp on the cached version of our index xml
	 *
	 * If the file does not exist, return a time of 0.
	 *
	 * @param	string		cache xml file name (fully pathed)
	 * @return	integer
	 **/
	function  _get_cache_xml_file_time ( $cache_xml_file_name )  {
		if (file_exists ($cache_xml_file_name))  {
			$file_stat  = stat ($cache_xml_file_name);
			$file_time = $file_stat['mtime'];
			}
		else
			$file_time = 0;

		return $file_time;
		}  //  end-method  _get_cache_xml_file_time ()


	// ------------------------------------------------------------------------
	/**
	 * Massage member groups
	 *
	 * Generates an array from the Simple XML sub-object of [member-groups],
	 * extracting only tags that match photos we care about, and consequently
	 * only member groups that contain such tags.
	 *
	 * @param	array	cache xml file name (fully pathed)
	 * @return	integer
	 **/
	function  _massage_member_groups ( $member_groups, $tags_in_use )  {
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		// Config items we use in a few places
		$shoosh_tags      = $this->config->item('shoosh_tags', 'phoko');

		$mg_array = array();

 		foreach  ($member_groups->member  as  $mg)  {
			// Again, we have to insert an underscore into category names here,
			// because KPA *doesn't* put one in member group categories ... and
			// we need to keep it consistent with everywhere else (when it *does*).
 			$category   = (string) $mg['category'];
			if (strstr ($category, " "))
				$category = str_replace (" ", "_", $category);
 			$group_name = (string) $mg['group-name'];
 			$tag        = (string) $mg['member'];

 			// By checking against $tags_in_use we are tacitly vetoing most of the
 			// SHOOSH TAGS, as $tags_in_use was filtered by that config setting.
 			// The one thing we didn't catch there was MEMBER GROUPS, of course,
 			// so that's all we check for here.
			if ( (isset ($shoosh_tags[$category]))  AND  (! in_array ($group_name, $shoosh_tags[$category]) )  )
				$mg_array[$category][$group_name][] = $tag;
 			}

 		// Sort the sub-arrays, in-place, alphabetically.
 		foreach ($mg_array as $category => $group)
 			foreach ($group as $group_name => $tag)
 				sort ( &$mg_array[$category][$group_name] );

		return $mg_array;
		}  //  end-method  _massage_member_groups ()


	}   // end-class  Kxml ()
