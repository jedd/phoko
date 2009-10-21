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
	 *     ['all_tags']
	 *         ['Locations']
	 *              ['London'] = 27           // value is occurence count
	 *              ...
	 *         ...
	 *
	 *
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
	 * @param	$string		index.xml file (fully pathed)
	 * @return	array of pictures
	 **/
	function  get_pictures  ( $index_xml_file_name = FALSE )  {
		if (! $index_xml_file_name )
			return FALSE;

		// ~~~~~~~~~
		// Variables
		$config = $this->config->item('phoko');


		// Get file timestamps
		$index_xml_file_time = $this->_get_index_xml_file_time ($index_xml_file_name);
		$cache_xml_file_time = $this->_get_cache_xml_file_time ($config['cache_xml_file_name']);

		// If both times are set to zero, neither file is visible, so bomb out.
		if ( ($index_xml_file_time == 0) AND ($cache_xml_file_time == 0) )
			return FALSE;

		// If cache file is newer, we use it immediately.
		if ($cache_xml_file_time > $index_xml_file_time)
			return ( unserialize (file_get_contents ($config['cache_xml_file_name'])) );

		// If we get here, we know we're going to use index.xml
		$xml_content = simplexml_load_file($index_xml_file_name);
		if (! $xml_content)  {
			echo "Failed to read or comprehend index.xml file";
			return FALSE;
			}

		// Now we cycle through the entire XML object - actually a mix of object and
		// array types - with xml attributes (such as picture's width, date, etc)
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
		// as a kind of global function for the duration.

		// Stage 1 - find all the images that have the PUBLISH tag as a Keyword
		foreach ($xml_content->images->image  as  $image)  {
			if  ($image->options)  {
				foreach ($image->options->option  as $option)  {
					if ($option['name'] == "Keywords")  {
						foreach ($option->value  as  $value)  {
							if  ($value['value'] == $config['publish_keyword'])  {
								// Our picture-array[] contains two sub-arrays.  This builds the first
								// section - picturearray['images'] - by retrieving all picture attributes,
								// such as height, width, filename, etc.

								// Create our key - eg. 325f77a90f - that we'll use everywhere from now on.
								$image_id   = substr ($image['md5sum'], 0, $config['image_id_size']);

								// We must cast as (string) here, otherwise we end up with Objects.
								foreach ($config['image_attributes'] as $attr)
									$kpa_db['images'][$image_id][$attr] = (string)$image[$attr];

								// Take a step back down the nested loops, as we want ALL this image's information.
								foreach ($image->options->option as $revisted_option)  {
									$tag_category = (string)$revisted_option['name'];  // Locations, Persons, Keywords ...
									$x = 0;
									foreach ($revisted_option->value as $tagset)  {
										$kpa_db['images'][$image_id]['tags'][$tag_category][$x++] = (string)$tagset['value'];
										}
									}

								}  // end-if image-is-to-be-published
							}  // end-foreach
						} // end-if ($option['name'] == "Keywords")
					}  // end-foreach
				}  // end-if ($image->options)
			}  // end-foreach

		// HERE we have a populated $kpa_db['images'] sub-array.


		// Stage 2 - calculate member groups - only note groups that contain tags that we care about, of course.

		// dump ($kpa_db);

		// Have to do this, because you can't -> to a variable with a hyphen.
		$mg_string = "member-groups";
		$member_groups = $xml_content->$mg_string;
		// dump ($member_groups);



		// Create/overwrite the cached xml output for next time
		// file_put_contents ($config['cache_xml_file_name'], serialize($kpa_db));

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
	 * @param	$string		index.xml file (fully pathed)
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
	 * @param	$string		cache xml file name (fully pathed)
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





	}   // end-class  Kxml ()
