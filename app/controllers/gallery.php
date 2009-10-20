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
		} // end-constructor



	// ------------------------------------------------------------------------
	/**
	 *	index
	 *
	 *	The first thing an arriving visitor sees.
	 **/
	function  index ()  {


		$this->load->view ("main_page");
		// $this->load->view ("fiew");
		}  // end-method  index ()



	}   // end-class  gallery ()

/* End of file gallery.php */
/* Location: ./app/controllers/gallery.php */
