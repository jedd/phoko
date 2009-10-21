<?php
	/**
		Application name:  kphpalbum
		Author:  jedd
		Email:  jedd@progsoc.uts.edu.au
		Project Page (soon) :  http://dgwiki.dingogully.com.au/Jedd/Software
		Version: varying - 200902 (roughly)
		Licence: GPL v2 (or later)
		Copyright 2009 by Jedd

		Please consult the accompanying README.HTML file for more information.
	**/


	/**
	*** Available functions:
	***
	*** =======================================================================
	*** Performance / debugging stuff
	*** =============================
	*** double   grab_utime  ()
	***    identify the current time, in microseconds
	***    used in header and footer to identify the
	***    time taken to render the page.
	***
	*** =======================================================================
	*** URL fiddling
	*** ============
	*** array_of_query   url_split ( (string) $url )
	***    given a url including a query of ?a=b&c=d&e=f
	***    returns array a=>b , c=>d, e=>f
	***
	*** function  url_modify ( (array) $url , (string) $newkey, (string) $newvalue )
	***    returns normal url array, after adding (or updating)
	***    newkey with newvalue
	***
	*** string  url_recombine ( (array) $url )
	***    return a string from basepath through to query,
	***    taking all items in the url array
	***
	*** =======================================================================
	*** Miscellaneous
	*** =============
	*** array_of_publish_images   load_index_xml_file  ( $xmlfile , two other things, shooshtags )
	***    given a fully pathed XML file, load it, parse it,
	***    and return an array containing info about each image
	***    that has the appropriate tag (default 'PUBLISH')
	***
	*** array_of_categories   count_categories (  (array) array_of_publish_images
	***    using the load_index_xml_file() output above, feed this
	***    (or a filtered publishpics[] array) into this function and
	***    in return you get a 2d array of People, Place, keywords (except
	***    the PUBLISH reserved keyword from config.php) - mostly for cosmetic
	***    use next to links.
	***
	*** array_of_tags   find_all_image_tags  ( array of published images , tagtype (string)
	***    given an array ofpublished imaages, and a tag type ('place', 'people' ...)
	***    returns a list of all the possible tag types ('Georgia', 'Jedd', 'Akshat')
	***    in alphabetical order.  Might also return the count for each, so 2D array.
	***
	*** string     prev_image  ( array of published images ,  current image (string))
	*** string     next_image  ( array of published images ,  current image (string))
	***    given the current image - presumably md5um, but one day possibly integer,
	***    return the md5 (or one day possibly integer) of the previous or next.
	***    if we are at the end, return NULL
	***
	*** integer        image_number  ( array of published images  , (string) md5sum )
	***    returns the position in the array of published images, that matches
	***    the given md5sum
	***
	*** boolean        generate_cache_image  ($md5sum, $size, $imagesizes)
	***     returns TRUE or FALSE, depending if the appropriate cache file
	***     can, and indeed is, created.
	***
	*** html_string    grab_exif_info   ( $publish array , $md5 of file , int verbosity )
	***     returns a printable html-compliant string, showing various
	***     exif information, depending on verbosity (1, 2, or 3)
	***
	*** array          identify_tag_categories  ( publish array )
	***     returns a 1d array of tag categories - will either be handed
	***     publish[] or fullpublish[] probably.  previously used membergroups
	***     to identify tag categories - not especially portable.  this method
	***     identifies ANY array[] within each picimage[] as PROOF OF A CATEGORY
	***     which isn't ideal.  LATER we should look at having an addition dimension
	***     in publish, for each pic, with [tags], say, and have keywords under that.
	***
	*** =======================================================================
	*** Membergroups
	*** ============
	***
	*** string   lookup_parent_group ( string tagtype , string tag , array membergroups)
	***     returns the string name of the parent group if it exists,
	***     otherwise returns NULL
	***
	*** array of filters    lookup_child_groups  ( string tagtype, string parentgroup , array membergroups)
	***     the opposite of lookup_parent_group() - this function takes a parentgroup
	***     name and gives you all the child names - useful in the filter functions,
	***     where we're hiding the fact that a filter like "[India]" is actually a
	***     virtual filter of a dozen or so sub-categories.
	***
	***
	*** =======================================================================
	*** Filters
	*** =======
	***
	*** array_of_filters   filter_extract_from_url   ( url array , membergroups array);
	***    take the normal url_in array (early on) and extract the
	***    f1=, f2=, etc fields.  LATER we can have a global config
	***    variable to specify max # of filters (I'm thinking 4 or 5)
	***
	*** string   generate_filter_table  ( filter array , url array , BOOL showthumbs)
	***     returns a string containing all the HTML you need to display
	***     a table showing current filters.  We can assume at least
	***     one filter is present otherwise it never gets called.  Input
	***     array data is the output of filter_extract_from_url(), and
	***     a copy of the url array so we can generate our own URLs
	***     within the table.
	***     If the showthumbs bool is TRUE, then do the normal thing,
	***     if it's false then insert a small <TD> at the start offering
	***     to show thumbnails.
	***
	*** boolean  filter_check_exist   ( filter array , (string) filter to check)
	***     returns TRUE if filter exists already, false if it doesn't
	***
	*** integer    filter_next_available  ( filter array )
	***     returns the # of the next available filter, or 0 if we
	***     are already at 5 in use.
	***
	*** array   filter_publish_array  (  full_publish array ,  filters array , $membergroups )
	***    returns a filtered array based on .. well, the filters provided.
	***    need membergroups in case we have a super-group filter passed to us.
	***
	*** =======================================================================
	*** ADMIN
	*** =======
	*** string  admin_cache_stats  (  full_publish_array )
	***     returns a table (ready to echo) with stats about the cache.
	***
	**/



	// =========================================================================
	//    URL GROUP STUFF
	// -------------------


	// -------------------------------------------------------------------------
	function url_split ($url)  {
		$query = parse_url ($url, PHP_URL_QUERY);
		$keyvalue_list = explode ( "&", $query);

		if ($query)  {
			foreach($keyvalue_list as $x=>$value) {
				$keyvalue = explode("=", $value);
				$urlarray['query'][$keyvalue[0]] = $keyvalue[1];
				}
			}
		// One addition to the basics
		if (strstr ($url , "?"))
			$urlarray['baseurl'] = substr ( $url, 0, strpos( $url, "?"));
		else
			$urlarray['baseurl'] = $url;
		return ($urlarray);
		}


	// -------------------------------------------------------------------------
	function url_recombine ($url)  {
		$out_url = $url['baseurl'];
		if ( $url['query'] != NULL )  {
			$multiple = FALSE;
			foreach ($url['query'] as $key=>$value)  {
				if ($multiple)
					$out_url = $out_url . "&amp;" . $key . "=" . $value;
				else
					$out_url = $out_url . "?" . $key . "=" . $value;
				$multiple = TRUE;
				}
			}
		return $out_url;
		}


	// -------------------------------------------------------------------------
	function  url_modify ($url , $key, $value)  {
		// Happily this overwrites, or creates new, array keys as needed
		if ($value)
			$url['query'][$key] = $value;
		else  {
			// NULL implies a key deletion.  If it's a filter, it's
			// more complex (must maintain consecutive/contiguous keys)
			// Else - just delete the key as declared.
			// And though this is ugly, it makes the algorithm for filter
			// removals so much easier - sort the array first.
			if ($key[0] == 'f')  {
				$filter_to_delete = $key[1] ;
				foreach ($url['query'] as $k=>$v)  {
					if ( (strlen ($k) == 2) && ($k[0] == 'f'))  {
						$this_filter = $k[1];
						if ($this_filter <  $filter_to_delete)
							$return_url['query'][$k] = $v;
						else
							if ($url['query']['f' . ($this_filter + 1) ])
								$return_url['query'][$k] = $url['query']['f' . ($this_filter + 1) ] ;
						}
					else
						$return_url['query'][$k] = $v;
					}
				$url = $return_url;
				}
			else  // .. simply unset the component.
				unset ($url['query'][$key]);
			}
		// Okay, this ksort is a tad ugly, but makes some things easier,
		// and presumably it's not very expensive if it's already sorted.
		ksort ($url['query']);
		return $url;
		}



	// -------------------------------------------------------------------------
	function  count_categories ($publisharray)  {
		// Right now deal with people and places only
		// LATER - deal with custom categories, and super-categories
		// LATER - this will mean coming up with an algorithm to identify
		// all tag groups - not just those that are member-group members
		// (which is a shortfall of the current way - it only works because
		// my picture set contains membergroups in all four categories)
		$catcount = array();
		if ($publisharray)  {
			foreach  ( $publisharray as $picarray )  {
				if ($picarray['Persons'])
					foreach ($picarray['Persons'] as $person)
						if ($catcount['Persons'][$person])
							$catcount['Persons'][$person]++;
						else
							$catcount['Persons'][$person] = 1;

				if ($picarray['Locations'])
					foreach ($picarray['Locations'] as $place)
						if ($catcount['Locations'][$place])
							$catcount['Locations'][$place]++;
						else
							$catcount['Locations'][$place] = 1;

				if ($picarray['Keywords'])
					foreach ($picarray['Keywords'] as $keyword)
						if ($catcount['Keywords'][$keyword])
							$catcount['Keywords'][$keyword]++;
						else
							$catcount['Keywords'][$keyword] = 1;

				if ($picarray['The Farm'])
					foreach ($picarray['The Farm'] as $keyword)
						if ($catcount['The Farm'][$keyword])
							$catcount['The Farm'][$keyword]++;
						else
							$catcount['The Farm'][$keyword] = 1;

				}
			return $catcount;
			}
		else
			return NULL;
		}



	// -------------------------------------------------------------------------
	function  format_pretty_date ($date)  {
		// Returns a relatively pretty and human readable string

		$hours24 = substr ($date, 11, 2);
		$minutes = substr ($date, 14, 2);
		$year    = substr ($date, 0,  4);
		$month   = substr ($date, 5,  2);
		$dom     = substr ($date, 8,  2);

		$unixtime  = mktime (12, 0, 0, $month, $dom, $year);
		$timearray = getdate ($unixtime);

		$dow = substr($timearray['weekday'], 0, 3);
		$date = substr ($date , 0, 10);
		$hours = ($hours24 > 12)   ?  ($hours24 - 12)  : $hours24;
		$ampm  = ($hours24 > 12)   ?  "pm"             : "am";

		$datestring = $date .", ". $dow .", ". $hours .":". $minutes . $ampm;

		return $datestring;
		}

	// -------------------------------------------------------------------------
	function   load_index_xml_file  ( $xmlfile , $PUBLISHKEYWORD , $picturerepository , $SHOOSHTAGS )  {
		// LATER - look at flock on the array_file, to prevent
		// contention if two users hit it at the same time after
		// index.xml was updated.  Unlikely ... so a low priority.

		// If the cached publish array file is preferable, use it.
// 		if (file_exists ($xmlfile)) {
// 			$xmlstat = stat ($xmlfile);
// 			$xmltime = $xmlstat['mtime'];
// 			}
// 		else
// 			$xmltime = 0;

// 		if (file_exists ("cache/index.kphp"))  {
// 			$kphpstat = stat ("cache/index.kphp");
// 			$kphptime = $kphpstat['mtime'];
// 			if ( $xmltime <  $kphptime ) {
// 				$rawdata = file_get_contents ("cache/index.kphp");
// 				$fullxmlextract = unserialize ($rawdata);
// 				return $fullxmlextract;
// 				}
// 			}

		// else we fall through, and load the index.xml file directly (then save the cached version)
/*		$imagearray = array();
		$indexxml = simplexml_load_file($xmlfile);*/
		// LATER -- look into using alternative xml loading functions:
		// http://localhost/doc/php-doc/html/function.xml-parse.html
		// http://localhost/doc/php-doc/html/function.sdo-das-xml-loadfile.html
		// http://localhost/doc/php-doc/html/function.domxml-open-file.html
		// Probably the XML_Parse is the way to go(?) - but do some perf comparisons.
		$x = 1;
		foreach ($indexxml->images->image  as  $image)  {
			if  ($image->options)  {
				foreach ($image->options->option  as $option)  {
					if ($option['name'] == "Keywords")  {
						foreach ($option->value  as  $value)  {
							if  ($value['value'] == $PUBLISHKEYWORD)  {
								// Basically in here we're dropping 'back down' a foreach level to
								// grab the info in there - so we're not just looking for 'Keywords',
								// we just use that to find the PUBLISHKEYWORD trigger .. okay?

// 								// Basic pic info to grab (cast as string or else Bad Things happen)
// 								$infowewant = array ("width", "height", "startDate", "endDate",
// 													"angle", "md5sum", "description");
// 								foreach ($infowewant as $info)
// 									(string)$imagearray[$x][$info]   = (string)$image[$info];

// 								// Special case for 'file' as we need to prepend the path.
// 								(string)$imagearray[$x]['file'] = $picturerepository.(string)$image['file'];

								// People - now Persons in recent kphotoalbum
// 								foreach ($image->options->option  as  $suboption)
// 									if ($suboption['name'] == "Persons")
// 										foreach ($suboption->value as $peoplevalue)
// 												if ( ! (in_array ($keywordvalue['value'] ,
// 														$SHOOSHTAGS['Persons']) ) )
// 												$imagearray[$x]['Persons'][]
// 													= (string)$peoplevalue['value'];

								// Places - now Locations in recent kphotoalbum
// 								foreach ($image->options->option  as  $suboption)
// 									if ($suboption['name'] == "Locations")
// 										foreach ($suboption->value as $placesvalue)
// 												if ( ! (in_array ($keywordvalue['value'] ,
// 														$SHOOSHTAGS['Locations']) ) )
// 													$imagearray[$x]['Locations'][]
// 														= (string)$placesvalue['value'];

								// Keywords
// 								foreach ($image->options->option  as  $suboption)
// 									if ($suboption['name'] == "Keywords")
// 										foreach ($suboption->value as $keywordvalue)
// 											if ($keywordvalue['value'] != $PUBLISHKEYWORD )
// 												if ( ! (in_array ($keywordvalue['value'] ,
// 														$SHOOSHTAGS['Keywords']) ) )
// 													$imagearray[$x]['Keywords'][]
// 														= (string) $keywordvalue['value'];



								// LATER - sort this out - we don't need multiple entries, but
								// instead can just grab them based on cat-types from somewhere.
								// REMEMBER - Be very CAREFUL to watch out for keyword=PUBLISHKEYWORD
								// once we wrap this up into a one-size-fits-all function.

								// Everything that's not people/place/keywords (ie. all custom categories)
// 								foreach ($image->options->option  as  $suboption)
// 									if (		($suboption['name'] != "Locations")
// 											&&	($suboption['name'] != "Keywords")
// 											&& 	($suboption['name'] != "Persons")  )
// 										foreach ($suboption->value as $placesvalue)  {
// 											// note member-groups insist on using _ underscores - not
// 											// especially consistent - LATER on work out other problems
// 											// we may have with spaces or other char substitutions that
// 											// may be going on ..?
// 											$son = str_ireplace ("_" , " " , (string)$suboption['name']);
// 											// JEDD _ this next two lines are likely to break badly
// 											if ( ! (in_array ($placesvalue['value'] ,
// 													$SHOOSHTAGS[$son]) ) )  {
// 												$imagearray[$x][$son][] = (string)$placesvalue['value'];
// 												}
// 											}


								// LATER - work out custom categories
								// LATER - work out super categories
								$x++;
								}
							}
						}
					}
				}
			}


		$membergroups = array();
		$mgstring = "member-groups";  // LATER - work out how else to escape "-" in xml strings
 		foreach  ($indexxml->$mgstring->member  as  $mg) {
 			// LATER - here is where we check if member is useful .. if it's
 			// a tag in use in the $publish array above.

			// confirm $mg['member'] is in $imagearray;
// 			echo "Looking for ". $mg['member'] ." - result = ".
			// LATER - in_array doesn't recursively hunt through the array .. dammit!
			$result = in_array ($mg['member'] ,  $imagearray);
// 			echo  ($result) ? "true" : "false";
// 			echo "<br>";
			$category   =  (string) $mg['category'];
			$groupname  =  (string) $mg['group-name'];
			$member     =  (string) $mg['member'];
			$shooshcat  =  (array)  $SHOOSHTAGS[$category];
			if ( ! (in_array ($groupname , $shooshcat) ) )  {
				$membergroups [$category] [$groupname] [] = $member;
				}
			else
				$membergroups [$category] = NULL;   // This resolves a few if's failing elsewhere.
			// echo $groupname . "<br>";   /// DEBUG stuff
 			// echo "<br />mgstring = ". $mgstring;
 			}
		$fullxmlextract['imagearray']    =  $imagearray;
		$fullxmlextract['membergroups']  =  $membergroups;

		// Save the data out, so subsequent hits will be against the cache file
		$rawdata = serialize ($fullxmlextract);
		$byteswritten = file_put_contents ("cache/index.kphp", $rawdata);
/*		echo "Bytes written to cache index = <b>" . $byteswritten . "</b><br />\n";
		echo "Length of rawdata string = <b>" . strlen($rawdata) . "</b><br />\n";*/
		return $fullxmlextract;
		}



	// -------------------------------------------------------------------------
	function  find_all_image_tags ($publish , $tagtype)  {
		foreach  ( $publish as $picarray )  {
			if ($picarray[$tagtype])  {
				foreach ($picarray[$tagtype] as $tag)  {
					if ($returnarray[$tag]['count'])
						$returnarray[$tag]['count']++;
					else
						$returnarray[$tag]['count'] = 1;
					}
				}
			}
		if ($returnarray)  {
			ksort ($returnarray);
			return $returnarray;
			}
		else
			return NULL;
		}









	// -------------------------------------------------------------------------
	function  generate_explorifier  ( $member_groups, $this_category , $url_a,  $tags_in_use,
										 $all_tags, $filters_a, $publish, $this_image )  {
		// Returns the full text ready to dump to screen for the ACTIVE tag in the explorifier

		// Handle Grouped items first.  They're already in alphabetical order.
		// We need to handle items that are in multiple parent groups, so we
		// search for parentage, and flag it (so we can ignore flagged items
		// later when we do the non-parented items).

		if ($member_groups)  {
			foreach ($member_groups as $mg=>$members)  {
				$any_kids = 0;   // We only show [Group] if there are any children
				$urlize_mg = true;
				if (  (filter_check_exist ($filters_a , $this_category ."=". $mg))
						|| (filter_next_available ($filters_a) == NULL)  )
					$urlize_mg = false;
				else  {
					$next_filter_name =  "f".  filter_next_available($filters_a);
					$url_mg_a = url_modify ($url_a , $next_filter_name ,
											urlencode ($this_category ."=[". $mg ."]"));
					$url_mg = url_recombine ($url_mg_a);
					}
				foreach ($members as $member)  {
					$urlize_member = true;
					if (is_array ($tags_in_use))  {  // It may well be empty, after all
						if (  (filter_check_exist ($filters_a , $this_category ."=". $member))
								|| (filter_next_available ($filters_a) == NULL)
								|| (! array_key_exists ($member , $tags_in_use) )  )
							$urlize_member = false;
						else  {
							$next_filter_name =  "f".  filter_next_available($filters_a);
							$url_member_a = url_modify ($url_a , $next_filter_name ,
													urlencode ($this_category ."=". $member ));
							$url_member = url_recombine ($url_member_a);
							}
						if ( (array_key_exists ($member , $tags_in_use)) ) {
							$any_kids++;
							if ($any_kids == 1)  {   // Do the [groupheading] quickly first, if we're here.
								$ret .= nbsp(5);
								if ($urlize_mg)
									$ret .= "<a href=\"". $url_mg ."\">[ ". $mg ." ]</a><br />";
								else
									$ret .= "[ ". $mg ." ]<br />";
								}
							$ret .= nbsp (10) ."o". nbsp(2);
							if ($urlize_member)
								$ret .= "<a href=\"". $url_member . "\">". $member ."</a>";
							else
								$ret .= $member ;
							$ret .= "<font color=\"grey\">";
							$ret .= nbsp(3) ."( ". $tags_in_use[$member]['count'] ;
							$ret .= " / ".$all_tags[$member]['count']." )";
							$ret .= "</font>";
							$ret .=  "<br />";
							$tags_in_use[$member]['shown'] = true;
							}
						}





					}
				}
			}

		$any_kids = 0;
		if (is_array ($tags_in_use))  {
			foreach ($tags_in_use as $tag=>$count)  {
				if (  (filter_check_exist ($filters_a , $this_category ."=". $tag))
						|| (filter_next_available ($filters_a) == NULL)  )
					$urlize_tag = false;
				else  {
					$urlize_tag = true;
					$next_filter_name  =  "f".  filter_next_available($filters_a);
					$url_tag_a         = url_modify ($url_a , $next_filter_name ,
											urlencode ($this_category ."=". $tag ));
					$url_tag           = url_recombine ($url_tag_a);
					}
				if (! $tags_in_use[$tag]['shown'])  {
					$any_kids++;
					if ($any_kids == 1)
						$ret .= nbsp(5) ."[ Ungrouped ]<br />";
						$ret .= nbsp (10) ."o". nbsp(2);
					if ($urlize_tag)
						$ret .= "<a href=\"". $url_tag . "\">". $tag ."</a>";
					else
						$ret .= $tag ;
					$ret     .= "<font color=\"grey\">";
					$ret     .=  nbsp(3) . "( ". $count['count'] ." / ". $all_tags[$tag]['count']." )<br />";
					$ret     .= "</font>";
					}
				}
			}
		return ($ret);
		}







	// -------------------------------------------------------------------------
	function  prev_image  ($publish , $current_image)  {
		// LATER - deal with (int) image offsets, not just md5sums
		if ($publish[0]['md5sum'] == $current_image)
			return NULL;
		$last_time = $publish[0]['md5sum'];
		foreach ($publish as $image)  {
			if ($image['md5sum'] == $current_image)
				$previous = $last_time;
			$last_time = $image['md5sum'];
			}
		return $previous;
		}


	// -------------------------------------------------------------------------
	function  next_image  ($publish , $current_image)  {
		if ($publish[sizeof($publish)]['md5sum'] == $current_image)
			return NULL;
		foreach ($publish as $image)  {
			if ($watchout == true)
				$next = $image['md5sum'];
			if ($image['md5sum'] == $current_image)
				$watchout = true;
			else
				$watchout = false;
			}
		return $next;
		}


	// -------------------------------------------------------------------------
	function image_number ($publish , $md5)  {
		// Returns image # of the pic matching $md5, within the publish array
		$x = 1;
		foreach ($publish as $image)  {
			if ($image['md5sum'] == $md5)
				$image_number = $x;
			$x++;
			}
		return $image_number;
		}



	// -------------------------------------------------------------------------
	function  generate_cache_image  ($imagesizes, $md5sum, $publish, $size)  {
		// LATER - check if the file is > 0 bytes - as can often happen
		// if the initial hit to the relevant page was interrupted .. usually
		// by someone quite impatient at the idea of taking 40 seconds to
		// open a single page (!).  If it's zero, of course, rebuild it.
		if (file_exists ('cache/' . $md5sum . "." . $size))  {
			$extant    = new Imagick ('cache/' . $md5sum . "." . $size);
			$extant_y  = $extant->getImageHeight();
			$extant_x  = $extant->getImageWidth();
			$ideal_x   = $imagesizes[$size]['x'];
			$ideal_y   = $imagesizes[$size]['y'];

			// LATER - a 2s (for the very wide pano) delay was introduced
			// in here when I started doing these checks on larger files.
			// It MIGHT just be the handling of the larger files .. or I
			// may be doing some checks I don't need to do here.  Check and
			// see if we can optimise it a tad better.

			// POSSIBLY do a check on the ratio of the large (if exists) rather than the large?

			if ($size == "huge")   // ie. we really care about the quality of this one
				if ( ( $pic_ratio = (int) ( $extant_x / $extant_y )  )  > 2)
					$ideal_x   = $ideal_x  *  $pic_ratio;

			if (    (($extant_y < $extant_x)  &&  ($extant_x  == $ideal_x) )
				||  (($extant_y > $extant_x)  &&  ($extant_y  == $ideal_y) )  )
				return true;
			}

		// If we get here, the cache file either doesn't exist, or is the wrong size.

		// LATER - there has to be a bettery way than looping through to find this .. ?
		foreach ($publish as $pic)
			if ($pic['md5sum'] == $md5sum)
				$filename = $pic['file'];
		$image  = new Imagick($filename);
		$image_format =  $image->getImageFormat();
		$height = $image->getImageHeight();
		$width  = $image->getImageWidth();


		// We need a less cumbersome way of referring to the new size
		// AND we need to do some funky calculations for panorama (very
		// wide, very short) image files -- yes, this breaks the implied
		// consistency from config.php .. but low quality panos are irritating
		// me.  Maybe a setting later to configure whether we do this Nice thing ..?
		$new_x = $imagesizes[$size]['x'];
		$new_y = $imagesizes[$size]['y'];
		if ($size == "huge")   // ie. we really care about the quality of this one
			if ( ( $pic_ratio = (int) ($width / $height )  )  > 2)
				$new_x = $new_x * $pic_ratio;


		// If Image format is in JPEG already, then it's easy to resize it,
		// otherwise we have to do some funky system() calls - the cache
		// must only ever contain JPEG's.
		if ($image_format != "JPEG")  {
			$cachefile = 'cache/' . $md5sum .".". $size;
			$system_command         = "convert -sample ";
			$system_command        .=
				($height < $width) ? $new_x :"x". $new_y ;
		/*	if ($height < $width)
				$system_command    .= $new_x;
			else
				$system_command    .= "x".  $new_y;*/
			$system_command        .= " ". $filename ." jpg:". $cachefile;
			system ($system_command ) ;
			}
		else  {
			if ($height < $width)
				$image->scaleImage($new_x , 0);
			else
				$image->scaleImage(0, $new_y);
			$cachefile = 'cache/' . $md5sum . "." . $size;
			file_put_contents ( $cachefile , $image);
			}
		// LATER - investigate why / how / how much EXIFdata is copied
		// by the above process, as it certainly seems to come across
		// to all the cache files ...
		// - - -
		// LATER - in either case, copy the EXIF data, using "exifcopy -bo"
		// (maybe we ship that with the distro ..? limits us to i386 linux)
		// to the thumbnail, and elsewhere LATER check the thumbnail for EXIF
		// data as the preference than the kphotoalbum original.
		return true;

	}



	// -------------------------------------------------------------------------
	function   grab_exif_info   ($publish  , $md5sum , $verbosity)  {
		// Try to grab the EXIF data from the thumbnail (smallest, therefore
		// fastest to read).  Used to read it from the original repository,
		// but can't guarantee visibility of same .. plus much slower even
		// if we have visibility.  At some point something we did earlier (in
		// the generate-cache function) replicated EXIF data .. so we'll
		// say thanks to the PHP deity of our choice and carry on ...

		// LATER - fix up error handling on exif read, as exifcopy doesn't leave
		// us with a usable/readable exif content int he thumbnail for some reason.
		// One of the few places I'm using the @ thing to suppress errors .. as
		// they can expose, here, the file structure of the picture archive.
		$filename = "cache/". $md5sum .".thumb";
		if (! ($exif_a = exif_read_data ( $filename , "COMPUTED" )))
			return "None available, sorry.";
		$grey = "<font color=\"grey\">";
		$ungrey = "</font>";

		$info_1  = $grey ."Exp:".$ungrey. $ungrey . $exif_a['ExposureTime'] .", ";
		$info_1 .= $grey ."Ap:".$ungrey. $exif_a['COMPUTED']['ApertureFNumber'] .", ";
		$info_1 .= $grey ."ISO:".$ungrey. $exif_a['ISOSpeedRatings'] ;

		$info_2  = $grey ."Flash:".$ungrey ;
		$info_2 .= ($exif_a['Flash'] == 89) ? "Probably" : "Probably&nbsp;not" ;
		$info_2 .= "(". $exif_a['Flash'] ."), ";
		$info_3 .= $grey ."Focal&nbsp;Length:".$ungrey. $exif_a['FocalLength'] .", ";
		// LATER - work out a better way of getting a short string for Make
		// $info_2 .= $grey ."Make:".$ungrey. substr ($exif_a['Make'], 0, strpos($exif_a['Make'], "," )) ;
		$info_2 .= $grey ."Make:". $ungrey . $exif_a['Make'] . ","  ;

		$info_3  = $grey ."Model:".$ungrey. $exif_a['Model'] ;

		switch ($verbosity)  {
			case 1	:	$info_string = $info_1;
						break;
			case 2	:	$info_string = $info_1 . ", " . $info_2;
						break;
			case 3	:	$info_string = $info_1 . ", " . $info_2 .", ". $info_3;
						break;
			}
		return $info_string;
		}



	// -------------------------------------------------------------------------
	function  identify_tag_categories  ( $publish )  {
		$cat_a = array();
		foreach ($publish as $picarray)
			foreach ($picarray as $item=>$contents)
				if (is_array ($contents))
					if (! in_array ((string)$item , $cat_a))
						$cat_a[] = (string)$item;
		sort ($cat_a);
		return $cat_a;
		}





	// =========================================================================
	//    MEMBER GROUP STUFF

	// -------------------------------------------------------------------------
	function  lookup_parent_group ( $tagtype , $tag , $membergroups )  {
		if ($membergroups[$tagtype])  {
			foreach ($membergroups[$tagtype] as $parent_name=>$parent_group)  {
				$parent = $parent_name;
				foreach ($parent_group as $groupmember)
					if ($groupmember == $tag)
						return $parent;
				}
			}
		return NULL;
		}


	// -------------------------------------------------------------------------
	function  lookup_child_groups ( $tagtype , $parenttag , $membergroups )  {
		// returns array of child tags
		$child_tags = array();
		foreach ($membergroups[$tagtype] as $parent_name=>$parent_group)
			if ($parent_name == $parenttag)
				foreach ($parent_group as $groupmember)
					$child_tags[] = $groupmember;
		return $child_tags;
		}




	// =========================================================================
	//    Thumbnail scrollbar

	function   generate_thumbnail_scrollbar ( $url_a , $publish , $settings )  {
		// Generates the full string (table to /table) for the pseudo-scrollbar.

		// We divide the scrollbar into 20 pieces - one of which will be
		// our highlighted bit, and the other 19 will be consistent 5% width
		// jpg's that are href's to new &o= ... so the trick is to work out
		// the left & right widths as fragments of 20, and then to rattle
		// through creating the href links.  20 was chosen as 5% gaps both
		// look and work okay, and are much easier to work with than 1/100ths.

		$granularity = $settings['scrollbar_granularity'];
		$thumbs_per_page =  $settings['thumbsperpage'];
		$number_of_pictures = sizeof ($publish);
		$current_offset = $url_a['query']['o'];

		// If we make an array of position offsets, it saves trying to handle
		// edge cases (first & last) specially.
		$delta = (int) (($number_of_pictures - $thumbs_per_page + 1)  /  ($granularity - 1 ) );
		$positions = array();
		$positions[1] = 1;
		$close_match = 1;
		for ( $x = 2 ; $x < $granularity ; $x++)  {
			$positions[$x] = $positions[$x - 1] + $delta;
			if ($positions[$x] < $current_offset)
				$close_match = $x;
			}
		$positions[$granularity] = $number_of_pictures - $thumbs_per_page + 1;

		// We calculate a better match for the current blug spot
		$diff_backwards = $current_offset - $positions[$close_match];
		$diff_forwards  = $positions[$close_match + 1] - $current_offset;
		$best_match     = ($diff_backwards < $diff_forwards) ? $close_match : $close_match + 1 ;

		$table  = "\n<table class=\"thumbnail_scroll\">\n<tr>\n<td>";
		//$table  = "\n<table width=\"100%\">\n<tr>\n<td>";
		$jpg_width = 100 / $granularity;

		// Now, run through each member of positions[] and pump out the good stuff.
		for ($x = 1 ; $x < ($granularity + 1) ; $x++)  {
			if ($best_match == $x)
				$table .= "<img border=\"0\" src=\"scrollbar_blue.jpg\" width=\"". $jpg_width ."%\" height=\"8px\">";
			else  {
				$new_url_a = url_modify ($url_a , "o" , $positions[$x]);
				$new_url_s = url_recombine ($new_url_a);
				$table .= "<a href=\"". $new_url_s ."\" >";
				$table .= "<img border=\"0\" src=\"scrollbar_green.jpg\" width=\"". $jpg_width ."%\" height=\"8px\">";
				$table .= "</a>";
				}
			}

		$table .= "\n</td>\n</tr>\n</table>";
		return $table;
		}



	// =========================================================================
	//    FILTERS


	// -------------------------------------------------------------------------
	function  filter_extract_from_url  ( $url , $membergroups)   {
		if ($url['query'])  {
			foreach ($url['query'] as $querykey => $queryvalue)  {
				if ($querykey[0] == 'f')  {
					$actual = explode ("=" ,  urldecode ( $queryvalue ));
					$filters_a[$querykey[1]]['type']   =  $actual[0];
					$filters_a[$querykey[1]]['value']  =  $actual[1];
					}
				}
			if ($filters_a)  {
				ksort ( $filters_a);
				// Now check if any are ParentGroupFilterThings, and create ['children'] sub if so.
				foreach ($filters_a as $x=>$filter)  {
					if ($filter['value'][0] == "[")  {
						$pg_name = substr ($filter['value'], 1, (strlen ($filter['value']) -2));
						$child_groups = lookup_child_groups ($filter['type'], $pg_name , $membergroups);
						foreach ($child_groups as $child)
							$filter_children[] = $child;
						ksort ($filter_children);
						$filters_a[$x]['children'] = $filter_children;
						}
					}
				return $filters_a;
				}
			else
				return NULL;
			}
		}




	// -------------------------------------------------------------------------
	function  generate_filter_table  ( $filters , $url , $showthumbnails )   {
		// We take URL (in) so we can generate inline URL's to specific
		// actions - such as the removal of a given filter [X] next to
		// each filter currently enabled/shown.
		$table  = "\n<table width=\"100%\"  class=\"filters_table\">";
		$table .= "\n<tr width=\"100%\">\n";

		if ($showthumbnails)  {
			$new_url = url_modify ($url , "t" , "n");
			$new_url_string = url_recombine ($new_url);
			$table .= "\n<td align=\"center\" class=\"thumbnails_outer\" width=\"5%\">\n";
			$table .= "<a class=\"tag_link\" href=\"". $new_url_string ."\">Hide&nbsp;thumbs</a>";
			$table .= "\n</td>";
			}
		else  {
			$new_url = url_modify ($url , "t" , NULL);
			$new_url_string = url_recombine ($new_url);
			$table .= "\n<td align=\"center\" class=\"thumbnails_outer\" width=\"8%\">\n";
			$table .= "<a class=\"tag_link\" href=\"". $new_url_string ."\">&nbsp;Show&nbsp;thumbs&nbsp;</a>";
			$table .= "\n</td>";
			}

		$table .= "\n<td class=\"filters_heading\" width=\"10\">\n<b>&nbsp;Filters</b>:\n<td>\n";

		if (sizeof($filters) > 0)  {
			for ($x = 1 ; $x < 6 ; $x++)  {
				// LATER - work out a better algorithm than either no width-tag,
				// and letting it free-form (good for one or two verbose tags)
				// or fixed length (18%) tags (good for visual consistency).
				$table .= "<td width=\"18%\" align=\"left\">\n";
				// $table .= "<td  align=\"left\">\n";
				if ($filters[$x])  {
					//$table .= "<font color=\"grey\">" . $filters[$x]['type'] . "</font>";
					$table .= "<i>" . $filters[$x]['type'] . "</i>";
					$table .= "/" ;
					$table .= "<b>" . $filters[$x]['value'] . "</b>";
					$url_a = url_modify ($url , "a" , "df" . (string)$x);
					$url_string = url_recombine ($url_a);
					$title_tag = "Remove filter\n" . $filters[$x]['type'] . "/" . $filters[$x]['value'];
					$table .= " <a href=\"" . $url_string . "\" title=\"" . $title_tag . "\">(X)</a>\n";
					}
				$table .= "\n</td>\n";
				}
			}
		else  {
			$table .= "\n<td width=\"90%\" align=\"left\">";
			$table .= " <i>None enabled.</i>\n";
			$table .= "\n</td>";
			}

		// Management button - when MGMT is enabled, the filter table isn't generated
		// so this isn't a toggle, just an ON BUTTON.  The OFF button is in the function
		// generate_management_table().
		// LATER - if you use the filter_table() stuff as part of management, then this
		// will need to be sorted out (wrapped in an if url[m] thing).
		$new_url     = url_modify ($url , "m" , "s");
		$title_tag   = "SHOW Management Tools\n(If you don't understand,\nthen don't press this)";
		$new_url_string = url_recombine ($new_url);
		$table .= "\n<td class=\"management_heading\"> ";
		$table .= "\n<a href=\"". $new_url_string ."\" ";
		$table .= "title=\"". $title_tag ."\" ";
		$table .= ">&nbsp;Admin&nbsp;Tools&nbsp;</a>\n<td>";

		$table .= "</tr>\n";
		$table .= "</table>";  // Don't put a \n here, or it pushes a blank line in.  Yucky.
		return $table;
		}



	// -------------------------------------------------------------------------
	function  filter_check_exist   ($filters_a , $filter_string)  {
		$filter_to_check = explode ("=" ,  $filter_string);
		$returnvalue = FALSE;
		if ($filters_a)
			foreach ($filters_a as $filter)
				if  ( 		($filter['type'] == $filter_to_check[0])
						&&	($filter['value'] == $filter_to_check['1']) )
					$returnvalue = TRUE;
		return $returnvalue;
		}


	// -------------------------------------------------------------------------
	function  filter_next_available  ($filters_a)   {
		// LATER - we can put the # of filters allowed in the config file.
		if ( ($x = sizeof($filters_a)) > 4)
			return NULL;
		else
			return ($x + 1);
		}


	// -------------------------------------------------------------------------
	function  filter_publish_array  ($fullpublish, $filters_a, $membergroups)   {
		if (sizeof ($filters_a) == 0)
			return $fullpublish;  // too easy

		// NOW - sorting out handling of [filter] (supergroup nomenclature).
		// note that this will break for any user that has square-brackets []
		// around any of his normal filters .. but who'd have that?  No one,
		// that's who.

		$x = 1;
		foreach ($fullpublish as $image)  {
			$matchimage = TRUE;
			foreach ($filters_a as $filter)  {
				$matchfilter = FALSE;
				// LATER - do a major fix on this, probably interpreting filters
				// and index.XML->publish[] to agree on a standard nomenclature,
				// probably biting the bullet and doing it the jedd way, rather
				// than the xml way (it never gets pushed back to the index.xml
				// anyway).

				$filter_type   =  $filter['type'];
				$filter_value  =  $filter['value'];
				if ($image[$filter_type])   {  // We have at least potential to match, so search
					if ($filter_value[0] == "[")  {
						// Okay - we're working with a GROUP of basically OR-filters, so invert the logic
						$groupmatch = FALSE;
						foreach ($filter['children'] as $child)
							foreach ($image[$filter_type] as $thing)
								if ($thing == $child)
									$groupmatch = TRUE;
						if ($groupmatch)
							$matchfilter = TRUE;
						}
					else  // normal filter - a reductive filter (a big AND)
						foreach ($image[$filter_type] as $thing)
							if ($thing == $filter_value)
								$matchfilter = TRUE;
					}

				if ($matchfilter == FALSE)
					$matchimage = FALSE;

				}  //  end-foreach $filters as $filter
			if ($matchimage == TRUE)
				$newpublish[$x++] = $image;
			}  // end-foreach $fullpublish as $image

		return $newpublish;
		}


	// -------------------------------------------------------------------------
	function  generate_management_table  ( $url , $publish , $fullpublish, $settings)   {
		// We generate a table, like the filter table, that shows a few
		// links that either report on, and maybe later work to fix up,
		// the cache ...


		$table  = "\n<table width=\"100%\"  class=\"management_table_heading\">";
		$table .= "\n<tr>\n";

		$url_x = url_modify ($url , "m", "s");
		$table .= "\n<td class=\"management_menu_button\">";
		$table .= "\n<a href=\"". url_recombine ($url_x) ."\">Show Cache Stats</a>";
		$table .= "\n</td>";


		// Far-right - fixed button to take us away from this ADMIN stuff
		$new_url        = url_modify ($url , "m" , NULL);
		$new_url_string = url_recombine ($new_url);
		$table .= "\n<td class=\"management_heading\">";
		$table .= "\n<a href=\"". $new_url_string ."\" ";
		$table .= "title=\"\nHIDE&nbsp;Management&nbsp;Tools\n(Return to normalcy)\n&nbsp;\" ";
		$table .= ">Admin&nbsp;OFF</a>\n<td>";
		$table .= "\n</tr>";


		$table .= "\n</table>";
		return $table;
		}
