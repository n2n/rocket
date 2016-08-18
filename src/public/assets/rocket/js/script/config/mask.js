jQuery(document).ready(function($) {
	(function() {
		var jqElemDivFieldOrder = $("#rocket-script-mask-fields");
		if (jqElemDivFieldOrder.length == 0) return;
		
		var ScriptFieldListEntry = function(id, label, clickCallback) {
			this.id = id;
			this.jqElemLi = $("<li/>");
			(function(_obj) {
				_obj.jqElemLi.append($("<a/>").append($("<i/>", {
					"class": "fa fa-plus"
				}).text(label).click(function(e) {
					e.preventDefault();
					clickCallback(_obj);
				})));
			}).call(this, this);
		};
		
		var ScriptFieldList = function() {
			this.jqElemUl = $("<ul/>");
		};
		
		ScriptFieldList.prototype.addField = function(entry) {
			this.jqElemUl.append(entry.jqElemLi);
		};
		
		var MaskScriptField = function(jqElemLi) {
			this.jqElemLi = jqElemLi;
			this.jqElemARemove = $("<a/>").append($("<i/>", {"class": "fa fa-icon-times"}))
					.text();
			this.jqElemInputFieldId = jqElemLi.children(".rocket-mask-field-id");
			this.jqElemInputGroupKey = jqElemLi.children(".rocket-mask-group-key");
		};
		
		MaskScriptField.prototype.setGroup = function(group) {
		};
		
		var MaskScriptFieldGroup = function(jqElemLi) {
			this.jqElemLi = jqElemLi;
			this.key = jqElemLi.data("key");
			this.jqElemInputTitle = jqElemLi.children(".rocket-mask-group-title");
			this.jqElemSelectType = jqElemLi.children(".rocket-mask-group-type");
			this.jqElemParentKey = jqElemLi.children(".rocket-mask-group-parent-key");
		};
		
	})();
});