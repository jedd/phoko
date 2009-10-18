<?php

class Gallery extends Controller {

	function Gallery()
	{
		parent::Controller();	
	}
	
	function index()
	{
		$this->load->view('welcome_message');
	}
}

/* End of file gallery.php */
/* Location: ./app/controllers/gallery.php */
