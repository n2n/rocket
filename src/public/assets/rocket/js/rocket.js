var Rocket;
(function (Rocket) {
    Jhtml.ready((elements) => {
        console.log("ready!!!!");
    });
    var container;
    var blocker;
    var initializer;
    jQuery(document).ready(function ($) {
        var jqContainer = $("#rocket-content-container");
        container = new Rocket.Cmd.Container(jqContainer);
        blocker = new Rocket.Cmd.Blocker(container);
        blocker.init($("body"));
        initializer = new Rocket.Display.Initializer(container, jqContainer.data("error-tab-title"), jqContainer.data("display-error-label"));
        initializer.scan();
        Jhtml.ready(() => {
            initializer.scan();
        });
        (function () {
            Jhtml.ready(() => {
                $(".rocket-impl-overview").each(function () {
                    Rocket.Impl.Overview.OverviewPage.from($(this));
                });
            });
            Jhtml.ready(() => {
                $(".rocket-impl-overview").each(function () {
                    Rocket.Impl.Overview.OverviewPage.from($(this));
                });
            });
        })();
        (function () {
            $("form.rocket-impl-form").each(function () {
                Rocket.Impl.Form.from($(this));
            });
            Jhtml.ready(() => {
                $("form.rocket-impl-form").each(function () {
                    Rocket.Impl.Form.from($(this));
                });
            });
        })();
        (function () {
            $(".rocket-impl-to-many").each(function () {
                Rocket.Impl.Relation.ToMany.from($(this));
            });
            Jhtml.ready(() => {
                $(".rocket-impl-to-many").each(function () {
                    Rocket.Impl.Relation.ToMany.from($(this));
                });
            });
        })();
        (function () {
            $(".rocket-impl-to-one").each(function () {
                Rocket.Impl.Relation.ToOne.from($(this));
            });
            Jhtml.ready(() => {
                $(".rocket-impl-to-one").each(function () {
                    Rocket.Impl.Relation.ToOne.from($(this));
                });
            });
        })();
        (function () {
            let t = new Rocket.Impl.Translator(container);
            t.scan();
            Jhtml.ready(() => {
                t.scan();
            });
        })();
        (function () {
            Jhtml.ready((elements) => {
                $(elements).find("a.rocket-jhtml").each(function () {
                    new Rocket.Display.Command(Jhtml.Ui.Link.from(this)).observe();
                });
            });
        })();
    });
    function scan(context = null) {
        initializer.scan();
    }
    Rocket.scan = scan;
    function getContainer() {
        return container;
    }
    Rocket.getContainer = getContainer;
    function layerOf(elem) {
        return Rocket.Cmd.Layer.of($(elem));
    }
    Rocket.layerOf = layerOf;
    function contextOf(elem) {
        return Rocket.Cmd.Zone.of($(elem));
    }
    Rocket.contextOf = contextOf;
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Cmd;
    (function (Cmd) {
        class Blocker {
            constructor(container) {
                this.container = container;
                this.jqBlocker = null;
                for (let layer of container.layers) {
                    this.observeLayer(layer);
                }
                var that = this;
                container.layerOn(Cmd.Container.LayerEventType.ADDED, function (layer) {
                    that.observeLayer(layer);
                    that.check();
                });
            }
            observeLayer(layer) {
                for (let context of layer.contexts) {
                    this.observePage(context);
                }
                layer.onNewPage((context) => {
                    this.observePage(context);
                    this.check();
                });
            }
            observePage(context) {
                var checkCallback = () => {
                    this.check();
                };
                context.on(Cmd.Zone.EventType.SHOW, checkCallback);
                context.on(Cmd.Zone.EventType.HIDE, checkCallback);
                context.on(Cmd.Zone.EventType.CLOSE, checkCallback);
                context.on(Cmd.Zone.EventType.CONTENT_CHANGED, checkCallback);
                context.on(Cmd.Zone.EventType.BLOCKED_CHANGED, checkCallback);
            }
            init(jqContainer) {
                if (this.jqContainer) {
                    throw new Error("Blocker already initialized.");
                }
                this.jqContainer = jqContainer;
                this.check();
            }
            check() {
                if (!this.jqContainer || !this.container.currentLayer.currentZone)
                    return;
                if (!this.container.currentLayer.currentZone.locked) {
                    if (!this.jqBlocker)
                        return;
                    this.jqBlocker.remove();
                    this.jqBlocker = null;
                    return;
                }
                if (this.jqBlocker)
                    return;
                this.jqBlocker =
                    $("<div />", {
                        "class": "rocket-zone-block",
                        "css": {
                            "position": "fixed",
                            "top": 0,
                            "left": 0,
                            "right": 0,
                            "bottom": 0
                        }
                    })
                        .appendTo(this.jqContainer);
            }
        }
        Cmd.Blocker = Blocker;
    })(Cmd = Rocket.Cmd || (Rocket.Cmd = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Cmd;
    (function (Cmd) {
        class Container {
            constructor(jqContainer) {
                this.layerCallbackRegistery = new Rocket.util.CallbackRegistry();
                this.jqContainer = jqContainer;
                this._layers = new Array();
                var layer = new Cmd.Layer(this.jqContainer.find(".rocket-main-layer"), this._layers.length, this, Jhtml.getOrCreateMonitor());
                this._layers.push(layer);
            }
            get layers() {
                return this._layers.slice();
            }
            get mainLayer() {
                if (this._layers.length > 0) {
                    return this._layers[0];
                }
                throw new Error("Container empty.");
            }
            get currentLayer() {
                if (this._layers.length == 0) {
                    throw new Error("Container empty.");
                }
                var layer = null;
                for (let i in this._layers) {
                    if (this._layers[i].visible) {
                        layer = this._layers[i];
                    }
                }
                if (layer !== null)
                    return layer;
                return this._layers[this._layers.length - 1];
            }
            unregisterLayer(layer) {
                var i = this._layers.indexOf(layer);
                if (i < 0)
                    return;
                this._layers.splice(i, 1);
                this.layerTrigger(Container.LayerEventType.REMOVED, layer);
            }
            createLayer(dependentPage = null) {
                var jqLayer = $("<div />", {
                    "class": "rocket-layer"
                });
                this.jqContainer.append(jqLayer);
                var layer = new Cmd.Layer(jqLayer, this._layers.length, this, Jhtml.Monitor.from(jqLayer.get(0)));
                this._layers.push(layer);
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
                var that = this;
                layer.on(Cmd.Layer.EventType.CLOSE, function () {
                    that.unregisterLayer(layer);
                });
                if (dependentPage === null) {
                    this.layerTrigger(Container.LayerEventType.ADDED, layer);
                    return layer;
                }
                dependentPage.on(Cmd.Zone.EventType.CLOSE, function () {
                    layer.close();
                });
                dependentPage.on(Cmd.Zone.EventType.HIDE, function () {
                    layer.hide();
                });
                dependentPage.on(Cmd.Zone.EventType.SHOW, function () {
                    layer.show();
                });
                this.layerTrigger(Container.LayerEventType.ADDED, layer);
                return layer;
            }
            getAllPages() {
                var contexts = new Array();
                for (var i in this._layers) {
                    var layerPages = this._layers[i].contexts;
                    for (var j in layerPages) {
                        contexts.push(layerPages[j]);
                    }
                }
                return contexts;
            }
            layerTrigger(eventType, layer) {
                var container = this;
                this.layerCallbackRegistery.filter(eventType.toString())
                    .forEach(function (callback) {
                    callback(layer);
                });
            }
            layerOn(eventType, callback) {
                this.layerCallbackRegistery.register(eventType.toString(), callback);
            }
            layerOff(eventType, callback) {
                this.layerCallbackRegistery.unregister(eventType.toString(), callback);
            }
        }
        Cmd.Container = Container;
        (function (Container) {
            let LayerEventType;
            (function (LayerEventType) {
                LayerEventType[LayerEventType["REMOVED"] = 0] = "REMOVED";
                LayerEventType[LayerEventType["ADDED"] = 1] = "ADDED";
            })(LayerEventType = Container.LayerEventType || (Container.LayerEventType = {}));
        })(Container = Cmd.Container || (Cmd.Container = {}));
    })(Cmd = Rocket.Cmd || (Rocket.Cmd = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Cmd;
    (function (Cmd) {
        class Layer {
            constructor(jqLayer, _level, _container, _monitor) {
                this.jqLayer = jqLayer;
                this._level = _level;
                this._container = _container;
                this._monitor = _monitor;
                this._zones = new Array();
                this.callbackRegistery = new Rocket.util.CallbackRegistry();
                this._visible = true;
                this.onNewPageCallbacks = new Array();
                this.onNewHistoryEntryCallbacks = new Array();
                var jqPage = jqLayer.children(".rocket-zone:first");
                if (jqPage.length > 0) {
                    var page = new Cmd.Zone(jqPage, Jhtml.Url.create(window.location.href), this);
                    this.addZone(page);
                }
                this.monitor.history.onChanged(() => this.historyChanged());
                this.monitor.registerCompHandler("rocket-page", this);
                this.historyChanged();
            }
            get monitor() {
                return this._monitor;
            }
            containsUrl(url) {
                for (var i in this._zones) {
                    if (this._zones[i].containsUrl(url))
                        return true;
                }
                return false;
            }
            getZoneByUrl(urlExpr) {
                var url = Jhtml.Url.create(urlExpr);
                for (var i in this._zones) {
                    if (this._zones[i].containsUrl(url)) {
                        return this._zones[i];
                    }
                }
                return null;
            }
            historyChanged() {
                let currentEntry = this.monitor.history.currentEntry;
                if (!currentEntry)
                    return;
                let page = this.getZoneByUrl(currentEntry.page.url);
                if (!page) {
                    page = this.createZone(currentEntry.page.url);
                    page.clear(true);
                    this.addZone(page);
                }
                this.switchToZone(page);
            }
            createZone(urlExpr) {
                let url = Jhtml.Url.create(urlExpr);
                if (this.containsUrl(url)) {
                    throw new Error("Page with url already available: " + url);
                }
                var jqZone = $("<div />");
                this.jqLayer.append(jqZone);
                var zone = new Cmd.Zone(jqZone, url, this);
                this.addZone(zone);
                return zone;
            }
            get currentZone() {
                if (this.empty || !this._monitor.history.currentEntry) {
                    return null;
                }
                var url = this._monitor.history.currentPage.url;
                for (var i in this._zones) {
                    if (this._zones[i].containsUrl(url)) {
                        return this._zones[i];
                    }
                }
                return null;
            }
            get container() {
                return this._container;
            }
            get visible() {
                return this._visible;
            }
            trigger(eventType) {
                var layer = this;
                this.callbackRegistery.filter(eventType.toString())
                    .forEach(function (callback) {
                    callback(layer);
                });
            }
            on(eventType, callback) {
                this.callbackRegistery.register(eventType.toString(), callback);
            }
            off(eventType, callback) {
                this.callbackRegistery.unregister(eventType.toString(), callback);
            }
            show() {
                this.trigger(Layer.EventType.SHOW);
                this._visible = true;
                this.jqLayer.show();
            }
            hide() {
                this.trigger(Layer.EventType.SHOW);
                this._visible = false;
                this.jqLayer.hide();
            }
            get level() {
                return this._level;
            }
            get empty() {
                return this._zones.length == 0;
            }
            get contexts() {
                return this._zones.slice();
            }
            addZone(page) {
                this._zones.push(page);
                var that = this;
                page.on(Cmd.Zone.EventType.CLOSE, function (context) {
                    for (var i in that._zones) {
                        if (that._zones[i] !== context)
                            continue;
                        that._zones.splice(parseInt(i), 1);
                        break;
                    }
                });
                for (var i in this.onNewPageCallbacks) {
                    this.onNewPageCallbacks[i](page);
                }
            }
            onNewPage(onNewPageCallback) {
                this.onNewPageCallbacks.push(onNewPageCallback);
            }
            clear() {
                for (var i in this._zones) {
                    this._zones[i].close();
                }
            }
            close() {
                this.trigger(Layer.EventType.CLOSE);
                let context = null;
                while (context = this._zones.pop()) {
                    context.close();
                }
                this._zones = new Array();
                this.jqLayer.remove();
            }
            switchToZone(zone) {
                for (var i in this._zones) {
                    if (this._zones[i] === zone) {
                        zone.show();
                    }
                    else {
                        this._zones[i].hide();
                    }
                }
            }
            attachComp(comp, loadObserver) {
                if (!comp.model.response)
                    return false;
                let zone = this.getZoneByUrl(comp.model.response.url);
                if (zone) {
                    zone.applyComp(comp, loadObserver);
                    return true;
                }
                return false;
            }
            detachComp(comp) {
                return !this.jqLayer.get(0).contains(comp.elements[0]);
            }
            pushHistoryEntry(urlExpr) {
                let url = Jhtml.Url.create(urlExpr);
                let history = this.monitor.history;
                let page = history.getPageByUrl(url);
                if (page) {
                    history.push(page);
                    return;
                }
                let zone = this.getZoneByUrl(url);
                if (!zone) {
                    history.push(new Jhtml.Page(url, this.createPromise(zone)));
                    return;
                }
                history.push(new Jhtml.Page(url, null));
            }
            createPromise(zone) {
                return new Promise((resolve) => {
                    resolve({
                        exec() {
                            this.switchToPage(zone);
                        }
                    });
                });
            }
            static create(jqLayer, _level, _container, history) {
                if (Layer.test(jqLayer)) {
                    throw new Error("Layer already bound to this element.");
                }
                jqLayer.addClass("rocket-layer");
                jqLayer.data("rocketLayer", this);
            }
            static test(jqLayer) {
                var layer = jqLayer.data("rocketLayer");
                if (layer instanceof Layer) {
                    return layer;
                }
                return null;
            }
            static of(jqElem) {
                if (!jqElem.hasClass(".rocket-layer")) {
                    jqElem = jqElem.closest(".rocket-layer");
                }
                var layer = Layer.test(jqElem);
                if (layer === undefined) {
                    return null;
                }
                return layer;
            }
        }
        Cmd.Layer = Layer;
        (function (Layer) {
            let EventType;
            (function (EventType) {
                EventType[EventType["SHOW"] = 0] = "SHOW";
                EventType[EventType["HIDE"] = 1] = "HIDE";
                EventType[EventType["CLOSE"] = 2] = "CLOSE";
            })(EventType = Layer.EventType || (Layer.EventType = {}));
        })(Layer = Cmd.Layer || (Cmd.Layer = {}));
    })(Cmd = Rocket.Cmd || (Rocket.Cmd = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var util;
    (function (util) {
        class CallbackRegistry {
            constructor() {
                this.callbackMap = {};
            }
            register(nature, callback) {
                if (this.callbackMap[nature] === undefined) {
                    this.callbackMap[nature] = new Array();
                }
                this.callbackMap[nature].push(callback);
            }
            unregister(nature, callback) {
                if (this.callbackMap[nature] === undefined) {
                    return;
                }
                for (let i in this.callbackMap[nature]) {
                    if (this.callbackMap[nature][i] === callback) {
                        this.callbackMap[nature].splice(parseInt(i), 1);
                        return;
                    }
                }
            }
            filter(nature) {
                if (this.callbackMap[nature] === undefined) {
                    return new Array();
                }
                return this.callbackMap[nature];
            }
        }
        util.CallbackRegistry = CallbackRegistry;
        class ArgUtils {
            static valIsset(arg) {
                if (arg !== null && arg !== undefined)
                    return;
                throw new InvalidArgumentError("Invalid arg: " + arg);
            }
        }
        util.ArgUtils = ArgUtils;
        class ElementUtils {
            static isControl(elem) {
                switch (elem.tagName) {
                    case 'A':
                    case 'BUTTON':
                    case 'INPUT':
                    case 'TEXTAREA':
                    case 'SELECT':
                        return true;
                    default:
                        return false;
                }
            }
        }
        util.ElementUtils = ElementUtils;
        class InvalidArgumentError extends Error {
        }
        util.InvalidArgumentError = InvalidArgumentError;
        class IllegalStateError extends Error {
            static assertTrue(arg, errMsg = null) {
                if (arg === true)
                    return;
                if (errMsg === null) {
                    errMsg = "Illegal state";
                }
                throw new Error(errMsg);
            }
        }
        util.IllegalStateError = IllegalStateError;
    })(util = Rocket.util || (Rocket.util = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        class StructureElement {
            constructor(jqElem) {
                this.onShowCallbacks = [];
                this.onHideCallbacks = [];
                this.toolbar = null;
                this.highlightedParent = null;
                this.jqElem = jqElem;
                jqElem.addClass("rocket-structure-element");
                jqElem.data("rocketStructureElement", this);
                this.valClasses();
            }
            valClasses() {
                if (this.isField() || this.isGroup()) {
                    this.jqElem.removeClass("rocket-structure-element");
                }
                else {
                    this.jqElem.addClass("rocket-structure-element");
                }
            }
            get jQuery() {
                return this.jqElem;
            }
            setGroup(group) {
                if (!group) {
                    this.jqElem.removeClass("rocket-group");
                }
                else {
                    this.jqElem.addClass("rocket-group");
                }
                this.valClasses();
            }
            isGroup() {
                return this.jqElem.hasClass("rocket-group");
            }
            setField(field) {
                if (!field) {
                    this.jqElem.removeClass("rocket-field");
                }
                else {
                    this.jqElem.addClass("rocket-field");
                }
                this.valClasses();
            }
            isField() {
                return this.jqElem.hasClass("rocket-field");
            }
            getToolbar() {
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
            }
            getTitle() {
                return this.jqElem.children("label:first").text();
            }
            getParent() {
                return StructureElement.of(this.jqElem);
            }
            isVisible() {
                return this.jqElem.is(":visible");
            }
            show(includeParents = false) {
                for (var i in this.onShowCallbacks) {
                    this.onShowCallbacks[i](this);
                }
                this.jqElem.show();
                var parent;
                if (includeParents && null !== (parent = this.getParent())) {
                    parent.show(true);
                }
            }
            hide() {
                for (var i in this.onHideCallbacks) {
                    this.onHideCallbacks[i](this);
                }
                this.jqElem.hide();
            }
            onShow(callback) {
                this.onShowCallbacks.push(callback);
            }
            onHide(callback) {
                this.onHideCallbacks.push(callback);
            }
            scrollTo() {
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
            }
            highlight(findVisibleParent = false) {
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
            }
            unhighlight(slow = false) {
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
            }
            static from(jqElem, create = false) {
                var structureElement = jqElem.data("rocketStructureElement");
                if (structureElement instanceof StructureElement)
                    return structureElement;
                if (!create)
                    return null;
                structureElement = new StructureElement(jqElem);
                jqElem.data("rocketStructureElement", structureElement);
                return structureElement;
            }
            static of(jqElem) {
                jqElem = jqElem.closest(".rocket-structure-element, .rocket-group, .rocket-field");
                if (jqElem.length == 0)
                    return null;
                var structureElement = jqElem.data("rocketStructureElement");
                if (structureElement instanceof StructureElement) {
                    return structureElement;
                }
                structureElement = StructureElement.from(jqElem, true);
                jqElem.data("rocketStructureElement", structureElement);
                return structureElement;
            }
        }
        Display.StructureElement = StructureElement;
        class Toolbar {
            constructor(jqToolbar) {
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
            get jQuery() {
                return this.jqToolbar;
            }
            getJqControls() {
                return this.jqControls;
            }
            getCommandList() {
                return this.commandList;
            }
        }
        Display.Toolbar = Toolbar;
        class CommandList {
            constructor(jqCommandList, simple = false) {
                this.jqCommandList = jqCommandList;
                if (simple) {
                    jqCommandList.addClass("rocket-simple-commands");
                }
            }
            get jQuery() {
                return this.jqCommandList;
            }
            createJqCommandButton(buttonConfig, prepend = false) {
                this.jqCommandList.show();
                if (buttonConfig.iconType === undefined) {
                    buttonConfig.iconType = "fa fa-circle-o";
                }
                if (buttonConfig.severity === undefined) {
                    buttonConfig.severity = Display.Severity.SECONDARY;
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
            }
            static create(simple = false) {
                return new CommandList($("<div />"), simple);
            }
        }
        Display.CommandList = CommandList;
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Cmd;
    (function (Cmd) {
        var display = Rocket.Display;
        var util = Rocket.util;
        class Zone {
            constructor(jqZone, url, layer) {
                this.urls = [];
                this.callbackRegistery = new util.CallbackRegistry();
                this._blocked = false;
                this.locks = new Array();
                this.jqZone = jqZone;
                this.urls.push(this._activeUrl = url);
                this._layer = layer;
                jqZone.addClass("rocket-zone");
                jqZone.data("rocketPage", this);
                this.reset();
                this.hide();
            }
            get layer() {
                return this._layer;
            }
            get jQuery() {
                return this.jqZone;
            }
            containsUrl(url) {
                for (var i in this.urls) {
                    if (this.urls[i].equals(url))
                        return true;
                }
                return false;
            }
            get activeUrl() {
                return this._activeUrl;
            }
            fireEvent(eventType) {
                var that = this;
                this.callbackRegistery.filter(eventType.toString()).forEach(function (callback) {
                    callback(that);
                });
            }
            ensureNotClosed() {
                if (this.jqZone !== null)
                    return;
                throw new Error("Page already closed.");
            }
            close() {
                this.trigger(Zone.EventType.CLOSE);
                this.jqZone.remove();
                this.jqZone = null;
            }
            show() {
                this.trigger(Zone.EventType.SHOW);
                this.jqZone.show();
            }
            hide() {
                this.trigger(Zone.EventType.HIDE);
                this.jqZone.hide();
            }
            reset() {
                this.additionalTabManager = new AdditionalTabManager(this);
                this._menu = new Menu(this);
            }
            clear(showLoader = false) {
                this.jqZone.empty();
                if (showLoader) {
                    this.jqZone.addClass("rocket-loading");
                }
                this.trigger(Zone.EventType.CONTENT_CHANGED);
            }
            applyHtml(html) {
                this.endLoading();
                this.jqZone.html(html);
                this.reset();
                this.trigger(Zone.EventType.CONTENT_CHANGED);
            }
            applyComp(comp, loadObserver) {
                this.endLoading();
                comp.attachTo(this.jqZone.get(0), loadObserver);
                this.reset();
                this.trigger(Zone.EventType.CONTENT_CHANGED);
            }
            isLoading() {
                return this.jqZone.hasClass("rocket-loading");
            }
            endLoading() {
                this.jqZone.removeClass("rocket-loading");
            }
            applyContent(jqContent) {
                this.endLoading();
                this.jqZone.append(jqContent);
                this.reset();
                this.trigger(Zone.EventType.CONTENT_CHANGED);
            }
            trigger(eventType) {
                var context = this;
                this.callbackRegistery.filter(eventType.toString())
                    .forEach(function (callback) {
                    callback(context);
                });
            }
            on(eventType, callback) {
                this.callbackRegistery.register(eventType.toString(), callback);
            }
            off(eventType, callback) {
                this.callbackRegistery.unregister(eventType.toString(), callback);
            }
            createAdditionalTab(title, prepend = false) {
                return this.additionalTabManager.createTab(title, prepend);
            }
            get menu() {
                return this._menu;
            }
            get locked() {
                return this.locks.length > 0;
            }
            releaseLock(lock) {
                let i = this.locks.indexOf(lock);
                if (i == -1)
                    return;
                this.locks.splice(i, 1);
                this.trigger(Zone.EventType.BLOCKED_CHANGED);
            }
            createLock() {
                var that = this;
                var lock = new Lock(function (lock) {
                    that.releaseLock(lock);
                });
                this.locks.push(lock);
                this.trigger(Zone.EventType.BLOCKED_CHANGED);
                return lock;
            }
            static of(jqElem) {
                if (!jqElem.hasClass(".rocket-zone")) {
                    jqElem = jqElem.parents(".rocket-zone");
                }
                var context = jqElem.data("rocketPage");
                if (context instanceof Zone)
                    return context;
                return null;
            }
        }
        Cmd.Zone = Zone;
        class Lock {
            constructor(releaseCallback) {
                this.releaseCallback = releaseCallback;
            }
            release() {
                this.releaseCallback(this);
            }
        }
        Cmd.Lock = Lock;
        class AdditionalTabManager {
            constructor(context) {
                this.jqAdditional = null;
                this.context = context;
                this.tabs = new Array();
            }
            createTab(title, prepend = false) {
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
            }
            removeTab(tab) {
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
            }
            setupAdditional() {
                if (this.jqAdditional !== null)
                    return;
                var jqPage = this.context.jQuery;
                jqPage.addClass("rocket-contains-additional");
                this.jqAdditional = $("<div />", {
                    "class": "rocket-additional"
                });
                this.jqAdditional.append($("<ul />", { "class": "rocket-additional-nav" }));
                this.jqAdditional.append($("<div />", { "class": "rocket-additional-container" }));
                jqPage.prepend(this.jqAdditional);
            }
            setdownAdditional() {
                if (this.jqAdditional === null)
                    return;
                this.context.jQuery.removeClass("rocket-contains-additional");
                this.jqAdditional.remove();
                this.jqAdditional = null;
            }
        }
        class AdditionalTab {
            constructor(jqNavItem, jqContent) {
                this.active = false;
                this.onShowCallbacks = [];
                this.onHideCallbacks = [];
                this.onDisposeCallbacks = [];
                this.jqNavItem = jqNavItem;
                this.jqContent = jqContent;
                this.jqNavItem.click(this.show);
                this.jqContent.hide();
            }
            getJqNavItem() {
                return this.jqNavItem;
            }
            getJqContent() {
                return this.jqContent;
            }
            isActive() {
                return this.active;
            }
            show() {
                this.active = true;
                this.jqNavItem.addClass("rocket-active");
                this.jqContent.show();
                for (var i in this.onShowCallbacks) {
                    this.onShowCallbacks[i](this);
                }
            }
            hide() {
                this.active = false;
                this.jqContent.hide();
                this.jqNavItem.removeClass("rocket-active");
                for (var i in this.onHideCallbacks) {
                    this.onHideCallbacks[i](this);
                }
            }
            dispose() {
                this.jqNavItem.remove();
                this.jqContent.remove();
                for (var i in this.onDisposeCallbacks) {
                    this.onDisposeCallbacks[i](this);
                }
            }
            onShow(callback) {
                this.onShowCallbacks.push(callback);
            }
            onHide(callback) {
                this.onHideCallbacks.push(callback);
            }
            onDispose(callback) {
                this.onDisposeCallbacks.push(callback);
            }
        }
        Cmd.AdditionalTab = AdditionalTab;
        class Menu {
            constructor(context) {
                this._toolbar = null;
                this._commandList = null;
                this._partialCommandList = null;
                this.context = context;
            }
            get toolbar() {
                if (this._toolbar) {
                    return this._toolbar;
                }
                let jqToolbar = this.context.jQuery.find(".rocket-zone-toolbar:first");
                if (jqToolbar.length == 0) {
                    jqToolbar = $("<div />", { "class": "rocket-zone-toolbar" }).prependTo(this.context.jQuery);
                }
                return this._toolbar = new display.Toolbar(jqToolbar);
            }
            getJqPageCommands() {
                var jqCommandList = this.context.jQuery.find(".rocket-zone-commands:first");
                if (jqCommandList.length == 0) {
                    jqCommandList = $("<div />", {
                        "class": "rocket-zone-commands"
                    });
                    this.context.jQuery.append(jqCommandList);
                }
                return jqCommandList;
            }
            get partialCommandList() {
                if (this._partialCommandList !== null) {
                    return this._partialCommandList;
                }
                var jqPageCommands = this.getJqPageCommands();
                var jqPartialCommands = jqPageCommands.children(".rocket-partial-commands:first");
                if (jqPartialCommands.length == 0) {
                    jqPartialCommands = $("<div />", { "class": "rocket-partial-commands" }).prependTo(jqPageCommands);
                }
                return this._partialCommandList = new display.CommandList(jqPartialCommands);
            }
            get commandList() {
                if (this._commandList !== null) {
                    return this._commandList;
                }
                var jqPageCommands = this.getJqPageCommands();
                var jqCommands = jqPageCommands.children(":not(.rocket-partial-commands):first");
                if (jqCommands.length == 0) {
                    jqCommands = $("<div />").appendTo(jqPageCommands);
                }
                return this._commandList = new display.CommandList(jqCommands);
            }
        }
        Cmd.Menu = Menu;
        (function (Zone) {
            let EventType;
            (function (EventType) {
                EventType[EventType["SHOW"] = 0] = "SHOW";
                EventType[EventType["HIDE"] = 1] = "HIDE";
                EventType[EventType["CLOSE"] = 2] = "CLOSE";
                EventType[EventType["CONTENT_CHANGED"] = 3] = "CONTENT_CHANGED";
                EventType[EventType["ACTIVE_URL_CHANGED"] = 4] = "ACTIVE_URL_CHANGED";
                EventType[EventType["BLOCKED_CHANGED"] = 5] = "BLOCKED_CHANGED";
            })(EventType = Zone.EventType || (Zone.EventType = {}));
        })(Zone = Cmd.Zone || (Cmd.Zone = {}));
    })(Cmd = Rocket.Cmd || (Rocket.Cmd = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        class Command {
            constructor(jLink) {
                this.jLink = jLink;
                this._observing = false;
                alert("huii");
            }
            observe() {
                if (this._observing)
                    return;
                this._observing = true;
                this.jLink.onDirective((directivePromise) => {
                    alert("wut");
                    $(this.jLink.element).find("i").attr("class", "fa fa-minus-circle");
                });
            }
        }
        Display.Command = Command;
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        class Entry {
            constructor(jqElem) {
                this.jqElem = jqElem;
                this._selector = null;
                this._state = Entry.State.PERSISTENT;
                this.callbackRegistery = new Rocket.util.CallbackRegistry();
                var that = this;
                jqElem.on("remove", function () {
                    that.trigger(Entry.EventType.DISPOSED);
                });
                let jqSelector = jqElem.find(".rocket-entry-selector:first");
                if (jqSelector.length > 0) {
                    this.initSelector(jqSelector);
                }
            }
            initSelector(jqSelector) {
                this._selector = new Display.EntrySelector(jqSelector, this);
                var that = this;
                this.jqElem.click(function (e) {
                    if (getSelection().toString() || Rocket.util.ElementUtils.isControl(e.target)) {
                        return;
                    }
                    that._selector.selected = !that._selector.selected;
                });
            }
            trigger(eventType) {
                var entry = this;
                this.callbackRegistery.filter(eventType.toString())
                    .forEach(function (callback) {
                    callback(entry);
                });
            }
            on(eventType, callback) {
                this.callbackRegistery.register(eventType.toString(), callback);
            }
            off(eventType, callback) {
                this.callbackRegistery.unregister(eventType.toString(), callback);
            }
            get jqQuery() {
                return this.jqElem;
            }
            show() {
                this.jqElem.show();
            }
            hide() {
                this.jqElem.hide();
            }
            dispose() {
                this.jqElem.remove();
            }
            get state() {
                return this._state;
            }
            set state(state) {
                if (this._state == state)
                    return;
                this._state = state;
                if (state == Entry.State.REMOVED) {
                    this.trigger(Entry.EventType.REMOVED);
                }
            }
            get generalId() {
                return this.jqElem.data("rocket-general-id").toString();
            }
            get id() {
                if (this.draftId !== null) {
                    return this.draftId.toString();
                }
                return this.idRep;
            }
            get idRep() {
                return this.jqElem.data("rocket-id-rep").toString();
            }
            get draftId() {
                var draftId = parseInt(this.jqElem.data("rocket-draft-id"));
                if (!isNaN(draftId)) {
                    return draftId;
                }
                return null;
            }
            get identityString() {
                return this.jqElem.data("rocket-identity-string");
            }
            get selector() {
                return this._selector;
            }
            static from(jqElem) {
                var entry = jqElem.data("rocketEntry");
                if (entry instanceof Entry) {
                    return entry;
                }
                entry = new Entry(jqElem);
                jqElem.data("rocketEntry", entry);
                return entry;
            }
            static findFrom(jqElem) {
                var jqElem = jqElem.closest(".rocket-entry");
                if (jqElem.length == 0)
                    return null;
                return Entry.from(jqElem);
            }
            static findAll(jqElem, includeSelf = false) {
                var entries = new Array();
                var jqEntries = jqElem.find(".rocket-entry");
                jqEntries = jqEntries.add(jqElem.filter(".rocket-entry"));
                jqEntries.each(function () {
                    entries.push(Entry.from($(this)));
                });
                return entries;
            }
        }
        Display.Entry = Entry;
        (function (Entry) {
            let State;
            (function (State) {
                State[State["PERSISTENT"] = 0] = "PERSISTENT";
                State[State["REMOVED"] = 1] = "REMOVED";
            })(State = Entry.State || (Entry.State = {}));
            let EventType;
            (function (EventType) {
                EventType[EventType["DISPOSED"] = 0] = "DISPOSED";
                EventType[EventType["REFRESHED"] = 1] = "REFRESHED";
                EventType[EventType["REMOVED"] = 2] = "REMOVED";
            })(EventType = Entry.EventType || (Entry.EventType = {}));
        })(Entry = Display.Entry || (Display.Entry = {}));
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        class EntryForm {
            constructor(jqElem) {
                this.jqEiTypeSelect = null;
                this.inited = false;
                this.jqElem = jqElem;
            }
            init() {
                if (this.inited) {
                    throw new Error("EntryForm already initialized:");
                }
                this.inited = true;
                if (!this.jqElem.hasClass("rocket-multi-ei-type"))
                    return;
                this.jqEiTypeSelect = this.jqElem.children(".rocket-ei-type-selector").find("select");
                this.updateDisplay();
                this.jqEiTypeSelect.change(() => {
                    this.updateDisplay();
                });
            }
            updateDisplay() {
                if (!this.jqEiTypeSelect)
                    return;
                this.jqElem.children(".rocket-ei-type-entry-form").hide();
                this.jqElem.children(".rocket-ei-type-" + this.jqEiTypeSelect.val()).show();
            }
            get jQuery() {
                return this.jqElem;
            }
            get multiEiType() {
                return this.jqEiTypeSelect ? true : false;
            }
            get curEiTypeId() {
                if (!this.multiEiType) {
                    return this.jqElem.data("rocket-ei-type-id");
                }
                return this.jqEiTypeSelect.val();
            }
            set curEiTypeId(typeId) {
                this.jqEiTypeSelect.val(typeId);
            }
            get curGenericLabel() {
                if (!this.multiEiType) {
                    return this.jqElem.data("rocket-generic-label");
                }
                return this.jqEiTypeSelect.children(":selected").text();
            }
            get curGenericIconType() {
                if (!this.multiEiType) {
                    return this.jqElem.data("rocket-generic-icon-type");
                }
                return this.jqEiTypeSelect.data("rocket-generic-icon-types")[this.curEiTypeId];
            }
            get typeMap() {
                let typeMap = {};
                if (!this.multiEiType) {
                    typeMap[this.curEiTypeId] = this.curGenericLabel;
                    return typeMap;
                }
                this.jqEiTypeSelect.children().each(function () {
                    let jqElem = $(this);
                    typeMap[jqElem.attr("value")] = jqElem.text();
                });
                return typeMap;
            }
            static from(jqElem, create = true) {
                var entryForm = jqElem.data("rocketEntryForm");
                if (entryForm instanceof EntryForm)
                    return entryForm;
                if (!create)
                    return null;
                entryForm = new EntryForm(jqElem);
                entryForm.init();
                jqElem.data("rocketEntryForm", entryForm);
                return entryForm;
            }
            static firstOf(jqElem) {
                if (jqElem.hasClass("rocket-entry-form")) {
                    return EntryForm.from(jqElem);
                }
                let jqEntryForm = jqElem.find(".rocket-entry-form:first");
                if (jqEntryForm.length == 0)
                    return null;
                return EntryForm.from(jqEntryForm);
            }
            static find(jqElem, mulitTypeOnly = false) {
                let entryForms = [];
                jqElem.find(".rocket-entry-form" + (mulitTypeOnly ? ".rocket-multi-ei-type" : "")).each(function () {
                    entryForms.push(EntryForm.from($(this)));
                });
                return entryForms;
            }
        }
        Display.EntryForm = EntryForm;
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        class EntrySelector {
            constructor(jqElem, _entry) {
                this.jqElem = jqElem;
                this._entry = _entry;
                this.changedCallbacks = new Array();
                this._selected = false;
            }
            get jQuery() {
                return this.jqElem;
            }
            get entry() {
                return this._entry;
            }
            get selected() {
                return this._selected;
            }
            set selected(selected) {
                if (this._selected == selected)
                    return;
                this._selected = selected;
                this.triggerChanged();
            }
            whenChanged(callback) {
                this.changedCallbacks.push(callback);
            }
            triggerChanged() {
                this.changedCallbacks.forEach(function (callback) {
                    callback();
                });
            }
        }
        Display.EntrySelector = EntrySelector;
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        class Initializer {
            constructor(container, errorTabTitle, displayErrorLabel) {
                this.container = container;
                this.errorTabTitle = errorTabTitle;
                this.displayErrorLabel = displayErrorLabel;
                this.errorIndexes = new Array();
            }
            scan() {
                var errorIndex = null;
                while (undefined !== (errorIndex = this.errorIndexes.pop())) {
                    errorIndex.getTab().dispose();
                }
                var contexts = this.container.getAllPages();
                for (var i in contexts) {
                    this.scanPage(contexts[i]);
                }
            }
            scanPage(context) {
                var that = this;
                var i = 0;
                var jqPage = context.jQuery;
                Display.EntryForm.find(jqPage, true);
                jqPage.find(".rocket-group-main").each(function () {
                    var jqElem = $(this);
                    if (jqElem.hasClass("rocket-group-main")) {
                        Initializer.scanGroupNav(jqElem.parent());
                    }
                });
                var errorIndex = null;
                jqPage.find(".rocket-message-error").each(function () {
                    var structureElement = Display.StructureElement.of($(this));
                    if (errorIndex === null) {
                        errorIndex = new ErrorIndex(context.createAdditionalTab(that.errorTabTitle), that.displayErrorLabel);
                        that.errorIndexes.push(errorIndex);
                    }
                    errorIndex.addError(structureElement, $(this).text());
                });
            }
            static scanGroupNav(jqContainer) {
                let curGroupNav = null;
                jqContainer.children().each(function () {
                    var jqElem = $(this);
                    if (!jqElem.hasClass("rocket-group-main")) {
                        curGroupNav = null;
                        return;
                    }
                    if (curGroupNav === null) {
                        curGroupNav = GroupNav.fromMain(jqElem);
                    }
                    var group = Display.StructureElement.from(jqElem);
                    if (group === null) {
                        curGroupNav.registerGroup(Display.StructureElement.from(jqElem, true));
                    }
                });
                return curGroupNav;
            }
        }
        Display.Initializer = Initializer;
        class GroupNav {
            constructor(jqGroupNav) {
                this.jqGroupNav = jqGroupNav;
                this.groups = new Array();
                jqGroupNav.addClass("rocket-main-group-nav nav nav-tabs");
                jqGroupNav.hide();
            }
            registerGroup(group) {
                this.groups.push(group);
                if (this.groups.length == 2) {
                    this.jqGroupNav.show();
                }
                var jqLi = $("<li />", {
                    "text": group.getTitle(),
                    "class": { "class": "nav-item" }
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
            }
            static fromMain(jqElem, create = true) {
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
            }
        }
        class ErrorIndex {
            constructor(tab, displayErrorLabel) {
                this.tab = tab;
                this.displayErrorLabel = displayErrorLabel;
            }
            getTab() {
                return this.tab;
            }
            addError(structureElement, errorMessage) {
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
            }
        }
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        let Severity;
        (function (Severity) {
            Severity[Severity["PRIMARY"] = 0] = "PRIMARY";
            Severity[Severity["SECONDARY"] = 1] = "SECONDARY";
            Severity[Severity["SUCCESS"] = 2] = "SUCCESS";
            Severity[Severity["DANGER"] = 3] = "DANGER";
            Severity[Severity["INFO"] = 4] = "INFO";
            Severity[Severity["WARNING"] = 5] = "WARNING";
        })(Severity = Display.Severity || (Display.Severity = {}));
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var $ = jQuery;
        class Form {
            constructor(jqForm) {
                this._config = new Form.Config();
                this.jqForm = jqForm;
                this._jForm = Jhtml.Ui.Form.from(jqForm.get(0));
                this._jForm.on("submit", () => {
                    this.block();
                });
                this._jForm.on("submitted", () => {
                    this.unblock();
                });
            }
            get jQuery() {
                return this.jqForm;
            }
            get jForm() {
                return this._jForm;
            }
            get config() {
                return this._config;
            }
            block() {
                let zone;
                if (!this.lock && this.config.blockPage && (zone = Rocket.Cmd.Zone.of(this.jqForm))) {
                    this.lock = zone.createLock();
                }
            }
            unblock() {
                if (this.lock) {
                    this.lock.release();
                    this.lock = null;
                }
            }
            static from(jqForm) {
                var form = jqForm.data("rocketImplForm");
                if (form instanceof Form)
                    return form;
                if (jqForm.length == 0) {
                    throw new Error("Invalid argument");
                }
                form = new Form(jqForm);
                jqForm.data("rocketImplForm", form);
                return form;
            }
        }
        Impl.Form = Form;
        (function (Form) {
            class Config {
                constructor() {
                    this.blockPage = true;
                }
            }
            Form.Config = Config;
            let EventType;
            (function (EventType) {
                EventType[EventType["SUBMIT"] = 0] = "SUBMIT";
                EventType[EventType["SUBMITTED"] = 1] = "SUBMITTED";
            })(EventType = Form.EventType || (Form.EventType = {}));
        })(Form = Impl.Form || (Impl.Form = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        class Translator {
            constructor(container) {
                this.container = container;
            }
            scan() {
                for (let context of this.container.getAllPages()) {
                    let elems = context.jQuery.find(".rocket-impl-translation-manager").toArray();
                    let elem;
                    while (elem = elems.pop()) {
                        this.initTm($(elem), context);
                    }
                    let jqViewControl = context.menu.toolbar.getJqControls().find(".rocket-impl-translation-view-control");
                    let jqTranslatables = context.jQuery.find(".rocket-impl-translatable");
                    if (jqTranslatables.length == 0) {
                        jqViewControl.hide();
                        continue;
                    }
                    jqViewControl.show();
                    if (jqViewControl.length == 0) {
                        jqViewControl = $("<div />", { "class": "rocket-impl-translation-view-control" });
                        context.menu.toolbar.getJqControls().show().append(jqViewControl);
                    }
                    let viewMenu = ViewMenu.from(jqViewControl);
                    jqTranslatables.each((i, elem) => {
                        viewMenu.registerTranslatable(Translatable.from($(elem)));
                    });
                }
            }
            initTm(jqElem, context) {
                let tm = TranslationManager.from(jqElem);
                let se = Rocket.Display.StructureElement.of(jqElem);
                let jqBase = null;
                if (!se) {
                    jqBase = context.jQuery;
                }
                else {
                    jqBase = jqElem;
                }
                jqBase.find(".rocket-impl-translatable").each((i, elem) => {
                    tm.registerTranslatable(Translatable.from($(elem)));
                });
            }
        }
        Impl.Translator = Translator;
        class ViewMenu {
            constructor(jqContainer) {
                this.jqContainer = jqContainer;
                this.translatables = [];
                this.items = {};
                this.changing = false;
            }
            draw(languagesLabel, visibleLabel) {
                $("<div />", { "class": "rocket-impl-translation-status" })
                    .append($("<label />", { "text": visibleLabel }).prepend($("<i></i>", { "class": "fa fa-language" })))
                    .append(this.jqStatus = $("<span></span>"))
                    .prependTo(this.jqContainer);
                new Rocket.Display.CommandList(this.jqContainer).createJqCommandButton({
                    iconType: "fa fa-cog",
                    label: languagesLabel
                }).click(() => this.jqMenu.toggle());
                this.jqMenu = $("<ul></ul>", { "class": "rocket-impl-translation-status-menu" }).hide();
                this.jqContainer.append(this.jqMenu);
            }
            updateStatus() {
                let prettyLocaleIds = [];
                for (let localeId in this.items) {
                    if (!this.items[localeId].on)
                        continue;
                    prettyLocaleIds.push(this.items[localeId].prettyLocaleId);
                }
                this.jqStatus.empty();
                this.jqStatus.text(prettyLocaleIds.join(", "));
                let onDisabled = prettyLocaleIds.length == 1;
                for (let localeId in this.items) {
                    this.items[localeId].disabled = onDisabled && this.items[localeId].on;
                }
            }
            get visibleLocaleIds() {
                let localeIds = [];
                for (let localeId in this.items) {
                    if (!this.items[localeId].on)
                        continue;
                    localeIds.push(localeId);
                }
                return localeIds;
            }
            registerTranslatable(translatable) {
                if (-1 < this.translatables.indexOf(translatable))
                    return;
                if (!this.jqStatus) {
                    this.draw(translatable.jQuery.data("rocket-impl-languages-label"), translatable.jQuery.data("rocket-impl-visible-label"));
                }
                this.translatables.push(translatable);
                translatable.jQuery.on("remove", () => this.unregisterTranslatable(translatable));
                for (let content of translatable.contents) {
                    if (!this.items[content.localeId]) {
                        let item = this.items[content.localeId] = new ViewMenuItem(content.localeId, content.localeName, content.prettyLocaleId);
                        item.draw($("<li />").appendTo(this.jqMenu));
                        item.on = Object.keys(this.items).length == 1;
                        item.whenChanged(() => this.menuChanged());
                        this.updateStatus();
                    }
                    content.visible = this.items[content.localeId].on;
                    content.whenChanged(() => {
                        if (this.changing || !content.active)
                            return;
                        this.items[content.localeId].on = true;
                    });
                }
            }
            unregisterTranslatable(translatable) {
                let i = this.translatables.indexOf(translatable);
                if (-1 < i) {
                    this.translatables.splice(i, 1);
                }
            }
            menuChanged() {
                if (this.changing) {
                    throw new Error("already changing");
                }
                this.changing = true;
                let visiableLocaleIds = [];
                for (let i in this.items) {
                    if (this.items[i].on) {
                        visiableLocaleIds.push(this.items[i].localeId);
                    }
                }
                for (let translatable of this.translatables) {
                    translatable.visibleLocaleIds = visiableLocaleIds;
                }
                this.updateStatus();
                this.changing = false;
            }
            static from(jqElem) {
                let vm = jqElem.data("rocketImplViewMenu");
                if (vm instanceof ViewMenu) {
                    return vm;
                }
                vm = new ViewMenu(jqElem);
                jqElem.data("rocketImplViewMenu", vm);
                return vm;
            }
        }
        class ViewMenuItem {
            constructor(localeId, label, prettyLocaleId) {
                this.localeId = localeId;
                this.label = label;
                this.prettyLocaleId = prettyLocaleId;
                this._on = true;
                this.changedCallbacks = [];
            }
            draw(jqElem) {
                this.jqI = $("<i></i>");
                this.jqA = $("<a />", { "href": "", "text": this.label + " ", "class": "btn" })
                    .append(this.jqI)
                    .appendTo(jqElem)
                    .click((evt) => {
                    if (this.disabled)
                        return;
                    this.on = !this.on;
                    evt.preventDefault();
                    return false;
                });
                this.checkI();
            }
            get disabled() {
                return this.jqA.hasClass("disabled");
            }
            set disabled(disabled) {
                if (disabled) {
                    this.jqA.addClass("disabled");
                }
                else {
                    this.jqA.removeClass("disabled");
                }
            }
            get on() {
                return this._on;
            }
            set on(on) {
                if (this._on == on)
                    return;
                this._on = on;
                this.checkI();
                this.triggerChanged();
            }
            triggerChanged() {
                for (let callback of this.changedCallbacks) {
                    callback();
                }
            }
            whenChanged(callback) {
                this.changedCallbacks.push(callback);
            }
            checkI() {
                if (this.on) {
                    this.jqI.attr("class", "fa fa-toggle-on");
                }
                else {
                    this.jqI.attr("class", "fa fa-toggle-off");
                }
            }
        }
        class TranslationManager {
            constructor(jqElem) {
                this.jqElem = jqElem;
                this.min = 0;
                this.translatables = [];
                this.menuItems = [];
                this.changing = false;
                this.min = parseInt(jqElem.data("rocket-impl-min"));
                this.initControl();
                this.initMenu();
                this.val();
            }
            val() {
                let activeLocaleIds = [];
                for (let menuItem of this.menuItems) {
                    if (!menuItem.active)
                        continue;
                    activeLocaleIds.push(menuItem.localeId);
                }
                let activeDisabled = activeLocaleIds.length <= this.min;
                for (let menuItem of this.menuItems) {
                    if (menuItem.mandatory)
                        continue;
                    if (!menuItem.active && activeLocaleIds.length < this.min) {
                        menuItem.active = true;
                        activeLocaleIds.push(menuItem.localeId);
                    }
                    menuItem.disabled = activeDisabled && menuItem.active;
                }
                return activeLocaleIds;
            }
            registerTranslatable(translatable) {
                if (-1 < this.translatables.indexOf(translatable))
                    return;
                this.translatables.push(translatable);
                translatable.activeLocaleIds = this.activeLocaleIds;
                translatable.jQuery.on("remove", () => this.unregisterTranslatable(translatable));
                for (let tc of translatable.contents) {
                    tc.whenChanged(() => {
                        this.activeLocaleIds = translatable.activeLocaleIds;
                    });
                }
            }
            unregisterTranslatable(translatable) {
                let i = this.translatables.indexOf(translatable);
                if (i > -1) {
                    this.translatables.splice(i, 1);
                }
            }
            get activeLocaleIds() {
                let localeIds = Array();
                for (let menuItem of this.menuItems) {
                    if (menuItem.active) {
                        localeIds.push(menuItem.localeId);
                    }
                }
                return localeIds;
            }
            set activeLocaleIds(localeIds) {
                if (this.changing)
                    return;
                this.changing = true;
                let changed = false;
                for (let menuItem of this.menuItems) {
                    if (menuItem.mandatory)
                        continue;
                    let active = -1 < localeIds.indexOf(menuItem.localeId);
                    if (menuItem.active != active) {
                        changed = true;
                    }
                    menuItem.active = active;
                }
                if (!changed) {
                    this.changing = false;
                    return;
                }
                localeIds = this.val();
                for (let translatable of this.translatables) {
                    translatable.activeLocaleIds = localeIds;
                }
                this.changing = false;
            }
            menuChanged() {
                if (this.changing)
                    return;
                this.changing = true;
                let localeIds = this.val();
                for (let translatable of this.translatables) {
                    translatable.activeLocaleIds = localeIds;
                }
                this.changing = false;
            }
            initControl() {
                let jqLabel = this.jqElem.children("label:first");
                let cmdList = Rocket.Display.CommandList.create(true);
                cmdList.createJqCommandButton({
                    iconType: "fa fa-language",
                    label: jqLabel.text(),
                    tooltip: this.jqElem.data("rocket-impl-tooltip")
                }).click(() => this.toggle());
                jqLabel.replaceWith(cmdList.jQuery);
            }
            initMenu() {
                this.jqMenu = this.jqElem.find(".rocket-impl-translation-menu");
                this.jqMenu.hide();
                this.jqMenu.children().each((i, elem) => {
                    let mi = new MenuItem($(elem));
                    this.menuItems.push(mi);
                    mi.whenChanged(() => {
                        this.menuChanged();
                    });
                });
            }
            toggle() {
                this.jqMenu.toggle();
            }
            static from(jqElem) {
                let tm = jqElem.data("rocketImplTranslationManager");
                if (tm instanceof TranslationManager) {
                    return tm;
                }
                tm = new TranslationManager(jqElem);
                jqElem.data("rocketImplTranslationManager", tm);
                return tm;
            }
        }
        Impl.TranslationManager = TranslationManager;
        class MenuItem {
            constructor(jqElem) {
                this.jqElem = jqElem;
                this._localeId = this.jqElem.data("rocket-impl-locale-id");
                this._mandatory = this.jqElem.data("rocket-impl-mandatory") ? true : false;
                this.init();
            }
            init() {
                if (this.jqCheck) {
                    throw new Error("already initialized");
                }
                this.jqCheck = this.jqElem.find("input[type=checkbox]");
                if (this.mandatory) {
                    this.jqCheck.prop("checked", true);
                    this.jqCheck.prop("disabled", true);
                }
                this.jqCheck.change(() => { this.updateClasses(); });
            }
            updateClasses() {
                if (this.disabled) {
                    this.jqElem.addClass("rocket-disabled");
                }
                else {
                    this.jqElem.removeClass("rocket-disabled");
                }
                if (this.active) {
                    this.jqElem.addClass("rocket-active");
                }
                else {
                    this.jqElem.removeClass("rocket-active");
                }
            }
            whenChanged(callback) {
                this.jqCheck.change(callback);
            }
            get disabled() {
                return this.jqCheck.is(":disabled");
            }
            set disabled(disabled) {
                this.jqCheck.prop("disabled", disabled);
                this.updateClasses();
            }
            get active() {
                return this.jqCheck.is(":checked");
            }
            set active(active) {
                this.jqCheck.prop("checked", active);
                this.updateClasses();
            }
            get localeId() {
                return this._localeId;
            }
            get mandatory() {
                return this._mandatory;
            }
        }
        class Translatable {
            constructor(jqElem) {
                this.jqElem = jqElem;
                this._contents = {};
            }
            get jQuery() {
                return this.jqElem;
            }
            get localeIds() {
                return Object.keys(this._contents);
            }
            get contents() {
                let O = Object;
                return O.values(this._contents);
            }
            set visibleLocaleIds(localeIds) {
                for (let content of this.contents) {
                    content.visible = -1 < localeIds.indexOf(content.localeId);
                }
            }
            get visibleLocaleIds() {
                let localeIds = new Array();
                for (let content of this.contents) {
                    if (!content.visible)
                        continue;
                    localeIds.push(content.localeId);
                }
                return localeIds;
            }
            set activeLocaleIds(localeIds) {
                for (let content of this.contents) {
                    content.active = -1 < localeIds.indexOf(content.localeId);
                }
            }
            get activeLocaleIds() {
                let localeIds = new Array();
                for (let content of this.contents) {
                    if (!content.active)
                        continue;
                    localeIds.push(content.localeId);
                }
                return localeIds;
            }
            scan() {
                this.jqElem.children().each((i, elem) => {
                    let jqElem = $(elem);
                    let localeId = jqElem.data("rocket-impl-locale-id");
                    if (!localeId || this._contents[localeId])
                        return;
                    this._contents[localeId] = new TranslatedContent(localeId, jqElem);
                });
            }
            static from(jqElem) {
                let translatable = jqElem.data("rocketImplTranslatable");
                if (translatable instanceof Translatable) {
                    return translatable;
                }
                translatable = new Translatable(jqElem);
                jqElem.data("rocketImplTranslatable", translatable);
                translatable.scan();
                return translatable;
            }
        }
        Impl.Translatable = Translatable;
        class TranslatedContent {
            constructor(_localeId, jqElem) {
                this._localeId = _localeId;
                this.jqElem = jqElem;
                this.jqEnabler = null;
                this.changedCallbacks = [];
                this._visible = true;
                this.jqTranslation = jqElem.children(".rocket-impl-translation");
            }
            get localeId() {
                return this._localeId;
            }
            get prettyLocaleId() {
                return this.jqElem.find("label:first").text();
            }
            get localeName() {
                return this.jqElem.find("label:first").attr("title");
            }
            get visible() {
                return this._visible;
            }
            set visible(visible) {
                if (visible) {
                    if (this._visible)
                        return;
                    this._visible = true;
                    this.jqElem.show();
                    this.triggerChanged();
                    return;
                }
                if (!this._visible)
                    return;
                this._visible = false;
                this.jqElem.hide();
                this.triggerChanged();
            }
            get active() {
                return this.jqEnabler ? false : true;
            }
            set active(active) {
                if (active) {
                    if (this.jqEnabler) {
                        this.jqEnabler.remove();
                        this.jqEnabler = null;
                        this.triggerChanged();
                    }
                    return;
                }
                if (this.jqEnabler)
                    return;
                this.jqEnabler = $("<button />", {
                    "class": "rocket-impl-enabler",
                    "type": "button",
                    "text": " " + this.jqElem.data("rocket-impl-activate-label"),
                    "click": () => { this.active = true; }
                }).prepend($("<i />", { "class": "fa fa-language", "text": "" })).appendTo(this.jqElem);
                this.triggerChanged();
            }
            triggerChanged() {
                for (let callback of this.changedCallbacks) {
                    callback();
                }
            }
            whenChanged(callback) {
                this.changedCallbacks.push(callback);
            }
        }
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Overview;
        (function (Overview) {
            var $ = jQuery;
            class Header {
                constructor(overviewContent) {
                    this.overviewContent = overviewContent;
                }
                init(jqElem) {
                    this.jqElem = jqElem;
                    this.state = new State(this.overviewContent);
                    this.state.draw(this.jqElem.find(".rocket-impl-state:first"));
                    this.quicksearchForm = new QuicksearchForm(this.overviewContent);
                    this.quicksearchForm.init(this.jqElem.find("form.rocket-impl-quicksearch:first"));
                    this.critmodForm = new CritmodForm(this.overviewContent);
                    this.critmodForm.init(this.jqElem.find("form.rocket-impl-critmod:first"));
                    this.critmodSelect = new CritmodSelect(this.overviewContent);
                    this.critmodSelect.init(this.jqElem.find("form.rocket-impl-critmod-select:first"), this.critmodForm);
                    this.critmodForm.drawControl(this.critmodSelect.jQuery.parent());
                }
            }
            Overview.Header = Header;
            class State {
                constructor(overviewContent) {
                    this.overviewContent = overviewContent;
                }
                draw(jqElem) {
                    this.jqElem = jqElem;
                    var that = this;
                    this.jqAllButton = $("<button />", { "type": "button", "class": "btn btn-secondary" }).appendTo(jqElem);
                    this.jqAllButton.click(function () {
                        that.overviewContent.showAll();
                        that.reDraw();
                    });
                    this.jqSelectedButton = $("<button />", { "type": "button", "class": "btn btn-secondary" }).appendTo(jqElem);
                    this.jqSelectedButton.click(function () {
                        that.overviewContent.showSelected();
                        that.reDraw();
                    });
                    this.reDraw();
                    this.overviewContent.whenContentChanged(function () { that.reDraw(); });
                    this.overviewContent.whenSelectionChanged(function () { that.reDraw(); });
                }
                reDraw() {
                    var numEntries = this.overviewContent.numEntries;
                    if (numEntries == 1) {
                        this.jqAllButton.text(numEntries + " " + this.jqElem.data("entries-label"));
                    }
                    else {
                        this.jqAllButton.text(numEntries + " " + this.jqElem.data("entries-plural-label"));
                    }
                    if (this.overviewContent.selectedOnly) {
                        this.jqAllButton.removeClass("active");
                        this.jqSelectedButton.addClass("active");
                    }
                    else {
                        this.jqAllButton.addClass("active");
                        this.jqSelectedButton.removeClass("active");
                    }
                    if (!this.overviewContent.selectable) {
                        this.jqSelectedButton.hide();
                        return;
                    }
                    this.jqSelectedButton.show();
                    var numSelected = this.overviewContent.numSelectedEntries;
                    if (numSelected == 1) {
                        this.jqSelectedButton.text(numSelected + " " + this.jqElem.data("selected-label"));
                    }
                    else {
                        this.jqSelectedButton.text(numSelected + " " + this.jqElem.data("selected-plural-label"));
                    }
                    if (0 == numSelected) {
                        this.jqSelectedButton.prop("disabled", true);
                        return;
                    }
                    this.jqSelectedButton.prop("disabled", false);
                }
            }
            class QuicksearchForm {
                constructor(overviewContent) {
                    this.overviewContent = overviewContent;
                    this.sc = 0;
                    this.serachVal = null;
                }
                init(jqForm) {
                    if (this.form) {
                        throw new Error("Quicksearch already initialized.");
                    }
                    this.jqForm = jqForm;
                    this.form = Jhtml.Ui.Form.from(jqForm.get(0));
                    this.form.on("submit", () => {
                        this.onSubmit();
                    });
                    this.form.config.disableControls = false;
                    this.form.config.actionUrl = jqForm.data("rocket-impl-post-url");
                    this.form.config.successResponseHandler = (response) => {
                        if (!response.model || !response.model.snippet)
                            return false;
                        this.whenSubmitted(response.model.snippet, response.model.additionalData);
                        return true;
                    };
                    this.initListeners();
                }
                initListeners() {
                    this.form.reset();
                    var jqButtons = this.jqForm.find("button[type=submit]");
                    this.jqSearchButton = $(jqButtons.get(0));
                    var jqClearButton = $(jqButtons.get(1));
                    this.jqSearchInput = this.jqForm.find("input[type=search]:first");
                    var that = this;
                    this.jqSearchInput.on("paste keyup", function () {
                        that.send(false);
                    });
                    this.jqSearchInput.on("change", function () {
                        that.send(true);
                    });
                    jqClearButton.on("click", function () {
                        that.jqSearchInput.val("");
                        that.updateState();
                    });
                }
                updateState() {
                    if (this.jqSearchInput.val().toString().length > 0) {
                        this.jqForm.addClass("rocket-active");
                    }
                    else {
                        this.jqForm.removeClass("rocket-active");
                    }
                }
                send(force) {
                    var searchVal = this.jqSearchInput.val().toString();
                    if (this.serachVal == searchVal)
                        return;
                    this.updateState();
                    this.overviewContent.clear(true);
                    this.serachVal = searchVal;
                    var si = ++this.sc;
                    var that = this;
                    if (force) {
                        that.jqSearchButton.click();
                        return;
                    }
                    setTimeout(function () {
                        if (si !== that.sc)
                            return;
                        that.jqSearchButton.click();
                    }, 300);
                }
                onSubmit() {
                    this.sc++;
                    this.overviewContent.clear(true);
                }
                whenSubmitted(snippet, info) {
                    this.overviewContent.initFromResponse(snippet, info);
                }
            }
            class CritmodSelect {
                constructor(overviewContent) {
                    this.overviewContent = overviewContent;
                }
                get jQuery() {
                    return this.jqForm;
                }
                init(jqForm, critmodForm) {
                    if (this.form) {
                        throw new Error("CritmodSelect already initialized.");
                    }
                    this.jqForm = jqForm;
                    this.form = Jhtml.Ui.Form.from(jqForm.get(0));
                    this.form.reset();
                    this.critmodForm = critmodForm;
                    this.jqButton = jqForm.find("button[type=submit]").hide();
                    this.form.config.disableControls = false;
                    this.form.config.actionUrl = jqForm.data("rocket-impl-post-url");
                    this.form.config.autoSubmitAllowed = false;
                    this.form.config.successResponseHandler = (response) => {
                        if (response.model && response.model.snippet) {
                            this.whenSubmitted(response.model.snippet, response.model.additionalData);
                            return true;
                        }
                        return false;
                    };
                    this.jqSelect = jqForm.find("select:first").change(() => {
                        this.send();
                    });
                    critmodForm.onChange(() => {
                        this.form.abortSubmit();
                        this.updateId();
                    });
                    critmodForm.whenChanged((idOptions) => {
                        this.updateIdOptions(idOptions);
                    });
                }
                updateState() {
                    if (this.jqSelect.val()) {
                        this.jqForm.addClass("rocket-active");
                    }
                    else {
                        this.jqForm.removeClass("rocket-active");
                    }
                }
                send() {
                    this.form.submit({ button: this.jqButton.get(0) });
                    this.updateState();
                    this.overviewContent.clear(true);
                    var id = this.jqSelect.val();
                    this.critmodForm.activated = id ? true : false;
                    this.critmodForm.critmodSaveId = id.toString();
                    this.critmodForm.freeze();
                }
                whenSubmitted(snippet, info) {
                    this.overviewContent.initFromResponse(snippet, info);
                    this.critmodForm.reload();
                }
                updateId() {
                    var id = this.critmodForm.critmodSaveId;
                    if (id && isNaN(parseInt(id))) {
                        this.jqSelect.append($("<option />", { "value": id, "text": this.critmodForm.critmodSaveName }));
                    }
                    this.jqSelect.val(id);
                    this.updateState();
                }
                updateIdOptions(idOptions) {
                    this.jqSelect.empty();
                    for (let id in idOptions) {
                        this.jqSelect.append($("<option />", { value: id.trim(), text: idOptions[id] }));
                    }
                    this.jqSelect.val(this.critmodForm.critmodSaveId);
                }
            }
            class CritmodForm {
                constructor(overviewContent) {
                    this.overviewContent = overviewContent;
                    this.changeCallbacks = [];
                    this.changedCallbacks = [];
                    this._open = true;
                }
                drawControl(jqControlContainer) {
                    this.jqControlContainer = jqControlContainer;
                    this.jqOpenButton = $("<button />", {
                        "class": "btn btn-secondary",
                        "text": jqControlContainer.data("rocket-impl-open-filter-label") + " "
                    })
                        .append($("<i />", { "class": "fa fa-filter" }))
                        .click(() => { this.open = true; })
                        .appendTo(jqControlContainer);
                    this.jqEditButton = $("<button />", {
                        "class": "btn btn-secondary",
                        "text": jqControlContainer.data("rocket-impl-edit-filter-label") + " "
                    })
                        .append($("<i />", { "class": "fa fa-filter" }))
                        .click(() => { this.open = true; })
                        .appendTo(jqControlContainer);
                    this.jqCloseButton = $("<button />", {
                        "class": "btn btn-secondary",
                        "text": jqControlContainer.data("rocket-impl-close-filter-label") + " "
                    })
                        .append($("<i />", { "class": "fa fa-times" }))
                        .click(() => { this.open = false; })
                        .appendTo(jqControlContainer);
                    this.open = false;
                }
                updateControl() {
                    if (!this.jqOpenButton)
                        return;
                    if (this.open) {
                        this.jqControlContainer.addClass("rocket-open");
                        this.jqOpenButton.hide();
                        this.jqEditButton.hide();
                        this.jqCloseButton.show();
                        return;
                    }
                    this.jqControlContainer.removeClass("rocket-open");
                    if (this.critmodSaveId) {
                        this.jqOpenButton.hide();
                        this.jqEditButton.show();
                    }
                    else {
                        this.jqOpenButton.show();
                        this.jqEditButton.hide();
                    }
                    this.jqCloseButton.hide();
                }
                get open() {
                    return this._open;
                }
                set open(open) {
                    this._open = open;
                    if (open) {
                        this.jqForm.show();
                    }
                    else {
                        this.jqForm.hide();
                    }
                    this.updateControl();
                }
                init(jqForm) {
                    if (this.form) {
                        throw new Error("CritmodForm already initialized.");
                    }
                    this.jqForm = jqForm;
                    this.form = Jhtml.Ui.Form.from(jqForm.get(0));
                    this.form.reset();
                    this.form.config.actionUrl = jqForm.data("rocket-impl-post-url");
                    this.form.config.successResponseHandler = (response) => {
                        if (response.model && response.model.snippet) {
                            this.whenSubmitted(response.model.snippet, response.model.additionalData);
                            return true;
                        }
                        return false;
                    };
                    var activateFunc = (ensureCritmodSaveId) => {
                        this.activated = true;
                        if (ensureCritmodSaveId && !this.critmodSaveId) {
                            this.critmodSaveId = "new";
                        }
                        this.onSubmit();
                    };
                    var deactivateFunc = () => {
                        this.activated = false;
                        this.critmodSaveId = null;
                        this.block();
                        this.onSubmit();
                    };
                    this.jqApplyButton = jqForm.find(".rocket-impl-critmod-apply").click(function () { activateFunc(false); });
                    this.jqClearButton = jqForm.find(".rocket-impl-critmod-clear").click(function () { deactivateFunc(); });
                    this.jqNameInput = jqForm.find(".rocket-impl-critmod-name");
                    this.jqSaveButton = jqForm.find(".rocket-impl-critmod-save").click(function () { activateFunc(true); });
                    this.jqSaveAsButton = jqForm.find(".rocket-impl-critmod-save-as").click(() => {
                        this.critmodSaveId = null;
                        activateFunc(true);
                    });
                    this.jqDeleteButton = jqForm.find(".rocket-impl-critmod-delete").click(function () { deactivateFunc(); });
                    this.updateState();
                }
                get activated() {
                    return this.jqForm.hasClass("rocket-active");
                }
                set activated(activated) {
                    if (activated) {
                        this.jqForm.addClass("rocket-active");
                    }
                    else {
                        this.jqForm.removeClass("rocket-active");
                    }
                }
                get critmodSaveId() {
                    return this.jqForm.data("rocket-impl-critmod-save-id");
                }
                set critmodSaveId(critmodSaveId) {
                    this.jqForm.data("rocket-impl-critmod-save-id", critmodSaveId);
                    this.updateControl();
                }
                get critmodSaveName() {
                    return this.jqNameInput.val().toString();
                }
                updateState() {
                    if (this.critmodSaveId) {
                        this.jqSaveAsButton.show();
                        this.jqDeleteButton.show();
                    }
                    else {
                        this.jqSaveAsButton.hide();
                        this.jqDeleteButton.hide();
                    }
                }
                freeze() {
                    this.form.abortSubmit();
                    this.form.disableControls();
                    this.block();
                }
                block() {
                    if (this.jqBlocker)
                        return;
                    this.jqBlocker = $("<div />", { "class": "rocket-impl-critmod-blocker" })
                        .appendTo(this.jqForm);
                }
                reload() {
                    var url = this.form.config.actionUrl;
                    Jhtml.Monitor.of(this.jqForm.get(0)).lookupModel(Jhtml.Url.create(url)).then((model) => {
                        this.replaceForm(model.snippet, model.additionalData);
                    });
                }
                onSubmit() {
                    this.changeCallbacks.forEach(function (callback) {
                        callback();
                    });
                    this.overviewContent.clear(true);
                }
                whenSubmitted(snippet, info) {
                    this.overviewContent.init(1);
                    this.replaceForm(snippet, info);
                }
                replaceForm(snippet, info) {
                    if (this.jqBlocker) {
                        this.jqBlocker.remove();
                        this.jqBlocker = null;
                    }
                    var jqForm = $(snippet.elements);
                    this.jqForm.replaceWith(jqForm);
                    this.form = null;
                    snippet.markAttached();
                    this.init(jqForm);
                    this.open = this.open;
                    this.updateControl();
                    var idOptions = info.critmodSaveIdOptions;
                    this.changedCallbacks.forEach(function (callback) {
                        callback(idOptions);
                    });
                }
                onChange(callback) {
                    this.changeCallbacks.push(callback);
                }
                whenChanged(callback) {
                    this.changedCallbacks.push(callback);
                }
            }
            ;
        })(Overview = Impl.Overview || (Impl.Overview = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Overview;
        (function (Overview) {
            var $ = jQuery;
            class OverviewContent {
                constructor(jqElem, loadUrl) {
                    this.jqElem = jqElem;
                    this.loadUrl = loadUrl;
                    this.pages = new Array();
                    this.fakePage = null;
                    this.selectorState = new SelectorState();
                    this.changedCallbacks = new Array();
                    this._currentPageNo = null;
                    this.allInfo = null;
                    this.loadingPageNos = new Array();
                    this.jqLoader = null;
                }
                isInit() {
                    return this._currentPageNo != null && this._numPages != null && this._numEntries != null;
                }
                initFromDom(currentPageNo, numPages, numEntries) {
                    this.reset(false);
                    this._currentPageNo = currentPageNo;
                    this._numPages = numPages;
                    this._numEntries = numEntries;
                    let page = this.createPage(this.currentPageNo);
                    page.jqContents = this.jqElem.children();
                    this.selectorState.observePage(page);
                    if (this.allInfo) {
                        this.allInfo = new AllInfo([page], 0);
                    }
                    this.buildFakePage();
                    this.triggerContentChange();
                }
                init(currentPageNo) {
                    this.reset(false);
                    this.goTo(currentPageNo);
                    if (this.allInfo) {
                        this.allInfo = new AllInfo([this.pages[currentPageNo]], 0);
                    }
                    this.buildFakePage();
                    this.triggerContentChange();
                }
                initFromResponse(snippet, info) {
                    this.reset(false);
                    var page = this.createPage(parseInt(info.pageNo));
                    this._currentPageNo = page.pageNo;
                    this.initPageFromResponse(page, snippet, info);
                    if (this.allInfo) {
                        this.allInfo = new AllInfo([page], 0);
                    }
                    this.buildFakePage();
                    this.triggerContentChange();
                }
                clear(showLoader) {
                    this.reset(showLoader);
                    this.triggerContentChange();
                }
                reset(showLoader) {
                    let page = null;
                    while (undefined !== (page = this.pages.pop())) {
                        page.dispose();
                        this.unmarkPageAsLoading(page.pageNo);
                    }
                    this._currentPageNo = null;
                    if (this.fakePage) {
                        this.fakePage.dispose();
                        this.unmarkPageAsLoading(this.fakePage.pageNo);
                        this.fakePage = null;
                    }
                    if (this.allInfo) {
                        this.allInfo = new AllInfo([], 0);
                    }
                    if (showLoader) {
                        this.addLoader();
                    }
                    else {
                        this.removeLoader();
                    }
                }
                initSelector(selectorObserver) {
                    this.selectorState.activate(selectorObserver);
                    this.triggerContentChange();
                    this.buildFakePage();
                }
                buildFakePage() {
                    if (!this.selectorState.selectorObserver)
                        return;
                    if (this.fakePage) {
                        throw new Error("Fake page already existing.");
                    }
                    this.fakePage = new Page(0);
                    this.fakePage.hide();
                    var idReps = this.selectorState.selectorObserver.getSelectedIds();
                    var unloadedIds = idReps.slice();
                    var that = this;
                    this.selectorState.entries.forEach(function (entry) {
                        let id = entry.id;
                        let i;
                        if (-1 < (i = unloadedIds.indexOf(id))) {
                            unloadedIds.splice(i, 1);
                        }
                    });
                    this.loadFakePage(unloadedIds);
                    return this.fakePage;
                }
                loadFakePage(unloadedIdReps) {
                    if (unloadedIdReps.length == 0) {
                        this.fakePage.jqContents = $();
                        this.selectorState.observeFakePage(this.fakePage);
                        return;
                    }
                    this.markPageAsLoading(0);
                    var fakePage = this.fakePage;
                    Jhtml.Monitor.of(this.jqElem.get(0)).lookupModel(this.loadUrl.extR(null, { "idReps": unloadedIdReps }))
                        .then((model) => {
                        if (fakePage !== this.fakePage)
                            return;
                        this.unmarkPageAsLoading(0);
                        var jqContents = $(model.snippet.elements).find(".rocket-overview-content:first").children();
                        fakePage.jqContents = jqContents;
                        this.jqElem.append(jqContents);
                        model.snippet.markAttached();
                        this.selectorState.observeFakePage(fakePage);
                        this.triggerContentChange();
                    });
                }
                get selectedOnly() {
                    return this.allInfo != null;
                }
                showSelected() {
                    var scrollTop = $("html, body").scrollTop();
                    var visiblePages = new Array();
                    this.pages.forEach(function (page) {
                        if (page.visible) {
                            visiblePages.push(page);
                        }
                        page.hide();
                    });
                    this.selectorState.showSelectedEntriesOnly();
                    this.selectorState.autoShowSelected = true;
                    if (this.allInfo === null) {
                        this.allInfo = new AllInfo(visiblePages, scrollTop);
                    }
                    this.updateLoader();
                    this.triggerContentChange();
                }
                showAll() {
                    if (this.allInfo === null)
                        return;
                    this.selectorState.hideEntries();
                    this.selectorState.autoShowSelected = false;
                    this.allInfo.pages.forEach(function (page) {
                        page.show();
                    });
                    $("html, body").scrollTop(this.allInfo.scrollTop);
                    this.allInfo = null;
                    this.updateLoader();
                    this.triggerContentChange();
                }
                get currentPageNo() {
                    return this._currentPageNo;
                }
                get numPages() {
                    return this._numPages;
                }
                get numEntries() {
                    return this._numEntries;
                }
                get numSelectedEntries() {
                    if (!this.selectorState.isActive())
                        return null;
                    if (this.fakePage !== null && this.fakePage.isContentLoaded()) {
                        return this.selectorState.selectedEntries.length;
                    }
                    return this.selectorState.selectorObserver.getSelectedIds().length;
                }
                get selectable() {
                    return this.selectorState.selectorObserver != null;
                }
                setCurrentPageNo(currentPageNo) {
                    if (this._currentPageNo == currentPageNo) {
                        return;
                    }
                    this._currentPageNo = currentPageNo;
                    this.triggerContentChange();
                }
                triggerContentChange() {
                    var that = this;
                    this.changedCallbacks.forEach(function (callback) {
                        callback(that);
                    });
                }
                changeBoundaries(numPages, numEntries) {
                    if (this._numPages == numPages && this._numEntries == numEntries) {
                        return;
                    }
                    this._numPages = numPages;
                    this._numEntries = numEntries;
                    if (this.currentPageNo > this.numPages) {
                        this.goTo(this.numPages);
                        return;
                    }
                    this.triggerContentChange();
                }
                whenContentChanged(callback) {
                    this.changedCallbacks.push(callback);
                }
                whenSelectionChanged(callback) {
                    this.selectorState.whenChanged(callback);
                }
                isPageNoValid(pageNo) {
                    return (pageNo > 0 && pageNo <= this.numPages);
                }
                containsPageNo(pageNo) {
                    return this.pages[pageNo] !== undefined;
                }
                applyContents(page, jqContents) {
                    if (page.jqContents !== null) {
                        throw new Error("Contents already applied.");
                    }
                    page.jqContents = jqContents;
                    for (var pni = page.pageNo - 1; pni > 0; pni--) {
                        if (this.pages[pni] === undefined || !this.pages[pni].isContentLoaded())
                            continue;
                        jqContents.insertAfter(this.pages[pni].jqContents.last());
                        this.selectorState.observePage(page);
                        return;
                    }
                    this.jqElem.prepend(jqContents);
                    this.selectorState.observePage(page);
                }
                goTo(pageNo) {
                    if (!this.isPageNoValid(pageNo)) {
                        throw new Error("Invalid pageNo: " + pageNo);
                    }
                    if (this.selectedOnly) {
                        throw new Error("No paging support for selected entries.");
                    }
                    if (pageNo === this.currentPageNo) {
                        return;
                    }
                    if (this.pages[pageNo] === undefined) {
                        this.showSingle(pageNo);
                        this.load(pageNo);
                        this.setCurrentPageNo(pageNo);
                        return;
                    }
                    if (this.scrollToPage(this.currentPageNo, pageNo)) {
                        this.setCurrentPageNo(pageNo);
                        return;
                    }
                    this.showSingle(pageNo);
                    this.setCurrentPageNo(pageNo);
                }
                showSingle(pageNo) {
                    for (var i in this.pages) {
                        if (this.pages[i].pageNo == pageNo) {
                            this.pages[i].show();
                        }
                        else {
                            this.pages[i].hide();
                        }
                    }
                }
                scrollToPage(pageNo, targetPageNo) {
                    var page = null;
                    if (pageNo < targetPageNo) {
                        for (var i = pageNo; i <= targetPageNo; i++) {
                            if (!this.containsPageNo(i) || !this.pages[i].isContentLoaded()) {
                                return false;
                            }
                            page = this.pages[i];
                            page.show();
                        }
                    }
                    else {
                        for (var i = pageNo; i >= targetPageNo; i--) {
                            if (!this.containsPageNo(i) || !this.pages[i].isContentLoaded() || !this.pages[i].visible) {
                                return false;
                            }
                            page = this.pages[i];
                        }
                    }
                    $("html, body").stop().animate({
                        scrollTop: page.jqContents.first().offset().top
                    }, 500);
                    return true;
                }
                markPageAsLoading(pageNo) {
                    if (-1 < this.loadingPageNos.indexOf(pageNo)) {
                        throw new Error("page already loading");
                    }
                    this.loadingPageNos.push(pageNo);
                    this.updateLoader();
                }
                unmarkPageAsLoading(pageNo) {
                    var i = this.loadingPageNos.indexOf(pageNo);
                    if (-1 == i)
                        return;
                    this.loadingPageNos.splice(i, 1);
                    this.updateLoader();
                }
                updateLoader() {
                    for (var i in this.loadingPageNos) {
                        if (this.loadingPageNos[i] == 0 && this.selectedOnly) {
                            this.addLoader();
                            return;
                        }
                        if (this.loadingPageNos[i] > 0 && !this.selectedOnly) {
                            this.addLoader();
                            return;
                        }
                    }
                    this.removeLoader();
                }
                addLoader() {
                    if (this.jqLoader)
                        return;
                    this.jqLoader = $("<div />", { "class": "rocket-impl-overview-loading" })
                        .insertAfter(this.jqElem.parent("table"));
                }
                removeLoader() {
                    if (!this.jqLoader)
                        return;
                    this.jqLoader.remove();
                    this.jqLoader = null;
                }
                createPage(pageNo) {
                    if (this.containsPageNo(pageNo)) {
                        throw new Error("Page already exists: " + pageNo);
                    }
                    var page = this.pages[pageNo] = new Page(pageNo);
                    if (this.selectedOnly) {
                        page.hide();
                    }
                    return page;
                }
                load(pageNo) {
                    var page = this.createPage(pageNo);
                    this.markPageAsLoading(pageNo);
                    Jhtml.Monitor.of(this.jqElem.get(0))
                        .lookupModel(this.loadUrl.extR(null, { "pageNo": pageNo }))
                        .then((model) => {
                        if (page !== this.pages[pageNo])
                            return;
                        this.unmarkPageAsLoading(pageNo);
                        this.initPageFromResponse(page, model.snippet, model.additionalData);
                        this.triggerContentChange();
                    })
                        .catch(e => {
                        if (page !== this.pages[pageNo])
                            return;
                        this.unmarkPageAsLoading(pageNo);
                        throw e;
                    });
                }
                initPageFromResponse(page, snippet, data) {
                    this.changeBoundaries(data.numPages, data.numEntries);
                    var jqContents = $(snippet.elements).find(".rocket-overview-content:first").children();
                    snippet.elements = jqContents.toArray();
                    this.applyContents(page, jqContents);
                    snippet.markAttached();
                }
            }
            Overview.OverviewContent = OverviewContent;
            class SelectorState {
                constructor() {
                    this._selectorObserver = null;
                    this.entryMap = {};
                    this.fakeEntryMap = {};
                    this.changedCallbacks = new Array();
                    this._autoShowSelected = false;
                }
                activate(selectorObserver) {
                    if (this._selectorObserver) {
                        throw new Error("Selector state already activated");
                    }
                    this._selectorObserver = selectorObserver;
                    if (!selectorObserver)
                        return;
                    for (let id in this.entryMap) {
                        if (this.entryMap[id].selector === null)
                            continue;
                        selectorObserver.observeEntrySelector(this.entryMap[id].selector);
                    }
                }
                observeFakePage(fakePage) {
                    var that = this;
                    fakePage.entries.forEach(function (entry) {
                        if (that.containsEntryId(entry.id)) {
                            entry.dispose();
                        }
                        else {
                            that.registerEntry(entry);
                        }
                    });
                }
                get selectorObserver() {
                    return this._selectorObserver;
                }
                isActive() {
                    return this._selectorObserver != null;
                }
                observePage(page) {
                    var that = this;
                    page.entries.forEach(function (entry) {
                        if (that.fakeEntryMap[entry.id]) {
                            that.fakeEntryMap[entry.id].dispose();
                        }
                        that.registerEntry(entry);
                    });
                }
                registerEntry(entry, fake = false) {
                    this.entryMap[entry.id] = entry;
                    if (fake) {
                        this.fakeEntryMap[entry.id] = entry;
                    }
                    if (entry.selector === null)
                        return;
                    if (this.selectorObserver !== null) {
                        this.selectorObserver.observeEntrySelector(entry.selector);
                    }
                    if (this.autoShowSelected && entry.selector.selected) {
                        entry.show();
                    }
                    var that = this;
                    entry.selector.whenChanged(function () {
                        if (that.autoShowSelected && entry.selector.selected) {
                            entry.show();
                        }
                        that.triggerChanged();
                    });
                    var onFunc = function () {
                        if (that.entryMap[entry.id] !== entry)
                            return;
                        delete that.entryMap[entry.id];
                        delete that.fakeEntryMap[entry.id];
                    };
                    entry.on(Rocket.Display.Entry.EventType.DISPOSED, onFunc);
                    entry.on(Rocket.Display.Entry.EventType.REMOVED, onFunc);
                }
                containsEntryId(id) {
                    return this.entryMap[id] !== undefined;
                }
                get entries() {
                    var k = Object;
                    return k.values(this.entryMap);
                }
                get selectedEntries() {
                    var entries = new Array();
                    for (let entry of this.entries) {
                        if (!entry.selector || !entry.selector.selected)
                            continue;
                        entries.push(entry);
                    }
                    return entries;
                }
                get autoShowSelected() {
                    return this._autoShowSelected;
                }
                set autoShowSelected(showSelected) {
                    this._autoShowSelected = showSelected;
                }
                showSelectedEntriesOnly() {
                    this.entries.forEach(function (entry) {
                        if (entry.selector.selected) {
                            entry.show();
                        }
                        else {
                            entry.hide();
                        }
                    });
                }
                hideEntries() {
                    this.entries.forEach(function (entry) {
                        entry.hide();
                    });
                }
                triggerChanged() {
                    this.changedCallbacks.forEach(function (callback) {
                        callback();
                    });
                }
                whenChanged(callback) {
                    this.changedCallbacks.push(callback);
                }
            }
            class AllInfo {
                constructor(pages, scrollTop) {
                    this.pages = pages;
                    this.scrollTop = scrollTop;
                }
            }
            class Page {
                constructor(pageNo, _jqContents = null) {
                    this.pageNo = pageNo;
                    this._jqContents = _jqContents;
                    this._visible = true;
                }
                get visible() {
                    return this._visible;
                }
                show() {
                    this._visible = true;
                    this.disp();
                }
                hide() {
                    this._visible = false;
                    this.disp();
                }
                dispose() {
                    if (!this.isContentLoaded())
                        return;
                    this._jqContents.remove();
                    this._jqContents = null;
                    this._entries = null;
                }
                isContentLoaded() {
                    return this.jqContents !== null;
                }
                get entries() {
                    return this._entries;
                }
                get jqContents() {
                    return this._jqContents;
                }
                set jqContents(jqContents) {
                    this._jqContents = jqContents;
                    this._entries = Rocket.Display.Entry.findAll(this.jqContents, true);
                    this.disp();
                    var that = this;
                    for (var i in this._entries) {
                        let entry = this._entries[i];
                        entry.on(Rocket.Display.Entry.EventType.DISPOSED, function () {
                            let j = that._entries.indexOf(entry);
                            if (-1 == j)
                                return;
                            that._entries.splice(j, 1);
                        });
                    }
                }
                disp() {
                    if (this._jqContents === null)
                        return;
                    var that = this;
                    this._entries.forEach(function (entry) {
                        if (that._visible) {
                            entry.show();
                        }
                        else {
                            entry.hide();
                        }
                    });
                }
                removeEntryById(id) {
                    for (var i in this._entries) {
                        if (this._entries[i].id != id)
                            continue;
                        this._entries[i].jqQuery.remove();
                        this._entries.splice(parseInt(i), 1);
                        return;
                    }
                }
            }
        })(Overview = Impl.Overview || (Impl.Overview = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Overview;
        (function (Overview) {
            var $ = jQuery;
            class OverviewPage {
                constructor(jqContainer, overviewContent) {
                    this.jqContainer = jqContainer;
                    this.overviewContent = overviewContent;
                }
                initSelector(selectorObserver) {
                    this.overviewContent.initSelector(selectorObserver);
                }
                static findAll(jqElem) {
                    var oc = new Array();
                    jqElem.find(".rocket-impl-overview").each(function () {
                        oc.push(OverviewPage.from($(this)));
                    });
                    return oc;
                }
                static from(jqElem) {
                    var overviewPage = jqElem.data("rocketImplOverviewPage");
                    if (overviewPage instanceof OverviewPage) {
                        return overviewPage;
                    }
                    var jqForm = jqElem.children("form");
                    var overviewContent = new Overview.OverviewContent(jqElem.find("tbody.rocket-overview-content:first"), Jhtml.Url.create(jqElem.children(".rocket-impl-overview-tools").data("content-url")));
                    overviewContent.initFromDom(jqElem.data("current-page"), jqElem.data("num-pages"), jqElem.data("num-entries"));
                    var pagination = new Pagination(overviewContent);
                    pagination.draw(jqForm.children(".rocket-zone-commands"));
                    var header = new Overview.Header(overviewContent);
                    header.init(jqElem.children(".rocket-impl-overview-tools"));
                    overviewPage = new OverviewPage(jqElem, overviewContent);
                    jqElem.data("rocketImplOverviewPage", overviewPage);
                    return overviewPage;
                }
            }
            Overview.OverviewPage = OverviewPage;
            class Pagination {
                constructor(overviewContent) {
                    this.overviewContent = overviewContent;
                }
                getCurrentPageNo() {
                    return this.overviewContent.currentPageNo;
                }
                getNumPages() {
                    return this.overviewContent.numPages;
                }
                goTo(pageNo) {
                    this.overviewContent.goTo(pageNo);
                    return;
                }
                draw(jqContainer) {
                    var that = this;
                    this.jqPagination = $("<div />", { "class": "rocket-impl-overview-pagination" });
                    jqContainer.append(this.jqPagination);
                    this.jqPagination.append($("<button />", {
                        "type": "button",
                        "class": "rocket-impl-pagination-first btn btn-secondary",
                        "click": function () { that.goTo(1); }
                    }).append($("<i />", {
                        "class": "fa fa-step-backward"
                    })));
                    this.jqPagination.append($("<button />", {
                        "type": "button",
                        "class": "rocket-impl-pagination-prev btn btn-secondary",
                        "click": function () {
                            if (that.getCurrentPageNo() > 1) {
                                that.goTo(that.getCurrentPageNo() - 1);
                            }
                        }
                    }).append($("<i />", {
                        "class": "fa fa-chevron-left"
                    })));
                    this.jqInput = $("<input />", {
                        "class": "rocket-impl-pagination-no",
                        "type": "text",
                        "value": this.getCurrentPageNo()
                    }).on("change", function () {
                        var pageNo = parseInt(that.jqInput.val().toString());
                        if (pageNo === NaN || !that.overviewContent.isPageNoValid(pageNo)) {
                            that.jqInput.val(that.overviewContent.currentPageNo);
                            return;
                        }
                        that.jqInput.val(pageNo);
                        that.overviewContent.goTo(pageNo);
                    });
                    this.jqPagination.append(this.jqInput);
                    this.jqPagination.append($("<button />", {
                        "type": "button",
                        "class": "rocket-impl-pagination-next btn btn-secondary",
                        "click": function () {
                            if (that.getCurrentPageNo() < that.getNumPages()) {
                                that.goTo(that.getCurrentPageNo() + 1);
                            }
                        }
                    }).append($("<i />", {
                        "class": "fa fa-chevron-right"
                    })));
                    this.jqPagination.append($("<button />", {
                        "type": "button",
                        "class": "rocket-impl-pagination-last btn btn-secondary",
                        "click": function () { that.goTo(that.getNumPages()); }
                    }).append($("<i />", {
                        "class": "fa fa-step-forward"
                    })));
                    this.overviewContent.whenContentChanged(function () {
                        if (!that.overviewContent.isInit() || that.overviewContent.selectedOnly || that.overviewContent.numPages <= 1) {
                            that.jqPagination.hide();
                        }
                        else {
                            that.jqPagination.show();
                        }
                        that.jqInput.val(that.overviewContent.currentPageNo);
                    });
                }
            }
            class FixedHeader {
                constructor(numEntries) {
                    this.fixed = false;
                    this.numEntries = numEntries;
                }
                getNumEntries() {
                    return this.numEntries;
                }
                draw(jqHeader, jqTable) {
                    this.jqHeader = jqHeader;
                    this.jqTable = jqTable;
                }
                calcDimensions() {
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
                }
                scrolled() {
                    var headerHeight = this.jqHeader.children().outerHeight();
                    if (this.jqTable.offset().top - $(window).scrollTop() <= this.fixedCssAttrs.top + headerHeight) {
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
                }
                cloneTableHeader() {
                    this.jqTableClone = this.jqTable.clone();
                    this.jqTableClone.css("margin-bottom", 0);
                    this.jqTableClone.children("tbody").remove();
                    this.jqHeader.append(this.jqTableClone);
                    this.jqTableClone.hide();
                    var jqClonedChildren = this.jqTableClone.children("thead").children("tr").children();
                    this.jqTable.children("thead").children("tr").children().each(function (index) {
                        jqClonedChildren.eq(index).innerWidth($(this).innerWidth());
                    });
                }
            }
        })(Overview = Impl.Overview || (Impl.Overview = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Overview;
        (function (Overview) {
            var display = Rocket.Display;
            class MultiEntrySelectorObserver {
                constructor(originalIdReps = new Array()) {
                    this.originalIdReps = originalIdReps;
                    this.identityStrings = {};
                    this.selectors = {};
                    this.selectedIds = originalIdReps;
                }
                observeEntrySelector(selector) {
                    var that = this;
                    var jqCheck = $("<input />", { "type": "checkbox" });
                    selector.jQuery.empty();
                    selector.jQuery.append(jqCheck);
                    jqCheck.change(function () {
                        selector.selected = jqCheck.is(":checked");
                    });
                    selector.whenChanged(function () {
                        jqCheck.prop("checked", selector.selected);
                        that.chSelect(selector.selected, selector.entry.id);
                    });
                    var entry = selector.entry;
                    var id = entry.id;
                    selector.selected = this.containsSelectedId(id);
                    this.selectors[id] = selector;
                    this.identityStrings[id] = entry.identityString;
                    entry.on(display.Entry.EventType.DISPOSED, function () {
                        delete that.selectors[id];
                    });
                    entry.on(display.Entry.EventType.REMOVED, function () {
                        that.chSelect(false, id);
                    });
                }
                containsSelectedId(id) {
                    return -1 < this.selectedIds.indexOf(id);
                }
                chSelect(selected, id) {
                    if (selected) {
                        if (-1 < this.selectedIds.indexOf(id))
                            return;
                        this.selectedIds.push(id);
                        return;
                    }
                    var i;
                    if (-1 < (i = this.selectedIds.indexOf(id))) {
                        this.selectedIds.splice(i, 1);
                    }
                }
                getSelectedIds() {
                    return this.selectedIds;
                }
                getIdentityStringById(id) {
                    if (this.identityStrings[id] !== undefined) {
                        return this.identityStrings[id];
                    }
                    return null;
                }
                getSelectorById(id) {
                    if (this.selectors[id] !== undefined) {
                        return this.selectors[id];
                    }
                    return null;
                }
                setSelectedIds(selectedIds) {
                    this.selectedIds = selectedIds;
                    var that = this;
                    for (var id in this.selectors) {
                        this.selectors[id].selected = that.containsSelectedId(id);
                    }
                }
            }
            Overview.MultiEntrySelectorObserver = MultiEntrySelectorObserver;
            class SingleEntrySelectorObserver {
                constructor(originalId = null) {
                    this.originalId = originalId;
                    this.selectedId = null;
                    this.identityStrings = {};
                    this.selectors = {};
                    this.selectedId = originalId;
                }
                observeEntrySelector(selector) {
                    var that = this;
                    var jqCheck = $("<input />", { "type": "radio" });
                    selector.jQuery.empty();
                    selector.jQuery.append(jqCheck);
                    jqCheck.change(() => {
                        selector.selected = jqCheck.is(":checked");
                    });
                    selector.whenChanged(() => {
                        jqCheck.prop("checked", selector.selected);
                        this.chSelect(selector.selected, selector.entry.id);
                    });
                    var entry = selector.entry;
                    var id = entry.id;
                    selector.selected = this.selectedId === id;
                    this.selectors[id] = selector;
                    this.identityStrings[id] = entry.identityString;
                    entry.on(display.Entry.EventType.DISPOSED, () => {
                        delete this.selectors[id];
                    });
                    entry.on(display.Entry.EventType.REMOVED, function () {
                        this.chSelect(false, id);
                    });
                }
                getSelectedIds() {
                    return [this.selectedId];
                }
                chSelect(selected, id) {
                    if (!selected) {
                        if (this.selectedId === id) {
                            this.selectedId = null;
                        }
                        return;
                    }
                    if (this.selectedId === id)
                        return;
                    this.selectedId = id;
                    for (let id in this.selectors) {
                        if (id === this.selectedId)
                            continue;
                        this.selectors[id].selected = false;
                    }
                }
                getIdentityStringById(id) {
                    if (this.identityStrings[id] !== undefined) {
                        return this.identityStrings[id];
                    }
                    return null;
                }
                getSelectorById(id) {
                    if (this.selectors[id] !== undefined) {
                        return this.selectors[id];
                    }
                    return null;
                }
                setSelectedId(selectedId) {
                    if (this.selectors[selectedId]) {
                        this.selectors[selectedId].selected = true;
                        return;
                    }
                    this.selectedId = selectedId;
                    for (let id in this.selectors) {
                        this.selectors[id].selected = false;
                    }
                }
            }
            Overview.SingleEntrySelectorObserver = SingleEntrySelectorObserver;
        })(Overview = Impl.Overview || (Impl.Overview = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Relation;
        (function (Relation) {
            class AddControlFactory {
                constructor(embeddedEntryRetriever, addLabel, replaceLabel = null) {
                    this.embeddedEntryRetriever = embeddedEntryRetriever;
                    this.addLabel = addLabel;
                    this.replaceLabel = replaceLabel;
                }
                createAdd() {
                    return AddControl.create(this.addLabel, this.embeddedEntryRetriever);
                }
                createReplace() {
                    return AddControl.create(this.replaceLabel, this.embeddedEntryRetriever);
                }
            }
            Relation.AddControlFactory = AddControlFactory;
            class AddControl {
                constructor(jqElem, embeddedEntryRetriever) {
                    this.onNewEntryCallbacks = [];
                    this.disposed = false;
                    this.embeddedEntryRetriever = embeddedEntryRetriever;
                    this.jqElem = jqElem;
                    this.jqButton = jqElem.children("button");
                    this.jqButton.on("mouseenter", () => {
                        this.embeddedEntryRetriever.setPreloadEnabled(true);
                    });
                    this.jqButton.on("click", () => {
                        if (this.isLoading())
                            return;
                        if (this.jqMultiTypeUl) {
                            this.jqMultiTypeUl.toggle();
                            return;
                        }
                        this.block(true);
                        this.embeddedEntryRetriever.lookupNew((embeddedEntry) => {
                            this.examine(embeddedEntry);
                        }, () => {
                            this.block(false);
                        });
                    });
                }
                get jQuery() {
                    return this.jqElem;
                }
                block(blocked) {
                    if (blocked) {
                        this.jqButton.prop("disabled", true);
                        this.jqElem.addClass("rocket-impl-loading");
                    }
                    else {
                        this.jqButton.prop("disabled", false);
                        this.jqElem.removeClass("rocket-impl-loading");
                    }
                }
                examine(embeddedEntry) {
                    this.block(false);
                    if (!embeddedEntry.entryForm.multiEiType) {
                        this.fireCallbacks(embeddedEntry);
                        return;
                    }
                    this.multiTypeEmbeddedEntry = embeddedEntry;
                    this.jqMultiTypeUl = $("<ul />", { "class": "rocket-impl-multi-type-menu" });
                    this.jqElem.append(this.jqMultiTypeUl);
                    let typeMap = embeddedEntry.entryForm.typeMap;
                    for (let typeId in typeMap) {
                        this.jqMultiTypeUl.append($("<li />").append($("<button />", {
                            "type": "button",
                            "text": typeMap[typeId],
                            "click": () => {
                                embeddedEntry.entryForm.curEiTypeId = typeId;
                                this.jqMultiTypeUl.remove();
                                this.jqMultiTypeUl = null;
                                this.multiTypeEmbeddedEntry = null;
                                this.fireCallbacks(embeddedEntry);
                            }
                        })));
                    }
                }
                dispose() {
                    this.disposed = true;
                    this.jqElem.remove();
                    if (this.multiTypeEmbeddedEntry !== null) {
                        this.fireCallbacks(this.multiTypeEmbeddedEntry);
                        this.multiTypeEmbeddedEntry = null;
                    }
                }
                isLoading() {
                    return this.jqElem.hasClass("rocket-impl-loading");
                }
                fireCallbacks(embeddedEntry) {
                    if (this.disposed)
                        return;
                    this.onNewEntryCallbacks.forEach(function (callback) {
                        callback(embeddedEntry);
                    });
                }
                onNewEmbeddedEntry(callback) {
                    this.onNewEntryCallbacks.push(callback);
                }
                static create(label, embeddedEntryRetriever) {
                    return new AddControl($("<div />", { "class": "rocket-impl-add-entry" })
                        .append($("<button />", { "text": label, "type": "button", "class": "btn btn-block btn-secondary" })), embeddedEntryRetriever);
                }
            }
            Relation.AddControl = AddControl;
        })(Relation = Impl.Relation || (Impl.Relation = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Relation;
        (function (Relation) {
            class EmbeddedEntryRetriever {
                constructor(lookupUrlStr, propertyPath, draftMode, startKey = null, keyPrefix = null) {
                    this.preloadEnabled = false;
                    this.preloadedResponseObjects = new Array();
                    this.pendingLookups = new Array();
                    this.urlStr = lookupUrlStr;
                    this.propertyPath = propertyPath;
                    this.draftMode = draftMode;
                    this.startKey = startKey;
                    this.keyPrefix = keyPrefix;
                }
                setPreloadEnabled(preloadEnabled) {
                    if (!this.preloadEnabled && preloadEnabled && this.preloadedResponseObjects.length == 0) {
                        this.load();
                    }
                    this.preloadEnabled = preloadEnabled;
                }
                lookupNew(doneCallback, failCallback = null) {
                    this.pendingLookups.push({ "doneCallback": doneCallback, "failCallback": failCallback });
                    this.check();
                    this.load();
                }
                check() {
                    if (this.pendingLookups.length == 0 || this.preloadedResponseObjects.length == 0)
                        return;
                    var pendingLookup = this.pendingLookups.shift();
                    let snippet = this.preloadedResponseObjects.shift();
                    var embeddedEntry = new EmbeddedEntry($(snippet.elements), false);
                    pendingLookup.doneCallback(embeddedEntry);
                    snippet.markAttached();
                }
                load() {
                    let url = Jhtml.Url.create(this.urlStr).extR(null, {
                        "propertyPath": this.propertyPath + (this.startKey !== null ? "[" + this.keyPrefix + (this.startKey++) + "]" : ""),
                        "draft": this.draftMode ? 1 : 0
                    });
                    Jhtml.lookupModel(url)
                        .then((model) => {
                        this.doneResponse(model.snippet);
                    })
                        .catch(e => {
                        this.failResponse();
                        throw e;
                    });
                }
                failResponse() {
                    if (this.pendingLookups.length == 0)
                        return;
                    var pendingLookup = this.pendingLookups.shift();
                    if (pendingLookup.failCallback !== null) {
                        pendingLookup.failCallback();
                    }
                }
                doneResponse(snippet) {
                    this.preloadedResponseObjects.push(snippet);
                    this.check();
                }
            }
            Relation.EmbeddedEntryRetriever = EmbeddedEntryRetriever;
            class EmbeddedEntry {
                constructor(jqEntry, readOnly) {
                    this.readOnly = readOnly;
                    this.entryGroup = Rocket.Display.StructureElement.from(jqEntry, true);
                    this.bodyGroup = Rocket.Display.StructureElement.from(jqEntry.children(".rocket-impl-body"), true);
                    this.jqOrderIndex = jqEntry.children(".rocket-impl-order-index").hide();
                    this.jqSummary = jqEntry.children(".rocket-impl-summary");
                    this.jqPageCommands = this.bodyGroup.jQuery.children(".rocket-zone-commands");
                    if (readOnly) {
                        var rcl = new Rocket.Display.CommandList(this.jqSummary.children(".rocket-simple-commands"), true);
                        this.jqRedFocusButton = rcl.createJqCommandButton({ iconType: "fa fa-file", label: "Detail",
                            severity: Rocket.Display.Severity.SECONDARY });
                    }
                    else {
                        this._entryForm = Rocket.Display.EntryForm.firstOf(jqEntry);
                        var ecl = this.bodyGroup.getToolbar().getCommandList();
                        this.jqExpMoveUpButton = ecl.createJqCommandButton({ iconType: "fa fa-arrow-up", label: "Move up" });
                        this.jqExpMoveDownButton = ecl.createJqCommandButton({ iconType: "fa fa-arrow-down", label: "Move down" });
                        this.jqExpRemoveButton = ecl.createJqCommandButton({ iconType: "fa fa-times", label: "Remove",
                            severity: Rocket.Display.Severity.DANGER });
                        var rcl = new Rocket.Display.CommandList(this.jqSummary.children(".rocket-simple-commands"), true);
                        this.jqRedFocusButton = rcl.createJqCommandButton({ iconType: "fa fa-pencil", label: "Edit",
                            severity: Rocket.Display.Severity.WARNING });
                        this.jqRedRemoveButton = rcl.createJqCommandButton({ iconType: "fa fa-times", label: "Remove",
                            severity: Rocket.Display.Severity.DANGER });
                    }
                    this.reduce();
                    jqEntry.data("rocketImplEmbeddedEntry", this);
                }
                get entryForm() {
                    return this._entryForm;
                }
                onMove(callback) {
                    if (this.readOnly)
                        return;
                    this.jqExpMoveUpButton.click(function () {
                        callback(true);
                    });
                    this.jqExpMoveDownButton.click(function () {
                        callback(false);
                    });
                }
                onRemove(callback) {
                    if (this.readOnly)
                        return;
                    this.jqExpRemoveButton.click(function () {
                        callback();
                    });
                    this.jqRedRemoveButton.click(function () {
                        callback();
                    });
                }
                onFocus(callback) {
                    this.jqRedFocusButton.click(function () {
                        callback();
                    });
                    this.bodyGroup.onShow(function () {
                        callback();
                    });
                }
                get jQuery() {
                    return this.entryGroup.jQuery;
                }
                getExpandedCommandList() {
                    return this.bodyGroup.getToolbar().getCommandList();
                }
                expand(asPartOfList = true) {
                    this.entryGroup.show();
                    this.jqSummary.hide();
                    this.bodyGroup.show();
                    this.entryGroup.setGroup(true);
                    if (asPartOfList) {
                        this.jqPageCommands.hide();
                    }
                    else {
                        this.jqPageCommands.show();
                    }
                    if (this.readOnly)
                        return;
                    if (asPartOfList) {
                        this.jqExpMoveUpButton.show();
                        this.jqExpMoveDownButton.show();
                        this.jqExpRemoveButton.show();
                        this.jqPageCommands.hide();
                    }
                    else {
                        this.jqExpMoveUpButton.hide();
                        this.jqExpMoveDownButton.hide();
                        this.jqExpRemoveButton.hide();
                        this.jqPageCommands.show();
                    }
                }
                reduce() {
                    this.entryGroup.show();
                    this.jqSummary.show();
                    this.bodyGroup.hide();
                    let jqContentType = this.jqSummary.find(".rocket-impl-content-type:first");
                    jqContentType.children("span").text(this.entryForm.curGenericLabel);
                    jqContentType.children("i").attr("class", this.entryForm.curGenericIconType);
                    this.entryGroup.jQuery.removeClass("rocket-group");
                }
                hide() {
                    this.entryGroup.hide();
                }
                setOrderIndex(orderIndex) {
                    this.jqOrderIndex.val(orderIndex);
                }
                getOrderIndex() {
                    return parseInt(this.jqOrderIndex.val());
                }
                setMoveUpEnabled(enabled) {
                    if (this.readOnly)
                        return;
                    if (enabled) {
                        this.jqExpMoveUpButton.show();
                    }
                    else {
                        this.jqExpMoveUpButton.hide();
                    }
                }
                setMoveDownEnabled(enabled) {
                    if (this.readOnly)
                        return;
                    if (enabled) {
                        this.jqExpMoveDownButton.show();
                    }
                    else {
                        this.jqExpMoveDownButton.hide();
                    }
                }
                dispose() {
                    this.jQuery.remove();
                }
            }
            Relation.EmbeddedEntry = EmbeddedEntry;
        })(Relation = Impl.Relation || (Impl.Relation = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Relation;
        (function (Relation) {
            var cmd = Rocket.Cmd;
            var display = Rocket.Display;
            var $ = jQuery;
            class ToMany {
                constructor(selector = null, embedded = null) {
                    this.selector = selector;
                    this.embedded = embedded;
                }
                static from(jqToMany) {
                    var toMany = jqToMany.data("rocketImplToMany");
                    if (toMany instanceof ToMany) {
                        return toMany;
                    }
                    let toManySelector = null;
                    let jqSelector = jqToMany.children(".rocket-impl-selector");
                    if (jqSelector.length > 0) {
                        toManySelector = new ToManySelector(jqSelector, jqSelector.find("li.rocket-new-entry").detach());
                        jqSelector.find("ul li").each(function () {
                            var entry = new SelectedEntry($(this));
                            entry.label = toManySelector.determineIdentityString(entry.idRep);
                            toManySelector.addSelectedEntry(entry);
                        });
                    }
                    var jqCurrents = jqToMany.children(".rocket-impl-currents");
                    var jqNews = jqToMany.children(".rocket-impl-news");
                    var addControlFactory = null;
                    let toManyEmbedded = null;
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
                            var entryFormRetriever = new Relation.EmbeddedEntryRetriever(jqNews.data("new-entry-form-url"), propertyPath, jqNews.data("draftMode"), startKey, "n");
                            addControlFactory = new Relation.AddControlFactory(entryFormRetriever, jqNews.data("add-item-label"));
                        }
                        toManyEmbedded = new ToManyEmbedded(jqToMany, addControlFactory);
                        jqCurrents.children(".rocket-impl-entry").each(function () {
                            toManyEmbedded.addEntry(new Relation.EmbeddedEntry($(this), toManyEmbedded.isReadOnly()));
                        });
                        jqNews.children(".rocket-impl-entry").each(function () {
                            toManyEmbedded.addEntry(new Relation.EmbeddedEntry($(this), toManyEmbedded.isReadOnly()));
                        });
                    }
                    var toMany = new ToMany(toManySelector, toManyEmbedded);
                    jqToMany.data("rocketImplToMany", toMany);
                    return toMany;
                }
            }
            Relation.ToMany = ToMany;
            class ToManySelector {
                constructor(jqElem, jqNewEntrySkeleton) {
                    this.jqElem = jqElem;
                    this.jqNewEntrySkeleton = jqNewEntrySkeleton;
                    this.entries = new Array();
                    this.browserLayer = null;
                    this.browserSelectorObserver = null;
                    this.jqElem = jqElem;
                    this.jqUl = jqElem.children("ul");
                    this.originalIdReps = jqElem.data("original-id-reps");
                    this.identityStrings = jqElem.data("identity-strings");
                    this.init();
                }
                determineIdentityString(idRep) {
                    return this.identityStrings[idRep];
                }
                init() {
                    var jqCommandList = $("<div />");
                    this.jqElem.append(jqCommandList);
                    var that = this;
                    var commandList = new display.CommandList(jqCommandList);
                    commandList.createJqCommandButton({ label: this.jqElem.data("select-label") })
                        .mouseenter(function () {
                        that.loadBrowser();
                    })
                        .click(function () {
                        that.openBrowser();
                    });
                    commandList.createJqCommandButton({ label: this.jqElem.data("reset-label") }).click(function () {
                        that.reset();
                    });
                    commandList.createJqCommandButton({ label: this.jqElem.data("clear-label") }).click(function () {
                        that.clear();
                    });
                }
                createSelectedEntry(idRep, identityString = null) {
                    var entry = new SelectedEntry(this.jqNewEntrySkeleton.clone().appendTo(this.jqUl));
                    entry.idRep = idRep;
                    if (identityString !== null) {
                        entry.label = identityString;
                    }
                    else {
                        entry.label = this.determineIdentityString(idRep);
                    }
                    this.addSelectedEntry(entry);
                    return entry;
                }
                addSelectedEntry(entry) {
                    this.entries.push(entry);
                    var that = this;
                    entry.commandList.createJqCommandButton({ iconType: "fa fa-times", label: this.jqElem.data("remove-entry-label") }).click(function () {
                        that.removeSelectedEntry(entry);
                    });
                }
                removeSelectedEntry(entry) {
                    for (var i in this.entries) {
                        if (this.entries[i] !== entry)
                            continue;
                        entry.jQuery.remove();
                        this.entries.splice(parseInt(i), 1);
                    }
                }
                reset() {
                    this.clear();
                    for (let idRep of this.originalIdReps) {
                        this.createSelectedEntry(idRep);
                    }
                }
                clear() {
                    for (var i in this.entries) {
                        this.entries[i].jQuery.remove();
                    }
                    this.entries.splice(0, this.entries.length);
                }
                loadBrowser() {
                    if (this.browserLayer !== null)
                        return;
                    var that = this;
                    this.browserLayer = Rocket.getContainer().createLayer(cmd.Zone.of(this.jqElem));
                    this.browserLayer.hide();
                    this.browserLayer.on(cmd.Layer.EventType.CLOSE, function () {
                        that.browserLayer = null;
                        that.browserSelectorObserver = null;
                    });
                    let url = this.jqElem.data("overview-tools-url");
                    this.browserLayer.monitor.exec(url).then(() => {
                        that.iniBrowserPage(this.browserLayer.getZoneByUrl(url));
                    });
                }
                iniBrowserPage(context) {
                    if (this.browserLayer === null)
                        return;
                    var ocs = Impl.Overview.OverviewPage.findAll(context.jQuery);
                    if (ocs.length == 0)
                        return;
                    ocs[0].initSelector(this.browserSelectorObserver = new Impl.Overview.MultiEntrySelectorObserver());
                    var that = this;
                    context.menu.partialCommandList.createJqCommandButton({ label: this.jqElem.data("select-label") }).click(function () {
                        that.updateSelection();
                        context.layer.hide();
                    });
                    context.menu.partialCommandList.createJqCommandButton({ label: this.jqElem.data("cancel-label") }).click(function () {
                        context.layer.hide();
                    });
                    this.updateBrowser();
                }
                openBrowser() {
                    this.loadBrowser();
                    this.updateBrowser();
                    this.browserLayer.show();
                }
                updateBrowser() {
                    if (this.browserSelectorObserver === null)
                        return;
                    var selectedIds = new Array();
                    this.entries.forEach(function (entry) {
                        selectedIds.push(entry.idRep);
                    });
                    this.browserSelectorObserver.setSelectedIds(selectedIds);
                }
                updateSelection() {
                    if (this.browserSelectorObserver === null)
                        return;
                    this.clear();
                    var that = this;
                    this.browserSelectorObserver.getSelectedIds().forEach(function (id) {
                        var identityString = that.browserSelectorObserver.getIdentityStringById(id);
                        if (identityString !== null) {
                            that.createSelectedEntry(id, identityString);
                            return;
                        }
                        that.createSelectedEntry(id);
                    });
                }
            }
            class SelectedEntry {
                constructor(jqElem) {
                    this.jqElem = jqElem;
                    jqElem.prepend(this.jqLabel = $("<span />"));
                    this.cmdList = new display.CommandList($("<div />").appendTo(jqElem), true);
                    this.jqInput = jqElem.children("input").hide();
                }
                get jQuery() {
                    return this.jqElem;
                }
                get commandList() {
                    return this.cmdList;
                }
                get label() {
                    return this.jqLabel.text();
                }
                set label(label) {
                    this.jqLabel.text(label);
                }
                get idRep() {
                    return this.jqInput.val().toString();
                }
                set idRep(idRep) {
                    this.jqInput.val(idRep);
                }
            }
            class ToManyEmbedded {
                constructor(jqToMany, addButtonFactory = null) {
                    this.compact = true;
                    this.sortable = true;
                    this.entries = new Array();
                    this.expandPage = null;
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
                    let jqGroup = this.jqToMany.children(".rocket-group");
                    if (jqGroup.length > 0) {
                        jqGroup.append(this.jqEmbedded);
                    }
                    else {
                        this.jqToMany.append(this.jqEmbedded);
                    }
                    this.jqEntries = $("<div />");
                    this.jqEmbedded.append(this.jqEntries);
                    if (this.compact) {
                        var structureElement = Rocket.Display.StructureElement.of(this.jqEmbedded);
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
                            let that = this;
                            jqButton.click(function () {
                                that.expand();
                            });
                        }
                    }
                    if (this.sortable) {
                        this.initSortable();
                    }
                    this.changed();
                }
                isReadOnly() {
                    return this.addControlFactory === null;
                }
                changed() {
                    for (let i in this.entries) {
                        let index = parseInt(i);
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
                            this.firstAddControl.jQuery.hide();
                        }
                        this.lastAddControl.jQuery.hide();
                    }
                    else {
                        if (this.firstAddControl !== null) {
                            this.firstAddControl.jQuery.show();
                        }
                        this.lastAddControl.jQuery.show();
                    }
                }
                createFirstAddControl() {
                    var addControl = this.addControlFactory.createAdd();
                    var that = this;
                    this.jqEmbedded.prepend(addControl.jQuery);
                    addControl.onNewEmbeddedEntry(function (newEntry) {
                        that.insertEntry(newEntry);
                    });
                    return addControl;
                }
                createEntryAddControl(entry) {
                    var addControl = this.addControlFactory.createAdd();
                    var that = this;
                    this.entryAddControls.push(addControl);
                    addControl.jQuery.insertBefore(entry.jQuery);
                    addControl.onNewEmbeddedEntry(function (newEntry) {
                        that.insertEntry(newEntry, entry);
                    });
                    return addControl;
                }
                createLastAddControl() {
                    var addControl = this.addControlFactory.createAdd();
                    var that = this;
                    this.jqEmbedded.append(addControl.jQuery);
                    addControl.onNewEmbeddedEntry(function (newEntry) {
                        that.addEntry(newEntry);
                    });
                    return addControl;
                }
                insertEntry(entry, beforeEntry = null) {
                    entry.jQuery.detach();
                    if (beforeEntry === null) {
                        this.entries.unshift(entry);
                        this.jqEntries.prepend(entry.jQuery);
                    }
                    else {
                        entry.jQuery.insertBefore(beforeEntry.jQuery);
                        this.entries.splice(beforeEntry.getOrderIndex(), 0, entry);
                    }
                    this.initEntry(entry);
                    this.changed();
                }
                addEntry(entry) {
                    entry.setOrderIndex(this.entries.length);
                    this.entries.push(entry);
                    this.jqEntries.append(entry.jQuery);
                    this.initEntry(entry);
                    if (this.isReadOnly())
                        return;
                    this.changed();
                }
                switchIndex(oldIndex, newIndex) {
                    var entry = this.entries[oldIndex];
                    this.entries[oldIndex] = this.entries[newIndex];
                    this.entries[newIndex] = entry;
                    this.changed();
                }
                initEntry(entry) {
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
                            that.entries[oldIndex].jQuery.insertBefore(that.entries[newIndex].jQuery);
                        }
                        else {
                            that.entries[oldIndex].jQuery.insertAfter(that.entries[newIndex].jQuery);
                        }
                        that.switchIndex(oldIndex, newIndex);
                    });
                    entry.onRemove(function () {
                        that.entries.splice(entry.getOrderIndex(), 1);
                        entry.jQuery.remove();
                        that.changed();
                    });
                    entry.onFocus(function () {
                        that.expand(entry);
                    });
                }
                initSortable() {
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
                }
                enabledSortable() {
                    this.jqEntries.sortable("enable");
                    this.jqEntries.disableSelection();
                }
                disableSortable() {
                    this.jqEntries.sortable("disable");
                    this.jqEntries.enableSelection();
                }
                isExpanded() {
                    return this.expandPage !== null;
                }
                isPartialExpaned() {
                    return this.dominantEntry !== null;
                }
                expand(dominantEntry = null) {
                    if (this.isExpanded())
                        return;
                    if (this.sortable) {
                        this.disableSortable();
                    }
                    this.dominantEntry = dominantEntry;
                    this.expandPage = Rocket.getContainer().createLayer().createZone(window.location.href);
                    this.jqEmbedded.detach();
                    let contentJq = $("<div />", { "class": "rocket-content" }).append(this.jqEmbedded);
                    this.expandPage.applyContent(contentJq);
                    $("<header></header>").insertBefore(contentJq);
                    this.expandPage.layer.pushHistoryEntry(window.location.href);
                    for (let i in this.entries) {
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
                    var jqCommandButton = this.expandPage.menu.commandList
                        .createJqCommandButton({ iconType: "fa fa-times", label: this.closeLabel, severity: display.Severity.WARNING }, true);
                    jqCommandButton.click(function () {
                        that.expandPage.layer.close();
                    });
                    this.expandPage.on(cmd.Zone.EventType.CLOSE, function () {
                        that.reduce();
                    });
                    this.changed();
                }
                reduce() {
                    if (!this.isExpanded())
                        return;
                    this.dominantEntry = null;
                    this.expandPage = null;
                    this.jqEmbedded.detach();
                    this.jqToMany.append(this.jqEmbedded);
                    for (let i in this.entries) {
                        this.entries[i].reduce();
                    }
                    if (this.sortable) {
                        this.enabledSortable();
                    }
                    this.changed();
                }
            }
        })(Relation = Impl.Relation || (Impl.Relation = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Relation;
        (function (Relation) {
            var cmd = Rocket.Cmd;
            var display = Rocket.Display;
            class ToOne {
                constructor(toOneSelector = null, embedded = null) {
                    this.toOneSelector = toOneSelector;
                    this.embedded = embedded;
                    if (toOneSelector && embedded) {
                        embedded.whenChanged(function () {
                            if (embedded.currentEntry || embedded.newEntry) {
                                toOneSelector.jQuery.hide();
                            }
                            else {
                                toOneSelector.jQuery.show();
                            }
                        });
                    }
                }
                static from(jqToOne) {
                    let toOne = jqToOne.data("rocketImplToOne");
                    if (toOne instanceof ToOne) {
                        return toOne;
                    }
                    let toOneSelector = null;
                    let jqSelector = jqToOne.children(".rocket-impl-selector");
                    if (jqSelector.length > 0) {
                        toOneSelector = new ToOneSelector(jqSelector);
                    }
                    let jqCurrent = jqToOne.children(".rocket-impl-current");
                    let jqNew = jqToOne.children(".rocket-impl-new");
                    let addControlFactory = null;
                    let toOneEmbedded = null;
                    if (jqCurrent.length > 0 || jqNew.length > 0) {
                        if (jqNew.length > 0) {
                            var propertyPath = jqNew.data("property-path");
                            var entryFormRetriever = new Relation.EmbeddedEntryRetriever(jqNew.data("new-entry-form-url"), propertyPath, jqNew.data("draftMode"));
                            addControlFactory = new Relation.AddControlFactory(entryFormRetriever, jqNew.data("add-item-label"), jqNew.data("replace-item-label"));
                        }
                        toOneEmbedded = new ToOneEmbedded(jqToOne, addControlFactory);
                        jqCurrent.children(".rocket-impl-entry").each(function () {
                            toOneEmbedded.currentEntry = new Relation.EmbeddedEntry($(this), toOneEmbedded.isReadOnly());
                        });
                        jqNew.children(".rocket-impl-entry").each(function () {
                            toOneEmbedded.newEntry = new Relation.EmbeddedEntry($(this), toOneEmbedded.isReadOnly());
                        });
                    }
                    toOne = new ToOne(toOneSelector, toOneEmbedded);
                    jqToOne.data("rocketImplToOne", toOne);
                    return toOne;
                }
            }
            Relation.ToOne = ToOne;
            class ToOneEmbedded {
                constructor(jqToOne, addButtonFactory = null) {
                    this.compact = true;
                    this.expandPage = null;
                    this.changedCallbacks = new Array();
                    this.jqToOne = jqToOne;
                    this.addControlFactory = addButtonFactory;
                    this.compact = (true == jqToOne.data("compact"));
                    this.closeLabel = jqToOne.data("close-label");
                    this.jqEmbedded = $("<div />", {
                        "class": "rocket-impl-embedded"
                    });
                    this.jqToOne.append(this.jqEmbedded);
                    this.jqEntries = $("<div />");
                    this.jqEmbedded.append(this.jqEntries);
                    this.changed();
                }
                isReadOnly() {
                    return this.addControlFactory === null;
                }
                changed() {
                    if (this.addControlFactory === null)
                        return;
                    if (!this.addControl) {
                        this.addControl = this.createAddControl();
                    }
                    if (!this.firstReplaceControl) {
                        this.firstReplaceControl = this.createReplaceControl(true);
                    }
                    if (!this.secondReplaceControl) {
                        this.secondReplaceControl = this.createReplaceControl(false);
                    }
                    if (this.currentEntry || this.newEntry) {
                        this.addControl.jQuery.hide();
                        this.firstReplaceControl.jQuery.show();
                        this.secondReplaceControl.jQuery.show();
                    }
                    else {
                        this.addControl.jQuery.show();
                        this.firstReplaceControl.jQuery.hide();
                        this.secondReplaceControl.jQuery.hide();
                    }
                    this.triggerChanged();
                }
                createReplaceControl(prepend) {
                    var addControl = this.addControlFactory.createReplace();
                    if (prepend) {
                        this.jqEmbedded.prepend(addControl.jQuery);
                    }
                    else {
                        this.jqEmbedded.append(addControl.jQuery);
                    }
                    addControl.onNewEmbeddedEntry((newEntry) => {
                        this.newEntry = newEntry;
                    });
                    return addControl;
                }
                createAddControl() {
                    var addControl = this.addControlFactory.createAdd();
                    this.jqEmbedded.append(addControl.jQuery);
                    addControl.onNewEmbeddedEntry((newEntry) => {
                        this.newEntry = newEntry;
                    });
                    return addControl;
                }
                get currentEntry() {
                    return this._currentEntry;
                }
                set currentEntry(entry) {
                    if (this._currentEntry === entry)
                        return;
                    if (this._currentEntry) {
                        this._currentEntry.dispose();
                    }
                    this._currentEntry = entry;
                    if (!entry)
                        return;
                    if (this.newEntry) {
                        this._currentEntry.jQuery.detach();
                    }
                    entry.onRemove(() => {
                        this._currentEntry.dispose();
                        this._currentEntry = null;
                        this.changed();
                    });
                    this.initEntry(entry);
                    this.changed();
                }
                get newEntry() {
                    return this._newEntry;
                }
                set newEntry(entry) {
                    if (this._newEntry === entry)
                        return;
                    if (this._newEntry) {
                        this._newEntry.dispose();
                    }
                    this._newEntry = entry;
                    if (!entry)
                        return;
                    if (this.currentEntry) {
                        this.currentEntry.jQuery.detach();
                    }
                    entry.onRemove(() => {
                        this._newEntry.dispose();
                        this._newEntry = null;
                        if (this.currentEntry) {
                            this.currentEntry.jQuery.appendTo(this.jqEntries);
                        }
                        this.changed();
                    });
                    this.initEntry(entry);
                    this.changed();
                }
                initEntry(entry) {
                    this.jqEntries.append(entry.jQuery);
                    if (this.isExpanded()) {
                        entry.expand(false);
                    }
                    else {
                        entry.reduce();
                    }
                    entry.onFocus(() => {
                        this.expand();
                    });
                }
                isExpanded() {
                    return this.expandPage !== null;
                }
                expand() {
                    if (this.isExpanded())
                        return;
                    this.expandPage = Rocket.getContainer().createLayer().createZone(window.location.href);
                    this.jqEmbedded.detach();
                    let contentJq = $("<div />", { "class": "rocket-content" }).append(this.jqEmbedded);
                    this.expandPage.applyContent(contentJq);
                    $("<header></header>").insertBefore(contentJq);
                    this.expandPage.layer.pushHistoryEntry(window.location.href);
                    if (this.newEntry) {
                        this.newEntry.expand(false);
                    }
                    if (this.currentEntry) {
                        this.currentEntry.expand(false);
                    }
                    var jqCommandButton = this.expandPage.menu.commandList
                        .createJqCommandButton({ iconType: "fa fa-times", label: this.closeLabel, severity: display.Severity.WARNING }, true);
                    jqCommandButton.click(() => {
                        this.expandPage.layer.close();
                    });
                    this.expandPage.on(cmd.Zone.EventType.CLOSE, () => {
                        this.reduce();
                    });
                    this.changed();
                }
                reduce() {
                    if (!this.isExpanded())
                        return;
                    this.expandPage = null;
                    this.jqEmbedded.detach();
                    this.jqToOne.append(this.jqEmbedded);
                    if (this.newEntry) {
                        this.newEntry.reduce();
                    }
                    if (this.currentEntry) {
                        this.currentEntry.reduce();
                    }
                    this.changed();
                }
                triggerChanged() {
                    for (let callback of this.changedCallbacks) {
                        callback();
                    }
                }
                whenChanged(callback) {
                    this.changedCallbacks.push(callback);
                }
            }
            class ToOneSelector {
                constructor(jqElem) {
                    this.jqElem = jqElem;
                    this.browserLayer = null;
                    this.browserSelectorObserver = null;
                    this.jqElem = jqElem;
                    this.jqInput = jqElem.children("input").hide();
                    this.originalIdRep = jqElem.data("original-id-rep");
                    this.identityStrings = jqElem.data("identity-strings");
                    this.init();
                    this.selectEntry(this.selectedIdRep);
                }
                get jQuery() {
                    return this.jqElem;
                }
                get selectedIdRep() {
                    let idRep = this.jqInput.val().toString();
                    if (idRep.length == 0)
                        return null;
                    return idRep;
                }
                init() {
                    this.jqSelectedEntry = $("<div />");
                    this.jqSelectedEntry.append(this.jqEntryLabel = $("<span />", { "text": this.identityStrings[this.originalIdRep] }));
                    new display.CommandList($("<div />").appendTo(this.jqSelectedEntry), true)
                        .createJqCommandButton({ iconType: "fa fa-times", label: this.jqElem.data("remove-entry-label") })
                        .click(() => {
                        this.clear();
                    });
                    this.jqElem.append(this.jqSelectedEntry);
                    var jqCommandList = $("<div />");
                    this.jqElem.append(jqCommandList);
                    var commandList = new display.CommandList(jqCommandList);
                    commandList.createJqCommandButton({ label: this.jqElem.data("select-label") })
                        .mouseenter(() => {
                        this.loadBrowser();
                    })
                        .click(() => {
                        this.openBrowser();
                    });
                    commandList.createJqCommandButton({ label: this.jqElem.data("reset-label") })
                        .click(() => {
                        this.reset();
                    });
                }
                selectEntry(idRep, identityString = null) {
                    this.jqInput.val(idRep);
                    if (idRep === null) {
                        this.jqSelectedEntry.hide();
                        return;
                    }
                    this.jqSelectedEntry.show();
                    if (identityString === null) {
                        identityString = this.identityStrings[idRep];
                    }
                    this.jqEntryLabel.text(identityString);
                }
                reset() {
                    this.selectEntry(this.originalIdRep);
                }
                clear() {
                    this.selectEntry(null);
                }
                loadBrowser() {
                    if (this.browserLayer !== null)
                        return;
                    var that = this;
                    this.browserLayer = Rocket.getContainer().createLayer(cmd.Zone.of(this.jqElem));
                    this.browserLayer.hide();
                    this.browserLayer.on(cmd.Layer.EventType.CLOSE, function () {
                        that.browserLayer = null;
                        that.browserSelectorObserver = null;
                    });
                    let url = this.jqElem.data("overview-tools-url");
                    this.browserLayer.monitor.exec(url).then(() => {
                        that.iniBrowserPage(this.browserLayer.getZoneByUrl(url));
                    });
                }
                iniBrowserPage(context) {
                    if (this.browserLayer === null)
                        return;
                    var ocs = Impl.Overview.OverviewPage.findAll(context.jQuery);
                    if (ocs.length == 0)
                        return;
                    ocs[0].initSelector(this.browserSelectorObserver = new Impl.Overview.SingleEntrySelectorObserver());
                    var that = this;
                    context.menu.partialCommandList.createJqCommandButton({ label: this.jqElem.data("select-label") }).click(function () {
                        that.updateSelection();
                        context.layer.hide();
                    });
                    context.menu.partialCommandList.createJqCommandButton({ label: this.jqElem.data("cancel-label") }).click(function () {
                        context.layer.hide();
                    });
                    this.updateBrowser();
                }
                openBrowser() {
                    this.loadBrowser();
                    this.updateBrowser();
                    this.browserLayer.show();
                }
                updateBrowser() {
                    if (this.browserSelectorObserver === null)
                        return;
                    this.browserSelectorObserver.setSelectedId(this.selectedIdRep);
                }
                updateSelection() {
                    if (this.browserSelectorObserver === null)
                        return;
                    this.clear();
                    this.browserSelectorObserver.getSelectedIds().forEach((id) => {
                        var identityString = this.browserSelectorObserver.getIdentityStringById(id);
                        if (identityString !== null) {
                            this.selectEntry(id, identityString);
                            return;
                        }
                        this.selectEntry(id);
                    });
                }
            }
        })(Relation = Impl.Relation || (Impl.Relation = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
//# sourceMappingURL=rocket.js.map