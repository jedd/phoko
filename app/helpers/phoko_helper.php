<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Phoko
 *
 * Web gallery for a KPhotoAlbum repository
 *
 * phoko_helper - contains all the rinky dinky little functions we rely on to do Stuff.
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
 * Super Dump!
 *
 * Takes one parameter, and var_dumps that sucker (well, it print_r's
 * it at the moment as it produces a tighter output).
 *
 * @param anything The variable to var_dump() straight to screen.
 **/
function dump($var)  {
	// echo "<pre>".__FILE__.' @ line: '.__LINE__ .'<br />Result: ';
	echo "<pre>";
	if (isset ($var))
		if ($var)
			print_r ($var);
		else
			echo "dump() value FALSE or NULL";
	else
		echo "dump() value not set.";
	echo "</pre>";
	} // end-function pdb_var_dump ()




/* End of file phoko_helper.php */
/* Location: ./app/helpers/phoko_helper.php */
