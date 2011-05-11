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
 * Kpa
 *
 * Back-end to the KPhotoAlbum XML store.
 *
 * Provides all primities for dealing with the index.xml file that
 * Kphotoalbum stores all tags in.  We may end up with a Kmysql()
 * model later, once the MySQL store for KPA becomes stable.
 *
 * There are also some cache functions stored in here - it got too
 * messy keeping them in a separate model.  It might make it
 * marginally more complex to move to MySQL, but only marginally.
 *
 * @package     phoko
 * @version     v1
 * @author      jedd
 * @link        http://dingogully.com.au/trac/phoko
 **/

// ------------------------------------------------------------------------

class  Kpa extends  CI_Model {

	// ------------------------------------------------------------------------
	/**
	 *   Attributes
	 **/

	// Thumbs are the list of thumbnails we will be showing this time round
	// (they are loaded by select_thumbs())
	var $thumbs = array();

	// kpa_full is the PUBLISHED set of images from KPA's index.xml file, and
	// contains information for each publishable image, super-groups, and
	// custom categories.  It is instantiated by get_pictures() - originally
	// under-utilised here, but as we OO things it becomes more important.
	var $kpa_full = array();

	// kpa_filt is the SUBSET of the PUBLISHED set - it contains only the
	// images that meet the various FILTERS provided in the URL.  It is
	// instantiated by generate_kpa_filt()
	var $kpa_filt = array();


	// ------------------------------------------------------------------------
	/**
	 *   Constructor
	 **/

	function  __construct ()  {
		parent::__construct();
		} // end-constructor



	// ------------------------------------------------------------------------
	/**
	 *   Setters
	 **/



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
	 * $kpa_full
	 *     ['images']
	 *         ['3f66e0ba7a']
	 *              ['width'] = 789
	 *              ['startDate'] = 1999-08-23T12:32:00
	 *              ...
	 *              ['label'] = 'pict0003.jpg'
	 *              ['tags']
	 *                   ['Keywords']
	 *                         0 = 'church - of the vera cruz (templar)'
	 *                   ['Locations']
	 *                         0 = 'segovia'
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
	 * @TODO- we could lookup the exif-info.db file here and consult it,
	 *        BUT it's a SQLITE database that I'm unfamiliar with talking
	 *        to - so instead we consult each file separately.  Yes, this is
	 *        also pretty heavy - but it's an infrequent process, so it'll
	 *        do for the time being.  Early assessments suggest that scanning
	 *        ~800 images and grabbing the EXIF data (as below) takes ~ 1s.
	 *
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
		$repository_path     = $this->config->item('repository');

		// Get file timestamps
		$index_xml_file_time = $this->_get_index_xml_file_time ($index_xml_file_name);
		$cache_xml_file_time = $this->_get_cache_xml_file_time ($cache_xml_file_name);

		// If both times are set to zero, neither file is visible, so bomb out.
		if ( ($index_xml_file_time == 0) AND ($cache_xml_file_time == 0) )
			return FALSE;

		/// @todo - enable or disable this to forcea  re-parse of the index.xml file
		// If cache file is newer, we use it immediately.
		if ($cache_xml_file_time > $index_xml_file_time)  {
			$kpa_full = unserialize (file_get_contents ($cache_xml_file_name) );
			$this->kpa_full = $kpa_full;
			/// @todo We can later avoid returning this.
			return $kpa_full;
			}

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

								// Collect the EXIF data from the actual image file proper.
								$kpa_full['images'][$image_id]['exif'] = $this->_get_exif_from_file ($repository_path . $image['file']);

								// We must cast as (string) here, otherwise we end up with Objects.
								foreach ($image_attributes as $attr)
									$kpa_full['images'][$image_id][$attr] = (string)$image[$attr];

								// HERE we have to 'step back' to an earlier nested loop, to get ALL the image's data.

								// Cycle through outer array of tag categories, treating the three default category
								// types (Locations/Persons/Keywords) equally as any custom categories we find.
								foreach ($image->options->option as $revisited_option)  {
									$tag_category = (string)$revisited_option['name'];
									$tag_category = str_replace ("_", " ", $tag_category);

								// We ignore Tokens as a tag category - they're meant to be short-term
								// attributes.  If we don't do this here then we get some on-browser
								// errors when there are published pictures that have tokens.
								if ($tag_category != "Tokens")  {
										// Go through inner array, picking up tags within this tag_category.
										$x = 0;
										foreach ($revisited_option->value as $tagset)  {
											$tag_value = (string)$tagset['value'];

											// Publish-tag is added to shoosh_tags in the config, so we just use that.
											if ( ! (in_array ($tag_value, $shoosh_tags[$tag_category])) )  {
												$kpa_full['images'][$image_id]['tags'][$tag_category][$x++] = $tag_value;

												// We keep a counter of occurences of each tag.
												if (isset ($kpa_full['tags'][$tag_category][$tag_value]))
													$kpa_full['tags'][$tag_category][$tag_value] += 1;
												else
													$kpa_full['tags'][$tag_category][$tag_value] = 1;
												}  // end-if (picture not in shoosh tags)
											}  // end-foreach ($revisited_option->value as $tagset)
										} // end-if $tag_category != 'Tokens'
									}  // end-foreach ($image->options->option as $revisited_option)
								}  // end-if image-is-to-be-published
							}  // end-foreach ($option->value  as  $value)
						} // end-if ($option['name'] == "Keywords")
					}  // end-foreach
				}  // end-if ($image->options)
			}  // end-foreach

		// HERE we have $kpa_full[] with two sub-arrays: ['images'] and ['tags']

		// Sort the contents of each of the $kpa_full['tags'] sub-arrays.
		foreach ($kpa_full['tags'] as $y => $z)
			ksort (&$kpa_full['tags'][$y]);


		// Stage 2 - calculate member groups - only note groups that contain tags that we care about, of course.

		// Have to do this, because you can't -> to a variable with a hyphen.
		$mg_string = "member-groups";
		$member_groups = $xml_content->$mg_string;

		$kpa_full['member_groups'] = $this->_massage_member_groups ($member_groups, $kpa_full['tags'] );

		// Create/overwrite the cached xml output for next time
		file_put_contents ($cache_xml_file_name, serialize($kpa_full));

		$this->kpa_full = $kpa_full;

		return $kpa_full;
		}  // end-method  get_pictures ()



	// ------------------------------------------------------------------------
	/**
	 * Generate KPA FILTered set
	 *
	 * Based on the Filters provided in the URL, produce a subset of
	 * the $kpa_full array, showing just images that we want.
	 *
	 * @param	array	$filters	The array of filters from the URL
	 **/
	function  generate_kpa_filt  ( $filters = NULL )  {
		if ($filters == NULL)  {
			$this->kpa_filt = $this->kpa_full;
			return sizeof ($this->kpa_filt['images']);
			}

		// Start with the larger set - the complete published image collection,
		// and for each image check if we have a match across ALL filters (we
		// treat them as AND, not OR) and if so, transcribe the image information
		// and populate ['tags'] and ['member_groups'] too.
		$kf = array();
		$number_of_filters = sizeof($filters);
		foreach ($this->kpa_full['images'] as $image_id => $image_details)  {
			$filters_met = 0;
			foreach ($filters as $filter)  {
				if (isset ($image_details['tags'][$filter['category']]))  {
					if (in_array ($filter['actual'] , $image_details['tags'][$filter['category']]))  {
						$filters_met++;
						}
					}
				}
			if ($filters_met >= $number_of_filters)  {
				$kf['images'][$image_id] = $image_details;
				}
			}

		// Tags need to be calculated, showing on extant tags in the filter group, so we
		// work similarly to the way we do this in the main rip-it-outta-the-xml-file().

		foreach ($kf['images'] as $image_id => $image_info)  {
			if (isset ($image_info['tags']))  {
				$tags = $image_info['tags'];
				foreach ($tags as $tag_category => $tag_entry)  {
					/// @todo This is one place where we are happy to have _'s removed from
					/// categories up front - later we should do this across the board, hence
					/// we flag this here for future attention - not happy about KPA's handling
					/// of underscores in names .. in other words, not my fault!  :)
					$tag_category = str_replace ("_", " ", $tag_category);

					foreach ($tag_entry as $tag_actual)  {
						if (isset($kf['tags'][$tag_category][$tag_actual]))
							$kf['tags'][$tag_category][$tag_actual]++;
						else
							$kf['tags'][$tag_category][$tag_actual] = 1;
						}
					}
				}
			}

		// Set our attribute
		$this->kpa_filt = $kf;

		// Might be useful to know the number of images in our new set
		return sizeof ($kf['images']);
		}  //  end-method  generate_kpa_filt  ()



	// ------------------------------------------------------------------------
	/**
	 * Get tag categories
	 *
	 * Simply retrieves the tag categories we have (in $kpa_full)
	 * such as Persons, Locations, Keywords, and custom categories.
	 *
	 * @return	array
	 **/
	function  get_tag_categories  ( )  {
		$tag_categories = array();

		foreach ($this->kpa_full['tags'] as $category => $foo)
			$tag_categories[] = $category;

		return $tag_categories;

		}  //  end-method  get_tag_categories  ()



	// ------------------------------------------------------------------------
	/**
	 * Get tag counts
	 *
	 * Provides a list of counts for a given set of tags.
	 *
	 * If the filt == full, we only need to do this once, otherwise
	 * we'll provide two sets of counts (filt and full).  These data
	 * are shown next to tag links in the image-info view, to indicate
	 * the number of images that reside in a given (potential) filter.
	 *
	 * @param	string	$image_id	We use this image's tag set.
	 * @return	array
	 **/
	function  get_tag_counts  ( $image_id )  {
		// We don't assume we have a valid image_id
		if (! isset ($this->kpa_full['images'][$image_id]['tags'] ))
			return FALSE;

		// Arrays are already created in kpa_full and kpa_filt, so no cost
		// to send them both back even if they're identical - the view will
		// handle what to do with duplicate data - it's a display-time decision.
		$retval = array();
		$retval['kpa_full'] = $this->kpa_full['tags'];
		$retval['kpa_filt'] = $this->kpa_filt['tags'];

		return $retval;
		}  //  end-method  get_tag_counts ()



	// ------------------------------------------------------------------------
	/**
	 * Select thumbs to show
	 *
	 * Sets the local attribute $thumbs (array) with information on the
	 * thumbs we'll be showing on this trip through.
	 *
	 * Caters for:
	 *     * config setting of thumbs-to-show-at-a-time
	 *     * offset (local attribute)
	 *     * filters (just not yet...)
	 *
	 **/
	function  select_thumbs ( $offset = 1 )  {
		$image_repository = $this->config->item('repository');
		$thumbs_per_page  = $this->config->item('thumbs_per_page');
		$CI =& get_instance();

		$last_thumb_to_show = $offset + $thumbs_per_page;

		$tharray = array();

		$x = 1;

		foreach ($this->kpa_filt['images'] as $thumb_id => $thumb_details)  {
			if ( ($x >= $offset) AND ($x < $last_thumb_to_show) )  {
				$tharray[$thumb_id]['description'] = $thumb_details['description'];
				$tharray[$thumb_id]['file_name'] = $this->prepare_image (
														$thumb_id,
														$image_repository. $thumb_details['file'],
														$thumb_details,
														$image_type = 'small' );

				$tharray[$thumb_id]['width'] = $thumb_details['width'];
				$tharray[$thumb_id]['height'] = $thumb_details['height'];

				/// @todo Work out a better place for this - either another loop in the controller,
				/// or relocate the function into this model.
				$tharray[$thumb_id]['link'] = $CI->_create_url_with_new_image_id($thumb_id);

				// Depart once we have our $thumbs_per_page worth of thumbs information
				/// @todo Check for end of list happening before full # of thumbs acquired
				if ($x > $last_thumb_to_show)
					break;
				}
			$x++;
			}


		$this->thumbs = $tharray;
		// No need to return anything here - but perhaps TRUE/FALSE or number of thumbs?

		}  // end-method  select_thumbs ()


	// ------------------------------------------------------------------------
	/**
	 * Create date-stamp array (of $kpa_filt)
	 *
	 * Returns an array for later use by a javascript function,
	 * so we want an array of strings that look like this:
	 * ("1999-01-05<br />Spain", "2000-04-30<br />Chiang Mai",  ...).
	 *
	 * @return	integer
	 **/
	function   create_date_stamp_array() {
		$date_array = array();

		// Javascript starts at 0, like PHP, but our /o image offset starts at 1,
		// so we need a dummy entry here as a place filler.
		$date_array[] = "0000-00-00";

		foreach ($this->kpa_filt['images'] as $image )  {
			// Grab date portion - yyyy-mm-dd (10 chars)
			$image_date = substr ($image['startDate'] , 0, 10);

			// Identify first location (if it exists)
			/// @todo get parent (country) location if it exists)
			/// @todo alternatively get this information into kpa_f* at read-xml time
			if (isset ($image['tags']['Locations'][0]))
				$image_location = $image['tags']['Locations'][0];
			else
				$image_location = 'Somewhere';

			// Concat date . <br> . location
			$date_array[] = '"'. $image_date ."<br />". $image_location .'"';
			}

		$return_value = implode (", ", $date_array);

		return $return_value;
		}  // end-method  create_date_stamp_array ()






	// ------------------------------------------------------------------------
	/**
	 * Get position number of image (in set)
	 *
	 * Returns an integer, somewhere betweeen 1 and sizeof($kpa_filt)
	 *
	 *
	 * @param	string		$image_id
	 * @return	integer
	 **/
	function   get_position_number  ($current_image)  {
		$x = 1;
		foreach ($this->kpa_filt['images'] as $image_id => $foo)  {
			if ($current_image == $image_id)  {
				$position = $x;
				break;
				}
			$x++;
			}

		// We cater for main image not being in the thumb set - which will
		// happen if we add a filter that doesn't include the currently
		// viewed main picture - I'm happy with this behaviour, but just
		// need to cater for it here.

		if (isset ($position))
			return $position;
		else
			return 1;
		}  // end-method  get_position_number  ()



	// ------------------------------------------------------------------------
	/**
	 * Get first image_id
	 *
	 * Returns the image_id of the first image in the $kpa_filt array
	 *
	 * @return	string
	 **/
	function   get_first_image_id_from_kpa_filt  ( )  {
		$image_id_size = $this->config->item('image_id_size');

		// Can't think of a better way of identifying the first in the array
		foreach ($this->kpa_filt['images'] as $image_id => $foo)  {
			break;
			}

		// Sanity check - confirm $image_id is '10' chars long
		if (strlen ($image_id) == $image_id_size)
			return $image_id;
		else
			return FALSE;
		}  // end-method  get_first_image_id_from_kpa_filt  ()



	// ------------------------------------------------------------------------
	/**
	 * Get last image_id
	 *
	 * Returns the image_id of the last image in the $kpa_filt array
	 *
	 * @return	string
	 **/
	function   get_last_image_id_from_kpa_filt  ( )  {
		$image_id_size = $this->config->item('image_id_size');

		// Can't think of a better way of identifying the last in the array
		foreach ($this->kpa_filt['images'] as $image_id => $foo)  {
			;
			}

		// Sanity check - confirm $image_id is '10' chars long
		if (strlen ($image_id) == $image_id_size)
			return $image_id;
		else
			return FALSE;
		}  // end-method  get_last_image_id_from_kpa_filt  ()




	// ------------------------------------------------------------------------
	/**
	 * Get position number of previous (in set) image
	 *
	 * Returns an integer, somewhere betweeen 1 and sizeof($kpa_filt)
	 *
	 *
	 * @param	string		$image_id
	 * @return	integer
	 **/
	function   get_prev_image_id ($current_image)  {
		$prev_image_id = FALSE;
		foreach ($this->kpa_filt['images'] as $image_id => $foo)  {
			if ($current_image == $image_id)
				break;
			$prev_image_id = $image_id;
			}
		return $prev_image_id;
		}  // end-method  get_prev_image_id



	// ------------------------------------------------------------------------
	/**
	 * Get position number of next (in set) image
	 *
	 * Returns an integer, somewhere betweeen 1 and sizeof($kpa_filt)
	 *
	 *
	 * @param	string		$image_id
	 * @return	integer
	 **/
	function   get_next_image_id ($current_image)  {
		$next_flag = $next_image_id = FALSE;

		foreach ($this->kpa_filt['images'] as $image_id => $foo)  {
			if ($next_flag)  {
				$next_image_id = $image_id;
				break;
				}
			if ($current_image == $image_id)
				$next_flag = TRUE;
			}
		return $next_image_id;
		}  // end-method  get_next_image_id




	// ------------------------------------------------------------------------
	/**
	 * Get best offset
	 *
	 * Returns the best possible valid offset (for thumbnails) for a given image ID
	 *
	 * @param	string	$image_id
	 * @return	integer
	 **/
	function   get_best_offset  ( $current_image_id )  {
		$x = $best_offset = 1;
		foreach ($this->kpa_filt['images'] as $image_id => $foo)  {
			if ($current_image_id == $image_id)
				$best_offset = $x;
			$x++;
			}

		return $best_offset;

		}  //  end-method  get_best_offset();



	// ========================================================================
	// ------------------------------------------------------------------------
	// C A C H E    M A N A G E M E N T    S T U F F
	// ------------------------------------------------------------------------
	// ========================================================================



	// ------------------------------------------------------------------------
	/**
	 * Get list of cache files
	 *
	 * Will return list of files in cache, for a given type (small, large).
	 * Absent a nominated type, will return all - in sub-arrays - using a
	 * recursive call back to itself.
	 *
	 * @param	string	$type	One of 'all', 'small', 'large'
	 * @return	array
	 **/
	function  cache_get_list_of_files ( $type = "all"  )  {
		/// @todo work out why ('image_sizes', 'phoko') fails but ('image_sizes')
		/// works - whereas the former works fine in kxml.php (another model).
		$image_sizes    = $this->config->item('image_sizes');
		$image_id_size  = $this->config->item('image_id_size');

		// This is a recursive function - if we come in with all, we actually
		// then return here with 'small' and 'large' in order.
		// If we arrive with any specific size, we go straight to the else below.
		if ($type == "all")  {
			$return_array = array();
			foreach ($image_sizes as $size => $foo)
				$return_array[$size] = $this->cache_get_list_of_files($size);
			return $return_array;
			}
		else  {
			/// @todo confirm $type is in_array()
			// Work through the contents of the particularly sub-dir under ./cache/
			if ($handle = opendir("cache/". $type ))  {
				$file_list = array();
				// For every directory entry that is the right size (this then rules
				// out . and ..) we grab the name and file size in bytes.
				while (false !== ($file = readdir($handle)))   {
					$image_id = substr ($file , 0, $image_id_size);
					if (strlen ($image_id) == $image_id_size)
						$file_list[$image_id] = filesize ("cache/". $type ."/". $file);
					}
				closedir($handle);
				return $file_list;
				}
			}
		}  // end-method  cache_get_list_of_files()



	// ------------------------------------------------------------------------
	/**
	 * Delete cache files
	 *
	 * Given a list of files and the file type (small, large) this
	 * function deletes those files.
	 *
	 * @param	string	$type			One of 'all', 'small', 'large'
	 * @param	array	$delete_files	Array of files to delete
	 **/
	function  delete_cache_files ( $file_type, $delete_files )  {
		foreach ($delete_files as $file_to_delete)  {
			$pathed_filename = "cache/". $file_type ."/". $file_to_delete .".jpg";

			if (file_exists ($pathed_filename))
				unlink ($pathed_filename);
			}
		} //  end-method  delete_cache_files ()


	// ------------------------------------------------------------------------
	/**
	 * Create cache files
	 *
	 * Given a list of files and the file type (small, large) this
	 * function creates those files.
	 *
	 * @param	string	$type			One of 'all', 'small', 'large'
	 * @param	array	$create_files	Array of files to create
	 **/
	function  create_cache_files ( $file_type, $create_files )  {

		foreach ($create_files as $image_id_to_create)  {
			$image_repository = $this->config->item('repository');

			$image_info = $this->get_image_info ($image_id_to_create);

			$pathed_original_filename = $image_repository . $image_info['file'];

			$this->prepare_image ( $image_id_to_create , $pathed_original_filename , $image_info , $file_type );
			}
		} //  end-method  create_cache_files ()



	// ------------------------------------------------------------------------
	/**
	 * Get image info
	 *
	 * Given an image_id - pull out the relevant sub-array from $kpa_full
	 *
	 * @param	string	$image_id
	 **/
	function  get_image_info  ( $image_id )  {
		return  $this->kpa_full['images'][$image_id];
		}  //  end-method  get_image_info ()




	// ------------------------------------------------------------------------
	/**
	 * Get image EXIF info
	 *
	 * Given an image_id - pull out the relevant sub-array from $kpa_full
	 *
	 * @param	string	$image_id
	 **/
	function  get_image_exif  ( $image_id )  {
		return  $this->kpa_full['images'][$image_id]['exif'];
		}  //  end-method  get_image_exif ()



	// ------------------------------------------------------------------------
	/**
	 * Prepare image
	 *
	 * If the image is present, returns TRUE immediately.
	 *
	 * If the image is not present in the cache, generate it and then return TRUE.
	 *
	 * It's possible, but unlikely, that we'll ever return FALSE -- if we do it's
	 * because something unpleasant has happened - run out of space, gd library
	 * isn't working, original file can't be found, etc.
	 *
	 * @NOTE that RAW is an exception that we don't play with yet - and when
	 * we do, a call of $type=ALL won't return RAW unless it has its nominated
	 * tag in the kpa_xml_db file.  Raws are likely to be offered for wallpapers
	 * only, where the original size & quality of the file should be preserved.
	 *
	 * @param	string	$image_id		The ID of the image we're about to provide
	 * @param	array	$image_info		A dump from the [images] sub-array of kpa_full
	 * @param	string	$type			The type of image (all, small, large, raw)
	 * @return	string
	 **/
	function  prepare_image  ( $image_id = FALSE, $original_file = '', $image_info = NULL, $image_type = 'all' )  {
		// @TODO get rid of $image_info - we appear to not use it.

		// @todo Here is where we'd check if image_type is 'raw', and handle that
		// separately, then return.

		// If 'all', recursively call this function with 'small' and 'large'
		$image_sizes = $this->config->item('image_sizes');

		if ($image_type == 'all')
			foreach ($image_sizes as $type => $foo)  {
				$status = $this->prepare_image($image_id, $type);
				if (! $status)  /// @todo handle different failure types differently
					return FALSE;
				}

		/// Okay, we're now definitely dealing with one of small or large

		$cache_file_name = "cache/". $image_type ."/". $image_id .".jpg";
		$tmp_file_name   = "cache/". $image_type ."/". $image_id ."_tmp.jpg";

		// First things first - if the image exists, we return TRUE immediately.
		if ( file_exists($cache_file_name) )
			return $cache_file_name;

		// Second things second - we MUST have access to the original.
		if (! file_exists($original_file))
			return FALSE;

		$original_file_size = filesize ($original_file);

		// Third - we generate the new cache file
		$this->image_lib->clear();
		$image_config['new_image'] = $tmp_file_name;
		$image_config['image_library'] = 'ImageMagick';
		// $image_config['library_path'] = '/usr/lib/php5/20060613+lfs/';
		$image_config['library_path'] = '/usr/bin';
		$image_config['source_image'] = $original_file;
		$image_config['width'] = $image_sizes[$image_type]['x'];
		$image_config['height'] = $image_sizes[$image_type]['y'];

		/// @todo We want a sliding scale of quality, determined by original
		/// file size - otherwise we were seeing massive differences in
		/// final output of the cache files.  We might need to change
		/// this algorithm again to take into account physical dimensions
		/// (width * height) too - as small files with many pixels probably
		/// need a higher quality.  Very much a work in progress.

		// Files > 5MB get q=65
		if ($original_file_size > 5000000)
			$image_config['quality'] = 60;

		// Files between 3MB and 5MB get q=75
		if ( ($original_file_size > 3000000) AND ($original_file_size <= 5000000) )
			$image_config['quality'] = 75;

		// Files between 2MB and 3MB get q=85
		if ( ($original_file_size > 2000000) AND ($original_file_size <= 3000000) )
			$image_config['quality'] = 85;

		// Tiny files - under 2MB - get very good treatment - q=95
		if ($original_file_size <= 200000)
			$image_config['quality'] = 95;

		$image_config['maintain_ratio'] = TRUE;
		$this->image_lib->initialize($image_config);
		$this->image_lib->resize();

		// Hopefully this is fast/atomic enough to not have a PHP timeout occur during the rename
		if (file_exists ($tmp_file_name))
			rename ($tmp_file_name, $cache_file_name);

		echo $this->image_lib->display_errors();

		return $cache_file_name;
		}  // end-method  prepare_image ()





	// ========================================================================
	// ------------------------------------------------------------------------
	// S E T T E R S   A N D    G E T T E R S
	// ------------------------------------------------------------------------
	// ========================================================================



	// ========================================================================
	// ------------------------------------------------------------------------
	// P R I V A T E   F U N C T I O N S  -- nothing to see here.
	// ------------------------------------------------------------------------
	// ========================================================================



	/**
	 * Get EXIF information for a given file
	 *
	 * Returns an array of EXIF information in the format:
	 *   'Manufacturer' => 'Olympus',
	 *   'Model' => 'E-620',
	 *   'ISO' => '400',
	 *   . . .
	 *
	 * If the file can not be accessed, return an empty array,
	 * but typically we'll only be doing this when re-creating
	 * the index.kpa file - and that implies (assumes?) that we
	 * have access to the full repository.
	 *
	 * @param	string		image file name (fully pathed)
	 * @return	array		collection of EXIF data for the file
	 **/
	function _get_exif_from_file ( $image_path )  {
		// A flat array of strings of exif types - FNumber, ISOSpeedRatings, etc
		$exif_tags_of_interest = $this->config->item('exif_tags_of_interest');
		// This is what we'll return.
		$exif_array = array();

		if (file_exists ($image_path))  {
			$exif_info_from_file = exif_read_data ($image_path);
			foreach ($exif_tags_of_interest as $kpa_exif_tag => $exif_tag_info)  {
				foreach ($exif_tag_info['exif_tag_names'] as $synonym)  {
					if (isset ($exif_info_from_file[$synonym]))  {
						$value = trim ($exif_info_from_file[$synonym]);
						switch ($exif_tag_info['type'])  {
							case "rational" :
									$fraction = trim ((string)($value));
									// This method is slightly faster than using a preg function
									$slash_pos = strpos ($fraction, "/");
									if ($slash_pos !== FALSE)  {
										$dividend = substr ($fraction, 0, ($slash_pos));
										$divisor  = substr ($fraction, ($slash_pos + 1) );
										$value    = floor ($dividend / $divisor);
										}
									else  // No slash means it's .. too hard to work out.
										$value = $fraction;
									break;
							case "lookup" : // For flash only - the big ugly hex-value -> string
									/** Most cameras seem to store this in decimal format, but our
									 * lookup table is hex, with leading '0x'.  We have to sensibly
									 * convert in one place, and I've chosen here.
									 * @TODO Try to intelligently guess if a hex format came in */
									$hex_value = "0x" . (string) dechex((int)$value);
									$value = $exif_tag_info['lookup'][$hex_value];
									break;
							default:	// For string and integer types
									$value = trim ((string)($value));
									if (isset ($exif_tag_info['content_synonyms'][$value]))
										$value = $exif_tag_info['content_synonyms'][$value];
									break;
							}  // end-switch


						// Finally, if we have any suffixes or prefixes ...
						if (isset ($exif_tag_info['suffix']))
							$value = $value . $exif_tag_info['suffix'];
						if (isset ($exif_tag_info['prefix']))
							$value = $exif_tag_info['prefix'] . $value;

						$exif_array[$kpa_exif_tag] = $value;

						break;

						}  //end-if - kpa tag synonym was matched, hence that break there
					} // end-foreach - synonyms (we found one!)
				}  // end-foreach - exif_tags_of_interest
			}  // end-if - the original image file is available
		return  $exif_array;
		}  // end-method  _get_exif_from_file ()





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
		$shoosh_tags      = $this->config->item('shoosh_tags');
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
			if ( (isset ($shoosh_tags[$category]))  AND  (! in_array ($group_name, $shoosh_tags[$category]) ) )  {
				$mg_array[$category][$group_name][] = $tag;
				}
			}

		// Sort the sub-arrays, in-place, alphabetically.
		foreach ($mg_array as $category => $group)
			foreach ($group as $group_name => $tag)
				sort ( &$mg_array[$category][$group_name] );

		return $mg_array;
		}  //  end-method  _massage_member_groups ()


	}   // end-class  Kpa ()
