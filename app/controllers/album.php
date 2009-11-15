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

		$prev_next_data['this_image_position'] = $this->Kpa->get_position_number ($id);
		$prev_next_data['total_number_of_images'] = sizeof ($this->Kpa->kpa_filt['images']);
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
	function  cache ( )  {
		// Default (absent any parameters) will be to show cache stats.

		// Prepare the view partials
		$this->data['title'] = "Cache Management";
		$this->data['footer_links'] = array ('Main gallery' => '/album/gallery');
		$this->data['content']['top'] = "Cache management.<br />Use the <b>Main Gallery</b> link bottom right to return to the gallery.";

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
		$max_offset = $this->Kpa->get_max_offset();

		if ( $this->url_array['offset'] <= $max_offset)
			return ($this->url_array['offset'] + 1);
		else
			return FALSE;
		}  // end-method  _get_next_offset ()







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
			$thumbs_per_page = $this->config->item('thumbs_per_page');
			$total_number_of_images_in_set = $this->Kpa->generate_kpa_filt ($this->url_array['filters']);

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
							"actual" => urldecode (substr($segment, 2)),		// eg 'foo bar'
							"urlencoded" => substr($segment, 2),				// eg 'foo_bar'
							"url_minus_this_filter" => $this->_create_url_minus_this_segment($segs, $segment),
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
	 * Create URL minus this segment
	 *
	 * Takes a full segment dump, and removes segment in question, and
	 * returns the segment dump back.  Hopefully a one or two liner,
	 * that we can relocate into the _parse_url() function - but might
	 * need more smarts, and might be used elsewhere.
	 *
	 * @param	array	$segs		A full uri->segment_array()
	 * @param	string	$segment	The segment we want to remove
	 * @return	array
	 */
	function  _create_url_minus_this_segment ($segs, $segment)  {
		$newuri = "";
		foreach ($segs as $seg)
			if ($seg != $segment)
				$newuri .= $seg ."/";
		return $newuri;
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
