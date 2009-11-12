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
 * render_thumbs  (view)
 *
 * Prepare the thumb bit - at the top of the screen usually - using the
 * 'small' sized image, and that's about it!
*
 *
 * @package     phoko
 * @version     v1
 * @author      jedd
 * @link        http://dingogully.com.au/trac/phoko
 **/

// ------------------------------------------------------------------------


foreach ($thumbs as $thumb_id => $thumb)  {
	if ($current_image_id != $thumb_id)
		echo "\n<a class=\"img_thumb\" href=\"". site_url() . $thumb['link'] ."\">";

	$image_properties = array(
						'src' => $thumb['file_name'],
						'alt' => $thumb['description'],
						'height' => '70px',
						'title' => $thumb['description'],
						'border' => '0'
						);

	if ($current_image_id == $thumb_id)  {
		$image_properties['border'] = "1";
		}
	echo img($image_properties);
	if ($current_image_id != $thumb_id)
		echo "</a>\n";

	echo nbs(1);
	}
