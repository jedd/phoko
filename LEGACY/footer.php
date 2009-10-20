<?php
	//echo "<hr>";
	$end_time = grab_utime();
	$run_time = $end_time - $start_time;

	echo "\n<table width=\"100%\">";
	echo "\n<tr width=\"100%\" valign=\"top\">";

	echo "\n<td align=\"left\">";
	echo "Page&nbsp;rendered&nbsp;in&nbsp;<b>" . 	substr($run_time, 0, 5) . "</b>&nbsp;seconds.";
	echo "\n</td>";

	echo "\n<td align=\"right\">";


	// Provide some relatively robust (long-lived) URL's to this page / filter set.
	if ( ($url_a['query']['i'] ) && ($settings['show_management_stuff'] == false) )  {
		echo "Shortest&nbsp;identifying&nbsp;URL: &nbsp;\n";

		echo "<font size=\"-1\">";
		echo "<b>http://". $_SERVER['HTTP_HOST'] . $url_a['baseurl'] ;
		echo "?i=". $url_a['query']['i'] ;
		echo "<br>\n";
		echo "</b>";

		if ($url_a['query']['f1'])  {
			echo "Shortest&nbsp;set&nbsp;(filter)&nbsp;URL: &nbsp;\n";
			echo "<b>http://". $_SERVER['HTTP_HOST'] . $url_a['baseurl'] ;
			echo "?";
			$x = 1;
			foreach ($url_a['query'] as $querykey => $queryvalue)  {
				if ($querykey[0] == 'f')  {
					echo "f". $x ."=";
					echo $queryvalue ;
					echo "&amp;";
					$x++;
					}
				}
			}




		echo "</font>";
		}
	echo "\n</td>";

	echo "\n</tr>";
	echo "\n</table>";

	// echo "<a href="README.html">README</a>";
?>


</BODY>
</HTML>