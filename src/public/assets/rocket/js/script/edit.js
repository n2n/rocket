jQuery(document).ready(function($) {
	"use strict";
	(function() {
		var jqElemsSelectScriptType = $("select.rocket-script-type-selection");
		if (jqElemsSelectScriptType.length === 0) return;

		var RefreshSelectOption = function(jqElemOption) {
			this.value = jqElemOption.val();
			this.dependentElements = new Array();
		};

		RefreshSelectOption.prototype.equals = function(option) {
			return option.value === this.value;
		};

		RefreshSelectOption.prototype.deactivate = function() {
			for (var i in this.dependentElements) {
				this.dependentElements[i].detach();
			}
		};

		RefreshSelectOption.prototype.activate = function(jqElemContainer) {
			var elemsToInitialize = [];
			$.each(this.dependentElements, function(index, jqElem) {
				var callback = function() {
					jqElem.appendTo(jqElemContainer);
				};
				if (typeof window.Wysiwyg === 'undefined') {
					callback();
				} else {
					Wysiwyg.ckHack(jqElem, callback);
				}
				elemsToInitialize.push(jqElem);
			});
			for (var i in elemsToInitialize) {
				rocket.core.contentInitializer.initElement(elemsToInitialize[i])
			}
		};

		RefreshSelectOption.prototype.addDependentElement = function(jqElem) {
			this.dependentElements.push(jqElem);
		};

		var RefreshSelect = function(jqElemSelect) {
			this.currentOption = null;
			this.options = new Object();
			this.jqElemContainer = jqElemSelect.parents(".rocket-type-dependent-entry-form:first");
			(function(_obj) {
				jqElemSelect.children("option").each(function() {
					var option = new RefreshSelectOption($(this));
					_obj.jqElemContainer.children(".rocket-script-type-" + option.value).each(function() {
						option.addDependentElement($(this));
					});
					_obj.options[option.value] = option;
				});
				jqElemSelect.change(function() {
					var currentValue = jqElemSelect.val();
					if (null !== _obj.currentOption) {
						_obj.currentOption.deactivate();
					} else {
						for (var i in _obj.options) {
							if (i === currentValue) continue;
							_obj.options[i].deactivate();
						}
					}
					if (_obj.options.hasOwnProperty(currentValue)) {
						_obj.currentOption = _obj.options[currentValue];
						_obj.currentOption.activate(_obj.jqElemContainer);
					} else {
						_obj.currentOption = null;
					}
				});
				jqElemSelect.change();
			}).call(this, this);

		};
		jqElemsSelectScriptType.each(function() {
			new RefreshSelect($(this));
		}).change();
	})();
	
	(function(){
		//Critical Script Field
		var jqElemsCriticalScriptFields = $(".rocket-critical-input");
		if (jqElemsCriticalScriptFields.length == 0) return;
		
		var CriticalInput = function(jqElem) {
			var _obj = this;
			this.jqElem = jqElem;
			this.jqElemLockedContainer = $("<div/>", 
					{"class": "rocket-critical-input-locked-container"}).insertAfter(this.jqElem);
			
			var alternateText = this.jqElem.val();
			if (this.jqElem.is("select")) {
				var jqElemOption = jqElem.find("option[value='" + alternateText + "']");
				if (jqElemOption.length > 0) {
					alternateText = jqElemOption.text();
				}
			} 
			
			this.jqElemAlternateText = $("<span/>", {text: alternateText}).appendTo(this.jqElemLockedContainer);
			this.dialog = null;
			
			this.jqElemUnlock = $("<button/>", {
				"class": "rocket-critical-input-unlock rocket-control"
			}).append($("<i/>", {"class": jqElem.data("icon-unlock") || "fa fa-pencil"}))
			.appendTo(this.jqElemLockedContainer);
		
			jqElem.hide();
			
			if (jqElem.data("confirm-message")) {
				this.initializeDialog(jqElem.data("confirm-message"), jqElem.data("edit-label"), jqElem.data("cancel-label"));
				this.jqElemUnlock.click(function(e) {
					e.preventDefault();
					rocket.core.stressWindow.open(_obj.dialog);
					return false;
				});
			} else {
				this.jqElemUnlock.click(function(e) {
					e.preventDefault();
					_obj.showInput();
					return false;
				});
			}
		};
		
		CriticalInput.prototype.initializeDialog = function(message, editLabel, cancelLabel) {
			var _obj = this;
			this.dialog = new rocket.Dialog(message);
			this.dialog.addButton(editLabel, function() {
				_obj.showInput();
				_obj.jqElemUnlock.remove();
			});
			
			this.dialog.addButton(cancelLabel, function() {
				//defaultbehaviour is to close the dialog
			});
		}
		
		CriticalInput.prototype.showInput = function() {
			this.jqElemLockedContainer.hide();
			this.jqElem.show();
		};
		
		jqElemsCriticalScriptFields.each(function() {
			new CriticalInput($(this));
		});
	})();
	
	(function() {
		// Relational Script Fields
		/**
		 * class Item
		 */
		var Item = function(jqElem, label, showLabel) {
			showLabel = showLabel || false;
			this.jqElem = jqElem;
			
			this.jqElemSelectItemType = null;
			this.jqElemRocketEnabler = null;
			this.controls = null;
			this.jqElemLabel = null;
			
			(function(_obj) {
				//initialization
				this.jqElemSelectItemType = this.jqElem.find("> .rocket-type-dependent-entry-form > .rocket-script-type-selector select.rocket-script-type-selection");
				this.jqElemRocketEnabler = jqElem.find(".rocket-object-enabler:first").hide();
				this.initialized = this.jqElemRocketEnabler.prop("checked");
				if (!showLabel) {
					this.jqElemRocketEnabler.next("label").hide()
					this.jqElemLabel = $("<label/>", {
						text: label
					}).prependTo(this.jqElem);
				}
				this.controls = new rocket.Controls("simple");
				this.jqElem.prepend(this.controls.jqElemUl);
				
			}).call(this, this);
		};
	
		Item.prototype.reset = function() {
			this.jqElem.find("input, textarea, select").each(function() {
				var jqElem = $(this);
				if (this.defaultValue != undefined) {
					this.value = this.defaultValue;
				} else if (jqElem.hasClass("rocket-script-type-selection")) {
					jqElem.val(jqElem.children("option:first").val());
				}
			});
			this.jqElem.detach();
			this.jqElemRocketEnabler.prop("checked", false);
		};
		
		Item.prototype.initialize = function() {
			if (!this.initialized) {
				rocket.core.contentInitializer.initElement(this.jqElem);
				this.initialized = true;
			}
			this.jqElemRocketEnabler.prop("checked", true);
			this.jqElem.show();
		};
		
		Item.prototype.ckHack = function(callback) {
			if (typeof Wysiwyg === 'undefined') {
				callback();
				return;
			}
			Wysiwyg.ckHack(this.jqElem, callback);
		};
		
		Item.prototype.getTypeName = function() {
			if (this.jqElemSelectItemType.length > 0) {
				return this.jqElemSelectItemType.children(":selected").text();
			}
			if (this.jqElem.data("type-name")) {
				return this.jqElem.data("type-name");
			}
			return this.item.jqElemLabel.text();
		}
		/**
		 * class AvailableItemManager
		 */
		var AvailableItemManager = function() {
			this.items = new Array();
			
			this.onEmptyCallbacks = new Array();
			this.onNotEmptyCallbacks = new Array();
		};
		
		AvailableItemManager.prototype.add = function(item) {
			this.items.push(item);
			item.reset();
			if (this.items.length === 1) {
				this.triggerOnNotEmtpyCallbacks();
			}
		};
		
		AvailableItemManager.prototype.request = function() {
			if (this.items.length === 0) return null;
			var item = this.items.pop();
			if (!item.initialized) {
				item.initialize();
			}
			if (this.items.length === 0) {
				this.triggerOnEmtpyCallbacks();
			}
			return item;
		};
		
		AvailableItemManager.prototype.triggerOnNotEmtpyCallbacks = function() {
			for (var i in this.onNotEmptyCallbacks) {
				this.onNotEmptyCallbacks[i].call();
			}
		};
		
		AvailableItemManager.prototype.triggerOnEmtpyCallbacks = function() {
			for (var i in this.onEmptyCallbacks) {
				this.onEmptyCallbacks[i].call();
			}
		};
		
		AvailableItemManager.prototype.onEmpty = function(onEmptyCallback) {
			this.onEmptyCallbacks.push(onEmptyCallback)
			if (this.items.length === 0) {
				onEmptyCallback();
			}
		};
		
		AvailableItemManager.prototype.onNotEmpty = function(onNotEmptyCallback) {
			this.onNotEmptyCallbacks.push(onNotEmptyCallback);
			if (this.items.length > 0) {
				onNotEmptyCallback();
			}
		};
		
		AvailableItemManager.prototype.isEmpty = function() {
			return this.items.length === 0;
		};
		/**
		 * class ItemAddButton
		 */
		var ItemAddButton = function(btnText, aim, clickCallback) {
			this.jqElemBtn = null;
			this.aim = aim;
			this.clickCallback = clickCallback;
			
			(function(_obj) {
				this.createButton(btnText);
				this.aim.onEmpty(function() {
					_obj.jqElemBtn.hide();
				});
				
				this.aim.onNotEmpty(function() {
					_obj.jqElemBtn.show();
				});
				this.registerClickListener();
			}).call(this, this);
		};
		
		ItemAddButton.prototype.createButton = function(btnText) {
			this.jqElemBtn = $("<button/>", {
				"type": "button", 
				"text": btnText, 
				"class": "rocket-control rocket-control-full"
			}).prepend($("<i/>", {
				"class": "fa fa-plus"
			}));
		};
		
		ItemAddButton.prototype.registerClickListener = function() {
			var _obj = this;
			this.jqElemBtn.off('click.btn-add').on('click.btn-add', function(event) {
				event.preventDefault();
				var item = _obj.aim.request();
				if (null === item) return;
				_obj.clickCallback(item);
			});
		};
		
		var ItemArray = function(jqElemUl) {
			this.jqElemUl = jqElemUl;
			this.itemAddButton = null;
			this.aim = new AvailableItemManager();
			this.itemLabel = jqElemUl.parents("li.rocket-control-group:first").data("target-label");
			
			(function(_obj) {
				var itemsToAdd = new Array();
				this.jqElemUl.children("li.rocket-new").each(function() {
					var item = new Item($(this), _obj.itemLabel);
					item.controls.addControl("Remove", function() {
						_obj.aim.add(item);
					}, "fa fa-times rocket-control-danger");
					if (!item.initialized) {
						itemsToAdd.push(item);
					}
				});
				this.jqElemUl.children("li.rocket-current").each(function() {
					var item = new Item($(this), _obj.itemLabel)
					item.controls.addControl("Remove", function() {
						item.jqElem.remove();
					}, "fa fa-times rocket-control-danger");
				});
				this.itemAddButton = new ItemAddButton(jqElemUl.data("text-add-item"), this.aim, function(item) {
					item.ckHack(function() {
						_obj.jqElemUl.append(item.jqElem.show());
					});
				});
				this.jqElemUl.after(this.itemAddButton.jqElemBtn);
				itemsToAdd.reverse();
				for (var i in itemsToAdd) {
					this.aim.add(itemsToAdd[i]);
				}
			}).call(this, this);
		};
		
		var ItemAssignationList = function(title, inputFilterPlaceholder) {
			this.jqElemSection = $("<section/>").append($("<h1/>", {text: title}));
			this.jqElemInputFilter = $("<input/>", {"class": "rocket-item-assignation-list-filter", placeholder: inputFilterPlaceholder}).appendTo(this.jqElemSection).hide();
			this.jqElemUl = $("<ul/>", {"class": "rocket-item-assignation-list"}).appendTo(this.jqElemSection);
			(function(_obj) {
				this.jqElemInputFilter.keyup(function() {
					var regexp = new RegExp($(this).val(), "i");
					_obj.jqElemUl.children("li").each(function() {
						var jqElem = $(this);
						if (jqElem.text().match(regexp)) {
							jqElem.show();
						} else {
							jqElem.hide();
						}
					});
				});
				this.jqElemUl.on('tomany.listchanged', function() {
					var childNum = _obj.jqElemUl.children("li").length;
					if (childNum > 10) {
						_obj.showInputFilter();
					} else {
						_obj.hideInputFilter();
					}
					
					if (childNum > 5) {
						_obj.jqElemUl.addClass("rocket-item-assignation-list-compressed");
					} else {
						_obj.jqElemUl.removeClass("rocket-item-assignation-list-compressed");
					}
				});
			}).call(this, this)
		};
		
		ItemAssignationList.prototype.showInputFilter = function() {
			if (this.jqElemInputFilter.is(":visible")) return;
			this.jqElemInputFilter.show();
		};
		
		ItemAssignationList.prototype.hideInputFilter = function() {
			if (!this.jqElemInputFilter.is(":visible")) return;
			this.jqElemInputFilter.val("");
			this.jqElemInputFilter.hide();
			this.jqElemUl.children("li").show();
		};
		
		var ItemAssignator = function(jqElemUl) {
			if (jqElemUl.length === null) return;
			this.assignedItemsList = new ItemAssignationList(jqElemUl.data("assigned-items-title"), 
					jqElemUl.data("input-filter-placeholder"));
			this.unassignedItemsList = new ItemAssignationList(jqElemUl.data("unassigned-items-tile"), 
					jqElemUl.data("input-filter-placeholder"));
			this.itemLabel = jqElemUl.parents("li.rocket-control-group:first").data("target-label");
			
			(function(_obj){
				var assignTitle = jqElemUl.data("assign-title"), unassignTitle = jqElemUl.data("unassign-title"), assignator;
				assignator = $("<div/>", {
					"class": "rocket-item-assignator"
				}).insertAfter(jqElemUl);
				this.assignedItemsList.jqElemSection.appendTo(assignator);
				this.unassignedItemsList.jqElemSection.appendTo(assignator);
				
				jqElemUl.children("li").each(function() {
					var item = new Item($(this), _obj.itemLabel, true), assignButton, unassignButton;
					
					assignButton = item.controls.addControl(assignTitle, function() {
						assignButton.hide();
						item.jqElem.appendTo(_obj.assignedItemsList.jqElemUl);
						item.jqElemRocketEnabler.prop("checked", true);
						unassignButton.show();
						_obj.assignedItemsList.jqElemUl.trigger("tomany.listchanged");
						_obj.unassignedItemsList.jqElemUl.trigger("tomany.listchanged");
					}, "fa fa-plus-circle rocket-control-success");
					
					unassignButton = item.controls.addControl(unassignTitle, function() {
						unassignButton.hide();
						item.jqElem.appendTo(_obj.unassignedItemsList.jqElemUl);
						item.jqElemRocketEnabler.prop("checked", false);
						assignButton.show();
						_obj.assignedItemsList.jqElemUl.trigger("tomany.listchanged");
						_obj.unassignedItemsList.jqElemUl.trigger("tomany.listchanged");
					}, "fa fa-minus-circle rocket-control-danger");
					
					if (item.jqElemRocketEnabler.prop("checked")) {
						item.jqElem.appendTo(_obj.assignedItemsList.jqElemUl);
						assignButton.hide();
					} else {
						item.jqElem.appendTo(_obj.unassignedItemsList.jqElemUl);
						unassignButton.hide();
					}
				});
				this.assignedItemsList.jqElemUl.trigger("tomany.listchanged");
				this.unassignedItemsList.jqElemUl.trigger("tomany.listchanged");
			}).call(this, this);
		};
		
		var ContentItem = function(item, frozen, aim) {
			this.item = item;
			this.contentItemAddButton = null;
			this.frozen = frozen;
			this.aim = aim;
			this.panel = null;
			this.jqElemHeading = null;
			if (frozen) {
				this.frozenPanelName = null;
				this.frozenOrderIndex = null;
			} else {
				this.jqElemInputPanel = null;
				this.jqElemInputOrderIndex = null;
			}
			
			(function(_obj) {
				var jqElemPanelControl = item.jqElem.find(".rocket-properties:first > .rocket-field-panel").hide();
				var jqElemOrderIndexControl = item.jqElem.find(".rocket-properties:first > .rocket-field-orderIndex").hide(); 
				if (frozen) {
					this.frozenPanelName = $.trim(jqElemPanelControl.find(".rocket-controls:first").text());
					this.frozenOrderIndex = jqElemOrderIndexControl.find(".rocket-controls:first").text();
				} else {
					this.jqElemInputPanel = jqElemPanelControl.find("input:first");
					this.jqElemInputOrderIndex = jqElemOrderIndexControl.find("input:first");
				}
				item.jqElemSelectItemType.parents(".rocket-script-type-selector").hide();
				this.jqElemHeader = $("<div/>", {"class": "rocket-content-item-header"}).css({
					"position": "relative"
				}).prependTo(this.item.jqElem).append(this.item.jqElemLabel)
						.append(this.item.jqElem.find(".rocket-simple-controls:first"));
			}).call(this, this);
		};
		
		ContentItem.prototype.setType = function(itemType) {
			if (this.item.jqElemSelectItemType.length === 0) return;
			this.item.jqElemSelectItemType.val(itemType);
			this.item.jqElemSelectItemType.change();
		};
		
		ContentItem.prototype.getType = function() {
			if (this.item.jqElemSelectItemType.length === 0) return null;
			return this.item.jqElemSelectItemType.val();
		};
		
		ContentItem.prototype.initialize = function() {
			this.item.initialize();
		};
		
		ContentItem.prototype.reset = function() {
			this.item.reset();
			if (null !== this.panel) {
				this.panel = null;
				this.contentItemAddButton.jqElemContainer.remove();
			}
		};
		
		ContentItem.prototype.getPanelName = function() {
			if (this.frozen) return this.frozenPanelName
			return this.jqElemInputPanel.val();
		};
		
		ContentItem.prototype.setPanelName = function(panelName) {
			if (this.frozen) return;
			this.jqElemInputPanel.val(panelName);
		};
		
		ContentItem.prototype.getOrderIndex = function() {
			if (this.frozen) return this.frozenOrderIndex;
			return this.jqElemInputOrderIndex.val();
		};
		
		ContentItem.prototype.setPanel = function(panel) {
			this.panel = panel;
			if (!this.frozen) {
				var _obj = this;
				this.setPanelName(panel.name);
				this.contentItemAddButton = new ContentItemAddButton(panel, panel.getTextAdd(), function(contentItem) {
					contentItem.item.ckHack(function() {
						panel.appendContentItem(contentItem);
						contentItem.item.jqElem.insertBefore(_obj.item.jqElem);
						contentItem.item.jqElem.trigger(panel.contentItemOption.contentItemChangeEventName);
					});
				});
				this.contentItemAddButton.jqElemContainer.prependTo(this.item.jqElem);
			}
		}
		
		var ContentItemAddButton = function(panel, btnText, clickCallback) {
			this.panel = panel;
			this.jqElemBtn = null;
			this.jqElemUl = null;
			this.jqElemContainer = null;
			this.clickCallback = clickCallback;
			
			(function(_obj) {
				this.jqElemContainer = $("<div/>", {
					"class": "rocket-content-item-panel-insert-ci"
				});
				ItemAddButton.prototype.createButton.call(this, btnText);
				this.initializeItemTypes();
				
				panel.aim.onEmpty(function() {
					_obj.jqElemBtn.addClass(_obj.disabledClassName).removeClass(_obj.buttonOpenClassName);
					_obj.jqElemUl.hide().removeClass(_obj.menuOpenClassName);
				});
				
				panel.aim.onNotEmpty(function() {
					_obj.jqElemBtn.removeClass(_obj.disabledClassName);
				});
			}).call(this, this)
		};
		
		ContentItemAddButton.prototype.disabledClassName = "rocket-control-disabled";
		ContentItemAddButton.prototype.menuOpenClassName = "rocket-dd-menu-open";
		ContentItemAddButton.prototype.buttonOpenClassName = "rocket-command-insert-ci-open";
		
		ContentItemAddButton.prototype.initializeItemTypes = function() {
			var _obj = this;
			this.jqElemBtn.off('click.btn-add').on('click.btn-add', function() {
				if (_obj.jqElemBtn.hasClass(_obj.disabledClassName)) return;
				if (_obj.jqElemUl.hasClass(_obj.menuOpenClassName)) {
					_obj.jqElemUl.hide().removeClass(_obj.menuOpenClassName);
					_obj.jqElemBtn.removeClass(_obj.buttonOpenClassName);
				} else {
					_obj.refreshContentItems();
					_obj.jqElemUl.addClass(_obj.menuOpenClassName).show();
					_obj.jqElemBtn.addClass(_obj.buttonOpenClassName);
				}
			});
			
			if (null === this.jqElemUl) {
				this.jqElemUl = $("<ul/>").hide();
				this.jqElemContainer.append(this.jqElemBtn).append(this.jqElemUl);
			} else {
				this.jqElemUl.empty().hide();
			}
		};
		
		ContentItemAddButton.prototype.refreshContentItems = function() {
			var _obj = this;
			this.jqElemUl.empty();
			for (var i in this.panel.contentItemTypes) {
				var itemType = this.panel.contentItemTypes[i];
				var text = this.panel.contentItemTypes[i];
				if (null !== this.panel.contentItemOption.availableContentItemLabels && 
						null != this.panel.contentItemOption.availableContentItemLabels[itemType]) {
					text = this.panel.contentItemOption.availableContentItemLabels[itemType];
				}
				var jqAContentItemType = $("<a/>", {text: text, href: "#"}).click(function(e) {
					e.preventDefault();
					var contentItem = _obj.panel.aim.request();
					contentItem.setType($(this).data("item-type"));
					_obj.clickCallback(contentItem);
					_obj.jqElemBtn.click();
				}).data("item-type", itemType).appendTo($("<li/>").appendTo(this.jqElemUl));
			}
		}
		
		var ContentItemPanel = function(name, label, contentItemTypes, contentItemOption) {
			this.contentItemOption = contentItemOption;
			this.contentItemTypes = contentItemTypes;
			this.aim = contentItemOption.aim;
			this.name = name;
			
			this.jqElemLiContainer = null;
			this.jqElemUl = null;
			this.jqElemLabel = null;
			this.contentItemAddButton = null;
			
			(function(_obj) {
				this.jqElemLabel = $("<label/>", {
					"text": label
				});
				this.jqElemLiContainer = $("<li/>").addClass("rocket-content-item-panel rocket-controls").append(this.jqElemLabel);
				
				this.jqElemUl = $("<ul/>").addClass("rocket-content-items rocket-option-array").appendTo(this.jqElemLiContainer);
				if (!this.aim.isEmpty()) {
					this.contentItemAddButton = new ContentItemAddButton(this, this.getTextAdd(), function(contentItem) {
						contentItem.item.ckHack(function() {
							_obj.appendContentItem(contentItem);
						});
					});
					this.contentItemAddButton.jqElemContainer.appendTo(this.jqElemLiContainer);
				}
			}).call(this, this)
		};
		
		ContentItemPanel.prototype.getTextAdd = function() {
			return this.contentItemOption.textAddItem;
		};
		
		ContentItemPanel.prototype.appendContentItem = function(contentItem) {
			var _obj = this, typeName = contentItem.item.getTypeName();
			this.jqElemUl.append(contentItem.item.jqElem);
			contentItem.setPanel(this);
			contentItem.item.jqElem
					.off(this.contentItemOption.contentItemChangeEventName + '.panel')
					.on(this.contentItemOption.contentItemChangeEventName + '.panel', function() {
						_obj.updateOrderIndexes();
					});
			if (typeName) {
				contentItem.item.jqElemLabel.text(typeName);
			}
			this.updateOrderIndexes();
		}
		
		ContentItemPanel.prototype.updateOrderIndexes = function() {
			this.jqElemUl.children("li").each(function(index) {
				$(this).find(".rocket-properties:first > .rocket-field-orderIndex input:first").val(index);
			});
		};
		
		ContentItemPanel.prototype.setLabel = function(label) {
			this.jqElemLabel.text(label);
		};
		
		ContentItemPanel.prototype.getLabel = function() {
			return this.jqElemLabel.text();
		};
		
		ContentItemPanel.prototype.remove = function() {
			this.jqElemLiContainer.find(".rocket-content-item-remove").click();
			this.jqElemLiContainer.detach();
		}
		
		rocket.state.ContentItemPanel = ContentItemPanel;
		
		var ContentItemOption = function(jqElemLiContentItemOption, jqElemDivOptionArray) {
			this.aim = new AvailableItemManager();
			this.jqElemLi = jqElemLiContentItemOption;
			this.jqElemUl = jqElemDivOptionArray.children("ul:first");
			this.textAddItem = this.jqElemUl.data("text-add-item");
			this.textRemove = this.jqElemUl.data("text-remove");
			this.textUp = this.jqElemUl.data("text-up");
			this.textDown = this.jqElemUl.data("text-down");
			this.frozen = jqElemLiContentItemOption.data("frozen") || false;
			this.panels = [];
			this.availableContentItemTypes = null;
			this.availableContentItemLabels = null;
			this.itemLabel = jqElemLiContentItemOption.data("target-label");
			this.unAssignedItems = [];
			
			(function(_obj) {
				var itemsToAdd = new Array();
				var itemsToAssign = new Object();
				var panelOptions = jqElemLiContentItemOption.data("content-item-panels");
				this.jqElemUl.children("li.rocket-new").each(function() {
					var contentItem = _obj.createContentItem($(this), true);
					if (!contentItem.item.initialized) {
						itemsToAdd.push(contentItem);
					} else {
						var panelName = contentItem.getPanelName();
						if (!itemsToAssign.hasOwnProperty(panelName)) {
							itemsToAssign[panelName] = new Array();
						}
						itemsToAssign[panelName].push(contentItem);
					}
					
					if (null === _obj.availableContentItemTypes) {
						_obj.availableContentItemTypes = new Array();
						_obj.availableContentItemLabels = new Object();
						contentItem.item.jqElemSelectItemType.children("option").each(function() {
							_obj.availableContentItemTypes.push($(this).val());
							_obj.availableContentItemLabels[$(this).val()] = $(this).text();
						});
					}
				});
				this.jqElemUl.children("li.rocket-current").each(function() {
					var contentItem = _obj.createContentItem($(this), false);
					var panelName = contentItem.getPanelName();
					if (!itemsToAssign.hasOwnProperty(panelName)) {
						itemsToAssign[panelName] = new Array();
					}
					itemsToAssign[panelName].push(contentItem);
				});
				itemsToAdd.reverse();
				for (var i in itemsToAdd) {
					this.aim.add(itemsToAdd[i]);
				}
				var panels = new Object();
				for (var i in panelOptions) {
					var contentItemTypes = panelOptions[i].allowedContentItemIds 
					if (contentItemTypes.length === 0) {
						contentItemTypes = this.availableContentItemTypes;
					}
					var panel = new ContentItemPanel(i, panelOptions[i].label, contentItemTypes, this);
					
					if (itemsToAssign.hasOwnProperty(i)) {
						itemsToAssign[i].sort(function(a, b) {
							return a.getOrderIndex() - b.getOrderIndex(); 
						});
						for (var j in itemsToAssign[i]) {
							panel.appendContentItem(itemsToAssign[i][j]);
						}
					}
					delete itemsToAssign[i];
					panels[i] = panel;
				}
				this.unAssignedItems = itemsToAssign;
				this.setPanels(panels);
			}).call(this, this);
		};
		
		ContentItemOption.prototype.setPanels = function(panels) {
			for (var i in this.panels) {
				if (!panels.hasOwnProperty(i)) {
					this.panels[i].remove();
				} else {
					this.panels[i].setLabel(panels[i].getLabel());
				}
			}
			for (var i in panels) {
				this.jqElemUl.append(panels[i].jqElemLiContainer);
			}
			this.panels = panels;
			for (var i in this.unAssignedItems) {
				if (this.panels.hasOwnProperty(i)) {
					for (var j in this.unAssignedItems[i]) {
						this.panels[i].appendContentItem(this.unAssignedItems[i][j]);
					}
				} 
			}
		};
		
		ContentItemOption.prototype.createContentItem = function(jqElem, reyclable) {
			var _obj = this;
			var item = new Item(jqElem, this.itemLabel);
			var contentItem = new ContentItem(item, this.frozen, this.aim);
			if (!this.frozen) {
				item.controls.addControl(this.textRemove, function() {
					item.jqElem.trigger(_obj.contentItemChangeEventName);
					if (reyclable) {
						_obj.aim.add(contentItem);
					} else {
						item.jqElem.remove();
					}
				}, "fa fa-times").addClass("rocket-content-item-remove");
				item.controls.addControl(this.textUp, function() {
					var jqElemPrev = item.jqElem.prev();
					if (jqElemPrev.length === 0) return;
					item.ckHack(function() {
						item.jqElem.insertBefore(jqElemPrev);
						item.jqElem.trigger(_obj.contentItemChangeEventName);
					});
				}, "fa fa-arrow-up");
				item.controls.addControl(this.textDown, function() {
					var jqElemNext = item.jqElem.next();
					if (jqElemNext.length === 0) return;
					item.ckHack(function() {
						item.jqElem.insertAfter(jqElemNext);
						item.jqElem.trigger(_obj.contentItemChangeEventName);
					});
				}, "fa fa-arrow-down");
			}
			return contentItem;
		};
		
		ContentItemOption.prototype.contentItemChangeEventName = "content-item-change";
		
		(function() {
			rocket.state.contentItemOptions = new Array();
			var ToMany = function(jqElem) {
				this.jqElem = jqElem;
				this.itemArray = new ItemArray(jqElem.children("ul.rocket-option-array:first"));
				this.itemAssignator = new ItemAssignator(jqElem.children("ul.rocket-existing:first"));
			};
			
			var initToMany = function(jqElemContainer) {
				jqElemContainer.find(".rocket-to-many").each(function() {
					var jqElem = $(this);
					if (jqElem.data('initialized.toMany')) return;
					jqElem.data('initialized.toMany', true);
					var jqElemDivControlGroup = jqElem.parents(".rocket-control-group:first");
					if (jqElemDivControlGroup.hasClass("rocket-content-item-option")) {
						rocket.state.contentItemOptions.push(new ContentItemOption(jqElemDivControlGroup, jqElem));
					} else {
						new ToMany(jqElem);
					}
				});
			}
			window.rocket.core.contentInitializer.registerInitFunction(function(jqElem) {
				initToMany(jqElem);
			});
			initToMany($("body"));
		})();
		
		(function() {
			var ToOne = function(jqElem, initialize) {
				this.jqElem = jqElem;
				this.aim = new AvailableItemManager();
				this.required = jqElem.data("required");
				
				this.jqElemDivExisting = null;
				this.jqElemDivCurrent = null;
				this.jqElemDivNew = null;
				
				this.itemAddButton = null;
				
				this.newItem = null;
				this.currentItem = null;
				
				(function(_obj) {
					this.jqElemDivExisting = jqElem.children(".rocket-existing:first");
					this.jqElemDivCurrent = jqElem.children(".rocket-current:first");
					this.jqElemDivNew = jqElem.children(".rocket-new:first");
					
					if (this.jqElemDivCurrent.length > 0) {
						this.currentItem = new Item(this.jqElemDivCurrent, 'Current Item');
						if (!this.required) {
							this.currentItem.controls.addControl("Remove", function() {
								_obj.jqElemDivExisting.prependTo(jqElem);
								_obj.aim.add(_obj.currentItem);
							}, "fa fa-times");
						}
					}
					
					if (this.jqElemDivNew.length > 0) {
						this.newItem = new Item(this.jqElemDivNew, "Current Item", true, false);
						var wasInitialized = this.newItem.initialized;
						if (!this.newItem.initialized && !(initialize && this.required)) {
							_obj.aim.add(this.newItem);
						} else {
							this.newItem.initialize();
						}
						if (!this.required || (this.jqElemDivExisting.length > 0)) {
							this.newItem.controls.addControl("Remove", function() {
								_obj.jqElemDivExisting.prependTo(jqElem);
								if (null !== _obj.currentItem) {
									_obj.currentItem.jqElem.prependTo(jqElem);
								}
								_obj.aim.add(_obj.newItem);
							}, "fa fa-times");
						}
					}
					
					if (this.currentItem !== null || this.newItem !== null) {
						var isAdd = (this.currentItem === null || this.newItem === null);
						//@todo THINK: Add Button and Replace Button 
						this.itemAddButton = new ItemAddButton(
								isAdd ? jqElem.data("text-add-item") : jqElem.data("text-replace-item"), this.aim, function() {
								_obj.jqElemDivExisting.detach(); 
								if (null !== _obj.currentItem) {
									if (_obj.newItem === null) {
										_obj.currentItem.jqElem.insertBefore(_obj.itemAddButton.jqElemBtn);
									} else {
										_obj.currentItem.jqElem.detach();
									}
								}
								if (_obj.newItem !== null) {
									_obj.newItem.ckHack(function() {
										_obj.newItem.jqElem.insertBefore(_obj.itemAddButton.jqElemBtn);
									});
								}
							}
						);
						this.jqElem.append(this.itemAddButton.jqElemBtn);
						if (this.required && isAdd) {
							this.itemAddButton.jqElemBtn.click();
						}
					}
				}).call(this, this)
			};
			
			//default initializer		
			var initToOne = function(jqElem, initialize) {
				var jqElemsDivRocketToOne = jqElem.find("div.rocket-to-one");
				if (jqElemsDivRocketToOne.length === 0) return;
				
				jqElemsDivRocketToOne.each(function() {
					var jqElem = $(this);
					if (jqElem.data('initialized.toOne')) return;
					jqElem.data('initialized.toOne', true);
					new ToOne($(this), initialize);
				});
			};
			
			window.rocket.core.contentInitializer.registerInitFunction(function(jqElem) {
				initToOne(jqElem, true);
			});
			initToOne($("body"));
		})();
	})();
});