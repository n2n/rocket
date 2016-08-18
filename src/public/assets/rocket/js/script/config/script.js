(function($) {
	if (typeof rocket == 'undefined') return;
	
	//setup rocket.script;
	var RocketDraggable = function(jqElem, options) {
		if (jqElem.find(".rocket-config-list-drag").length == 0) { 
			jqElem.prepend($("<span/>").addClass("rocket-config-list-drag")
					.append($("<i/>", {"class": "icon-th"})));
		}
		jqElem.addClass("rocket-draggable");
		
		var defaultOptions = {
			start: function(event, ui) {
				$(this).addClass("rocket-draggable-active");
			},
			stop: function(event, ui) {
				var _obj = $(this);
				//clone the helper, cause it's getting destroyed by default before the animation starts
				var helperClone = ui.helper.clone();
				ui.helper.after(helperClone);
				helperClone.animate({
					left: _obj.position().left,
					top: _obj.position().top,
					opacity: 0.3
				}, 'slow', function(){
					$(this).fadeOut('fast', function() {$(this).remove()});
				});
				$(this).removeClass("rocket-draggable-active");
				jqElem.trigger('drop');
			},
			refreshPositions: true,
			cursor: "move",
			helper: function( event ) {
				var jqElemParent = $("<div/>");
				if ($(this).is("tr")) {
					jqElemParent = $("<table/>");
				} 
				return jqElemParent.addClass("rocket-dragging").css({
					width: $(this).parent().width(),
					padding: 0,
					margin: 0,
					border: "1px solid #A0958B",
					opacity: 0.8,
					zIndex: 100
				}).css("background-color", $(this).css("background-color")).append($(this).clone(true));
			}
		};
		jqElem.draggable($.extend(defaultOptions,options));
	};
	
	var RocketSortable = function(jqElem, draggableOptions, droppableOptions) {
		var defaultDraggableOptions = {axis: "y"};
		new RocketDraggable(jqElem, $.extend(defaultDraggableOptions, draggableOptions));
		var defaultDroppableOptions = {
				tolerance: "intersect",
				hoverClass: "rocket-config-list-section-hover",
				
				over: function(event, ui) {
					if (ui.offset.top > $(this).offset().top) {
						$(this).before(ui.draggable);
					} else {
						$(this).after(ui.draggable);
					}
				}
				
			}
		jqElem.droppable($.extend(defaultDroppableOptions, droppableOptions));
	};
	
	var RocketScript = function() {
		this.Draggable = RocketDraggable;
		this.Sortable = RocketSortable;
	}
	rocket.script = new RocketScript();
})(jQuery)
