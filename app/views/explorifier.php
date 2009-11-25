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
 * explorifier  (view)
 *
 * Prepare the accordion data that will appear in the 'Explorifier' tab,
 * on the standard gallery page.
 *
 * This is basically tag categories (Places, Keywords etc) shown as
 * accordion headings, with member categories and normal categories
 * shown as href's to filter actions.
 *
 * @package     phoko
 * @version     v1
 * @author      jedd
 * @link        http://dingogully.com.au/trac/phoko
 **/

// ------------------------------------------------------------------------

$category_abbreviations = $this->config->item('category_abbreviations');

echo "<div id=\"accordion\">";

foreach ($categories as $category)  {
	echo "<h3><a href=\"#\">". $category ."</a></h3>\n";
	echo "<div>\n";

	echo "<ul class=\"explorifier_tags\">\n";

	if ( isset ($tag_counts['kpa_filt'][$category]) )  {
		foreach ($tag_counts['kpa_filt'][$category] as $tag => $tag_count)  {

			if (  ( (! isset ($url_array['filters_actual'])) OR (! $url_array['filters_actual']) )
				OR ( (is_array($url_array['filters_actual'])) AND (! in_array($tag, $url_array['filters_actual'])) )  ) {
				$url_with_this_as_new_filter = current_url() ."/f". $category_abbreviations[$category] . rawurlencode ($tag);
				echo "<li>". anchor ($url_with_this_as_new_filter , $tag, array ('title'=>'Add this as a filter')) ." <font class=\"subdued\">(". $tag_count .")</font></li>\n";
				}
			else
				echo "<li><font class=\"subdued\">". $tag ." (". $tag_count .")</font></li>\n";
			}
		}
	else
		echo "<li>No valid tags in this category with current filters</li>\n";


	echo "</ul>";

	echo "</div>\n";
	}

echo "</div>";  // end-div 'accordion'
