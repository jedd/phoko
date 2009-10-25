<?php


foreach ($cache_file_list as $foo=>$bar)  {
	echo $foo ." ==> <br />";
	foreach ($bar as $file => $size)
		echo nbs(3) . $file ." ==> ". $size ."<br />";
	}