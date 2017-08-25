var rocket;
(function (rocket) {
    var cmd;
    (function (cmd) {
        var $ = jQuery;
        var Monitor = (function () {
            function Monitor(executor) {
                this.executor = executor;
            }
            Monitor.prototype.scanMain = function (jqContent, layer) {
                var that = this;
                jqContent.find("a.rocket-ajah").each(function () {
                    (new LinkAction(that.executor, jQuery(this), layer)).activate();
                });
            };
            Monitor.prototype.scan = function (jqContainer) {
                var that = this;
                jqContainer.find("a.rocket-ajah").each(function () {
                    CommandAction.from($(this), that.executor);
                });
            };
            return Monitor;
        }());
        cmd.Monitor = Monitor;
        var LinkAction = (function () {
            function LinkAction(executor, jqA, layer) {
                this.executor = executor;
                this.jqA = jqA;
                this.layer = layer;
            }
            LinkAction.prototype.activate = function () {
                var that = this;
                this.jqA.click(function (e) {
                    e.stopImmediatePropagation();
                    e.stopPropagation();
                    that.handle();
                    return false;
                });
            };
            LinkAction.prototype.handle = function () {
                var url = this.jqA.attr("href");
                this.executor.exec(url, { currentLayer: this.layer });
            };
            return LinkAction;
        }());
        var CommandAction = (function () {
            function CommandAction(executor, jqElem) {
                this.executor = executor;
                this.jqElem = jqElem;
                var that = this;
                jqElem.click(function (e) {
                    that.handle();
                    return false;
                });
            }
            CommandAction.prototype.handle = function () {
                var url = this.jqElem.attr("href");
                var context = cmd.Context.findFrom(this.jqElem);
                if (context === null) {
                    throw new Error("Command belongs to no Context.");
                }
                this.executor.exec(url, { currentContext: context });
            };
            CommandAction.from = function (jqElem, executor) {
                var commandAction = jqElem.data("rocketCommandAction");
                if (commandAction)
                    return commandAction;
                commandAction = new CommandAction(executor, jqElem);
                jqElem.data("rocketCommandAction", commandAction);
                return commandAction;
            };
            return CommandAction;
        }());
    })(cmd = rocket.cmd || (rocket.cmd = {}));
})(rocket || (rocket = {}));
var rocket;
(function (rocket) {
    var display;
    (function (display) {
        var Initializer = (function () {
            function Initializer(container, errorTabTitle, displayErrorLabel) {
                this.container = container;
                this.errorTabTitle = errorTabTitle;
                this.displayErrorLabel = displayErrorLabel;
                this.errorIndexes = new Array();
            }
            Initializer.prototype.scan = function () {
                var errorIndex = null;
                while (undefined !== (errorIndex = this.errorIndexes.pop())) {
                    errorIndex.getTab().dispose();
                }
                var contexts = this.container.getAllContexts();
                for (var i in contexts) {
                    this.scanContext(contexts[i]);
                }
            };
            Initializer.prototype.scanContext = function (context) {
                var that = this;
                var i = 0;
                var jqContext = context.getJQuery();
                jqContext.find(".rocket-group, .rocket-group-main, .rocket-field").each(function () {
                    var jqElem = $(this);
                    var structureElement = display.StructureElement.from(jqElem);
                    if (structureElement !== null)
                        return;
                    if (!jqElem.hasClass("rocket-group-main")) {
                        display.StructureElement.from(jqElem, true);
                        ;
                        return;
                    }
                    Initializer.scanGroupNav(jqElem.parent());
                });
                var errorIndex = null;
                jqContext.find(".rocket-message-error").each(function () {
                    var structureElement = display.StructureElement.findFrom($(this));
                    if (errorIndex === null) {
                        errorIndex = new ErrorIndex(context.createAdditionalTab(that.errorTabTitle), that.displayErrorLabel);
                        that.errorIndexes.push(errorIndex);
                    }
                    errorIndex.addError(structureElement, $(this).text());
                });
            };
            Initializer.scanGroupNav = function (jqContainer) {
                var curGroupNav = null;
                jqContainer.children(".rocket-group, .rocket-group-main, .rocket-group-autonomic").each(function () {
                    var jqElem = $(this);
                    if (!jqElem.hasClass("rocket-group-main")) {
                        curGroupNav = null;
                        return;
                    }
                    if (curGroupNav === null) {
                        curGroupNav = GroupNav.fromMain(jqElem);
                    }
                    var group = display.StructureElement.from(jqElem);
                    if (group === null) {
                        curGroupNav.registerGroup(display.StructureElement.from(jqElem, true));
                    }
                });
                return curGroupNav;
            };
            return Initializer;
        }());
        display.Initializer = Initializer;
        var GroupNav = (function () {
            function GroupNav(jqGroupNav) {
                this.jqGroupNav = jqGroupNav;
                this.groups = new Array();
                jqGroupNav.addClass("rocket-main-group-nav");
                jqGroupNav.hide();
            }
            GroupNav.prototype.registerGroup = function (group) {
                this.groups.push(group);
                if (this.groups.length == 2) {
                    this.jqGroupNav.show();
                }
                var jqLi = $("<li />", {
                    "text": group.getTitle(),
                    "clss": { "cursor": "pointer" }
                });
                this.jqGroupNav.append(jqLi);
                var that = this;
                jqLi.click(function () {
                    group.show();
                });
                group.onShow(function () {
                    jqLi.addClass("rocket-active");
                    for (var i in that.groups) {
                        if (that.groups[i] !== group) {
                            that.groups[i].hide();
                        }
                    }
                });
                group.onHide(function () {
                    jqLi.removeClass("rocket-active");
                });
                if (this.groups.length == 1) {
                    group.show();
                }
            };
            GroupNav.fromMain = function (jqElem, create) {
                if (create === void 0) { create = true; }
                var groupNav = null;
                var jqPrev = jqElem.prev(".rocket-main-group-nav");
                if (jqPrev.length > 0) {
                    groupNav = jqPrev.data("rocketGroupNav");
                }
                if (groupNav)
                    return groupNav;
                if (!create)
                    return null;
                var jqUl = $("<ul />").insertBefore(jqElem);
                return new GroupNav(jqUl);
            };
            return GroupNav;
        }());
        var ErrorIndex = (function () {
            function ErrorIndex(tab, displayErrorLabel) {
                this.tab = tab;
                this.displayErrorLabel = displayErrorLabel;
            }
            ErrorIndex.prototype.getTab = function () {
                return this.tab;
            };
            ErrorIndex.prototype.addError = function (structureElement, errorMessage) {
                var jqElem = $("<div />", {
                    "class": "rocket-error-index-entry",
                    "css": { "cursor": "pointer" }
                }).append($("<div />", {
                    "text": errorMessage
                })).append($("<div />", {
                    "text": this.displayErrorLabel
                }));
                this.tab.getJqContent().append(jqElem);
                var clicked = false;
                var visibleSe = null;
                jqElem.mouseenter(function () {
                    structureElement.highlight(true);
                });
                jqElem.mouseleave(function () {
                    structureElement.unhighlight(clicked);
                    clicked = false;
                });
                jqElem.click(function () {
                    clicked = true;
                    structureElement.show(true);
                    structureElement.scrollTo();
                });
            };
            return ErrorIndex;
        }());
    })(display = rocket.display || (rocket.display = {}));
})(rocket || (rocket = {}));
var rocket;
(function (rocket) {
    var display;
    (function (display) {
        var StructureElement = (function () {
            function StructureElement(jqElem) {
                this.onShowCallbacks = new Array();
                this.onHideCallbacks = new Array();
                this.toolbar = null;
                this.highlightedParent = null;
                this.jqElem = jqElem;
                jqElem.addClass("rocket-structure-element");
                jqElem.data("rocketStructureElement", this);
            }
            StructureElement.prototype.getJQuery = function () {
                return this.jqElem;
            };
            StructureElement.prototype.setGroup = function (group) {
                if (!group) {
                    this.jqElem.removeClass("rocket-group");
                }
                else {
                    this.jqElem.addClass("rocket-group");
                }
            };
            StructureElement.prototype.isGroup = function () {
                return this.jqElem.hasClass("rocket-group") || this.jqElem.hasClass("rocket-group-main");
            };
            StructureElement.prototype.setField = function (field) {
                if (!field) {
                    this.jqElem.removeClass("rocket-field");
                }
                else {
                    this.jqElem.addClass("rocket-field");
                }
            };
            StructureElement.prototype.isField = function () {
                return this.jqElem.hasClass("rocket-field");
            };
            StructureElement.prototype.getToolbar = function () {
                if (this.toolbar !== null) {
                    return this.toolbar;
                }
                if (!this.isGroup()) {
                    return null;
                }
                var jqToolbar = this.jqElem.children(".rocket-group-toolbar:first");
                if (jqToolbar.length == 0) {
                    jqToolbar = $("<div />", { "class": "rocket-group-toolbar" });
                    this.jqElem.prepend(jqToolbar);
                }
                return this.toolbar = new Toolbar(jqToolbar);
            };
            StructureElement.prototype.getTitle = function () {
                return this.jqElem.children("label:first").text();
            };
            StructureElement.prototype.getParent = function () {
                return StructureElement.findFrom(this.jqElem);
            };
            StructureElement.prototype.isVisible = function () {
                return this.jqElem.is(":visible");
            };
            StructureElement.prototype.show = function (includeParents) {
                if (includeParents === void 0) { includeParents = false; }
                for (var i in this.onShowCallbacks) {
                    this.onShowCallbacks[i](this);
                }
                this.jqElem.show();
                var parent;
                if (includeParents && null !== (parent = this.getParent())) {
                    parent.show(true);
                }
            };
            StructureElement.prototype.hide = function () {
                for (var i in this.onHideCallbacks) {
                    this.onHideCallbacks[i](this);
                }
                this.jqElem.hide();
            };
            //		public addChild(structureElement: StructureElement) {
            //			var that = this;
            //			structureElement.onShow(function () {
            //				that.show();
            //			});
            //		}
            StructureElement.prototype.onShow = function (callback) {
                this.onShowCallbacks.push(callback);
            };
            StructureElement.prototype.onHide = function (callback) {
                this.onHideCallbacks.push(callback);
            };
            StructureElement.prototype.scrollTo = function () {
                var top = this.jqElem.offset().top;
                var maxOffset = top - 50;
                var height = this.jqElem.outerHeight();
                var margin = $(window).height() - height;
                var offset = top - (margin / 2);
                if (maxOffset < offset) {
                    offset = maxOffset;
                }
                $("html, body").animate({
                    "scrollTop": offset
                }, 250);
            };
            StructureElement.prototype.highlight = function (findVisibleParent) {
                if (findVisibleParent === void 0) { findVisibleParent = false; }
                this.jqElem.addClass("rocket-highlighted");
                if (!findVisibleParent || this.isVisible())
                    return;
                this.highlightedParent = this;
                while (null !== (this.highlightedParent = this.highlightedParent.getParent())) {
                    if (!this.highlightedParent.isVisible())
                        continue;
                    this.highlightedParent.highlight();
                    return;
                }
            };
            StructureElement.prototype.unhighlight = function (slow) {
                if (slow === void 0) { slow = false; }
                this.jqElem.removeClass("rocket-highlighted");
                if (slow) {
                    this.jqElem.addClass("rocket-highlight-remember");
                }
                else {
                    this.jqElem.removeClass("rocket-highlight-remember");
                }
                if (this.highlightedParent !== null) {
                    this.highlightedParent.unhighlight();
                    this.highlightedParent = null;
                }
            };
            StructureElement.from = function (jqElem, create) {
                if (create === void 0) { create = false; }
                var structureElement = jqElem.data("rocketStructureElement");
                if (structureElement instanceof StructureElement)
                    return structureElement;
                if (!create)
                    return null;
                structureElement = new StructureElement(jqElem);
                jqElem.data("rocketStructureElement", structureElement);
                return structureElement;
            };
            StructureElement.findFrom = function (jqElem) {
                jqElem = jqElem.parents(".rocket-group, .rocket-field");
                var structureElement = jqElem.data("rocketStructureElement");
                if (structureElement instanceof StructureElement) {
                    return structureElement;
                }
                return null;
            };
            return StructureElement;
        }());
        display.StructureElement = StructureElement;
        var Toolbar = (function () {
            function Toolbar(jqToolbar) {
                this.jqToolbar = jqToolbar;
                this.jqControls = jqToolbar.children(".rocket-group-controls");
                if (this.jqControls.length == 0) {
                    this.jqControls = $("<div />", { "class": "rocket-group-controls" });
                    this.jqToolbar.append(this.jqControls);
                    this.jqControls.hide();
                }
                else if (this.jqControls.is(':empty')) {
                    this.jqControls.hide();
                }
                var jqCommands = jqToolbar.children(".rocket-simple-commands");
                if (jqCommands.length == 0) {
                    jqCommands = $("<div />", { "class": "rocket-simple-commands" });
                    jqToolbar.append(jqCommands);
                }
                this.commandList = new CommandList(jqCommands, true);
            }
            Toolbar.prototype.getJQuery = function () {
                return this.jqToolbar;
            };
            Toolbar.prototype.getJqControls = function () {
                return this.jqControls;
            };
            Toolbar.prototype.getCommandList = function () {
                return this.commandList;
            };
            return Toolbar;
        }());
        var CommandList = (function () {
            function CommandList(jqCommandList, simple) {
                if (simple === void 0) { simple = false; }
                this.jqCommandList = jqCommandList;
                if (simple) {
                    jqCommandList.addClass("rocket-simple-commands");
                }
            }
            CommandList.prototype.getJQuery = function () {
                return this.jqCommandList;
            };
            CommandList.prototype.createJqCommandButton = function (buttonConfig /*, iconType: string, label: string, severity: Severity = Severity.SECONDARY, tooltip: string = null*/, prepend) {
                if (prepend === void 0) { prepend = false; }
                this.jqCommandList.show();
                if (buttonConfig.iconType === undefined) {
                    buttonConfig.iconType = "fa fa-circle-o";
                }
                if (buttonConfig.severity === undefined) {
                    buttonConfig.severity = display.Severity.SECONDARY;
                }
                var jqButton = $("<button />", {
                    "class": "btn btn-" + buttonConfig.severity,
                    "title": buttonConfig.tooltip,
                    "type": "button"
                }).append($("<i />", {
                    "class": buttonConfig.iconType
                })).append($("<span />", {
                    "text": buttonConfig.label
                }));
                if (prepend) {
                    this.jqCommandList.prepend(jqButton);
                }
                else {
                    this.jqCommandList.append(jqButton);
                }
                return jqButton;
            };
            return CommandList;
        }());
        display.CommandList = CommandList;
    })(display = rocket.display || (rocket.display = {}));
})(rocket || (rocket = {}));
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 *
 */
var rocket;
(function (rocket) {
    var impl;
    (function (impl) {
        var display = rocket.display;
        var $ = jQuery;
        var ToMany = (function () {
            function ToMany(selector, embedded) {
                if (selector === void 0) { selector = null; }
                if (embedded === void 0) { embedded = null; }
                this.selector = selector;
                this.embedded = embedded;
            }
            ToMany.from = function (jqToMany) {
                var toMany = jqToMany.data("rocketImplToMany");
                if (toMany instanceof ToMany) {
                    return toMany;
                }
                var toManySelector = null;
                var jqSelector = jqToMany.children(".rocket-impl-selector");
                if (jqSelector.length > 0) {
                    toManySelector = new ToManySelector(jqSelector);
                }
                var jqCurrents = jqToMany.children(".rocket-impl-currents");
                var jqNews = jqToMany.children(".rocket-impl-news");
                var addControlFactory = null;
                var toManyEmbedded = null;
                if (jqCurrents.length > 0 || jqNews.length > 0) {
                    if (jqNews.length > 0) {
                        var propertyPath = jqNews.data("property-path");
                        var startKey = 0;
                        var testPropertyPath = propertyPath + "[n";
                        jqNews.find("input, textarea").each(function () {
                            var name = $(this).attr("name");
                            if (0 == name.indexOf(testPropertyPath)) {
                                name = name.substring(testPropertyPath.length);
                                name.match(/^[0-9]+/).forEach(function (key) {
                                    var curKey = parseInt(key);
                                    if (curKey >= startKey) {
                                        startKey = curKey + 1;
                                    }
                                });
                            }
                        });
                        var entryFormRetriever = new EmbeddedEntryRetriever(jqNews.data("new-entry-form-url"), propertyPath, jqNews.data("draftMode"), startKey, "n");
                        addControlFactory = new AddControlFactory(entryFormRetriever, jqNews.data("add-item-label"));
                    }
                    toManyEmbedded = new ToManyEmbedded(jqToMany, addControlFactory);
                    jqCurrents.children(".rocket-impl-entry").each(function () {
                        toManyEmbedded.addEntry(new EmbeddedEntry($(this), toManyEmbedded.isReadOnly()));
                    });
                    jqNews.children(".rocket-impl-entry").each(function () {
                        toManyEmbedded.addEntry(new EmbeddedEntry($(this), toManyEmbedded.isReadOnly()));
                    });
                }
                var toMany = new ToMany(toManySelector, toManyEmbedded);
                jqToMany.data("rocketImplToMany", toMany);
                return toMany;
            };
            return ToMany;
        }());
        impl.ToMany = ToMany;
        var ToManySelector = (function () {
            function ToManySelector(jqElem) {
                this.entries = new Array();
                this.jqElem = jqElem;
                this.jqUl = jqElem.children("ul");
                this.init();
            }
            ToManySelector.prototype.init = function () {
                var jqCommandList = $("<div />");
                this.jqElem.append(jqCommandList);
                var that = this;
                var commandList = new display.CommandList(jqCommandList);
                commandList.createJqCommandButton({ label: this.jqElem.data("select-label") }).click(function () {
                    that.openBrowser();
                });
                commandList.createJqCommandButton({ label: this.jqElem.data("reset-label") }).click(function () {
                });
                commandList.createJqCommandButton({ label: this.jqElem.data("clear-label") }).click(function () {
                });
            };
            ToManySelector.prototype.addSelectedEntry = function (entry) {
                this.entries.push(entry);
            };
            ToManySelector.prototype.openBrowser = function () {
                var layer = rocket.getContainer().createLayer();
                rocket.exec(this.jqElem.data("overview-tools-url"), {
                    showLoadingContext: true,
                    currentLayer: layer
                });
            };
            return ToManySelector;
        }());
        var SelectedEntry = (function () {
            function SelectedEntry(jqElem) {
                this.jqElem = jqElem;
            }
            Object.defineProperty(SelectedEntry.prototype, "jQuery", {
                get: function () {
                    return this.jqElem;
                },
                enumerable: true,
                configurable: true
            });
            return SelectedEntry;
        }());
        var ToManyEmbedded = (function () {
            function ToManyEmbedded(jqToMany, addButtonFactory) {
                if (addButtonFactory === void 0) { addButtonFactory = null; }
                this.compact = true;
                this.sortable = true;
                this.entries = new Array();
                this.expandContext = null;
                this.dominantEntry = null;
                this.firstAddControl = null;
                this.lastAddControl = null;
                this.entryAddControls = new Array();
                this.jqToMany = jqToMany;
                this.addControlFactory = addButtonFactory;
                this.compact = (true == jqToMany.data("compact"));
                this.sortable = (true == jqToMany.data("sortable"));
                this.closeLabel = jqToMany.data("close-label");
                this.jqEmbedded = $("<div />", {
                    "class": "rocket-impl-embedded"
                });
                this.jqToMany.append(this.jqEmbedded);
                this.jqEntries = $("<div />");
                this.jqEmbedded.append(this.jqEntries);
                if (this.compact) {
                    var structureElement = rocket.display.StructureElement.findFrom(this.jqToMany);
                    structureElement.setGroup(true);
                    var toolbar = structureElement.getToolbar();
                    if (toolbar !== null) {
                        var jqButton = null;
                        if (this.isReadOnly()) {
                            jqButton = toolbar.getCommandList().createJqCommandButton({ iconType: "fa fa-file", label: "Detail" });
                        }
                        else {
                            jqButton = toolbar.getCommandList().createJqCommandButton({ iconType: "fa fa-pencil", label: "Edit", severity: display.Severity.WARNING });
                        }
                        var that_1 = this;
                        jqButton.click(function () {
                            that_1.expand();
                        });
                    }
                }
                if (this.sortable) {
                    this.initSortable();
                }
                this.changed();
            }
            ToManyEmbedded.prototype.isReadOnly = function () {
                return this.addControlFactory === null;
            };
            ToManyEmbedded.prototype.changed = function () {
                for (var i_1 in this.entries) {
                    var index = parseInt(i_1);
                    this.entries[index].setOrderIndex(index);
                    if (this.isPartialExpaned())
                        continue;
                    this.entries[index].setMoveUpEnabled(index > 0);
                    this.entries[index].setMoveDownEnabled(index < this.entries.length - 1);
                }
                if (this.addControlFactory === null)
                    return;
                if (this.entries.length === 0 && this.firstAddControl !== null) {
                    this.firstAddControl.dispose();
                    this.firstAddControl = null;
                }
                if (this.entries.length > 0 && this.firstAddControl === null) {
                    this.firstAddControl = this.createFirstAddControl();
                }
                for (var i in this.entryAddControls) {
                    this.entryAddControls[i].dispose();
                }
                if (this.isExpanded() && !this.isPartialExpaned()) {
                    for (var i in this.entries) {
                        if (parseInt(i) == 0)
                            continue;
                        this.entryAddControls.push(this.createEntryAddControl(this.entries[i]));
                    }
                }
                if (this.lastAddControl === null) {
                    this.lastAddControl = this.createLastAddControl();
                }
                if (this.isPartialExpaned()) {
                    if (this.firstAddControl !== null) {
                        this.firstAddControl.getJQuery().hide();
                    }
                    this.lastAddControl.getJQuery().hide();
                }
                else {
                    if (this.firstAddControl !== null) {
                        this.firstAddControl.getJQuery().show();
                    }
                    this.lastAddControl.getJQuery().show();
                }
            };
            ToManyEmbedded.prototype.createFirstAddControl = function () {
                var addControl = this.addControlFactory.create();
                var that = this;
                this.jqEmbedded.prepend(addControl.getJQuery());
                addControl.onNewEmbeddedEntry(function (newEntry) {
                    that.insertEntry(newEntry);
                    if (!that.isExpanded()) {
                        that.expand(newEntry);
                    }
                });
                return addControl;
            };
            ToManyEmbedded.prototype.createEntryAddControl = function (entry) {
                var addControl = this.addControlFactory.create();
                var that = this;
                this.entryAddControls.push(addControl);
                addControl.getJQuery().insertBefore(entry.getJQuery());
                addControl.onNewEmbeddedEntry(function (newEntry) {
                    that.insertEntry(newEntry, entry);
                });
                return addControl;
            };
            ToManyEmbedded.prototype.createLastAddControl = function () {
                var addControl = this.addControlFactory.create();
                var that = this;
                this.jqEmbedded.append(addControl.getJQuery());
                addControl.onNewEmbeddedEntry(function (newEntry) {
                    that.addEntry(newEntry);
                    if (!that.isExpanded()) {
                        that.expand(newEntry);
                    }
                });
                return addControl;
            };
            ToManyEmbedded.prototype.insertEntry = function (entry, beforeEntry) {
                if (beforeEntry === void 0) { beforeEntry = null; }
                entry.getJQuery().detach();
                if (beforeEntry === null) {
                    this.entries.unshift(entry);
                    this.jqEntries.prepend(entry.getJQuery());
                }
                else {
                    entry.getJQuery().insertBefore(beforeEntry.getJQuery());
                    this.entries.splice(beforeEntry.getOrderIndex(), 0, entry);
                }
                this.initEntry(entry);
                this.changed();
            };
            ToManyEmbedded.prototype.addEntry = function (entry) {
                entry.setOrderIndex(this.entries.length);
                this.entries.push(entry);
                this.jqEntries.append(entry.getJQuery());
                this.initEntry(entry);
                if (this.isReadOnly())
                    return;
                this.changed();
            };
            ToManyEmbedded.prototype.switchIndex = function (oldIndex, newIndex) {
                var entry = this.entries[oldIndex];
                this.entries[oldIndex] = this.entries[newIndex];
                this.entries[newIndex] = entry;
                this.changed();
            };
            ToManyEmbedded.prototype.initEntry = function (entry) {
                if (this.isExpanded()) {
                    entry.expand();
                }
                else {
                    entry.reduce();
                }
                var that = this;
                entry.onMove(function (up) {
                    var oldIndex = entry.getOrderIndex();
                    var newIndex = up ? oldIndex - 1 : oldIndex + 1;
                    if (newIndex < 0 || newIndex >= that.entries.length) {
                        return;
                    }
                    if (up) {
                        that.entries[oldIndex].getJQuery().insertBefore(that.entries[newIndex].getJQuery());
                    }
                    else {
                        that.entries[oldIndex].getJQuery().insertAfter(that.entries[newIndex].getJQuery());
                    }
                    that.switchIndex(oldIndex, newIndex);
                });
                entry.onRemove(function () {
                    that.entries.splice(entry.getOrderIndex(), 1);
                    entry.getJQuery().remove();
                    this.update();
                });
                entry.onFocus(function () {
                    that.expand(entry);
                });
            };
            ToManyEmbedded.prototype.initSortable = function () {
                var that = this;
                var oldIndex = 0;
                this.jqEntries.sortable({
                    "handle": ".rocket-impl-handle",
                    "forcePlaceholderSize": true,
                    "placeholder": "rocket-impl-entry-placeholder",
                    "start": function (event, ui) {
                        var oldIndex = ui.item.index();
                    },
                    "update": function (event, ui) {
                        var newIndex = ui.item.index();
                        that.switchIndex(oldIndex, newIndex);
                    }
                }).disableSelection();
            };
            ToManyEmbedded.prototype.enabledSortable = function () {
                this.jqEntries.sortable("enable");
                this.jqEntries.disableSelection();
            };
            ToManyEmbedded.prototype.disableSortable = function () {
                this.jqEntries.sortable("disable");
                this.jqEntries.enableSelection();
            };
            ToManyEmbedded.prototype.isExpanded = function () {
                return this.expandContext !== null;
            };
            ToManyEmbedded.prototype.isPartialExpaned = function () {
                return this.dominantEntry !== null;
            };
            ToManyEmbedded.prototype.expand = function (dominantEntry) {
                if (dominantEntry === void 0) { dominantEntry = null; }
                if (this.isExpanded())
                    return;
                if (this.sortable) {
                    this.disableSortable();
                }
                this.dominantEntry = dominantEntry;
                this.expandContext = rocket.getContainer().createLayer().createContext(window.location.href);
                this.jqEmbedded.detach();
                this.expandContext.applyContent(this.jqEmbedded);
                this.expandContext.getLayer().pushHistoryEntry(window.location.href);
                for (var i in this.entries) {
                    if (dominantEntry === null) {
                        this.entries[i].expand(true);
                    }
                    else if (dominantEntry === this.entries[i]) {
                        this.entries[i].expand(false);
                    }
                    else {
                        this.entries[i].hide();
                    }
                }
                var that = this;
                var jqCommandButton = this.expandContext.getMenu().getCommandList()
                    .createJqCommandButton({ iconType: "fa fa-times", label: this.closeLabel, severity: display.Severity.WARNING }, true);
                jqCommandButton.click(function () {
                    that.expandContext.getLayer().close();
                });
                this.expandContext.onClose(function () {
                    that.reduce();
                });
                this.changed();
                n2n.ajah.update();
            };
            ToManyEmbedded.prototype.reduce = function () {
                if (!this.isExpanded())
                    return;
                this.dominantEntry = null;
                this.expandContext = null;
                this.jqEmbedded.detach();
                this.jqToMany.append(this.jqEmbedded);
                for (var i in this.entries) {
                    this.entries[i].reduce();
                }
                if (this.sortable) {
                    this.enabledSortable();
                }
                this.changed();
                n2n.ajah.update();
            };
            return ToManyEmbedded;
        }());
        impl.ToManyEmbedded = ToManyEmbedded;
        var EmbeddedEntry = (function () {
            function EmbeddedEntry(jqEntry, readOnly) {
                this.entryGroup = display.StructureElement.from(jqEntry, true);
                this.readOnly = readOnly;
                this.bodyGroup = display.StructureElement.from(jqEntry.children(".rocket-impl-body"), true);
                this.jqOrderIndex = jqEntry.children(".rocket-impl-order-index").hide();
                this.jqSummary = jqEntry.children(".rocket-impl-summary");
                this.jqContextCommands = this.bodyGroup.getJQuery().children(".rocket-context-commands");
                if (readOnly) {
                    var rcl = new display.CommandList(this.jqSummary.children(".rocket-simple-commands"), true);
                    this.jqRedFocusButton = rcl.createJqCommandButton({ iconType: "fa fa-file", label: "Detail", severity: display.Severity.SECONDARY });
                }
                else {
                    this.entryForm = display.EntryForm.from(jqEntry, true);
                    var ecl = this.bodyGroup.getToolbar().getCommandList();
                    this.jqExpMoveUpButton = ecl.createJqCommandButton({ iconType: "fa fa-arrow-up", label: "Move up" });
                    this.jqExpMoveDownButton = ecl.createJqCommandButton({ iconType: "fa fa-arrow-down", label: "Move down" });
                    this.jqExpRemoveButton = ecl.createJqCommandButton({ iconType: "fa fa-times", label: "Remove", severity: display.Severity.DANGER });
                    var rcl = new display.CommandList(this.jqSummary.children(".rocket-simple-commands"), true);
                    this.jqRedFocusButton = rcl.createJqCommandButton({ iconType: "fa fa-pencil", label: "Edit", severity: display.Severity.WARNING });
                    this.jqRedRemoveButton = rcl.createJqCommandButton({ iconType: "fa fa-times", label: "Remove", severity: display.Severity.DANGER });
                }
                this.reduce();
                jqEntry.data("rocketImplEmbeddedEntry", this);
            }
            EmbeddedEntry.prototype.getEntryForm = function () {
                return this.entryForm;
            };
            EmbeddedEntry.prototype.onMove = function (callback) {
                if (this.readOnly)
                    return;
                this.jqExpMoveUpButton.click(function () {
                    callback(true);
                });
                this.jqExpMoveDownButton.click(function () {
                    callback(false);
                });
            };
            EmbeddedEntry.prototype.onRemove = function (callback) {
                if (this.readOnly)
                    return;
                this.jqExpRemoveButton.click(function () {
                    callback();
                });
                this.jqRedRemoveButton.click(function () {
                    callback();
                });
            };
            EmbeddedEntry.prototype.onFocus = function (callback) {
                this.jqRedFocusButton.click(function () {
                    callback();
                });
                this.bodyGroup.onShow(function () {
                    callback();
                });
            };
            EmbeddedEntry.prototype.getJQuery = function () {
                return this.entryGroup.getJQuery();
            };
            EmbeddedEntry.prototype.getExpandedCommandList = function () {
                return this.bodyGroup.getToolbar().getCommandList();
            };
            EmbeddedEntry.prototype.expand = function (asPartOfList) {
                if (asPartOfList === void 0) { asPartOfList = true; }
                this.entryGroup.show();
                this.jqSummary.hide();
                this.bodyGroup.show();
                this.entryGroup.getJQuery().addClass("rocket-group");
                if (asPartOfList) {
                    this.jqContextCommands.hide();
                }
                else {
                    this.jqContextCommands.show();
                }
                if (this.readOnly)
                    return;
                if (asPartOfList) {
                    this.jqExpMoveUpButton.show();
                    this.jqExpMoveDownButton.show();
                    this.jqExpRemoveButton.show();
                    this.jqContextCommands.hide();
                }
                else {
                    this.jqExpMoveUpButton.hide();
                    this.jqExpMoveDownButton.hide();
                    this.jqExpRemoveButton.hide();
                    this.jqContextCommands.show();
                }
            };
            EmbeddedEntry.prototype.reduce = function () {
                this.entryGroup.show();
                this.jqSummary.show();
                this.bodyGroup.hide();
                this.entryGroup.getJQuery().removeClass("rocket-group");
            };
            EmbeddedEntry.prototype.hide = function () {
                this.entryGroup.hide();
            };
            EmbeddedEntry.prototype.setOrderIndex = function (orderIndex) {
                this.jqOrderIndex.val(orderIndex);
            };
            EmbeddedEntry.prototype.getOrderIndex = function () {
                return parseInt(this.jqOrderIndex.val());
            };
            EmbeddedEntry.prototype.setMoveUpEnabled = function (enabled) {
                if (this.readOnly)
                    return;
                if (enabled) {
                    this.jqExpMoveUpButton.show();
                }
                else {
                    this.jqExpMoveUpButton.hide();
                }
            };
            EmbeddedEntry.prototype.setMoveDownEnabled = function (enabled) {
                if (this.readOnly)
                    return;
                if (enabled) {
                    this.jqExpMoveDownButton.show();
                }
                else {
                    this.jqExpMoveDownButton.hide();
                }
            };
            return EmbeddedEntry;
        }());
        var EmbeddedEntryRetriever = (function () {
            function EmbeddedEntryRetriever(lookupUrlStr, propertyPath, draftMode, startKey, keyPrefix) {
                if (startKey === void 0) { startKey = null; }
                if (keyPrefix === void 0) { keyPrefix = null; }
                this.preloadEnabled = false;
                this.preloadedResponseObjects = new Array();
                this.pendingLookups = new Array();
                this.urlStr = lookupUrlStr;
                this.propertyPath = propertyPath;
                this.draftMode = draftMode;
                this.startKey = startKey;
                this.keyPrefix = keyPrefix;
            }
            EmbeddedEntryRetriever.prototype.setPreloadEnabled = function (preloadEnabled) {
                if (!this.preloadEnabled && preloadEnabled && this.preloadedResponseObjects.length == 0) {
                    this.load();
                }
                this.preloadEnabled = preloadEnabled;
            };
            EmbeddedEntryRetriever.prototype.lookupNew = function (doneCallback, failCallback) {
                if (failCallback === void 0) { failCallback = null; }
                this.pendingLookups.push({ "doneCallback": doneCallback, "failCallback": failCallback });
                this.check();
                this.load();
            };
            EmbeddedEntryRetriever.prototype.check = function () {
                if (this.pendingLookups.length == 0 || this.preloadedResponseObjects.length == 0)
                    return;
                var pendingLookup = this.pendingLookups.shift();
                var embeddedEntry = new EmbeddedEntry($(n2n.ajah.analyze(this.preloadedResponseObjects.shift())), false);
                pendingLookup.doneCallback(embeddedEntry);
                n2n.ajah.update();
            };
            EmbeddedEntryRetriever.prototype.load = function () {
                var that = this;
                $.ajax({
                    "url": this.urlStr,
                    "data": {
                        "propertyPath": this.propertyPath + "[" + this.keyPrefix + (this.startKey++) + "]",
                        "draft": this.draftMode ? 1 : 0
                    },
                    "dataType": "json"
                }).fail(function (jqXHR, textStatus, data) {
                    if (jqXHR.status != 200) {
                        rocket.handleErrorResponse(this.urlStr, jqXHR);
                    }
                    that.failResponse();
                }).done(function (data, textStatus, jqXHR) {
                    that.doneResponse(data);
                });
            };
            EmbeddedEntryRetriever.prototype.failResponse = function () {
                if (this.pendingLookups.length == 0)
                    return;
                var pendingLookup = this.pendingLookups.shift();
                if (pendingLookup.failCallback !== null) {
                    pendingLookup.failCallback();
                }
            };
            EmbeddedEntryRetriever.prototype.doneResponse = function (data) {
                this.preloadedResponseObjects.push(data);
                this.check();
            };
            return EmbeddedEntryRetriever;
        }());
        var AddControlFactory = (function () {
            function AddControlFactory(embeddedEntryRetriever, label) {
                this.embeddedEntryRetriever = embeddedEntryRetriever;
                this.label = label;
            }
            AddControlFactory.prototype.create = function () {
                return AddControl.create(this.label, this.embeddedEntryRetriever);
            };
            return AddControlFactory;
        }());
        var AddControl = (function () {
            function AddControl(jqElem, embeddedEntryRetriever) {
                this.onNewEntryCallbacks = new Array();
                this.disposed = false;
                this.embeddedEntryRetriever = embeddedEntryRetriever;
                this.jqElem = jqElem;
                this.jqButton = jqElem.children("button");
                var that = this;
                this.jqButton.on("mouseenter", function () {
                    that.embeddedEntryRetriever.setPreloadEnabled(true);
                });
                this.jqButton.on("click", function () {
                    if (that.isLoading())
                        return;
                    that.block(true);
                    that.embeddedEntryRetriever.lookupNew(function (embeddedEntry) {
                        that.examine(embeddedEntry);
                    }, function () {
                        that.block(false);
                    });
                });
            }
            AddControl.prototype.getJQuery = function () {
                return this.jqElem;
            };
            AddControl.prototype.block = function (blocked) {
                if (blocked) {
                    this.jqButton.prop("disabled", true);
                    this.jqElem.addClass("rocket-impl-loading");
                }
                else {
                    this.jqButton.prop("disabled", false);
                    this.jqElem.removeClass("rocket-impl-loading");
                }
            };
            AddControl.prototype.examine = function (embeddedEntry) {
                this.block(false);
                if (!embeddedEntry.getEntryForm().hasTypeSelector()) {
                    this.fireCallbacks(embeddedEntry);
                    return;
                }
                this.examinedEmbeddedEntry = embeddedEntry;
            };
            AddControl.prototype.dispose = function () {
                this.disposed = true;
                this.jqElem.remove();
                if (this.examinedEmbeddedEntry !== null) {
                    this.fireCallbacks(this.examinedEmbeddedEntry);
                    this.examinedEmbeddedEntry = null;
                }
            };
            AddControl.prototype.isLoading = function () {
                return this.jqElem.hasClass("rocket-impl-loading");
            };
            AddControl.prototype.fireCallbacks = function (embeddedEntry) {
                if (this.disposed)
                    return;
                this.onNewEntryCallbacks.forEach(function (callback) {
                    callback(embeddedEntry);
                });
            };
            AddControl.prototype.onNewEmbeddedEntry = function (callback) {
                this.onNewEntryCallbacks.push(callback);
            };
            AddControl.create = function (label, embeddedEntryRetriever) {
                return new AddControl($("<div />").append($("<button />", { "text": label, "type": "button" })), embeddedEntryRetriever);
            };
            return AddControl;
        }());
    })(impl = rocket.impl || (rocket.impl = {}));
})(rocket || (rocket = {}));
var rocket;
(function (rocket) {
    var cmd;
    (function (cmd) {
        var Executor = (function () {
            function Executor(container) {
                this.container = container;
            }
            Executor.prototype.purifyExecConfig = function (config) {
                config.forceReload = config.forceReload === true;
                config.showLoadingContext = config.showLoadingContext !== false;
                config.createNewLayer = config.createNewLayer === true;
                if (!config.currentLayer) {
                    if (config.currentContext) {
                        config.currentLayer = config.currentContext.getLayer();
                    }
                    else {
                        config.currentLayer = this.container.getCurrentLayer();
                    }
                }
                if (!config.currentContext) {
                    config.currentContext = null;
                }
                return config;
            };
            Executor.prototype.exec = function (url, config) {
                if (config === void 0) { config = null; }
                config = this.purifyExecConfig(config);
                var targetContext = null;
                if (!config.createNewLayer) {
                    targetContext = config.currentLayer.getContextByUrl(url);
                }
                if (targetContext !== null) {
                    if (config.currentLayer.getCurrentContext() !== targetContext) {
                        config.currentLayer.pushHistoryEntry(targetContext.getUrl());
                    }
                    if (!config.forceReload) {
                        if (config.done) {
                            setTimeout(function () { config.done(new ExecResult(null, targetContext)); }, 0);
                        }
                        return;
                    }
                }
                if (targetContext === null && config.showLoadingContext) {
                    targetContext = config.currentLayer.createContext(url);
                    config.currentLayer.pushHistoryEntry(url);
                }
                if (targetContext !== null) {
                    targetContext.clear(true);
                }
                var that = this;
                $.ajax({
                    "url": url.toString(),
                    "dataType": "json"
                }).fail(function (jqXHR, textStatus, data) {
                    if (jqXHR.status != 200) {
                        config.currentLayer.getContainer().handleError(url.toString(), jqXHR.responseText);
                        return;
                    }
                    alert("Not yet implemented press F5 after ok.");
                }).done(function (data, textStatus, jqXHR) {
                    that.analyzeResponse(config.currentLayer, data, url.toString(), targetContext);
                    if (config.done) {
                        config.done(new ExecResult(null, targetContext));
                    }
                });
            };
            Executor.prototype.analyzeResponse = function (currentLayer, response, targetUrl, targetContext) {
                if (targetContext === void 0) { targetContext = null; }
                if (typeof response["additional"] === "object") {
                    if (this.execDirectives(currentLayer, response["additional"])) {
                        if (targetContext !== null)
                            targetContext.close();
                        return true;
                    }
                }
                if (targetContext === null) {
                    targetContext = currentLayer.getContextByUrl(targetUrl);
                    if (targetContext !== null) {
                        currentLayer.pushHistoryEntry(targetUrl);
                    }
                }
                if (targetContext === null) {
                    targetContext = currentLayer.createContext(targetUrl);
                    currentLayer.pushHistoryEntry(targetUrl);
                }
                targetContext.applyHtml(n2n.ajah.analyze(response));
                n2n.ajah.update();
            };
            Executor.prototype.execDirectives = function (currentLayer, info) {
                if (info.directive == "redirectBack") {
                    var index = currentLayer.getCurrentHistoryIndex();
                    if (index > 0) {
                        this.exec(currentLayer.getHistoryUrlByIndex(index - 1), { "currentLayer": currentLayer });
                        return true;
                    }
                    if (info.fallbackUrl) {
                        this.exec(info.fallbackUrl, { "currentLayer": currentLayer });
                        return true;
                    }
                    currentLayer.close();
                }
                return false;
            };
            return Executor;
        }());
        cmd.Executor = Executor;
        var ExecResult = (function () {
            function ExecResult(order, _context) {
                this._context = _context;
            }
            Object.defineProperty(ExecResult.prototype, "context", {
                get: function () {
                    return this._context;
                },
                enumerable: true,
                configurable: true
            });
            return ExecResult;
        }());
        cmd.ExecResult = ExecResult;
    })(cmd = rocket.cmd || (rocket.cmd = {}));
})(rocket || (rocket = {}));
var rocket;
(function (rocket) {
    var util;
    (function (util) {
        var CallbackRegistry = (function () {
            function CallbackRegistry() {
                this.callbackMap = new Array();
            }
            CallbackRegistry.prototype.register = function (nature, callback) {
                if (this.callbackMap[nature] === undefined) {
                    this.callbackMap[nature] = new Array();
                }
                this.callbackMap[nature].push(callback);
            };
            CallbackRegistry.prototype.unregister = function (nature, callback) {
                if (this.callbackMap[nature] === undefined) {
                    return;
                }
                for (var i in this.callbackMap[nature]) {
                    if (this.callbackMap[nature][i] === callback) {
                        this.callbackMap[nature].splice(i, 1);
                        return;
                    }
                }
            };
            CallbackRegistry.prototype.filter = function (nature) {
                if (this.callbackMap[nature] === undefined) {
                    return new Array();
                }
                return this.callbackMap[nature];
            };
            return CallbackRegistry;
        }());
        util.CallbackRegistry = CallbackRegistry;
    })(util = rocket.util || (rocket.util = {}));
})(rocket || (rocket = {}));
var rocket;
(function (rocket) {
    var container;
    var executor;
    var initializer;
    jQuery(document).ready(function ($) {
        var jqContainer = $("#rocket-content-container");
        container = new rocket.cmd.Container(jqContainer);
        executor = new rocket.cmd.Executor(container);
        var monitor = new rocket.cmd.Monitor(executor);
        monitor.scanMain($("#rocket-global-nav"), container.getMainLayer());
        monitor.scan(jqContainer);
        n2n.dispatch.registerCallback(function () {
            monitor.scan(jqContainer);
        });
        initializer = new rocket.display.Initializer(container, jqContainer.data("error-tab-title"), jqContainer.data("display-error-label"));
        initializer.scan();
        n2n.dispatch.registerCallback(function () {
            initializer.scan();
        });
        (function () {
            $(".rocket-impl-overview").each(function () {
                rocket.impl.OverviewContext.scan($(this));
            });
            n2n.dispatch.registerCallback(function () {
                $(".rocket-impl-overview").each(function () {
                    rocket.impl.OverviewContext.scan($(this));
                });
            });
        })();
        (function () {
            $("form.rocket-impl-form").each(function () {
                rocket.impl.Form.scan($(this));
            });
            n2n.dispatch.registerCallback(function () {
                $("form.rocket-impl-form").each(function () {
                    rocket.impl.Form.scan($(this));
                });
            });
        })();
        (function () {
            $(".rocket-impl-to-many").each(function () {
                rocket.impl.ToMany.from($(this));
            });
            n2n.dispatch.registerCallback(function () {
                $(".rocket-impl-to-many").each(function () {
                    rocket.impl.ToMany.from($(this));
                });
            });
        })();
    });
    function scan(context) {
        if (context === void 0) { context = null; }
        initializer.scan();
    }
    rocket.scan = scan;
    function getContainer() {
        return container;
    }
    rocket.getContainer = getContainer;
    function layerOf(elem) {
        return rocket.cmd.Layer.findFrom($(elem));
    }
    rocket.layerOf = layerOf;
    function contextOf(elem) {
        return rocket.cmd.Context.findFrom($(elem));
    }
    rocket.contextOf = contextOf;
    function handleErrorResponse(url, responseObject) {
        container.handleError(url, responseObject.responseText);
    }
    rocket.handleErrorResponse = handleErrorResponse;
    function exec(url, config) {
        if (config === void 0) { config = null; }
        executor.exec(url, config);
    }
    rocket.exec = exec;
    function analyzeResponse(currentLayer, response, targetUrl, targetContext) {
        if (targetContext === void 0) { targetContext = null; }
        return executor.analyzeResponse(currentLayer, response, targetUrl, targetContext);
    }
    rocket.analyzeResponse = analyzeResponse;
})(rocket || (rocket = {}));
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 *
 */
var rocket;
(function (rocket) {
    var impl;
    (function (impl) {
        var $ = jQuery;
        var Form = (function () {
            function Form(jqForm) {
                this.jqForm = jqForm;
            }
            Form.prototype.observe = function () {
                var that = this;
                this.jqForm.submit(function () {
                    that.submit(new FormData(this));
                    return false;
                });
                var that = this;
                this.jqForm.find("input[type=submit], button[type=submit]").each(function () {
                    $(this).click(function () {
                        var formData = new FormData(that.jqForm.get(0));
                        formData.append(this.name, this.value);
                        that.submit(formData);
                        return false;
                    });
                });
            };
            Form.prototype.submit = function (formData) {
                var that = this;
                var url = this.jqForm.attr("action");
                $.ajax({
                    "url": url,
                    "type": "POST",
                    "data": formData,
                    "cache": false,
                    "processData": false,
                    "contentType": false,
                    "dataType": "json",
                    "success": function (data, textStatus, jqXHR) {
                        rocket.analyzeResponse(rocket.layerOf(that.jqForm.get(0)), data, url);
                    },
                    "error": function (jqXHR, textStatus, errorThrown) {
                        rocket.handleErrorResponse(url, jqXHR);
                    }
                });
            };
            Form.scan = function (jqForm) {
                var form = jqForm.data("rocketImplForm");
                if (form)
                    return form;
                form = new Form(jqForm);
                jqForm.data("rocketImplForm", form);
                form.observe();
                return form;
            };
            return Form;
        }());
        impl.Form = Form;
    })(impl = rocket.impl || (rocket.impl = {}));
})(rocket || (rocket = {}));
var rocket;
(function (rocket) {
    var cmd;
    (function (cmd) {
        var display = rocket.display;
        var util = rocket.util;
        var Container = (function () {
            function Container(jqContainer) {
                this.jqErrorLayer = null;
                this.jqContainer = jqContainer;
                this.layers = new Array();
                var layer = new Layer(this.jqContainer.find(".rocket-main-layer"), this.layers.length, this);
                this.layers.push(layer);
                var that = this;
                layer.onNewHistoryEntry(function (historyIndex, context) {
                    var stateObj = {
                        "type": "rocketContext",
                        "level": layer.getLevel(),
                        "url": context.getUrl(),
                        "historyIndex": historyIndex
                    };
                    history.pushState(stateObj, "seite 2", context.getUrl().toString());
                });
                $(window).bind("popstate", function (e) {
                    if (that.jqErrorLayer) {
                        that.jqErrorLayer.remove();
                        that.jqErrorLayer = null;
                    }
                    if (!history.state) {
                        layer.go(0, window.location.href);
                        return;
                    }
                    if (history.state.type != "rocketContext" || history.state.level != 0) {
                        return;
                    }
                    if (!layer.go(history.state.historyIndex, history.state.url)) {
                    }
                });
            }
            Container.prototype.handleError = function (url, html) {
                var stateObj = {
                    "type": "rocketErrorContext",
                    "url": url
                };
                if (this.jqErrorLayer) {
                    this.jqErrorLayer.remove();
                    history.replaceState(stateObj, "n2n Rocket", url);
                }
                else {
                    history.pushState(stateObj, "n2n Rocket", url);
                }
                this.jqErrorLayer = $("<div />", { "class": "rocket-error-layer" });
                this.jqErrorLayer.css({ "position": "fixed", "top": 0, "left": 0, "right": 0, "bottom": 0 });
                this.jqContainer.append(this.jqErrorLayer);
                var iframe = document.createElement("iframe");
                this.jqErrorLayer.append(iframe);
                iframe.contentWindow.document.open();
                iframe.contentWindow.document.write(html);
                iframe.contentWindow.document.close();
                $(iframe).css({ "width": "100%", "height": "100%" });
            };
            Container.prototype.getMainLayer = function () {
                if (this.layers.length > 0) {
                    return this.layers[0];
                }
                throw new Error("Container empty.");
            };
            Container.prototype.getCurrentLayer = function () {
                if (this.layers.length == 0) {
                    throw new Error("Container empty.");
                }
                var layer = null;
                for (var i in this.layers) {
                    if (this.layers[i].isVisible()) {
                        layer = this.layers[i];
                    }
                }
                if (layer !== null)
                    return layer;
                return this.layers[this.layers.length - 1];
            };
            Container.prototype.createLayer = function (dependentContext) {
                if (dependentContext === void 0) { dependentContext = null; }
                var jqLayer = $("<div />", {
                    "class": "rocket-layer"
                });
                this.jqContainer.append(jqLayer);
                var layer = new Layer(jqLayer, this.layers.length, this);
                this.layers.push(layer);
                var jqToolbar = $("<div />", {
                    "class": "rocket-layer-toolbar rocket-simple-commands"
                });
                jqLayer.append(jqToolbar);
                var jqButton = $("<button />", {
                    "class": "btn btn-danger"
                }).append($("<i />", {
                    "class": "fa fa-times"
                })).click(function () {
                    layer.close();
                });
                jqToolbar.append(jqButton);
                if (dependentContext === null) {
                    return layer;
                }
                dependentContext.onClose(function () {
                    layer.close();
                });
                dependentContext.onHide(function () {
                    layer.hide();
                });
                dependentContext.onShow(function () {
                    layer.show();
                });
                return layer;
            };
            Container.prototype.getAllContexts = function () {
                var contexts = new Array();
                for (var i in this.layers) {
                    var layerContexts = this.layers[i].getContexts();
                    for (var j in layerContexts) {
                        contexts.push(layerContexts[j]);
                    }
                }
                return contexts;
            };
            return Container;
        }());
        cmd.Container = Container;
        var Layer = (function () {
            function Layer(jqContentGroup, level, container) {
                this.currentHistoryIndex = null;
                this.visible = true;
                this.contexts = new Array();
                this.onNewContextCallbacks = new Array();
                this.onNewHistoryEntryCallbacks = new Array();
                this.historyUrls = new Array();
                this.jqLayer = jqContentGroup;
                this.level = level;
                this.container = container;
                jqContentGroup.addClass("rocket-layer");
                jqContentGroup.data("rocketLayer", this);
                var jqContext = jqContentGroup.children(".rocket-context");
                if (jqContext.length > 0) {
                    var context = new Context(jqContext, Url.create(window.location.href), this);
                    this.addContext(context);
                    this.pushHistoryEntry(context.getUrl());
                }
            }
            Layer.prototype.getContainer = function () {
                return this.container;
            };
            Layer.prototype.isVisible = function () {
                return this.visible;
            };
            Layer.prototype.show = function () {
                this.visible = true;
                this.jqLayer.show();
            };
            Layer.prototype.hide = function () {
                this.visible = false;
                this.jqLayer.hide();
            };
            Layer.prototype.getLevel = function () {
                return this.level;
            };
            Layer.prototype.getCurrentContext = function () {
                if (this.contexts.length == 0) {
                    throw new Error("no context avaialble");
                }
                var url = this.historyUrls[this.currentHistoryIndex];
                for (var i in this.contexts) {
                    if (this.contexts[i].getUrl().equals(url)) {
                        return this.contexts[i];
                    }
                }
                return null;
            };
            Layer.prototype.getContexts = function () {
                return this.contexts;
            };
            Layer.prototype.getCurrentHistoryIndex = function () {
                return this.currentHistoryIndex;
            };
            Layer.prototype.addContext = function (context) {
                this.contexts.push(context);
                var that = this;
                context.onClose(function (context) {
                    for (var i in that.contexts) {
                        if (that.contexts[i] !== context)
                            continue;
                        that.contexts.splice(parseInt(i), 1);
                        break;
                    }
                });
                for (var i in this.onNewContextCallbacks) {
                    this.onNewContextCallbacks[i](context);
                }
            };
            Layer.prototype.pushHistoryEntry = function (url) {
                url = Url.create(url);
                var context = this.getContextByUrl(url);
                if (context === null) {
                    throw new Error("Not context with this url found: " + url);
                }
                this.currentHistoryIndex = this.historyUrls.length;
                this.historyUrls.push(context.getUrl());
                for (var i in this.onNewHistoryEntryCallbacks) {
                    this.onNewHistoryEntryCallbacks[i](this.currentHistoryIndex, context);
                }
                this.switchToContext(context);
            };
            Layer.prototype.go = function (historyIndex, urlExpr) {
                var url = Url.create(urlExpr);
                if (this.historyUrls.length < (historyIndex + 1)) {
                    throw new Error("Invalid history index: " + historyIndex);
                }
                if (this.historyUrls[historyIndex].equals(url)) {
                    throw new Error("Url missmatch for history index " + historyIndex + ". Url: " + url + " History url: "
                        + this.historyUrls[historyIndex]);
                }
                this.currentHistoryIndex = historyIndex;
                var context = this.getContextByUrl(this.historyUrls[historyIndex]);
                if (context === null)
                    return false;
                this.switchToContext(context);
                return true;
            };
            Layer.prototype.getHistoryUrlByIndex = function (historyIndex) {
                if (this.historyUrls.length <= historyIndex)
                    return null;
                return this.historyUrls[historyIndex];
            };
            Layer.prototype.getContextByUrl = function (urlExpr) {
                var url = Url.create(urlExpr);
                for (var i in this.contexts) {
                    if (this.contexts[i].getUrl().equals(url)) {
                        return this.contexts[i];
                    }
                }
                return null;
            };
            Layer.prototype.switchToContext = function (context) {
                for (var i in this.contexts) {
                    if (this.contexts[i] === context) {
                        context.show();
                    }
                    else {
                        this.contexts[i].hide();
                    }
                }
            };
            Layer.prototype.createContext = function (urlExpr) {
                var url = Url.create(urlExpr);
                if (this.getContextByUrl(url)) {
                    throw new Error("Context with url already available: " + url);
                }
                var jqContent = $("<div />");
                this.jqLayer.append(jqContent);
                var context = new Context(jqContent, url, this);
                this.addContext(context);
                return context;
            };
            Layer.prototype.clear = function () {
                for (var i in this.contexts) {
                    this.contexts[i].close();
                }
            };
            Layer.prototype.close = function () {
                var context;
                while (undefined !== (context = this.contexts.pop())) {
                    context.close();
                }
                this.contexts = new Array();
                this.jqLayer.remove();
            };
            Layer.prototype.onNewContext = function (onNewContextCallback) {
                this.onNewContextCallbacks.push(onNewContextCallback);
            };
            Layer.prototype.onNewHistoryEntry = function (onNewHistoryEntryCallback) {
                this.onNewHistoryEntryCallbacks.push(onNewHistoryEntryCallback);
            };
            Layer.findFrom = function (jqElem) {
                if (!jqElem.hasClass(".rocket-layer")) {
                    jqElem = jqElem.parents(".rocket-layer");
                }
                var layer = jqElem.data("rocketLayer");
                if (layer === undefined) {
                    return null;
                }
                return layer;
            };
            return Layer;
        }());
        cmd.Layer = Layer;
        var Context = (function () {
            function Context(jqContext, url, layer) {
                this.onShowCallbacks = new Array();
                this.onHideCallbacks = new Array();
                this.onCloseCallbacks = new Array();
                this.whenContentChangedCallbacks = new Array();
                this.callbackRegistery = new util.CallbackRegistry();
                this.jqContext = jqContext;
                this.url = url;
                this.layer = layer;
                jqContext.addClass("rocket-context");
                jqContext.data("rocketContext", this);
                this.reset();
                this.hide();
            }
            Context.prototype.getLayer = function () {
                return this.layer;
            };
            Context.prototype.getJQuery = function () {
                return this.jqContext;
            };
            Context.prototype.getUrl = function () {
                return this.url;
            };
            Context.prototype.ensureNotClosed = function () {
                if (this.jqContext !== null)
                    return;
                throw new Error("Context already closed.");
            };
            Context.prototype.close = function () {
                var callback;
                while (undefined !== (callback = this.onCloseCallbacks.shift())) {
                    callback(this);
                }
                this.jqContext.remove();
                this.jqContext = null;
            };
            Context.prototype.show = function () {
                this.jqContext.show();
                var callback;
                while (undefined !== (callback = this.onShowCallbacks.shift())) {
                    callback(this);
                }
            };
            Context.prototype.hide = function () {
                this.jqContext.hide();
                var callback;
                while (undefined !== (callback = this.onShowCallbacks.shift())) {
                    callback(this);
                }
            };
            Context.prototype.reset = function () {
                this.additionalTabManager = new AdditionalTabManager(this);
                this.menu = new Menu(this);
            };
            Context.prototype.clear = function (loading) {
                if (loading === void 0) { loading = false; }
                this.jqContext.empty();
                this.jqContext.addClass("rocket-loading");
                this.reset();
            };
            Context.prototype.applyHtml = function (html) {
                this.endLoading();
                this.jqContext.html(html);
                this.reset();
            };
            Context.prototype.isLoading = function () {
                return this.jqContext.hasClass("rocket-loading");
            };
            Context.prototype.endLoading = function () {
                this.jqContext.removeClass("rocket-loading");
            };
            Context.prototype.applyContent = function (jqContent) {
                this.endLoading();
                this.jqContext.append(jqContent);
                this.reset();
                var context = this;
                this.callbackRegistery.filter(Context.EventType.CONTENT_CHANGED.toString()).forEach(function (callback) {
                    callback(context);
                });
            };
            Context.prototype.onShow = function (callback) {
                this.onShowCallbacks.push(callback);
            };
            Context.prototype.onHide = function (callback) {
                this.onHideCallbacks.push(callback);
            };
            Context.prototype.onClose = function (onCloseCallback) {
                this.onCloseCallbacks.push(onCloseCallback);
            };
            Context.prototype.whenContentChanged = function (whenContentChangedCallback) {
                this.whenContentChangedCallbacks.push(whenContentChangedCallback);
            };
            Context.prototype.on = function (eventType, callback) {
                this.callbackRegistery.register(eventType.toString(), callback);
            };
            Context.prototype.off = function (eventType, callback) {
                this.callbackRegistery.unregister(eventType.toString(), callback);
            };
            Context.prototype.createAdditionalTab = function (title, prepend) {
                if (prepend === void 0) { prepend = false; }
                return this.additionalTabManager.createTab(title, prepend);
            };
            Context.prototype.getMenu = function () {
                return this.menu;
            };
            Context.findFrom = function (jqElem) {
                if (!jqElem.hasClass(".rocket-context")) {
                    jqElem = jqElem.parents(".rocket-context");
                }
                var context = jqElem.data("rocketContext");
                if (context)
                    return context;
                return null;
            };
            return Context;
        }());
        cmd.Context = Context;
        var AdditionalTabManager = (function () {
            function AdditionalTabManager(context) {
                this.jqAdditional = null;
                this.context = context;
                this.tabs = new Array();
            }
            AdditionalTabManager.prototype.createTab = function (title, prepend) {
                if (prepend === void 0) { prepend = false; }
                this.setupAdditional();
                var jqNavItem = $("<li />", {
                    "text": title
                });
                var jqContent = $("<div />", {
                    "class": "rocket-additional-content"
                });
                if (prepend) {
                    this.jqAdditional.find(".rocket-additional-nav").prepend(jqNavItem);
                }
                else {
                    this.jqAdditional.find(".rocket-additional-nav").append(jqNavItem);
                }
                this.jqAdditional.find(".rocket-additional-container").append(jqContent);
                var tab = new AdditionalTab(jqNavItem, jqContent);
                this.tabs.push(tab);
                var that = this;
                tab.onShow(function () {
                    for (var i in that.tabs) {
                        if (that.tabs[i] === tab)
                            continue;
                        this.tabs[i].hide();
                    }
                });
                tab.onDispose(function () {
                    that.removeTab(tab);
                });
                if (this.tabs.length == 1) {
                    tab.show();
                }
                return tab;
            };
            AdditionalTabManager.prototype.removeTab = function (tab) {
                for (var i in this.tabs) {
                    if (this.tabs[i] !== tab)
                        continue;
                    this.tabs.splice(parseInt(i), 1);
                    if (this.tabs.length == 0) {
                        this.setdownAdditional();
                        return;
                    }
                    if (tab.isActive()) {
                        this.tabs[0].show();
                    }
                    return;
                }
            };
            AdditionalTabManager.prototype.setupAdditional = function () {
                if (this.jqAdditional !== null)
                    return;
                var jqContext = this.context.getJQuery();
                jqContext.addClass("rocket-contains-additional");
                this.jqAdditional = $("<div />", {
                    "class": "rocket-additional"
                });
                this.jqAdditional.append($("<ul />", { "class": "rocket-additional-nav" }));
                this.jqAdditional.append($("<div />", { "class": "rocket-additional-container" }));
                jqContext.append(this.jqAdditional);
            };
            AdditionalTabManager.prototype.setdownAdditional = function () {
                if (this.jqAdditional === null)
                    return;
                this.context.getJQuery().removeClass("rocket-contains-additional");
                this.jqAdditional.remove();
                this.jqAdditional = null;
            };
            return AdditionalTabManager;
        }());
        var AdditionalTab = (function () {
            function AdditionalTab(jqNavItem, jqContent) {
                this.active = false;
                this.onShowCallbacks = new Array();
                this.onHideCallbacks = new Array();
                this.onDisposeCallbacks = new Array();
                this.jqNavItem = jqNavItem;
                this.jqContent = jqContent;
                this.jqNavItem.click(this.show);
                this.jqContent.hide();
            }
            AdditionalTab.prototype.getJqNavItem = function () {
                return this.jqNavItem;
            };
            AdditionalTab.prototype.getJqContent = function () {
                return this.jqContent;
            };
            AdditionalTab.prototype.isActive = function () {
                return this.active;
            };
            AdditionalTab.prototype.show = function () {
                this.active = true;
                this.jqNavItem.addClass("rocket-active");
                this.jqContent.show();
                for (var i in this.onShowCallbacks) {
                    this.onShowCallbacks[i](this);
                }
            };
            AdditionalTab.prototype.hide = function () {
                this.active = false;
                this.jqContent.hide();
                this.jqNavItem.removeClass("rocket-active");
                for (var i in this.onHideCallbacks) {
                    this.onHideCallbacks[i](this);
                }
            };
            AdditionalTab.prototype.dispose = function () {
                this.jqNavItem.remove();
                this.jqContent.remove();
                for (var i in this.onDisposeCallbacks) {
                    this.onDisposeCallbacks[i](this);
                }
            };
            AdditionalTab.prototype.onShow = function (callback) {
                this.onShowCallbacks.push(callback);
            };
            AdditionalTab.prototype.onHide = function (callback) {
                this.onHideCallbacks.push(callback);
            };
            AdditionalTab.prototype.onDispose = function (callback) {
                this.onDisposeCallbacks.push(callback);
            };
            return AdditionalTab;
        }());
        cmd.AdditionalTab = AdditionalTab;
        var Menu = (function () {
            function Menu(context) {
                this.commandList = null;
                this.context = context;
            }
            Menu.prototype.getCommandList = function () {
                if (this.commandList !== null) {
                    return this.commandList;
                }
                var jqCommandList = this.context.getJQuery().find(".rocket-context-commands");
                if (jqCommandList.length == 0) {
                    jqCommandList = $("<div />", {
                        "class": "rocket-context-commands"
                    });
                    this.context.getJQuery().append(jqCommandList);
                }
                return this.commandList = new display.CommandList(jqCommandList);
            };
            return Menu;
        }());
        cmd.Menu = Menu;
        var Url = (function () {
            function Url(urlStr) {
                this.urlStr = urlStr;
            }
            Url.prototype.toString = function () {
                return this.urlStr;
            };
            Url.prototype.equals = function (url) {
                return this.urlStr == url.urlStr;
            };
            Url.create = function (urlExpression) {
                if (urlExpression instanceof Url) {
                    return urlExpression;
                }
                return new Url(Url.absoluteStr(urlExpression));
            };
            Url.absoluteStr = function (urlExpression) {
                if (urlExpression instanceof Url) {
                    return urlExpression.toString();
                }
                var urlStr = urlExpression;
                if (!/^(?:\/|[a-z]+:\/\/)/.test(urlStr)) {
                    return window.location.toString().replace(/\/+$/, "") + "/" + urlStr;
                }
                if (!/^(?:[a-z]+:)?\/\//.test(urlStr)) {
                    return window.location.protocol + "//" + window.location.host + urlStr;
                }
                return urlStr;
            };
            return Url;
        }());
        cmd.Url = Url;
    })(cmd = rocket.cmd || (rocket.cmd = {}));
})(rocket || (rocket = {}));
var rocket;
(function (rocket) {
    var display;
    (function (display) {
        var EntryForm = (function () {
            function EntryForm(jqEntryForm) {
                this.jqEntryForm = jqEntryForm;
            }
            EntryForm.prototype.getJQuery = function () {
                return this.jqEntryForm;
            };
            EntryForm.prototype.hasTypeSelector = function () {
                return this.jqEntryForm.find(".rocket-type-dependent-entry-form").length > 0;
            };
            EntryForm.from = function (jqElem, create) {
                if (create === void 0) { create = false; }
                var entryForm = jqElem.data("rocketEntryForm");
                if (entryForm instanceof EntryForm)
                    return entryForm;
                if (!create)
                    return null;
                entryForm = new EntryForm(jqElem);
                jqElem.data("rocketEntryForm", entryForm);
                return entryForm;
            };
            return EntryForm;
        }());
        display.EntryForm = EntryForm;
    })(display = rocket.display || (rocket.display = {}));
})(rocket || (rocket = {}));
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 *
 */
var rocket;
(function (rocket) {
    var impl;
    (function (impl) {
        var $ = jQuery;
        var OverviewContext = (function () {
            function OverviewContext(jqContainer) {
                this.jqContainer = jqContainer;
            }
            OverviewContext.prototype.initPageNav = function () {
            };
            OverviewContext.scan = function (jqContainer) {
                if (jqContainer.data("rocketImplOverviewContext"))
                    return null;
                var overviewContext = new OverviewContext(jqContainer);
                jqContainer.data("rocketImplOverviewContext", overviewContext);
                jqContainer.data("content-url");
                var jqForm = jqContainer.children("form");
                var pagination = new Pagination(jqContainer.data("num-pages"), jqContainer.data("current-page"));
                pagination.draw(jqForm.children(".rocket-context-commands"));
                var fixedHeader = new FixedHeader(jqContainer.data("num-entries"));
                fixedHeader.draw(jqContainer.children(".rocket-impl-overview-tools"), jqForm.find("table:first"));
                return overviewContext;
            };
            return OverviewContext;
        }());
        impl.OverviewContext = OverviewContext;
        var Pagination = (function () {
            function Pagination(numPages, currentPageNo) {
                this.numPages = numPages;
                this.currentPageNo = currentPageNo;
            }
            Pagination.prototype.getCurrentPageNo = function () {
                return this.currentPageNo;
            };
            Pagination.prototype.getNumPages = function () {
                return this.numPages;
            };
            Pagination.prototype.goTo = function (pageNo) {
                alert(pageNo);
            };
            Pagination.prototype.draw = function (jqContainer) {
                var that = this;
                this.jqPagination = $("<div />", { "class": "rocket-impl-overview-pagination" });
                jqContainer.append(this.jqPagination);
                this.jqPagination.append($("<a />", {
                    "href": "#",
                    "class": "rocket-impl-pagination-first rocket-control",
                    "click": function () { that.goTo(1); }
                }).append($("<i />", {
                    "class": "fa fa-step-backward"
                })));
                this.jqPagination.append($("<button />", {
                    "class": "rocket-impl-pagination-prev rocket-control",
                    "click": function () { that.goTo(that.getCurrentPageNo() - 1); }
                }).append($("<i />", {
                    "class": "fa fa-chevron-left"
                })));
                this.jqInput = $("<input />", {
                    "class": "rocket-impl-pagination-no",
                    "type": "text",
                    "value": this.currentPageNo
                });
                this.jqPagination.append(this.jqInput);
                this.jqPagination.append($("<button />", {
                    "class": "rocket-impl-pagination-next rocket-control",
                    "click": function () { that.goTo(that.getCurrentPageNo() + 1); }
                }).append($("<i />", {
                    "class": "fa fa-chevron-right"
                })));
                this.jqPagination.append($("<button />", {
                    "href": "#",
                    "class": "rocket-impl-pagination-last rocket-control",
                    "click": function () { that.goTo(that.getNumPages()); }
                }).append($("<i />", {
                    "class": "fa fa-step-forward"
                })));
            };
            return Pagination;
        }());
        var FixedHeader = (function () {
            function FixedHeader(numEntries) {
                this.fixed = false;
                this.numEntries = numEntries;
            }
            FixedHeader.prototype.getNumEntries = function () {
                return this.numEntries;
            };
            FixedHeader.prototype.draw = function (jqHeader, jqTable) {
                this.jqHeader = jqHeader;
                this.jqTable = jqTable;
                this.cloneTableHeader();
                var that = this;
                $("#rocket-content-container").scroll(function () {
                    that.scrolled();
                });
                //			var headerOffset = this.jqHeader.offset().top;
                //			var headerHeight = this.jqHeader.height();
                //			var headerWidth = this.jqHeader.width();
                //			this.jqHeader.css({"position": "fixed", "top": headerOffset});
                //			this.jqHeader.parent().css("padding-top", headerHeight);
                this.calcDimensions();
                $(window).resize(function () {
                    that.calcDimensions();
                });
            };
            FixedHeader.prototype.calcDimensions = function () {
                this.jqHeader.parent().css("padding-top", null);
                this.jqHeader.css("position", "relative");
                var headerOffset = this.jqHeader.offset();
                this.fixedCssAttrs = {
                    "position": "fixed",
                    "top": $("#rocket-content-container").offset().top,
                    "left": headerOffset.left,
                    "right": $(window).width() - (headerOffset.left + this.jqHeader.outerWidth())
                };
                this.scrolled();
            };
            FixedHeader.prototype.scrolled = function () {
                var headerHeight = this.jqHeader.children().outerHeight();
                if (this.jqTable.offset().top <= this.fixedCssAttrs.top + headerHeight) {
                    if (this.fixed)
                        return;
                    this.fixed = true;
                    this.jqHeader.css(this.fixedCssAttrs);
                    this.jqHeader.parent().css("padding-top", headerHeight);
                    this.jqTableClone.show();
                }
                else {
                    if (!this.fixed)
                        return;
                    this.fixed = false;
                    this.jqHeader.css({
                        "position": "relative",
                        "top": "",
                        "left": "",
                        "right": ""
                    });
                    this.jqHeader.parent().css("padding-top", "");
                    this.jqTableClone.hide();
                }
            };
            FixedHeader.prototype.cloneTableHeader = function () {
                this.jqTableClone = this.jqTable.clone();
                this.jqTableClone.css("margin-bottom", 0);
                this.jqTableClone.children("tbody").remove();
                this.jqHeader.append(this.jqTableClone);
                this.jqTableClone.hide();
                var jqClonedChildren = this.jqTableClone.children("thead").children("tr").children();
                this.jqTable.children("thead").children("tr").children().each(function (index) {
                    jqClonedChildren.eq(index).innerWidth($(this).innerWidth());
                    //				jqClonedChildren.css({
                    //					"boxSizing": "border-box"	
                    //				});
                });
                //			this.jqTable.children("thead").hide();
            };
            return FixedHeader;
        }());
    })(impl = rocket.impl || (rocket.impl = {}));
})(rocket || (rocket = {}));
var rocket;
(function (rocket) {
    var cmd;
    (function (cmd) {
        var Context;
        (function (Context) {
            (function (EventType) {
                EventType[EventType["CONTENT_CHANGED"] = "contentChanged"] = "CONTENT_CHANGED";
            })(Context.EventType || (Context.EventType = {}));
            var EventType = Context.EventType;
        })(Context = cmd.Context || (cmd.Context = {}));
    })(cmd = rocket.cmd || (rocket.cmd = {}));
})(rocket || (rocket = {}));
var rocket;
(function (rocket) {
    var display;
    (function (display) {
        (function (Severity) {
            Severity[Severity["PRIMARY"] = "primary"] = "PRIMARY";
            Severity[Severity["SECONDARY"] = "secondary"] = "SECONDARY";
            Severity[Severity["SUCCESS"] = "success"] = "SUCCESS";
            Severity[Severity["DANGER"] = "danger"] = "DANGER";
            Severity[Severity["INFO"] = "info"] = "INFO";
            Severity[Severity["WARNING"] = "warning"] = "WARNING";
        })(display.Severity || (display.Severity = {}));
        var Severity = display.Severity;
    })(display = rocket.display || (rocket.display = {}));
})(rocket || (rocket = {}));
