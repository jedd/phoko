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

echo "<div id=\"accordion\">";

foreach ($categories as $category)  {
	echo "<h3><a href=\"#\">". $category ."</a></h3>";
	echo "<div>";
	echo "Stuff about ". $category ." goes in here ...";
	echo "</div>";
	}

echo "</div>";  // end-div 'accordion'
