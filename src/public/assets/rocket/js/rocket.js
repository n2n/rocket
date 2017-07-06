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
                    errorIndex.dispose();
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
                jqContext.find(".rocket-group-simple, .rocket-group-main, .rocket-group-autonomic, .rocket-field").each(function () {
                    var jqElem = $(this);
                    var structureElement = display.StructureElement.from(jqElem);
                    if (structureElement !== null)
                        return;
                    if (!jqElem.hasClass("rocket-group-main")) {
                        Initializer.createStructureElement(jqElem);
                        return;
                    }
                    Initializer.scanGroupNav(jqElem.parent());
                });
                var errorIndex = null;
                jqContext.find(".rocket-message-error").each(function () {
                    var structureElement = display.StructureElement.findFrom($(this));
                    if (errorIndex === null) {
                        errorIndex = new ErrorIndex(context.createAdditionalTab(that.errorTabTitle), that.displayErrorLabel);
                    }
                    errorIndex.addError(structureElement, $(this).text());
                });
            };
            Initializer.createStructureElement = function (jqElem) {
                var structureElement = display.StructureElement.from(jqElem, jqElem.hasClass("rocket-group-simple") || jqElem.hasClass("rocket-group-main")
                    || jqElem.hasClass("rocket-group-autonomic"), jqElem.hasClass("rocket-field"));
                var parentStructureElement = display.StructureElement.findFrom(jqElem);
                if (parentStructureElement !== null) {
                    parentStructureElement.addChild(structureElement);
                }
                return structureElement;
            };
            Initializer.scanGroupNav = function (jqContainer) {
                var curGroupNav = null;
                jqContainer.children(".rocket-group-simple, .rocket-group-main, .rocket-group-autonomic").each(function () {
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
                        curGroupNav.registerGroup(Initializer.createStructureElement(jqElem));
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
            ErrorIndex.prototype.addError = function (field, errorMessage) {
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
                jqElem.mouseenter(function () {
                    field.highlight();
                });
                jqElem.mouseleave(function () {
                    field.unhighlight(clicked);
                    clicked = false;
                });
                jqElem.click(function () {
                    clicked = true;
                    field.show();
                    field.scrollTo();
                });
            };
            return ErrorIndex;
        }());
    })(display = rocket.display || (rocket.display = {}));
})(rocket || (rocket = {}));
var rocket;
(function (rocket) {
    var cmd;
    (function (cmd) {
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
                    history.pushState(stateObj, "seite 2", context.getUrl());
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
                    var context = new Context(jqContext, window.location.href, this);
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
                    if (this.contexts[i].getUrl() == url) {
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
                        delete that.contexts[i];
                        break;
                    }
                });
                for (var i in this.onNewContextCallbacks) {
                    this.onNewContextCallbacks[i](context);
                }
            };
            Layer.prototype.pushHistoryEntry = function (url) {
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
            Layer.prototype.go = function (historyIndex, url) {
                if (this.historyUrls.length < (historyIndex + 1)) {
                    throw new Error("Invalid history index: " + historyIndex);
                }
                if (this.historyUrls[historyIndex] != url) {
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
            Layer.prototype.getContextByUrl = function (url) {
                for (var i in this.contexts) {
                    if (this.contexts[i].getUrl() == url) {
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
            Layer.prototype.createContext = function (url) {
                if (this.getContextByUrl(url)) {
                    throw new Error("Context with url already available: " + url);
                }
                var jqContent = $("<div/>");
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
                throw new Error("layer close not yet implemented.");
            };
            Layer.prototype.dispose = function () {
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
                this.jqContext = jqContext;
                this.url = url;
                this.layer = layer;
                this.additionalTabManager = new AdditionalTabManager(this);
                jqContext.addClass("rocket-context");
                jqContext.data("rocketContext", this);
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
                this.jqContext.remove();
                this.jqContext = null;
                var callback;
                while (undefined !== (callback = this.onCloseCallbacks.shift())) {
                    callback(this);
                }
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
            Context.prototype.clear = function (loading) {
                if (loading === void 0) { loading = false; }
                this.jqContext.empty();
                this.jqContext.addClass("rocket-loading");
            };
            Context.prototype.applyHtml = function (html) {
                this.endLoading();
                this.jqContext.html(html);
            };
            Context.prototype.endLoading = function () {
                this.jqContext.removeClass("rocket-loading");
            };
            Context.prototype.applyContent = function (jqContent) {
                this.endLoading();
                this.jqContext.append(jqContent);
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
            Context.prototype.createAdditionalTab = function (title, prepend) {
                if (prepend === void 0) { prepend = false; }
                return this.additionalTabManager.createTab(title, prepend);
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
                    delete this.tabs[i];
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
        var ContextCommands = (function () {
            function ContextCommands(jqContextCommands) {
            }
            return ContextCommands;
        }());
    })(cmd = rocket.cmd || (rocket.cmd = {}));
})(rocket || (rocket = {}));
var rocket;
(function (rocket) {
    var display;
    (function (display) {
        var StructureElement = (function () {
            function StructureElement(jqElem, group, field) {
                this.onShowCallbacks = new Array();
                this.onHideCallbacks = new Array();
                this.toolbar = null;
                this.jqElem = jqElem;
                this.group = group;
                this.field = field;
                if (group) {
                    jqElem.addClass("rocket-group");
                }
                if (field) {
                    jqElem.addClass("rocket-field");
                }
                jqElem.data("rocketStructureElement", this);
            }
            StructureElement.prototype.isGroup = function () {
                return this.group;
            };
            StructureElement.prototype.isField = function () {
                return this.field;
            };
            StructureElement.prototype.getToolbar = function () {
                if (this.toolbar !== null) {
                    return this.toolbar;
                }
                if (!this.group) {
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
            StructureElement.prototype.show = function () {
                this.jqElem.show();
                for (var i in this.onShowCallbacks) {
                    this.onShowCallbacks[i](this);
                }
            };
            StructureElement.prototype.hide = function () {
                this.jqElem.hide();
                for (var i in this.onHideCallbacks) {
                    this.onHideCallbacks[i](this);
                }
            };
            StructureElement.prototype.addChild = function (structureElement) {
                var that = this;
                structureElement.onShow(function () {
                    that.show();
                });
            };
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
            StructureElement.prototype.highlight = function () {
                this.jqElem.addClass("rocket-highlighted");
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
            };
            StructureElement.from = function (jqElem, createAsGroup, createAsField) {
                if (createAsGroup === void 0) { createAsGroup = false; }
                if (createAsField === void 0) { createAsField = false; }
                var structureElement = jqElem.data("rocketStructureElement");
                if (structureElement instanceof StructureElement)
                    return structureElement;
                if (!createAsGroup && !createAsField)
                    return null;
                structureElement = new StructureElement(jqElem, createAsGroup, createAsField);
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
                this.jqCommands = jqToolbar.children(".rocket-simple-commands");
                if (this.jqCommands.length == 0) {
                    this.jqCommands = $("<div />", { "class": "rocket-simple-commands" });
                    this.jqToolbar.append(this.jqCommands);
                    this.jqCommands.hide();
                }
                else if (this.jqCommands.is(':empty')) {
                    this.jqCommands.hide();
                }
                if (this.jqControls.is(':empty') && this.jqCommands.is(':empty')) {
                    this.jqToolbar.hide();
                }
            }
            Toolbar.prototype.show = function () {
                this.jqToolbar.show();
            };
            Toolbar.prototype.hide = function () {
                this.jqToolbar.hide();
            };
            Toolbar.prototype.getJqControls = function () {
                return this.jqControls;
            };
            Toolbar.prototype.getJqCommands = function () {
                return this.jqCommands;
            };
            Toolbar.prototype.createCommandButton = function (iconType, label, type, tooltip) {
                if (tooltip === void 0) { tooltip = null; }
                this.show();
                this.jqCommands.show();
                var jqButton = $("<button />", {
                    "class": "btn btn-" + type,
                    "title": tooltip
                }).append($("<i />", {
                    "class": iconType
                })).append($("<span />", {
                    "text": label
                }));
                this.jqCommands.append(jqButton);
                return jqButton;
            };
            return Toolbar;
        }());
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
        var ToMany = (function () {
            function ToMany(jqToMany) {
                this.compact = true;
                this.sortable = true;
                this.entries = new Array();
                this.expandContext = null;
                this.jqToMany = jqToMany;
                this.compact = (true == jqToMany.data("compact"));
                this.sortable = (true == jqToMany.data("sortable"));
                this.closeLabel = jqToMany.data("close-label");
                jqToMany.data("rocketToMany", this);
                this.jqEmbedded = $("<div />", {
                    "class": "rocket-impl-embedded"
                });
                this.jqToMany.append(this.jqEmbedded);
                if (this.compact) {
                    var structureElement = rocket.display.StructureElement.findFrom(this.jqToMany);
                    var toolbar = structureElement.getToolbar();
                    if (toolbar !== null) {
                        var jqButton = toolbar.createCommandButton("fa fa-pencil", "Edit", "warining");
                        var that = this;
                        jqButton.click(function () {
                            that.expand();
                        });
                    }
                }
            }
            ToMany.prototype.addEntry = function (entry) {
                entry.setOrderIndex(this.entries.length);
                this.entries.push(entry);
                entry.getJQuery().detach();
                this.jqEmbedded.append(entry.getJQuery());
                if (this.isExpanded()) {
                    entry.expand();
                    this.expand();
                }
                else {
                    entry.reduce();
                    this.reduce();
                }
            };
            ToMany.prototype.isExpanded = function () {
                return this.expandContext !== null;
            };
            ToMany.prototype.expand = function () {
                if (this.isExpanded())
                    return;
                if (this.sortable) {
                    this.jqEmbedded.sortable("disable");
                    this.jqEmbedded.enableSelection();
                }
                this.expandContext = rocket.getContainer().createLayer().createContext(window.location.href);
                this.jqEmbedded.detach();
                this.expandContext.applyContent(this.jqEmbedded);
                this.expandContext.getLayer().pushHistoryEntry(window.location.href);
                for (var i in this.entries) {
                    this.entries[i].expand();
                }
            };
            ToMany.prototype.reduce = function () {
                if (this.sortable) {
                    var that = this;
                    var oldIndex = 0;
                    this.jqEmbedded.sortable({
                        "forcePlaceholderSize": true,
                        "placeholder": "rocket-impl-entry-placeholder",
                        "start": function (event, ui) {
                            var oldIndex = ui.item.index();
                        },
                        "update": function (event, ui) {
                            var newIndex = ui.item.index();
                            that.entries[oldIndex].setOrderIndex(newIndex);
                            that.entries[newIndex].setOrderIndex(oldIndex);
                            var entry = that.entries[oldIndex];
                            that.entries[oldIndex] = that.entries[newIndex];
                            that.entries[newIndex] = entry;
                        }
                    }).disableSelection();
                }
            };
            ToMany.from = function (jqToMany) {
                var toMany = jqToMany.data("rocketToMany");
                if (toMany instanceof ToMany) {
                    return toMany;
                }
                toMany = new ToMany(jqToMany);
                jqToMany.find(".rocket-impl-entry").each(function () {
                    toMany.addEntry(new EmbeddedEntry($(this)));
                });
                return toMany;
            };
            return ToMany;
        }());
        impl.ToMany = ToMany;
        var EmbeddedEntry = (function () {
            function EmbeddedEntry(jqEntry) {
                this.jqEntry = jqEntry;
                this.jqOrderIndex = jqEntry.children(".rocket-impl-order-index").hide();
                this.jqSummary = jqEntry.children(".rocket-impl-summary");
                this.jqBody = jqEntry.children(".rocket-impl-body");
                this.reduce();
                jqEntry.data("rocketImplEmbeddedEntry", this);
            }
            EmbeddedEntry.prototype.getJQuery = function () {
                return this.jqEntry;
            };
            EmbeddedEntry.prototype.expand = function () {
                this.jqSummary.hide();
                this.jqBody.show();
            };
            EmbeddedEntry.prototype.reduce = function () {
                this.jqSummary.show();
                this.jqBody.hide();
            };
            EmbeddedEntry.prototype.setOrderIndex = function (orderIndex) {
                this.jqOrderIndex.val(orderIndex);
            };
            EmbeddedEntry.prototype.getOrderIndex = function () {
                return parseInt(this.jqOrderIndex.val());
            };
            EmbeddedEntry.from = function (jqElem, create) {
                if (create === void 0) { create = false; }
                var entry = jqElem.data("rocketImplEmbeddedEntry");
                if (entry instanceof EmbeddedEntry) {
                    return entry;
                }
                if (create) {
                    return new EmbeddedEntry(jqElem);
                }
                return null;
            };
            return EmbeddedEntry;
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
                    "url": url,
                    "dataType": "json"
                }).fail(function (jqXHR, textStatus, data) {
                    if (jqXHR.status != 200) {
                        config.currentLayer.getContainer().handleError(url, jqXHR.responseText);
                        return;
                    }
                    alert("Not yet implemented press F5 after ok.");
                }).done(function (data, textStatus, jqXHR) {
                    that.analyzeResponse(config.currentLayer, data, url, targetContext);
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
                    currentLayer.pushHistoryEntry(targetUrl);
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
            function ExecResult(order, context) {
            }
            return ExecResult;
        }());
        cmd.ExecResult = ExecResult;
    })(cmd = rocket.cmd || (rocket.cmd = {}));
})(rocket || (rocket = {}));
var rocket;
(function (rocket) {
    var container;
    var executor;
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
        (function () {
            var initializer = new rocket.display.Initializer(container, jqContainer.data("error-tab-title"), jqContainer.data("display-error-label"));
            initializer.scan();
            n2n.dispatch.registerCallback(function () {
                initializer.scan();
            });
        })();
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
                $(".rocket-impl-many").each(function () {
                    rocket.impl.ToMany.from($(this));
                });
            });
        })();
    });
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
                        alert(jqXHR.responseText);
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
