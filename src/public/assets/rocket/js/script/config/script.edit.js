jQuery(document).ready(function($) {
	var jqElemFormScriptEdit = $("#rocket-script-edit");
	if (jqElemFormScriptEdit.length === 0) return;
	
	var saveLabel = jqElemFormScriptEdit.data("save-label");
	var cancelLabel = jqElemFormScriptEdit.data("cancel-label");
	var deleteLabel = jqElemFormScriptEdit.data("delete-label");
	
	var AddingManager = function(jqElemContainer, jqElemCommandContainer) {
		this.jqElemCommandContainer = jqElemCommandContainer || jqElemContainer;
		this.jqElemContainer = jqElemContainer;
		this.saveCallback = null;
		this.cancelCallback = null;
		this.controls = null;
		this.id = this.generateId();
	};
	
	AddingManager.prototype.staticId = 0;
	
	AddingManager.prototype.generateId = function() {
		return ++AddingManager.prototype.staticId;
	};
	
	AddingManager.prototype.initialize = function(saveCallback, cancelCallback) {
		var _obj = this;
		this.saveCallback = saveCallback;
		this.cancelCallback = cancelCallback;
		if (null === this.controls) {
			this.controls = new rocket.Controls('simple');
			this.controls.addControl(saveLabel, function() {
				_obj.save();
				return false;
			}, "fa fa-save");
			this.controls.addControl(cancelLabel, function() {
				_obj.cancel();
				return false;
			}, "fa fa-times-circle");
			this.jqElemCommandContainer.append(this.controls.jqElemUl);
		}
		$(window).on('click.AddingManager' + this.id, function(e) {
			e.preventDefault();
			_obj.save(true);
		}).on('keydown.AddingManager' + this.id, function(e) {
			var keyCode = (window.event) ? e.keyCode : e.which;
			switch(keyCode) {
				case 13:
					//enter;
					_obj.save(false);
					return false;
					break;
				case 27:
					e.preventDefault();
					_obj.cancel();
					break;
			}
		});
		
		this.jqElemContainer.on('click.AddingManager' + this.id, function() {
			return false;
		});
	};
	
	AddingManager.prototype.save = function(cancelIfEmpty) {
		this.offListeners(this.jqElemContainer);
	
		if (false === this.saveCallback(cancelIfEmpty)) {
			this.initialize(this.saveCallback, this.cancelCallback)
		} 
	};
	
	AddingManager.prototype.cancel = function() {
		this.offListeners(this.jqElemContainer);
		this.cancelCallback();
	};
	
	AddingManager.prototype.offListeners = function(jqElemContainer) {
		$(window).off('click.AddingManager' + this.id);
		$(window).off('keydown.AddingManager' + this.id);
		jqElemContainer.off('click.AddingManager' + this.id);
		if (null !== this.controls) {
			this.controls.remove();
			this.controls = null;
		}
	};
	
	var AssignedItem = function(assignedItems, jqElemItem, keepWritable) {
		keepWritable = keepWritable || false;
		this.assignedItems = assignedItems;
		this.jqElemItem = jqElemItem;
		this.jqElemCommandContainer = jqElemItem.children("td:last");
		this.jqElemInputId = jqElemItem.find("input.rocket-config-assigned-item-id");
		this.jqElemInputClassName = jqElemItem.find("input.rocket-config-assigned-item-class-identifier");
		this.intitialize(keepWritable);
	};
	
	AssignedItem.prototype.intitialize = function(keepWritable) {
		//draggable span
		this.jqElemItem.children("td:first").prepend(
				$("<span/>").addClass("rocket-config-list-drag").append(
						$("<i/>", {"class": "icon-th"})));
		new rocket.script.Sortable(this.jqElemItem, {
			containment: this.assignedItems.jqElemContainer
		});
		
		this.jqElemItem.appendTo(this.assignedItems.jqElemContainer);
		
		if (!keepWritable) {
			this.assignReadable();
		}
	};
	
	//
	//-- Commands & Constraints & ChangeListeners
	// 
	AssignedItem.prototype.getClassName = function() {
		return this.jqElemInputClassName.val();
	};
	
	AssignedItem.prototype.assignReadable = function(applyReadOnly) {
		applyReadOnly = (null == applyReadOnly) ? true : applyReadOnly;
		var _obj = this, className = this.getClassName();
		if (applyReadOnly) {
			this.jqElemInputId.attr("readonly", true).removeAttr("placeholder");
			this.jqElemInputClassName.attr("readonly", true).removeAttr("placeholder");
		}

		var controls = new rocket.Controls('simple');
		controls.addControl(deleteLabel, function() {
			_obj.remove();
			return false;
		}, "fa fa-times-circle");
		this.jqElemCommandContainer.append(controls.jqElemUl);
		this.assignedItems.assignedItemNames[className] = className;
	};
	
	AssignedItem.prototype.remove = function() {
		var _obj = this;
		var className = this.getClassName();
		if (this.assignedItems.assignedItemNames.hasOwnProperty(className)) {
			delete this.assignedItems.assignedItemNames[className];
		}
		this.jqElemItem.fadeOut(200, function() {
			_obj.jqElemItem.remove();
		});
	};

	var AssignedItems = function(jqElemContainer) {
		this.jqElemContainer = jqElemContainer;
		this.assignedItemNames = new Object();
		this.jqElemAssignedItemPlain = null;
		this.addLabel = jqElemContainer.data("add-label");
		this.initialize();
	};
	
	AssignedItems.prototype.initialize = function () {
		var _obj  = this;
		this.jqElemContainer.children(".rocket-config-table-row").each(function() {
			new AssignedItem(_obj, $(this));
		});
		
		this.jqElemAssignedItemPlain = this.jqElemContainer.find("tr:not(.rocket-config-table-row):first")
				.detach().addClass("rocket-config-table-item").hide();
		
		this.jqElemContainer.parents("table:first").after(
			$("<a/>", {"href": "#", "class": "rocket-control", "title": this.addIconTitle}).click(function(e) {
				_obj.addUnregisteredItem();
				return false;
			}).append($("<i/>", {"class": this.addIconClassName})).append($("<span/>", {"text": this.addLabel}))
		);
	};
	
	AssignedItems.prototype.addUnregisteredItem = function() {
		var _obj = this;
		var assignedItem = new AssignedItem(this, this.jqElemAssignedItemPlain.clone(), true);
		var addingManager = new AddingManager(assignedItem.jqElemItem, assignedItem.jqElemCommandContainer);
		
		addingManager.initialize(function(cancelIfEmpty) {
			if (assignedItem.getClassName().length > 0) {
				assignedItem.assignReadable(false);
			} else {
				if (cancelIfEmpty) {
					addingManager.cancel();
				} else {
					assignedItem.jqElemInputClassName.focus();
					return false;
				}
			}
		}, function() {
			assignedItem.remove();
		});
		assignedItem.jqElemItem.fadeIn(function() {
			assignedItem.jqElemInputId.focus();
		});
	};
	
	AssignedItems.prototype.assignItemWithName = function(className, id) {
		if (this.assignedItemNames.hasOwnProperty(className)) return;
		var assignedItem = new AssignedItem(this, this.jqElemAssignedItemPlain.clone(true), true);
		assignedItem.jqElemInputId.val(id);
		assignedItem.jqElemInputClassName.val(className);
		assignedItem.assignReadable();
		assignedItem.jqElemItem.show();
	};
	
	(function() {
		////////////////////
		// GENERAL
		////////////////////
	})();
	
	(function() {
		////////////////////
		// FIELDS
		////////////////////
		var ScriptField = function(scriptFieldManager, jqElemItem, writable, propertyName) {
			writable = writable || false;
			this.manager = scriptFieldManager;
			this.jqElemItem = jqElemItem;
			this.jqElemCommandsContainer = jqElemItem.find("td:last");
			this.jqElemInputId = jqElemItem.find("input.rocket-script-fields-config-id:first");
			this.jqElemInputPropertyName = jqElemItem.find("input.rocket-script-fields-config-property-name:first");
			this.jqElemInputEntityPropertyName = jqElemItem.find("input.rocket-script-fields-config-entity-property-name:first");
			this.jqElemInputClassName = jqElemItem.find("input.rocket-script-fields-config-class-name:first");
			this.jqElemInputLabel = jqElemItem.find("input.rocket-script-fields-config-label:first");
			this.completionElementClassName = null;
			
			(function(_obj, writable, propertyName) {
				//draggable span
				this.jqElemItem.children("td:first").prepend(
						$("<span/>").addClass("rocket-config-list-drag").append(
								$("<i/>", {"class": "icon-th"})));
				new rocket.script.Sortable(this.jqElemItem, {
					containment: this.manager.jqElemContainer
				});
				
				this.jqElemInputPropertyName.hide();
				this.jqElemInputEntityPropertyName.hide();
				
				var source = new Object();
				if (propertyName) {
					this.jqElemInputPropertyName.val(propertyName);
					this.jqElemInputEntityPropertyName.val(propertyName);
				} 
				
				var entityPropertyName = this.jqElemInputEntityPropertyName.val();
				if (entityPropertyName.length > 0) {
					if (!this.manager.knownProperties.hasOwnProperty(entityPropertyName)) {
						//@todo add item in list with command to delete this button
						//this.remove();
						//return;
					} else {
						for (var i in this.manager.knownProperties[entityPropertyName].compatibleFields) {
							source[this.manager.knownProperties[entityPropertyName].compatibleFields[i].fieldClassName] 
									= this.manager.knownProperties[entityPropertyName].compatibleFields[i].typeName;
						}
					} 
					
				} else {
					for (var i in this.manager.knownFields) {
						if (this.manager.knownFields[i].entityPropertyRequired) continue;
						source[this.manager.knownFields[i].fieldClassName] = this.manager.knownFields[i].typeName;
					}
					
				}
				
				this.completionElementClassName = new n2n.AutoCompletionElement(this.jqElemInputClassName, {
					source: source,
					allowCustom: true
				});
				
				if (!writable) {
					this.applyControls();
				}
				this.assign();
			}).call(this, this, writable, propertyName);
		};
		
		ScriptField.prototype.getId = function() {
			return this.jqElemInputId.val();
		};
		
		ScriptField.prototype.setClassName = function(className) {
			this.completionElementClassName.setValue(className);
		};
		
		ScriptField.prototype.remove = function() {
			var _obj = this;
			//this.jqElemLi.fadeOut(200, function() {
				_obj.jqElemItem.detach();
				delete _obj.manager.assignedScriptFieldIds[_obj.getId()];
			//});
		};
		
		ScriptField.prototype.assign = function() {
			var id = this.getId();
			this.manager.assignedScriptFieldIds[id] = id;
			this.jqElemItem.appendTo(this.manager.jqElemContainer);
			this.jqElemItem.show();
		}
		
		ScriptField.prototype.applyControls = function() {
			var _obj = this;
			var controls = new rocket.Controls(rocket.Controls.TYPE_SIMPLE);
			controls.addControl(this.manager.banTitle, function() {
				_obj.manager.createUnassignedScriptField(_obj.getId(), _obj);
				_obj.remove();
			}, 'fa fa-ban');
			this.jqElemCommandsContainer.append(controls.jqElemUl);
		};
		
		ScriptField.prototype.isValid = function() {
			return (this.getId().length > 0 && this.jqElemInputClassName.val().length > 0);
		}
		
		ScriptField.prototype.isEmpty = function() {
			return (this.getId().length == 0 && this.jqElemInputClassName.val().length == 0);
		};
		
		var ScriptFieldManager = function(jqElemContainer) {
			this.jqElemContainer = jqElemContainer;
			this.assignedScriptFieldIds = new Object();
			this.knownProperties = jqElemContainer.data("known-properties");
			this.knownFields = jqElemContainer.data("known-fields");
			
			this.jqElemUlUnassignedScriptFieldManager = null;
			this.jqElemScriptFieldPlain = null;
			
			this.banTitle = jqElemContainer.data("ban-title");
			this.assignTitle = jqElemContainer.data("assign-title");
			this.addLabel = jqElemContainer.data("add-label");

			(function(_obj) {
				this.jqElemContainer.children(".rocket-config-table-row").each(function() {
					new ScriptField(_obj, $(this));
				});
				this.jqElemUlUnassignedScriptFieldManager = $("<ul/>").insertAfter(this.jqElemContainer.parents("table:first"));
				
				var controls = new rocket.Controls();
				controls.addControl(this.addLabel, function() {
					_obj.addUnregisteredScriptField();
					return false;
				}, 'fa fa-plus-circle');
				
				$("#rocket-script-config-fields > .rocket-list").append(controls.jqElemUl);
				
				for (var i in this.knownProperties) {
					if (this.assignedScriptFieldIds.hasOwnProperty(i)) continue;
					this.createUnassignedScriptField(i);
				}
				
				this.jqElemScriptFieldPlain = this.jqElemContainer.children("tr:not(.rocket-config-table-row):first")
						.addClass("rocket-config-table-row").detach();
			}).call(this, this);
		}
		
		ScriptFieldManager.prototype.createUnassignedScriptField = function(id, oldScriptField) {
			if (!this.knownProperties.hasOwnProperty(id)) return;
			var _obj = this;
			var jqElemLiUnassigned = $("<li/>", {text: id, "class": "rocket-config-list-item"}).appendTo(this.jqElemUlUnassignedScriptFieldManager);
			
			var controls = new rocket.Controls(rocket.Controls.TYPE_SIMPLE);
			controls.addControl(this.assignTitle, function(jqElemAButton, e) {
				e.stopPropagation();
				var _func = this;
				this.jqElemClassNameSuggestions = this.jqElemClassNameSuggestions || null;
				if (oldScriptField) {
					oldScriptField.assign();
					oldScriptField.jqElemInputId.focus();
					jqElemLiUnassigned.remove();
				} else {
					if (null === this.jqElemClassNameSuggestions) {
						controls.jqElemUl.css({
							position: "relative"
						});
						this.jqElemClassNameSuggestions = $("<ul/>").css({
							position: "absolute",
							top: jqElemAButton.outerHeight(true) + jqElemAButton.position().top,
							zIndex: 2000,
							left: "250px"
						}).addClass("rocket-script-field-class-name-suggestions").appendTo($("<li/>").appendTo(controls.jqElemUl));
						
						for (var i in _obj.knownProperties[id].compatibleFields) {
							this.jqElemClassNameSuggestions.append($("<li/>", {text: _obj.knownProperties[id].compatibleFields[i].typeName}).click(function() {
								var scriptField = new ScriptField(_obj, _obj.jqElemScriptFieldPlain.clone(true), true, id);
								scriptField.jqElemInputId.val(id);
								scriptField.jqElemInputLabel.val(_obj.knownProperties[id].suggestedLabel);
								scriptField.setClassName($(this).data("class-name"));
								scriptField.jqElemInputId.focus();
								jqElemLiUnassigned.remove();
							}).css({
								whiteSpace: "nowrap"
							}).addClass("rocket-script-field-class-name-suggestion-entry")
								.data('class-name', _obj.knownProperties[id].compatibleFields[i].fieldClassName));
						}
						$(window).on('click.classNameSuggestions', function() {
							$(window).off('click.classNameSuggestions');
							_func.jqElemClassNameSuggestions.hide();
						});
					} else {
						this.jqElemClassNameSuggestions.show();
					}
					var maxHeight = $(document).height() - this.jqElemClassNameSuggestions.offset().top;
					if (maxHeight < this.jqElemClassNameSuggestions.height()) {
						this.jqElemClassNameSuggestions.height(maxHeight).css({
							overflowY: "scroll"
						});
					}
				}
			}, 'fa fa-share');
			jqElemLiUnassigned.append(controls.jqElemUl);
		};
		
		ScriptFieldManager.prototype.addUnregisteredScriptField = function() {
			var _obj = this;
			var newScriptField = new ScriptField(this, _obj.jqElemScriptFieldPlain.clone(true), true)
			var addingManager = new AddingManager(newScriptField.jqElemItem, newScriptField.jqElemCommandsContainer);
			addingManager.initialize(function(cancelIfEmpty) {
				if (newScriptField.isValid()) {
					newScriptField.applyReadable();
				} else {
					if (newScriptField.isEmpty() && cancelIfEmpty) {
						addingManager.cancel();
					} else {
						if (newScriptField.getId().length == 0) {
							newScriptField.jqElemInputId.focus();
						} else {
							newScriptField.jqElemInputClassName.focus();
						}
						return false;
					}
				}
			}, function() {
				newScriptField.remove();
			})
			newScriptField.jqElemInputId.focus();
		};
		new ScriptFieldManager($("#rocket-config-assigned-script-fields"));
	})();
	
	(function() {
		////////////////////
		// COMMANDS
		////////////////////
		var assignedScriptCommands = new AssignedItems($("#rocket-config-assigned-script-commands"));
		
		$("#rocket-config-available-script-commands a.rocket-config-assign-script").click(function(){
			assignedScriptCommands.assignItemWithName($(this).data("script-command-name"));
			return false;
		});
		
		$("#rocket-config-available-script-command-groups li.rocket-config-list-item").each(function(){
			var _obj = $(this);
			$(this).find("a.rocket-config-assign-script").click(function(){
				_obj.find("ul.rocket-config-script-command-groups-commands > li").each(function(){
					assignedScriptCommands.assignItemWithName($(this).data("script-command-name"));
				});
				return false;
			});
		});
	})();
	
	(function() {
		////////////////////
		// CONSTRAINTS
		////////////////////
		var assignedConstraints = new AssignedItems($("#rocket-config-assigned-edit-constraints"));
		$("#rocket-config-available-script-constraints a.rocket-config-assign-script").click(function(){
			assignedConstraints.assignItemWithName($(this).data("script-constraint-name"));
			return false;
		});
		
	})();
	
	(function() {
		////////////////////
		// LISTENERS
		////////////////////
		new AssignedItems($("#rocket-config-assigned-edit-listeners"));
	})();
});