<?php
	echo doctype('html4-trans');
?>
<html>
<head>
<title>
Phoko Album
</title>

<?php
	echo "\n". link_tag('theme/default/gallery.css');

	$jquery = array (
				"href" => "js/jquery-1.3.2.js",
				"type" => "text/javascript",
				);
	echo "\n". link_tag($jquery);
?>

</head>

<body>

Here we are




<br />
<hr />
Page rendered in {elapsed_time} seconds

</body>
</html>