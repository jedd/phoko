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
						/// @todo get height into the CSS so we can create a netbook theme
						'height' => '50px',
						'title' => $thumb['description'],
						'border' => '0'
						);

	if ($current_image_id == $thumb_id)  {
		$image_properties['border'] = "2";
		}
	echo img($image_properties);
	if ($current_image_id != $thumb_id)
		echo "</a>\n";

	echo nbs(1);
	}

	// Now the slider bar for quickly shifting around the thumbnail collection
?>
<script type="text/javascript">
	// We have $every_date_stamp - a comma separated list from PHP,
	// that we assign to an array, and then just index[] into that.
	var datestamp_array = Array ( <?php echo $every_date_stamp; ?> );

	$( function() {
		$("#slider").tooltip ({
			delay: 100,
			track: true,
			showURL: false,
			});

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

			// We can use either the TITLE tag, or the tooltip.js plugin for jquery,
			// to show the date of the slider's current position.  The TITLE tag
			// seems lethargic during updates, unfortunately, hence we're using
			// the tooltip approach instead (the author of this plugin is porting
			// to jquery-ui apparently).
			slide: function (ev, ui) {
				// The tooltip approach - h3 is the default css type from the
				// tooltip author, so we'll go along with that here.
				 $("#tooltip").html( "<h3>" + datestamp_array[ui.value] + "</h3>");
				}
			});
		});
</script>

<div id="slider" title="The slider of time">
</div>
