jQuery(document).ready(function($) {
	(function() {
		var jqElemsContentItems = $(".rocket-content-items");
		if (jqElemsContentItems.length === 0) return;
		(function() {
			var ContentItemPanel = function(label) {
				this.contentItems = new Array();
				this.jqElem = $("<li/>", {"class": "rocket-content-item-panel"});
				this.jqElemCiList = $("<ul/>", {"class": "rocket-option-array rocket-content-items"}).appendTo(this.jqElem);
				this.jqElem.prepend($("<label/>", {"text": label}));
			};
			
			ContentItemPanel.prototype.addContentItem = function(contentItem) {
				var newContentItems = new Array();
				var set = false;
				for (var i in this.contentItems) {
					if (!set && (this.contentItems[i].orderIndex > contentItem.orderIndex)) {
						set = true;
						newContentItems.push(contentItem);
					}
					newContentItems.push(this.contentItems[i]);
				}
				if (!set) {
					newContentItems.push(contentItem);
				}
				this.jqElemCiList.append(contentItem.jqElem.parent("li").prepend($("<label/>", 
						{"text": contentItem.typeName})));
				rocket.core.contentInitializer.initElement(contentItem.jqElem);
				this.contentItems = newContentItems;
			};
			
			var ContentItem = function(jqElem) {
				this.jqElem = jqElem;
				this.typeName = $.trim(jqElem.find(".rocket-field-type:first")
						.hide().children(".rocket-controls").text());
				this.panel = $.trim(jqElem.find(".rocket-field-panel:first")
						.hide().children(".rocket-controls").text());
				this.orderIndex = $.trim(jqElem.find(".rocket-field-orderIndex:first")
						.hide().children(".rocket-controls").text());
			};
			
			var ContentItemOption = function(jqElem) {
				this.panels = new Object();
				this.panelNames = jqElem.data("panels");
				(function(_obj) {
					var jqElemUl = jqElem.find("ul:first");
					jqElemUl.find("> li > .rocket-properties").each(function() {
						var contentItem = new ContentItem($(this));
						if (!_obj.panels.hasOwnProperty(contentItem.panel)) {
							_obj.panels[contentItem.panel] = new ContentItemPanel(_obj.panelNames[contentItem.panel]);
						}
						_obj.panels[contentItem.panel].addContentItem(contentItem);
					});
					for (var i in this.panels) {
						jqElemUl.append(this.panels[i].jqElem);
					}
				}).call(this, this);
			};
			
			jqElemsContentItems.each(function() {
				new ContentItemOption($(this));
			});
		})();
	})();
});