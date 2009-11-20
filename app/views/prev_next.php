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

// if ($prev_offset_by_page_url)
// 	echo anchor ($prev_offset_by_page_url , "<|") . nbs(4);

if ($prev_image_url)
	echo anchor ($prev_image_url ."/a", "&lt;prev");
else
	echo "&lt;prev";

echo nbs(2);

echo " (". $this_image_position ." <font size=\"-2\">of</font> ". $total_number_of_images .") ";

echo nbs(2);

if ($next_image_url)
	echo anchor ($next_image_url ."/a", "next&gt;");
else
	echo "next&gt;";

// if ($next_offset_by_page_url)
// 	echo nbs(4) . anchor ($next_offset_by_page_url , "|>");
