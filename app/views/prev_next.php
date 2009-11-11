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
 * prev_next  (view)
 *
 * Renders Prev and Next buttons - typically shown on left, just above Explorifier
 *
 *
 * @package     phoko
 * @version     v1
 * @author      jedd
 * @link        http://dingogully.com.au/trac/phoko
 **/

// ------------------------------------------------------------------------


if ($prev_image_url)
	echo anchor ($prev_image_url, "PREV");
else
	echo "PREV";

echo " (". $this_image_position ." / ". $total_number_of_images .") ";

if ($next_image_url)
	echo anchor ($next_image_url, "NEXT");
else
	echo "NEXT";
