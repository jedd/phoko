/*
 * phoko custom jQuery stuff
 *
 * Primarily just settings / functions for the jQuery UI
 * features we're using - navitabs and accordion.
 */

// Tabs jQuery-UI effect
$(function() {
	$("#navi_tabs").tabs({
		fx:  {
			opacity: 'toggle'
			},
		cookie: true,					// sadly there's no equiv in the accordion() world
		});
	});

// Accordion jQuery-UI effect
$(function() {
	$("#accordion").accordion({
		autoHeight: false,
		header: 'h3',
		cookie: true,
		collapsible: true
		// navigation: true
		// active: 0 // open first category at load by default
		});
	});


