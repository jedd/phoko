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
<div id="everything">

<div id="top">
Top bit - for all kinds of things
</div> <!-- /top -->


<div id="left" >
Left navigation menu for tag tracking
</div> <!-- /left -->


<div id="right">
Right side bit.
<br />

<br />
Ahh ... more on the right.  As many
words as you could want.  And then
some.
</div> <!-- /right -->


<div id="middle">
Main bit

It should wrap somewhere conveniently on the right, but not clear how far across it should get.
</div> <!-- /middle -->



<div id="footer">
	<table width="100%">
	<tr width="00%">
	<td width="50%" align="left">
		Page rendered in {elapsed_time} seconds and using {memory_usage}
	</td>
	<td width="50%" align="right">
		<?php echo anchor ('/album/cache', "Cache Management"); ?>
	</td>
	</tr>
	</table>
</div> <!-- /footer -->



</div> <!-- /everything -->
</body>
</html>