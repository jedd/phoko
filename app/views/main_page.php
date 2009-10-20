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

<div id="container">

<div id="top">
Top bit - for all kinds of things
</div> <!-- /top -->


<div id="leftnav" >
Left navigation menu for tag tracking
</div> <!-- /leftnav -->


<div id="content">
Main bit
</div> <!-- /content -->


<div id="rightnav">
Right side bit
</div> <!-- /rightnav -->


<div id="footer">
Page rendered in {elapsed_time} seconds
</div> <!-- /footer -->

</div> <!-- /container -->


</body>
</html>