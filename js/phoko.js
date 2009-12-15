/*
 * phoko custom jQuery stuff
 *
 * Primarily just settings / functions for the jQuery UI
 * features we're using - navitabs and accordion.
 */

// Image size calculation

$(document).ready(function() {
	$("#actual_image").click(function () {
		var iw = $("#actual_image").width();
		// alert (iw);
		});
	var iw = $("#actual_image").width();

	// @todo - work out whether to do all this in js (perhaps we have to?)
	// or .. whatever.  probably need a function to update the imagemap, and
	// we call it on click as well as on resize of window?  
	// No idea how we get the prev & next URL's ... oh, hang on, they can
	// come in on page load, the same way that the 'open this url in a new window'
	// does for it right now.
	})




// Tabs jQuery-UI effect
$(function() {
	$("#navi_tabs").tabs({
		fx:  {
			opacity: 'toggle'
			},
		cookie: true,					// sadly there's no equiv in the accordion() world
		});
	});


/// legacy experiment with code grabbed from the net somewhere
// var userpanel = $("#accordion");


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
		collapsible: true,

		// active: 2, // THIS WORKS, OF COURSE
		active: $.cookie("accordionselected"),  // THIS DOESN'T WORK! I consistently get a collapsed accordion

		autoHeight: false,
		header: 'h3',
		change: function(event, ui) {
			var index = $(this).find("h3").index ( ui.newHeader[0] );
			// alert (index);  /// This WORKS - it shows 0-3 depending on what I've just selected
			$.cookie("accordionselected", index);
			}
		});
	});


