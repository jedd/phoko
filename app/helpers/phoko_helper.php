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
function  dump ($var)  {
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
	} // end-function  dump ()


// ------------------------------------------------------------------------
/**
 * Pretty date
 *
 * Takes one parameter - an ISO8601 formatted date - and returns it
 * in slightly more human readable form.
 *
 * eg. 2009-05-31T16:42:07  ====>  2009-05-31 , 4pm
 *
 * @param	$date_in	string (iso8601 date)
 * @return	string
 **/
function  pretty_date ( $date_in )  {
	if (strlen ($date_in) != 19)
		return $date_in;				// return in confusion!

	$hh = substr ($date_in, 11, 2);
	$mm = substr ($date_in, 14, 2);

	if ($mm > 30)
		$hh++;

	if ( ($hh > 23) OR ($hh == '00'))
		$hh_string = "midnight";
	else
		if ($hh > 12)
			$hh_string = ($hh - 12) ."pm";
		else
			if ($hh == 12)
				$hh_string = "midday";
			else
				$hh_string = $hh ."am";

	$output = substr ($date_in, 0, 10) . nbs(1) ."<font class=\"date_tilde\">~</font>". nbs(1) . $hh_string;

	return $output;
	}  // end-function  pretty_date ()




/* End of file phoko_helper.php */
/* Location: ./app/helpers/phoko_helper.php */
