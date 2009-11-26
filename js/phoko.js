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


var userpanel = $("#accordion");
var active_accordion = $.cookie("accordionselected");
if (active_accordion == undefined) {
	$.cookie("accordionselected", 0);
	// active_accordion = 0;  // Gave this up and instead pull straight from cookie in the accordion function below
	}


/// THIS WORKS - that is, right now the cookie contains the right number (0-3) depending on the last accordion setting
// alert ("Hey, cookie says : " + $.cookie("accordionselected"));


// Accordion jQuery-UI effect
$(function() {
	$("#accordion").accordion({
		// active: 2, // THIS WORKS, OF COURSE
		active: $.cookie("accordionselected", 0),  // THIS DOESN'T WORK! I consistently get a collapsed accordion
		autoHeight: false,
		header: 'h3',
		collapsible: true,
		change: function(event, ui) {
			var index = $(this).find("h3").index ( ui.newHeader[0] );
			$.cookie("accordionselected", index);
			}
		});
	});


