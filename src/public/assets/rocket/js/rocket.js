var rocket;
(function (rocket) {
    var cmd;
    (function (cmd) {
        var $ = jQuery;
        var Monitor = (function () {
            function Monitor(container) {
                this.container = container;
            }
            Monitor.prototype.scanMain = function (jqContent, layer) {
                var that = this;
                jqContent.find("a.rocket-ajah").each(function () {
                    (new LinkAction(jQuery(this), layer)).activate();
                });
            };
            Monitor.prototype.scan = function (jqContainer) {
                jqContainer.find("a.rocket-ajah").each(function () {
                    CommandAction.from($(this));
                });
            };
            return Monitor;
        }());
        cmd.Monitor = Monitor;
        var LinkAction = (function () {
            function LinkAction(jqA, layer) {
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
                this.layer.exec(url);
            };
            return LinkAction;
        }());
        var CommandAction = (function () {
            function CommandAction(jqElem) {
                this.jqElem = jqElem;
                var that = this;
                jqElem.click(function (e) {
                    that.handle();
                    return false;
                });
            }
            CommandAction.prototype.handle = function () {
                var url = this.jqElem.attr("href");
                var layer = cmd.Layer.findFrom(this.jqElem);
                if (layer === null) {
                    throw new Error("Command belongs to no layer.");
                }
                layer.exec(url);
            };
            CommandAction.from = function (jqElem) {
                var commandAction = jqElem.data("rocketCommandAction");
                if (commandAction)
                    return commandAction;
                commandAction = new CommandAction(jqElem);
                jqElem.data("rocketCommandAction", commandAction);
                return commandAction;
            };
            return CommandAction;
        }());
    })(cmd = rocket.cmd || (rocket.cmd = {}));
})(rocket || (rocket = {}));
var rocket;
(function (rocket) {
    var cmd;
    (function (cmd) {
        var Container = (function () {
            function Container(jqContainer) {
                this.jqContainer = jqContainer;
                this.layers = new Array();
                var layer = new Layer(this.jqContainer.find(".rocket-main-layer"), this.layers.length);
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
            Container.prototype.getMainLayer = function () {
                if (this.layers.length > 0) {
                    return this.layers[0];
                }
                throw new Error("MainLayer ");
            };
            return Container;
        }());
        cmd.Container = Container;
        var Layer = (function () {
            function Layer(jqContentGroup, level) {
                this.currentHistoryIndex = null;
                this.contexts = new Array();
                this.onNewContextCallbacks = new Array();
                this.onNewHistoryEntryCallbacks = new Array();
                this.historyUrls = new Array();
                this.jqContentGroup = jqContentGroup;
                this.level = level;
                jqContentGroup.addClass("rocket-layer");
                jqContentGroup.data("rocketLayer", this);
                var jqContext = jqContentGroup.children(".rocket-context");
                if (jqContext.length > 0) {
                    var context = new Context(jqContext, window.location.href, this);
                    this.addContext(context);
                    this.createHistoryEntry(context);
                }
            }
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
            Layer.prototype.createHistoryEntry = function (context) {
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
            Layer.prototype.exec = function (url, config) {
                if (config === void 0) { config = null; }
                var forceReload = false;
                var showLoadingContext = true;
                var doneCallback;
                if (config !== null) {
                    forceReload = config.forceReload === true;
                    showLoadingContext = config.showLoadingContext !== false;
                    doneCallback = config.done;
                }
                var context = this.getContextByUrl(url);
                if (context !== null) {
                    if (this.getCurrentContext() !== context) {
                        this.createHistoryEntry(context);
                    }
                    if (!forceReload) {
                        if (doneCallback) {
                            setTimeout(function () { doneCallback(new ExecResult(null, context)); }, 0);
                        }
                        return;
                    }
                }
                if (context === null && showLoadingContext) {
                    context = this.createContext(url);
                    this.createHistoryEntry(context);
                }
                if (context !== null) {
                    context.clear(true);
                }
                var that = this;
                $.ajax({
                    "url": url,
                    "dataType": "json"
                }).fail(function (data) {
                    context.applyErrorHtml(data.responseText);
                }).done(function (data) {
                    context.applyHtml(n2n.ajah.analyze(data));
                    n2n.ajah.update();
                    if (doneCallback) {
                        doneCallback(new ExecResult(null, context));
                    }
                });
            };
            Layer.prototype.createContext = function (url) {
                var jqContent = $("<div/>");
                this.jqContentGroup.append(jqContent);
                var context = new Context(jqContent, url, this);
                this.addContext(context);
                return context;
            };
            Layer.prototype.clear = function () {
                for (var i in this.contexts) {
                    this.contexts[i].close();
                }
            };
            Layer.prototype.dispose = function () {
                this.contexts = new Array();
                this.jqContentGroup.remove();
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
        var ExecResult = (function () {
            function ExecResult(order, context) {
            }
            return ExecResult;
        }());
        var Context = (function () {
            function Context(jqContext, url, layer) {
                this.jqContext = jqContext;
                this.url = url;
                this.layer = layer;
                this.onCloseCallbacks = new Array();
                jqContext.addClass("rocket-context");
                jqContext.data("rocketContext", this);
                this.hide();
            }
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
                //			var callback;
                //			while (undefined !== (callback = this.onShowCallbacks.shift())) {
                //				callback(this);
                //			}
            };
            Context.prototype.hide = function () {
                this.jqContext.hide();
            };
            Context.prototype.clear = function (loading) {
                if (loading === void 0) { loading = false; }
                this.jqContext.empty();
                this.jqContext.addClass("rocket-loading");
            };
            Context.prototype.applyHtml = function (html) {
                this.jqContext.removeClass("rocket-loading");
                this.jqContext.html(html);
            };
            Context.prototype.applyErrorHtml = function (html) {
                this.jqContext.removeClass("rocket-loading");
                var iframe = document.createElement('iframe');
                this.jqContext.append(iframe);
                iframe.contentWindow.document.open();
                iframe.contentWindow.document.write(html);
                iframe.contentWindow.document.close();
                $(iframe).css({ "width": "100%", "height": "100%" });
            };
            Context.prototype.onClose = function (onCloseCallback) {
                this.onCloseCallbacks.push(onCloseCallback);
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
        var Entry = (function () {
            function Entry() {
            }
            return Entry;
        }());
        cmd.Entry = Entry;
    })(cmd = rocket.cmd || (rocket.cmd = {}));
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
                pagination.draw(jqForm.children(".rocket-context-controls"));
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
                var headerHeight = this.jqHeader.children(".rocket-tool-panel").outerHeight();
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
    jQuery(document).ready(function ($) {
        var jqContainer = $("#rocket-content-container");
        var container = new rocket.cmd.Container(jqContainer);
        var monitor = new rocket.cmd.Monitor(container);
        monitor.scanMain($("#rocket-global-nav"), container.getMainLayer());
        monitor.scan(jqContainer);
        n2n.dispatch.registerCallback(function () {
            monitor.scan(jqContainer);
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
    });
    function contextOf(elem) {
        return rocket.cmd.Context.findFrom($(elem));
    }
    rocket.contextOf = contextOf;
    function handleErrorResponse(responseObject) {
        alert(JSON.stringify(responseObject));
        $("html").html(responseObject.responseText);
    }
    rocket.handleErrorResponse = handleErrorResponse;
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
            };
            Form.prototype.submit = function (formData) {
                var that = this;
                $.ajax({
                    "url": this.jqForm.attr("action"),
                    "type": "POST",
                    "data": formData,
                    "cache": false,
                    "processData": false,
                    "contentType": false,
                    "dataType": "json",
                    "success": function (data, textStatus, jqXHR) {
                        var html = n2n.ajah.analyze(data);
                        alert(html);
                        rocket.contextOf(that.jqForm.get(0)).applyHtml(html);
                        n2n.ajah.update();
                    },
                    "error": function (jqXHR, textStatus, errorThrown) {
                        //if fails     
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
