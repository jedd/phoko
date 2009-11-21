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
 * Album
 *
 * Primary controller for the Phoko suite.
 *
 * @package     phoko
 * @version     v1
 * @author      jedd
 * @link        http://dingogully.com.au/trac/phoko
 **/

// ------------------------------------------------------------------------

class  Album extends  Controller {

	// ------------------------------------------------------------------------
	/**
	 *   Attributes
	 **/

	// This is where bits of the URL, once parsed, get dumped.  It's an array
	// of arrays, for instance $url_array['filters'] => array ('actual'=> 'foo', ...)
	var $url_array = array ();




	// ------------------------------------------------------------------------
	/**
	 *   Constructor
	 **/

	function  __construct ()  {
		parent::Controller();

		// A very basic breadcrumb system - primarily for internal use only, on redirects - sometimes
		// if a user clicks a link too fast (it seems) things can get confused, hence we check if
		// the two links are identical - if so we don't change anything.
		if ($this->session->userdata('uri_ultimate') != uri_string())  {
			$this->session->set_userdata('uri_penultimate' , $this->session->userdata('uri_ultimate'));
			$this->session->set_userdata('uri_ultimate', uri_string());
			}

		// Set the default theme for new users - resides purely in session data
		if (! $this->session->userdata('theme'))  {
			$this->session->set_userdata('theme', 'default');
			}

		// For use in the view(s)
		$this->data['theme'] = $this->session->userdata('theme');
		$this->data['valid_themes'] = $this->config->item('valid_themes');

		} // end-constructor



	// ------------------------------------------------------------------------
	/**
	 *	index
	 *
	 *	The entry point - probably just happy to redirect to phoko/album by default.
	 *
	 **/
	function  index ()  {
		redirect ('/album/gallery');

		}  // end-method  index ()



	// ------------------------------------------------------------------------
	/**
	 *	gallery
	 *
	 *	The default method for arriving users, and should handle most of the
	 *  'normal' stuff we're doing here.
	 *
	 * @param	unknown		we'll have some soon, I'm sure.
	 *
	 **/
	 function  gallery ( )  {
		// Load up the $kpa_full array with the images, tags, and member_groups
		/// @todo - set this up as part of the model's constructor?
		$this->Kpa->get_pictures();

		// Extract FILTERS from URL - we need this before doing almost everything else.
		$this->_extract_filters_from_url();

		// Generate the FILTERED list (kpa_filt) of images to show
		$total_number_of_images_in_set = $this->Kpa->generate_kpa_filt ($this->url_array['filters']);

		// Extract OFFSET from URL
		$this->_extract_offset_from_url();

		// Extract the image_id from the URL (it gets saved at $this->url_array['image_id')
		$this->_extract_image_id_from_url();

		// Optimising means re-calculating the offset if it's unreasonable or we are /a/djusting
		$this->_optimise_offset();

		// Add 'url less this filter' key/val to the $url_array['filters'] array
		// (couldn't do it earlier as it relies on having the rest of the URL parsed).
		$this->_generate_remove_this_filter_keys();

		// At this time, kpa->kpa_full and kpa->kpa_filt are both populated.  We'll
		// always choose to show kpa_filt, but need to differentiate elsewhere.
		$kpa_show = $this->Kpa->kpa_filt;

		// Prepare the generic view partials
		$this->data['title'] = $this->config->item('name');
		$this->data['footer_links'] = array ('Cache management' => '/album/cache');

		// Filters for Main View
		/// @todo we should move filter generation into a view partial
		if (isset ($this->url_array['filters']))
			$this->data['filters'] = $this->url_array['filters'];


		/// --------------------------------
		/// Generating the various view bits

		// This will save us a bit of typing
		$id     = $this->url_array['image_id'];
		$offset = $this->url_array['offset'];


		// The prev-next buttons (left)
		$prev_image_id = $this->Kpa->get_prev_image_id ($id);
		$next_image_id = $this->Kpa->get_next_image_id ($id);

		$prev_offset = $this->_get_prev_offset ();
		$next_offset = $this->_get_next_offset ();
		$prev_next_data['prev_image_url'] = ($prev_image_id) ? $this->_create_url_with_new_image_id ($prev_image_id , $prev_offset) : FALSE;
		$prev_next_data['next_image_url'] = ($next_image_id) ? $this->_create_url_with_new_image_id ($next_image_id , $next_offset) : FALSE;

		// The 'next {thumbs per page}' buttons - only need the offset to change.
		$prev_offset_by_page = $this->_get_prev_offset_by_page ();
		$next_offset_by_page = $this->_get_next_offset_by_page ();
		// The next/prev page jump links are probably confusing and redundant, after the slider has been implemented.
		// $prev_next_data['prev_offset_by_page_url'] = ($prev_offset_by_page) ? $this->_create_url_with_new_image_id ($id , $prev_offset_by_page) : FALSE;
		// $prev_next_data['next_offset_by_page_url'] = ($next_offset_by_page) ? $this->_create_url_with_new_image_id ($id , $next_offset_by_page) : FALSE;


		$prev_next_data['this_image_position'] = $this->Kpa->get_position_number ($id);
		$prev_next_data['total_number_of_images'] = $total_number_of_images_in_set;
		$this->data['prev_next_view'] = $this->load->view("prev_next", $prev_next_data, TRUE);


		// The image-info window (left)
		$current_image_info['id'] = $id;
		$current_image_info['image'] = $kpa_show['images'][$id];
		$current_image_info['url_array'] = $this->url_array;
		$this->data['image_info_view'] = $this->load->view("image_info", $current_image_info, TRUE);


		// The main picture window (middle)
		$image_repository = $this->config->item('repository');
		$image_original_file_name = $image_repository . $kpa_show['images'][$id]['file'];
		$main_image_stuff['path'] = $this->Kpa->prepare_image ( $id, $image_original_file_name, $kpa_show['images'][$id], $image_type = 'medium' );
		$this->data['content']['image_proper'] = $this->load->view ("render_image", $main_image_stuff, TRUE);


		// The thumbnail view (top)
		$this->Kpa->select_thumbs( $offset );
		$thumb_view_data['current_image_id'] = $id;
		$thumb_view_data['thumbs'] = $this->Kpa->thumbs;
		$thumb_view_data['max_offset'] = $this->_get_max_offset();
		$thumb_view_data['current_offset'] = $this->url_array['offset'];
		$thumb_view_data['url_sans_offset'] = $this->_create_url_with_no_offset();
		$thumb_view_data['every_date_stamp'] = $this->Kpa->create_date_stamp_array();
		$this->data['content']['top'] = $this->load->view ("render_thumbs", $thumb_view_data, TRUE);


		// Load the primary view
		$this->load->view ("main_page", $this->data);
		}  // end-method  gallery ()





	// ------------------------------------------------------------------------
	/**
	 * Cache management
	 *
	 * Offers functions for cache management - specifically showing
	 * cache usage, missing images, extraneous images, and functions
	 * to tidy either up.
	 *
	 * @param	unknown		but we'll definitely want a couple .. tidy/edit/?
	 * @return	integer
	 **/
	function  cache ( $action = FALSE , $file_type = FALSE )  {
		// Default (absent any parameters) will be to show cache stats.

		if ($action == "create")
			$this->_cache_create_items ($file_type);

		if ($action == "delete")
			$this->_cache_delete_items ($file_type);

		// Prepare the view partials
		$this->data['title'] = "Cache Management";
		$this->data['footer_links'] = array ('Main gallery' => '/album/gallery');
		$this->data['content']['top'] = "Use the <b>Main Gallery</b> link bottom right to return to the gallery.";

		$cache_view['cache_file_list'] = $this->Kpa->cache_get_list_of_files();
		$kpa_full = $this->Kpa->get_pictures();
		$cache_view['kpa_db_images'] = $kpa_full['images'];
		$cache_view['stats'] = $this->_compare_cache_with_kpa_db($cache_view['cache_file_list'], $cache_view['kpa_db_images']);
		$this->data['content']['main'] = $this->load->view('cache_status', $cache_view, TRUE);

		// Load the primary view
		$this->load->view ("main_page", $this->data);

		// Note to self - in the cache model, when making new files, to
		// protect us from harm in the event that we get a PHP timeout
		// mid-creation of the image, we should create it with a dummy
		// name, and then rename it as the last step - that way it will
		// be easy (or irrelevant, take your pick) to remove the temp
		// filename, and continue recreating missing files.  This is
		// assuming renames are atomic (which is a fairly safe bet)

		}  // end-method  cache ()



	// ------------------------------------------------------------------------
	/**
	 * Cache create items
	 *
	 * Generates cache entries by converting image files into the
	 * various categoried .. cache images.
	 *
	 * There's no elegant way to tell if we've run out of time on
	 * a PHP instance, so we just power on and let the user work it
	 * out on a subsequent reload of the page.  Best we can do.  We
	 * try to protect the user from half-created cache files by
	 * generating a new file with a temporary name, and then copying
	 * it into place as the final act - this should afford some degree
	 * of safety from cruft.  Subsequent attempts to create a cache
	 * file only check for the presence of the cache file proper, not
	 * the temporary file - so clashes should be minimal.
	 *
	 * @param	string		$file_type	Type of cache (small, medium, large)
	 **/
	function  _cache_create_items ($file_type)  {
		return;
		}  // end-method _cache_create_items ()



	/**
	 * Cache delete items
	 *
	 * Deletes extraneous entries in the cache directories.
	 *
	 * @param	string		$file_type	Type of cache (small, medium, large)
	 **/
	function  _cache_delete_items ($file_type)  {
		echo $file_type;

		$cache_file_list = $this->Kpa->cache_get_list_of_files();
		$kpa_full = $this->Kpa->get_pictures();
		$cache_differential = $this->_compare_cache_with_kpa_db($cache_file_list, $kpa_full);

		dump ($cache_differential);
		return;
		}  // end-method _cache_delete_items ()




	// ------------------------------------------------------------------------
	/**
	 * Settings
	 *
	 * Offers ways to change user settings - typically pushes something into
	 * the session array and then immediately redirects from whence it came.
	 *
	 * @param	string		$thing	What we're setting
	 * @param	string		$value	What we're changing it to
	 **/
	function  settings ( $thing = NULL , $value = NULL )  {
		$return_to = $this->session->userdata('uri_penultimate');

		// All settings are consistently formatted - so easy to switch() on.
		switch ($thing)  {
			case "theme":
					$valid_themes = $this->config->item ('valid_themes');
					if (isset ($valid_themes[$value]))
						$this->session->set_userdata('theme', $value);
					break;
			}

		// We never display a 'settings' screen - so we return to origin here.
 		redirect ($return_to);
		}  // end-method  settings ()




	// ========================================================================
	// ------------------------------------------------------------------------
	// P R I V A T E   F U N C T I O N S  -- nothing to see here.
	// ------------------------------------------------------------------------
	// ========================================================================


	// ------------------------------------------------------------------------
	/**
	 * Optimise offset
	 *
	 * Returns the best offset for the thumbnail view.
	 *
	 * We call this on every page load, but we only change the /offset under
	 * one of two conditions:
	 *
	 *  *  an /a flag in the URL (means to adjust - present on filter links etc)
	 *  *  the offset we have is greater than the max offset for the set.
	 *
	 **/
	function   _optimise_offset  ()  {
		$optimise_the_offset = FALSE;
		$adust_url_flag_present = FALSE;
		$is_current_image_id_not_shown_in_thumbnails = TRUE;

		$thumbs_per_page  = $this->config->item('thumbs_per_page');
		$image_position = $this->Kpa->get_position_number($this->url_array['image_id']);

		$segs  = $this->uri->segment_array();
		$seg_x = 3;
		while ( isset($segs[$seg_x]) )  {
			$segment = $segs[$seg_x];
			if ($segment[0] == 'a')
				$adust_url_flag_present = TRUE;
			$seg_x++;
			}

		// This will catch those /next/prev/ buttons that default to having /a
		// appended - we really only want to respect that if the image we're
		// showing IS NOT going to be visible in the thumbs too.
		$leftmost_thumb  = $this->url_array['offset'];
		$rightmost_thumb = $this->url_array['offset'] + $thumbs_per_page;
		if (($image_position >= $leftmost_thumb) AND ($image_position <= $rightmost_thumb) ) {
			$is_current_image_id_not_shown_in_thumbnails = FALSE;
			}

		if ($is_current_image_id_not_shown_in_thumbnails AND $adust_url_flag_present)
			$optimise_the_offset = TRUE;

		// And if we're clearly out of bounds ...
		$max_offset = $this->_get_max_offset();
		if ($this->url_array['offset'] > $max_offset)
			$optimise_the_offset = TRUE;

		if ($optimise_the_offset)
			$this->url_array['offset'] = $image_position;

		}  // end-method  _optimise_offset ()



	// ------------------------------------------------------------------------
	/**
	 * Get max offset
	 *
	 * Returns the largest possible valid offset (for thumbnails).
	 *
	 * @return	integer
	 **/
	function   _get_max_offset  ()  {
		$thumbs_per_page  = $this->config->item('thumbs_per_page');

		$max_offset = sizeof ($this->Kpa->kpa_filt['images']) - $thumbs_per_page + 1;

		return $max_offset;
		}  //  end-method  _get_max_offset();



	// ------------------------------------------------------------------------
	/**
	 * Get previous offset number (if valid)
	 *
	 * Returns an integer, somewhere betweeen 1 and ( sizeof($kpa_filt) - $thumbs_to_show)
	 *
	 * @return	integer
	 **/
	function   _get_prev_offset  ()  {
		if ($this->url_array['offset'] > 1)
			return ($this->url_array['offset'] - 1);
		else
			return FALSE;
		}  // end-method  _get_prev_offset ()



	// ------------------------------------------------------------------------
	/**
	 * Get next offset number (if valid)
	 *
	 * Returns an integer, somewhere betweeen 1 and ( sizeof($kpa_filt) - $thumbs_to_show)
	 *
	 * @return	integer
	 **/
	function   _get_next_offset  ()  {
		$max_offset = $this->_get_max_offset();
		if ( $this->url_array['offset'] < $max_offset)
			return ($this->url_array['offset'] + 1);
		else
			return $max_offset;
		}  // end-method  _get_next_offset ()



	// ------------------------------------------------------------------------
	/**
	 * Get prev offset by page (thumbs-per-page)
	 *
	 * Returns an integer, somewhere betweeen 1 and ( sizeof($kpa_filt) - $thumbs_to_show)
	 *
	 * @return	integer
	 **/
	function   _get_prev_offset_by_page  ()  {
		$thumbs_per_page = $this->config->item('thumbs_per_page');

		if ($this->url_array['offset'] > $thumbs_per_page)
			return ( $this->url_array['offset'] - $thumbs_per_page);
		else
			return 1;

		} // end-method  _get_prev_offset_by_page ()



	// ------------------------------------------------------------------------
	/**
	 * Get next offset by page (thumbs-per-page)
	 *
	 * Returns an integer, somewhere betweeen 1 and ( sizeof($kpa_filt) - $thumbs_to_show)
	 *
	 * @return	integer
	 **/
	function   _get_next_offset_by_page  ()  {
		$thumbs_per_page = $this->config->item('thumbs_per_page');
		$max_offset      = $this->_get_max_offset();

		if ($this->url_array['offset']  <  ($max_offset - $thumbs_per_page))
			return ($this->url_array['offset'] + $thumbs_per_page);
		else
			return $max_offset;

		}  // end-method  _get_next_offset_by_page ()




	// ------------------------------------------------------------------------
	/**
	 * Extract offset from URL
	 *
	 * Pull the /o... entry from the URL we arrived with, or assume '1' if
	 * not present.  Save directly to $this->url_array['offset']
	 *
	 **/
	function  _extract_offset_from_url  ( )  {
		$segs  = $this->uri->segment_array();

		$seg_x = 3;						// We start at segment(3)
		$farray = array ();				// filter array - our return data

		$offset = FALSE;
		while ( isset($segs[$seg_x]) )  {
			$segment = $segs[$seg_x];
			if ($segment[0] == 'o')  {
				$offset_segment = substr($segment, 1);
				if (is_numeric($offset_segment))
					$offset = $offset_segment;
				}
			$seg_x++;
			}

		if (! $offset)  {
			// Determine sanity of offset setting ...
			$total_number_of_images_in_set = $this->Kpa->generate_kpa_filt ($this->url_array['filters']);
			$thumbs_per_page = $this->config->item('thumbs_per_page');


			if ($offset > ($total_number_of_images_in_set  - $thumbs_per_page + 1))
				$offset = 1;
			}

		$this->url_array['offset'] = $offset;
		}


	// ------------------------------------------------------------------------
	/**
	 * Extract filters from URL
	 *
	 * Pull any /f... entries from the URL we arrived with.
	 *
	 * $this->url_array['filters'] is set with whatever it finds (or FALSE on empty)
	 *
	 * @return	bool	TRUE if filters exist, FALSE otherwise
	 **/
	function  _extract_filters_from_url  ( )  {
		$segs  = $this->uri->segment_array();

		$seg_x = 3;						// We start at segment(3)
		$farray = array ();				// filter array - our return data

		// K=Keywords, L=Locations ...
		$category_abbreviations = $this->config->item('category_abbreviations');

		while ( isset($segs[$seg_x]) )  {
			$segment = $segs[$seg_x];
			if ($segment[0] == 'f')  {
				/// @todo exclude filters will start with 'e' or something
				/// @todo do we cull > 5 filters here, elsewhere, or allow infinite filters?
				$filter_category = array_search ($segment[1], $category_abbreviations);
				$farray[] = array (
							"actual" => rawurldecode (substr($segment, 2)),		// eg 'foo bar'
							"urlencoded" => substr($segment, 2),				// eg 'foo_bar'
							"category" => $filter_category,						// eg 'Keywords'
							);
				}
			$seg_x++;
			}

		$filters_actual = array();
		if (isset ($farray))  {
			foreach ($farray as $filter)
				$filters_actual[] = $filter['actual'];
			}
		else
			$farray = NULL;


		$this->url_array['filters'] = (sizeof ($farray) > 0)  ?  $farray  :  FALSE;
		$this->url_array['filters_actual'] = (sizeof ($filters_actual) > 0)  ?  $filters_actual  :  FALSE;

		return ($farray) ?  TRUE  :  FALSE;
		} // end-method  extract_filters_from_url



	// ------------------------------------------------------------------------
	/**
	 * Extract image_id from URL
	 *
	 * Pull the /i... entry from the URL we arrived with.
	 *
	 * $this->url_array['image_id'] is set with whatever it finds,
	 * or given a default value (the first ID in the current set)
	 *
	 **/
	function  _extract_image_id_from_url  ( )  {
		$segs  = $this->uri->segment_array();

		$seg_x = 3;						// We start at segment(3)
		$farray = array ();				// filter array - our return data
		$image_id = FALSE;

		while ( isset($segs[$seg_x]) )  {
			$segment = $segs[$seg_x];
			/// @todo Do we want to handle errors of multiple /i's or just ignore them?
			if ($segment[0] == "i")
				$image_id = substr($segment, 1);
			$seg_x++;
			}

		// If none is given on the URL, take the first in the filtered set we have.
		if (! $image_id)
			$image_id = $this->Kpa->get_first_image_id_from_kpa_filt();

		$this->url_array['image_id'] = $image_id;
		}  // end-method  _extract_image_id_from_url  ()




	// ------------------------------------------------------------------------
	/**
	 * Generate a 'remove this filter' value for all filters
	 *
	 * Update url_array['filters'] with 'url_minus_this_filter' key
	 *
	 */
	function  _generate_remove_this_filter_keys ( )  {
		$segs = $this->uri->segment_array();
		$category_abbreviations = $this->config->item('category_abbreviations');

		$base_url = $segs[1] ."/". $segs[2] ;
		$base_url .= "/i". $this->url_array['image_id'];
		$base_url .= "/o". $this->url_array['offset'];

		// Okay - loosely, we take a copy of the set of filters, and do a loop
		// within a loop - the outer loop is where we generate the 'url minus
		// this particular filter'.  The inner loop runs through each filter,
		// comparing it to the current outer-loop filter - if it's not a match
		// then we keep it in the new URL we're generating (in other words, if
		// they match, quietly drop it from the URL). Inside the outer loop we
		// then assign this new URL to the ['url_minus_this_filter'] key.

		$x = 0;
		$copy_of_filters = $this->url_array['filters'];
		while (isset ($this->url_array['filters'][$x])) {
			$this_filter_urlencoded = $this->url_array['filters'][$x]['urlencoded'];
			$new_url = "";
			foreach ($copy_of_filters as $copy_filter)  {
				$category = $copy_filter['category'];
				$category_code = $category_abbreviations[$category];
				if ($this_filter_urlencoded != $copy_filter['urlencoded'])
					$new_url .= "/f". $category_code . $copy_filter['urlencoded'];
				}
			$this->url_array['filters'][$x]['url_minus_this_filter'] = $base_url . $new_url;
			$x++;
			}

		}  //  end-method  _create_url_minus_this_segment ()



	// ------------------------------------------------------------------------
	/**
	 * Create URL with new image ID
	 *
	 * Replaces the URL's current image ID (if it exists) with this new one.
	 *
	 * Good for then generating things like links under thumbnails and the like.
	 *
	 * @param	string	$image_id
	 * @param	int		$offset (optional) if you want to change this at the same time
	 * @return	string
	 */
	function  _create_url_with_new_image_id  ($new_image_id, $new_offset = FALSE)  {
		$segs    = $this->uri->segment_array();
		$new_url = $segs[1] ."/". $segs[2];

		// We used to build the new URL based on the current uri segments,
		// but now we build it from scratch using $this->url_array components.

		/// image_id will ALWAYS be present - @todo generate an error if not found?
		$new_url .= "/i" . $new_image_id;

		// Filters
		if ($this->url_array['filters'])  {
			$category_abbreviations = $this->config->item('category_abbreviations');
			foreach ($this->url_array['filters'] as $filter)  {
				$category = $filter['category'];
				$new_url .= "/f" . $category_abbreviations[$category] . $filter['urlencoded'];
				}
			}

		/// offset will ALWAYS be present - @todo generate an error if not found?
		if ($new_offset)
			$new_url .= "/o" . $new_offset;
		else
			$new_url .= "/o" . $this->url_array['offset'];

		return $new_url;
		}  // end-method  _create_url_with_new_image_id  ()




	// ------------------------------------------------------------------------
	/**
	 * Create URL with no offset value
	 *
	 * Generates a full current URL but without any /o offset value at all.  We
	 * need this for the jquery-ui SLIDER that we use in the thumbnail section,
	 * as we add the /o bit in there dynamically.
	 *
	 * @return	string
	 */
	function  _create_url_with_no_offset  ( )  {
		$segs    = $this->uri->segment_array();
		$new_url = $segs[1] ."/". $segs[2];

		$new_url .= "/i" . $this->url_array['image_id'];

		// Filters
		if ($this->url_array['filters'])  {
			$category_abbreviations = $this->config->item('category_abbreviations');
			foreach ($this->url_array['filters'] as $filter)  {
				$category = $filter['category'];
				$new_url .= "/f" . $category_abbreviations[$category] . $filter['urlencoded'];
				}
			}

		return $new_url;
		}  // end-method  _create_url_with_new_image_id  ()





	// ------------------------------------------------------------------------
	/**
	 * Compare cache (reality) with kpa_full (ideal)
	 *
	 * Returns an array containing the following information:
	 * [small]
	 *		[cache_size] => 7
	 *		[cache_count] => 1
	 *		[extraneous_count] => 1
	 *		[missing_count] => 565
	 *		[extraneous] => Array
	 *			(
	 *				[0] => 63b6574ab3
	 *				...
	 *			)
	 *		[missing] => Array
	 *			(
	 *				[0] => 63b6574ab4
	 *				[1] => e27fcf562a
	 *				...
	 *			)
	 * [ ... ]  // (repeated for 'medium' and 'large' sizes)
	 * [kpa]
	 *		[total] => 565
	 *
	 * @param	array		$cache_file_list		All the cache files currently on disk
	 * @param	array		$kpa_images_full		The [images] sub-array ONLY of $kp_dba
	 * @return	array
	 **/
	function  _compare_cache_with_kpa_db  ($cache_file_list , $kpa_images_full)  {
		// Prepare the pro forma $stats array that we'll be returning
		$stats = array();
		$image_sizes = $this->config->item('image_sizes');
		foreach ($image_sizes as $type => $foo)  {
			$stats[$type]['cache_size'] = 0;			// Running total of cache files
			$stats[$type]['cache_count'] = 0;			// Number of cache files
			$stats[$type]['extraneous_count'] = 0;		// Cache files we don't need
			$stats[$type]['missing_count'] = 0;			// Cache files we can't find
			}

		/// First - get the simple summary statistics
		foreach ($cache_file_list as $type => $file_info)
			foreach ($file_info as $file_name => $size)  {
				$stats[$type]['cache_size'] += $size;
				$stats[$type]['cache_count'] ++;
				}

		/// Second - tidy up the kpa_db_images array - we really only want a
		/// very simple array like ('aabbccddee', 'bbccddeeff', 'ccddeeff00' ...)
		$kpa_db_images = array();
		foreach ($kpa_images_full as $name => $foo)
			$kpa_db_images[] = $name;
		$stats['kpa']['total'] = count ($kpa_db_images);

		/// Third - go through the cache directory list and work out what files
		/// are EXTRANEOUS - ie. not represented in the kpa_db_images
		foreach ($cache_file_list as $type => $file_info)
			foreach ($file_info as $file_name => $foo)
				if (! in_array ($file_name, $kpa_db_images))  {
					$stats[$type]['extraneous_count'] ++;
					$stats[$type]['extraneous'][] = $file_name;
					}

		/// Fourth - go through the kpa_db_images list and work out what files
		/// are MISSING - ie. not present in the cache.
		foreach ($kpa_db_images as $kpa_image_id)
			foreach ($cache_file_list as $type => $files)
				if (! isset ($files[$kpa_image_id]))  {
					$stats[$type]['missing_count'] ++;
					$stats[$type]['missing'][] = $kpa_image_id;
					}

		return $stats;
		}  // end-method  _compare_cache_with_kpa_db  ()



	}   // end-class  album ()

/* End of file album.php */
/* Location: ./app/controllers/album.php */
