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

	// Load the jquery-UI library
	echo "\n<script type=\"text/javascript\" src=\"". base_url() ."js/jquery-ui-1.7.2.custom.min.js\"></script>";

	// Load any jquery-UI plugins
	echo "\n<script type=\"text/javascript\" src=\"". base_url() ."js/jquery.cookie.js\"></script>";
?>

</head>

<body>
<div id="everything">

<div id="top">
	<?php
		if ( isset($content['top']))
			echo $content['top'];
	?>

</div> <!-- /top -->


<div id="left" >
	<?php
		if (isset ($content['left']))
			echo $content['left'];
	?>

<script type="text/javascript">
$(function() {
	$("#navi_tabs").tabs({ fx: { opacity: 'toggle' } });
	//getter
	var cookie = $("#navi_tabs").tabs('option', 'cookie');
	//setter
	$("#navi_tabs").tabs('option', 'cookie', { expires: 30 });
	});
</script>

<div id="navi_tabs">
	<ul>
		<li><a href="#tabs-1">This image</a></li>
		<li><a href="#tabs-2">Explorifier</a></li>
	</ul>
	<div id="tabs-1">
		<p>
			<?php
				if (isset ($image_info_view))
					echo $image_info_view;
			?>
		</p>
	</div>
	<div id="tabs-2">
		<p>
			This will show all tags that exist - perhaps filtered based on what's already shown .. as per the original version?
		</p>
	</div>
</div>


</div> <!-- /left -->

<div id="main">
	<div id="main_box" class="newClass ui-corner-all">
		<?php
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