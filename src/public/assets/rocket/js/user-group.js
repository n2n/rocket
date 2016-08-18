jQuery(document).ready(function($) {
	(function() {
		var jqElemUserGroupMenuItems = $(".rocket-user-group-menu-items");
		if (jqElemUserGroupMenuItems.length === 0) return;
		(function() {
			var UserGroupMenuItem = function(menuItems, jqElem) {
				this.jqElem = jqElem;
				this.menuItems = menuItems;
				this.jqElemCbx = jqElem.find("input[type=checkbox]:first").hide();
				this.jqElemAAssign = null;
				this.jqElemAUnassign = null;
				(function(_obj) {
					var controls = new rocket.Controls('simple');
					this.jqElemAAssign = controls.addControl(menuItems.textAssign, function() {
						_obj.assign();
					}, "fa fa-plus-circle rocket-control-success");
					this.jqElemAUnassign = controls.addControl(menuItems.textUnassign, function() {
						_obj.unassign();
					}, "fa fa-minus-circle rocket-control-danger");
					this.jqElem.prepend(controls.jqElemUl);
					if (this.isAssigned()) {
						this.assign();
					} else {
						this.unassign();
					}
				}).call(this, this);
			};
			
			UserGroupMenuItem.prototype.isAssigned = function() {
				return this.jqElemCbx.is(":checked");
			};
			
			UserGroupMenuItem.prototype.assign = function() {
				this.jqElemCbx.prop("checked", true);
				this.jqElemAAssign.hide();
				this.jqElemAUnassign.show();
				this.jqElem.appendTo(this.menuItems.jqElemUlAssignedItems);
				this.menuItems.checkEnable();
			};

			UserGroupMenuItem.prototype.unassign = function() {
				this.jqElemCbx.prop("checked", false);
				this.jqElemAAssign.show();
				this.jqElemAUnassign.hide();
				this.jqElem.appendTo(this.menuItems.jqElemUlUnassignedItems);
				this.menuItems.checkEnable();
			};
			
			var UserGroupMenuItems = function(jqElem) {
				this.jqElem = jqElem;
				this.jqElemCbxEnable = jqElem.children("input[type=checkbox]:first");
				this.jqElemUlItems = jqElem.children("ul:first");
				this.jqElemDivItemAssignator = null;
				this.jqElemUlAssignedItems = null;
				this.jqElemUlUnassignedItems = null;
				this.textAssign = jqElem.data("assign-title");
				this.textUnassign = jqElem.data("unassign-title");
				(function(_obj) {
					jqElem.children().hide();
					this.jqElemDivItemAssignator = $("<div/>", {"class": "rocket-item-assignator"}).appendTo(jqElem);
					this.jqElemUlAssignedItems = $("<ul/>", {
						"class": "rocket-item-assignation-list"
					}).appendTo($("<section/>").append($("<h1/>", {
						"text": jqElem.data("accessable-items-title")
					})).appendTo(this.jqElemDivItemAssignator));
					this.jqElemUlUnassignedItems = $("<ul/>", {
						"class": "rocket-item-assignation-list"
					}).appendTo($("<section/>").append($("<h1/>", {
						"text": jqElem.data("unaccessable-items-title")
					})).appendTo(this.jqElemDivItemAssignator));
					this.jqElemUlItems.children().each(function() {
						new UserGroupMenuItem(_obj, $(this));
					});
				}).call(this, this);
			};
			
			UserGroupMenuItems.prototype.checkEnable = function() {
				this.jqElemCbxEnable.prop("checked", (this.jqElemUlAssignedItems.children().length > 0));
			};
			new UserGroupMenuItems(jqElemUserGroupMenuItems);
		})();
	})();
});