/*
 * phoko custom jQuery stuff
 *
 * Primarily just settings / functions for the jQuery UI
 * features we're using - navitabs and accordion.
 */

// Tabs jQuery-UI effect
$(function() {
	$("#navi_tabs").tabs({
		fx: { opacity: 'toggle' },
		selected: 0,
		select: function (event,ui) {
			// var url = 'foobar' + $("#navi_tabs").attr("selectedIndex");
			var url = ui.tab;
			// var foo = url.attr(id);

			// var tabs = $("#navi_tabs").tabs();
			// var id = $('#selectBox').attr("selectedIndex");
			// var selectedTab = tabs("option", "selected");

			window.alert (url);


//			$("#accordion").accordion('activate', 0);
//			$("#accordion").accordion('activate', 1);
//			$("#accordion").accordion('activate', 2);
			}
		});
	//getter
	// var cookie = $("#navi_tabs").tabs('option', 'cookie');
	//setter
	// $("#navi_tabs").tabs('option', 'cookie', { expires: 30 });
	});

// Accordion jQuery-UI effect
$(function() {
	$("#accordion").accordion({
		autoHeight: false,
		header: 'h3',
		collapsible: true,
		navigation: true,
		active: 0 // open first category at load by default
		});
	});

$('#navi_tabs').each(function() {
   var href = $.data(this, 'href.tabs');
   console.log(href);
})

