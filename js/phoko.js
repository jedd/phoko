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
	var userpanel = $("#accordion"); 
	var index = $.cookie("accordion"); 
	var active_accordion; 
	if (index !== undefined) { 
		active_accordion = 0;
		} 
	$("#accordion").accordion({
		active: active_accordion,
		autoHeight: false,
		header: 'h3',
		cookie: true,
		collapsible: true,
		change: function(event, ui) { 
			var index = $(this).find("h3").index ( ui.newHeader[0] ); 
			$.cookie("accordion", index); 
			} 
		});
	});


jQuery(function($) { 
	var userpanel = $("#accordion"); 
	var index = $.cookie("accordion"); 
	var active; 
	if (index !== undefined) { 
		active = userpanel.find("h3:eq(" + index + ")"); 
		} 
//	userpanel.accordion({ 
//		active: active, 
//		change: function(event, ui) { 
//			var index = $(this).find("h3").index ( ui.newHeader[0] ); 
//			$.cookie("accordion", index); 
//			} 
//		}); 

	}); 
