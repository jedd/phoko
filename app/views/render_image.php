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
 * render_image  (view)
 *
 * Prepare the main image window - showing the 'medium' sized image,
 * and that's about it!
*
 *
 * @package     phoko
 * @version     v1
 * @author      jedd
 * @link        http://dingogully.com.au/trac/phoko
 **/

// ------------------------------------------------------------------------

$image_properties = array(
           'src' => $path,
           'alt' => 'something here',
           // 'class' => 'post_images',
           'width' => '100%',
           // 'height' => '200',
           'title' => 'something else here',
           // 'rel' => 'lightbox',
 );

echo img($image_properties);