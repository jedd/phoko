<?php
/**
 * Phoko
 *
 * Web gallery for a KPhotoAlbum repository
 *
 * @package		phoko
 * @author		jedd
 * @version		v1
 * @copyright	Copyright (c) 2009, jedd
 * @license		GPL 2 or later
 * @link		http://dingogully.com.au/trac/phoko
 **/

// ------------------------------------------------------------------------

/**
 * cache_status  (view)
 *
 * Prepare some text describing the state of play of the cache directory.
 *
 *
 * @package     phoko
 * @version     v1
 * @author      jedd
 * @link        http://dingogully.com.au/trac/phoko
 **/

// ------------------------------------------------------------------------
?>

<h1>
Cache status
</h1>

<h2>
Statistics
</h2>
<p>
We have a total of <?php echo $stats['kpa']['total']; ?> images available for viewing.
</p>

<p>
Within the image cache repository, we have:
<?php
	/// @todo pull the types array in from config or .. ?
	$sizes = array ('small', 'medium', 'large');
	foreach ($sizes as $size)
		echo "<br> o". nbs(3) . $stats[$size]['cache_count'] ." ". $size ." files";
?>
</p>
<table border="1" cellpadding="4" >
	<tr>
		<td>
		</td>
		<td>
			small
		</td>
		<td>
			medium
		</td>
		<td>
			large
		</td>
	</tr>
	<?php
		$items = array ('cache_size', 'cache_count', 'extraneous_count', 'missing_count');
		foreach ($items as $item)  {
			echo "\n<tr>\n";
			echo "\n<td>\n";
			echo $item;
			echo "\n</td>\n";
			foreach ($sizes as $size)  {
				echo "\n<td>\n";
				echo $stats[$size][$item];
				echo "\n</td>\n";
				}
			echo "\n</tr>\n";
			}
	?>
</table>

