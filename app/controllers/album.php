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


	// ------------------------------------------------------------------------
	/**
	 *   Constructor
	 **/

	function  __construct ()  {
		parent::Controller();

		// A very basic breadcrumb system - primarily for internal use only, on redirects - sometimes
		// if a user clicks a link too fast (it seems) things can get confused, hence we check if
		// the two links are identical - if so we don't change anything.
		if ($this->session->userdata('uri_ultimate') != $this->session->userdata('uri_penultimate'))  {
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
		// Load up the $kpa_db array with the images, tags, and member_groups
		$kpa_db = $this->Kxml->get_pictures();

		// Prepare the generic view partials
		$this->data['title'] = $this->config->item('name');
		$this->data['footer_links'] = array ('Cache management' => '/album/cache');
		$this->data['content']['top'] = "Thumbnails will appear up here.";
		$this->data['content']['main'] = "Normal dispay stuff will appear in here - usually just a picture, with some navigation tools wrapped around it.";

		// View partial for the current image information
		$id = '42f6e42680'; /// @todo obviously need to pull this in from somewhere dynamically
		$current_image_info['id'] = $id;
		$current_image_info['image'] = $kpa_db['images'][$id];
		$this->data['image_info_view'] = $this->load->view("image_info", $current_image_info, TRUE);

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
		$this->load->model("Cache");

		// Prepare the view partials
		$this->data['title'] = "Cache Management";
		$this->data['footer_links'] = array ('Main gallery' => '/album/gallery');
		$this->data['content']['top'] = "Cache management.<br />Use the <b>Main Gallery</b> link bottom right to return to the gallery.";

		$cache_view['cache_file_list'] = $this->Cache->get_list_of_cache_files();
		$kpa_db_full = $this->Kxml->get_pictures();
		$cache_view['kpa_db_images'] = $kpa_db_full['images'];
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
	 * Compare cache (reality) with kpa_db (ideal)
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
	 * @param	array		$kpa_db_images_full		The [images] sub-array ONLY of $kp_dba
	 * @return	array
	 **/
	function  _compare_cache_with_kpa_db  ($cache_file_list , $kpa_db_images_full)  {
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
		foreach ($kpa_db_images_full as $name => $foo)
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
