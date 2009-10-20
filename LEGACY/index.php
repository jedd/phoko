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


	require_once ('config.php');
	require_once ('functions.php');
	require_once ('header.php');

	// Performance check (used later as part of a delta)
	$start_time  =  grab_utime();

	// Break down the URL query section into mode[] array,
	// containing all value/key pairs.
	$url_a       =  url_split ($_SERVER['REQUEST_URI']);

	// ?a means ACTION  (such as ?a=df means Delete a Filter).  We
	// have to do ACTIONs early, as they change the EFFECTIVE URL
	// that we proceed to work with.
	if ($a_query = $url_a['query']['a'])  {
		// LATER - other ACTIONS can be dealt with in here.
		if ($a_query[0] == 'd')
			if ($a_query[1] == 'f')  {
				// Delete Filter rule
				$filter_to_delete = 'f' . $a_query[2] ;
				$newurl = url_modify ($url_a , $filter_to_delete, NULL);
				$url_a = $newurl;
				}
		// Remove a= query from canonical url_a[] before proceeding.
		$newurl = url_modify ($url_a , "a" , NULL);
		$url_a = $newurl;
		}


	// Load and interpret index xml - two components are returned:
	$fullxmlextract = load_index_xml_file ($xmlfile, $PUBLISHKEYWORD, $picturerepository, $SHOOSHTAGS) ;
	$fullpublish   =  $fullxmlextract['imagearray'];
	$membergroups  =  $fullxmlextract['membergroups'];


	// This is very handy later on.
	$filters_a   =  filter_extract_from_url ($url_a, $membergroups);


	// LATER - new function to tidy up $membergroups:
	// --- strip out items that aren't represented in $publish
	// --- don't have any member-groups that are in use in $publish
	// --- rename major sections to Persons/Place/Keyword
	// --- ... anything else?


	// If we have any filters, modify publish[] list to reflect them.
	if ( $filters_a )
		$publish = filter_publish_array ($fullpublish , $filters_a, $membergroups);
	else
		$publish = $fullpublish;

	if ($url_a['query']['i'] == NULL)
		$url_a = url_modify($url_a , "i" , $publish[1]['md5sum']);

	// LATER - assess uniqueness of first n chars in md5sum,
	// as it may be feasible to make the URL much much shorter,
	// by offering either minimally unique string (say first 5
	// chars or so), and just handle it if there's duplicates
	// (say if there's an addition to the image set, subsequently,
	// that includes the same first 5 chars as *its* md5sum).

	// LATER - set up a $settings[] array with all the different settings
	// that we can show - as determined by ?q entries - stick 'em all
	// into the one place for convenience.  It'll require a lot of
	// downstream changes though.

	// The new $settings[] array - migrate other settings to this thing,
	// which will make it easier to set defaults (cookies anyone?) LATER.

	// Thumbnails
	if ($url_a['query']['t'] == "n" )
		$settings['showthumbnails'] = false;
	else
		$settings['showthumbnails'] = true;

	// Pulled from config.php, stored in $settings for function calls
	$settings['thumbsperpage'] = $thumbsperpage;

	// Management (must follow Thumbnails, as it supersedes it)
	if ($url_a['query']['m'])  {
		$settings['show_management_stuff'] = true;
		$settings['management_action'] = $url_a['query']['m'];  // the management 'action'
		$settings['show_filter_table'] = false;  // The only thing that disables filter_table
		$settings['show_thumbnail_scrollbar'] = false;  // The only thing that disables this
		$settings['showthumbnails'] = false;     // Thumbnails are disabled in Management Mode
		}
	else  {
		$settings['show_management_stuff'] = false;
		$settings['show_filter_table'] = true;  // This is the default setting, as it were.
		$settings['show_thumbnail_scrollbar'] = true; // This is the default setting, as it were.
		}

	// Pull this from config.php - option may exist in the future to modify this on the fly.
	$settings['scrollbar_granularity'] = $scrollbar_granularity;

	// EXIF Verbosity
	if ( ($url_a['query']['e'] > 0) && ($url_a['query']['e'] < 4) )
		$settings['exif_verbosity'] = $url_a['query']['e'];
	else  {
		$url_a = url_modify ($url_a , "e" , NULL);
		$settings['exif_verbosity'] = 0;
		}

	// TAGS expanded (location, people, places, etc)
	// ( There is a neatness to having only one expanded at a time, but
	//   maybe LATER look at multiple tag-types exploded?  )
	$settings['tag_type_to_show'] = urldecode ($url_a['query']['st']);


	// LATER
	// come up with a set of variables for use all over the place that show:
	// - ideal offset
	// - ?
	// If the ?o isn't set, on the URL, then set one in the url_a[] array.
	// consider putting these, along with config variables, into a
	// settings[] array for handing to all those functions .. to save
	// on the problem of using the nasty evil global command (in two
	// functions so far, i think).

	// LATER
	// check how the thing handles the absence of various ?q= items,
	// specifically the ksort problem that pops up in the absence
	// of a list of publishable pics.

	// LATER extend the above - insert sensible offsets (absent same)
	// if someone lands with an ?i=md5checksum url only, or with an
	// o=thumboffset only .. etc.

	// Load catcounts array with details on numbers of each place, person, etc
	$catcount = count_categories ($publish);


	// Explorifier, at least, needs IN_TOTO set .. other places might want the reduced version.
	$tag_categories_in_use  = identify_tag_categories ($publish);
	$tag_categories_in_toto = identify_tag_categories ($fullpublish);


	if ( $settings['showthumbnails'] )  {
		// Thumbnails along the top - you're almost always going to want this stuff here.
		if ( ($url_a['query']['o']) && ((int)$url_a['query']['o'] <= sizeof($publish)) )
			$offset = $url_a['query']['o'];
		else
			$offset = $url_a['query']['o'] = 1;

		if ($offset > (sizeof($publish) - $thumbsperpage + 1))
			$offset = 1;

		if (($offset + $thumbsperpage - 1) < sizeof($publish))
			$lastpic = $offset + $thumbsperpage -1 ;
		else
			$lastpic = sizeof($publish);

		if (! $url_a['query']['i'])  {
			// No image provided - set to the first one by default.
			// LATER - check this, as it's already done above .. not sure why still here.
			}


		$first_thumbnail_shown = $offset;
		$last_thumbnail_shown = $lastpic;

		// Show thumbnails here:
		echo "\n<table  width=\"100%\">";
		echo "\n<tr valign=\"middle\">";

		// Left-side thumbnail navigation 'buttons'
		echo "\n<td width=\"4%\" align=\"left\" class=\"thumbnails_outer\">";
		if ($offset == 1)
			echo "<font class=\"tag_link_unselected\" color=\"grey\">&lt;&nbsp;1&nbsp;</font>";
		else  {
			$next_url        = url_modify    ($url_a , "o" , (string)( $offset - 1 ) );
			$next_url_str    = url_recombine ($next_url);
			echo "<a class=\"tag_link\" href=\"" . $next_url_str . "\">&lt;&nbsp;1&nbsp;</a>\n";
			}

		echo "<br />\n";

		if ($offset == 1)
			echo "<font class=\"tag_link_unselected\" color=\"grey\">&lt;&lt; " . $thumbsperpage . "</font>";
		else  {
			if ($offset > $thumbsperpage)
				$newoffset = $offset - $thumbsperpage ;
			else
				$newoffset = 1;
			$next_url        = url_modify    ($url_a , "o" , (string)$newoffset);
			$next_url_str    = url_recombine ($next_url);
			echo "<a class=\"tag_link\" href=\"" . $next_url_str . "\">&lt;&lt; " . $thumbsperpage . "</a>\n";
			}
		echo "<br />\n";

		if ($offset == 1)
			echo "<font class=\"tag_link_unselected\" color=\"grey\">&lt;&lt;&lt;&lt;&nbsp;</font>";
		else  {
			$next_url        = url_modify    ($url_a , "o" , (string)( 1 ) );
			$next_url_str    = url_recombine ($next_url);
			echo "<a class=\"tag_link\" href=\"" . $next_url_str . "\">&lt;&lt;&lt;&lt;&nbsp;</a>\n";
			}
		echo "<font size =\"-2\"><br />&nbsp;&nbsp;[1]</font>";
		echo "</td>\n";


		// ------------------------------------------------------------------------
		// Thumbnail area (top, middle) - the actual pictures
		echo "\n<td  width=\"92%\">";
		echo "<table width=\"100%\">"; // table to fit the thumbnail scollbar under
		echo "<tr>";
		echo "<td class=\"thumbnails_inner\">";
		echo "\n<font size=\"+3\">" . $first_thumbnail_shown . "</font>";

		// We set this here, and use it late when deciding whether to shift
		// the thumbnail offset by +/- one on next/prev button.
		$current_thumbnail_is_in_view = false;

		for ( $x = $offset ; $x < ($offset + $thumbsperpage); $x++ )  {
			$thumbtoshow = $publish[$x];
			$url_2           = url_modify ($url_a , "i" , $thumbtoshow['md5sum']) ;
			if (! $url_a['query']['o'])
				$url_3       = url_modify ($url_2 , "o" , "1");
			else
				$url_3       = $url_2;
			$next_url_str    = url_recombine ($url_3);
			if ($publish[$x])  {
				generate_cache_image ($imagesizes , $thumbtoshow['md5sum'] , $publish, "thumb");
				echo "\n<a href=" . $next_url_str . ">";
				echo "<img ";
				if ($thumbtoshow['md5sum'] == $url_a['query']['i'])  {
					echo "style=\"border-color:red\" border=\"3\"";
					$current_thumbnail_is_in_view = true;
					}
				echo " border=\"0\" src=\"image.php?size=thumb&md5=". $thumbtoshow['md5sum'] ."\">";
				//echo "src=\"image.php?size=thumb&file=" . $thumbtoshow['file'] ;
				//echo "&md5=" . $thumbtoshow['md5sum'] . "\">";
				echo "</a>&nbsp;";
				}
			}
		echo "<font size=\"+3\">" . $last_thumbnail_shown . "</font>";
		echo "</td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td>";
		// ========================================================================
		// Show the pseudo thumbnail scroll bar just beneath the thumbs and above filters.
		// We test both settings - we don't use scrollbar yet, but might differentiate later.
		// LATER - work out why this is padded, despite me asserting no padding everywhere.
		// ALSO note that it's not quite 100% (probably a clue) and gets a backround (similar)
		// if one's noted in the css.  Probably an inherited problem .. thus cascading.
		if ( $settings['show_thumbnail_scrollbar']  &&  $settings['showthumbnails'] )
			echo generate_thumbnail_scrollbar  ( $url_a , $publish , $settings );
		echo "</td>";
		echo "</tr>";

		echo "</table>";
		echo "</td>";



		// Right-side thumbnail navigation 'buttons'
		echo "\n<td width=\"4%\" align=\"right\" class=\"thumbnails_outer	\">\n";
		if ( ($offset + $thumbsperpage) > sizeof($publish))
			echo "<font class=\"tag_link_unselected\" color=\"grey\">&nbsp;1&nbsp;&gt;</font>";
		else  {
			$next_url        = url_modify    ($url_a , "o" , (string)( $offset + 1 ) );
			$next_url_str    = url_recombine ($next_url);
			echo "<a class=\"tag_link\" href=\"" . $next_url_str . "\">&nbsp;1&nbsp;&gt;</a>";
			}

		echo "<br />\n";

		if  ( $offset  >  (sizeof($publish) - $thumbsperpage) )
			echo "<font class=\"tag_link_unselected\" color=\"grey\">". $thumbsperpage ." &gt;&gt;</font>";
		else  {
			if ( ( sizeof ($publish) - ( $thumbsperpage * 2 )) >=  $offset )
				$newoffset = ( $offset + $thumbsperpage );
			else
				$newoffset = sizeof ($publish) - $thumbsperpage + 1;
			$next_url        = url_modify    ($url_a , "o" , (string)$newoffset);
			$next_url_str    = url_recombine ($next_url);
			echo "<a class=\"tag_link\" href=\"" . $next_url_str . "\">". $thumbsperpage ." &gt;&gt;</a>";
			}

		echo "<br />\n";

		if ( ($offset + $thumbsperpage) > sizeof($publish))
			echo "<font class=\"tag_link_unselected\" color=\"grey\">&nbsp;&gt;&gt;&gt;&gt;</font>";
		else  {
			$next_url       = url_modify ($url_a , "o" , (string)( sizeof($publish) - $thumbsperpage + 1) );
			$next_url_str   = url_recombine ($next_url);
			echo "<a class=\"tag_link\" href=\"" . $next_url_str . "\">&nbsp;&gt;&gt;&gt;&gt;</a>\n";
			}
		echo "<font size =\"-2\"><br />[". sizeof ($publish) ."]&nbsp;</font>";


		echo "\n</td>";
		echo "\n</tr>";
		echo "\n</table>";
		}


	// ========================================================================
	// Show the filter table just beneath the thumbnails, just above the
	// main table.  The function handles what to do if there's no filters.
	if ($settings['show_filter_table'])
		echo generate_filter_table  ( $filters_a , $url_a , $settings['showthumbnails'] );



	// ========================================================================
	// Show the management table just beneath the filter table, if it's selected.
	if  ($settings['show_management_stuff'])
		echo  generate_management_table ($url_a, $publish, $fullpublish, $settings);



	// ========================================================================
	/// Do the big table, that contains three sections
	// ==================================================
	/// LATER - really break these things down into sub-
	/// functions -- it'll make everything so much easier
	// ==================================================


	// For convenience sake, copy the current picture's information into $thispic
	foreach ($publish as $publishablepic)
		if ($publishablepic['md5sum'] == $url_a['query']['i'] )
			$thispic = $publishablepic;


	///  ********** THIS IS THE START OF THE MAIN FULL-WIDTH TABLE
	///
	/// There's an ALMIGHTY if {} around the contents of this table ..
	/// and it will look at lot better, LATER, when it's a bunch of
	/// calls to functions in function.php .. rather than this monolithic
	/// mess.  In the interim .. deal with it.



	if  ($settings['show_management_stuff'])    {
		require_once ('functions_admin.php');
		// We do Management Stuff, rather than Picture Stuff.
		echo "\n<br />";
		echo "\n<table class=\"management_table_body\" >";
		switch  ($settings['management_action'])  {
			case "ct" :	echo admin_create_thumbnails ($fullpublish , $imagesizes);
						break;
			case "cb" :	echo admin_create_bigs ($fullpublish , $imagesizes);
						break;
			case "d" :	echo admin_delete_surplus_cache_files ($fullpublish);
						break;
			case "s" :	echo "<pre>";
						echo admin_cache_stats ($fullpublish , $url_a);
						break;
			default :	echo "Dodgy URL parameter provided.";
			}
		echo "\n</table>";
		echo "\n<hr>";
		}
	else  {  // Picture stuff - what we usually do
		/// ==============================================================================
		/// THIS IS THE START OF THE ALMIGHTY TABLE - CONTAINING THREE SUB-TABLES, AS ONE
		/// BIG MONOLITHIC LUMP OF CODE ... IT WILL BE FIXED ... LATER.  HONESTLY IT WILL.
		/// ==============================================================================

		echo "\n<table width=\"100%\" border=\"1\">";
		echo "\n<tr>";

		// Left-hand box - available tags
		echo "\n<td valign=\"top\" align=\"left\" width=\"18%\" class=\"picture_info_headings\">";
		echo "\n<p class=\"left_right_headings\">";
		echo "\n<br />&nbsp;Tag explorifier" ;
		echo "\n</p>";

		foreach ($tag_categories_in_toto as $this_tag_type)  {
			$tag_counts_filtered        =  find_all_image_tags ($publish , $this_tag_type);
			$tag_counts_unfiltered      =  find_all_image_tags ($fullpublish , $this_tag_type);
			$setornot =  ($settings['tag_type_to_show'] == $this_tag_type)   ?   NULL  :  $this_tag_type;
			$collapse_url_a       = url_modify ($url_a , "st" , urlencode($setornot));
			$collapse_url_string  = url_recombine ($collapse_url_a);
			$heading  = "<a class=\"tag_link_heading\" href=\"" . $collapse_url_string . "\">" ;
			$heading .= nbsp (1);
			$heading .= ($settings['tag_type_to_show'] == $this_tag_type)  ? "-" : "+" ;
			$heading .= nbsp (1);
			$heading .= ucfirst ($this_tag_type) ."</a>";
			echo $heading;

			echo "<br />";

			if ($settings['tag_type_to_show'] == $this_tag_type)  {
				echo  generate_explorifier ( $membergroups[$this_tag_type], $this_tag_type, $url_a,
											$tag_counts_filtered, $tag_counts_unfiltered,
											$filters_a, $publish, $url_a['query']['i']);
				echo "<br />";

				}
			echo "</p>";
			}



		// ----------------------------------------------------------------------
		// Middle box - the medium-sized image proper
		// ------------------------------------------

		// LATER _ work out maybe a javascript way of identifying
		// the width of the display .. and fix up the size - fixed
		// or %age - for the middle pane.
		//
		// By using large size + 10 pixels, rather than a percentage, we get
		// a consistent location for prev/next buttons, even on portrait images.
		// echo "<td align=\"center\" width=\"". ($imagesizes['large']['x'] + 10) ."\" bgcolor=\"#cfffcf\">";

		echo "\n<td valign=\"top\" align=\"center\" width=\"48%\" bgcolor=\"#cfffcf\">";

		// Mini-table inside to do the left/right buttons - perhaps div instead?
		$prev_image_md5 = prev_image ($publish , $url_a['query']['i']);
		$next_image_md5 = next_image ($publish , $url_a['query']['i']);
		echo "\n\n<table width=\"100%\"><tr><td width=\"33%\" align=\"left\" bgcolor=\"#afffaf\">";

		$image_number = image_number ($publish , $url_a['query']['i']);

		if ($prev_image_md5)  {
			if ($current_thumbnail_is_in_view)
				if ($url_a['query']['o'] > 1)
					$temp_url_a = url_modify ($url_a, "o", ($url_a['query']['o'] -1));
				else
					$temp_url_a = $url_a;
			else
				// if image number is near the start ...
				// LATER - come up with a much better algorithm than this stinker
				if ($image_number < ( $x = (($thumbsperpage) / 2) + ($thumbsperpage / 4)) )
					$temp_url_a = url_modify ($url_a, "o", 1);
				else
					if ($image_number > (sizeof($publish) - $thumbsperpage))
						$temp_url_a = url_modify ($url_a, "o", (sizeof($publish) - $thumbsperpage));
					else
						$temp_url_a = url_modify ($url_a, "o", ($image_number - ($thumbsperpage / 2)));

			$new_url_a = url_modify ($temp_url_a, "i" , $prev_image_md5);
			$new_url_string = url_recombine ($new_url_a);
			echo "<a href=\"" . $new_url_string . "\" class=\"prev_next_buttons\">";
			echo "&lt;&lt;&nbsp;Previous&nbsp;&lt;&lt;</a>";
			}
		else
			echo "<i>At beginning</i>";


		echo "\n</td>";
		echo "\n<td align=\"center\" width=\"33%\" bgcolor=\"#cfffcf\">";
		echo "Image <b>" . $image_number . "</b> of <b>". sizeof ($publish) ,"</b><br />";
		echo "\n</td>";
		echo "\n<td align=\"right\" width=\"33%\" bgcolor=\"#afffaf\">";


		// LATER - fix this up, as offset is really screwed up, and NEXT
		// doesn't set the url the way that it should - edge cases are
		// especially screwy at the end of the array.  Confirm for arrays < sizeof(publish)
		// amongst other confusions.
		if ($next_image_md5)  {
			if ($current_thumbnail_is_in_view)
				if ($url_a['query']['o'] < (sizeof($publish) - $thumbsperpage + 1))  {
					$temp_url_a = url_modify ($url_a, "o", ($url_a['query']['o'] + 1));
					}
				else  {
					$temp_url_a = $url_a;
					}
			else  {
				$probable_best_offset = $image_number - ( $thumbsperpage / 2) + ($thumbsperpage / 4);
				if ($probable_best_offset > ( sizeof($publish) - $thumbsperpage + 1))  {
					$best_offset = ( sizeof($publish) - $thumbsperpage + 1);
					}
				else
					$best_offset = $probable_best_offset;
				$temp_url_a = url_modify ($url_a, "o", (int)$best_offset);
				}
			$new_url_a = url_modify ($temp_url_a, "i" , $next_image_md5);
			$new_url_string = url_recombine ($new_url_a);
			echo "<a href=\"" . $new_url_string . "\" class=\"prev_next_buttons\">";
			echo "&gt;&gt;&nbsp;Next&nbsp;&gt;&gt;</a>";
			}
		else
			echo "<i>At end</i>";

		echo "\n</td></tr></table>";



		// And then, finally, call up the actual large image
		generate_cache_image ($imagesizes , $url_a['query']['i'] , $publish, "large");
		generate_cache_image ($imagesizes , $url_a['query']['i'] , $publish, "huge");
		echo "<a href=\"image_popup.php?size=huge&md5=". $url_a['query']['i'] ."\"";
		echo " TARGET=\"_blank\">";
		echo "<img border=\"0\" width=\"100%\" src=\"image.php?size=large&md5=". $url_a['query']['i'] ."\">";
		echo "</a>";
		echo "\n</td>";





		// ----------------------------------------------------------------------
		// Right-hand side box - tags relating to this picture
		echo "\n<td valign=\"top\">";
		echo "\n<p class=\"left_right_headings\">";
		echo "\n<br />&nbsp;Picture information" ;
		echo "\n</p>";

		// LATER - offer search / filter capabilities here - YYYY as one URL,
		// then -MM as a filter for that specific yyyy-mm, then down to -dd.
		// The filter will have to look at start and end dates both, to match,
		// but that shouldn't be too tricky.

		// First, the date - easy.
		echo "<font class=\"picture_info_heading\">";
		echo "<b>&nbsp;Date: </b>" ; // . $thispic['startDate'] ;
		echo "</font>";
		echo format_pretty_date ($thispic['startDate']);
		if ($thispic['startDate'] != $thispic['endDate'])
			echo " &nbsp; -- &nbsp; ".format_pretty_date ($thispic['endDate']);

		// Handle membergroups in [] brackets (keep them in the filter name - to identify MG's)
		foreach ($tag_categories_in_use as $this_tag_type)  {
			echo "\n<br />";
			echo "<font class=\"picture_info_heading\">";
			echo "&nbsp;". ucfirst(urldecode($this_tag_type))  .":";
			echo "</font>";
			$comma = 0;
			if (isset ( $thispic[$this_tag_type]))  {
				// LATER - if we really want 'none specified' bits here (after all we can
				// always consult the tag-explorifier) we can bring the heading into this
				// section - and therefore only show Tag Types that have at least one
				// match for this picture.  My personal preference is to actally revert
				// to special-case handling - people/place/keywords are always present,
				// but special categories are hidden if they're empty.  Need to ponder.
				foreach ($thispic[$this_tag_type] as $tag) {
/*					if  (	(isset ($SHOOSHTAGS[$this_tag_type]))
							&& (! in_array ($tag, $SHOOSHTAGS[$this_tag_type]))  )  {*/
					if  (! in_array ($tag, $SHOOSHTAGS[$this_tag_type]))   {
						// LATER - do this better - assess the numbers of candidates better
						$sep = ($comma++) ? ", " : " ";
						if (		( filter_check_exist ($filters_a , $this_tag_type ."=". $tag) )
								||	( filter_next_available($filters_a) == NULL) )
							echo $sep . $tag;
						else  {
							$filtername = "f". filter_next_available($filters_a) ;
							$thisurl = url_modify ($url_a , $filtername , urlencode ($this_tag_type ."=". $tag));
							$thisurlstring = url_recombine ($thisurl);
							echo $sep . "<a class=\"tag_link\" href=\"". $thisurlstring . "\">". $tag ."</a>" ;
							}
						if ($y = $catcount[$this_tag_type][$tag])
							echo "<font size=\"-1\"><b><i> (". $y .")</i></b></font>";



						$parentgroup = lookup_parent_group ($this_tag_type, $tag, $membergroups);
						if  (	(isset ($SHOOSHTAGS[$this_tag_type]))
							&& (! in_array ($parentgroup, $SHOOSHTAGS[$this_tag_type]))  )  {

							if ( $parentgroup )  {
								if ( 	( filter_check_exist ($filters_a , $this_tag_type ."=[". $parentgroup ."]") )
										|| ( filter_next_available($filters_a) == NULL) )
									echo "[". $parentgroup ."]";
								else  {
									$filtername = "f". filter_next_available($filters_a);
									$thisurl = url_modify ($url_a , $filtername ,
												urlencode ($this_tag_type ."=[". $parentgroup ."]"));
									$thisurlstring = url_recombine ($thisurl);
									echo " [<a class=\"tag_link\" href=\"". $thisurlstring ."\">". $parentgroup ."</a>]";
									}
								}
							}
						}
					}
				}
			else  {
				echo " none specified";
				}
			echo ".";
			}




		$comma = 0;
		// LATER - this is crying out for a DIV / AJAX thingy.
		echo "\n<br />";
		echo "<font class=\"picture_info_heading\">";
		echo "&nbsp;EXIF info:";
		echo "</font>";
		switch ($settings['exif_verbosity'])  {
			case 0	:	$up_verbosity       =  url_modify ($url_a , "e" , "1");
						$up_url_string      =  url_recombine ($up_verbosity);
						echo   "(--/<a class=\"tag_link\" href=\"" . $up_url_string . "\">++</a>)";
						break;
			// LATER - change this to url[query][e]+1 or -1 and set case1 & case2 in
			// the same subsection (fall-through from 1 to 2).  Slightly neater, but
			// possibly less readable - have a think.
			case 1	:	$down_verbosity     =  url_modify ($url_a , "e" , NULL);
						$down_url_string    =  url_recombine ($down_verbosity);
						$up_verbosity       =  url_modify ($url_a , "e" , "2");
						$up_url_string      =  url_recombine ($up_verbosity);
						echo  "(<a class=\"tag_link\" href=\"". $down_url_string . "\">--</a>/";
						echo   "<a class=\"tag_link\" href=\"" . $up_url_string . "\">++</a>)";
						break;
			case 2	:	$down_verbosity     =  url_modify ($url_a , "e" , "1");
						$down_url_string    =  url_recombine ($down_verbosity);
						$up_verbosity       =  url_modify ($url_a , "e" , "3");
						$up_url_string      =  url_recombine ($up_verbosity);
						echo  "(<a class=\"tag_link\" href=\"". $down_url_string . "\">--</a>/";
						echo   "<a class=\"tag_link\" href=\"" . $up_url_string . "\">++</a>)";
						break;
			case 3	:	$down_verbosity     =  url_modify ($url_a , "e" , "2");
						$down_url_string    =  url_recombine ($down_verbosity);
						echo   "(<a class=\"tag_link\" href=\"" . $down_url_string . "\">--</a>/++)";
						break;
			}
		if ($settings['exif_verbosity'] > 0)  {
			echo "<br />&nbsp;&nbsp;&nbsp;";
			// We send fullpublish as we might have an image in focus that isn't in thumbnail list now.
			echo grab_exif_info ($fullpublish , $url_a['query']['i'] , $settings['exif_verbosity']);
			}

		// LATER -
		// during thumbnail creation, copy exif data from main to thumb?
		// - is there an imagemagick or gd script for this, or a call
		// out to system ('exifcopy') ?

		// LATER - work out why empty lines aren't being respected in
		// the index.xml -> publish[] conversion - newlines aren't showing
		// up in a var_dump of the string .. so something is stripping them.
		if (isset ($thispic['description']))  {
			echo "<hr>";
			// I think this looks nicer without the word 'Description' at the top,
			// but it probably depends on the user and the nature & quality of the
			// comments in the kphotoalbum repository.  Maybe LATER set this as a
			// user (cookie) or config.inc setting .. ?
			//echo "\n<p><b>&nbsp;Description:</b><br />";
			echo "<br />";
			// LATER - I think a table-within a table, or better yet, work out how to fix
			// up the PADDING in this TD .. so the text is slightly indented all the way down.
			echo "&nbsp;&nbsp;". $thispic['description'];
			echo "</p>";
			}





		// ============================================
		// debugging area - noice
		echo "<hr>";
		echo "<pre>";
		// 	$bob = lookup_child_groups ("Locations"	, "China" , $membergroups);
		// 	var_dump ($filters_a);

		echo "\n</pre>";
		// ===========================================

		echo "\n</td>";
		echo "\n</tr>";
		echo "\n</table>";

		}/// ==================================================================
		///  THIS IS THE END OF THE ALMIGHTY TABLE - CONTAINING THREE SUB-TABLES,
		///  AS ONE BIG MONOLITHIC LUMP OF CODE ... IT WILL BE FIXED ... LATER.
		///  ==================================================================



	include ('footer.php');
?>

