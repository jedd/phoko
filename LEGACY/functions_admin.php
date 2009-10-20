<?php
	///
	/// functions_admin.php
	///
	/// Each one is reasonably documented, so no header description sorry.

	// -------------------------------------------------------------------------
	function  admin_minifunc_identify_files  ($publish)   {
		// Return an array with the files we've got (no need to match a type,
		// as the cost of doing this once for all three types of files is low).
		// We also store the file size for use in the reporting section.

		// LATER - work out how to use thumb/large/huge without hard coding it.
		if ($handle = opendir('cache/'))  {
			while (false !== ($file = readdir($handle)))   {
				$size = filesize ("cache/" . $file);
				if (strstr ($file , ".thumb"))
					$files_present ['thumb'] [substr ($file , 0, 32)] = $size;
				if (strstr ($file , ".large"))
					$files_present ['large'] [substr ($file , 0, 32)] = $size;
				if (strstr ($file , ".huge"))
					$files_present ['huge']  [substr ($file , 0, 32)] = $size;
				}
			closedir($handle);
			return $files_present;
			}
		else
			return NULL;
		}


	// -------------------------------------------------------------------------
	function  admin_minifunc_identify_surplus_files  ($publish , $files_present)   {
		// Return an array with the files we've got (no need to match a type,
		// as the cost of doing this once for all three types of files is low.

		// LATER - work out a better way of setting and using these 3 file names
		$cachefiletypes = array ("thumb", "large", "huge");

		foreach ($cachefiletypes as $cachefiletype)  {
			foreach ($files_present[$cachefiletype] as $cache_file_we_have => $have_it)  {
				$assume = false;
				foreach ($publish as $picarray)
					if ($cache_file_we_have == $picarray['md5sum'])
						$assume = true;
				if (! $assume)
					$surplus_files[$cachefiletype][$cache_file_we_have] = true;
				}
			}
		return $surplus_files;
		}



	// -------------------------------------------------------------------------
	function  admin_delete_surplus_cache_files  ($publish)   {
		$files_present = admin_minifunc_identify_files  ($publish) ;
		$surplus_files = admin_minifunc_identify_surplus_files ($publish, $files_present);

		$table .= "\n<tr>";
		$table .= "\n<td>";

		// LATER - work out a better way of setting and using these 3 file names
		$cachefiletypes = array ("thumb", "large", "huge");

		foreach ($cachefiletypes as $file_type)  {
			if ( ( $size = sizeof ($surplus_files[$file_type] )) > 0 )   {
				$table .= "\nDeleting " . $size  ." ". $file_type ." files ...";
				//echo "<br>Surplus of ". $file_type ." = " . sizeof
				foreach ($surplus_files[$file_type] as $file=>$uselessflag)
					unlink ("./cache/" . $file . "." . $file_type);
				$table .= " Done.\n<br />\n";
				}
			}
		$table .= "\n<br/>\n<p>\n<i>Now go back to Show Cache Stats ... </i><br />";
		$table .= "\n</td>";
		$table .= "\n</tr>";
		return $table;
		}


	// -------------------------------------------------------------------------
	function  admin_minifunc_identify_missing_files  ($publish)   {
		// Return an array with the files we're missing.

		// LATER - work out a better way of setting and using these 3 file names
		$cachefiletypes = array ("thumb", "large", "huge");

		foreach  ( $publish as $picarray )
			foreach ($cachefiletypes as $cachefile)
				if (!  (file_exists ("cache/". $picarray['md5sum'] .".". $cachefile)) )
					$missing[$cachefile][$picarray['md5sum']] = true;

		return $missing;
		}


	// -------------------------------------------------------------------------
	function  admin_create_thumbnails   ($publish, $imagesizes)   {
		// Build all the missing thumbnails

		// LATER - check if this value is *huge* .. and offer some alternative.
		// LATER - and/or check if there's some way to identify the time it'll take,
		// the current PHP timeout value, and then extrapolate from the time it takes
		// to make the first one ... ?

		$table .= "\n<tr>";
		$table .= "\n<td>";

		// Grab the complete list of missing files
		$missing = admin_minifunc_identify_missing_files($publish);

		// To assist with stopping injection attacks on &m=ct ..
		if (sizeof ($missing['thumb']) == 0)  {
			$table .= "No thumbs to generate.\n</td>\n</tr>";
			return $table;
			}

		// Actually generate the cache files .. though the periods won't show up as we go.  Oh well.
		foreach ($missing['thumb'] as $missing_thumb => $have_it)
			generate_cache_image ($imagesizes , $missing_thumb , $publish , "thumb");
		$table .= "<br />Thumbnails generated (or script ran out of time - check the Cache Stats!";

		$table .= "\n</td>";
		$table .= "\n</tr>";
		return $table;
		}


	// -------------------------------------------------------------------------
	function  admin_create_bigs   ($publish, $imagesizes)   {
		// Build all the missing Big Files (huge and large)

		// LATER - check if this value is *huge* .. and offer some alternative.
		// LATER - and/or check if there's some way to identify the time it'll take,
		// the current PHP timeout value, and then extrapolate from the time it takes
		// to make the first one ... ?

		$table .= "\n<tr>";
		$table .= "\n<td>";

		// Grab the complete list of missing files
		$missing = admin_minifunc_identify_missing_files($publish);

		$large_and_huge = array ("large", "huge");
		foreach ($large_and_huge as $file_type)  {
			// To assist with stopping injection attacks on &m=ct ..
			if ( sizeof ($missing[$file_type]) == 0)
				continue;
			foreach ($missing[$file_type] as $missing_file => $foo)
				generate_cache_image ($imagesizes , $missing_file , $publish , $file_type);
			$table .= "<br />". $file_type ." generated (or script expired - check the Cache Stats!";
			}

		$table .= "\n</td>";
		$table .= "\n</tr>";
		return $table;
		}


	// -------------------------------------------------------------------------
	function  admin_minifunc_disk_usage ($files_present)  {
		// Report on the disk space used by the three file types
		// Happily we already recorded the sizes in $files_present[]
		$sizes['total'] = 0;
		foreach ($files_present as $cache_file_type=>$list_of_files)  {
			$sizes[$cache_file_type] = 0;
			foreach ($list_of_files  as  $file_md5=> $file_size)
				$sizes[$cache_file_type]  +=  $file_size ;
			$sizes['total']  += $sizes[$cache_file_type];
			}

		// Revert to MeatyBytes (can't do it earlier due to rounding problems)
		foreach ($sizes as $type => $value)
			$sizes[$type] = (integer) ($value / 1024 / 1024);

		return $sizes;

		}




	// -------------------------------------------------------------------------
	function  admin_cache_stats  ($publish , $url_a)   {
		// This function returns the guts of the table that's shown on the
		// 'Show Cache Stats' page - the main page for 'Admin Stuff' page.


		// LATER - work out a better way of setting and using these 3 file names
		$cachefiletypes = array ("thumb", "large", "huge");

		// The easiest stat to start with
		$number_of_images = sizeof($publish);

		// Let's look at what cache files we're obviously missing
		$missing = admin_minifunc_identify_missing_files($publish);

		// Let's look at what we've got already in our cache
		$files_present = admin_minifunc_identify_files  ($publish) ;

		// Let's look at which of the files in the cache are surplus to requirements
		$surplus_files = admin_minifunc_identify_surplus_files ($publish, $files_present);

		// Let's look at the space being used by the cache
		$cache_space_usage = admin_minifunc_disk_usage ($files_present);

		// ..................................................
		// Now, start displaying the data we've collected ...
		$table .= "\n<tr>";
		$table .= "\n<td>";
		$table .= "\n<br>Image collection spans : " . $publish[1]['startDate'] ;
		$table .= "\n through to " . $publish[$number_of_images]['endDate'];
		$table .= "\n<p>";
		$table .= "\nNumber of images registered in collection : <b>". $number_of_images ."</b>";
		$table .= "\n<p>";

		// Details on the disk usage aspect
		$table .= "\n<p>";
		$table .= "\nCache directory space usage:";
		natsort ($cache_space_usage);
		foreach ($cache_space_usage as $type => $value)
			$table .= "<br />&nbsp;&nbsp;&nbsp;<b>". $value ."</b> MB (". $type .")";
		$table .= "\n<p>";

		// Display stats for missing files ...
		$table .= "\n<br />";
		$table .= "\nWe are missing :";
		foreach ($cachefiletypes as $filetype)
			$table .= "\n<br />&nbsp;&nbsp;&nbsp;". sizeof ($missing[$filetype])
						." </b>". $filetype ."</b> pictures.";

		// Offer to create required thumbnails
		if (sizeof ($missing['thumb']) > 0)  {
			$new_url = url_modify ($url_a , "m" , "ct");
			$new_url_string = url_recombine ($new_url);
			$table .= "<br /><br /><a href=\"". $new_url_string ."\">CREATE required thumbnails</a>\n";
			}

		// Offer to create required large/huge's (these should always be in sync)
		if ( (sizeof ($missing['large']) > 0) || (sizeof ($missing['huge']) > 0) )  {
			$new_url = url_modify ($url_a , "m" , "cb");
			$new_url_string = url_recombine ($new_url);
			$table .= "<br /><br /><a href=\"". $new_url_string ."\">CREATE large/huge pictures</a>"
					. " &nbsp;&nbsp;&nbsp;<i>(Please read note below)</i>";
			}


		// Display stats for surplus files ...
		$table .= "\n<br />\n<p>\n<br />";
		$offer_delete_option = false;
		foreach ($cachefiletypes as $filetype)  {
			$table .= "\nWe have a surplus of <b>" . sizeof ($surplus_files[$filetype])
						." ". $filetype ."</b> cache files.\n<br />";
			if (sizeof ($surplus_files[$filetype]) > 0)
				$offer_delete_option = true;
			}

		// And offer to delete all surplus files if we have any ...
		$new_url = url_modify ($url_a , "m" , "d");
		$new_url_string = url_recombine ($new_url);
		if ($offer_delete_option)  {
			$table .= "<a href=\"". $new_url_string ."\">DELETE surplus files</a>\n";
			}

		$table .=  "\n<br /> <br /> <br />"
				.	"\n<hr>"
				.	"\n<i><b>Note about creating images and thumbnails</b>"
				.	"\n<p>The creation of these files will happen automatically during normal "
				.	"site usage - the first time a user views a thumbnail or a normal sized "
				.	"picture, the cache file is created from the primary archive on the fly. "
				.	"However, this "
				.	"option to create new images is useful in certain situations - such as not "
				.	"having visibility of the primary archive from the web server, having a very "
				.	"slow machine hosting your web site, etc."
				.	"</p>"
				.	"\n<p>Having said that, even a fast machine will take some time to convert "
				.	"very large source files into the cached images we keep here.  If you strike "
				.	"problems, you should look at changing the php.ini file to allow longer runtimes "
				.	"of php scripts (the default is 30 seconds), and you may have to enter the "
				.	"cache directory and clean up any half-baked cache files (you can identify these "
				.	"if they don't load properly through the web browser)."
				.	"</p>";

		$table .= "\n</td>";
		$table .= "\n</tr>";
		return $table;
		}