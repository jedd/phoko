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
 * Gallery
 *
 * Primary controller for the Phoko suite.
 *
 * @package     phoko
 * @version     v1
 * @author      jedd
 * @link        http://dingogully.com.au/trac/phoko
 **/

// ------------------------------------------------------------------------

class  Gallery extends  Controller {

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

		// Models
		$this->load->model ("Kxml");
		} // end-constructor



	// ------------------------------------------------------------------------
	/**
	 *	index
	 *
	 *	The first thing an arriving visitor sees.
	 **/
	function  index ()  {
		// Get all our settings - directories, paths, image sizes, etc - store in $this->config[phoko]
		$this->load->config ('phoko', TRUE);
		$config = $this->config->item('phoko');


		// Load up the $pictures array with all the pictures we're going to show
		$pictures = $this->Kxml->get_pictures($config['index_xml_file']);



		$this->load->view ("main_page");
		// $this->load->view ("fiew");
		}  // end-method  index ()



	}   // end-class  gallery ()

/* End of file gallery.php */
/* Location: ./app/controllers/gallery.php */
