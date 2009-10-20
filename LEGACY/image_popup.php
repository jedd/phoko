<?php  require_once ('config.php');  require_once ('functions.php')  ?>
<html>
<body>
</body>
<?php
	$query = parse_url ($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
	$keyvalue_list = explode ( "&", $query);

	$url_a       =  url_split ($_SERVER['REQUEST_URI']);

	$imagefilename = "cache/". $url_a['query']['md5'] .".". $url_a['query']['size'];

	if ($ALLOW_PANORAMA)  {
		// Check if this is a candidate for panorama viewing (ie. ratio > 2)

		$panowidth   =  $panoramasizes [$url_a['query']['p']]['x'];
		$panoheight  =  $panoramasizes [$url_a['query']['p']]['y'];

		$extant    = new Imagick ($imagefilename);
		$extant_y  = $extant->getImageHeight();
		$extant_x  = $extant->getImageWidth();
		$pic_ratio = (int) ( $extant_x / $extant_y );
		// LATER - a 2s (for the very wide pano) delay was introduced
		// in here when I started doing these checks on larger files.
		// It MIGHT just be the handling of the larger files .. or I
		// may be doing some checks I don't need to do here.  Check and
		// see if we can optimise it a tad better.

		// POSSIBLY do a check on the ratio of the large (if exists) rather than the large?

		if ($pic_ratio > 2)  {
			if ($url_a['query']['p'])
				$current_pano = $url_a['query']['p'];
			else  {
				$current_pano = NULL;
				}
			echo "\nPanorama Options : &nbsp; &nbsp; &nbsp;\n";
			$new_url_a       =  url_modify ($url_a, "p", NULL);
			$new_url_string  =  url_recombine ($new_url_a);
			echo ($current_pano)   ?   "<a href=\"". $new_url_string ."\">"  :  "<B>" ;
			echo "No panorama effect";
			echo ($current_pano)   ?   "</a>"  :  "</B>" ;
			foreach ($panoramasizes as $pano_size_code=>$pano_size_settings)  {
				echo "&nbsp;&nbsp;&nbsp; / &nbsp;&nbsp;&nbsp;";
				$new_url_a       =  url_modify ($url_a, "p", $pano_size_code);
				$new_url_string  =  url_recombine ($new_url_a);
				$description = $pano_size_settings['desc']
								. "&nbsp("
								. $pano_size_settings['x']
								." x "
								. $pano_size_settings['y']
								.")";
				echo ($current_pano == $pano_size_code)
					? "<b>"  :  "\n<a href=\"". $new_url_string ."\">";
				echo $description ;
				echo ($current_pano == $pano_size_code)
					? "<B>"  :  "</a>";
				}
			}
		echo "<br />";
		}
	if ($url_a['query']['p'])  {
		echo "\n<applet
				 code=\"ptviewer.class\"
				 archive=\"ptviewer/ptviewer.jar\"
				 width=\"". $panowidth ."\"
				 height=\"". $panoheight ."\">";
		echo "\n<param name=file value=\"". $imagefilename ."\">";
		echo "\n<param name=cursor value=\"MOVE\">";
		echo "\n<param name=pan value=-105>";
		echo "\n<param name=showToolbar value=\"true\">";
		echo "\n<param name=imgLoadFeedback value=\"false\">";
		// Find out what this next thing does ...
		// echo "<param name=hotspot0 value=\"X21.3 Y47.7 u'Sample27L2.htm' n'Hotspot description'\">";
		echo "\n</applet>";
		}
	else  {

		//$width  = $extant->getImageWidth();
		//if (    (($height < $width)  &&  ($width  == $imagesizes[$size]['x']) )
		//	||  (($height > $width)  &&  ($height == $imagesizes[$size]['y']) )  ) {
		$extant = new Imagick ($imagefilename);	//$height = $extant->getImageHeight();
		echo "<img width=\"100%\" ";
		echo " src=\"image.php?md5=". $url_a['query']['md5'] ."&size=". $url_a['query']['size'] ."\">";
		}


?>

</body>
</html>
