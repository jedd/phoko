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


<script type="text/javascript">
// Tabs jQuery-UI effect
$(function() {
	$("#navi_tabs").tabs({ fx: { opacity: 'toggle' } });
	//getter
	var cookie = $("#navi_tabs").tabs('option', 'cookie');
	//setter
	$("#navi_tabs").tabs('option', 'cookie', { expires: 30 });
	});


// Accordion jQuery-UI effect
$(function() {
	$("#accordion").accordion({
		autoHeight: false,
		header: 'h3'
		});
	});

</script>


<div id="navi_tabs">
	<ul>
		<li><a href="#tabs-1">This image</a></li>
		<li><a href="#tabs-2">Explorifier</a></li>
	</ul>
	<div id="tabs-1">
			<?php
				if (isset ($image_info_view))
					echo $image_info_view;
			?>
	</div>
	<div id="tabs-2">



		<div id="accordion">
			<h3><a href="#">Section 1</a></h3>
			<div>
				<p>
				Mauris mauris ante, blandit et, ultrices a, suscipit eget, quam. Integer
				ut neque. Vivamus nisi metus, molestie vel, gravida in, condimentum sit
				amet, nunc. Nam a nibh. Donec suscipit eros. Nam mi. Proin viverra leo ut
				odio. Curabitur malesuada. Vestibulum a velit eu ante scelerisque vulputate.
				</p>
			</div>
			<h3><a href="#">Section 2</a></h3>
			<div>
				<p>
				Sed non urna. Donec et ante. Phasellus eu ligula. Vestibulum sit amet
				purus. Vivamus hendrerit, dolor at aliquet laoreet, mauris turpis porttitor
				velit, faucibus interdum tellus libero ac justo. Vivamus non quam. In
				suscipit faucibus urna.
				</p>
			</div>
		</div>




	</div>
</div>


</div> <!-- /left -->

<div id="main">
	<div id="main_box" class="newClass ui-corner-all">
		<?php
			if (isset ($content['image_proper']))  {
				echo anchor_popup ('/album/onebigone/i'. $id , $content['image_proper'], array ('border' => '0'));
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