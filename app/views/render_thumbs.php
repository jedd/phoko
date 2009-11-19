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


	// Now the slider bar for quickly shifting around the thumbnail collection
?>
<style type="text/css">
	#demo-frame > div.demo { padding: 10px !important; };
</style>

<script type="text/javascript">
	$( function() {
		$("#slider").slider({
			// o1 is left-most slider position
			min: 1,

			// o$max_offset is the largest slider position we'll offer
			max: <?php echo $max_offset; ?>,

			// current slider position determined by current /o value
			value: <?php echo $current_offset; ?>,

			// This is reasonably straightforward - 'stop' means when the user
			// lets go of the slider.  We grab the new value of the slider at
			// that time, and generate the new URL we want, and then simply
			// launch to that new location.  Easy, huh?
			stop: function (ev, ui) {
				var newvalue = $("#slider").slider('value');
				var newurl = "<?php echo site_url() . $url_sans_offset ."/o"; ?>" + newvalue;
				self.location = newurl;
				},

			// We want the slider's ALT tag to be modified with the DATE
			// of the image that it corresponds to - this might get messy.
			// We have $every_date_stamp - a comma separated list from PHP,
			// that we assign to an array, and then just index[] into that.
			slide: function (ev, ui) {
				var datestamp_array = Array ( <?php echo $every_date_stamp; ?> );
				var current_value = $("#slider").slider('value');
				$("#slider").attr({  title: datestamp_array[current_value] } );
				}
			});
		} );
</script>

<div id="slider" title="Drag and release to move around the thumbnails.">
</div>
