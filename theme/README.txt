Existing Themes

	Default
		Dark blue motif - custom-rolled jquery-ui theme based on
		original colour scheme by jedd - see the internals of the
		custom.css file for a URL that will take you back to the
		themeroller site and let you modify this exact theme.

	Frosty
		Uses the 'cupertino' theme with one modification (font
		size changed from 1.1 to 1em) from themeroller at jquery-ui,
		or you can use the url within the css file to revisit the
		themeroller page and modify again if you want.


Installing New Themes
	You need a jquery_ui theme, which you can dump in (look at the
	existing options) and then symlink (again, look at existing) so
	you can keep the names of the originals/customs as they come in.
	You need a gallery.css file - look at existing ones for inspiration.
	Nothing is extraordinarily complex about this stuff.  Check the
	main_page.php (view) to clarify what the source is looking for.

	Symlink custom or pre-rolled jquery ui themes like this:
	#  ln  -s  jquery-ui-1.7.2.custom.css  jquery-ui.css

	The important thing being that there's a jquery-ui.css file available.

