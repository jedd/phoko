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

$category_abbreviations = $this->config->item('category_abbreviations');

if (isset ($image['tags']))  {
	echo "<ul class=\"image_tags_headings\">\n";
	foreach ($image['tags'] as $category => $tags)  {
		echo "<li>\n";
		echo "<font class=\"various_headings\">". $category .": </font>\n";
		echo "</li>\n";
		echo "<ul class=\"image_tags\">\n";
		foreach ($tags as $tag)  {
			echo "<li>\n";
			/// @todo later we can also disable linking a tag if we've reached X filter count
			// The 2nd half of the OR is only ever evaluated IF we have filters
			if ( ( ! isset ($url_parsed['actual_filters'])) OR (! in_array ($tag, $url_parsed['actual_filters'])) )  {
				$url_with_this_as_new_filter = current_url() ."/f". $category_abbreviations[$category] . rawurlencode ($tag);
				echo anchor ($url_with_this_as_new_filter , $tag, array ('title'=>'Add this as a filter')) . "\n";
				}
			else
				echo $tag;
			echo "</li>\n";
			}
		echo "</ul>\n";
		}
	echo "</ul>\n";
	}

echo "\n<hr />\n";

echo $image['description'];

