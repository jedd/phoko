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
	 * NOTE - this is the function that gets re-written if we move to a
	 *        MySQL backend for the KPA repository.  So long as it returns
	 *        an array of pictures that looks like the one we're returning
	 *        here, everything will be peachy.
	 *
	 * @param	$string		index.xml file (fully pathed)
	 * @return	array of pictures
	 **/
	function  get_pictures  ( $index_xml_file = FALSE )  {
		if (! $index_xml_file )
			return FALSE;

		/// @todo Pull the cache file name from the config file/array
		$cache_xml_file_name = "cache/index.kphp";

		// Get file timestamps
		$index_xml_file_time = $this->_get_index_xml_file_time ($index_xml_file);
		$cache_xml_file_time = $this->_get_cache_xml_file_time ($cache_xml_file_name);

		// If both times are set to zero, neither file is visible, so bomb out.
		if ( ($index_xml_file_time == 0) AND ($cache_xml_file_time == 0) )
			return FALSE;

		// If cache file is newer, we use it.
		if ($cache_xml_file_time > $index_xml_file_time)  {
			$raw_data = file_get_contents ($cache_xml_file_name);
			$picture_array = unserialize ($raw_data);
			return $picture_array;
			}




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
	function  _get_index_xml_file_time ($index_xml_file)  {
		if (file_exists ($index_xml_file)) {
			$file_stat = stat ($index_xml_file);
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
