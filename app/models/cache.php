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
 * Cache
 *
 * Back-end to the Cache management system
 *
 * Provides all primities for dealing with the cache - identifying
 * present files, generating cache images when required, returning
 * cached file information, and so on.
 *
 * @package     phoko
 * @version     v1
 * @author      jedd
 * @link        http://dingogully.com.au/trac/phoko
 **/

// ------------------------------------------------------------------------

class  Cache extends  Model {

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
	 * Get list of cache files
	 *
	 * Will return list of files in cache, for a given type (small, medium, large).
	 * Absent a nominated type, will return all - in sub-arrays - using a
	 * recursive call back to itself.
	 *
	 * @param	string	$type	One of 'all', 'small', 'medium', 'large'
	 * @return	array
	 **/
	function  get_list_of_cache_files ( $type = "all"  )  {
		/// @todo work out why I can't pull in config->items here - it works
		/// okay in the kxml model - it just consistently returns false here.
		$image_sizes    = $this->config->item('image_sizes', 'phoko');
		$image_id_size  = $this->config->item('image_id_size', 'phoko');


		$image_id_size = 10; /// @todo get this from $config
		$image_sizes = array (
					"small" =>
						array ("x" => 80,   "y" => 60),
					"medium"  =>
						array ("x" => 640,  "y" => 480),
					"large"  =>
						array ("x" => 1200,  "y" => 900));

		// This is a recursive function - if we come in with all, we actually
		// then return here with 'small', 'medium' and 'large' in order.
		// If we arrive with any specific size, we go straight to the else below.
		if ($type == "all")  {
			$return_array = array();
			foreach ($image_sizes as $size => $foo)
				$return_array[$size] = $this->get_list_of_cache_files($size);
			return $return_array;
			}
		else  {
			// Work through the contents of the particularly sub-dir under ./cache/
			if ($handle = opendir("cache/". $type ))  {
				$file_list = array();
				// For every directory entry that isn't . and .. we'll
				// grab the filename (assuming consistently named as per the
				// ->config(image_id_size) item) and the file size in bytes.
				while (false !== ($file = readdir($handle)))   {
					$image_id = substr ($file , 0, $image_id_size);
					if ( $image_id != "." AND  $image_id != "..")
						$file_list[$image_id] = filesize ("cache/". $type ."/". $file);
					}
				closedir($handle);
				return $file_list;
				}
			$return_array = array();
			}
		}  // end-method  get_list_of_cache_files ()





	// ========================================================================
	// ------------------------------------------------------------------------
	// P R I V A T E   F U N C T I O N S  -- nothing to see here.
	// ------------------------------------------------------------------------
	// ========================================================================

	/**
	 * @param
	 * @return
	 **/

	}   // end-class  Cache ()
