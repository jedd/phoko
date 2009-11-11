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

foreach ($thumbs as $thumb)  {
	echo "\n<a class=\"img_thumb\" href=\"". site_url() . $thumb['link'] ."\">";
	$image_properties = array(
						'src' => $thumb['file_name'],
						'alt' => $thumb['info']['description'],
						'height' => '70px',
						'title' => $thumb['info']['description'],
						'border' => '0'
						);
	echo img($image_properties);
	echo "</a>\n";

	echo nbs(1);
	}
