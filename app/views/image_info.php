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
 * image_info  (view)
 *
 * Prepare the tabulated data that will appear in the 'This image' tab,
 * on the standard gallery page.
 *
 * This includes image EXIF information, tags with links to categories,
 * and description text etc.
 *
 * @package     phoko
 * @version     v1
 * @author      jedd
 * @link        http://dingogully.com.au/trac/phoko
 **/

// ------------------------------------------------------------------------

if ($image['startDate'] == $image['endDate'])
	echo pretty_date($image['startDate']);
else
	echo "Between: ". $image['startDate'] ." and ". $image['endDate'];

echo "\n<hr />\n";

if (isset ($image['tags']))  {
	echo "<ul class=\"image_tags_headings\">\n";
	foreach ($image['tags'] as $category => $tags)  {
		echo "<li>\n";
		echo $category .":\n";
		echo "</li>\n";
		echo "<ul class=\"image_tags\">\n";
		foreach ($tags as $tag)  {
			echo "<li>\n";
			echo anchor ("/gallery/settings/filter/" , $tag) . "\n";
			echo "</li>\n";
			}
		echo "</ul>\n";
		}
	echo "</ul>\n";
	}

echo "\n<hr />\n";

echo $image['description'];

