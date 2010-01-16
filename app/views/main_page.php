<?php
	echo doctype('html4-trans');
?>
<html>
<head>
<title><?php echo $title; ?></title>
<?php
	// Load the common stylesheet (mostly for layout)
	echo "\n". link_tag('theme/gallery.css');

	// Load the theme-specific stylesheets (mostly for colour)
	echo "\n". link_tag('theme/'. $theme .'/gallery.css');
	echo "\n". link_tag('theme/'. $theme .'/jquery-ui.css');

	// Load the jquery library
	echo "\n<script type=\"text/javascript\" src=\"". base_url() ."js/jquery-1.3.2.js\"></script>";

	// Load any jquery plugins
	echo "\n<script type=\"text/javascript\" src=\"". base_url() ."js/jquery.tooltip.js\"></script>";

	// Load the jquery-UI library
	echo "\n<script type=\"text/javascript\" src=\"". base_url() ."js/jquery-ui-1.7.2.custom.min.js\"></script>";

	// Load any jquery-UI plugins
	echo "\n<script type=\"text/javascript\" src=\"". base_url() ."js/jquery.cookie.js\"></script>";

	// Load custom phoko javascript
	echo "\n<script type=\"text/javascript\" src=\"". base_url() ."js/phoko.js\"></script>";
?>

</head>

<body>
<div id="everything">

<div id="top">
	<div id="top_box" class="newClass ui-corner-all">
	<?php
		if ( isset($content['top']))
			echo $content['top'];
	?>
	</div>  <!-- /top_box -->
</div> <!-- /top -->

<?php
	/// @todo shift this into a view partial
	if ( (isset ($filters)) AND ($filters))  {
		echo "<div id=\"filters\">";
		echo "<div id=\"filters_box\" class=\"newClass ui-corner-all\">";
		echo "<font class=\"various_headings\">Filtering on: </font>";
		$fstring = array ();
		foreach ($filters as $filter)
			$fstring[] = anchor ($filter['url_minus_this_filter'] ."/a", $filter['actual'], array ('title'=>'Remove this filter', 'class'=>'deletelink'));
		echo implode (nbs(2) ."::". nbs(2) , $fstring);
		echo "</div>"; // filters_box
		echo "</div>"; // filters
		}
?>

<div id="left" >
	<?php
		if (isset ($content['left']))
			echo $content['left'];
	?>

<?php
	if (isset($prev_next_view))  {
		echo "<div id=\"prev_next_box\" class=\"newClass ui-corner-all\">\n";
		echo $prev_next_view;
		echo "</div>";  //prev_next_buttons
		}
?>

<div id="navi_tabs">
	<ul>
		<li><a href="#image_info">This image</a></li>
		<li><a href="#explorifier">Explorifier</a></li>
	</ul>

	<div id="image_info">
		<?php
			if (isset ($image_info_view))
				echo $image_info_view;
		?>
	</div>

	<div id="explorifier">
		<?php
			if (isset ($explorifier_view))
				echo $explorifier_view;
		?>
	</div>

</div>


</div> <!-- /left -->

<div id="main">
	<div id="main_box" class="newClass ui-corner-all">
		<?php
			if (isset ($content['image_proper']))  {
				// The image is divided into three vertical bars - left, middle, right
				// Using the main gallery.css - left & right will be 20%, middle will be
				// everything else.  Clicking the left or right sides of the image will
				// navigate you to the previous or next image - clicking the middle of
				// the image will open up a new popup full-screen (you can't use percentages
				// for the popup - a known feature of popups).

				// If we're at the first or last, do a dodgy.  We can't just not display this span,
				// otherwise it turns the column into the same as the middle (open new window).
				if (! $prev_image_url)
					$prev_image_url = current_url();
				if (! $next_image_url)
					$next_image_url = current_url();

				echo anchor ($prev_image_url,  "<span id=\"main_pic_left_link\"></span>", array("title" => "Move to previous image"));
				echo anchor ($next_image_url,  "<span id=\"main_pic_right_link\"></span>", array("title" => "Move to next image"));

				$image_attributes = array('title' => 'View full screen version of this image', 'border' => '0', 'height' => '2000', 'width' => '2000');
				echo anchor_popup ("album/onebigone/i". $id , "<span id=\"main_pic_middle_link\"></span></a>", $image_attributes);

				// The image, underneath.  Doing it this way means we don't get the effective
				// mouse-over thing happening anymore, unfortunately.  LATER - we can look
				// at doing those with the anchors above, or via through CSS, for each of the
				// regions.
				echo $content['image_proper'] ;
				}
			// It's unlikely we'll ever have image_proper AND the cache created 'main'
			if (isset ($content['main']))
				echo $content['main'];
		?>

	</div>  <!-- /main_box -->
</div> <!-- /main -->



<div id="footer">
	<div id="footer_box" class="newClass ui-corner-all">
		<table width="100%">
		<tr width="00%">

		<td width="35%" align="left">
			Page rendered in {elapsed_time} seconds and using {memory_usage}
			<br />
			<?php
				echo anchor_popup ("http://dingogully.com.au/trac/phoko", "Phoko project site");
				echo nbs(15);
				// The image is actually 1320 x 590.  Iceweasel is tricky to make this size Just Right.
				echo anchor_popup (base_url() . "theme/how_to_use_this_gallery.png", "How to use this gallery", array("width" => "1340", "height" => "610", "border" => "0"));
			?>
		</td>

		<td width="40%" align="center">

		</td>

		<td width="25%" align="left">
			<?php
				echo "Page: ";
				$footer_links_list = array();
				foreach ($footer_links as $name=>$link_url)
					$footer_links_list[] = anchor ($link_url, $name);
				echo implode ($footer_links_list);
			?>
			<br />
			<?php
				echo "Theme: ";
				$theme_list = array();
				foreach ($valid_themes as $link=>$theme_name)
					if ($link == $theme)
						$theme_list[] = $link;
					else
						$theme_list[] = anchor ("/album/settings/theme/". $link, $link, array("title" => $theme_name));
				echo implode (', ', $theme_list);
			?>
		</td>

		</tr>
		</table>
	</div>  <!-- /footer_box -->
</div> <!-- /footer -->


</div> <!-- /everything -->
</body>
</html>
