namespace rocket.impl.overview {
	import cmd = rocket.cmd;
	import impl = rocket.impl;
	
	var $ = jQuery;

	export class Header {
		private jqElem: JQuery;
		private jqAllButton: JQuery;
		private jqSelectedButton: JQuery;
		
		constructor(private overviewContent: OverviewContent) {
			
		}
		
		draw(jqElem: JQuery) {
			this.jqElem = jqElem;
			var that = this;
			
			var jqState = jqElem.find(".rocket-impl-state:first");
			
			this.jqAllButton = $("<button />", { "type": "button", "class": "btn btn-secondary" }).appendTo(jqState);
			this.jqAllButton.click(function () {
				that.overviewContent.showAll();
				that.reDraw();
			});
			
			this.jqSelectedButton = $("<button />", { "type": "button", "class": "btn btn-secondary" }).appendTo(jqState);
			this.jqSelectedButton.click(function () {
				that.overviewContent.showSelected();
				that.reDraw();
			});
			
			this.reDraw();
			
			this.overviewContent.whenChanged(function () { that.reDraw(); });
			this.overviewContent.selectorState.whenChanged(function () { that.reDraw(); }); 
		}
		
		private reDraw() {
			var numEntries = this.overviewContent.numEntries;
			if (numEntries == 1) {
				this.jqAllButton.text(numEntries + " " + this.jqElem.data("entries-label"));
			} else {
				this.jqAllButton.text(numEntries + " " + this.jqElem.data("entries-plural-label"));
			}
			
			if (this.overviewContent.selectedOnly) {
				this.jqAllButton.removeClass("active");
				this.jqSelectedButton.addClass("active");
			} else {
				this.jqAllButton.addClass("active");
				this.jqSelectedButton.removeClass("active");
			}
			
			var selectorState = this.overviewContent.selectorState;
			
			if (!selectorState.isActive()) {
				this.jqSelectedButton.hide();
				return;
			}
			
			this.jqSelectedButton.show();
			
			var numSelected = numSelected = selectorState.selectorObserver.getSelectedIds().length;
			if (numSelected == 1) {
				this.jqSelectedButton.text(numSelected + " " + this.jqElem.data("selected-label"));
			} else {
				this.jqSelectedButton.text(numSelected + " " + this.jqElem.data("selected-plural-label"));
			}
			
			if (0 == numSelected) {
				this.jqSelectedButton.prop("disabled", true);
				return;
			}
			
			this.jqSelectedButton.prop("disabled", false);			
		}
	}
	
	class QuickSearch {
		constructor(private overviewContent: OverviewContent, jqForm) {
		}	
	}
}