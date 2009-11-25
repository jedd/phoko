/*
 * phoko custom jQuery stuff
 *
 * Primarily just settings / functions for the jQuery UI
 * features we're using - navitabs and accordion.
 */

// Tabs jQuery-UI effect
$(function() {
	$("#navi_tabs").tabs({ fx: { opacity: 'toggle' } });
	//getter
	var cookie = $("#navi_tabs").tabs('option', 'cookie');
	//setter
	$("#navi_tabs").tabs('option', 'cookie', { expires: 30 });
	});

// Accordion jQuery-UI effect
$(function() {
	$("#accordion").accordion({
		autoHeight: false,
		header: 'h3',
		collapsible: true,
		navigation: true,
		active: true // open first category at load by default
		});
	});


