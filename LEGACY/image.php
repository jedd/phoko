<?php
	// This page should not be called directly - it's called by index.php
	// to dynamically display the jpg created already within the main line
	// (It's generated there primarily to avoid ever having to transmit
	// the filename of the image).  All we need here is the md5 and the size,
	// and we have confidence that that image is in the cache/ directory.

	// require_once ('config.php');
	//require_once ('functions.php');

	header('Content-Type: image/jpeg');
	$md5      = $_GET['md5'];
	$size     = $_GET['size'];
	$imgfile  = "cache/". $md5 .".". $size;

	// LATER - should confirm md5sum of the given file, to
	// ensure we're not being hijacked to grab arbitrary files!
	// (though anyone willing to guess md5sum's .. is probably
	// more hardcore than we can deal with algorithmically).

	$imgfile = ("cache/". $md5 .".". $size);
	if (file_exists ($imgfile))
		echo readfile  ("cache/". $md5 .".". $size);
	else
		echo "We have a problem but we're inside a jpg .. bugger.";


?>