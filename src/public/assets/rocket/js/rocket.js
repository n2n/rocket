var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var Rocket;
(function (Rocket) {
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
        Jhtml.ready(function () {
            initializer.scan();
        });
        (function () {
            Jhtml.ready(function () {
                $(".rocket-impl-overview").each(function () {
                    Rocket.Impl.Overview.OverviewPage.from($(this));
                });
            });
            Jhtml.ready(function () {
                $(".rocket-impl-overview").each(function () {
                    Rocket.Impl.Overview.OverviewPage.from($(this));
                });
            });
        })();
        (function () {
            $("form.rocket-impl-form").each(function () {
                Rocket.Impl.Form.from($(this));
            });
            Jhtml.ready(function () {
                $("form.rocket-impl-form").each(function () {
                    Rocket.Impl.Form.from($(this));
                });
            });
        })();
        (function () {
            $(".rocket-impl-to-many").each(function () {
                Rocket.Impl.Relation.ToMany.from($(this));
            });
            Jhtml.ready(function () {
                $(".rocket-impl-to-many").each(function () {
                    Rocket.Impl.Relation.ToMany.from($(this));
                });
            });
        })();
        (function () {
            $(".rocket-impl-to-one").each(function () {
                Rocket.Impl.Relation.ToOne.from($(this));
            });
            Jhtml.ready(function () {
                $(".rocket-impl-to-one").each(function () {
                    Rocket.Impl.Relation.ToOne.from($(this));
                });
            });
        })();
        (function () {
            var t = new Rocket.Impl.Translator(container);
            t.scan();
            Jhtml.ready(function () {
                t.scan();
            });
        })();
    });
    function scan(context) {
        if (context === void 0) { context = null; }
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
        return Rocket.Cmd.Page.of($(elem));
    }
    Rocket.contextOf = contextOf;
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Cmd;
    (function (Cmd) {
        var Blocker = (function () {
            function Blocker(container) {
                this.container = container;
                this.jqBlocker = null;
                for (var _i = 0, _a = container.layers; _i < _a.length; _i++) {
                    var layer = _a[_i];
                    this.observeLayer(layer);
                }
                var that = this;
                container.layerOn(Cmd.Container.LayerEventType.ADDED, function (layer) {
                    that.observeLayer(layer);
                    that.check();
                });
            }
            Blocker.prototype.observeLayer = function (layer) {
                var _this = this;
                for (var _i = 0, _a = layer.contexts; _i < _a.length; _i++) {
                    var context = _a[_i];
                    this.observePage(context);
                }
                layer.onNewPage(function (context) {
                    _this.observePage(context);
                    _this.check();
                });
            };
            Blocker.prototype.observePage = function (context) {
                var _this = this;
                var checkCallback = function () {
                    _this.check();
                };
                context.on(Cmd.Page.EventType.SHOW, checkCallback);
                context.on(Cmd.Page.EventType.HIDE, checkCallback);
                context.on(Cmd.Page.EventType.CLOSE, checkCallback);
                context.on(Cmd.Page.EventType.CONTENT_CHANGED, checkCallback);
                context.on(Cmd.Page.EventType.BLOCKED_CHANGED, checkCallback);
            };
            Blocker.prototype.init = function (jqContainer) {
                if (this.jqContainer) {
                    throw new Error("Blocker already initialized.");
                }
                this.jqContainer = jqContainer;
                this.check();
            };
            Blocker.prototype.check = function () {
                if (!this.jqContainer || !this.container.currentLayer.currentPage)
                    return;
                if (!this.container.currentLayer.currentPage.locked) {
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
                        "class": "rocket-context-block",
                        "css": {
                            "position": "fixed",
                            "top": 0,
                            "left": 0,
                            "right": 0,
                            "bottom": 0
                        }
                    })
                        .appendTo(this.jqContainer);
            };
            return Blocker;
        }());
        Cmd.Blocker = Blocker;
    })(Cmd = Rocket.Cmd || (Rocket.Cmd = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Cmd;
    (function (Cmd) {
        var Container = (function () {
            function Container(jqContainer) {
                this.layerCallbackRegistery = new Rocket.util.CallbackRegistry();
                this.jqContainer = jqContainer;
                this._layers = new Array();
                var layer = new Cmd.Layer(this.jqContainer.find(".rocket-main-layer"), this._layers.length, this, Jhtml.getOrCreateMonitor());
                this._layers.push(layer);
            }
            Object.defineProperty(Container.prototype, "layers", {
                get: function () {
                    return this._layers.slice();
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(Container.prototype, "mainLayer", {
                get: function () {
                    if (this._layers.length > 0) {
                        return this._layers[0];
                    }
                    throw new Error("Container empty.");
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(Container.prototype, "currentLayer", {
                get: function () {
                    if (this._layers.length == 0) {
                        throw new Error("Container empty.");
                    }
                    var layer = null;
                    for (var i in this._layers) {
                        if (this._layers[i].visible) {
                            layer = this._layers[i];
                        }
                    }
                    if (layer !== null)
                        return layer;
                    return this._layers[this._layers.length - 1];
                },
                enumerable: true,
                configurable: true
            });
            Container.prototype.unregisterLayer = function (layer) {
                var i = this._layers.indexOf(layer);
                if (i < 0)
                    return;
                this._layers.splice(i, 1);
                this.layerTrigger(Container.LayerEventType.REMOVED, layer);
            };
            Container.prototype.createLayer = function (dependentPage) {
                if (dependentPage === void 0) { dependentPage = null; }
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
                dependentPage.on(Cmd.Page.EventType.CLOSE, function () {
                    layer.close();
                });
                dependentPage.on(Cmd.Page.EventType.HIDE, function () {
                    layer.hide();
                });
                dependentPage.on(Cmd.Page.EventType.SHOW, function () {
                    layer.show();
                });
                this.layerTrigger(Container.LayerEventType.ADDED, layer);
                return layer;
            };
            Container.prototype.getAllPages = function () {
                var contexts = new Array();
                for (var i in this._layers) {
                    var layerPages = this._layers[i].contexts;
                    for (var j in layerPages) {
                        contexts.push(layerPages[j]);
                    }
                }
                return contexts;
            };
            Container.prototype.layerTrigger = function (eventType, layer) {
                var container = this;
                this.layerCallbackRegistery.filter(eventType.toString())
                    .forEach(function (callback) {
                    callback(layer);
                });
            };
            Container.prototype.layerOn = function (eventType, callback) {
                this.layerCallbackRegistery.register(eventType.toString(), callback);
            };
            Container.prototype.layerOff = function (eventType, callback) {
                this.layerCallbackRegistery.unregister(eventType.toString(), callback);
            };
            return Container;
        }());
        Cmd.Container = Container;
        (function (Container) {
            var LayerEventType;
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
        var Layer = (function () {
            function Layer(jqLayer, _level, _container, _monitor) {
                var _this = this;
                this.jqLayer = jqLayer;
                this._level = _level;
                this._container = _container;
                this._monitor = _monitor;
                this._contexts = new Array();
                this.callbackRegistery = new Rocket.util.CallbackRegistry();
                this._visible = true;
                this.onNewPageCallbacks = new Array();
                this.onNewHistoryEntryCallbacks = new Array();
                var jqPage = jqLayer.children(".rocket-context:first");
                if (jqPage.length > 0) {
                    var page = new Cmd.Page(jqPage, Jhtml.Url.create(window.location.href), this);
                    this.addPage(page);
                }
                this.monitor.history.onChanged(function () { return _this.historyChanged(); });
                this.monitor.registerCompHandler("rocket-page", this);
                this.historyChanged();
            }
            Object.defineProperty(Layer.prototype, "monitor", {
                get: function () {
                    return this._monitor;
                },
                enumerable: true,
                configurable: true
            });
            Layer.prototype.containsUrl = function (url) {
                for (var i in this._contexts) {
                    if (this._contexts[i].containsUrl(url))
                        return true;
                }
                return false;
            };
            Layer.prototype.getPageByUrl = function (urlExpr) {
                var url = Jhtml.Url.create(urlExpr);
                for (var i in this._contexts) {
                    if (this._contexts[i].containsUrl(url)) {
                        return this._contexts[i];
                    }
                }
                return null;
            };
            Layer.prototype.historyChanged = function () {
                var currentEntry;
                if (!currentEntry)
                    return;
                var page = this.getPageByUrl(currentEntry.page.url);
                if (!page) {
                    this.addPage(page = this.createPage(currentEntry.page.url));
                }
                this.switchToPage(page);
            };
            Layer.prototype.createPage = function (urlExpr) {
                var url = Jhtml.Url.create(urlExpr);
                if (this.containsUrl(url)) {
                    throw new Error("Page with url already available: " + url);
                }
                var jqContent = $("<div />");
                this.jqLayer.append(jqContent);
                var context = new Cmd.Page(jqContent, url, this);
                this.addPage(context);
                return context;
            };
            Object.defineProperty(Layer.prototype, "currentPage", {
                get: function () {
                    if (this.empty || !this._monitor.history.currentEntry) {
                        return null;
                    }
                    var url = this._monitor.history.currentPage.url;
                    for (var i in this._contexts) {
                        if (this._contexts[i].containsUrl(url)) {
                            return this._contexts[i];
                        }
                    }
                    return null;
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(Layer.prototype, "container", {
                get: function () {
                    return this._container;
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(Layer.prototype, "visible", {
                get: function () {
                    return this._visible;
                },
                enumerable: true,
                configurable: true
            });
            Layer.prototype.trigger = function (eventType) {
                var layer = this;
                this.callbackRegistery.filter(eventType.toString())
                    .forEach(function (callback) {
                    callback(layer);
                });
            };
            Layer.prototype.on = function (eventType, callback) {
                this.callbackRegistery.register(eventType.toString(), callback);
            };
            Layer.prototype.off = function (eventType, callback) {
                this.callbackRegistery.unregister(eventType.toString(), callback);
            };
            Layer.prototype.show = function () {
                this.trigger(Layer.EventType.SHOW);
                this._visible = true;
                this.jqLayer.show();
            };
            Layer.prototype.hide = function () {
                this.trigger(Layer.EventType.SHOW);
                this._visible = false;
                this.jqLayer.hide();
            };
            Object.defineProperty(Layer.prototype, "level", {
                get: function () {
                    return this._level;
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(Layer.prototype, "empty", {
                get: function () {
                    return this._contexts.length == 0;
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(Layer.prototype, "contexts", {
                get: function () {
                    return this._contexts.slice();
                },
                enumerable: true,
                configurable: true
            });
            Layer.prototype.addPage = function (page) {
                this._contexts.push(page);
                var that = this;
                page.on(Cmd.Page.EventType.CLOSE, function (context) {
                    for (var i in that._contexts) {
                        if (that._contexts[i] !== context)
                            continue;
                        that._contexts.splice(parseInt(i), 1);
                        break;
                    }
                });
                for (var i in this.onNewPageCallbacks) {
                    this.onNewPageCallbacks[i](page);
                }
            };
            Layer.prototype.onNewPage = function (onNewPageCallback) {
                this.onNewPageCallbacks.push(onNewPageCallback);
            };
            Layer.prototype.clear = function () {
                for (var i in this._contexts) {
                    this._contexts[i].close();
                }
            };
            Layer.prototype.close = function () {
                this.trigger(Layer.EventType.CLOSE);
                var context = null;
                while (context = this._contexts.pop()) {
                    context.close();
                }
                this._contexts = new Array();
                this.jqLayer.remove();
            };
            Layer.prototype.switchToPage = function (context) {
                for (var i in this._contexts) {
                    if (this._contexts[i] === context) {
                        context.show();
                    }
                    else {
                        this._contexts[i].hide();
                    }
                }
            };
            Layer.prototype.attachComp = function (comp) {
                var page = this.getPageByUrl(comp.model.response.url);
                if (page) {
                    page.applyComp(comp);
                    return true;
                }
                return false;
            };
            Layer.prototype.detachComp = function (comp) {
                return false;
            };
            Layer.create = function (jqLayer, _level, _container, history) {
                if (Layer.test(jqLayer)) {
                    throw new Error("Layer already bound to this element.");
                }
                jqLayer.addClass("rocket-layer");
                jqLayer.data("rocketLayer", this);
            };
            Layer.test = function (jqLayer) {
                var layer = jqLayer.data("rocketLayer");
                if (layer instanceof Layer) {
                    return layer;
                }
                return null;
            };
            Layer.of = function (jqElem) {
                if (!jqElem.hasClass(".rocket-layer")) {
                    jqElem = jqElem.closest(".rocket-layer");
                }
                var layer = Layer.test(jqElem);
                if (layer === undefined) {
                    return null;
                }
                return layer;
            };
            return Layer;
        }());
        Cmd.Layer = Layer;
        (function (Layer) {
            var EventType;
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
        var CallbackRegistry = (function () {
            function CallbackRegistry() {
                this.callbackMap = {};
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
                        this.callbackMap[nature].splice(parseInt(i), 1);
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
        var ArgUtils = (function () {
            function ArgUtils() {
            }
            ArgUtils.valIsset = function (arg) {
                if (arg !== null && arg !== undefined)
                    return;
                throw new InvalidArgumentError("Invalid arg: " + arg);
            };
            return ArgUtils;
        }());
        util.ArgUtils = ArgUtils;
        var ElementUtils = (function () {
            function ElementUtils() {
            }
            ElementUtils.isControl = function (elem) {
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
            };
            return ElementUtils;
        }());
        util.ElementUtils = ElementUtils;
        var InvalidArgumentError = (function (_super) {
            __extends(InvalidArgumentError, _super);
            function InvalidArgumentError() {
                return _super !== null && _super.apply(this, arguments) || this;
            }
            return InvalidArgumentError;
        }(Error));
        util.InvalidArgumentError = InvalidArgumentError;
        var IllegalStateError = (function (_super) {
            __extends(IllegalStateError, _super);
            function IllegalStateError() {
                return _super !== null && _super.apply(this, arguments) || this;
            }
            IllegalStateError.assertTrue = function (arg, errMsg) {
                if (errMsg === void 0) { errMsg = null; }
                if (arg === true)
                    return;
                if (errMsg === null) {
                    errMsg = "Illegal state";
                }
                throw new Error(errMsg);
            };
            return IllegalStateError;
        }(Error));
        util.IllegalStateError = IllegalStateError;
    })(util = Rocket.util || (Rocket.util = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        var StructureElement = (function () {
            function StructureElement(jqElem) {
                this.onShowCallbacks = [];
                this.onHideCallbacks = [];
                this.toolbar = null;
                this.highlightedParent = null;
                this.jqElem = jqElem;
                jqElem.addClass("rocket-structure-element");
                jqElem.data("rocketStructureElement", this);
                this.valClasses();
            }
            StructureElement.prototype.valClasses = function () {
                if (this.isField() || this.isGroup()) {
                    this.jqElem.removeClass("rocket-structure-element");
                }
                else {
                    this.jqElem.addClass("rocket-structure-element");
                }
            };
            Object.defineProperty(StructureElement.prototype, "jQuery", {
                get: function () {
                    return this.jqElem;
                },
                enumerable: true,
                configurable: true
            });
            StructureElement.prototype.setGroup = function (group) {
                if (!group) {
                    this.jqElem.removeClass("rocket-group");
                }
                else {
                    this.jqElem.addClass("rocket-group");
                }
                this.valClasses();
            };
            StructureElement.prototype.isGroup = function () {
                return this.jqElem.hasClass("rocket-group");
            };
            StructureElement.prototype.setField = function (field) {
                if (!field) {
                    this.jqElem.removeClass("rocket-field");
                }
                else {
                    this.jqElem.addClass("rocket-field");
                }
                this.valClasses();
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
            };
            return StructureElement;
        }());
        Display.StructureElement = StructureElement;
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
            Object.defineProperty(Toolbar.prototype, "jQuery", {
                get: function () {
                    return this.jqToolbar;
                },
                enumerable: true,
                configurable: true
            });
            Toolbar.prototype.getJqControls = function () {
                return this.jqControls;
            };
            Toolbar.prototype.getCommandList = function () {
                return this.commandList;
            };
            return Toolbar;
        }());
        Display.Toolbar = Toolbar;
        var CommandList = (function () {
            function CommandList(jqCommandList, simple) {
                if (simple === void 0) { simple = false; }
                this.jqCommandList = jqCommandList;
                if (simple) {
                    jqCommandList.addClass("rocket-simple-commands");
                }
            }
            Object.defineProperty(CommandList.prototype, "jQuery", {
                get: function () {
                    return this.jqCommandList;
                },
                enumerable: true,
                configurable: true
            });
            CommandList.prototype.createJqCommandButton = function (buttonConfig, prepend) {
                if (prepend === void 0) { prepend = false; }
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
            };
            CommandList.create = function (simple) {
                if (simple === void 0) { simple = false; }
                return new CommandList($("<div />"), simple);
            };
            return CommandList;
        }());
        Display.CommandList = CommandList;
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Cmd;
    (function (Cmd) {
        var display = Rocket.Display;
        var util = Rocket.util;
        var Page = (function () {
            function Page(jqPage, url, layer) {
                this.urls = [];
                this.callbackRegistery = new util.CallbackRegistry();
                this._blocked = false;
                this.locks = new Array();
                this.jqPage = jqPage;
                this.urls.push(this._activeUrl = url);
                this._layer = layer;
                jqPage.addClass("rocket-context");
                jqPage.data("rocketPage", this);
                this.reset();
                this.hide();
            }
            Object.defineProperty(Page.prototype, "layer", {
                get: function () {
                    return this._layer;
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(Page.prototype, "jQuery", {
                get: function () {
                    return this.jqPage;
                },
                enumerable: true,
                configurable: true
            });
            Page.prototype.containsUrl = function (url) {
                for (var i in this.urls) {
                    if (this.urls[i].equals(url))
                        return true;
                }
                return false;
            };
            Object.defineProperty(Page.prototype, "activeUrl", {
                get: function () {
                    return this._activeUrl;
                },
                enumerable: true,
                configurable: true
            });
            Page.prototype.fireEvent = function (eventType) {
                var that = this;
                this.callbackRegistery.filter(eventType.toString()).forEach(function (callback) {
                    callback(that);
                });
            };
            Page.prototype.ensureNotClosed = function () {
                if (this.jqPage !== null)
                    return;
                throw new Error("Page already closed.");
            };
            Page.prototype.close = function () {
                this.trigger(Page.EventType.CLOSE);
                this.jqPage.remove();
                this.jqPage = null;
            };
            Page.prototype.show = function () {
                this.trigger(Page.EventType.SHOW);
                this.jqPage.show();
            };
            Page.prototype.hide = function () {
                this.trigger(Page.EventType.HIDE);
                this.jqPage.hide();
            };
            Page.prototype.reset = function () {
                this.additionalTabManager = new AdditionalTabManager(this);
                this._menu = new Menu(this);
            };
            Page.prototype.clear = function (showLoader) {
                if (showLoader === void 0) { showLoader = false; }
                this.jqPage.empty();
                if (showLoader) {
                    this.jqPage.addClass("rocket-loading");
                }
                this.trigger(Page.EventType.CONTENT_CHANGED);
            };
            Page.prototype.applyHtml = function (html) {
                this.endLoading();
                this.jqPage.html(html);
                this.reset();
                this.trigger(Page.EventType.CONTENT_CHANGED);
            };
            Page.prototype.applyComp = function (comp) {
                this.endLoading();
                comp.attachTo(this.jqPage.get(0));
                this.reset();
                this.trigger(Page.EventType.CONTENT_CHANGED);
            };
            Page.prototype.isLoading = function () {
                return this.jqPage.hasClass("rocket-loading");
            };
            Page.prototype.endLoading = function () {
                this.jqPage.removeClass("rocket-loading");
            };
            Page.prototype.applyContent = function (jqContent) {
                this.endLoading();
                this.jqPage.append(jqContent);
                this.reset();
                this.trigger(Page.EventType.CONTENT_CHANGED);
            };
            Page.prototype.trigger = function (eventType) {
                var context = this;
                this.callbackRegistery.filter(eventType.toString())
                    .forEach(function (callback) {
                    callback(context);
                });
            };
            Page.prototype.on = function (eventType, callback) {
                this.callbackRegistery.register(eventType.toString(), callback);
            };
            Page.prototype.off = function (eventType, callback) {
                this.callbackRegistery.unregister(eventType.toString(), callback);
            };
            Page.prototype.createAdditionalTab = function (title, prepend) {
                if (prepend === void 0) { prepend = false; }
                return this.additionalTabManager.createTab(title, prepend);
            };
            Object.defineProperty(Page.prototype, "menu", {
                get: function () {
                    return this._menu;
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(Page.prototype, "locked", {
                get: function () {
                    return this.locks.length > 0;
                },
                enumerable: true,
                configurable: true
            });
            Page.prototype.releaseLock = function (lock) {
                var i = this.locks.indexOf(lock);
                if (i == -1)
                    return;
                this.locks.splice(i, 1);
                this.trigger(Page.EventType.BLOCKED_CHANGED);
            };
            Page.prototype.createLock = function () {
                var that = this;
                var lock = new Lock(function (lock) {
                    that.releaseLock(lock);
                });
                this.locks.push(lock);
                this.trigger(Page.EventType.BLOCKED_CHANGED);
                return lock;
            };
            Page.of = function (jqElem) {
                if (!jqElem.hasClass(".rocket-context")) {
                    jqElem = jqElem.parents(".rocket-context");
                }
                var context = jqElem.data("rocketPage");
                if (context instanceof Page)
                    return context;
                return null;
            };
            return Page;
        }());
        Cmd.Page = Page;
        var Lock = (function () {
            function Lock(releaseCallback) {
                this.releaseCallback = releaseCallback;
            }
            Lock.prototype.release = function () {
                this.releaseCallback(this);
            };
            return Lock;
        }());
        Cmd.Lock = Lock;
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
                var jqPage = this.context.jQuery;
                jqPage.addClass("rocket-contains-additional");
                this.jqAdditional = $("<div />", {
                    "class": "rocket-additional"
                });
                this.jqAdditional.append($("<ul />", { "class": "rocket-additional-nav" }));
                this.jqAdditional.append($("<div />", { "class": "rocket-additional-container" }));
                jqPage.append(this.jqAdditional);
            };
            AdditionalTabManager.prototype.setdownAdditional = function () {
                if (this.jqAdditional === null)
                    return;
                this.context.jQuery.removeClass("rocket-contains-additional");
                this.jqAdditional.remove();
                this.jqAdditional = null;
            };
            return AdditionalTabManager;
        }());
        var AdditionalTab = (function () {
            function AdditionalTab(jqNavItem, jqContent) {
                this.active = false;
                this.onShowCallbacks = [];
                this.onHideCallbacks = [];
                this.onDisposeCallbacks = [];
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
        Cmd.AdditionalTab = AdditionalTab;
        var Menu = (function () {
            function Menu(context) {
                this._toolbar = null;
                this._commandList = null;
                this._partialCommandList = null;
                this.context = context;
            }
            Object.defineProperty(Menu.prototype, "toolbar", {
                get: function () {
                    if (this._toolbar) {
                        return this._toolbar;
                    }
                    var jqToolbar = this.context.jQuery.find(".rocket-context-toolbar:first");
                    if (jqToolbar.length == 0) {
                        jqToolbar = $("<div />", { "class": "rocket-context-toolbar" }).prependTo(this.context.jQuery);
                    }
                    return this._toolbar = new display.Toolbar(jqToolbar);
                },
                enumerable: true,
                configurable: true
            });
            Menu.prototype.getJqPageCommands = function () {
                var jqCommandList = this.context.jQuery.find(".rocket-context-commands:first");
                if (jqCommandList.length == 0) {
                    jqCommandList = $("<div />", {
                        "class": "rocket-context-commands"
                    });
                    this.context.jQuery.append(jqCommandList);
                }
                return jqCommandList;
            };
            Object.defineProperty(Menu.prototype, "partialCommandList", {
                get: function () {
                    if (this._partialCommandList !== null) {
                        return this._partialCommandList;
                    }
                    var jqPageCommands = this.getJqPageCommands();
                    var jqPartialCommands = jqPageCommands.children(".rocket-partial-commands:first");
                    if (jqPartialCommands.length == 0) {
                        jqPartialCommands = $("<div />", { "class": "rocket-partial-commands" }).prependTo(jqPageCommands);
                    }
                    return this._partialCommandList = new display.CommandList(jqPartialCommands);
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(Menu.prototype, "commandList", {
                get: function () {
                    if (this._commandList !== null) {
                        return this._commandList;
                    }
                    var jqPageCommands = this.getJqPageCommands();
                    var jqCommands = jqPageCommands.children(":not(.rocket-partial-commands):first");
                    if (jqCommands.length == 0) {
                        jqCommands = $("<div />").appendTo(jqPageCommands);
                    }
                    return this._commandList = new display.CommandList(jqCommands);
                },
                enumerable: true,
                configurable: true
            });
            return Menu;
        }());
        Cmd.Menu = Menu;
        (function (Page) {
            var EventType;
            (function (EventType) {
                EventType[EventType["SHOW"] = 0] = "SHOW";
                EventType[EventType["HIDE"] = 1] = "HIDE";
                EventType[EventType["CLOSE"] = 2] = "CLOSE";
                EventType[EventType["CONTENT_CHANGED"] = 3] = "CONTENT_CHANGED";
                EventType[EventType["ACTIVE_URL_CHANGED"] = 4] = "ACTIVE_URL_CHANGED";
                EventType[EventType["BLOCKED_CHANGED"] = 5] = "BLOCKED_CHANGED";
            })(EventType = Page.EventType || (Page.EventType = {}));
        })(Page = Cmd.Page || (Cmd.Page = {}));
    })(Cmd = Rocket.Cmd || (Rocket.Cmd = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        var Entry = (function () {
            function Entry(jqElem) {
                this.jqElem = jqElem;
                this._selector = null;
                this._state = Entry.State.PERSISTENT;
                this.callbackRegistery = new Rocket.util.CallbackRegistry();
                var that = this;
                jqElem.on("remove", function () {
                    that.trigger(Entry.EventType.DISPOSED);
                });
                var jqSelector = jqElem.find(".rocket-entry-selector:first");
                if (jqSelector.length > 0) {
                    this.initSelector(jqSelector);
                }
            }
            Entry.prototype.initSelector = function (jqSelector) {
                this._selector = new Display.EntrySelector(jqSelector, this);
                var that = this;
                this.jqElem.click(function (e) {
                    if (getSelection().toString() || Rocket.util.ElementUtils.isControl(e.target)) {
                        return;
                    }
                    that._selector.selected = !that._selector.selected;
                });
            };
            Entry.prototype.trigger = function (eventType) {
                var entry = this;
                this.callbackRegistery.filter(eventType.toString())
                    .forEach(function (callback) {
                    callback(entry);
                });
            };
            Entry.prototype.on = function (eventType, callback) {
                this.callbackRegistery.register(eventType.toString(), callback);
            };
            Entry.prototype.off = function (eventType, callback) {
                this.callbackRegistery.unregister(eventType.toString(), callback);
            };
            Object.defineProperty(Entry.prototype, "jqQuery", {
                get: function () {
                    return this.jqElem;
                },
                enumerable: true,
                configurable: true
            });
            Entry.prototype.show = function () {
                this.jqElem.show();
            };
            Entry.prototype.hide = function () {
                this.jqElem.hide();
            };
            Entry.prototype.dispose = function () {
                this.jqElem.remove();
            };
            Object.defineProperty(Entry.prototype, "state", {
                get: function () {
                    return this._state;
                },
                set: function (state) {
                    if (this._state == state)
                        return;
                    this._state = state;
                    if (state == Entry.State.REMOVED) {
                        this.trigger(Entry.EventType.REMOVED);
                    }
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(Entry.prototype, "generalId", {
                get: function () {
                    return this.jqElem.data("rocket-general-id").toString();
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(Entry.prototype, "id", {
                get: function () {
                    if (this.draftId !== null) {
                        return this.draftId.toString();
                    }
                    return this.idRep;
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(Entry.prototype, "idRep", {
                get: function () {
                    return this.jqElem.data("rocket-id-rep").toString();
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(Entry.prototype, "draftId", {
                get: function () {
                    var draftId = parseInt(this.jqElem.data("rocket-draft-id"));
                    if (!isNaN(draftId)) {
                        return draftId;
                    }
                    return null;
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(Entry.prototype, "identityString", {
                get: function () {
                    return this.jqElem.data("rocket-identity-string");
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(Entry.prototype, "selector", {
                get: function () {
                    return this._selector;
                },
                enumerable: true,
                configurable: true
            });
            Entry.from = function (jqElem) {
                var entry = jqElem.data("rocketEntry");
                if (entry instanceof Entry) {
                    return entry;
                }
                entry = new Entry(jqElem);
                jqElem.data("rocketEntry", entry);
                return entry;
            };
            Entry.findFrom = function (jqElem) {
                var jqElem = jqElem.closest(".rocket-entry");
                if (jqElem.length == 0)
                    return null;
                return Entry.from(jqElem);
            };
            Entry.findAll = function (jqElem, includeSelf) {
                if (includeSelf === void 0) { includeSelf = false; }
                var entries = new Array();
                var jqEntries = jqElem.find(".rocket-entry");
                jqEntries = jqEntries.add(jqElem.filter(".rocket-entry"));
                jqEntries.each(function () {
                    entries.push(Entry.from($(this)));
                });
                return entries;
            };
            return Entry;
        }());
        Display.Entry = Entry;
        (function (Entry) {
            var State;
            (function (State) {
                State[State["PERSISTENT"] = 0] = "PERSISTENT";
                State[State["REMOVED"] = 1] = "REMOVED";
            })(State = Entry.State || (Entry.State = {}));
            var EventType;
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
        var EntryForm = (function () {
            function EntryForm(jqElem) {
                this.jqEiTypeSelect = null;
                this.inited = false;
                this.jqElem = jqElem;
            }
            EntryForm.prototype.init = function () {
                var _this = this;
                if (this.inited) {
                    throw new Error("EntryForm already initialized:");
                }
                this.inited = true;
                if (!this.jqElem.hasClass("rocket-multi-ei-type"))
                    return;
                this.jqEiTypeSelect = this.jqElem.children(".rocket-ei-type-selector").find("select");
                this.updateDisplay();
                this.jqEiTypeSelect.change(function () {
                    _this.updateDisplay();
                });
            };
            EntryForm.prototype.updateDisplay = function () {
                if (!this.jqEiTypeSelect)
                    return;
                this.jqElem.children(".rocket-ei-type-entry-form").hide();
                this.jqElem.children(".rocket-ei-type-" + this.jqEiTypeSelect.val()).show();
            };
            Object.defineProperty(EntryForm.prototype, "jQuery", {
                get: function () {
                    return this.jqElem;
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(EntryForm.prototype, "multiEiType", {
                get: function () {
                    return this.jqEiTypeSelect ? true : false;
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(EntryForm.prototype, "curEiTypeId", {
                get: function () {
                    if (!this.multiEiType) {
                        return this.jqElem.data("rocket-ei-type-id");
                    }
                    return this.jqEiTypeSelect.val();
                },
                set: function (typeId) {
                    this.jqEiTypeSelect.val(typeId);
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(EntryForm.prototype, "curGenericLabel", {
                get: function () {
                    if (!this.multiEiType) {
                        return this.jqElem.data("rocket-generic-label");
                    }
                    return this.jqEiTypeSelect.children(":selected").text();
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(EntryForm.prototype, "curGenericIconType", {
                get: function () {
                    if (!this.multiEiType) {
                        return this.jqElem.data("rocket-generic-icon-type");
                    }
                    return this.jqEiTypeSelect.data("rocket-generic-icon-types")[this.curEiTypeId];
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(EntryForm.prototype, "typeMap", {
                get: function () {
                    var typeMap = {};
                    if (!this.multiEiType) {
                        typeMap[this.curEiTypeId] = this.curGenericLabel;
                        return typeMap;
                    }
                    this.jqEiTypeSelect.children().each(function () {
                        var jqElem = $(this);
                        typeMap[jqElem.attr("value")] = jqElem.text();
                    });
                    return typeMap;
                },
                enumerable: true,
                configurable: true
            });
            EntryForm.from = function (jqElem, create) {
                if (create === void 0) { create = true; }
                var entryForm = jqElem.data("rocketEntryForm");
                if (entryForm instanceof EntryForm)
                    return entryForm;
                if (!create)
                    return null;
                entryForm = new EntryForm(jqElem);
                entryForm.init();
                jqElem.data("rocketEntryForm", entryForm);
                return entryForm;
            };
            EntryForm.firstOf = function (jqElem) {
                if (jqElem.hasClass("rocket-entry-form")) {
                    return EntryForm.from(jqElem);
                }
                var jqEntryForm = jqElem.find(".rocket-entry-form:first");
                if (jqEntryForm.length == 0)
                    return null;
                return EntryForm.from(jqEntryForm);
            };
            EntryForm.find = function (jqElem, mulitTypeOnly) {
                if (mulitTypeOnly === void 0) { mulitTypeOnly = false; }
                var entryForms = [];
                jqElem.find(".rocket-entry-form" + (mulitTypeOnly ? ".rocket-multi-ei-type" : "")).each(function () {
                    entryForms.push(EntryForm.from($(this)));
                });
                return entryForms;
            };
            return EntryForm;
        }());
        Display.EntryForm = EntryForm;
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        var EntrySelector = (function () {
            function EntrySelector(jqElem, _entry) {
                this.jqElem = jqElem;
                this._entry = _entry;
                this.changedCallbacks = new Array();
                this._selected = false;
            }
            Object.defineProperty(EntrySelector.prototype, "jQuery", {
                get: function () {
                    return this.jqElem;
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(EntrySelector.prototype, "entry", {
                get: function () {
                    return this._entry;
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(EntrySelector.prototype, "selected", {
                get: function () {
                    return this._selected;
                },
                set: function (selected) {
                    if (this._selected == selected)
                        return;
                    this._selected = selected;
                    this.triggerChanged();
                },
                enumerable: true,
                configurable: true
            });
            EntrySelector.prototype.whenChanged = function (callback) {
                this.changedCallbacks.push(callback);
            };
            EntrySelector.prototype.triggerChanged = function () {
                this.changedCallbacks.forEach(function (callback) {
                    callback();
                });
            };
            return EntrySelector;
        }());
        Display.EntrySelector = EntrySelector;
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
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
                var contexts = this.container.getAllPages();
                for (var i in contexts) {
                    this.scanPage(contexts[i]);
                }
            };
            Initializer.prototype.scanPage = function (context) {
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
                    var structureElement = Display.StructureElement.findFrom($(this));
                    if (errorIndex === null) {
                        errorIndex = new ErrorIndex(context.createAdditionalTab(that.errorTabTitle), that.displayErrorLabel);
                        that.errorIndexes.push(errorIndex);
                    }
                    errorIndex.addError(structureElement, $(this).text());
                });
            };
            Initializer.scanGroupNav = function (jqContainer) {
                var curGroupNav = null;
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
            };
            return Initializer;
        }());
        Display.Initializer = Initializer;
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
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        var Severity;
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
        var Form = (function () {
            function Form(jqForm) {
                this._observing = false;
                this._config = new Form.Config();
                this.callbackRegistery = new Rocket.util.CallbackRegistry();
                this.curXhr = null;
                this.controlLockAutoReleaseable = true;
                this.jqForm = jqForm;
            }
            Object.defineProperty(Form.prototype, "jQuery", {
                get: function () {
                    return this.jqForm;
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(Form.prototype, "observing", {
                get: function () {
                    return this._observing;
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(Form.prototype, "config", {
                get: function () {
                    return this._config;
                },
                enumerable: true,
                configurable: true
            });
            Form.prototype.reset = function () {
                this.jqForm.get(0).reset();
            };
            Form.prototype.trigger = function (eventType) {
                var that = this;
                this.callbackRegistery.filter(eventType.toString())
                    .forEach(function (callback) {
                    callback(that);
                });
            };
            Form.prototype.on = function (eventType, callback) {
                this.callbackRegistery.register(eventType.toString(), callback);
            };
            Form.prototype.off = function (eventType, callback) {
                this.callbackRegistery.unregister(eventType.toString(), callback);
            };
            Form.prototype.observe = function () {
                if (this._observing)
                    return;
                this._observing = true;
                var that = this;
                this.jqForm.submit(function () {
                    if (!that._config.autoSubmitAllowed)
                        return false;
                    that.submit();
                    return false;
                });
                var that = this;
                this.jqForm.find("input[type=submit], button[type=submit]").each(function () {
                    $(this).click(function () {
                        if (!that._config.autoSubmitAllowed)
                            return false;
                        that.submit({ button: this });
                        return false;
                    });
                });
            };
            Form.prototype.buildFormData = function (submitConfig) {
                var formData = new FormData(this.jqForm.get(0));
                if (submitConfig && submitConfig.button) {
                    formData.append(submitConfig.button.name, submitConfig.button.value);
                }
                return formData;
            };
            Form.prototype.block = function () {
                var context;
                if (!this.lock && this.config.blockPage && (context = Rocket.Cmd.Page.findFrom(this.jqForm))) {
                    this.lock = context.createLock();
                }
                if (!this.controlLock && this.config.disableControls) {
                    this.disableControls();
                }
            };
            Form.prototype.unblock = function () {
                if (this.lock) {
                    this.lock.release();
                    this.lock = null;
                }
                if (this.controlLock && this.controlLockAutoReleaseable) {
                    this.controlLock.release();
                }
            };
            Form.prototype.disableControls = function (autoReleaseable) {
                if (autoReleaseable === void 0) { autoReleaseable = true; }
                this.controlLockAutoReleaseable = autoReleaseable;
                if (this.controlLock)
                    return;
                this.controlLock = new ControlLock(this.jqForm);
            };
            Form.prototype.enableControls = function () {
                if (this.controlLock) {
                    this.controlLock.release();
                    this.controlLock = null;
                    this.controlLockAutoReleaseable = true;
                }
            };
            Form.prototype.abortSubmit = function () {
                if (this.curXhr) {
                    var curXhr = this.curXhr;
                    this.curXhr = null;
                    curXhr.abort();
                    this.unblock();
                }
            };
            Form.prototype.submit = function (submitConfig) {
                this.abortSubmit();
                this.trigger(Form.EventType.SUBMIT);
                var formData = this.buildFormData(submitConfig);
                var url = this._config.actionUrl || this.jqForm.attr("action");
                var that = this;
                var xhr = this.curXhr = $.ajax({
                    "url": url,
                    "type": "POST",
                    "data": formData,
                    "cache": false,
                    "processData": false,
                    "contentType": false,
                    "dataType": "json",
                    "success": function (data, textStatus, jqXHR) {
                        if (that.curXhr !== xhr)
                            return;
                        if (that._config.successResponseHandler) {
                            that._config.successResponseHandler(data);
                        }
                        else {
                            Rocket.analyzeResponse(Rocket.layerOf(that.jqForm.get(0)), data, url);
                        }
                        if (submitConfig && submitConfig.success) {
                            submitConfig.success();
                        }
                        that.unblock();
                        that.trigger(Form.EventType.SUBMITTED);
                    },
                    "error": function (jqXHR, textStatus, errorThrown) {
                        if (that.curXhr !== xhr)
                            return;
                        Rocket.handleErrorResponse(url, jqXHR);
                        if (submitConfig && submitConfig.error) {
                            submitConfig.error();
                        }
                        that.unblock();
                        that.trigger(Form.EventType.SUBMITTED);
                    }
                });
                this.block();
            };
            Form.from = function (jqForm) {
                var form = jqForm.data("rocketImplForm");
                if (form instanceof Form)
                    return form;
                if (jqForm.length == 0) {
                    throw new Error("asd");
                }
                form = new Form(jqForm);
                jqForm.data("rocketImplForm", form);
                form.observe();
                return form;
            };
            return Form;
        }());
        Impl.Form = Form;
        var ControlLock = (function () {
            function ControlLock(jqContainer) {
                this.jqControls = jqContainer.find("input:not([disabled]), textarea:not([disabled]), button:not([disabled]), select:not([disabled])");
                this.jqControls.prop("disabled", true);
            }
            ControlLock.prototype.release = function () {
                if (!this.jqControls)
                    return;
                this.jqControls.prop("disabled", false);
                this.jqControls = null;
            };
            return ControlLock;
        }());
        (function (Form) {
            var Config = (function () {
                function Config() {
                    this.blockPage = true;
                    this.disableControls = true;
                    this.autoSubmitAllowed = true;
                    this.actionUrl = null;
                }
                return Config;
            }());
            Form.Config = Config;
            var EventType;
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
        var Translator = (function () {
            function Translator(container) {
                this.container = container;
            }
            Translator.prototype.scan = function () {
                var _loop_1 = function (context) {
                    var elems = context.jQuery.find(".rocket-impl-translation-manager").toArray();
                    var elem = void 0;
                    while (elem = elems.pop()) {
                        this_1.initTm($(elem), context);
                    }
                    var jqViewControl = context.menu.toolbar.getJqControls().find(".rocket-impl-translation-view-control");
                    var jqTranslatables = context.jQuery.find(".rocket-impl-translatable");
                    if (jqTranslatables.length == 0) {
                        jqViewControl.hide();
                        return "continue";
                    }
                    jqViewControl.show();
                    if (jqViewControl.length == 0) {
                        jqViewControl = $("<div />", { "class": "rocket-impl-translation-view-control" });
                        context.menu.toolbar.getJqControls().show().append(jqViewControl);
                    }
                    var viewMenu = ViewMenu.from(jqViewControl);
                    jqTranslatables.each(function (i, elem) {
                        viewMenu.registerTranslatable(Translatable.from($(elem)));
                    });
                };
                var this_1 = this;
                for (var _i = 0, _a = this.container.getAllPages(); _i < _a.length; _i++) {
                    var context = _a[_i];
                    _loop_1(context);
                }
            };
            Translator.prototype.initTm = function (jqElem, context) {
                var tm = TranslationManager.from(jqElem);
                var se = Rocket.Display.StructureElement.findFrom(jqElem);
                var jqBase = null;
                if (!se) {
                    jqBase = context.jQuery;
                }
                else {
                    jqBase = jqElem;
                }
                jqBase.find(".rocket-impl-translatable").each(function (i, elem) {
                    tm.registerTranslatable(Translatable.from($(elem)));
                });
            };
            return Translator;
        }());
        Impl.Translator = Translator;
        var ViewMenu = (function () {
            function ViewMenu(jqContainer) {
                this.jqContainer = jqContainer;
                this.translatables = [];
                this.items = {};
                this.changing = false;
            }
            ViewMenu.prototype.draw = function (languagesLabel, visibleLabel) {
                var _this = this;
                $("<div />", { "class": "rocket-impl-translation-status" })
                    .append($("<label />", { "text": visibleLabel }).prepend($("<i></i>", { "class": "fa fa-language" })))
                    .append(this.jqStatus = $("<span></span>"))
                    .prependTo(this.jqContainer);
                new Rocket.Display.CommandList(this.jqContainer).createJqCommandButton({
                    iconType: "fa fa-cog",
                    label: languagesLabel
                }).click(function () { return _this.jqMenu.toggle(); });
                this.jqMenu = $("<ul></ul>", { "class": "rocket-impl-translation-status-menu" }).hide();
                this.jqContainer.append(this.jqMenu);
            };
            ViewMenu.prototype.updateStatus = function () {
                var prettyLocaleIds = [];
                for (var localeId in this.items) {
                    if (!this.items[localeId].on)
                        continue;
                    prettyLocaleIds.push(this.items[localeId].prettyLocaleId);
                }
                this.jqStatus.empty();
                this.jqStatus.text(prettyLocaleIds.join(", "));
                var onDisabled = prettyLocaleIds.length == 1;
                for (var localeId in this.items) {
                    this.items[localeId].disabled = onDisabled && this.items[localeId].on;
                }
            };
            Object.defineProperty(ViewMenu.prototype, "visibleLocaleIds", {
                get: function () {
                    var localeIds = [];
                    for (var localeId in this.items) {
                        if (!this.items[localeId].on)
                            continue;
                        localeIds.push(localeId);
                    }
                    return localeIds;
                },
                enumerable: true,
                configurable: true
            });
            ViewMenu.prototype.registerTranslatable = function (translatable) {
                var _this = this;
                if (-1 < this.translatables.indexOf(translatable))
                    return;
                if (!this.jqStatus) {
                    this.draw(translatable.jQuery.data("rocket-impl-languages-label"), translatable.jQuery.data("rocket-impl-visible-label"));
                }
                this.translatables.push(translatable);
                translatable.jQuery.on("remove", function () { return _this.unregisterTranslatable(translatable); });
                var _loop_2 = function (content) {
                    if (!this_2.items[content.localeId]) {
                        var item = this_2.items[content.localeId] = new ViewMenuItem(content.localeId, content.localeName, content.prettyLocaleId);
                        item.draw($("<li />").appendTo(this_2.jqMenu));
                        item.on = Object.keys(this_2.items).length == 1;
                        item.whenChanged(function () { return _this.menuChanged(); });
                        this_2.updateStatus();
                    }
                    content.visible = this_2.items[content.localeId].on;
                    content.whenChanged(function () {
                        if (_this.changing || !content.active)
                            return;
                        _this.items[content.localeId].on = true;
                    });
                };
                var this_2 = this;
                for (var _i = 0, _a = translatable.contents; _i < _a.length; _i++) {
                    var content = _a[_i];
                    _loop_2(content);
                }
            };
            ViewMenu.prototype.unregisterTranslatable = function (translatable) {
                var i = this.translatables.indexOf(translatable);
                if (-1 < i) {
                    this.translatables.splice(i, 1);
                }
            };
            ViewMenu.prototype.menuChanged = function () {
                if (this.changing) {
                    throw new Error("already changing");
                }
                this.changing = true;
                var visiableLocaleIds = [];
                for (var i in this.items) {
                    if (this.items[i].on) {
                        visiableLocaleIds.push(this.items[i].localeId);
                    }
                }
                for (var _i = 0, _a = this.translatables; _i < _a.length; _i++) {
                    var translatable = _a[_i];
                    translatable.visibleLocaleIds = visiableLocaleIds;
                }
                this.updateStatus();
                this.changing = false;
            };
            ViewMenu.from = function (jqElem) {
                var vm = jqElem.data("rocketImplViewMenu");
                if (vm instanceof ViewMenu) {
                    return vm;
                }
                vm = new ViewMenu(jqElem);
                jqElem.data("rocketImplViewMenu", vm);
                return vm;
            };
            return ViewMenu;
        }());
        var ViewMenuItem = (function () {
            function ViewMenuItem(localeId, label, prettyLocaleId) {
                this.localeId = localeId;
                this.label = label;
                this.prettyLocaleId = prettyLocaleId;
                this._on = true;
                this.changedCallbacks = [];
            }
            ViewMenuItem.prototype.draw = function (jqElem) {
                var _this = this;
                this.jqI = $("<i></i>");
                this.jqA = $("<a />", { "href": "", "text": this.label + " ", "class": "btn" })
                    .append(this.jqI)
                    .appendTo(jqElem)
                    .click(function (evt) {
                    if (_this.disabled)
                        return;
                    _this.on = !_this.on;
                    evt.preventDefault();
                    return false;
                });
                this.checkI();
            };
            Object.defineProperty(ViewMenuItem.prototype, "disabled", {
                get: function () {
                    return this.jqA.hasClass("disabled");
                },
                set: function (disabled) {
                    if (disabled) {
                        this.jqA.addClass("disabled");
                    }
                    else {
                        this.jqA.removeClass("disabled");
                    }
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(ViewMenuItem.prototype, "on", {
                get: function () {
                    return this._on;
                },
                set: function (on) {
                    if (this._on == on)
                        return;
                    this._on = on;
                    this.checkI();
                    this.triggerChanged();
                },
                enumerable: true,
                configurable: true
            });
            ViewMenuItem.prototype.triggerChanged = function () {
                for (var _i = 0, _a = this.changedCallbacks; _i < _a.length; _i++) {
                    var callback = _a[_i];
                    callback();
                }
            };
            ViewMenuItem.prototype.whenChanged = function (callback) {
                this.changedCallbacks.push(callback);
            };
            ViewMenuItem.prototype.checkI = function () {
                if (this.on) {
                    this.jqI.attr("class", "fa fa-toggle-on");
                }
                else {
                    this.jqI.attr("class", "fa fa-toggle-off");
                }
            };
            return ViewMenuItem;
        }());
        var TranslationManager = (function () {
            function TranslationManager(jqElem) {
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
            TranslationManager.prototype.val = function () {
                var activeLocaleIds = [];
                for (var _i = 0, _a = this.menuItems; _i < _a.length; _i++) {
                    var menuItem = _a[_i];
                    if (!menuItem.active)
                        continue;
                    activeLocaleIds.push(menuItem.localeId);
                }
                var activeDisabled = activeLocaleIds.length <= this.min;
                for (var _b = 0, _c = this.menuItems; _b < _c.length; _b++) {
                    var menuItem = _c[_b];
                    if (menuItem.mandatory)
                        continue;
                    if (!menuItem.active && activeLocaleIds.length < this.min) {
                        menuItem.active = true;
                        activeLocaleIds.push(menuItem.localeId);
                    }
                    menuItem.disabled = activeDisabled && menuItem.active;
                }
                return activeLocaleIds;
            };
            TranslationManager.prototype.registerTranslatable = function (translatable) {
                var _this = this;
                if (-1 < this.translatables.indexOf(translatable))
                    return;
                this.translatables.push(translatable);
                translatable.activeLocaleIds = this.activeLocaleIds;
                translatable.jQuery.on("remove", function () { return _this.unregisterTranslatable(translatable); });
                for (var _i = 0, _a = translatable.contents; _i < _a.length; _i++) {
                    var tc = _a[_i];
                    tc.whenChanged(function () {
                        _this.activeLocaleIds = translatable.activeLocaleIds;
                    });
                }
            };
            TranslationManager.prototype.unregisterTranslatable = function (translatable) {
                var i = this.translatables.indexOf(translatable);
                if (i > -1) {
                    this.translatables.splice(i, 1);
                }
            };
            Object.defineProperty(TranslationManager.prototype, "activeLocaleIds", {
                get: function () {
                    var localeIds = Array();
                    for (var _i = 0, _a = this.menuItems; _i < _a.length; _i++) {
                        var menuItem = _a[_i];
                        if (menuItem.active) {
                            localeIds.push(menuItem.localeId);
                        }
                    }
                    return localeIds;
                },
                set: function (localeIds) {
                    if (this.changing)
                        return;
                    this.changing = true;
                    var changed = false;
                    for (var _i = 0, _a = this.menuItems; _i < _a.length; _i++) {
                        var menuItem = _a[_i];
                        if (menuItem.mandatory)
                            continue;
                        var active = -1 < localeIds.indexOf(menuItem.localeId);
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
                    for (var _b = 0, _c = this.translatables; _b < _c.length; _b++) {
                        var translatable = _c[_b];
                        translatable.activeLocaleIds = localeIds;
                    }
                    this.changing = false;
                },
                enumerable: true,
                configurable: true
            });
            TranslationManager.prototype.menuChanged = function () {
                if (this.changing)
                    return;
                this.changing = true;
                var localeIds = this.val();
                for (var _i = 0, _a = this.translatables; _i < _a.length; _i++) {
                    var translatable = _a[_i];
                    translatable.activeLocaleIds = localeIds;
                }
                this.changing = false;
            };
            TranslationManager.prototype.initControl = function () {
                var _this = this;
                var jqLabel = this.jqElem.children("label:first");
                var cmdList = Rocket.Display.CommandList.create(true);
                cmdList.createJqCommandButton({
                    iconType: "fa fa-language",
                    label: jqLabel.text(),
                    tooltip: this.jqElem.data("rocket-impl-tooltip")
                }).click(function () { return _this.toggle(); });
                jqLabel.replaceWith(cmdList.jQuery);
            };
            TranslationManager.prototype.initMenu = function () {
                var _this = this;
                this.jqMenu = this.jqElem.find(".rocket-impl-translation-menu");
                this.jqMenu.hide();
                this.jqMenu.children().each(function (i, elem) {
                    var mi = new MenuItem($(elem));
                    _this.menuItems.push(mi);
                    mi.whenChanged(function () {
                        _this.menuChanged();
                    });
                });
            };
            TranslationManager.prototype.toggle = function () {
                this.jqMenu.toggle();
            };
            TranslationManager.from = function (jqElem) {
                var tm = jqElem.data("rocketImplTranslationManager");
                if (tm instanceof TranslationManager) {
                    return tm;
                }
                tm = new TranslationManager(jqElem);
                jqElem.data("rocketImplTranslationManager", tm);
                return tm;
            };
            return TranslationManager;
        }());
        Impl.TranslationManager = TranslationManager;
        var MenuItem = (function () {
            function MenuItem(jqElem) {
                this.jqElem = jqElem;
                this._localeId = this.jqElem.data("rocket-impl-locale-id");
                this._mandatory = this.jqElem.data("rocket-impl-mandatory") ? true : false;
                this.init();
            }
            MenuItem.prototype.init = function () {
                if (this.jqCheck) {
                    throw new Error("already initialized");
                }
                this.jqCheck = this.jqElem.find("input[type=checkbox]");
                if (this.mandatory) {
                    this.jqCheck.prop("checked", true);
                    this.jqCheck.prop("disabled", true);
                }
                this.jqCheck.change(this.updateClasses());
            };
            MenuItem.prototype.updateClasses = function () {
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
            };
            MenuItem.prototype.whenChanged = function (callback) {
                this.jqCheck.change(callback);
            };
            Object.defineProperty(MenuItem.prototype, "disabled", {
                get: function () {
                    return this.jqCheck.is(":disabled");
                },
                set: function (disabled) {
                    this.jqCheck.prop("disabled", disabled);
                    this.updateClasses();
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(MenuItem.prototype, "active", {
                get: function () {
                    return this.jqCheck.is(":checked");
                },
                set: function (active) {
                    this.jqCheck.prop("checked", active);
                    this.updateClasses();
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(MenuItem.prototype, "localeId", {
                get: function () {
                    return this._localeId;
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(MenuItem.prototype, "mandatory", {
                get: function () {
                    return this._mandatory;
                },
                enumerable: true,
                configurable: true
            });
            return MenuItem;
        }());
        var Translatable = (function () {
            function Translatable(jqElem) {
                this.jqElem = jqElem;
                this._contents = {};
            }
            Object.defineProperty(Translatable.prototype, "jQuery", {
                get: function () {
                    return this.jqElem;
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(Translatable.prototype, "localeIds", {
                get: function () {
                    return Object.keys(this._contents);
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(Translatable.prototype, "contents", {
                get: function () {
                    var O = Object;
                    return O.values(this._contents);
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(Translatable.prototype, "visibleLocaleIds", {
                get: function () {
                    var localeIds = new Array();
                    for (var _i = 0, _a = this.contents; _i < _a.length; _i++) {
                        var content = _a[_i];
                        if (!content.visible)
                            continue;
                        localeIds.push(content.localeId);
                    }
                    return localeIds;
                },
                set: function (localeIds) {
                    for (var _i = 0, _a = this.contents; _i < _a.length; _i++) {
                        var content = _a[_i];
                        content.visible = -1 < localeIds.indexOf(content.localeId);
                    }
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(Translatable.prototype, "activeLocaleIds", {
                get: function () {
                    var localeIds = new Array();
                    for (var _i = 0, _a = this.contents; _i < _a.length; _i++) {
                        var content = _a[_i];
                        if (!content.active)
                            continue;
                        localeIds.push(content.localeId);
                    }
                    return localeIds;
                },
                set: function (localeIds) {
                    for (var _i = 0, _a = this.contents; _i < _a.length; _i++) {
                        var content = _a[_i];
                        content.active = -1 < localeIds.indexOf(content.localeId);
                    }
                },
                enumerable: true,
                configurable: true
            });
            Translatable.prototype.scan = function () {
                var _this = this;
                this.jqElem.children().each(function (i, elem) {
                    var jqElem = $(elem);
                    var localeId = jqElem.data("rocket-impl-locale-id");
                    if (!localeId || _this._contents[localeId])
                        return;
                    _this._contents[localeId] = new TranslatedContent(localeId, jqElem);
                });
            };
            Translatable.from = function (jqElem) {
                var translatable = jqElem.data("rocketImplTranslatable");
                if (translatable instanceof Translatable) {
                    return translatable;
                }
                translatable = new Translatable(jqElem);
                jqElem.data("rocketImplTranslatable", translatable);
                translatable.scan();
                return translatable;
            };
            return Translatable;
        }());
        Impl.Translatable = Translatable;
        var TranslatedContent = (function () {
            function TranslatedContent(_localeId, jqElem) {
                this._localeId = _localeId;
                this.jqElem = jqElem;
                this.jqEnabler = null;
                this.changedCallbacks = [];
                this._visible = true;
                this.jqTranslation = jqElem.children(".rocket-impl-translation");
            }
            Object.defineProperty(TranslatedContent.prototype, "localeId", {
                get: function () {
                    return this._localeId;
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(TranslatedContent.prototype, "prettyLocaleId", {
                get: function () {
                    return this.jqElem.find("label:first").text();
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(TranslatedContent.prototype, "localeName", {
                get: function () {
                    return this.jqElem.find("label:first").attr("title");
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(TranslatedContent.prototype, "visible", {
                get: function () {
                    return this._visible;
                },
                set: function (visible) {
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
                },
                enumerable: true,
                configurable: true
            });
            Object.defineProperty(TranslatedContent.prototype, "active", {
                get: function () {
                    return this.jqEnabler ? false : true;
                },
                set: function (active) {
                    var _this = this;
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
                        "click": function () { _this.active = true; }
                    }).prepend($("<i />", { "class": "fa fa-language", "text": "" })).appendTo(this.jqElem);
                    this.triggerChanged();
                },
                enumerable: true,
                configurable: true
            });
            TranslatedContent.prototype.triggerChanged = function () {
                for (var _i = 0, _a = this.changedCallbacks; _i < _a.length; _i++) {
                    var callback = _a[_i];
                    callback();
                }
            };
            TranslatedContent.prototype.whenChanged = function (callback) {
                this.changedCallbacks.push(callback);
            };
            return TranslatedContent;
        }());
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Overview;
        (function (Overview) {
            var $ = jQuery;
            var Header = (function () {
                function Header(overviewContent) {
                    this.overviewContent = overviewContent;
                }
                Header.prototype.init = function (jqElem) {
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
                };
                return Header;
            }());
            Overview.Header = Header;
            var State = (function () {
                function State(overviewContent) {
                    this.overviewContent = overviewContent;
                }
                State.prototype.draw = function (jqElem) {
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
                };
                State.prototype.reDraw = function () {
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
                };
                return State;
            }());
            var QuicksearchForm = (function () {
                function QuicksearchForm(overviewContent) {
                    this.overviewContent = overviewContent;
                    this.sc = 0;
                    this.serachVal = null;
                }
                QuicksearchForm.prototype.init = function (jqForm) {
                    if (this.form) {
                        throw new Error("Quicksearch already initialized.");
                    }
                    this.form = Impl.Form.from(jqForm);
                    var that = this;
                    this.form.on(Impl.Form.EventType.SUBMIT, function () {
                        that.onSubmit();
                    });
                    this.form.config.blockPage = false;
                    this.form.config.disableControls = false;
                    this.form.config.actionUrl = jqForm.data("rocket-impl-post-url");
                    this.form.config.successResponseHandler = function (data) {
                        that.whenSubmitted(data);
                    };
                    this.initListeners();
                };
                QuicksearchForm.prototype.initListeners = function () {
                    this.form.reset();
                    var jqButtons = this.form.jQuery.find("button[type=submit]");
                    this.jqSearchButton = $(jqButtons.get(0));
                    var jqClearButton = $(jqButtons.get(1));
                    this.jqSearchInput = this.form.jQuery.find("input[type=search]:first");
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
                };
                QuicksearchForm.prototype.updateState = function () {
                    if (this.jqSearchInput.val().length > 0) {
                        this.form.jQuery.addClass("rocket-active");
                    }
                    else {
                        this.form.jQuery.removeClass("rocket-active");
                    }
                };
                QuicksearchForm.prototype.send = function (force) {
                    var searchVal = this.jqSearchInput.val();
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
                };
                QuicksearchForm.prototype.onSubmit = function () {
                    this.sc++;
                    this.overviewContent.clear(true);
                };
                QuicksearchForm.prototype.whenSubmitted = function (data) {
                    this.overviewContent.initFromResponse(data);
                };
                return QuicksearchForm;
            }());
            var CritmodSelect = (function () {
                function CritmodSelect(overviewContent) {
                    this.overviewContent = overviewContent;
                }
                Object.defineProperty(CritmodSelect.prototype, "jQuery", {
                    get: function () {
                        return this.form.jQuery;
                    },
                    enumerable: true,
                    configurable: true
                });
                CritmodSelect.prototype.init = function (jqForm, critmodForm) {
                    if (this.form) {
                        throw new Error("CritmodSelect already initialized.");
                    }
                    this.form = Impl.Form.from(jqForm);
                    this.form.reset();
                    this.critmodForm = critmodForm;
                    this.jqButton = jqForm.find("button[type=submit]").hide();
                    this.form.config.blockPage = false;
                    this.form.config.disableControls = false;
                    this.form.config.actionUrl = jqForm.data("rocket-impl-post-url");
                    this.form.config.autoSubmitAllowed = false;
                    var that = this;
                    this.form.config.successResponseHandler = function (data) {
                        that.whenSubmitted(data);
                    };
                    this.jqSelect = jqForm.find("select:first").change(function () {
                        that.send();
                    });
                    critmodForm.onChange(function () {
                        that.form.abortSubmit();
                        that.updateId();
                    });
                    critmodForm.whenChanged(function (idOptions) {
                        that.updateIdOptions(idOptions);
                    });
                };
                CritmodSelect.prototype.updateState = function () {
                    if (this.jqSelect.val()) {
                        this.form.jQuery.addClass("rocket-active");
                    }
                    else {
                        this.form.jQuery.removeClass("rocket-active");
                    }
                };
                CritmodSelect.prototype.send = function () {
                    this.form.submit({ button: this.jqButton.get(0) });
                    this.updateState();
                    this.overviewContent.clear(true);
                    var id = this.jqSelect.val();
                    this.critmodForm.activated = id ? true : false;
                    this.critmodForm.critmodSaveId = id;
                    this.critmodForm.freeze();
                };
                CritmodSelect.prototype.whenSubmitted = function (data) {
                    this.overviewContent.initFromResponse(data);
                    this.critmodForm.reload();
                };
                CritmodSelect.prototype.updateId = function () {
                    var id = this.critmodForm.critmodSaveId;
                    if (id && isNaN(parseInt(id))) {
                        this.jqSelect.append($("<option />", { "value": id, "text": this.critmodForm.critmodSaveName }));
                    }
                    this.jqSelect.val(id);
                    this.updateState();
                };
                CritmodSelect.prototype.updateIdOptions = function (idOptions) {
                    this.jqSelect.empty();
                    for (var id in idOptions) {
                        this.jqSelect.append($("<option />", { value: id.trim(), text: idOptions[id] }));
                    }
                    this.jqSelect.val(this.critmodForm.critmodSaveId);
                };
                return CritmodSelect;
            }());
            var CritmodForm = (function () {
                function CritmodForm(overviewContent) {
                    this.overviewContent = overviewContent;
                    this.changeCallbacks = [];
                    this.changedCallbacks = [];
                    this._open = true;
                }
                CritmodForm.prototype.drawControl = function (jqControlContainer) {
                    var _this = this;
                    this.jqControlContainer = jqControlContainer;
                    this.jqOpenButton = $("<button />", {
                        "class": "btn btn-secondary",
                        "text": jqControlContainer.data("rocket-impl-open-filter-label") + " "
                    })
                        .append($("<i />", { "class": "fa fa-filter" }))
                        .click(function () { _this.open = true; })
                        .appendTo(jqControlContainer);
                    this.jqEditButton = $("<button />", {
                        "class": "btn btn-secondary",
                        "text": jqControlContainer.data("rocket-impl-edit-filter-label") + " "
                    })
                        .append($("<i />", { "class": "fa fa-filter" }))
                        .click(function () { _this.open = true; })
                        .appendTo(jqControlContainer);
                    this.jqCloseButton = $("<button />", {
                        "class": "btn btn-secondary",
                        "text": jqControlContainer.data("rocket-impl-close-filter-label") + " "
                    })
                        .append($("<i />", { "class": "fa fa-times" }))
                        .click(function () { _this.open = false; })
                        .appendTo(jqControlContainer);
                    this.open = false;
                };
                CritmodForm.prototype.updateControl = function () {
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
                };
                Object.defineProperty(CritmodForm.prototype, "open", {
                    get: function () {
                        return this._open;
                    },
                    set: function (open) {
                        this._open = open;
                        if (open) {
                            this.form.jQuery.show();
                        }
                        else {
                            this.form.jQuery.hide();
                        }
                        this.updateControl();
                    },
                    enumerable: true,
                    configurable: true
                });
                CritmodForm.prototype.init = function (jqForm) {
                    if (this.form) {
                        throw new Error("CritmodForm already initialized.");
                    }
                    this.form = Impl.Form.from(jqForm);
                    this.form.reset();
                    this.form.config.blockPage = false;
                    this.form.config.actionUrl = jqForm.data("rocket-impl-post-url");
                    var that = this;
                    this.form.config.successResponseHandler = function (data) {
                        that.whenSubmitted(data);
                    };
                    var activateFunc = function (ensureCritmodSaveId) {
                        that.activated = true;
                        if (ensureCritmodSaveId && !that.critmodSaveId) {
                            that.critmodSaveId = "new";
                        }
                        that.onSubmit();
                    };
                    var deactivateFunc = function () {
                        that.activated = false;
                        that.critmodSaveId = null;
                        that.block();
                        that.onSubmit();
                    };
                    this.jqApplyButton = jqForm.find(".rocket-impl-critmod-apply").click(function () { activateFunc(false); });
                    this.jqClearButton = jqForm.find(".rocket-impl-critmod-clear").click(function () { deactivateFunc(); });
                    this.jqNameInput = jqForm.find(".rocket-impl-critmod-name");
                    this.jqSaveButton = jqForm.find(".rocket-impl-critmod-save").click(function () { activateFunc(true); });
                    this.jqSaveAsButton = jqForm.find(".rocket-impl-critmod-save-as").click(function () {
                        that.critmodSaveId = null;
                        activateFunc(true);
                    });
                    this.jqDeleteButton = jqForm.find(".rocket-impl-critmod-delete").click(function () { deactivateFunc(); });
                    this.updateState();
                };
                Object.defineProperty(CritmodForm.prototype, "activated", {
                    get: function () {
                        return this.form.jQuery.hasClass("rocket-active");
                    },
                    set: function (activated) {
                        if (activated) {
                            this.form.jQuery.addClass("rocket-active");
                        }
                        else {
                            this.form.jQuery.removeClass("rocket-active");
                        }
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(CritmodForm.prototype, "critmodSaveId", {
                    get: function () {
                        return this.form.jQuery.data("rocket-impl-critmod-save-id");
                    },
                    set: function (critmodSaveId) {
                        this.form.jQuery.data("rocket-impl-critmod-save-id", critmodSaveId);
                        this.updateControl();
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(CritmodForm.prototype, "critmodSaveName", {
                    get: function () {
                        return this.jqNameInput.val();
                    },
                    enumerable: true,
                    configurable: true
                });
                CritmodForm.prototype.updateState = function () {
                    if (this.critmodSaveId) {
                        this.jqSaveAsButton.show();
                        this.jqDeleteButton.show();
                    }
                    else {
                        this.jqSaveAsButton.hide();
                        this.jqDeleteButton.hide();
                    }
                };
                CritmodForm.prototype.freeze = function () {
                    this.form.abortSubmit();
                    this.form.disableControls();
                    this.block();
                };
                CritmodForm.prototype.block = function () {
                    if (this.jqBlocker)
                        return;
                    this.jqBlocker = $("<div />", { "class": "rocket-impl-critmod-blocker" })
                        .appendTo(this.form.jQuery);
                };
                CritmodForm.prototype.reload = function () {
                    var url = this.form.config.actionUrl;
                    var that = this;
                    $.ajax({
                        "url": url,
                        "dataType": "json"
                    }).fail(function (jqXHR, textStatus, data) {
                        if (jqXHR.status != 200) {
                            Rocket.getContainer().handleError(url, jqXHR.responseText);
                            return;
                        }
                        throw new Error("invalid response");
                    }).done(function (data, textStatus, jqXHR) {
                        that.replaceForm(data);
                    });
                };
                CritmodForm.prototype.onSubmit = function () {
                    this.changeCallbacks.forEach(function (callback) {
                        callback();
                    });
                    this.overviewContent.clear(true);
                };
                CritmodForm.prototype.whenSubmitted = function (data) {
                    this.overviewContent.init(1);
                    this.replaceForm(data);
                };
                CritmodForm.prototype.replaceForm = function (data) {
                    if (this.jqBlocker) {
                        this.jqBlocker.remove();
                        this.jqBlocker = null;
                    }
                    var jqForm = $(n2n.ajah.analyze(data));
                    this.form.jQuery.replaceWith(jqForm);
                    this.form = null;
                    n2n.ajah.update();
                    this.init(jqForm);
                    this.open = this.open;
                    this.updateControl();
                    var idOptions = data.additional.critmodSaveIdOptions;
                    this.changedCallbacks.forEach(function (callback) {
                        callback(idOptions);
                    });
                };
                CritmodForm.prototype.onChange = function (callback) {
                    this.changeCallbacks.push(callback);
                };
                CritmodForm.prototype.whenChanged = function (callback) {
                    this.changedCallbacks.push(callback);
                };
                return CritmodForm;
            }());
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
            var OverviewContent = (function () {
                function OverviewContent(jqElem, loadUrl) {
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
                OverviewContent.prototype.isInit = function () {
                    return this._currentPageNo != null && this._numPages != null && this._numEntries != null;
                };
                OverviewContent.prototype.initFromDom = function (currentPageNo, numPages, numEntries) {
                    this.reset(false);
                    this._currentPageNo = currentPageNo;
                    this._numPages = numPages;
                    this._numEntries = numEntries;
                    var page = this.createPage(this.currentPageNo);
                    page.jqContents = this.jqElem.children();
                    this.selectorState.observePage(page);
                    if (this.allInfo) {
                        this.allInfo = new AllInfo([page], 0);
                    }
                    this.buildFakePage();
                    this.triggerContentChange();
                };
                OverviewContent.prototype.init = function (currentPageNo) {
                    this.reset(false);
                    this.goTo(currentPageNo);
                    if (this.allInfo) {
                        this.allInfo = new AllInfo([this.pages[currentPageNo]], 0);
                    }
                    this.buildFakePage();
                    this.triggerContentChange();
                };
                OverviewContent.prototype.initFromResponse = function (data) {
                    this.reset(false);
                    var page = this.createPage(parseInt(data.additional.pageNo));
                    this._currentPageNo = page.pageNo;
                    this.initPageFromResponse(page, data);
                    if (this.allInfo) {
                        this.allInfo = new AllInfo([page], 0);
                    }
                    this.buildFakePage();
                    this.triggerContentChange();
                };
                OverviewContent.prototype.clear = function (showLoader) {
                    this.reset(showLoader);
                    this.triggerContentChange();
                };
                OverviewContent.prototype.reset = function (showLoader) {
                    var page = null;
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
                };
                OverviewContent.prototype.initSelector = function (selectorObserver) {
                    this.selectorState.activate(selectorObserver);
                    this.triggerContentChange();
                    this.buildFakePage();
                };
                OverviewContent.prototype.buildFakePage = function () {
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
                        var id = entry.id;
                        var i;
                        if (-1 < (i = unloadedIds.indexOf(id))) {
                            unloadedIds.splice(i, 1);
                        }
                    });
                    this.loadFakePage(unloadedIds);
                    return this.fakePage;
                };
                OverviewContent.prototype.loadFakePage = function (unloadedIdReps) {
                    if (unloadedIdReps.length == 0) {
                        this.fakePage.jqContents = $();
                        this.selectorState.observeFakePage(this.fakePage);
                        return;
                    }
                    this.markPageAsLoading(0);
                    var fakePage = this.fakePage;
                    var that = this;
                    $.ajax({
                        "url": that.loadUrl.toString(),
                        "data": { "idReps": unloadedIdReps },
                        "dataType": "json"
                    }).fail(function (jqXHR, textStatus, data) {
                        if (fakePage !== that.fakePage)
                            return;
                        that.unmarkPageAsLoading(0);
                        if (jqXHR.status != 200) {
                            Rocket.getContainer().handleError(that.loadUrl, jqXHR.responseText);
                            return;
                        }
                        throw new Error("invalid response");
                    }).done(function (data, textStatus, jqXHR) {
                        if (fakePage !== that.fakePage)
                            return;
                        that.unmarkPageAsLoading(0);
                        var jqContents = $(n2n.ajah.analyze(data)).find(".rocket-overview-content:first").children();
                        fakePage.jqContents = jqContents;
                        that.jqElem.append(jqContents);
                        n2n.ajah.update();
                        that.selectorState.observeFakePage(fakePage);
                        that.triggerContentChange();
                    });
                };
                Object.defineProperty(OverviewContent.prototype, "selectedOnly", {
                    get: function () {
                        return this.allInfo != null;
                    },
                    enumerable: true,
                    configurable: true
                });
                OverviewContent.prototype.showSelected = function () {
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
                };
                OverviewContent.prototype.showAll = function () {
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
                };
                Object.defineProperty(OverviewContent.prototype, "currentPageNo", {
                    get: function () {
                        return this._currentPageNo;
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(OverviewContent.prototype, "numPages", {
                    get: function () {
                        return this._numPages;
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(OverviewContent.prototype, "numEntries", {
                    get: function () {
                        return this._numEntries;
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(OverviewContent.prototype, "numSelectedEntries", {
                    get: function () {
                        if (!this.selectorState.isActive())
                            return null;
                        if (this.fakePage !== null && this.fakePage.isContentLoaded()) {
                            return this.selectorState.selectedEntries.length;
                        }
                        return this.selectorState.selectorObserver.getSelectedIds().length;
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(OverviewContent.prototype, "selectable", {
                    get: function () {
                        return this.selectorState.selectorObserver != null;
                    },
                    enumerable: true,
                    configurable: true
                });
                OverviewContent.prototype.setCurrentPageNo = function (currentPageNo) {
                    if (this._currentPageNo == currentPageNo) {
                        return;
                    }
                    this._currentPageNo = currentPageNo;
                    this.triggerContentChange();
                };
                OverviewContent.prototype.triggerContentChange = function () {
                    var that = this;
                    this.changedCallbacks.forEach(function (callback) {
                        callback(that);
                    });
                };
                OverviewContent.prototype.changeBoundaries = function (numPages, numEntries) {
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
                };
                OverviewContent.prototype.whenContentChanged = function (callback) {
                    this.changedCallbacks.push(callback);
                };
                OverviewContent.prototype.whenSelectionChanged = function (callback) {
                    this.selectorState.whenChanged(callback);
                };
                OverviewContent.prototype.isPageNoValid = function (pageNo) {
                    return (pageNo > 0 && pageNo <= this.numPages);
                };
                OverviewContent.prototype.containsPageNo = function (pageNo) {
                    return this.pages[pageNo] !== undefined;
                };
                OverviewContent.prototype.applyContents = function (page, jqContents) {
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
                };
                OverviewContent.prototype.goTo = function (pageNo) {
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
                };
                OverviewContent.prototype.showSingle = function (pageNo) {
                    for (var i in this.pages) {
                        if (this.pages[i].pageNo == pageNo) {
                            this.pages[i].show();
                        }
                        else {
                            this.pages[i].hide();
                        }
                    }
                };
                OverviewContent.prototype.scrollToPage = function (pageNo, targetPageNo) {
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
                };
                OverviewContent.prototype.markPageAsLoading = function (pageNo) {
                    if (-1 < this.loadingPageNos.indexOf(pageNo)) {
                        throw new Error("page already loading");
                    }
                    this.loadingPageNos.push(pageNo);
                    this.updateLoader();
                };
                OverviewContent.prototype.unmarkPageAsLoading = function (pageNo) {
                    var i = this.loadingPageNos.indexOf(pageNo);
                    if (-1 == i)
                        return;
                    this.loadingPageNos.splice(i, 1);
                    this.updateLoader();
                };
                OverviewContent.prototype.updateLoader = function () {
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
                };
                OverviewContent.prototype.addLoader = function () {
                    if (this.jqLoader)
                        return;
                    this.jqLoader = $("<div />", { "class": "rocket-impl-overview-loading" })
                        .insertAfter(this.jqElem.parent("table"));
                };
                OverviewContent.prototype.removeLoader = function () {
                    if (!this.jqLoader)
                        return;
                    this.jqLoader.remove();
                    this.jqLoader = null;
                };
                OverviewContent.prototype.createPage = function (pageNo) {
                    if (this.containsPageNo(pageNo)) {
                        throw new Error("Page already exists: " + pageNo);
                    }
                    var page = this.pages[pageNo] = new Page(pageNo);
                    if (this.selectedOnly) {
                        page.hide();
                    }
                    return page;
                };
                OverviewContent.prototype.load = function (pageNo) {
                    var page = this.createPage(pageNo);
                    this.markPageAsLoading(pageNo);
                    var that = this;
                    $.ajax({
                        "url": that.loadUrl.toString(),
                        "data": { "pageNo": pageNo },
                        "dataType": "json"
                    }).fail(function (jqXHR, textStatus, data) {
                        if (page !== that.pages[pageNo])
                            return;
                        that.unmarkPageAsLoading(pageNo);
                        if (jqXHR.status != 200) {
                            Rocket.getContainer().handleError(that.loadUrl, jqXHR.responseText);
                            return;
                        }
                        throw new Error("invalid response");
                    }).done(function (data, textStatus, jqXHR) {
                        if (page !== that.pages[pageNo])
                            return;
                        that.unmarkPageAsLoading(pageNo);
                        that.initPageFromResponse(page, data);
                        that.triggerContentChange();
                    });
                };
                OverviewContent.prototype.initPageFromResponse = function (page, jsonData) {
                    this.changeBoundaries(jsonData.additional.numPages, jsonData.additional.numEntries);
                    var jqContents = $(n2n.ajah.analyze(jsonData)).find(".rocket-overview-content:first").children();
                    this.applyContents(page, jqContents);
                    n2n.ajah.update();
                };
                return OverviewContent;
            }());
            Overview.OverviewContent = OverviewContent;
            var SelectorState = (function () {
                function SelectorState() {
                    this._selectorObserver = null;
                    this.entryMap = {};
                    this.fakeEntryMap = {};
                    this.changedCallbacks = new Array();
                    this._autoShowSelected = false;
                }
                SelectorState.prototype.activate = function (selectorObserver) {
                    if (this._selectorObserver) {
                        throw new Error("Selector state already activated");
                    }
                    this._selectorObserver = selectorObserver;
                    if (!selectorObserver)
                        return;
                    for (var id in this.entryMap) {
                        if (this.entryMap[id].selector === null)
                            continue;
                        selectorObserver.observeEntrySelector(this.entryMap[id].selector);
                    }
                };
                SelectorState.prototype.observeFakePage = function (fakePage) {
                    var that = this;
                    fakePage.entries.forEach(function (entry) {
                        if (that.containsEntryId(entry.id)) {
                            entry.dispose();
                        }
                        else {
                            that.registerEntry(entry);
                        }
                    });
                };
                Object.defineProperty(SelectorState.prototype, "selectorObserver", {
                    get: function () {
                        return this._selectorObserver;
                    },
                    enumerable: true,
                    configurable: true
                });
                SelectorState.prototype.isActive = function () {
                    return this._selectorObserver != null;
                };
                SelectorState.prototype.observePage = function (page) {
                    var that = this;
                    page.entries.forEach(function (entry) {
                        if (that.fakeEntryMap[entry.id]) {
                            that.fakeEntryMap[entry.id].dispose();
                        }
                        that.registerEntry(entry);
                    });
                };
                SelectorState.prototype.registerEntry = function (entry, fake) {
                    if (fake === void 0) { fake = false; }
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
                };
                SelectorState.prototype.containsEntryId = function (id) {
                    return this.entryMap[id] !== undefined;
                };
                Object.defineProperty(SelectorState.prototype, "entries", {
                    get: function () {
                        var k = Object;
                        return k.values(this.entryMap);
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(SelectorState.prototype, "selectedEntries", {
                    get: function () {
                        var entries = new Array();
                        for (var _i = 0, _a = this.entries; _i < _a.length; _i++) {
                            var entry = _a[_i];
                            if (!entry.selector || !entry.selector.selected)
                                continue;
                            entries.push(entry);
                        }
                        return entries;
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(SelectorState.prototype, "autoShowSelected", {
                    get: function () {
                        return this._autoShowSelected;
                    },
                    set: function (showSelected) {
                        this._autoShowSelected = showSelected;
                    },
                    enumerable: true,
                    configurable: true
                });
                SelectorState.prototype.showSelectedEntriesOnly = function () {
                    this.entries.forEach(function (entry) {
                        if (entry.selector.selected) {
                            entry.show();
                        }
                        else {
                            entry.hide();
                        }
                    });
                };
                SelectorState.prototype.hideEntries = function () {
                    this.entries.forEach(function (entry) {
                        entry.hide();
                    });
                };
                SelectorState.prototype.triggerChanged = function () {
                    this.changedCallbacks.forEach(function (callback) {
                        callback();
                    });
                };
                SelectorState.prototype.whenChanged = function (callback) {
                    this.changedCallbacks.push(callback);
                };
                return SelectorState;
            }());
            var AllInfo = (function () {
                function AllInfo(pages, scrollTop) {
                    this.pages = pages;
                    this.scrollTop = scrollTop;
                }
                return AllInfo;
            }());
            var Page = (function () {
                function Page(pageNo, _jqContents) {
                    if (_jqContents === void 0) { _jqContents = null; }
                    this.pageNo = pageNo;
                    this._jqContents = _jqContents;
                    this._visible = true;
                }
                Object.defineProperty(Page.prototype, "visible", {
                    get: function () {
                        return this._visible;
                    },
                    enumerable: true,
                    configurable: true
                });
                Page.prototype.show = function () {
                    this._visible = true;
                    this.disp();
                };
                Page.prototype.hide = function () {
                    this._visible = false;
                    this.disp();
                };
                Page.prototype.dispose = function () {
                    if (!this.isContentLoaded())
                        return;
                    this._jqContents.remove();
                    this._jqContents = null;
                    this._entries = null;
                };
                Page.prototype.isContentLoaded = function () {
                    return this.jqContents !== null;
                };
                Object.defineProperty(Page.prototype, "entries", {
                    get: function () {
                        return this._entries;
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Page.prototype, "jqContents", {
                    get: function () {
                        return this._jqContents;
                    },
                    set: function (jqContents) {
                        this._jqContents = jqContents;
                        this._entries = Rocket.Display.Entry.findAll(this.jqContents, true);
                        this.disp();
                        var that = this;
                        var _loop_3 = function () {
                            var entry = this_3._entries[i];
                            entry.on(Rocket.Display.Entry.EventType.DISPOSED, function () {
                                var j = that._entries.indexOf(entry);
                                if (-1 == j)
                                    return;
                                that._entries.splice(j, 1);
                            });
                        };
                        var this_3 = this;
                        for (var i in this._entries) {
                            _loop_3();
                        }
                    },
                    enumerable: true,
                    configurable: true
                });
                Page.prototype.disp = function () {
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
                };
                Page.prototype.removeEntryById = function (id) {
                    for (var i in this._entries) {
                        if (this._entries[i].id != id)
                            continue;
                        this._entries[i].jqQuery.remove();
                        this._entries.splice(parseInt(i), 1);
                        return;
                    }
                };
                return Page;
            }());
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
            var OverviewPage = (function () {
                function OverviewPage(jqContainer, overviewContent) {
                    this.jqContainer = jqContainer;
                    this.overviewContent = overviewContent;
                }
                OverviewPage.prototype.initSelector = function (selectorObserver) {
                    this.overviewContent.initSelector(selectorObserver);
                };
                OverviewPage.findAll = function (jqElem) {
                    var oc = new Array();
                    jqElem.find(".rocket-impl-overview").each(function () {
                        oc.push(OverviewPage.from($(this)));
                    });
                    return oc;
                };
                OverviewPage.from = function (jqElem) {
                    var overviewPage = jqElem.data("rocketImplOverviewPage");
                    if (overviewPage instanceof OverviewPage) {
                        return overviewPage;
                    }
                    var jqForm = jqElem.children("form");
                    var overviewContent = new Overview.OverviewContent(jqElem.find("tbody.rocket-overview-content:first"), jqElem.children(".rocket-impl-overview-tools").data("content-url"));
                    overviewContent.initFromDom(jqElem.data("current-page"), jqElem.data("num-pages"), jqElem.data("num-entries"));
                    var pagination = new Pagination(overviewContent);
                    pagination.draw(jqForm.children(".rocket-context-commands"));
                    var header = new Overview.Header(overviewContent);
                    header.init(jqElem.children(".rocket-impl-overview-tools"));
                    overviewPage = new OverviewPage(jqElem, overviewContent);
                    jqElem.data("rocketImplOverviewPage", overviewPage);
                    return overviewPage;
                };
                return OverviewPage;
            }());
            Overview.OverviewPage = OverviewPage;
            var Pagination = (function () {
                function Pagination(overviewContent) {
                    this.overviewContent = overviewContent;
                }
                Pagination.prototype.getCurrentPageNo = function () {
                    return this.overviewContent.currentPageNo;
                };
                Pagination.prototype.getNumPages = function () {
                    return this.overviewContent.numPages;
                };
                Pagination.prototype.goTo = function (pageNo) {
                    this.overviewContent.goTo(pageNo);
                    return;
                };
                Pagination.prototype.draw = function (jqContainer) {
                    var that = this;
                    this.jqPagination = $("<div />", { "class": "rocket-impl-overview-pagination" });
                    jqContainer.append(this.jqPagination);
                    this.jqPagination.append($("<button />", {
                        "type": "button",
                        "class": "rocket-impl-pagination-first rocket-control",
                        "click": function () { that.goTo(1); }
                    }).append($("<i />", {
                        "class": "fa fa-step-backward"
                    })));
                    this.jqPagination.append($("<button />", {
                        "type": "button",
                        "class": "rocket-impl-pagination-prev rocket-control",
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
                        var pageNo = parseInt(that.jqInput.val());
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
                        "class": "rocket-impl-pagination-next rocket-control",
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
                        "class": "rocket-impl-pagination-last rocket-control",
                        "click": function () { that.goTo(that.getNumPages()); }
                    }).append($("<i />", {
                        "class": "fa fa-step-forward"
                    })));
                    this.overviewContent.whenContentChanged(function () {
                        if (!that.overviewContent.isInit() || that.overviewContent.selectedOnly || that.overviewContent.numPages == 1) {
                            that.jqPagination.hide();
                        }
                        else {
                            that.jqPagination.show();
                        }
                        that.jqInput.val(that.overviewContent.currentPageNo);
                    });
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
                    });
                };
                return FixedHeader;
            }());
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
            var MultiEntrySelectorObserver = (function () {
                function MultiEntrySelectorObserver(originalIdReps) {
                    if (originalIdReps === void 0) { originalIdReps = new Array(); }
                    this.originalIdReps = originalIdReps;
                    this.identityStrings = {};
                    this.selectors = {};
                    this.selectedIds = originalIdReps;
                }
                MultiEntrySelectorObserver.prototype.observeEntrySelector = function (selector) {
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
                };
                MultiEntrySelectorObserver.prototype.containsSelectedId = function (id) {
                    return -1 < this.selectedIds.indexOf(id);
                };
                MultiEntrySelectorObserver.prototype.chSelect = function (selected, id) {
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
                };
                MultiEntrySelectorObserver.prototype.getSelectedIds = function () {
                    return this.selectedIds;
                };
                MultiEntrySelectorObserver.prototype.getIdentityStringById = function (id) {
                    if (this.identityStrings[id] !== undefined) {
                        return this.identityStrings[id];
                    }
                    return null;
                };
                MultiEntrySelectorObserver.prototype.getSelectorById = function (id) {
                    if (this.selectors[id] !== undefined) {
                        return this.selectors[id];
                    }
                    return null;
                };
                MultiEntrySelectorObserver.prototype.setSelectedIds = function (selectedIds) {
                    this.selectedIds = selectedIds;
                    var that = this;
                    for (var id in this.selectors) {
                        this.selectors[id].selected = that.containsSelectedId(id);
                    }
                };
                return MultiEntrySelectorObserver;
            }());
            Overview.MultiEntrySelectorObserver = MultiEntrySelectorObserver;
            var SingleEntrySelectorObserver = (function () {
                function SingleEntrySelectorObserver(originalId) {
                    if (originalId === void 0) { originalId = null; }
                    this.originalId = originalId;
                    this.selectedId = null;
                    this.identityStrings = {};
                    this.selectors = {};
                    this.selectedId = originalId;
                }
                SingleEntrySelectorObserver.prototype.observeEntrySelector = function (selector) {
                    var _this = this;
                    var that = this;
                    var jqCheck = $("<input />", { "type": "radio" });
                    selector.jQuery.empty();
                    selector.jQuery.append(jqCheck);
                    jqCheck.change(function () {
                        selector.selected = jqCheck.is(":checked");
                    });
                    selector.whenChanged(function () {
                        jqCheck.prop("checked", selector.selected);
                        _this.chSelect(selector.selected, selector.entry.id);
                    });
                    var entry = selector.entry;
                    var id = entry.id;
                    selector.selected = this.selectedId === id;
                    this.selectors[id] = selector;
                    this.identityStrings[id] = entry.identityString;
                    entry.on(display.Entry.EventType.DISPOSED, function () {
                        delete _this.selectors[id];
                    });
                    entry.on(display.Entry.EventType.REMOVED, function () {
                        this.chSelect(false, id);
                    });
                };
                SingleEntrySelectorObserver.prototype.getSelectedIds = function () {
                    return [this.selectedId];
                };
                SingleEntrySelectorObserver.prototype.chSelect = function (selected, id) {
                    if (!selected) {
                        if (this.selectedId === id) {
                            this.selectedId = null;
                        }
                        return;
                    }
                    if (this.selectedId === id)
                        return;
                    this.selectedId = id;
                    for (var id_1 in this.selectors) {
                        if (id_1 === this.selectedId)
                            continue;
                        this.selectors[id_1].selected = false;
                    }
                };
                SingleEntrySelectorObserver.prototype.getIdentityStringById = function (id) {
                    if (this.identityStrings[id] !== undefined) {
                        return this.identityStrings[id];
                    }
                    return null;
                };
                SingleEntrySelectorObserver.prototype.getSelectorById = function (id) {
                    if (this.selectors[id] !== undefined) {
                        return this.selectors[id];
                    }
                    return null;
                };
                SingleEntrySelectorObserver.prototype.setSelectedId = function (selectedId) {
                    if (this.selectors[selectedId]) {
                        this.selectors[selectedId].selected = true;
                        return;
                    }
                    this.selectedId = selectedId;
                    for (var id in this.selectors) {
                        this.selectors[id].selected = false;
                    }
                };
                return SingleEntrySelectorObserver;
            }());
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
            var AddControlFactory = (function () {
                function AddControlFactory(embeddedEntryRetriever, addLabel, replaceLabel) {
                    if (replaceLabel === void 0) { replaceLabel = null; }
                    this.embeddedEntryRetriever = embeddedEntryRetriever;
                    this.addLabel = addLabel;
                    this.replaceLabel = replaceLabel;
                }
                AddControlFactory.prototype.createAdd = function () {
                    return AddControl.create(this.addLabel, this.embeddedEntryRetriever);
                };
                AddControlFactory.prototype.createReplace = function () {
                    return AddControl.create(this.replaceLabel, this.embeddedEntryRetriever);
                };
                return AddControlFactory;
            }());
            Relation.AddControlFactory = AddControlFactory;
            var AddControl = (function () {
                function AddControl(jqElem, embeddedEntryRetriever) {
                    var _this = this;
                    this.onNewEntryCallbacks = [];
                    this.disposed = false;
                    this.embeddedEntryRetriever = embeddedEntryRetriever;
                    this.jqElem = jqElem;
                    this.jqButton = jqElem.children("button");
                    this.jqButton.on("mouseenter", function () {
                        _this.embeddedEntryRetriever.setPreloadEnabled(true);
                    });
                    this.jqButton.on("click", function () {
                        if (_this.isLoading())
                            return;
                        if (_this.jqMultiTypeUl) {
                            _this.jqMultiTypeUl.toggle();
                            return;
                        }
                        _this.block(true);
                        _this.embeddedEntryRetriever.lookupNew(function (embeddedEntry) {
                            _this.examine(embeddedEntry);
                        }, function () {
                            _this.block(false);
                        });
                    });
                }
                Object.defineProperty(AddControl.prototype, "jQuery", {
                    get: function () {
                        return this.jqElem;
                    },
                    enumerable: true,
                    configurable: true
                });
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
                    var _this = this;
                    this.block(false);
                    if (!embeddedEntry.entryForm.multiEiType) {
                        this.fireCallbacks(embeddedEntry);
                        return;
                    }
                    this.multiTypeEmbeddedEntry = embeddedEntry;
                    this.jqMultiTypeUl = $("<ul />", { "class": "rocket-impl-multi-type-menu" });
                    this.jqElem.append(this.jqMultiTypeUl);
                    var typeMap = embeddedEntry.entryForm.typeMap;
                    var _loop_4 = function (typeId) {
                        this_4.jqMultiTypeUl.append($("<li />").append($("<button />", {
                            "type": "button",
                            "text": typeMap[typeId],
                            "click": function () {
                                embeddedEntry.entryForm.curEiTypeId = typeId;
                                _this.jqMultiTypeUl.remove();
                                _this.jqMultiTypeUl = null;
                                _this.multiTypeEmbeddedEntry = null;
                                _this.fireCallbacks(embeddedEntry);
                            }
                        })));
                    };
                    var this_4 = this;
                    for (var typeId in typeMap) {
                        _loop_4(typeId);
                    }
                };
                AddControl.prototype.dispose = function () {
                    this.disposed = true;
                    this.jqElem.remove();
                    if (this.multiTypeEmbeddedEntry !== null) {
                        this.fireCallbacks(this.multiTypeEmbeddedEntry);
                        this.multiTypeEmbeddedEntry = null;
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
                    return new AddControl($("<div />", { "class": "rocket-impl-add-entry" })
                        .append($("<button />", { "text": label, "type": "button", "class": "btn btn-block btn-secondary" })), embeddedEntryRetriever);
                };
                return AddControl;
            }());
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
                    var _this = this;
                    $.ajax({
                        "url": this.urlStr,
                        "data": {
                            "propertyPath": this.propertyPath + "[" + this.keyPrefix + (this.startKey++) + "]",
                            "draft": this.draftMode ? 1 : 0
                        },
                        "dataType": "json"
                    }).fail(function (jqXHR, textStatus, data) {
                        if (jqXHR.status != 200) {
                            Rocket.handleErrorResponse(_this.urlStr, jqXHR);
                        }
                        _this.failResponse();
                    }).done(function (data, textStatus, jqXHR) {
                        _this.doneResponse(data);
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
            Relation.EmbeddedEntryRetriever = EmbeddedEntryRetriever;
            var EmbeddedEntry = (function () {
                function EmbeddedEntry(jqEntry, readOnly) {
                    this.readOnly = readOnly;
                    this.entryGroup = Rocket.Display.StructureElement.from(jqEntry, true);
                    this.bodyGroup = Rocket.Display.StructureElement.from(jqEntry.children(".rocket-impl-body"), true);
                    this.jqOrderIndex = jqEntry.children(".rocket-impl-order-index").hide();
                    this.jqSummary = jqEntry.children(".rocket-impl-summary");
                    this.jqPageCommands = this.bodyGroup.jQuery.children(".rocket-context-commands");
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
                Object.defineProperty(EmbeddedEntry.prototype, "entryForm", {
                    get: function () {
                        return this._entryForm;
                    },
                    enumerable: true,
                    configurable: true
                });
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
                Object.defineProperty(EmbeddedEntry.prototype, "jQuery", {
                    get: function () {
                        return this.entryGroup.jQuery;
                    },
                    enumerable: true,
                    configurable: true
                });
                EmbeddedEntry.prototype.getExpandedCommandList = function () {
                    return this.bodyGroup.getToolbar().getCommandList();
                };
                EmbeddedEntry.prototype.expand = function (asPartOfList) {
                    if (asPartOfList === void 0) { asPartOfList = true; }
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
                };
                EmbeddedEntry.prototype.reduce = function () {
                    this.entryGroup.show();
                    this.jqSummary.show();
                    this.bodyGroup.hide();
                    var jqContentType = this.jqSummary.find(".rocket-impl-content-type:first");
                    jqContentType.children("span").text(this.entryForm.curGenericLabel);
                    jqContentType.children("i").attr("class", this.entryForm.curGenericIconType);
                    this.entryGroup.jQuery.removeClass("rocket-group");
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
                EmbeddedEntry.prototype.dispose = function () {
                    this.jQuery.remove();
                };
                return EmbeddedEntry;
            }());
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
                };
                return ToMany;
            }());
            Relation.ToMany = ToMany;
            var ToManySelector = (function () {
                function ToManySelector(jqElem, jqNewEntrySkeleton) {
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
                ToManySelector.prototype.determineIdentityString = function (idRep) {
                    return this.identityStrings[idRep];
                };
                ToManySelector.prototype.init = function () {
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
                };
                ToManySelector.prototype.createSelectedEntry = function (idRep, identityString) {
                    if (identityString === void 0) { identityString = null; }
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
                };
                ToManySelector.prototype.addSelectedEntry = function (entry) {
                    this.entries.push(entry);
                    var that = this;
                    entry.commandList.createJqCommandButton({ iconType: "fa fa-times", label: this.jqElem.data("remove-entry-label") }).click(function () {
                        that.removeSelectedEntry(entry);
                    });
                };
                ToManySelector.prototype.removeSelectedEntry = function (entry) {
                    for (var i in this.entries) {
                        if (this.entries[i] !== entry)
                            continue;
                        entry.jQuery.remove();
                        this.entries.splice(parseInt(i), 1);
                    }
                };
                ToManySelector.prototype.reset = function () {
                    this.clear();
                    for (var _i = 0, _a = this.originalIdReps; _i < _a.length; _i++) {
                        var idRep = _a[_i];
                        this.createSelectedEntry(idRep);
                    }
                };
                ToManySelector.prototype.clear = function () {
                    for (var i in this.entries) {
                        this.entries[i].jQuery.remove();
                    }
                    this.entries.splice(0, this.entries.length);
                };
                ToManySelector.prototype.loadBrowser = function () {
                    var _this = this;
                    if (this.browserLayer !== null)
                        return;
                    var that = this;
                    this.browserLayer = Rocket.getContainer().createLayer(cmd.Page.of(this.jqElem));
                    this.browserLayer.hide();
                    this.browserLayer.on(cmd.Layer.EventType.CLOSE, function () {
                        that.browserLayer = null;
                        that.browserSelectorObserver = null;
                    });
                    var url = this.jqElem.data("overview-tools-url");
                    this.browserLayer.monitor.exec(url).then(function () {
                        that.iniBrowserPage(_this.browserLayer.getPageByUrl(url));
                    });
                };
                ToManySelector.prototype.iniBrowserPage = function (context) {
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
                };
                ToManySelector.prototype.openBrowser = function () {
                    this.loadBrowser();
                    this.updateBrowser();
                    this.browserLayer.show();
                };
                ToManySelector.prototype.updateBrowser = function () {
                    if (this.browserSelectorObserver === null)
                        return;
                    var selectedIds = new Array();
                    this.entries.forEach(function (entry) {
                        selectedIds.push(entry.idRep);
                    });
                    this.browserSelectorObserver.setSelectedIds(selectedIds);
                };
                ToManySelector.prototype.updateSelection = function () {
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
                };
                return ToManySelector;
            }());
            var SelectedEntry = (function () {
                function SelectedEntry(jqElem) {
                    this.jqElem = jqElem;
                    jqElem.prepend(this.jqLabel = $("<span />"));
                    this.cmdList = new display.CommandList($("<div />", true).appendTo(jqElem));
                    this.jqInput = jqElem.children("input").hide();
                }
                Object.defineProperty(SelectedEntry.prototype, "jQuery", {
                    get: function () {
                        return this.jqElem;
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(SelectedEntry.prototype, "commandList", {
                    get: function () {
                        return this.cmdList;
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(SelectedEntry.prototype, "label", {
                    get: function () {
                        return this.jqLabel.text();
                    },
                    set: function (label) {
                        this.jqLabel.text(label);
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(SelectedEntry.prototype, "idRep", {
                    get: function () {
                        return this.jqInput.val().toString();
                    },
                    set: function (idRep) {
                        this.jqInput.val(idRep);
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
                    var jqGroup = this.jqToMany.children(".rocket-group");
                    if (jqGroup.length > 0) {
                        jqGroup.append(this.jqEmbedded);
                    }
                    else {
                        this.jqToMany.append(this.jqEmbedded);
                    }
                    this.jqEntries = $("<div />");
                    this.jqEmbedded.append(this.jqEntries);
                    if (this.compact) {
                        var structureElement = Rocket.Display.StructureElement.findFrom(this.jqEmbedded);
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
                };
                ToManyEmbedded.prototype.createFirstAddControl = function () {
                    var addControl = this.addControlFactory.createAdd();
                    var that = this;
                    this.jqEmbedded.prepend(addControl.jQuery);
                    addControl.onNewEmbeddedEntry(function (newEntry) {
                        that.insertEntry(newEntry);
                    });
                    return addControl;
                };
                ToManyEmbedded.prototype.createEntryAddControl = function (entry) {
                    var addControl = this.addControlFactory.createAdd();
                    var that = this;
                    this.entryAddControls.push(addControl);
                    addControl.jQuery.insertBefore(entry.jQuery);
                    addControl.onNewEmbeddedEntry(function (newEntry) {
                        that.insertEntry(newEntry, entry);
                    });
                    return addControl;
                };
                ToManyEmbedded.prototype.createLastAddControl = function () {
                    var addControl = this.addControlFactory.createAdd();
                    var that = this;
                    this.jqEmbedded.append(addControl.jQuery);
                    addControl.onNewEmbeddedEntry(function (newEntry) {
                        that.addEntry(newEntry);
                    });
                    return addControl;
                };
                ToManyEmbedded.prototype.insertEntry = function (entry, beforeEntry) {
                    if (beforeEntry === void 0) { beforeEntry = null; }
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
                };
                ToManyEmbedded.prototype.addEntry = function (entry) {
                    entry.setOrderIndex(this.entries.length);
                    this.entries.push(entry);
                    this.jqEntries.append(entry.jQuery);
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
                    return this.expandPage !== null;
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
                    this.expandPage = Rocket.getContainer().createLayer().createPage(window.location.href);
                    this.jqEmbedded.detach();
                    this.expandPage.applyContent(this.jqEmbedded);
                    this.expandPage.layer.pushHistoryEntry(window.location.href);
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
                    var jqCommandButton = this.expandPage.menu.commandList
                        .createJqCommandButton({ iconType: "fa fa-times", label: this.closeLabel, severity: display.Severity.WARNING }, true);
                    jqCommandButton.click(function () {
                        that.expandPage.layer.close();
                    });
                    this.expandPage.on(cmd.Page.EventType.CLOSE, function () {
                        that.reduce();
                    });
                    this.changed();
                    n2n.ajah.update();
                };
                ToManyEmbedded.prototype.reduce = function () {
                    if (!this.isExpanded())
                        return;
                    this.dominantEntry = null;
                    this.expandPage = null;
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
            var ToOne = (function () {
                function ToOne(toOneSelector, embedded) {
                    if (toOneSelector === void 0) { toOneSelector = null; }
                    if (embedded === void 0) { embedded = null; }
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
                ToOne.from = function (jqToOne) {
                    var toOne = jqToOne.data("rocketImplToOne");
                    if (toOne instanceof ToOne) {
                        return toOne;
                    }
                    var toOneSelector = null;
                    var jqSelector = jqToOne.children(".rocket-impl-selector");
                    if (jqSelector.length > 0) {
                        toOneSelector = new ToOneSelector(jqSelector);
                    }
                    var jqCurrent = jqToOne.children(".rocket-impl-current");
                    var jqNew = jqToOne.children(".rocket-impl-new");
                    var addControlFactory = null;
                    var toOneEmbedded = null;
                    if (jqCurrent.length > 0 || jqNew.length > 0) {
                        if (jqNew.length > 0) {
                            var propertyPath = jqNew.data("property-path");
                            var startKey = 0;
                            var testPropertyPath = propertyPath + "[n";
                            jqNew.find("input, textarea").each(function () {
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
                            var entryFormRetriever = new Relation.EmbeddedEntryRetriever(jqNew.data("new-entry-form-url"), propertyPath, jqNew.data("draftMode"), startKey, "n");
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
                };
                return ToOne;
            }());
            Relation.ToOne = ToOne;
            var ToOneEmbedded = (function () {
                function ToOneEmbedded(jqToOne, addButtonFactory) {
                    if (addButtonFactory === void 0) { addButtonFactory = null; }
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
                ToOneEmbedded.prototype.isReadOnly = function () {
                    return this.addControlFactory === null;
                };
                ToOneEmbedded.prototype.changed = function () {
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
                };
                ToOneEmbedded.prototype.createReplaceControl = function (prepend) {
                    var _this = this;
                    var addControl = this.addControlFactory.createReplace();
                    if (prepend) {
                        this.jqEmbedded.prepend(addControl.jQuery);
                    }
                    else {
                        this.jqEmbedded.append(addControl.jQuery);
                    }
                    addControl.onNewEmbeddedEntry(function (newEntry) {
                        _this.newEntry = newEntry;
                    });
                    return addControl;
                };
                ToOneEmbedded.prototype.createAddControl = function () {
                    var _this = this;
                    var addControl = this.addControlFactory.createAdd();
                    this.jqEmbedded.append(addControl.jQuery);
                    addControl.onNewEmbeddedEntry(function (newEntry) {
                        _this.newEntry = newEntry;
                    });
                    return addControl;
                };
                Object.defineProperty(ToOneEmbedded.prototype, "currentEntry", {
                    get: function () {
                        return this._currentEntry;
                    },
                    set: function (entry) {
                        var _this = this;
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
                        entry.onRemove(function () {
                            _this._currentEntry.dispose();
                            _this._currentEntry = null;
                            _this.changed();
                        });
                        this.initEntry(entry);
                        this.changed();
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(ToOneEmbedded.prototype, "newEntry", {
                    get: function () {
                        return this._newEntry;
                    },
                    set: function (entry) {
                        var _this = this;
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
                        entry.onRemove(function () {
                            _this._newEntry.dispose();
                            _this._newEntry = null;
                            if (_this.currentEntry) {
                                _this.currentEntry.jQuery.appendTo(_this.jqEntries);
                            }
                            _this.changed();
                        });
                        this.initEntry(entry);
                        this.changed();
                    },
                    enumerable: true,
                    configurable: true
                });
                ToOneEmbedded.prototype.initEntry = function (entry) {
                    var _this = this;
                    this.jqEntries.append(entry.jQuery);
                    if (this.isExpanded()) {
                        entry.expand(false);
                    }
                    else {
                        entry.reduce();
                    }
                    entry.onFocus(function () {
                        _this.expand();
                    });
                };
                ToOneEmbedded.prototype.isExpanded = function () {
                    return this.expandPage !== null;
                };
                ToOneEmbedded.prototype.expand = function () {
                    var _this = this;
                    if (this.isExpanded())
                        return;
                    this.expandPage = Rocket.getContainer().createLayer().createPage(window.location.href);
                    this.jqEmbedded.detach();
                    this.expandPage.applyContent(this.jqEmbedded);
                    this.expandPage.layer.pushHistoryEntry(window.location.href);
                    if (this.newEntry) {
                        this.newEntry.expand(false);
                    }
                    if (this.currentEntry) {
                        this.currentEntry.expand(false);
                    }
                    var jqCommandButton = this.expandPage.menu.commandList
                        .createJqCommandButton({ iconType: "fa fa-times", label: this.closeLabel, severity: display.Severity.WARNING }, true);
                    jqCommandButton.click(function () {
                        _this.expandPage.layer.close();
                    });
                    this.expandPage.on(cmd.Page.EventType.CLOSE, function () {
                        _this.reduce();
                    });
                    this.changed();
                    n2n.ajah.update();
                };
                ToOneEmbedded.prototype.reduce = function () {
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
                    n2n.ajah.update();
                };
                ToOneEmbedded.prototype.triggerChanged = function () {
                    for (var _i = 0, _a = this.changedCallbacks; _i < _a.length; _i++) {
                        var callback = _a[_i];
                        callback();
                    }
                };
                ToOneEmbedded.prototype.whenChanged = function (callback) {
                    this.changedCallbacks.push(callback);
                };
                return ToOneEmbedded;
            }());
            var ToOneSelector = (function () {
                function ToOneSelector(jqElem) {
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
                Object.defineProperty(ToOneSelector.prototype, "jQuery", {
                    get: function () {
                        return this.jqElem;
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(ToOneSelector.prototype, "selectedIdRep", {
                    get: function () {
                        var idRep = this.jqInput.val();
                        if (idRep.length == 0)
                            return null;
                        return idRep;
                    },
                    enumerable: true,
                    configurable: true
                });
                ToOneSelector.prototype.init = function () {
                    var _this = this;
                    this.jqSelectedEntry = $("<div />");
                    this.jqSelectedEntry.append(this.jqEntryLabel = $("<span />", { "text": this.identityStrings[this.originalIdRep] }));
                    new display.CommandList($("<div />", true).appendTo(this.jqSelectedEntry))
                        .createJqCommandButton({ iconType: "fa fa-times", label: this.jqElem.data("remove-entry-label") })
                        .click(function () {
                        _this.clear();
                    });
                    this.jqElem.append(this.jqSelectedEntry);
                    var jqCommandList = $("<div />");
                    this.jqElem.append(jqCommandList);
                    var commandList = new display.CommandList(jqCommandList);
                    commandList.createJqCommandButton({ label: this.jqElem.data("select-label") })
                        .mouseenter(function () {
                        _this.loadBrowser();
                    })
                        .click(function () {
                        _this.openBrowser();
                    });
                    commandList.createJqCommandButton({ label: this.jqElem.data("reset-label") })
                        .click(function () {
                        _this.reset();
                    });
                };
                ToOneSelector.prototype.selectEntry = function (idRep, identityString) {
                    if (identityString === void 0) { identityString = null; }
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
                };
                ToOneSelector.prototype.reset = function () {
                    this.selectEntry(this.originalIdRep);
                };
                ToOneSelector.prototype.clear = function () {
                    this.selectEntry(null);
                };
                ToOneSelector.prototype.loadBrowser = function () {
                    if (this.browserLayer !== null)
                        return;
                    var that = this;
                    this.browserLayer = Rocket.getContainer().createLayer(cmd.Page.of(this.jqElem));
                    this.browserLayer.hide();
                    this.browserLayer.on(cmd.Layer.EventType.CLOSE, function () {
                        that.browserLayer = null;
                        that.browserSelectorObserver = null;
                    });
                    Rocket.exec(this.jqElem.data("overview-tools-url"), {
                        showLoadingPage: true,
                        currentLayer: this.browserLayer,
                        done: function (result) {
                            that.iniBrowserPage(result.context);
                        }
                    });
                };
                ToOneSelector.prototype.iniBrowserPage = function (context) {
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
                };
                ToOneSelector.prototype.openBrowser = function () {
                    this.loadBrowser();
                    this.updateBrowser();
                    this.browserLayer.show();
                };
                ToOneSelector.prototype.updateBrowser = function () {
                    if (this.browserSelectorObserver === null)
                        return;
                    this.browserSelectorObserver.setSelectedId(this.selectedIdRep);
                };
                ToOneSelector.prototype.updateSelection = function () {
                    var _this = this;
                    if (this.browserSelectorObserver === null)
                        return;
                    this.clear();
                    this.browserSelectorObserver.getSelectedIds().forEach(function (id) {
                        var identityString = _this.browserSelectorObserver.getIdentityStringById(id);
                        if (identityString !== null) {
                            _this.selectEntry(id, identityString);
                            return;
                        }
                        _this.selectEntry(id);
                    });
                };
                return ToOneSelector;
            }());
        })(Relation = Impl.Relation || (Impl.Relation = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
//# sourceMappingURL=rocket.js.map