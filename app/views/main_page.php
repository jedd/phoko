<?php
	echo doctype('html4-trans');
?>
<html>
<head>
<title>
<?php echo $title; ?>
</title>

<?php
	// Load the right stylesheet
	echo "\n". link_tag('theme/'. $theme .'/gallery.css');

	// Load the jquery library
	$jquery = array (
				"href" => "js/jquery-1.3.2.js",
				"type" => "text/javascript",
				);
	echo "\n". link_tag($jquery);
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
</div> <!-- /left -->


<div id="main">
	<?php
		echo $content['main'];
	?>
</div> <!-- /main -->



<div id="footer">
	<table width="100%">
	<tr width="00%">
	<td width="35%" align="left">
		Page rendered in {elapsed_time} seconds and using {memory_usage}
	</td>
	<td width="40%" align="center">

	</td>

	<td width="25%" align="left">
		<?php
			foreach ($footer_links as $name=>$link_url)
				echo anchor ($link_url, $name);
		?>
		<br />
		<?php
			echo "Theme: ";
			foreach ($valid_themes as $link=>$theme_name)
				echo anchor ("/album/settings/theme/". $link, $link, array("title" => $theme_name)) . nbs(2);
		?>
	</td>
	</tr>
	</table>
</div> <!-- /footer -->



</div> <!-- /everything -->
</body>
</html>