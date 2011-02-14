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
		/// @todo work out a better place to put this
		/// This is truly ugly, I know, but the underscore is used in space-embedded
		/// category keys (eg. The_Farm) however it is NOT replaced consistently in
		/// KPA's XML, and consequently we have to deal with it somewhere.  This will
		/// in turn obviously break any underscores legitimately contained in category
		/// keys - but OTOH this code will more likely continue to act sanely if/when
		/// KPA fixes up its current inconsistency with handling embedded spaces.
		$category = str_replace ("_", " ", $category);

		echo "<li>\n";
		echo "<font class=\"various_headings\">". $category .": </font>\n";
		echo "</li>\n";
		echo "<ul class=\"image_tags\">\n";

		// Under each category (eg. 'Persons') we have a line for each tag (eg. 'jedd')
		foreach ($tags as $tag)  {
			echo "<li>\n";
			/// @todo later we can also disable linking a tag if we've reached X filter count
			// The 2nd half of the OR is only ever evaluated IF we have filters
			if ( ( (! isset ($url_array['filters_actual'])) OR (! $url_array['filters_actual']) )
				OR ( ( isset ($url_array['filters_actual'])) AND (is_array($url_array['filters_actual'])) AND (! in_array ($tag, $url_array['filters_actual'])) ) )  {
				$url_with_this_as_new_filter = current_url() ."/f". $category_abbreviations[$category] . rawurlencode ($tag);
				echo anchor ($url_with_this_as_new_filter , $tag, array ('title'=>'Add this as a filter'));
				}
			else
				echo $tag;

			// We always show tag counts (regardless of whether it's an active link or not)
			// If filt count == full count, then only show the one figure.
			// Because the visible image is not guaranteed to be in the thumb-visible set, we test first

			echo "<font class=\"subdued\">";
			if ( isset ($tag_counts['kpa_filt'][$category][$tag]))
				$tcfilt = $tag_counts['kpa_filt'][$category][$tag];
			else
				$tcfilt = FALSE;

			if ( $tcfilt == ($tcfull = $tag_counts['kpa_full'][$category][$tag]) )
				echo nbs(3) . "( " . $tcfilt ." )\n";
			else
				echo nbs(3) . "( " . $tcfilt ." : ". $tcfull ." )\n";
			echo "</font>";


			echo "</li>\n";
			}
		echo "</ul>\n";
		}
	echo "</ul>\n";
	}


if (isset ($image['exif']))  {
	echo "\n<hr />\n";
	echo "<font class=\"subdued\">";
	echo "EXIF<br />";
	// dump ($image['exif']);
	foreach ($image['exif'] as $name => $value)  {
		echo nbs(3) . $name .":". nbs(2) . $value ."<br />";
		}
	echo "</font>";
	}

echo "\n<hr />\n";

echo nl2br($image['description']);


