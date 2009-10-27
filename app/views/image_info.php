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
	echo "When: ". pretty_date($image['startDate']);
else
	echo "Between: ". $image['startDate'] ." and ". $image['endDate'];

echo "<br />";









// Array
// (
//     [width] => 3072
//     [description] => An almighty pomegranate, who knows how old.  I estimate mine would take about 25 years to get to this size.
//     [height] => 2304
//     [startDate] => 2009-05-31T16:42:07
//     [md5sum] => 42f6e426808405cde7f56b97661afe0a
//     [file] => CanonPics/2009/20090531/img_0201.jpg
//     [endDate] => 2009-05-31T16:42:07
//     [label] => img_0201
//     [tags] => Array
//         (
//             [Keywords] => Array
//                 (
//                     [0] => botanic gardens
//                     [1] => flora - pomegranate
//                 )
//
//             [Locations] => Array
//                 (
//                     [0] => madrid
//                 )
//
//             [Persons] => Array
//                 (
//                     [0] => ruth
//                     [1] => georgia
//                 )
//
//         )
//
// )

