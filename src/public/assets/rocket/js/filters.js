jQuery(document).ready(function($) {
	(function() {
		var jqElemsUl = $(".rocket-filter-items");
		if (jqElemsUl.length === 0) return;
		
		var Filter = function(jqElemUl) {
			this.jqElemUl = jqElemUl;
			this.jqElemDivContainer = null;
			this.jqElemAAddGroup = null;
			this.jqElemUlFilterItems = null;
			this.filterControls = null;
			this.jqElemAAddProp = null;
			this.availableGroups = new Array();
			this.onEmptyCallbacks = new Array();
			this.onNotEmptyCallbacks = new Array();
			this.addIconClassName = jqElemUl.data("add-icon-class-name");
			this.removeIconClassName = jqElemUl.data("remove-icon-class-name");
			this.textRemoveTitle = jqElemUl.data("text-remove-title");
			this.textAddGroup = jqElemUl.data("text-add-group");
			this.textActivateGroup = jqElemUl.data("text-activate-group");
			this.textOr = jqElemUl.data("text-or");
			this.textAnd = jqElemUl.data("text-and");
			
			this.activeGroup = null;
			this.initialize();
		};
		
		Filter.prototype.initialize = function() {
			var _obj = this;
			var filerableProperties = new Array();
			this.jqElemDivContainer = $("<div/>", {
				"class": "rocket-filter-root rocket-filter-group"
			}).insertBefore(this.jqElemUl).append(this.jqElemUl);
			this.jqElemAAddGroup = $("<a/>", {"class": "rocket-control", "href": "#"})
					.append($("<i/>", {"class": this.addIconClassName}))
					.append($("<span/>", {text: this.textAddGroup})).click(function(e) {
						e.preventDefault();
						var group = _obj.requestAvailableGroup();
						_obj.appendGroup(group);
						group.activate();
						_obj.checkAvailableGroups();
					}).insertAfter(this.jqElemUl);
			
			var jqElemUlFilterableProperties = $("<ul/>", {
				"class": "rocket-filterable-properties"
			}).insertAfter(this.jqElemDivContainer);
			
			this.jqElemUl.removeClass().addClass("rocket-filter-groups").children("li.rocket-filterable-property").each(function() {
				var filterableProperty = new FilterableProperty($(this), _obj);
				filerableProperties.push(filterableProperty);
				jqElemUlFilterableProperties.append(filterableProperty.jqElemLi);
			});
			
			var filterItemGroups = new Object()
			this.jqElemUl.find("li.rocket-filter-group-definition-item").each(function() {
				var filterItemGroup = new FilterItemGroup($(this).removeClass().addClass("rocket-filter-group"), _obj);
				filterItemGroups[filterItemGroup.getKey()] = filterItemGroup;
			});
			this.jqElemUl.children(".rocket-filter-group-definition").detach();
			
			for (var i in filterItemGroups) {
				var group = filterItemGroups[i];
				for (var j in filerableProperties) {
					var groupFilterItems = filerableProperties[j].getGroupedFilterItemsForGroupWithKey(group.getKey());
					if (null == groupFilterItems) continue;
					for (var k in groupFilterItems) {
						group.appendFilterItem(groupFilterItems[k])
					}
				}
				if (group.hasParentGroup()) {
					filterItemGroups[group.getParentGroupKey()].appendGroup(group);
				} 
			};
			for (var i in filterItemGroups) {
				var group = filterItemGroups[i];
				if (group.isEmpty()) {
					this.addAvailableGroup(group);
				} else {
					this.applyClickHandler(group);
					this.appendGroup(group);
				}
			};
			
			this.registerOnEmptyCallback(function() {
				_obj.jqElemAAddGroup.hide();
			});
			
			this.registerOnNotEmptyCallback(function() {
				_obj.jqElemAAddGroup.show();
				if (_obj.jqElemUl.children("li").length === 0) {
					_obj.filterControls.jqElemUl.hide();
				} else {
					_obj.filterControls.jqElemUl.show();
				}
			});

			this.jqElemUlFilterItems = $("<ul/>", {"class": "rocket-filter-items rocket-simple-controls"}).insertBefore(this.jqElemUl);
			
			for (var j in filerableProperties) {
				var groupFilterItems = filerableProperties[j].getGroupedFilterItemsForGroupWithKey(new String());
				if (null == groupFilterItems) continue;
				for (var k in groupFilterItems) {
					this.appendFilterItem(groupFilterItems[k]);
				}
			}
			
			this.filterControls = new rocket.Controls();
			this.filterControls.jqElemUl.insertBefore(this.jqElemUlFilterItems);
			this.filterControls.addControl(this.textActivateGroup, function() {
				if (null !== _obj.activeGroup) {
					_obj.activeGroup.jqElemLi.removeClass(_obj.activeClassName);
				}
				_obj.activeGroup = null;
				_obj.jqElemDivContainer.addClass(_obj.activeClassName);
			}, this.addIconClassName).click();
			
			this.checkAvailableGroups();
			
		};
		
		Filter.prototype.activeClassName = 'rocket-active';
		Filter.prototype.inactiveClassName = 'rocket-inactive';
		
		Filter.prototype.requestAvailableGroup = function() {
			var group = this.availableGroups.shift();
			this.applyClickHandler(group);
			this.checkAvailableGroups();
			group.setAnd(true);
			return group;
		};
		
		Filter.prototype.addAvailableGroup = function(filterItemGroup) {
			if (filterItemGroup.jqElemLi.hasClass(this.activeClassName)) {
				this.jqElemUlFilterItems.click();
			}
			filterItemGroup.jqElemLi.detach().off('click.filterItemGroup');
			this.availableGroups.unshift(filterItemGroup);
			this.checkAvailableGroups();
		};
		
		Filter.prototype.applyClickHandler = function(filterItemGroup) {
			var _obj = this;
			filterItemGroup.jqElemAAddProp.on('click.filterItemGroup', function(e) {
				if (null !== _obj.activeGroup) {
					_obj.activeGroup.jqElemLi.removeClass(_obj.activeClassName);
				} else {
					_obj.jqElemDivContainer.removeClass(_obj.activeClassName);
				}
				_obj.activeGroup = filterItemGroup;
				_obj.activeGroup.jqElemLi.addClass(_obj.activeClassName);
				return false;
			});
		};

		Filter.prototype.registerOnEmptyCallback = function(callback) {
			this.onEmptyCallbacks.push(callback);
		};
		
		Filter.prototype.registerOnNotEmptyCallback = function(callback) {
			this.onNotEmptyCallbacks.push(callback);
		};
		
		Filter.prototype.triggerOnEmptyCallbacks = function() {
			for (var i in this.onEmptyCallbacks) {
				this.onEmptyCallbacks[i]();
			};
		};
		
		Filter.prototype.triggerOnNotEmptyCallbacks = function() {
			for (var i in this.onNotEmptyCallbacks) {
				this.onNotEmptyCallbacks[i]();
			}
		};
		
		Filter.prototype.checkAvailableGroups = function() {
			if (this.availableGroups.length == 0) {
				this.triggerOnEmptyCallbacks();
			} else {
				this.triggerOnNotEmptyCallbacks();
			}
		};
		
		Filter.prototype.appendFilterItem = function(filterItem) {
			this.jqElemUlFilterItems.append(filterItem.jqElemLi);
		};
		
		Filter.prototype.appendGroup = function(group) {
			this.jqElemUl.append(group.jqElemLi);
		};
		
		var FilterItemGroup = function(jqElemLi, filter) {
			
			this.jqElemLi = jqElemLi;
			this.jqElemAAddProp = null;
			this.jqElemCheckBoxAndUsed = null;
			this.jqElemInputGroupParentKeys = null;
			this.jqElemAAnd = null;
			this.jqElemAOr = null;
			this.jqElemAAddInnerGroup = null;
			this.jqElemARemove = null;
			this.jqElemUlFilterItems = null
			this.jqElemUlGroups = null;
			
			this.groupControls = null;
			
			this.filter = filter;
			
			this.initialize();
		};
		
		FilterItemGroup.prototype.initialize = function() {
			var _obj = this;
			
			this.groupControls = new rocket.Controls();
			this.groupControls.jqElemUl.appendTo(this.jqElemLi);
			this.jqElemAAddProp = this.groupControls.addControl(this.filter.textActivateGroup, function() {
				//is all done in the applyClickhandler function
			}, this.filter.addIconClassName);
			
			var controlGroupAndOr = new rocket.ControlGroup();
			
			this.jqElemAAnd = controlGroupAndOr.addControl(this.filter.textAnd, function() {
				_obj.setAnd(true);
			});
			
			this.jqElemAOr = controlGroupAndOr.addControl(this.filter.textOr, function() {
				_obj.setAnd(false);
			});
			
			this.groupControls.addControlGroup(controlGroupAndOr); 
			
			this.jqElemARemove = this.groupControls.addControl(this.filter.textRemoveTitle, function() {
				_obj.jqElemLi.trigger('remove');
			}, this.filter.removeIconClassName);
			
			this.jqElemUlFilterItems = $("<ul/>", {"class": "rocket-filter-items rocket-simple-controls"}).appendTo(this.jqElemLi);
			this.jqElemUlGroups = $("<ul/>", {"class": "rocket-filter-groups"}).appendTo(this.jqElemLi);
			
			this.jqElemAAddInnerGroup = $("<a/>", {"class": "rocket-control", "href": "#"})
				.append($("<i/>", {"class": this.filter.addIconClassName}))
				.append($("<span/>", {text: this.filter.textAddGroup})).click(function(e) {
					e.preventDefault();
					var group = _obj.filter.requestAvailableGroup();
					_obj.appendGroup(group);
					group.activate();
				}).appendTo(this.jqElemLi);
			
			this.jqElemLi.on('remove', function(e) {
				e.stopPropagation();
				_obj.jqElemUlFilterItems.children().each(function() {
					$(this).trigger('remove');
				});
				_obj.jqElemUlGroups.children().each(function() {
					$(this).trigger('remove');
				});
				_obj.filter.addAvailableGroup(_obj);
				
			});
			
			this.jqElemCheckBoxAndUsed = this.jqElemLi.children(".rocket-filter-group-used").hide();
			this.jqElemInputGroupParentKeys = this.jqElemLi.children(".rocket-filter-group-parent-keys").hide();
			
			this.checkAnd();
			
			this.filter.registerOnEmptyCallback(function() {
				_obj.jqElemAAddInnerGroup.hide();
			});
			
			this.filter.registerOnNotEmptyCallback(function() {
				_obj.jqElemAAddInnerGroup.show();
			});
		};
		
		FilterItemGroup.prototype.setAnd = function(and) {
			this.jqElemCheckBoxAndUsed.prop("checked", and);
			this.checkAnd();
		};
		
		FilterItemGroup.prototype.isAnd = function() {
			return this.jqElemCheckBoxAndUsed.prop("checked");
		};
		
		FilterItemGroup.prototype.checkAnd = function() {
			if (this.isAnd()) {
				this.jqElemAAnd.addClass(this.filter.activeClassName).removeClass(this.filter.inactiveClassName);
				this.jqElemAOr.removeClass(this.filter.activeClassName).addClass(this.filter.inactiveClassName);
			} else {
				this.jqElemAAnd.removeClass(this.filter.activeClassName).addClass(this.filter.inactiveClassName);
				this.jqElemAOr.addClass(this.filter.activeClassName).removeClass(this.filter.inactiveClassName);
			}
		};
		
		FilterItemGroup.prototype.isEmpty = function() {
			return (0 === (this.jqElemUlGroups.children().length + this.jqElemUlFilterItems.children().length));
		}; 
		
		FilterItemGroup.prototype.appendGroup = function(filterItemGroup) {
			filterItemGroup.jqElemInputGroupParentKeys.val(this.getKey());
			this.jqElemUlGroups.append(filterItemGroup.jqElemLi);
		};
		
		FilterItemGroup.prototype.appendFilterItem = function(filterItem) {
			filterItem.jqElemInputGroupKey.val(this.getKey());
			this.jqElemUlFilterItems.append(filterItem.jqElemLi);
		};
		
		FilterItemGroup.prototype.hasParentGroup = function() {
			return (this.getParentGroupKey().length > 0);
		};
		
		FilterItemGroup.prototype.getParentGroupKey = function() {
			return this.jqElemInputGroupParentKeys.val();
		};
		
		FilterItemGroup.prototype.getKey = function() {
			return this.jqElemLi.data("key");
		};
		
		FilterItemGroup.prototype.activate = function() {
			return this.jqElemAAddProp.click();
		};
		
		var FilterableProperty = function(jqElemLi, filter) {
			this.jqElemLi = jqElemLi;
			this.jqElemAAddFilterItem = null;
			this.filter = filter;
			this.availableFilterItems = new Array();
			this.groupedFilterItems = new Object();
			this.name = null;
			this.initialize();
		};
		
		FilterableProperty.prototype.initialize = function() {
			var _obj = this;
			var jqElemLabel = this.jqElemLi.children("label:first").hide();
			this.name =  jqElemLabel.text();
			this.jqElemAAddFilterItem = $("<a/>", {"class": "rocket-control", "href": "#"})
					.append($("<i/>", {"class": this.filter.addIconClassName}))
					.append($("<span/>", {text: this.name})).click(function(e) {
						e.preventDefault();
						if (_obj.filter.activeGroup === null) {
							_obj.filter.appendFilterItem(_obj.requestAvailableFilterItem());
						} else {
							_obj.filter.activeGroup.appendFilterItem(_obj.requestAvailableFilterItem());
						}
					}).appendTo(this.jqElemLi);
			
			this.jqElemLi.children("ul:first").children().each(function() {
				var filterItem = new FilterItem($(this), _obj);
				if (filterItem.isUsed()) {
					var groupKey = filterItem.getGroupKey();
					if (null == _obj.groupedFilterItems[groupKey]) {
						_obj.groupedFilterItems[groupKey] = new Array();
					}
					_obj.groupedFilterItems[groupKey].push(filterItem);
				} else {
					_obj.addAvailableFilterItem(filterItem);
				}
			});
			
		};
		
		FilterableProperty.prototype.requestAvailableFilterItem = function() {
			if (this.availableFilterItems.length === 1) {
				this.jqElemAAddFilterItem.hide();
			}
			filterItem = this.availableFilterItems.shift();
			filterItem.jqElemInputUsed.prop("checked", true);
			return filterItem;
		};
		
		FilterableProperty.prototype.addAvailableFilterItem = function(filterItem) {
			filterItem.jqElemLi.detach();
			if (this.availableFilterItems.length === 0) {
				this.jqElemAAddFilterItem.show();
			}
			this.availableFilterItems.unshift(filterItem);
		};
		
		FilterableProperty.prototype.getGroupedFilterItemsForGroupWithKey = function(groupKey) {
			return this.groupedFilterItems[groupKey];
		};
		
		var FilterItem = function(jqElemLi, filterableProperty) {
			this.jqElemLi = jqElemLi;
			this.jqElemARemove = null
			this.jqElemInputUsed = null;
			this.jqElemInputGroupKey = null;
			this.filterableProperty = filterableProperty;
			this.initialize();
		};
		
		FilterItem.prototype.initialize = function() {
			var _obj = this;
			this.jqElemLi.on('remove', function(e) {
				e.stopPropagation();
				_obj.filterableProperty.addAvailableFilterItem(_obj);
			}).addClass("rocket-filter-item");
			
			this.jqElemLi.prepend($("<h4/>", {text: _obj.filterableProperty.name}));
			this.jqElemARemove = $('<a/>', {"class": "rocket-control rocket-control-danger", href: "#", title: this.filterableProperty.filter.textRemoveTitle})
				.append($("<i/>", {"class": this.filterableProperty.filter.removeIconClassName})).click(function(e) {
					e.preventDefault();
					_obj.jqElemLi.trigger('remove');
				}).appendTo(this.jqElemLi);
			
			this.jqElemInputUsed = this.jqElemLi.children(".rocket-filter-item-used").hide();
			this.jqElemInputGroupKey = this.jqElemLi.children(".rocket-filter-item-group-key").hide();
		};
		
		FilterItem.prototype.isUsed = function() {
			return this.jqElemInputUsed.prop("checked");
		}
		
		FilterItem.prototype.getGroupKey = function() {
			return this.jqElemInputGroupKey.val();
		};
		jqElemsUl.each(function() {
			new Filter($(this));
		});
	})();
});