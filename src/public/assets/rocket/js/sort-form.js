jQuery(document).ready(function($) {
	(function() {
		var jqSortLists = $("ul.rocket-sort-items");
		if (jqSortLists.length === 0) return;

		function SortList(jqSortList) {
			this.jqSortList = jqSortList;
			this.jqAddButton = $("<a/>", {"type": "button", "class": "rocket-control"})
				.append($("<i/>", {"class": "fa fa-plus-circle"}))
				.append($("<span/>", {"text": jqSortList.data("add-sort-label")}));
			this.jqItemStruct = jqSortList.children().last().detach();
			
			jqSortList.after($("<span/>").append(this.jqAddButton));
			
			(function(_obj) {
				jqSortList.find(".rocket-filter-sort-field select").each(function() {
					$(this).children().first().detach();
				});
				jqSortList.children().each(function() {
					_obj.initElement($(this));
				});
				this.jqAddButton.click(function() {
					_obj.extend();
				});
				this.extend();
			}).call(this, this);
		};
		
		SortList.prototype.initElement = function(jqElem) {		
			var jqDeleteButton = $("<a/>", {"type": "button", "class": "rocket-control rocket-control-danger"}); 
			jqDeleteButton.append($("<i/>", {"class": "fa fa-times"}));
			jqElem.append($("<span/>").append(jqDeleteButton));
			jqDeleteButton.click(function() {
				jqElem.remove();
			});
		}
		
		SortList.prototype.extend = function() {
			var jqItem = this.jqItemStruct.clone();
			this.jqSortList.append(jqItem);
			this.initElement(jqItem);
		};
		
		jqSortLists.each(function() {
			new SortList($(this));
		});
	})();
});