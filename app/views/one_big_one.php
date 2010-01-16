<?php
	echo doctype('html4-trans');
?>
<html>
<head>
<title><?php echo $image_id; ?></title>
<?php
	// Load the common stylesheet (mostly for layout)
	echo "\n". link_tag('theme/gallery.css');

	// Load the theme-specific stylesheets (mostly for colour)
	echo "\n". link_tag('theme/'. $theme .'/gallery.css');
	echo "\n". link_tag('theme/'. $theme .'/jquery-ui.css');

	// Load the jquery library
	// echo "\n<script type=\"text/javascript\" src=\"". base_url() ."js/jquery-1.3.2.js\"></script>";

	// Load any jquery plugins
	// echo "\n<script type=\"text/javascript\" src=\"". base_url() ."js/jquery.tooltip.js\"></script>";

	// Load the jquery-UI library
	// echo "\n<script type=\"text/javascript\" src=\"". base_url() ."js/jquery-ui-1.7.2.custom.min.js\"></script>";

	// Load any jquery-UI plugins
	// echo "\n<script type=\"text/javascript\" src=\"". base_url() ."js/jquery.cookie.js\"></script>";
?>

</head>

<body>
<div id="everything">

<?php
	// Turn the pop-up window into one big href - clicking on it will close the window.
	echo "<a href=\"javascript:window.close()\" title=\"Click to close this window\">";
	echo $image_proper;
	echo "</a>";
?>

</div> <!-- /everything -->
</body>
</html>