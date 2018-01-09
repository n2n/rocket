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
 */
var spec;
(function (spec) {
    var $ = jQuery;
    var EntryHeader = (function () {
        function EntryHeader(label, prependToElem) {
            if (prependToElem === void 0) { prependToElem = null; }
            this.elemUlControls = null;
            this.elemHeader = $("<div />", {
                "class": "rocket-entry-form-header"
            }).css({
                "position": "relative"
            });
            if (null !== prependToElem) {
                prependToElem.prepend(this.elemHeader);
            }
            this.elemLabel = $("<label />", {
                "text": label
            }).appendTo(this.elemHeader);
        }
        EntryHeader.prototype.setLabel = function (label) {
            this.elemLabel.text(label);
        };
        EntryHeader.prototype.addControl = function (elem) {
            if (null === this.elemUlControls) {
                this.elemUlControls = $("<ul />", {
                    "class": "rocket-simple-controls"
                }).appendTo(this.elemHeader);
            }
            if (!elem.is("li")) {
                elem = $("<li />").append(elem);
            }
            this.elemUlControls.append(elem);
        };
        return EntryHeader;
    }());
    spec.EntryHeader = EntryHeader;
})(spec || (spec = {}));
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
 */
var storage;
(function (storage) {
    var CookieStorage = (function () {
        function CookieStorage() {
        }
        CookieStorage.prototype.getValue = function (name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ')
                    c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0)
                    return c.substring(nameEQ.length, c.length);
            }
            return null;
        };
        CookieStorage.prototype.setValue = function (name, value, hours) {
            if (hours === void 0) { hours = null; }
            var expires, date;
            if (hours) {
                date = new Date();
                date.setTime(date.getTime() + (hours * 60 * 60 * 1000));
                expires = "; expires=" + date.toGMTString();
            }
            else {
                expires = "";
            }
            document.cookie = name + "=" + value + expires + "; path=/";
        };
        return CookieStorage;
    }());
    storage.CookieStorage = CookieStorage;
})(storage || (storage = {}));
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
 */
var ui;
(function (ui) {
    $ = jQuery;
    var MultiAdd = (function () {
        function MultiAdd(elemOpener, elemContent, alignment) {
            if (alignment === void 0) { alignment = MultiAdd.ALIGNMENT_RIGHT; }
            this.showContent = function () {
                this.elemContentContainer.show();
                var left = this.determineContentLeftPos();
                this.elemArrow.show().css({
                    "top": this.elemOpener.offset().top + (this.elemOpener.outerHeight() / 2)
                        - (this.elemArrow.outerHeight() / 2),
                    "left": left + 2
                });
                if (this.alignment === MultiAdd.ALIGNMENT_RIGHT) {
                    left += this.elemArrow.outerWidth() / 2;
                }
                else {
                    left -= this.elemArrow.outerWidth() / 2;
                }
                this.elemContentContainer.css({
                    "top": this.determineContentTopPos(),
                    "left": left
                });
                this.applyMouseLeave();
            };
            this.applyMouseLeave = function () {
                var that = this;
                this.resetMouseLeave();
                this.elemContentContainer.on("mouseenter.multi-add", function () {
                    that.applyMouseLeave();
                }).on("mouseleave.multi-add", function () {
                    that.mouseLeaveTimeout = setTimeout(function () {
                        that.hideContent();
                    }, 1000);
                }).on("click.multi-add", function (e) {
                    e.stopPropagation();
                });
                this.elemWindow.on("keyup.multi-add", function (e) {
                    if (e.which === 27) {
                        //escape	
                        that.hideContent();
                    }
                    ;
                }).on("click.multi-add", function () {
                    that.hideContent();
                });
            };
            this.hideContent = function () {
                this.elemContentContainer.hide();
                this.elemArrow.hide();
                this.resetMouseLeave();
            };
            this.resetMouseLeave = function () {
                if (null !== this.mouseLeaveTimeout) {
                    clearTimeout(this.mouseLeaveTimeout);
                    this.mouseLeaveTimeout = null;
                }
                this.elemContentContainer.off("mouseenter.multi-add mouseleave.multi-add click.multi-add");
                this.elemWindow.off("keyup.multi-add click.multi-add");
            };
            this.elemOpener = elemOpener;
            this.elemContent = elemContent;
            this.elemContentContainer = jQuery("<div />", {
                "class": "rocket-multi-add-content-container"
            }).css({
                "position": "fixed",
                "zIndex": 1000
            }).hide();
            this.elemWindow = jQuery(window);
            this.alignment = alignment;
            (function (that) {
                that.elemContentContainer.insertBefore(elemContent)
                    .append(elemContent);
                that.elemArrow = $("<span />").insertAfter(that.elemContentContainer).css({
                    "position": "fixed",
                    "background": "#818a91",
                    "transform": "rotate(45deg)",
                    "width": "15px",
                    "height": "15px"
                }).addClass("rocke-multi-add-arrow-left").hide();
                elemOpener.click(function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    that.showContent();
                });
                that.elemContentContainer.click(function () {
                    that.elemArrow.hide();
                });
            }).call(this, this);
        }
        MultiAdd.prototype.determineContentLeftPos = function () {
            if (this.alignment === MultiAdd.ALIGNMENT_RIGHT) {
                return this.elemOpener.offset().left + this.elemOpener.outerWidth();
            }
            return this.elemOpener.offset().left - this.elemContentContainer.outerWidth();
        };
        MultiAdd.prototype.determineContentTopPos = function () {
            return this.elemOpener.offset().top + this.elemOpener.outerHeight() / 2 -
                this.elemWindow.scrollTop() - (this.elemContentContainer.outerHeight() / 2);
        };
        MultiAdd.ALIGNMENT_LEFT = 'left';
        MultiAdd.ALIGNMENT_RIGHT = 'right';
        return MultiAdd;
    }());
    ui.MultiAdd = MultiAdd;
    var StackedContentContainer = (function () {
        function StackedContentContainer(contentStack, stackedContent) {
            this.contentStack = contentStack;
            this.stackedContent = stackedContent;
            this.elem = $("<div />", {
                "class": "rocket-content-container"
            });
            this.elemFooter = $("<div />", {
                "class": "rocket-page-controls"
            }).css({
                "height": $("#rocket-page-controls").innerHeight()
            });
            this.elemControls = $("<ul />").appendTo(this.elemFooter);
            (function (that) {
                that.elemClose = $("<a />", {
                    "href": "#",
                    "class": "rocket-stacked-content-close"
                }).append($("<i />", {
                    "class": "fa fa-times"
                })).appendTo(that.elem).click(function (e) {
                    e.preventDefault();
                    that.close();
                });
                that.elem.append(stackedContent.getElemContent());
                stackedContent.setup(that);
            }).call(this, this);
        }
        StackedContentContainer.prototype.addControl = function (iconClassName, label, callback) {
            $("<button />", {
                "class": "rocket-control",
                "text": label
            }).click(function (e) {
                e.preventDefault();
                callback();
            }).appendTo($("<li />").appendTo(this.elemControls));
        };
        StackedContentContainer.prototype.close = function () {
            var that = this;
            that.stackedContent.onClose();
            this.contentStack.popStackedContent(this, function () {
                that.stackedContent.onClosed();
                that.elem.detach();
                that.elemFooter.detach();
            });
        };
        StackedContentContainer.prototype.getElem = function () {
            return this.elem;
        };
        StackedContentContainer.prototype.getElemFooter = function () {
            return this.elemFooter;
        };
        return StackedContentContainer;
    }());
    ui.StackedContentContainer = StackedContentContainer;
    var ContentStack = (function () {
        function ContentStack(contentContainer) {
            this.stackedContentContainers = [];
            this.animationSpeed = 175;
            this.targetLeft = 215;
            this.elemContentContainer = contentContainer;
            (function (that) {
                $(window).on("keyup.contentStack", function (e) {
                    if (e.which != 27)
                        return;
                    that.closeCurrent();
                });
            }).call(this, this);
        }
        ContentStack.prototype.closeCurrent = function () {
            if (this.stackedContentContainers.length === 0)
                return;
            this.stackedContentContainers.pop().close();
        };
        ContentStack.prototype.addStackedContent = function (stackedContent) {
            var stackedContentContainer = new StackedContentContainer(this, stackedContent), zIndex = this.stackedContentContainers.length + 100, outerWidth = this.elemContentContainer.outerWidth();
            stackedContentContainer.getElem().appendTo(this.elemContentContainer).css({
                "zIndex": zIndex,
                "left": this.targetLeft + outerWidth,
                "right": -outerWidth
            }).animate({
                "left": this.targetLeft,
                "right": 0
            }, this.animationSpeed, function () {
                stackedContent.onAnimationComplete();
            });
            stackedContentContainer.getElemFooter().appendTo(this.elemContentContainer).css({
                "zIndex": zIndex,
                "left": this.targetLeft + outerWidth,
                "right": -outerWidth
            }).animate({
                "left": this.targetLeft,
                "right": 0
            }, this.animationSpeed);
            this.stackedContentContainers.push(stackedContentContainer);
        };
        ContentStack.prototype.popStackedContent = function (stackedContentContainer, callback) {
            var that = this, outerWidth = this.elemContentContainer.outerWidth();
            stackedContentContainer.getElem().animate({
                "left": outerWidth + this.targetLeft,
                "right": -outerWidth
            }, this.animationSpeed, function () {
                that.stackedContentContainers.splice(that.stackedContentContainers.indexOf(stackedContentContainer), 1);
                callback();
            });
            stackedContentContainer.getElemFooter().animate({
                "left": outerWidth + this.targetLeft,
                "right": -outerWidth
            }, this.animationSpeed);
        };
        return ContentStack;
    }());
    ui.ContentStack = ContentStack;
    var AdditionalContentEntry = (function () {
        function AdditionalContentEntry(additionalContent, title, elemContent) {
            this.additionalContent = additionalContent;
            this.elemContent = elemContent;
            (function (that) {
                that.elemActivator = $("<a />", {
                    "text": title,
                    "href": "#"
                }).click(function (e) {
                    e.preventDefault();
                    additionalContent.activate(that);
                });
            }).call(this, this);
        }
        AdditionalContentEntry.prototype.activate = function () {
            this.elemActivator.addClass("active");
            this.elemContent.show();
        };
        AdditionalContentEntry.prototype.deactivate = function () {
            this.elemActivator.removeClass("active");
            this.elemContent.hide();
        };
        AdditionalContentEntry.prototype.getElemActivator = function () {
            return this.elemActivator;
        };
        AdditionalContentEntry.prototype.getElemContent = function () {
            return this.elemContent;
        };
        AdditionalContentEntry.create = function (additionalContent, elemDiv) {
            var elemH2 = elemDiv.children("h2:first"), title = elemH2.text(), elemContent = elemH2.remove();
            return new AdditionalContentEntry(additionalContent, title, elemContent);
        };
        return AdditionalContentEntry;
    }());
    ui.AdditionalContentEntry = AdditionalContentEntry;
    var AdditionalContent = (function () {
        function AdditionalContent(elemContentContainer, elem) {
            if (elem === void 0) { elem = null; }
            this.activeEntry = null;
            this.elemContent = elemContentContainer.children(".rocket-content");
            if (null === elem) {
                this.elem = $("<div />", {
                    "id": "rocket-additional"
                }).insertAfter(this.elemContent);
                this.elemContent.addClass("rocket-contains-additional");
            }
            else {
                this.elem = elem;
            }
            this.elem.addClass("rocket-grouped-panels");
            rocketTs.markAsInitialized(".rocket-grouped-panels", this.elem);
            this.elemUlTabs = $("<ul />", {
                "class": "rocket-grouped-panels-navigation"
            }).appendTo(this.elem);
            this.elemContentContainer = $("<div />", {
                "class": "rocket-additional-container"
            }).appendTo(this.elem);
            (function (that) {
                if (null !== elem) {
                    elem.children().each(function () {
                        that.appendEntry(AdditionalContentEntry.create(that, $(this)));
                    });
                }
            }).call(this, this);
        }
        AdditionalContent.prototype.getElemContent = function () {
            return this.elemContent;
        };
        AdditionalContent.prototype.activate = function (entry) {
            if (null !== this.activeEntry) {
                this.activeEntry.deactivate();
            }
            this.activeEntry = entry;
            entry.activate();
        };
        AdditionalContent.prototype.appendEntry = function (entry) {
            this.elemUlTabs.append($("<li />").append(entry.getElemActivator()));
            this.elemContentContainer.append(entry.getElemContent());
            if (null === this.activeEntry) {
                this.activate(entry);
            }
        };
        AdditionalContent.prototype.createAndPrependEntry = function (title, elemContent, activate) {
            if (activate === void 0) { activate = true; }
            var entry = new AdditionalContentEntry(this, title, elemContent);
            this.appendEntry(entry);
            if (activate) {
                entry.activate();
            }
        };
        return AdditionalContent;
    }());
    ui.AdditionalContent = AdditionalContent;
    var UnsavedFormManager = (function () {
        function UnsavedFormManager() {
            this.listining = false;
            this.activate = function (text) {
                if ((!this.listening))
                    return false;
                this.elemWindow.off('beforeunload.unsavedFormManager').on('beforeunload.unsavedFormManager', function () {
                    return text || "Your changes won't be saved.";
                });
                return true;
            };
            this.deactivate = function () {
                this.elemWindow.off('beforeunload.unsavedFormManager');
            };
            this.elemWindow = $(window);
            (function (that) {
                that.elemWindow.load(function () {
                    that.listining = true;
                });
            }).call(this, this);
        }
        UnsavedFormManager.prototype.registerForm = function (elemForm) {
            if (!elemForm.is("form"))
                throw "Not a Form";
            var that = this;
            elemForm.on('submit.unsavedFormManager', function () {
                that.deactivate();
            }).on("keydown.unsavedFormManager change.unsavedFormManager", function () {
                if (that.activate(elemForm.data("text-unload"))) {
                    elemForm.off("keydown.unsavedFormManager change.unsavedFormManager");
                }
            });
        };
        return UnsavedFormManager;
    }());
    ui.UnsavedFormManager = UnsavedFormManager;
    var EntryFormCommand = (function () {
        function EntryFormCommand(text, callback, iconClassName) {
            if (callback === void 0) { callback = null; }
            if (iconClassName === void 0) { iconClassName = null; }
            this.elemContainer = $("<div />", {
                "class": "rocket-entry-form-command"
            });
            this.elemButton = $("<button />", {
                "text": text,
                "class": "rocket-control rocket-control-full"
            }).click(function (e) {
                e.preventDefault();
                callback();
            }).appendTo(this.elemContainer);
            if (null !== iconClassName) {
                this.elemButton.prepend($("<i />", {
                    "class": iconClassName
                }));
            }
        }
        EntryFormCommand.prototype.getElemContainer = function () {
            return this.elemContainer;
        };
        EntryFormCommand.prototype.getElemButton = function () {
            return this.elemButton;
        };
        EntryFormCommand.prototype.setLoading = function (loading) {
            if (loading) {
                this.elemButton.addClass("rocket-loading");
            }
            else {
                this.elemButton.removeClass("rocket-loading");
            }
        };
        return EntryFormCommand;
    }());
    ui.EntryFormCommand = EntryFormCommand;
})(ui || (ui = {}));
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
 */
var ui;
(function (ui) {
    var Dialog = (function () {
        function Dialog(msg, dialogType) {
            if (dialogType === void 0) { dialogType = 'warning'; }
            this.buttons = [];
            this.msg = msg;
            this.dialogType = dialogType;
        }
        Dialog.prototype.addButton = function (label, callback) {
            this.buttons.push({
                label: label,
                callback: callback
            });
        };
        Dialog.prototype.getMsg = function () {
            return this.msg;
        };
        Dialog.prototype.getDialogType = function () {
            return this.dialogType;
        };
        Dialog.prototype.getButtons = function () {
            return this.buttons;
        };
        return Dialog;
    }());
    ui.Dialog = Dialog;
    var StressWindow = (function () {
        function StressWindow() {
            this.close = function () {
                this.elemBackground.detach();
                this.elemDialog.detach();
                $(window).off('keydown.dialog');
            };
            this.elemBackground = $("<div />", {
                "class": "rocket-dialog-background"
            }).css({
                "position": "fixed",
                "height": "100%",
                "width": "100%",
                "top": 0,
                "left": 0,
                "z-index": 998,
                "opacity": 0
            });
            this.elemDialog = $("<div />").css({
                "position": "fixed",
                "z-index": 999
            });
            this.elemMessage = $("<p />", {
                "class": "rocket-dialog-message"
            }).appendTo(this.elemDialog);
            this.elemControls = $("<ul/>", {
                "class": "rocket-controls rocket-dialog-controls"
            }).appendTo(this.elemDialog);
        }
        StressWindow.prototype.open = function (dialog) {
            var that = this, elemBody = $("body"), elemWindow = $(window);
            this.elemDialog.removeClass()
                .addClass("rocket-dialog-" + dialog.getDialogType() + " rocket-dialog");
            this.elemMessage.empty().text(dialog.getMsg());
            this.initButtons(dialog);
            elemBody.append(this.elemBackground).append(this.elemDialog);
            //Position the dialog 
            this.elemDialog.css({
                "left": (elemWindow.width() - this.elemDialog.outerWidth(true)) / 2,
                "top": (elemWindow.height() - this.elemDialog.outerHeight(true)) / 3
            }).hide();
            this.elemBackground.show().animate({
                opacity: 0.7
            }, 151, function () {
                that.elemDialog.show();
            });
            elemWindow.on('keydown.dialog', function (event) {
                var keyCode = (window.event) ? event.keyCode : event.which;
                if (keyCode == 13) {
                    //Enter
                    that.elemConfirm.click();
                    $(window).off('keydown.dialog');
                }
                else if (keyCode == 27) {
                    //Esc
                    that.close();
                }
            });
        };
        StressWindow.prototype.initButtons = function (dialog) {
            var that = this;
            this.elemConfirm = null;
            this.elemControls.empty();
            dialog.getButtons().forEach(function (button) {
                var elemA = $("<a>", {
                    "href": "#"
                }).addClass("rocket-dialog-control rocket-control").click(function (e) {
                    e.preventDefault();
                    button['callback'](e);
                    that.close();
                }).text(button['label']);
                if (that.elemConfirm == null) {
                    that.elemConfirm = elemA;
                }
                that.elemControls.append($("<li/>").append(elemA));
            });
        };
        StressWindow.prototype.removeCurrentFocus = function () {
            //remove focus from all other to ensure that the submit button isn't fired twice
            $("<input/>", {
                "type": "text",
                "name": "remove-focus"
            }).appendTo($("body")).focus().remove();
        };
        return StressWindow;
    }());
    ui.StressWindow = StressWindow;
})(ui || (ui = {}));
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
 */
var storage;
(function (storage_1) {
    var WebStorage = (function () {
        function WebStorage(storageKey, storage) {
            if (storage === void 0) { storage = null; }
            this.data = {};
            this.baseStorageKey = storageKey;
            this.storage = storage;
            if (null !== storage) {
                var dataString = this.storage.getItem(storageKey);
                if (null !== dataString) {
                    this.data = $.parseJSON(dataString);
                }
            }
        }
        WebStorage.prototype.isAvailable = function () {
            return null !== this.storage;
        };
        WebStorage.prototype.getData = function (key) {
            if (!this.isAvailable() || !this.data.hasOwnProperty(key))
                return null;
            return this.data[key];
        };
        WebStorage.prototype.hasData = function (key) {
            if (!this.isAvailable())
                return false;
            return this.data.hasOwnProperty(key);
        };
        WebStorage.prototype.setData = function (key, data) {
            if (!this.isAvailable())
                return;
            this.data[key] = data;
            this.storage.setItem(this.baseStorageKey, JSON.stringify(this.data));
        };
        return WebStorage;
    }());
    storage_1.WebStorage = WebStorage;
})(storage || (storage = {}));
/// <reference path="ui/common.ts" />
/// <reference path="ui/dialog.ts" />
/// <reference path="storage/cookie.ts" />
/// <reference path="storage/web.ts" />
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
 */
var RocketTs = (function () {
    function RocketTs() {
        this.contentStack = null;
        this.uiInitFunctions = {};
        this.readyCallbacks = [];
        this.additionalContent = null;
        this.stressWindow = null;
        this.unsavedFormManager = null;
        this.cookieStorage = null;
        this.localStorage = null;
        this.finalEventTimers = {};
        this.initialized = false;
        var refreshPath = $("body").data("refresh-path");
        this.stressWindow = new ui.StressWindow();
        this.unsavedFormManager = new ui.UnsavedFormManager();
        this.cookieStorage = new storage.CookieStorage();
        this.localStorage = new storage.WebStorage(refreshPath, localStorage);
        (function (that) {
            jQuery(document).ready(function ($) {
                that.confirmableManager = new ui.ConfirmableManager();
                that.onDomReady($);
                var refresh = function () {
                    setTimeout(function () {
                        $.get(refreshPath);
                        refresh();
                    }, 300000);
                };
                refresh();
                $(".rocket-paging select, select.rocket-paging").change(function () {
                    window.location = this.value;
                });
                if (typeof $.fn.responsiveTable === 'function') {
                    $(".rocket-list").responsiveTable();
                }
                $(".rocket-unsaved-check-form").each(function () {
                    that.unsavedFormManager.registerForm($(this));
                });
                $(document).ajaxError(function (event, jqXhr, settings, thrownError) {
                    if (jqXhr.status === 0)
                        return;
                    var w = window.open(settings.url);
                    var newDoc = w.document.open("text/html", "replace");
                    newDoc.write(jqXhr.responseText);
                    newDoc.close();
                });
            });
        }).call(this, this);
    }
    RocketTs.prototype.resetForm = function (elem) {
        elem.find("input, textarea, select").each(function () {
            var jqElem = jQuery(this);
            if (this.defaultValue != undefined) {
                this.value = this.defaultValue;
            }
        });
    };
    RocketTs.prototype.onDomReady = function ($) {
        var that = this;
        this.elemContentContainer = $("#rocket-content-container");
        this.contentStack = new ui.ContentStack(this.elemContentContainer);
        var elemAdditional = $("#rocket-additional");
        if (elemAdditional.length > 0) {
            this.additionalContent = new ui.AdditionalContent(this.elemContentContainer, elemAdditional);
        }
        n2n.dispatch.registerCallback(function () {
            $.each(that.uiInitFunctions, function (selector, initFunction) {
                that.runInitFunction(selector, initFunction);
            });
        });
        this.readyCallbacks.forEach(function (callback) {
            callback($);
        });
        that.initialized = true;
    };
    RocketTs.prototype.ready = function (callback) {
        if (this.initialized) {
            callback(jQuery);
            return;
        }
        this.readyCallbacks.push(callback);
    };
    RocketTs.prototype.getElemContentContainer = function () {
        return this.elemContentContainer;
    };
    RocketTs.prototype.getContentStack = function () {
        return this.contentStack;
    };
    RocketTs.prototype.getLocalStorage = function () {
        return this.localStorage;
    };
    RocketTs.prototype.getOrCreateAdditionalContent = function () {
        if (null === this.additionalContent) {
            this.additionalContent = new ui.AdditionalContent(this.elemContentContainer);
        }
        return this.additionalContent;
    };
    RocketTs.prototype.registerUiInitFunction = function (selector, initFunction) {
        this.uiInitFunctions[selector] = initFunction;
        this.runInitFunction(selector, initFunction);
    };
    RocketTs.prototype.runInitFunction = function (selector, initFunction) {
        var that = this;
        jQuery(selector).each(function () {
            var elem = jQuery(this);
            if (that.isInitialized(selector, elem))
                return;
            that.markAsInitialized(selector, elem);
            initFunction(elem);
        });
    };
    RocketTs.prototype.isInitialized = function (selector, elem) {
        return elem.data("initialized" + selector);
    };
    RocketTs.prototype.markAsInitialized = function (selector, elem) {
        return elem.data("initialized" + selector, true);
    };
    RocketTs.prototype.analyzeAjahData = function (data) {
        return jQuery(jQuery.parseHTML(n2n.dispatch.analyze(data)));
    };
    RocketTs.prototype.updateUi = function () {
        n2n.dispatch.update();
    };
    RocketTs.prototype.showDialog = function (dialog) {
        this.stressWindow.open(dialog);
    };
    RocketTs.prototype.registerForm = function (form) {
        this.unsavedFormManager.registerForm(form);
    };
    RocketTs.prototype.setCookie = function (name, value, hours) {
        if (hours === void 0) { hours = null; }
        this.cookieStorage.setValue(name, value, hours);
    };
    RocketTs.prototype.getCookie = function (name) {
        return this.cookieStorage.getValue(name);
    };
    ;
    RocketTs.prototype.waitForFinalEvent = function (callback, milliSeconds, uniqueId) {
        if (this.finalEventTimers[uniqueId]) {
            clearTimeout(this.finalEventTimers[uniqueId]);
        }
        this.finalEventTimers[uniqueId] = setTimeout(callback, milliSeconds);
    };
    ;
    RocketTs.prototype.createLoadingElem = function () {
        return $("<div />", {
            "class": "rocket-control-group rocket-loading"
        }).css({
            "text-align": "center"
        });
    };
    RocketTs.prototype.objectify = function (obj) {
        if ($.isPlainObject(obj))
            return obj;
        if ($.isArray(obj)) {
            var tmpObj = {};
            $.each(obj, function (key, value) {
                tmpObj[key] = value;
            });
            return tmpObj;
        }
        return {};
    };
    RocketTs.prototype.creatControlElem = function (text, callback, iconClassName) {
        if (callback === void 0) { callback = null; }
        if (iconClassName === void 0) { iconClassName = null; }
        var aAttrs = {
            "href": "#",
            "class": "rocket-control"
        };
        if (null !== iconClassName) {
            aAttrs["title"] = text;
        }
        else {
            aAttrs["text"] = text;
        }
        var elemA = jQuery("<a />", aAttrs);
        if (null !== iconClassName) {
            elemA.append(jQuery("<i />", {
                "class": iconClassName
            }));
        }
        if (null !== callback) {
            elemA.click(function (e) {
                e.preventDefault();
                callback();
            });
        }
        return elemA;
    };
    RocketTs.prototype.getConfirmableManager = function () {
        return this.confirmableManager;
    };
    return RocketTs;
}());
var rocketTs = new RocketTs();
/// <reference path="..\..\rocket.ts" />
/// <reference path="..\common.ts" />
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
 */
var spec;
(function (spec) {
    var edit;
    (function (edit) {
        $ = jQuery;
        var ToManyAdd = (function () {
            function ToManyAdd(toMany, toManyEntryForm) {
                if (toManyEntryForm === void 0) { toManyEntryForm = null; }
                this.toManyEntryForm = null;
                this.toMany = toMany;
                this.elemDiv = $("<div />", {
                    "class": "rocket-entry-form-command"
                });
                this.toManyEntryForm = toManyEntryForm;
                (function (that) {
                    that.elemButton = $("<button />", {
                        "text": toMany.getAddItemLabel(),
                        "class": "rocket-control rocket-control-full"
                    }).prepend($("<i />", {
                        "class": "fa fa-plus"
                    })).click(function (e) {
                        e.preventDefault();
                        if (!toMany.hasTypes()) {
                            that.requestNewEntryForm();
                            return;
                        }
                        if (that.elemButton.hasClass("rocket-command-insert-open")) {
                            that.elemUlTypes.remove();
                            that.elemButton.removeClass("rocket-command-insert-open");
                            return;
                        }
                        that.elemUlTypes = $("<ul />", {
                            "class": "rocket-dd-menu-open"
                        }).insertAfter(that.elemButton);
                        that.elemButton.addClass("rocket-command-insert-open");
                        $.each(that.toMany.getTypes(), function (typeId, label) {
                            $("<li />").append($("<a />", {
                                "text": label
                            }).click(function () {
                                that.requestNewEntryForm(typeId);
                                that.elemButton.removeClass("rocket-command-insert-open");
                                that.elemUlTypes.remove();
                            })).appendTo(that.elemUlTypes);
                        });
                    }).appendTo(that.elemDiv);
                    toMany.getElem().on('numEntriesChanged.toMany', function () {
                        that.elemButton.prop("disabled", !toMany.areMoreEntriesAllowed());
                    }).on('loading.toMany', function () {
                        that.elemButton.prop("disabled", true);
                        that.elemButton.addClass("rocket-loading");
                    }).on('loadingComplete.toMany', function () {
                        that.elemButton.prop("disabled", false);
                        that.elemButton.removeClass("rocket-loading");
                    });
                }).call(this, this);
            }
            ToManyAdd.prototype.requestNewEntryForm = function (typeId) {
                if (typeId === void 0) { typeId = null; }
                var that = this;
                this.toMany.loading();
                this.toMany.requestNewEntryForm(function (toManyEntryForm) {
                    that.toMany.loadingComplete();
                    that.insertNewToManyEntryForm(toManyEntryForm);
                    rocketTs.updateUi();
                }, typeId);
            };
            ToManyAdd.prototype.insertNewToManyEntryForm = function (toManyEntryForm) {
                if (null === this.toManyEntryForm) {
                    this.toMany.getToManyEmbedded().addEntryForm(toManyEntryForm);
                    this.elemDiv.trigger("numEntriesChanged.toMany");
                    return;
                }
                toManyEntryForm.getElemLi().insertBefore(this.toManyEntryForm.getElemLi());
                this.toMany.getToManyEmbedded().updateOrderIndizes();
                this.elemDiv.trigger("numEntriesChanged.toMany");
            };
            ToManyAdd.prototype.getElemDiv = function () {
                return this.elemDiv;
            };
            return ToManyAdd;
        }());
        var ToManyEntryForm = (function () {
            function ToManyEntryForm(toMany, elemLi, addAllowed, headerLabel) {
                this.toManyAdd = null;
                this.elemLi = elemLi;
                this.elemLi.data("to-many-entry-form", this);
                this.elemContentContainer = $("<div />", {
                    "class": "rocket-embedded"
                }).append(elemLi.children()).appendTo(this.elemLi);
                this.entryHeader = new spec.EntryHeader(headerLabel, this.elemContentContainer);
                this.elemInputOrderIndex = this.elemContentContainer.children(".rocket-to-many-order-index").hide();
                if (addAllowed) {
                    this.toManyAdd = new ToManyAdd(toMany, this);
                    this.elemLi.prepend(this.toManyAdd.getElemDiv());
                }
                (function (that) {
                    that.elemUp = $("<li />").append(rocketTs.creatControlElem(toMany.getMoveUpLabel(), function () {
                        var elemLiPrev = elemLi.prev();
                        if (elemLiPrev.length === 0)
                            return;
                        that.ckHack(function () {
                            elemLi.insertBefore(elemLiPrev);
                        });
                        toMany.getToManyEmbedded().updateOrderIndizes();
                    }, "fa fa-arrow-up"));
                    that.entryHeader.addControl(that.elemUp);
                    that.elemDown = $("<li />").append(rocketTs.creatControlElem(toMany.getMoveDownLabel(), function () {
                        var elemLiNext = elemLi.next();
                        if (elemLiNext.length === 0)
                            return;
                        that.ckHack(function () {
                            elemLi.insertAfter(elemLiNext);
                        });
                        toMany.getToManyEmbedded().updateOrderIndizes();
                    }, "fa fa-arrow-down"));
                    that.entryHeader.addControl(that.elemDown);
                    that.elemRemove = rocketTs.creatControlElem(elemLi.data("remove-item-label") || toMany.getRemoveItemLabel(), function () {
                        elemLi.remove();
                        toMany.getElem().trigger("numEntriesChanged.toMany");
                        toMany.getToManyEmbedded().updateOrderIndizes();
                    }, "fa fa-times").appendTo($("<li />"));
                    that.entryHeader.addControl(that.elemRemove);
                    toMany.getElem().on("numEntriesChanged.toMany", function () {
                        if (toMany.areLessEntriesAllowed()) {
                            that.elemRemove.show();
                        }
                        else {
                            that.elemRemove.hide();
                        }
                    });
                }).call(this, this);
            }
            ToManyEntryForm.prototype.ckHack = function (callback) {
                if (typeof Wysiwyg === 'undefined') {
                    callback();
                    return;
                }
                Wysiwyg.ckHack(this.elemLi, function () {
                    callback();
                });
            };
            ToManyEntryForm.prototype.setHeaderLabel = function (headerLabel) {
                this.entryHeader.setLabel(headerLabel);
            };
            ToManyEntryForm.prototype.isOrderable = function () {
                return this.elemInputOrderIndex.length > 0;
            };
            ToManyEntryForm.prototype.setOrderIndex = function (orderIndex) {
                if (!this.isOrderable())
                    return;
                this.elemInputOrderIndex.val(orderIndex).change();
            };
            ToManyEntryForm.prototype.enableUp = function () {
                this.elemUp.show();
            };
            ToManyEntryForm.prototype.disableUp = function () {
                this.elemUp.hide();
            };
            ToManyEntryForm.prototype.enableDown = function () {
                this.elemDown.show();
            };
            ToManyEntryForm.prototype.disableDown = function () {
                this.elemDown.hide();
            };
            ToManyEntryForm.prototype.getOrderIndex = function () {
                if (!this.isOrderable())
                    return 0;
                return parseInt(this.elemInputOrderIndex.val());
            };
            ToManyEntryForm.prototype.getElemInputOrderIndex = function () {
                return this.elemInputOrderIndex;
            };
            ToManyEntryForm.prototype.getElemLi = function () {
                return this.elemLi;
            };
            return ToManyEntryForm;
        }());
        edit.ToManyEntryForm = ToManyEntryForm;
        var ToManyEmbedded = (function () {
            function ToManyEmbedded(toMany) {
                this.toManyAdd = null;
                this.elemContainer = $("<div />");
                this.elem = $("<div />").appendTo(this.elemContainer);
                this.toMany = toMany;
            }
            ToManyEmbedded.prototype.activate = function (enableAdd) {
                if (enableAdd) {
                    this.toManyAdd = new ToManyAdd(this.toMany);
                    this.toManyAdd.getElemDiv().appendTo(this.elemContainer);
                }
                this.elemContainer.appendTo(this.toMany.getElem());
                this.updateOrderIndizes();
            };
            ToManyEmbedded.prototype.getElemUl = function () {
                return this.elem;
            };
            ToManyEmbedded.prototype.getNumNewEntryForms = function () {
                return this.elem.children(".rocket-new").length;
            };
            ToManyEmbedded.prototype.addEntryForm = function (toManyEntryForm, inspectOrder, updateOrderIndizes) {
                if (inspectOrder === void 0) { inspectOrder = false; }
                if (updateOrderIndizes === void 0) { updateOrderIndizes = true; }
                if (!inspectOrder) {
                    this.elem.append(toManyEntryForm.getElemLi());
                    if (updateOrderIndizes) {
                        this.updateOrderIndizes();
                    }
                    return;
                }
                var orderIndex = toManyEntryForm.getOrderIndex(), added = false;
                this.elem.children().each(function (index) {
                    var tmpToManyEntryForm = $(this).data("to-many-entry-form");
                    if (tmpToManyEntryForm.getOrderIndex() <= orderIndex)
                        return;
                    toManyEntryForm.getElemLi().insertBefore(tmpToManyEntryForm.getElemLi());
                    added = true;
                    return false;
                });
                if (!added) {
                    this.elem.append(toManyEntryForm.getElemLi());
                }
                if (updateOrderIndizes) {
                    this.updateOrderIndizes();
                }
            };
            ToManyEmbedded.prototype.updateOrderIndizes = function () {
                var children = this.elem.children();
                children.each(function (index) {
                    var toManyEntryForm = $(this).data('to-many-entry-form');
                    toManyEntryForm.setOrderIndex(index);
                    if (index === 0) {
                        toManyEntryForm.disableUp();
                    }
                    else {
                        toManyEntryForm.enableUp();
                    }
                    if (index === (children.length - 1)) {
                        toManyEntryForm.disableDown();
                    }
                    else {
                        toManyEntryForm.enableDown();
                    }
                });
            };
            ToManyEmbedded.prototype.getEntryForms = function () {
                var entryForms = [];
                this.elem.children().each(function (index) {
                    entryForms.push($(this).data('to-many-entry-form'));
                });
                return entryForms;
            };
            ToManyEmbedded.prototype.getNumEntryForms = function () {
                return this.elem.children().length;
            };
            return ToManyEmbedded;
        }());
        var ToManySelectorStackedContent = (function () {
            function ToManySelectorStackedContent(toManySelector, elemContent) {
                this.stackedContentContainer = null;
                this.overviewTools = null;
                this.observeOnContentLoad = false;
                this.elemContent = $("<div />", {
                    "class": "rocket-to-one-stacked-content"
                }).append($("<div />", {
                    "class": "rocket-panel"
                }).append(elemContent));
                this.toManySelector = toManySelector;
                (function (that) {
                    elemContent.on('overview.contentLoaded', function (e, overviewTools) {
                        that.overviewTools = overviewTools;
                        that.overviewTools.getFixedHeader().setApplyDefaultFixedContainer(false);
                        if (null !== that.stackedContentContainer) {
                            overviewTools.setElemFixedContainer(that.stackedContentContainer.getElem(), that.observeOnContentLoad);
                        }
                    });
                }).call(this, this);
            }
            ToManySelectorStackedContent.prototype.getTitle = function () {
                return "test";
            };
            ;
            ToManySelectorStackedContent.prototype.getElemContent = function () {
                return this.elemContent;
            };
            ToManySelectorStackedContent.prototype.setup = function (stackedContentContainer) {
                var that = this;
                this.stackedContentContainer = stackedContentContainer;
                stackedContentContainer.addControl("fa fa-save", this.toManySelector.getAddLabel(), function () {
                    that.toManySelector.addByIdentityStrings(that.overviewTools.getOverviewContent().getSelectedIdentityStrings());
                    stackedContentContainer.close();
                });
                if (null !== this.overviewTools) {
                    this.overviewTools.setElemFixedContainer(stackedContentContainer.getElem(), false);
                }
            };
            ToManySelectorStackedContent.prototype.onAnimationComplete = function () {
                if (null !== this.overviewTools) {
                    this.overviewTools.getFixedHeader().startObserving();
                }
                else {
                    this.observeOnContentLoad = true;
                }
            };
            ToManySelectorStackedContent.prototype.onClose = function () {
                this.overviewTools.getFixedHeader().reset();
                this.overviewTools.getFixedHeader().stopObserving();
                this.overviewTools.getOverviewContent().removeSelection();
            };
            ToManySelectorStackedContent.prototype.onClosed = function () {
                this.stackedContentContainer.getElem().scrollTop(0);
                this.observeOnContentLoad = false;
            };
            return ToManySelectorStackedContent;
        }());
        var ToManySelectorEntry = (function () {
            function ToManySelectorEntry(toManySelector, elemLi, id) {
                if (id === void 0) { id = null; }
                this.toManySelector = toManySelector;
                this.elemInput = elemLi.find("input:first").hide();
                if (null !== id) {
                    this.elemInput.val(id);
                }
                this.elemLi = elemLi.addClass("rocket-to-many-" + this.elemInput.val());
                this.elemLabel = $("<span />", {
                    "text": toManySelector.getIdentityString(this.elemInput.val())
                });
                (function (that) {
                    that.elemLabelContainer = $("<div />", {
                        "class": "rocket-relation-label-container"
                    }).append(that.elemLabel).insertAfter(that.elemInput);
                    rocketTs.creatControlElem(toManySelector.getRemoveItemLabel(), function () {
                        elemLi.remove();
                    }, "fa fa-times").appendTo(that.elemLabelContainer);
                }).call(this, this);
            }
            ToManySelectorEntry.prototype.getElemInput = function () {
                return this.elemInput;
            };
            return ToManySelectorEntry;
        }());
        var ToManySelector = (function () {
            function ToManySelector(elem, removeItemLabel) {
                this.elemUl = null;
                this.elemSelect = null;
                this.elemReset = null;
                this.preloadedStackedContent = null;
                this.elem = elem;
                this.overviewToolsUrl = elem.data("overview-tools-url");
                this.identityStrings = rocketTs.objectify(elem.data("identity-strings"));
                this.originalIdReps = elem.data("original-id-reps");
                this.addLabel = elem.data("add-label");
                this.resetLabel = elem.data("reset-label");
                this.clearLabel = elem.data("clear-label");
                this.genericEntryLabel = elem.data("generic-entry-label");
                this.removeItemLabel = removeItemLabel;
                this.basePropertyName = elem.data('base-property-name');
                this.setup(elem.children("ul:first").addClass("rocket-to-many-selected-entries"));
                this.initControls();
            }
            ToManySelector.prototype.getElem = function () {
                return this.elem;
            };
            ToManySelector.prototype.getAddLabel = function () {
                return this.addLabel;
            };
            ToManySelector.prototype.getRemoveItemLabel = function () {
                return this.removeItemLabel;
            };
            ToManySelector.prototype.setup = function (elemUl) {
                var that = this;
                this.elemLiNew = elemUl.children(".rocket-new-entry:last").detach();
                this.elemUlClone = elemUl.clone();
                this.elemUl = elemUl;
                this.elemUl.prependTo(this.elem);
                this.elemUl.children().each(function () {
                    new ToManySelectorEntry(that, $(this));
                });
            };
            ToManySelector.prototype.initControls = function () {
                var that = this, elemControls = $("<ul />", {
                    "class": "rocket-to-many-controls"
                }).insertAfter(this.elemUl);
                rocketTs.creatControlElem(this.addLabel, function () {
                    rocketTs.getContentStack().addStackedContent(that.preloadedStackedContent);
                    rocketTs.updateUi();
                }).appendTo($("<li />").appendTo(elemControls));
                rocketTs.creatControlElem(this.resetLabel, function () {
                    that.reset();
                    that.elem.trigger("numEntriesChanged.toMany");
                }).appendTo($("<li />").appendTo(elemControls));
                rocketTs.creatControlElem(this.clearLabel, function () {
                    that.elemUl.empty();
                    that.elem.trigger("numEntriesChanged.toMany");
                }).appendTo($("<li />").appendTo(elemControls));
                that.loadOverlay();
            };
            ToManySelector.prototype.reset = function () {
                var that = this;
                this.elemUl.empty();
                this.originalIdReps.forEach(function (idRep) {
                    that.addEntry(idRep, false);
                });
            };
            ToManySelector.prototype.loadOverlay = function () {
                var that = this;
                $.getJSON(this.overviewToolsUrl, function (data) {
                    that.preloadedStackedContent = new ToManySelectorStackedContent(that, rocketTs.analyzeAjahData(data));
                    that.preloadedStackedContent.getElemContent().appendTo($("body"));
                    rocketTs.updateUi();
                    that.preloadedStackedContent.getElemContent().detach();
                });
            };
            ToManySelector.prototype.addByIdentityStrings = function (identityStrings) {
                var that = this;
                $.each(identityStrings, function (idRep, identityString) {
                    if (that.elemUl.children(".rocket-to-many-" + idRep).length > 0)
                        return;
                    that.identityStrings[idRep] = identityString;
                    that.addEntry(idRep);
                });
            };
            ToManySelector.prototype.addEntry = function (idRep, prepend) {
                if (prepend === void 0) { prepend = true; }
                var elemLi = this.elemLiNew.clone(), toManySelctorEntry = new ToManySelectorEntry(this, elemLi, idRep);
                toManySelctorEntry.getElemInput().attr("name", this.basePropertyName + "[]");
                if (prepend) {
                    elemLi.prependTo(this.elemUl);
                }
                else {
                    elemLi.appendTo(this.elemUl);
                }
                this.elem.trigger("numEntriesChanged.toMany");
            };
            ToManySelector.prototype.getIdentityString = function (id) {
                var identiyString = id;
                $.each(this.identityStrings, function (tmpId, tmpIdentiyString) {
                    if (id !== tmpId)
                        return;
                    identiyString = tmpIdentiyString;
                    return false;
                });
                return identiyString;
            };
            ToManySelector.prototype.getNumSelectedEntries = function () {
                return this.elemUl.children().length;
            };
            return ToManySelector;
        }());
        var ToMany = (function () {
            function ToMany(elem) {
                this.newEntryFormUrl = null;
                this.toManySelector = null;
                this.types = null;
                this.entryFormPreparationCallback = null;
                this.nextPropertyPathIndex = 0;
                this.elem = elem;
                this.min = elem.data("min");
                this.max = elem.data("max") || null;
                this.moveUpLabel = elem.data("move-up-label");
                this.moveDownLabel = elem.data("move-down-label");
                this.removeItemLabel = elem.data("remove-item-label");
                this.itemLabel = elem.data("item-label");
                this.toManyEmbedded = new ToManyEmbedded(this);
                elem.data("to-many", this);
                (function (that) {
                    var elemCurrent = elem.children("div.rocket-current"), elemNew = elem.children("div.rocket-new"), elemSelector = elem.children(".rocket-selector"), currentAvailable = elemCurrent.length > 0, newAvailable = elemNew.length > 0;
                    if (newAvailable) {
                        that.newEntryFormUrl = elemNew.data("new-entry-form-url");
                        that.newEntryFormPropertyPath = elemNew.data("property-path");
                        that.addItemLabel = elemNew.data("add-item-label");
                        elemNew.children(".rocket-new").each(function () {
                            that.toManyEmbedded.addEntryForm(new ToManyEntryForm(that, $(this), true, that.getItemLabel()), true, false);
                        });
                    }
                    if (elemCurrent.length > 0) {
                        elemCurrent.children(".rocket-current").each(function () {
                            var elemLi = $(this);
                            that.toManyEmbedded.addEntryForm(new ToManyEntryForm(that, elemLi, newAvailable, elemLi.data("item-label")), true, false);
                        });
                        elemCurrent.remove();
                    }
                    if (elemSelector.length > 0) {
                        that.toManySelector = new ToManySelector(elemSelector, that.removeItemLabel);
                    }
                    if (currentAvailable || newAvailable) {
                        that.toManyEmbedded.activate(newAvailable);
                    }
                    var eiSpecLabels = elem.data("ei-spec-labels"), numEiSpecLabels = 0;
                    console.log(eiSpecLabels);
                    $.each(eiSpecLabels, function () {
                        numEiSpecLabels++;
                    });
                    if (numEiSpecLabels > 1) {
                        that.setTypes(eiSpecLabels);
                    }
                }).call(this, this);
            }
            ToMany.prototype.getMoveUpLabel = function () {
                return this.moveUpLabel;
            };
            ToMany.prototype.getMoveDownLabel = function () {
                return this.moveDownLabel;
            };
            ToMany.prototype.getRemoveItemLabel = function () {
                return this.removeItemLabel;
            };
            ToMany.prototype.getItemLabel = function () {
                return this.itemLabel;
            };
            ToMany.prototype.setEntryFormPreperationCallback = function (entryFormPreperationCallback) {
                this.entryFormPreparationCallback = entryFormPreperationCallback;
                this.toManyEmbedded.getEntryForms().forEach(function (entryForm) {
                    entryFormPreperationCallback(entryForm);
                });
            };
            ToMany.prototype.getElem = function () {
                return this.elem;
            };
            ToMany.prototype.requestNewEntryForm = function (callback, typeId) {
                if (typeId === void 0) { typeId = null; }
                var params = {
                    "propertyPath": this.newEntryFormPropertyPath + "[n" + this.nextPropertyPathIndex++ + "]"
                }, that = this;
                $.getJSON(this.newEntryFormUrl, params, function (data) {
                    var elemLiEntryForm = $("<div />", {
                        "class": "rocket-new"
                    }).append(rocketTs.analyzeAjahData(data)), headerLabel = that.itemLabel;
                    if (null !== typeId) {
                        var elemTypeSelector = elemLiEntryForm.find(".rocket-script-type-selector:first").hide();
                        elemTypeSelector.find(".rocket-script-type-selection:first").val(typeId).change();
                        headerLabel = that.types[typeId];
                    }
                    var newEntryForm = new ToManyEntryForm(that, elemLiEntryForm, true, headerLabel);
                    if (null !== that.entryFormPreparationCallback) {
                        that.entryFormPreparationCallback(newEntryForm);
                    }
                    callback(newEntryForm);
                });
            };
            ToMany.prototype.getAddItemLabel = function () {
                return this.addItemLabel;
            };
            ToMany.prototype.hasTypes = function () {
                return null !== this.types;
            };
            ToMany.prototype.getTypes = function () {
                return this.types;
            };
            ToMany.prototype.setTypes = function (types) {
                this.types = types;
                this.toManyEmbedded.getEntryForms().forEach(function (entryForm) {
                    var elemTypeSelector = entryForm.getElemLi().find(".rocket-script-type-selector:first").hide(), headerLabel = types[elemTypeSelector.find(".rocket-script-type-selection:first").val()];
                    entryForm.setHeaderLabel(headerLabel);
                });
            };
            ToMany.prototype.determineNumEntries = function () {
                var numEntries = this.toManyEmbedded.getNumEntryForms();
                if (null !== this.toManySelector) {
                    numEntries = this.toManySelector.getNumSelectedEntries();
                }
                return numEntries;
            };
            ToMany.prototype.determineNumMore = function () {
                return this.max - this.determineNumEntries();
            };
            ToMany.prototype.areMoreEntriesAllowed = function () {
                if (this.max === null)
                    return true;
                return this.determineNumEntries() < this.max;
            };
            ToMany.prototype.areLessEntriesAllowed = function () {
                if (this.min === null)
                    return true;
                return this.determineNumEntries() > this.min;
            };
            ToMany.prototype.getGenericAddItemLabel = function () {
                return this.addItemLabel;
            };
            ToMany.prototype.getToManyEmbedded = function () {
                return this.toManyEmbedded;
            };
            ToMany.prototype.loading = function () {
                this.elem.trigger('loading.toMany');
            };
            ToMany.prototype.loadingComplete = function () {
                this.elem.trigger('loadingComplete.toMany');
            };
            return ToMany;
        }());
        edit.ToMany = ToMany;
        rocketTs.ready(function () {
            rocketTs.registerUiInitFunction("form .rocket-to-many", function (elem) {
                new ToMany(elem);
            });
            rocketTs.registerUiInitFunction(".rocket-selector-mag", function (elem) {
                new ToManySelector(elem, "Text: remove Item");
            });
        });
    })(edit = spec.edit || (spec.edit = {}));
})(spec || (spec = {}));
/// <reference path="..\..\rocket.ts" />
/// <reference path="..\common.ts" />
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
 */
var spec;
(function (spec) {
    var edit;
    (function (edit) {
        $ = jQuery;
        var ToOneCurrent = (function () {
            function ToOneCurrent(toOne, elem, removable, toOneNew, replaceItemLabel) {
                if (toOneNew === void 0) { toOneNew = null; }
                if (replaceItemLabel === void 0) { replaceItemLabel = null; }
                this.elemRemove = null;
                this.elemReplace = null;
                this.toOneNew = null;
                this.removable = null;
                this.replaceItemLabel = null;
                this.elemUlReplaceControlOptions = null;
                this.toOne = toOne;
                this.elem = elem;
                this.toOneNew = toOneNew;
                this.replaceItemLabel = replaceItemLabel;
                this.elemContent = elem.find(".rocket-to-one-content");
                this.eiSpecId = elem.data("ei-spec-id");
                (function (that) {
                    that.setRemovable(removable);
                    if (null !== toOneNew) {
                        if (toOneNew.isAvailabale()) {
                            //form was sent and has errors
                            that.remove();
                        }
                        else {
                            toOneNew.setToOneCurrent(that);
                            toOneNew.getElemAdd().hide();
                            toOneNew.applyAdd(replaceItemLabel, function () {
                                that.resetControls();
                                that.remove();
                            });
                        }
                    }
                }).call(this, this);
            }
            ToOneCurrent.prototype.setLoading = function () {
                this.elemContent.hide();
                this.elem.append(rocketTs.createLoadingElem());
            };
            ToOneCurrent.prototype.getEiSpecId = function () {
                return this.eiSpecId;
            };
            ToOneCurrent.prototype.resetControls = function () {
                if (null !== this.elemRemove) {
                    this.elemRemove.parent().remove();
                    this.elemRemove = null;
                }
                if (null !== this.elemReplace) {
                    this.elemReplace.parent().remove();
                    this.elemReplace = null;
                }
            };
            ToOneCurrent.prototype.getElemContent = function () {
                return this.elemContent;
            };
            ToOneCurrent.prototype.setRemovable = function (removable) {
                if (removable === this.removable)
                    return;
                var that = this;
                if (removable) {
                    if (null !== this.toOneNew) {
                        var replaceControl = new ToOneRecycleControl(that.toOne, function (eiSpecId) {
                            that.setLoading();
                            that.toOneNew.activate(eiSpecId);
                        }, function () {
                            that.setLoading();
                            that.toOneNew.activate();
                        });
                        replaceControl.setConfirmMessage(this.elem.data("replace-confirm-msg"));
                        replaceControl.setConfirmOkLabel(this.elem.data("replace-ok-label"));
                        replaceControl.setConfirmCancelLabel(this.elem.data("replace-cancel-label"));
                        this.elemReplace = replaceControl.getControlElem();
                        if (this.toOneNew.isRemovable()) {
                            this.elemRemove = this.toOne.addControl(this.elem.data("remove-item-label"), function () {
                                that.remove();
                                that.resetControls();
                                that.toOneNew.getElemAdd().show();
                            }, 'fa fa-times');
                        }
                    }
                    else {
                        this.elemRemove = this.toOne.addControl(this.elem.data("remove-item-label"), function () {
                            that.remove();
                        }, 'fa fa-times');
                    }
                }
                else {
                    if (null !== this.elemRemove) {
                        this.elemRemove.parent().remove();
                        this.elemRemove = null;
                    }
                    if (null !== this.elemReplace) {
                        this.elemReplace.parent().remove();
                        this.elemReplace = null;
                    }
                }
                this.removable = removable;
            };
            ToOneCurrent.prototype.remove = function () {
                this.elem.remove();
            };
            return ToOneCurrent;
        }());
        var ToOneSelectorStackedContent = (function () {
            function ToOneSelectorStackedContent(toOneSelector, elemContent) {
                this.stackedContentContainer = null;
                this.overviewTools = null;
                this.startObservingOnLoad = false;
                this.idClassName = "rocket-to-one-stacked-content-" + ++ToOneSelectorStackedContent.lastId;
                this.elemContent = $("<div />", {
                    "class": "rocket-to-one-stacked-content " + this.idClassName
                }).append($("<div />", {
                    "class": "rocket-panel"
                }).append(elemContent));
                this.toOneSelector = toOneSelector;
                (function (that) {
                    elemContent.on('overview.contentLoaded', function (e, overviewTools) {
                        that.overviewTools = overviewTools;
                        overviewTools.getFixedHeader().setApplyDefaultFixedContainer(false);
                        if (null !== that.stackedContentContainer) {
                            overviewTools.setElemFixedContainer(that.stackedContentContainer.getElem(), that.startObservingOnLoad);
                        }
                        that.overviewTools.setSelectable(false);
                    });
                    rocketTs.registerUiInitFunction("." + that.idClassName + " .rocket-overview-content:first > tr > .rocket-entry-selector", function (elem) {
                        var id = elem.data("entry-id-rep"), identityString = elem.data("identity-string");
                        rocketTs.creatControlElem(toOneSelector.getSelectLabel() + " (" + identityString + ")", function () {
                            var identityStrings = {};
                            identityStrings[id] = identityString;
                            toOneSelector.applyIdentityStrings(identityStrings);
                            that.stackedContentContainer.close();
                        }).appendTo(elem);
                        $(window).trigger('resize.overview');
                    });
                }).call(this, this);
            }
            ToOneSelectorStackedContent.prototype.getTitle = function () {
                return "test";
            };
            ;
            ToOneSelectorStackedContent.prototype.getElemContent = function () {
                return this.elemContent;
            };
            ToOneSelectorStackedContent.prototype.setup = function (stackedContentContainer) {
                this.stackedContentContainer = stackedContentContainer;
                if (null !== this.overviewTools) {
                    this.overviewTools.setElemFixedContainer(stackedContentContainer.getElem(), false);
                }
            };
            ToOneSelectorStackedContent.prototype.onAnimationComplete = function () {
                if (null !== this.overviewTools) {
                    this.overviewTools.getFixedHeader().startObserving();
                }
                else {
                    this.startObservingOnLoad = false;
                }
            };
            ToOneSelectorStackedContent.prototype.onClose = function () {
                this.overviewTools.getFixedHeader().reset();
                this.overviewTools.getFixedHeader().stopObserving();
            };
            ToOneSelectorStackedContent.prototype.onClosed = function () {
                this.stackedContentContainer.getElem().scrollTop(0);
                this.startObservingOnLoad = false;
            };
            ToOneSelectorStackedContent.lastId = 0;
            return ToOneSelectorStackedContent;
        }());
        var ToOneSelector = (function () {
            function ToOneSelector(elem, removeItemLabel) {
                this.elemSelect = null;
                this.elemReset = null;
                this.elemRemove = null;
                this.preloadedStackedContent = null;
                this.elem = elem;
                this.overviewToolsUrl = elem.data("overview-tools-url");
                this.elemInput = elem.find("input:first").hide();
                this.identityStrings = elem.data("identity-strings") || null;
                this.originalIdRep = elem.data("original-id-rep");
                this.selectLabel = elem.data("select-label");
                this.resetLabel = elem.data("reset-label");
                this.elemLabel = $("<span />");
                (function (that) {
                    that.elemLabelContainer = $("<div />", {
                        "class": "rocket-relation-label-container"
                    }).append(that.elemLabel).insertAfter(that.elemInput);
                    that.elemRemove = rocketTs.creatControlElem(removeItemLabel, function () {
                        that.applyIdentityStrings(null);
                        that.elemLabelContainer.detach();
                    }, "fa fa-times").appendTo(that.elemLabelContainer);
                    that.applyIdentityStrings(that.getIdentityString(that.elemInput.val()));
                    that.initControls();
                }).call(this, this);
            }
            ToOneSelector.prototype.getIdentityString = function (idRep) {
                if (!idRep)
                    return null;
                var identityString = {};
                identityString[idRep] = this.identityStrings[idRep];
                return identityString;
            };
            ToOneSelector.prototype.getSelectLabel = function () {
                return this.selectLabel;
            };
            ToOneSelector.prototype.initControls = function () {
                var that = this, elemControls = $("<div />", {
                    "class": "rocket-to-one-controls"
                }).insertAfter(this.elemLabelContainer);
                this.elemBtnSelect = rocketTs.creatControlElem(this.selectLabel, function () {
                    rocketTs.getContentStack().addStackedContent(that.preloadedStackedContent);
                    rocketTs.updateUi();
                }).appendTo(elemControls).addClass("rocket-loading").prop("disabled", true);
                this.elemReset = rocketTs.creatControlElem(this.resetLabel, function () {
                    that.applyIdentityStrings(that.getIdentityString(that.originalIdRep));
                }).appendTo(elemControls);
                that.loadOverlay();
            };
            ToOneSelector.prototype.loadOverlay = function () {
                var that = this;
                $.getJSON(this.overviewToolsUrl, function (data) {
                    that.preloadedStackedContent = new ToOneSelectorStackedContent(that, rocketTs.analyzeAjahData(data));
                    that.preloadedStackedContent.getElemContent().appendTo($("body"));
                    rocketTs.updateUi();
                    that.preloadedStackedContent.getElemContent().detach();
                    that.elemBtnSelect.removeClass("rocket-loading").prop("disabled", false);
                });
            };
            ToOneSelector.prototype.applyIdentityStrings = function (identityObject) {
                if (identityObject === void 0) { identityObject = null; }
                var that = this;
                this.elemLabelContainer.insertAfter(this.elemInput);
                if (null === identityObject) {
                    this.elemInput.val(null);
                    this.elemLabel.empty();
                }
                else {
                    $.each(identityObject, function (id, label) {
                        that.elemInput.val(id);
                        that.elemLabel.text(label);
                        return false;
                    });
                }
                if (!this.elemInput.val()) {
                    this.elemRemove.hide();
                }
                else {
                    this.elemRemove.show();
                }
            };
            return ToOneSelector;
        }());
        var ToOneNew = (function () {
            function ToOneNew(toOne, elem, removable) {
                this.elemRemove = null;
                this.removable = null;
                this.entryFormLoaded = false;
                this.activated = false;
                this.addCallback = null;
                this.elemEntryForm = null;
                this.toOneCurrent = null;
                this.elemScriptTypeSelector = null;
                this.elemTypeSelection = null;
                this.elemUlTypes = null;
                this.entryFormCommandAdd = null;
                this.toOne = toOne;
                this.elem = elem;
                this.newEntryFormUrl = elem.data("new-entry-form-url");
                this.removeItemLabel = elem.data("remove-item-label");
                this.addItemLabel = elem.data("add-item-label");
                this.propertyPath = elem.data("property-path");
                var elemContents = elem.children();
                this.elemEntryFormContainer = $("<div />");
                (function (that) {
                    if (elemContents.length > 0) {
                        that.initializeContent(elemContents);
                        that.activated = true;
                        that.entryFormLoaded = true;
                        if (that.hasTypeSelection()) {
                            if (!elem.data("prefilled")) {
                                that.applyEiSpecId(that.elemTypeSelection.val());
                            }
                            else {
                                that.initializeAdd();
                            }
                        }
                        else {
                            that.elemEntryFormContainer.appendTo(elem);
                        }
                    }
                    else {
                        that.initializeAdd();
                        that.elemEntryFormContainer.hide();
                    }
                    that.setRemovable(removable);
                }).call(this, this);
            }
            ToOneNew.prototype.getElem = function () {
                return this.elem;
            };
            ToOneNew.prototype.getElemAdd = function () {
                return this.entryFormCommandAdd.getElemContainer();
            };
            ToOneNew.prototype.initTypeSelction = function () {
                this.elemScriptTypeSelector = this.elemEntryForm.find("> .rocket-script-type-selector"),
                    this.elemTypeSelection = this.elemScriptTypeSelector.find(".rocket-script-type-selection");
            };
            ToOneNew.prototype.hasTypeSelection = function () {
                return this.elemScriptTypeSelector.length > 0 && this.elemScriptTypeSelector.length > 0;
            };
            ToOneNew.prototype.setToOneCurrent = function (toOneCurrent) {
                this.toOneCurrent = toOneCurrent;
            };
            ToOneNew.prototype.isAvailabale = function () {
                return this.activated;
            };
            ToOneNew.prototype.initializeContent = function (elemEntryForm) {
                this.elemEntryForm = elemEntryForm;
                this.initTypeSelction();
                this.elemEntryFormContainer.append(elemEntryForm);
                this.elemEntryFormContainer.children(".rocket-to-many-order-index").hide();
                var that = this;
                if (this.removable) {
                    this.elemRemove = this.toOne.addControl(this.removeItemLabel, function () {
                        that.elemEntryFormContainer.detach();
                        that.initializeAdd();
                    }, "fa fa-times");
                }
                if (this.hasTypeSelection()) {
                    var toOneRecyleControl = new ToOneRecycleControl(that.toOne, function (eiSpecId) {
                        that.applyEiSpecId(eiSpecId);
                    });
                    toOneRecyleControl.setExcludeEiSpecIdCallback(function (eiSpecId) {
                        if (eiSpecId === that.elemTypeSelection.val())
                            return true;
                        return false;
                    });
                }
            };
            ToOneNew.prototype.initializeAdd = function () {
                var that = this;
                if (null === this.entryFormCommandAdd) {
                    this.entryFormCommandAdd = new ui.EntryFormCommand(this.addItemLabel, function () {
                        if (!that.toOne.isTypeSelectable()) {
                            that.activate();
                            return;
                        }
                        var elemButton = that.entryFormCommandAdd.getElemButton();
                        if (elemButton.hasClass("rocket-command-insert-open")) {
                            that.elemUlTypes.hide();
                            elemButton.removeClass("rocket-command-insert-open");
                            return;
                        }
                        if (null === that.elemUlTypes) {
                            that.elemUlTypes = $("<ul />", {
                                "class": "rocket-dd-menu-open"
                            }).insertAfter(elemButton);
                        }
                        else {
                            that.elemUlTypes.empty();
                        }
                        elemButton.addClass("rocket-command-insert-open");
                        that.toOne.createTypeElemLis(function (eiSpecId) {
                            that.activate(eiSpecId);
                            elemButton.removeClass("rocket-command-insert-open");
                            that.elemUlTypes.hide();
                        }).forEach(function (elemLi) {
                            elemLi.appendTo(that.elemUlTypes).children("a").removeClass();
                        });
                    }, "fa fa-plus");
                }
                this.entryFormCommandAdd.getElemContainer().appendTo(this.elem);
            };
            ToOneNew.prototype.setRemovable = function (removable) {
                if (this.removable === removable)
                    return;
                var that = this;
                if (removable) {
                    var that = this;
                    this.initializeAdd();
                }
                else {
                    if (this.activated) {
                        this.activate();
                    }
                    if (null !== this.elemRemove) {
                        this.elemRemove.parent().remove();
                        this.elemRemove = null;
                    }
                }
                this.removable = removable;
            };
            ToOneNew.prototype.isRemovable = function () {
                return this.removable;
            };
            ToOneNew.prototype.applyAdd = function (addItemLabel, callback) {
                this.addItemLabel = addItemLabel;
                this.entryFormCommandAdd.getElemButton().text(addItemLabel);
                this.addCallback = callback;
            };
            ToOneNew.prototype.applyEiSpecId = function (eiSpecId) {
                if (this.elemTypeSelection.children("option[value=" + eiSpecId + "]").length === 0)
                    return;
                this.elemScriptTypeSelector.hide();
                this.toOne.setTypeSpecLabel(this.toOne.determineEiSpecLabel(eiSpecId));
                this.elemTypeSelection.val(eiSpecId).change();
                this.elem.trigger('applyEiSpecId.toOne', eiSpecId);
            };
            ToOneNew.prototype.activate = function (eiSpecId) {
                if (eiSpecId === void 0) { eiSpecId = null; }
                var that = this;
                this.requestNewEntryForm(function () {
                    that.elemEntryFormContainer.appendTo(that.elem);
                    rocketTs.updateUi();
                    if (null !== eiSpecId && that.hasTypeSelection()) {
                        that.applyEiSpecId(eiSpecId);
                    }
                    if (null !== that.entryFormCommandAdd) {
                        that.entryFormCommandAdd.getElemContainer().detach();
                    }
                    if (null !== that.addCallback) {
                        that.addCallback();
                    }
                });
                this.activated = true;
            };
            ToOneNew.prototype.requestNewEntryForm = function (callback) {
                var that = this;
                if (this.entryFormLoaded || this.activated) {
                    if (null !== this.elemEntryForm) {
                        this.elemEntryForm.appendTo(this.elemEntryFormContainer);
                        callback();
                    }
                    return;
                }
                this.entryFormLoaded = true;
                if (null !== this.entryFormCommandAdd) {
                    this.entryFormCommandAdd.setLoading(true);
                }
                $.getJSON(this.newEntryFormUrl, { propertyPath: this.propertyPath }, function (data) {
                    that.initializeContent(rocketTs.analyzeAjahData(data));
                    rocketTs.updateUi();
                    that.elemEntryFormContainer.show();
                    if (null !== that.entryFormCommandAdd) {
                        that.entryFormCommandAdd.setLoading(false);
                    }
                    callback();
                });
            };
            return ToOneNew;
        }());
        var ToOneRecycleControl = (function () {
            function ToOneRecycleControl(toOne, typeCallback, defaultCallback) {
                if (defaultCallback === void 0) { defaultCallback = null; }
                this.elemUlReplaceControlOptions = null;
                this.excludeEiSpecIdCallback = null;
                this.confirmMessage = null;
                this.confirmOkLabel = null;
                this.confirmCancelLabel = null;
                this.toOne = toOne;
                this.typeCallback = typeCallback;
                this.defaultCallback = defaultCallback;
                (function (that) {
                    that.controlElem = this.toOne.addControl(this.replaceItemLabel, function () {
                        if (!that.toOne.isTypeSelectable()) {
                            if (null !== defaultCallback) {
                                defaultCallback();
                                return;
                            }
                        }
                        if (null !== that.confirmMessage
                            && (null === that.elemUlReplaceControlOptions
                                || (null !== that.elemUlReplaceControlOptions
                                    && !that.elemUlReplaceControlOptions.hasClass("rocket-open")))) {
                            var dialog = new ui.Dialog(that.confirmMessage);
                            dialog.addButton(that.confirmOkLabel, function (e) {
                                e.stopPropagation();
                                that.initList();
                            });
                            dialog.addButton(that.confirmCancelLabel, function () {
                                //defaultbehaviour is to close the dialog
                            });
                            rocketTs.showDialog(dialog);
                            return;
                        }
                        that.initList();
                    }, 'fa fa-recycle').addClass("rocket-control-danger");
                }).call(this, this);
            }
            ToOneRecycleControl.prototype.initList = function () {
                var that = this;
                if (null === this.elemUlReplaceControlOptions) {
                    this.elemUlReplaceControlOptions = $("<ul />", {
                        "class": "rocket-control-options"
                    }).insertAfter(this.controlElem);
                }
                else {
                    if (this.elemUlReplaceControlOptions.hasClass("rocket-open")) {
                        this.hideList();
                        return;
                    }
                    this.elemUlReplaceControlOptions.empty();
                }
                console.log(this.elemUlReplaceControlOptions);
                this.showList();
                this.toOne.createTypeElemLis(function (eiSpecId) {
                    that.typeCallback(eiSpecId);
                    that.hideList();
                }, this.excludeEiSpecIdCallback).forEach(function (elemLi) {
                    elemLi.appendTo(that.elemUlReplaceControlOptions);
                });
                var elemReplacePosition = this.controlElem.position();
                this.elemUlReplaceControlOptions.css({
                    "position": "absolute",
                    "zIndex": 2,
                    "top": elemReplacePosition.top + this.controlElem.outerHeight(),
                    "left": elemReplacePosition.left + this.controlElem.outerWidth()
                        - this.elemUlReplaceControlOptions.outerWidth()
                });
            };
            ToOneRecycleControl.prototype.setConfirmMessage = function (confirmMessage) {
                if (confirmMessage === void 0) { confirmMessage = null; }
                this.confirmMessage = confirmMessage;
            };
            ToOneRecycleControl.prototype.setConfirmOkLabel = function (confirmOkLabel) {
                if (confirmOkLabel === void 0) { confirmOkLabel = null; }
                this.confirmOkLabel = confirmOkLabel;
            };
            ToOneRecycleControl.prototype.setConfirmCancelLabel = function (confirmCancelLabel) {
                if (confirmCancelLabel === void 0) { confirmCancelLabel = null; }
                this.confirmCancelLabel = confirmCancelLabel;
            };
            ToOneRecycleControl.prototype.hideList = function () {
                this.elemUlReplaceControlOptions.removeClass("rocket-open");
                this.elemUlReplaceControlOptions.hide();
                $(window).off('off.toOneRecycle');
            };
            ToOneRecycleControl.prototype.showList = function () {
                this.elemUlReplaceControlOptions.addClass("rocket-open").show();
                var that = this;
                $(window).on('click.toOneRecycle', function (e) {
                    if ($(e.target).is(that.controlElem) || $.contains(that.controlElem.get(0), e.target))
                        return;
                    that.hideList();
                });
            };
            ToOneRecycleControl.prototype.setExcludeEiSpecIdCallback = function (excludeEiSpecIdCallback) {
                if (excludeEiSpecIdCallback === void 0) { excludeEiSpecIdCallback = null; }
                this.excludeEiSpecIdCallback = excludeEiSpecIdCallback;
            };
            ToOneRecycleControl.prototype.getControlElem = function () {
                return this.controlElem;
            };
            return ToOneRecycleControl;
        }());
        var TypeConfig = (function () {
            function TypeConfig(label, callback) {
                this.label = label;
                this.callback = callback;
            }
            TypeConfig.prototype.getLabel = function () {
                return this.label;
            };
            TypeConfig.prototype.getCallback = function () {
                return this.callback;
            };
            return TypeConfig;
        }());
        var EiSpecConfig = (function () {
            function EiSpecConfig(specId) {
                this.additionalTypeConfigs = [];
                this.specId = specId;
            }
            EiSpecConfig.prototype.registerTypeConfig = function (label, callback) {
                this.additionalTypeConfigs.push(new TypeConfig(label, callback));
            };
            EiSpecConfig.prototype.getAdditionalTypeConfigs = function () {
                return this.additionalTypeConfigs;
            };
            EiSpecConfig.prototype.reset = function () {
                this.additionalTypeConfigs = [];
            };
            return EiSpecConfig;
        }());
        var ToOne = (function () {
            function ToOne(elem) {
                this.mandatory = false;
                this.toOneNew = null;
                this.toOneCurrent = null;
                this.toOneSelector = null;
                this.elemUlControls = null;
                this.eiSpecConfigs = {};
                this.elem = elem;
                this.mandatory = elem.data("mandatory") || false;
                this.itemLabel = elem.data("item-label");
                this.replaceItemLabel = elem.data("replace-item-label");
                this.removeItemLabel = elem.data("remove-item-label");
                this.eiSpecLabels = elem.data("ei-spec-labels");
                this.elemLabel = elem.parent(".rocket-controls:first").prev("label");
                this.defaultLabel = this.elemLabel.text();
                this.typeSelectable = false;
                (function (that) {
                    var numTypes = 0;
                    $.each(this.eiSpecLabels, function () {
                        numTypes++;
                    });
                    that.typeSelectable = numTypes > 1;
                    var elemNew = elem.children(".rocket-new:first");
                    if (elemNew.length > 0) {
                        that.toOneNew = new ToOneNew(that, elemNew, !that.mandatory);
                    }
                    var elemCurrent = elem.children(".rocket-current:first");
                    if (elemCurrent.length > 0) {
                        if (that.typeSelectable) {
                            that.setTypeSpecLabel(elemCurrent.data("item-label"));
                        }
                        that.toOneCurrent = new ToOneCurrent(that, elemCurrent, null !== that.toOneNew || !that.mandatory, that.toOneNew, that.replaceItemLabel);
                    }
                    var elemSelector = elem.children(".rocket-selector:first");
                    if (elemSelector.length > 0) {
                        new ToOneSelector(elemSelector, that.removeItemLabel);
                    }
                }).call(this, this);
            }
            ToOne.prototype.isTypeSelectable = function () {
                return this.typeSelectable;
            };
            ToOne.prototype.getEiSpecLabels = function () {
                return this.eiSpecLabels;
            };
            ToOne.prototype.setEiSpecLabels = function (eiSpecLabels) {
                this.eiSpecLabels = eiSpecLabels;
            };
            ToOne.prototype.determineEiSpecLabel = function (eiSpecId) {
                return this.eiSpecLabels[eiSpecId];
            };
            ToOne.prototype.setTypeSpecLabel = function (typeSpecLabel) {
                this.elemLabel.text(this.defaultLabel + ": " + typeSpecLabel);
            };
            ToOne.prototype.getToOneCurrent = function () {
                return this.toOneCurrent;
            };
            ToOne.prototype.getToOneNew = function () {
                return this.toOneNew;
            };
            ToOne.prototype.addControl = function (text, callback, iconClassName) {
                if (null === this.elemUlControls) {
                    this.elemUlControls = $("<ul />", {
                        "class": "rocket-simple-controls"
                    }).css({
                        "position": "absolute",
                        "top": "0",
                        "right": "0"
                    }).insertAfter(this.elemLabel);
                }
                var elemControl = rocketTs.creatControlElem(text, callback, iconClassName);
                $("<li />").append(elemControl)
                    .appendTo(this.elemUlControls);
                return elemControl;
            };
            ToOne.prototype.setMandatory = function (mandatory) {
                this.mandatory = mandatory;
                if (null !== this.toOneCurrent) {
                    this.toOneCurrent.setRemovable(null !== this.toOneNew || !mandatory);
                }
                if (null !== this.toOneNew) {
                    this.toOneNew.setRemovable(!mandatory);
                }
            };
            ToOne.prototype.getElem = function () {
                return this.elem;
            };
            ToOne.prototype.hasEiSpecConfig = function (eiSpecId) {
                return this.eiSpecConfigs.hasOwnProperty(eiSpecId);
            };
            ToOne.prototype.getOrCreateEiSpecConfig = function (eiSpecId) {
                if (!this.eiSpecLabels.hasOwnProperty(eiSpecId)) {
                    throw new Error("Invalid ei spec id: " + eiSpecId);
                }
                if (!this.eiSpecConfigs.hasOwnProperty(eiSpecId)) {
                    this.eiSpecConfigs[eiSpecId] = new EiSpecConfig(eiSpecId);
                }
                return this.eiSpecConfigs[eiSpecId];
            };
            ToOne.prototype.createTypeElemLis = function (typeCallback, excludeEiSpecIdCallback) {
                if (excludeEiSpecIdCallback === void 0) { excludeEiSpecIdCallback = null; }
                var lis = [], that = this;
                $.each(that.getEiSpecLabels(), function (eiSpecId, label) {
                    if (that.hasEiSpecConfig(eiSpecId)) {
                        var eiSpecConfig = that.getOrCreateEiSpecConfig(eiSpecId);
                        eiSpecConfig.getAdditionalTypeConfigs().forEach(function (typeConfig) {
                            lis.push(that.createTypeElemLi(typeConfig.getLabel(), function () {
                                typeConfig.getCallback()(that);
                                typeCallback(eiSpecId);
                            }));
                        });
                        return;
                    }
                    if (null !== excludeEiSpecIdCallback) {
                        if (excludeEiSpecIdCallback(eiSpecId))
                            return;
                    }
                    lis.push(that.createTypeElemLi(label, function () {
                        typeCallback(eiSpecId);
                    }));
                });
                lis.sort(function (elemLiA, elemLiB) {
                    if (elemLiA.data("sort") < elemLiB.data("sort"))
                        return -1;
                    if (elemLiA.data("sort") == elemLiB.data("sort"))
                        return 0;
                    return 1;
                });
                return lis;
            };
            ToOne.prototype.createTypeElemLi = function (label, callback) {
                return $("<li />").append(rocketTs.creatControlElem(label, function () {
                    callback();
                })).data("sort", label);
            };
            return ToOne;
        }());
        $(document).ready(function () {
            rocketTs.registerUiInitFunction("form .rocket-to-one", function (elem) {
                elem.data('rocket-to-one', new ToOne(elem));
                elem.trigger('initialized.toOne', elem.data('rocket-to-one'));
            });
        });
    })(edit = spec.edit || (spec.edit = {}));
})(spec || (spec = {}));
/// <reference path="..\rocket.ts" />
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
 */
var preview;
(function (preview) {
    $ = jQuery;
    var Iframe = (function () {
        function Iframe(elemIframe) {
            this.elemIframe = elemIframe;
            this.elemHeader = $("#rocket-header");
            this.elemPanelTitle = $(".rocket-panel:first h3:first");
            this.elemMainCommands = $(".rocket-main-commands:first");
            (function (that) {
                $(window).resize(function () {
                    rocketTs.waitForFinalEvent(function () {
                        that.adjustIframeHeight();
                    }, 30, 'preview.resize');
                });
                that.adjustIframeHeight();
            }).call(this, this);
        }
        Iframe.prototype.adjustIframeHeight = function () {
            var iFrameMinHeight = $(window).height() - this.elemHeader.height()
                - this.elemMainCommands.outerHeight() - this.elemPanelTitle.outerHeight();
            this.elemIframe.css({
                "min-height": iFrameMinHeight
            });
        };
        return Iframe;
    }());
    rocketTs.ready(function () {
        var elemIframe = $("#rocket-preview-content");
        if (elemIframe.length === 0)
            return;
        new Iframe(elemIframe);
    });
})(preview || (preview = {}));
/// <reference path="..\rocket.ts" />
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
 */
var ui;
(function (ui) {
    $ = jQuery;
    var Panel = (function () {
        function Panel(group, elem) {
            this.parentPanelGroupId = null;
            this.group = group;
            this.elem = elem;
            this.elemLi = $("<li/>", {
                "class": "rocket-panel-activator"
            });
            this.parentPanelGroupId = null;
            (function (that) {
                that.elemLi.append($("<a/>", {
                    "href": "#",
                    "text": elem.children(":first").hide().text()
                }).click(function (e) {
                    e.preventDefault();
                }));
                that.elemLi.click(function () {
                    that.show();
                });
                that.hide();
            }).call(this, this);
        }
        Panel.prototype.getElemLi = function () {
            return this.elemLi;
        };
        Panel.prototype.show = function () {
            this.elemLi.addClass("rocket-active");
            if (this.group.hasParentPanelGroup()) {
                if (null !== this.getId()) {
                    if (typeof history.pushState !== 'undefined') {
                        history.pushState(null, null, '#!' + this.getId());
                    }
                    else {
                        window.location.hash = "#!" + this.getId();
                    }
                }
            }
            this.elem.show();
        };
        ;
        Panel.prototype.hide = function () {
            this.elemLi.removeClass("rocket-active");
            this.elem.hide();
        };
        ;
        Panel.prototype.equals = function (obj) {
            return obj instanceof Panel && this.elemLi.is(obj.getElemLi());
        };
        ;
        Panel.prototype.getId = function () {
            return this.elem.attr("id") || null;
        };
        return Panel;
    }());
    var PanelGroup = (function () {
        function PanelGroup(elem) {
            this.currentPanel = null;
            this.elem = elem;
            this.elemUl = $("<ul/>", {
                "class": "rocket-grouped-panels-navigation"
            });
            (function (that) {
                var currentPanelId = window.location.hash.substr(2), panelToActivate = null;
                elem.children().each(function () {
                    var panel = new Panel(that, $(this));
                    if (null === panelToActivate || (panel.getId() === currentPanelId)) {
                        panelToActivate = panel;
                    }
                    that.addPanel(panel);
                });
                that.activatePanel(panelToActivate);
                that.elemUl.prependTo(elem);
            }).call(this, this);
        }
        PanelGroup.prototype.addPanel = function (panel) {
            var that = this;
            this.elemUl.append(panel.getElemLi().click(function () {
                that.activatePanel(panel);
            }));
        };
        ;
        PanelGroup.prototype.hasParentPanelGroup = function () {
            return this.elem.parents(".rocket-grouped-panels:first").length > 0;
        };
        PanelGroup.prototype.activatePanel = function (panel) {
            if (null !== this.currentPanel) {
                if (this.currentPanel.equals(panel))
                    return;
                this.currentPanel.hide();
            }
            else {
                panel.show();
            }
            this.currentPanel = panel;
        };
        ;
        return PanelGroup;
    }());
    ui.PanelGroup = PanelGroup;
    rocketTs.ready(function () {
        rocketTs.registerUiInitFunction(".rocket-grouped-panels", function (elemPanelGroup) {
            new PanelGroup(elemPanelGroup);
        });
    });
})(ui || (ui = {}));
var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
/// <reference path="..\rocket.ts" />
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
 */
var ui;
(function (ui) {
    var ConfirmableAdapter = (function () {
        function ConfirmableAdapter(manager, elem) {
            this.manager = manager;
            this.elem = elem;
            this.msg = this.elem.data("rocket-confirm-msg") || "Are you sure?";
            this.confirmOkLabel = this.elem.data("rocket-confirm-ok-label") || "Yes";
            ;
            this.confirmCancelLabel = this.elem.data("rocket-confirm-cancel-label") || "No";
        }
        ConfirmableAdapter.prototype.getMsg = function () {
            return this.msg;
        };
        ConfirmableAdapter.prototype.setMsg = function (msg) {
            this.msg = msg;
            ;
        };
        ConfirmableAdapter.prototype.getConfirmOkLabel = function () {
            return this.confirmOkLabel;
        };
        ConfirmableAdapter.prototype.setConfirmOkLabel = function (confirmOkLabel) {
            this.confirmOkLabel = confirmOkLabel;
        };
        ConfirmableAdapter.prototype.getConfirmCancelLabel = function () {
            return this.confirmCancelLabel;
        };
        ConfirmableAdapter.prototype.setConfirmCancelLabel = function (confirmCancelLabel) {
            this.confirmCancelLabel = confirmCancelLabel;
        };
        ConfirmableAdapter.prototype.showDialog = function () {
            this.manager.showDialog(this);
        };
        ConfirmableAdapter.prototype.confirmDialog = function () { };
        ;
        return ConfirmableAdapter;
    }());
    var ConfirmableSubmit = (function (_super) {
        __extends(ConfirmableSubmit, _super);
        function ConfirmableSubmit(manager, elemInput) {
            _super.call(this, manager, elemInput);
            this.elemForm = elemInput.parents("form:first");
            (function (that) {
                elemInput.off("click.form").on("click.formInput", function () {
                    that.showDialog();
                });
            }).call(this, this);
        }
        ConfirmableSubmit.prototype.confirmDialog = function () {
            this.elem.off("click.formInput");
            if (this.elemForm.length > 0) {
                var tempInput = $("<input/>", {
                    "type": "hidden",
                    "name": this.elem.attr("name"),
                    "value": this.elem.val()
                });
                this.elemForm.append(tempInput);
                this.elemForm.submit();
                tempInput.remove();
            }
        };
        return ConfirmableSubmit;
    }(ConfirmableAdapter));
    var ConfirmableForm = (function (_super) {
        __extends(ConfirmableForm, _super);
        function ConfirmableForm(manager, elemForm) {
            _super.call(this, manager, elemForm);
            (function (that) {
                elemForm.on("click.form", "input[type=submit]", function () {
                    that.elemSubmit = this;
                    that.showDialog();
                    //_obj.jqElemForm.find('input').blur();
                    return false;
                });
            }).call(this, this);
        }
        ConfirmableForm.prototype.confirmDialog = function () {
            this.elem.off("click.form");
            var tempInput = $("<input />", {
                "type": "hidden",
                "name": this.elemSubmit.attr("name"),
                "value": this.elemSubmit.val()
            }).appendTo(this.elem);
            this.elem.submit();
            tempInput.remove();
        };
        return ConfirmableForm;
    }(ConfirmableAdapter));
    var ConfirmableLink = (function (_super) {
        __extends(ConfirmableLink, _super);
        function ConfirmableLink(manager, elemA) {
            _super.call(this, manager, elemA);
            (function (that) {
                elemA.on("click.confirmable", function (e) {
                    e.preventDefault();
                    that.showDialog();
                });
            }).call(this, this);
        }
        ConfirmableLink.prototype.confirmDialog = function () {
            window.location.assign(this.elem.attr("href"));
        };
        return ConfirmableLink;
    }(ConfirmableAdapter));
    var ConfirmableManager = (function () {
        function ConfirmableManager() {
        }
        ConfirmableManager.prototype.initElem = function (elem) {
            if (elem.is("[type=submit]")) {
                return new ConfirmableSubmit(this, elem);
            }
            if (elem.is("form")) {
                return new ConfirmableForm(this, elem);
            }
            if (elem.is("a")) {
                return new ConfirmableLink(this, elem);
            }
            throw new Error("invalid confirmable");
        };
        ConfirmableManager.prototype.showDialog = function (confirmable) {
            var that = this, dialog = new ui.Dialog(confirmable.getMsg());
            dialog.addButton(confirmable.getConfirmOkLabel(), function () {
                confirmable.confirmDialog();
            });
            dialog.addButton(confirmable.getConfirmCancelLabel(), function () {
                //defaultbehaviour is to close the dialog
            });
            rocketTs.showDialog(dialog);
        };
        return ConfirmableManager;
    }());
    ui.ConfirmableManager = ConfirmableManager;
    rocketTs.ready(function () {
        rocketTs.registerUiInitFunction("[data-rocket-confirm-msg]", function (elemConfirmable) {
            rocketTs.getConfirmableManager().initElem(elemConfirmable);
        });
    });
})(ui || (ui = {}));
/// <reference path="..\..\rocket.ts" />
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
 */
var spec;
(function (spec) {
    var edit;
    (function (edit) {
        $ = jQuery;
        var TypeSelection = (function () {
            function TypeSelection(elemEntryForm) {
                this.types = {};
                this.elemCurrentType = null;
                this.elemEntryForm = elemEntryForm;
                this.elemSelect = elemEntryForm.find("> .rocket-script-type-selector .rocket-script-type-selection");
                (function (that) {
                    that.elemSelect.children().each(function () {
                        var value = $(this).val();
                        that.types[value] = elemEntryForm.children(".rocket-script-type-" + value).detach();
                    });
                    that.elemSelect.change(function () {
                        if (null !== that.elemCurrentType) {
                            that.elemCurrentType.detach();
                        }
                        that.elemCurrentType = that.types[that.elemSelect.val()].appendTo(that.elemEntryForm);
                        rocketTs.updateUi();
                    }).change();
                }).call(this, this);
            }
            return TypeSelection;
        }());
        rocketTs.ready(function ($) {
            rocketTs.registerUiInitFunction(".rocket-type-dependent-entry-form", function (elemEntryForm) {
                new TypeSelection(elemEntryForm);
            });
        });
    })(edit = spec.edit || (spec.edit = {}));
})(spec || (spec = {}));
/// <reference path="..\rocket.ts" />
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
 */
var tools;
(function (tools) {
    var MailCenter = (function () {
        function MailCenter(elem) {
            this.elem = elem;
            elem.find("article").each(function () {
                var elem = $(this);
                var elemMessage = elem.children("dl:first");
                elem.children("header:first").click(function () {
                    elemMessage.slideToggle();
                });
                elemMessage.hide();
            });
        }
        return MailCenter;
    }());
    rocketTs.ready(function () {
        var elemMailCenter = $("#rocket-tools-mail-center");
        if (elemMailCenter.length === 0)
            return;
        new MailCenter(elemMailCenter);
    });
})(tools || (tools = {}));
/// <reference path="..\rocket.ts" />
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
 */
var l10n;
(function (l10n) {
    $ = jQuery;
    var TranslationViewSwitch = (function () {
        function TranslationViewSwitch(defaultLabel, translationsOnlyLabel) {
            this.elemContainer = $("<ul />", {
                "class": "rocket-translation-view-switch"
            });
            this.elemStandard = $("<li />", {
                "text": defaultLabel
            }).addClass(TranslationViewSwitch.CLASS_NAME_ACTIVE).appendTo(this.elemContainer);
            this.elemTranslationOnly = $("<li />", {
                "text": translationsOnlyLabel
            }).addClass("rocket-active").appendTo(this.elemContainer);
            this.elemForClass = $("#rocket-content-container");
            (function (that) {
                that.elemStandard.click(function () {
                    that.elemForClass.removeClass(TranslationViewSwitch.CLASS_NAME_TRANSLATION_ONLY);
                    that.elemTranslationOnly.removeClass(TranslationViewSwitch.CLASS_NAME_ACTIVE);
                    that.elemStandard.addClass(TranslationViewSwitch.CLASS_NAME_ACTIVE);
                });
                that.elemTranslationOnly.click(function () {
                    that.elemForClass.addClass(TranslationViewSwitch.CLASS_NAME_TRANSLATION_ONLY);
                    that.elemTranslationOnly.addClass(TranslationViewSwitch.CLASS_NAME_ACTIVE);
                    that.elemStandard.removeClass(TranslationViewSwitch.CLASS_NAME_ACTIVE);
                });
            }).call(this, this);
        }
        TranslationViewSwitch.prototype.getElemContainer = function () {
            return this.elemContainer;
        };
        TranslationViewSwitch.CLASS_NAME_TRANSLATION_ONLY = "rocket-translation-only";
        TranslationViewSwitch.CLASS_NAME_ACTIVE = "rocket-active";
        return TranslationViewSwitch;
    }());
    var TranslationEnabler = (function () {
        function TranslationEnabler(elem) {
            this.activationCallbacks = [];
            this.deactivationCallbacks = [];
            this.elem = elem;
            elem.parent(".rocket-controls").prev("label").hide();
            this.elemToolPanel = elem.parents(".rocket-tool-panel:first");
            this.elemProperties = this.elemToolPanel.next(".rocket-properties:first");
            //			if (this.elem.children().length === 1) {
            //				elem.parents("li:first").hide();
            //				elem.find("input[type=checkbox]").prop("checked", true);
            //				return;
            //			}
            this.elemContainer = $("<div />").css({
                "position": "relative"
            }).insertBefore(elem);
            this.elemActivator = $("<a />", {
                "href": "#",
                "class": "rocket-translation-enabler-activator",
                "text": elem.data("active-locales-label")
            }).appendTo(this.elemContainer);
            this.elem.css({
                "position": "absolute",
                "left": "0",
                "top": this.elemActivator.outerHeight(true)
            }).appendTo(this.elemContainer).hide();
            (function (that) {
                that.elemActivator.click(function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (that.elemActivator.hasClass("rocket-open")) {
                        that.hide();
                    }
                    else {
                        that.show();
                    }
                });
                that.elem.click(function (e) {
                    e.stopPropagation();
                });
                this.elem.find("[data-locale-id]").each(function () {
                    var elemLi = $(this), elemCheckbox = elemLi.children("input[type=checkbox]"), localeId = elemLi.data("locale-id");
                    if (elemLi.data("mandatory")) {
                        if (!elemCheckbox.prop("checked")) {
                            elemCheckbox.prop("checked", true);
                            that.triggerActivationCallbacks(localeId);
                        }
                        elemCheckbox.clone().removeAttr("name").insertBefore(elemCheckbox).prop("disabled", true);
                        elemCheckbox.removeAttr("id").hide();
                    }
                    else {
                        elemCheckbox.change(function () {
                            if (elemCheckbox.prop("checked")) {
                                that.triggerActivationCallbacks(localeId);
                            }
                            else {
                                that.triggerDeactivationCallbacks(localeId);
                            }
                        });
                    }
                });
                that.elemProperties.addClass("rocket-translation-container");
                if (that.elemProperties.data("translation-enablers")) {
                    that.elemProperties.data("translation-enablers").push(this);
                }
                else {
                    that.elemProperties.data("translation-enablers", [this]);
                }
                ;
            }).call(this, this);
        }
        TranslationEnabler.prototype.hide = function () {
            this.elem.hide();
            this.elemActivator.removeClass("rocket-open");
            $(window).off("click.translationEnabler");
        };
        TranslationEnabler.prototype.show = function () {
            var that = this;
            this.elem.show();
            this.elemActivator.addClass("rocket-open");
            $(window).on("click.translationEnabler", function () {
                that.hide();
            });
        };
        TranslationEnabler.prototype.activate = function (localeId) {
            this.elem.find("[data-locale-id=" + localeId + "] > input[type=checkbox]").each(function () {
                $(this).prop("checked", true).change();
            });
        };
        TranslationEnabler.prototype.isActive = function (localeId) {
            return this.elem.find("[data-locale-id=" + localeId + "] > input[type=checkbox]").prop("checked");
        };
        TranslationEnabler.prototype.triggerActivationCallbacks = function (localeId) {
            this.activationCallbacks.forEach(function (activationCallback) {
                activationCallback(localeId);
            });
        };
        TranslationEnabler.prototype.registerActivationCallback = function (activationCallback) {
            this.activationCallbacks.push(activationCallback);
        };
        TranslationEnabler.prototype.registerDeactivationCallback = function (deactivationCallback) {
            this.deactivationCallbacks.push(deactivationCallback);
        };
        TranslationEnabler.prototype.triggerDeactivationCallbacks = function (localeId) {
            this.deactivationCallbacks.forEach(function (deactivationCallback) {
                deactivationCallback(localeId);
            });
        };
        TranslationEnabler.prototype.getElem = function () {
            return this.elem;
        };
        return TranslationEnabler;
    }());
    var TranslationEnablerManager = (function () {
        function TranslationEnablerManager() {
            this.translationEnablers = [];
        }
        TranslationEnablerManager.prototype.initializeElement = function (elemTranslationEnabler) {
            var that = this;
            elemTranslationEnabler.each(function () {
                that.translationEnablers.push(new TranslationEnabler($(this)));
            });
        };
        return TranslationEnablerManager;
    }());
    var NotSelectedTag = (function () {
        function NotSelectedTag(localeId, localeSelector) {
            this.elem = $("<li />", {
                "class": "rocket-locale-not-selected-" + localeId
            }).append($("<span />", {
                text: localeSelector.getLocaleLabel(localeId)
            }));
            (function (that) {
                that.elem.click(function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    localeSelector.selectLocaleWithId(localeId);
                    that.elem.remove();
                });
            }).call(this, this);
        }
        NotSelectedTag.prototype.getElem = function () {
            return this.elem;
        };
        return NotSelectedTag;
    }());
    var SelectedTag = (function () {
        function SelectedTag(localeId, localeSelector) {
            this.elem = $("<li />");
            this.elemText = $("<span />", {
                "text": localeSelector.getLocaleLabel(localeId)
            }).appendTo(this.elem);
            (function (that) {
                this.elemRemove = rocketTs.creatControlElem("Text: Remove Language", function () {
                    that.elem.remove();
                    localeSelector.removeSelectedLocaleWithId(localeId);
                }, "fa fa-times").removeClass("rocket-control").appendTo(this.elem);
                localeSelector.getElemUlSelectedContainer().on("localeChange", function () {
                    if ($(this).children().length === 1) {
                        that.elemRemove.hide();
                    }
                    else {
                        that.elemRemove.show();
                    }
                });
            }).call(this, this);
        }
        SelectedTag.prototype.getElem = function () {
            return this.elem;
        };
        return SelectedTag;
    }());
    var TranslationEntry = (function () {
        function TranslationEntry(localeSelector, elem) {
            this.active = true;
            this.elem = elem;
            this.localeId = elem.data("locale-id");
            this.error = elem.hasClass("rocket-has-error");
            this.translationEnablers = elem.parents(".rocket-translation-container:first").data("translation-enablers") || [];
            (function (that) {
                localeSelector.registerSelectionCallback(function (localeId) {
                    if (localeId !== that.localeId)
                        return;
                    that.show();
                });
                localeSelector.registerRemoveSelectionCallback(function (localeId) {
                    if (localeId !== that.localeId)
                        return;
                    that.hide();
                });
                if (this.translationEnablers.length > 0) {
                    that.elemLocaleControls = elem.find(".rocket-locale-controls:first");
                    var entryFormCommand = new ui.EntryFormCommand("Activate " + localeSelector.getLocaleLabel(that.localeId), function () {
                        that.translationEnablers.forEach(function (translationEnabler) {
                            translationEnabler.activate(that.localeId);
                        });
                    }, "fa fa-language");
                    that.elemActivate = entryFormCommand.getElemContainer().addClass("rocket-translation-activator");
                    var active = false;
                    that.translationEnablers.forEach(function (translationEnabler) {
                        active = active || translationEnabler.isActive(that.localeId);
                    });
                    if (!active) {
                        that.deactivate();
                    }
                    that.translationEnablers.forEach(function (translationEnabler) {
                        translationEnabler.registerActivationCallback(function (localeId) {
                            if (localeId !== that.localeId)
                                return;
                            that.activate();
                        });
                        translationEnabler.registerDeactivationCallback(function (localeId) {
                            if (localeId !== that.localeId)
                                return;
                            that.deactivate();
                        });
                    });
                }
            }).call(this, this);
        }
        TranslationEntry.prototype.hasError = function () {
            return this.error;
        };
        TranslationEntry.prototype.show = function () {
            this.elem.show();
            //this.elemActivate.show();
        };
        TranslationEntry.prototype.hide = function () {
            this.elem.hide();
            //this.elemActivate.hide();
        };
        TranslationEntry.prototype.activate = function () {
            if (this.active)
                return;
            this.elemActivate.detach();
            this.elemLocaleControls.children().show();
            rocketTs.updateUi();
            this.active = true;
        };
        TranslationEntry.prototype.deactivate = function () {
            if (!this.active)
                return;
            this.elemLocaleControls.children().hide();
            this.elemActivate.prependTo(this.elemLocaleControls);
            this.active = false;
        };
        return TranslationEntry;
    }());
    var LocaleSelector = (function () {
        function LocaleSelector(tem, languagesLabel, defaultLabel, translationsOnlyLabel) {
            this.localeLabels = {};
            this.selectedLocaleIds = [];
            this.notSelectedLocaleIds = [];
            this.initialized = false;
            this.selectionCallbacks = [];
            this.removeSelectionCallbacks = [];
            this.tem = tem;
            this.elemToolbar = $("#rocket-toolbar");
            if (this.elemToolbar.length === 0) {
                this.elemToolbar = $("<div />", { "id": "rocket-toolbar" })
                    .insertAfter($(".rocket-panel:first > h3:first"));
            }
            this.elemContainer = $("<div />", {
                "class": "rocket-locale-selection"
            }).appendTo(this.elemToolbar);
            this.elemUlSelectedContainer = $("<ul />", {
                "class": "rocket-selected-locales"
            }).appendTo(this.elemContainer);
            this.elemLabel = $("<a />", {
                "text": languagesLabel,
                "href": "#"
            }).appendTo(this.elemContainer);
            this.elemUlNotSelectedContainer = $("<ul />", {
                "class": "rocket-not-selected-locales"
            }).appendTo(this.elemContainer).hide();
            (function (that) {
                that.elemLabel.click(function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (that.elemContainer.hasClass("open")) {
                        that.close();
                    }
                    else {
                        that.open();
                    }
                });
                var translationSwitch = new TranslationViewSwitch(defaultLabel, translationsOnlyLabel);
                translationSwitch.getElemContainer().prependTo(this.elemToolbar);
            }).call(this, this);
        }
        LocaleSelector.prototype.initialize = function () {
            this.initialized = true;
            this.initSelectedLocales();
            this.initNotSelectedLocales();
        };
        LocaleSelector.prototype.initSelectedLocales = function () {
            var that = this;
            this.getSavedLocaleIds().forEach(function (localeId) {
                that.selectLocaleWithId(localeId);
            });
        };
        LocaleSelector.prototype.initNotSelectedLocales = function () {
            var selectedLocaleId = null, that = this;
            this.notSelectedLocaleIds.forEach(function (localeId) {
                if (that.selectedLocaleIds.length === 0
                    && null === selectedLocaleId) {
                    //If no locale is selected then select the first one					
                    //need to remember it here, if you push it directly, the array 
                    //will change internaly and the element after will be ignored
                    selectedLocaleId = localeId;
                }
                else {
                    that.addNotSelectedLocaleWithId(localeId);
                }
            });
            if (null !== selectedLocaleId) {
                that.selectLocaleWithId(selectedLocaleId);
            }
        };
        LocaleSelector.prototype.hasLocaleId = function (localeId) {
            return this.isLocaleIdSelected(localeId) || (this.notSelectedLocaleIds.indexOf(localeId) >= 0);
        };
        LocaleSelector.prototype.getLocaleLabel = function (localeId) {
            if (!this.localeLabels.hasOwnProperty(localeId))
                return localeId;
            return this.localeLabels[localeId];
        };
        LocaleSelector.prototype.initializeLocalizedElems = function (localizedElem) {
            var that = this;
            localizedElem.each(function () {
                var elem = $(this), localeId = elem.data("locale-id");
                if (!that.hasLocaleId(localeId)) {
                    that.notSelectedLocaleIds.push(localeId);
                    that.localeLabels[localeId] = elem.data("pretty-locale-id");
                }
                var translationEntry = new TranslationEntry(that, elem);
                if (!that.isLocaleIdSelected(localeId) && !translationEntry.hasError()) {
                    translationEntry.hide();
                }
                if (that.initialized)
                    return;
                that.initSelectedLocales();
                that.initNotSelectedLocales();
            });
            //			if (that.notSelectedLocaleIds.length <= 1) {
            //				//just one locale is available -> show elements like not translatable 	
            //				$(".rocket-properties [data-locale-id]").each(function() {
            //					var elem = $(this).show();	
            //					//elem.show().children("label:first").remove();
            //					//elem.parent().replaceWith(elem.children("div.rocket-controls").contents());
            //				});
            //				//that.elemContainer.remove();
            //				if (that.elemToolbar.children().length === 1) {
            //					this.elemToolbar.remove();	
            //				}
            //				return;
            //			}
        };
        LocaleSelector.prototype.open = function () {
            if (this.notSelectedLocaleIds.length === 0)
                return;
            this.elemContainer.addClass("open");
            this.elemUlNotSelectedContainer.show();
            var that = this;
            $(window).off("click.localeSelector").on("click.localeSelector", function () {
                that.close();
            });
        };
        LocaleSelector.prototype.close = function () {
            $(window).off("click.localeSelector");
            this.elemContainer.removeClass("open");
            this.elemUlNotSelectedContainer.hide();
        };
        LocaleSelector.prototype.getElemUlSelectedContainer = function () {
            return this.elemUlSelectedContainer;
        };
        LocaleSelector.prototype.getSavedLocaleIds = function () {
            var cookieValue = rocketTs.getCookie(LocaleSelector.COOKIE_NAME_SELECTED_LOCALE_IDS);
            if (!cookieValue)
                return [];
            return cookieValue.split(",");
        };
        LocaleSelector.prototype.saveState = function () {
            var savedLocaleIds = this.getSavedLocaleIds();
            this.selectedLocaleIds.forEach(function (value) {
                if (savedLocaleIds.indexOf(value) !== -1)
                    return;
                savedLocaleIds.push(value);
            });
            this.notSelectedLocaleIds.forEach(function (value) {
                if (savedLocaleIds.indexOf(value) === -1)
                    return;
                savedLocaleIds.splice(savedLocaleIds.indexOf(value), 1);
            });
            rocketTs.setCookie(LocaleSelector.COOKIE_NAME_SELECTED_LOCALE_IDS, savedLocaleIds.join(","));
            this.elemUlSelectedContainer.trigger("localeChange");
        };
        LocaleSelector.prototype.isLocaleIdSelected = function (localeId) {
            return this.selectedLocaleIds.indexOf(localeId) >= 0;
        };
        LocaleSelector.prototype.registerSelectionCallback = function (selectionCallback) {
            this.selectionCallbacks.push(selectionCallback);
        };
        LocaleSelector.prototype.triggerSelectionCallbacks = function (localeId) {
            this.selectionCallbacks.forEach(function (selectionCallback) {
                selectionCallback(localeId);
            });
        };
        LocaleSelector.prototype.selectLocaleWithId = function (localeId) {
            if (this.notSelectedLocaleIds.indexOf(localeId) === -1)
                return;
            var selectedTag = new SelectedTag(localeId, this);
            this.elemUlSelectedContainer.append(selectedTag.getElem());
            this.selectedLocaleIds.push(localeId);
            this.notSelectedLocaleIds.splice(this.notSelectedLocaleIds.indexOf(localeId), 1);
            // this.tem.activate(localeId);
            this.triggerSelectionCallbacks(localeId);
            this.saveState();
            if (this.notSelectedLocaleIds.length === 0) {
                this.close();
            }
        };
        LocaleSelector.prototype.registerRemoveSelectionCallback = function (removeSelectionCallback) {
            this.removeSelectionCallbacks.push(removeSelectionCallback);
        };
        LocaleSelector.prototype.triggerRemoveSelectionCallbacks = function (localeId) {
            this.removeSelectionCallbacks.forEach(function (removeSelectionCallback) {
                removeSelectionCallback(localeId);
            });
        };
        LocaleSelector.prototype.removeSelectedLocaleWithId = function (localeId) {
            if (this.selectedLocaleIds.indexOf(localeId) === -1)
                return;
            this.notSelectedLocaleIds.push(localeId);
            this.selectedLocaleIds.splice(this.selectedLocaleIds.indexOf(localeId), 1);
            this.addNotSelectedLocaleWithId(localeId);
            this.triggerRemoveSelectionCallbacks(localeId);
            //this.tem.deactivate(localeId);
            this.saveState();
        };
        LocaleSelector.prototype.addNotSelectedLocaleWithId = function (localeId) {
            if (this.elemUlNotSelectedContainer.children("li.rocket-locale-not-selected-" + localeId).length > 0)
                return;
            var selectedTag = new NotSelectedTag(localeId, this);
            this.elemUlNotSelectedContainer.append(selectedTag.getElem());
        };
        LocaleSelector.COOKIE_NAME_SELECTED_LOCALE_IDS = "selectedLocaleIds";
        return LocaleSelector;
    }());
    rocketTs.ready(function () {
        var localeSelector = null, tem = new TranslationEnablerManager();
        rocketTs.registerUiInitFunction(".rocket-translation-enabler", function (elem) {
            tem.initializeElement(elem);
        });
        rocketTs.registerUiInitFunction(".rocket-properties > [data-locale-id]", function (localizedElem) {
            if (null === localeSelector) {
                var languagesLabel = null, translationsOnlyLabel = null, defaultLabel = null, elemTranslatableContent = $(".rocket-translatable-content:first");
                if (elemTranslatableContent.length > 0) {
                    languagesLabel = elemTranslatableContent.data("languages-label");
                    defaultLabel = elemTranslatableContent.data("standard-label");
                    translationsOnlyLabel = elemTranslatableContent.data("translations-only-label");
                }
                if (null === languagesLabel) {
                    var elemTranslationEnabler = $(".rocket-translation-enabler:first");
                    languagesLabel = elemTranslationEnabler.data("languages-label");
                    defaultLabel = elemTranslationEnabler.data("standard-label");
                    translationsOnlyLabel = elemTranslationEnabler.data("translations-only-label");
                }
                if (null !== languagesLabel) {
                    localeSelector = new LocaleSelector(tem, languagesLabel, defaultLabel, translationsOnlyLabel);
                }
                else {
                    throw new Error("no languages label found");
                }
            }
            if (null === localeSelector)
                return;
            localeSelector.initializeLocalizedElems(localizedElem);
        });
        if (null !== localeSelector) {
            localeSelector.initialize();
        }
    });
})(l10n || (l10n = {}));
/// <reference path="..\..\rocket.ts" />
/// <reference path="toMany.ts" />
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
 */
var spec;
(function (spec) {
    var edit;
    (function (edit) {
        $ = jQuery;
        var ContentItemPanel = (function () {
            function ContentItemPanel(elemPanel, availableTypes) {
                this.elemPanel = elemPanel;
                this.name = elemPanel.data("name");
                this.allowedCiSpecIds = elemPanel.data("allowed-ci-spec-ids") || [];
                this.toMany = elemPanel.children(".rocket-to-many:first").data("to-many");
                this.initTypes(availableTypes);
            }
            ContentItemPanel.prototype.getName = function () {
                return this.name;
            };
            ContentItemPanel.prototype.initTypes = function (availableTypes) {
                var types = {}, that = this;
                $.each(availableTypes, function (specId, label) {
                    if (!that.isSpecIdAllowed(specId))
                        return;
                    types[specId] = label;
                });
                that.toMany.setTypes(types);
            };
            ContentItemPanel.prototype.isSpecIdAllowed = function (specId) {
                if (this.allowedCiSpecIds.length === 0)
                    return true;
                return this.allowedCiSpecIds.indexOf(specId) >= 0;
            };
            return ContentItemPanel;
        }());
        var ContentItems = (function () {
            function ContentItems(elem) {
                this.elem = elem;
                var availableTypes = elem.data("ci-ei-spec-labels");
                elem.children(".rocket-content-item-panel").each(function () {
                    new ContentItemPanel($(this), availableTypes);
                });
            }
            return ContentItems;
        }());
        rocketTs.ready(function () {
            rocketTs.registerUiInitFunction(".rocket-content-items", function (elemContentItems) {
                new ContentItems(elemContentItems);
            });
        });
    })(edit = spec.edit || (spec.edit = {}));
})(spec || (spec = {}));
/// <reference path="../rocket.ts" />
/// <reference path="../ui/panels.ts" />
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
 */
var spec;
(function (spec) {
    $ = jQuery;
    var AsideContainer = (function () {
        function AsideContainer(elemContainer) {
            this.elemContainer = elemContainer;
            this.elemMainContainer = $("<div />", {
                "class": "rocket-main-bundle"
            });
            this.elemAsideContainer = $("<div />", {
                "class": "rocket-aside-bundle"
            });
            this.elemContainer.children(":not(.rocket-control-group-aside)").appendTo(this.elemMainContainer);
            this.elemContainer.children(".rocket-control-group-aside").appendTo(this.elemAsideContainer);
            this.elemMainContainer.appendTo(this.elemContainer);
            this.elemAsideContainer.appendTo(this.elemContainer);
        }
        return AsideContainer;
    }());
    rocketTs.ready(function () {
        rocketTs.registerUiInitFunction(".rocket-aside-container", function (elem) {
            new AsideContainer(elem);
        });
        rocketTs.registerUiInitFunction(".rocket-control-group-main", function (elem) {
            var elemNextMainContainer = elem.next(".rocket-control-group-main");
            if (elemNextMainContainer.length === 0)
                return;
            var elemPanelGroup = $("<div />", {
                "class": "rocket-grouped-panels"
            });
            elemPanelGroup.insertBefore(elem).append(elem);
            var tmpElemNextMainContainer;
            do {
                tmpElemNextMainContainer = elemNextMainContainer.next(".rocket-control-group-main");
                elemPanelGroup.append(rocketTs.markAsInitialized(".rocket-control-group-main", elemNextMainContainer));
                elemNextMainContainer = tmpElemNextMainContainer;
            } while (elemNextMainContainer.length > 0);
            rocketTs.markAsInitialized(".rocket-grouped-panels", elemPanelGroup);
            new ui.PanelGroup(elemPanelGroup);
        });
    });
})(spec || (spec = {}));
/// <reference path="..\rocket.ts" />
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
 */
var spec;
(function (spec) {
    var $ = jQuery;
    var QuickSearchForm = (function () {
        function QuickSearchForm(overviewTools, elemContainer) {
            this.overviewTools = overviewTools;
            this.elemContainer = elemContainer;
            this.elemForm = elemContainer.children("form");
            this.applyFormSubmissions();
        }
        QuickSearchForm.prototype.applyFormSubmissions = function () {
            var that = this;
            this.elemForm.find("[type=submit]").click(function (e) {
                e.preventDefault();
                that.post($(this).attr("name"));
            });
            this.elemForm.submit(function (e) {
                e.preventDefault();
                that.elemForm.find("[type=submit]:first").click();
            });
        };
        QuickSearchForm.prototype.post = function (methName) {
            this.overviewTools.postForm(methName, new FormData(this.elemForm.get(0)));
        };
        return QuickSearchForm;
    }());
    var CritmodSortConstraint = (function () {
        function CritmodSortConstraint(critmodSort, elemLi) {
            this.critmodSort = critmodSort;
            this.elemLi = elemLi;
            this.elemSelectPropertyName = elemLi.find("select:first").hide();
            this.elemLabel = $("<span />").insertAfter(this.elemSelectPropertyName);
            (function (that) {
                that.elemRemove = rocketTs.creatControlElem("Text: remove", function () {
                    that.remove();
                }, "fa fa-times").appendTo(elemLi);
                elemLi.on('clear.critmod', function () {
                    that.remove();
                });
            }).call(this, this);
        }
        CritmodSortConstraint.prototype.setProperty = function (propertyName, label) {
            this.elemSelectPropertyName.val(propertyName);
            this.elemLabel.text(label);
        };
        CritmodSortConstraint.prototype.getPropertyName = function () {
            return this.elemSelectPropertyName.val();
        };
        CritmodSortConstraint.prototype.getLabel = function () {
            return this.elemLabel.text();
        };
        CritmodSortConstraint.prototype.remove = function () {
            this.critmodSort.addProperty(this.getPropertyName());
            this.elemLi.remove();
        };
        CritmodSortConstraint.prototype.getElemLi = function () {
            return this.elemLi;
        };
        return CritmodSortConstraint;
    }());
    var CritmodSort = (function () {
        function CritmodSort(elemUl, textAddSortLabel) {
            this.elemUl = elemUl;
            this.elemButtonAdd = $("<a />", {
                "text": textAddSortLabel,
                "class": "rocket-control"
            }).insertAfter(elemUl);
            this.elemContent = $("<div />").insertAfter(elemUl);
            this.sortFields = elemUl.data("sort-fields");
            (function (that) {
                var availableProperties = [];
                that.elemEmptyConstraint = that.elemUl.children(".rocket-empty-sort-constraint").detach();
                that.elemUl.children().each(function () {
                    var constraint = new CritmodSortConstraint(that, $(this));
                    availableProperties.push(constraint.getPropertyName());
                });
                that.elemUlProperties = $("<ul />", {
                    "class": "rocket-multi-add-entries"
                }).appendTo(that.elemContent);
                $.each(that.sortFields, function (propertyName, label) {
                    if (availableProperties.indexOf(propertyName) >= 0)
                        return;
                    that.addProperty(propertyName, label);
                });
                new ui.MultiAdd(that.elemButtonAdd, that.elemContent, ui.MultiAdd.ALIGNMENT_RIGHT);
            }).call(this, this);
        }
        CritmodSort.prototype.addProperty = function (propertyName, label) {
            if (label === void 0) { label = null; }
            if (null === label) {
                label = this.sortFields[propertyName];
            }
            var that = this;
            var elemLi = $("<li />").appendTo(that.elemUlProperties);
            rocketTs.creatControlElem(label, function () {
                var constraint = that.requestConstraint(propertyName, label);
                constraint.getElemLi().appendTo(that.elemUl);
                elemLi.remove();
            }).removeClass("rocket-control").appendTo(elemLi);
        };
        CritmodSort.prototype.clear = function () {
            this.elemUl.children().trigger('clear.critmod');
        };
        CritmodSort.prototype.requestConstraint = function (propertyName, label) {
            var constraint = new CritmodSortConstraint(this, this.elemEmptyConstraint.clone());
            constraint.setProperty(propertyName, label);
            return constraint;
        };
        return CritmodSort;
    }());
    var CritmodForm = (function () {
        function CritmodForm(overviewTools, elemCritmod) {
            this.overviewTools = overviewTools;
            this.elemCritmod = elemCritmod;
            this.initialize(elemCritmod.children(".rocket-critmod-form:first"));
        }
        CritmodForm.prototype.initialize = function (elemForm) {
            this.elemForm = elemForm;
            this.elemSelectFilter = elemForm.find(".rocket-critmod-save-select select:first");
            this.elemConfiguration = elemForm.find(".rocket-critmod-configuration:first").hide();
            this.formUrl = elemForm.attr("action");
            this.elemToggleConfiguration = $("<a />", {
                "href": "#",
                "class": "rocket-control rocket-critmod-configuration-opener"
            }).insertAfter(this.elemSelectFilter);
            (function (that) {
                var elemSubmitSelect = elemForm.find(".rocket-critmod-select[type=submit]:first");
                that.methNameSelect = elemSubmitSelect.attr("name");
                elemSubmitSelect.remove();
                that.elemSelectFilter.change(function () {
                    that.post(that.methNameSelect);
                });
                that.elemConfiguration.hide();
                var elemIconToggle = $("<i />", {
                    "class": "fa fa-cogs"
                }).appendTo(that.elemToggleConfiguration);
                that.elemToggleConfiguration.click(function (e) {
                    e.preventDefault();
                    if (that.elemToggleConfiguration.hasClass("open")) {
                        that.close();
                    }
                    else {
                        that.open();
                    }
                    that.overviewTools.getElem().trigger('heightChange');
                });
                that.critmodSort = new CritmodSort(elemForm.find(".rocket-sort:first"), "Add Sort");
                that.applyFormSubmissions();
            }).call(this, this);
        };
        CritmodForm.prototype.open = function (immediately) {
            if (immediately === void 0) { immediately = false; }
            var that = this;
            this.elemToggleConfiguration.addClass("open");
            if (immediately) {
                this.elemConfiguration.show();
                this.overviewTools.getElem().trigger('heightChange');
            }
            else {
                this.elemConfiguration.stop(true, true).slideDown({
                    duration: 200,
                    step: function () {
                        that.overviewTools.getElem().trigger('heightChange');
                    }
                });
            }
        };
        CritmodForm.prototype.close = function () {
            var that = this;
            this.elemToggleConfiguration.removeClass("open");
            this.elemConfiguration.stop(true, true).slideUp({
                duration: 200,
                step: function () {
                    that.overviewTools.getElem().trigger('heightChange');
                }
            });
        };
        CritmodForm.prototype.isFilterSelected = function () {
            return !!this.elemSelectFilter.val();
        };
        CritmodForm.prototype.hideFilterSubmitElems = function () {
            this.elemsFilterSubmitElems.each(function () {
                $(this).parents("li:first").hide();
            });
        };
        CritmodForm.prototype.showFilterSubmitElems = function () {
            this.elemsFilterSubmitElems.each(function () {
                $(this).parents("li:first").show();
            });
        };
        CritmodForm.prototype.applyFormSubmissions = function () {
            var elemSubmitApply = this.elemForm.find(".rocket-critmod-submit-apply:first"), that = this;
            elemSubmitApply.click(function (e) {
                e.preventDefault();
                that.post(elemSubmitApply.attr("name"));
            });
            this.elemsFilterSubmitElems = this.elemForm.find(".rocket-critmod-submit-save, .rocket-critmod-submit-delete");
            this.elemsFilterSubmitElems.click(function (e) {
                e.preventDefault();
                that.post($(this).attr("name"));
            });
            if (!this.isFilterSelected()) {
                this.hideFilterSubmitElems();
            }
            var elemSubmitClear = this.elemForm.find(".rocket-critmod-submit-clear");
            elemSubmitClear.click(function (e) {
                e.preventDefault();
                that.clear();
            });
            this.elemForm.submit(function (e) {
                e.preventDefault();
                elemSubmitApply.click();
            });
        };
        CritmodForm.prototype.clear = function () {
            this.critmodSort.clear();
        };
        CritmodForm.prototype.post = function (methName) {
            //this.overviewContent.clear();
            //this.setLoading(true);
            var formData = new FormData(this.elemForm.get(0));
            formData.append(methName, true);
            var that = this;
            $.ajax({
                "url": this.formUrl,
                "type": "POST",
                "data": formData,
                "processData": false,
                "contentType": false,
                "success": function (data) {
                    that.elemCritmod.empty();
                    var elemCritmod = $($.parseHTML(n2n.dispatch.analyze(data))), additionalData = data['additional'];
                    that.initialize(elemCritmod.children(".rocket-critmod-form:first").appendTo(that.elemCritmod));
                    rocketTs.updateUi();
                    that.open(true);
                    if (additionalData['valid']) {
                        that.overviewTools.reloadContent();
                    }
                }
            });
        };
        return CritmodForm;
    }());
    var Pagination = (function () {
        function Pagination(overviewContent, pageNo, numPages, overviewPath) {
            if (overviewPath === void 0) { overviewPath = null; }
            this.elemsPreloadedPage = {};
            this.loadingJqXHR = null;
            this.preloadingJqXHRs = [];
            this.contentHeight = null;
            this.firstLoadedPageNo = null;
            this.lastLoadedPageNo = null;
            this.pageChangePosition = null;
            this.overviewContent = overviewContent;
            this.overviewTools = overviewContent.getOverviewTools();
            this.overviewPath = overviewPath;
            this.elemContainer = $("<div />", {
                "class": "rocket-overview-pagination"
            });
            this.elemFirstPage = $("<a />", {
                "href": "#",
                "class": "rocket-pagination-first rocket-control"
            }).append($("<span />", {
                "text": 1
            })).append($("<i />", {
                "class": "fa fa-step-backward"
            })).appendTo(this.elemContainer);
            this.elemPrevPage = $("<a />", {
                "href": "#",
                "class": "rocket-pagination-prev rocket-control"
            }).append($("<i />", {
                "class": "fa fa-chevron-left"
            })).appendTo(this.elemContainer);
            this.elemPageNo = $("<input />", {
                "class": "rocket-pagination-current",
                "type": "text"
            }).appendTo(this.elemContainer);
            this.elemNextPage = $("<a />", {
                "href": "#",
                "class": "rocket-pagination-next rocket-control"
            }).append($("<i />", {
                "class": "fa fa-chevron-right"
            })).appendTo(this.elemContainer);
            this.elemLastPage = $("<a />", {
                "href": "#",
                "class": "rocket-pagination-last rocket-control"
            }).append($("<i />", {
                "class": "fa fa-step-forward"
            })).append($("<span />", {
                "text": numPages
            })).appendTo(this.elemContainer);
            (function (that) {
                that.elemFirstPage.click(function (e) {
                    e.preventDefault();
                    that.loadPage(1);
                });
                that.elemPrevPage.click(function (e) {
                    e.preventDefault();
                    if (that.pageNo <= 1)
                        return;
                    that.loadPage(that.pageNo - 1);
                });
                that.elemNextPage.click(function (e) {
                    e.preventDefault();
                    if (that.pageNo >= that.numPages)
                        return;
                    that.loadPage(that.pageNo + 1);
                });
                that.elemLastPage.click(function (e) {
                    e.preventDefault();
                    that.loadPage(that.numPages);
                });
                that.elemPageNo.keydown(function (e) {
                    if (e.which == 13) {
                        e.preventDefault();
                        that.loadPage(that.purifyPageNo(that.elemPageNo.val()));
                    }
                }).focus(function () {
                    $(this).select();
                });
                that.firstLoadedPageNo = pageNo;
                that.lastLoadedPageNo = pageNo;
                that.setCurrentPageNo(pageNo);
                that.setNumPages(numPages);
                that.preloadPages(pageNo);
            }).call(this, this);
        }
        Pagination.prototype.getFirstLoadedPageNo = function () {
            return this.firstLoadedPageNo;
        };
        Pagination.prototype.applyElemFixedContainer = function (elemFixedContainer) {
            var that = this;
            this.elemFixedContainer = elemFixedContainer;
            elemFixedContainer.scroll(function (e) {
                if (that.overviewContent.isScrolling())
                    return;
                if (that.overviewContent.isInSelectionMode())
                    return;
                that.checkNextPageLoad();
                that.determineCurrentPage();
            }).scroll();
            this.pageChangePosition = null;
        };
        Pagination.prototype.purifyPageNo = function (dirtyPageNo) {
            var pageNo = parseInt(dirtyPageNo);
            if (isNaN(pageNo) || pageNo < 1) {
                pageNo = 1;
            }
            else if (pageNo > this.numPages) {
                pageNo = this.numPages;
            }
            return pageNo;
        };
        Pagination.prototype.checkNextPageLoad = function () {
            if (this.isLoading())
                return;
            if (this.pageNo >= this.numPages)
                return;
            var elemFixedContainer = this.elemFixedContainer, containerHeight = elemFixedContainer.outerHeight(), offset = containerHeight / 3, contentHeight = elemFixedContainer.get(0).scrollHeight;
            if (contentHeight === 0)
                return;
            if ((contentHeight - offset) < elemFixedContainer.scrollTop() + containerHeight) {
                this.loadPage(this.pageNo + 1, true);
            }
        };
        Pagination.prototype.hasPage = function (pageNo) {
            return this.firstLoadedPageNo <= pageNo && this.lastLoadedPageNo >= pageNo;
        };
        Pagination.prototype.determineCurrentPage = function () {
            var lastPageNo = this.firstLoadedPageNo, that = this;
            if (null === this.pageChangePosition) {
                this.pageChangePosition = this.elemFixedContainer.offset().top + (this.elemFixedContainer.outerHeight() / 3 * 2);
            }
            $.each(this.overviewContent.getPageOffsets(), function (pageNo, offsetTop) {
                if (offsetTop > that.pageChangePosition)
                    return false;
                lastPageNo = pageNo;
            });
            if (lastPageNo === this.pageNo)
                return;
            this.setCurrentPageNo(lastPageNo);
        };
        Pagination.prototype.isLoading = function () {
            return null !== this.loadingJqXHR;
        };
        Pagination.prototype.resetPreloading = function () {
            $.each(this.preloadingJqXHRs, function (pageNo, jqXhr) {
                if (jqXhr) {
                    jqXhr.abort();
                }
            });
            this.elemsPreloadedPage = {};
        };
        Pagination.prototype.setLoading = function () {
            this.elemFirstPage.prop("disabled", true);
            this.elemPrevPage.prop("disabled", true);
            this.elemNextPage.prop("disabled", true);
            this.elemLastPage.prop("disabled", true);
            this.resetPreloading();
            this.overviewTools.setLoading();
        };
        Pagination.prototype.resetLoading = function () {
            this.loadingJqXHR = null;
            this.elemFirstPage.prop("disabled", false);
            this.elemPrevPage.prop("disabled", false);
            this.elemNextPage.prop("disabled", false);
            this.elemLastPage.prop("disabled", false);
            this.overviewTools.setLoading(false);
            $(window).scroll();
        };
        Pagination.prototype.loadPage = function (pageNo, append) {
            if (append === void 0) { append = false; }
            if (this.isLoading())
                return;
            this.overviewContent.preparePageLoad();
            pageNo = parseInt(pageNo);
            if (this.numPages < pageNo) {
                throw new Error("Invalid page num");
            }
            if (this.hasPage(pageNo)) {
                if (!append) {
                    if (this.overviewContent.scrollToPage(pageNo)) {
                        this.setCurrentPageNo(pageNo);
                    }
                    ;
                }
                return;
            }
            if (append) {
                this.lastLoadedPageNo = pageNo;
            }
            else {
                this.firstLoadedPageNo = pageNo;
                this.lastLoadedPageNo = pageNo;
            }
            if (this.elemsPreloadedPage.hasOwnProperty(pageNo.toString())) {
                if (append) {
                    this.overviewContent.appendPage(this.elemsPreloadedPage[pageNo].clone(), pageNo);
                }
                else {
                    this.overviewContent.replaceContent(this.elemsPreloadedPage[pageNo].clone(), pageNo);
                }
                this.setCurrentPageNo(pageNo);
                this.elemFixedContainer.scroll();
            }
            else {
                var that = this;
                this.setCurrentPageNo(pageNo);
                this.setLoading();
                if (!append) {
                    this.overviewContent.clear();
                }
                this.loadingJqXHR = this.overviewTools.getContent(pageNo, function (elem, data) {
                    that.elemsPreloadedPage[pageNo] = elem.clone();
                    if (append) {
                        that.overviewContent.appendPage(elem, pageNo);
                    }
                    else {
                        that.overviewContent.replaceContent(elem, pageNo);
                    }
                    that.overviewContent.setNumEntries(data['numEntries']);
                    that.resetLoading();
                    that.setNumPages(data['numPages']);
                    that.elemFixedContainer.scroll();
                });
            }
            this.preloadPages(pageNo);
        };
        Pagination.prototype.updateUrl = function () {
            if (null !== this.overviewPath && typeof history !== 'undefined') {
                var path = this.overviewPath;
                if (this.pageNo > 1) {
                    path += "/" + this.pageNo;
                }
                history.pushState(null, null, path);
            }
        };
        Pagination.prototype.preloadPages = function (pageNo) {
            var numPages = 2;
            for (var i = (pageNo - numPages); i <= (pageNo + numPages); i++) {
                this.preloadPage(i);
            }
        };
        Pagination.prototype.preloadPage = function (pageNo) {
            if (pageNo < 1 || pageNo === this.pageNo || pageNo > this.numPages
                || this.preloadingJqXHRs.hasOwnProperty(pageNo.toString())
                || this.elemsPreloadedPage.hasOwnProperty(pageNo.toString())
                || this.hasPage(pageNo))
                return;
            var that = this;
            var preloadingJqXHR = this.overviewTools.getContent(pageNo, function (elem, data) {
                that.elemsPreloadedPage[pageNo.toString()] = elem;
                if (that.preloadingJqXHRs.hasOwnProperty(pageNo.toString())) {
                    delete that.preloadingJqXHRs[pageNo];
                }
            });
            this.preloadingJqXHRs[pageNo] = preloadingJqXHR;
        };
        Pagination.prototype.getElemContainer = function () {
            return this.elemContainer;
        };
        Pagination.prototype.setNumPages = function (numPages) {
            this.numPages = numPages;
            this.elemPageNo.attr("max", numPages);
        };
        Pagination.prototype.setCurrentPageNo = function (pageNo) {
            this.pageNo = parseInt(pageNo);
            this.elemPageNo.val(pageNo);
            if (this.pageNo == 1) {
                this.elemFirstPage.prop("disabled", true);
                this.elemPrevPage.prop("disabled", true);
            }
            if (this.pageNo == this.numPages) {
                this.elemNextPage.prop("disabled", true);
                this.elemLastPage.prop("disabled", true);
            }
            this.updateUrl();
        };
        return Pagination;
    }());
    var EntryRow = (function () {
        function EntryRow(overviewContent, elem, pageNo, selectable) {
            if (selectable === void 0) { selectable = true; }
            this.elem = elem;
            this.elemIdRep = elem.find(".rocket-entry-selector");
            this.elemCbx = this.elemIdRep.children("input[type=checkbox]:first");
            this.idRep = this.elemIdRep.data("entry-id-rep");
            this.elem.addClass('rocket-id-rep-' + this.idRep);
            this.pageNo = pageNo;
            (function (that) {
                if (selectable) {
                    that.elemCbx.change(function () {
                        if (that.elemCbx.is(":checked")) {
                            that.elem.addClass("selected");
                            that.elemCbx.trigger('changed.select');
                        }
                        else {
                            that.elem.removeClass("selected");
                            that.elemCbx.trigger('changed.select');
                            if (overviewContent.isInSelectionMode()) {
                                if (overviewContent.getPagination().hasPage(pageNo)) {
                                    that.elem.hide();
                                }
                                else {
                                    that.elem.remove();
                                }
                            }
                        }
                    }).change();
                }
                else {
                    that.setSelectable(false);
                }
                that.elem.data("entry-row", this);
                that.elem.click(function (e) {
                    var elemTarget = $(e.target);
                    if (elemTarget.is("a") || elemTarget.is("button") || elemTarget.is(that.elemCbx))
                        return;
                    var isClickable = false;
                    elemTarget.parentsUntil(that.elem).each(function () {
                        if (!$(this).is("a") && !$(this).is("button"))
                            return;
                        isClickable = true;
                        return false;
                    });
                    if (isClickable)
                        return;
                    that.select(!that.elemCbx.prop("checked"));
                });
            }).call(this, this);
        }
        EntryRow.prototype.getPageNo = function () {
            return this.pageNo;
        };
        EntryRow.prototype.getElem = function () {
            return this.elem;
        };
        EntryRow.prototype.getIdRep = function () {
            return this.idRep;
        };
        EntryRow.prototype.equals = function (obj) {
            return obj instanceof EntryRow && obj.getIdRep() === this.idRep;
        };
        EntryRow.prototype.select = function (select) {
            if (select === void 0) { select = true; }
            this.elemCbx.prop("checked", select).change();
        };
        EntryRow.prototype.isSelected = function () {
            return this.elemCbx.prop("checked");
        };
        EntryRow.prototype.setSelectable = function (selectable) {
            if (selectable) {
                this.elemCbx.appendTo(this.elem);
            }
            else {
                this.elemCbx.detach();
            }
        };
        return EntryRow;
    }());
    var OverviewContent = (function () {
        function OverviewContent(overviewTools, elemMainContent, numPages, numEntries, pageNo) {
            this.scrolling = false;
            this.inSelectionMode = false;
            this.selectable = true;
            this.overviewTools = overviewTools;
            this.elemMainContent = elemMainContent;
            this.elemContent = elemMainContent.find(".rocket-overview-content");
            this.overviewPath = elemMainContent.data("overview-path") || null;
            this.elemEntryControls = $("<div />", {
                "class": "rocket-overview-entry-controls"
            }).appendTo(overviewTools.getElem());
            this.elemEntryInfos = $("<div />", {
                "class": "rocket-overview-entry-infos"
            }).appendTo(this.elemEntryControls);
            this.elemNumEntries = $("<a />", {
                "href": "#",
                "class": "rocket-control"
            }).appendTo(this.elemEntryInfos);
            (function (that) {
                that.pagination = new Pagination(that, pageNo, numPages, that.overviewPath);
                if (numPages > 1) {
                    that.elemEntryControls.append(that.pagination.getElemContainer());
                }
                that.elemNumEntries.click(function (e) {
                    e.preventDefault();
                    that.inSelectionMode = false;
                    that.showAll();
                });
                that.setNumEntries(numEntries);
                that.elemNumSelectedEntries = $("<a />", {
                    "href": "#",
                    "class": "rocket-control"
                }).appendTo(that.elemEntryInfos).click(function (e) {
                    e.preventDefault();
                    that.inSelectionMode = true;
                    that.showSelected();
                });
                var numSelectedEntries = 0, first = true;
                that.elemContent.children("tr").each(function () {
                    var entryRow = new EntryRow(that, $(this), 1);
                    if (first) {
                        entryRow.getElem().addClass(OverviewContent.CLASS_NAME_NEW_ROW)
                            .attr("data-page-no", pageNo);
                        first = false;
                    }
                    if (!entryRow.isSelected())
                        return;
                    numSelectedEntries++;
                });
                that.setNumSelectedEntries(numSelectedEntries);
                that.elemContent.on('changed.select', function () {
                    that.setNumSelectedEntries(that.elemContent.children("tr.selected").length);
                });
            }).call(this, this);
        }
        OverviewContent.prototype.preparePageLoad = function () {
            this.elemNumEntries.click();
        };
        OverviewContent.prototype.isInSelectionMode = function () {
            return this.inSelectionMode;
        };
        OverviewContent.prototype.setSelectable = function (selectable) {
            if (selectable) {
                this.elemNumSelectedEntries.appendTo(this.elemEntryInfos);
            }
            else {
                this.elemNumSelectedEntries.detach();
            }
            this.elemContent.children("tr").each(function () {
                $(this).data("entry-row").setSelectable(selectable);
            });
            this.selectable = selectable;
        };
        OverviewContent.prototype.getElemMainContent = function () {
            return this.elemMainContent;
        };
        OverviewContent.prototype.getPagination = function () {
            return this.pagination;
        };
        OverviewContent.prototype.getOverviewTools = function () {
            return this.overviewTools;
        };
        OverviewContent.prototype.showSelected = function () {
            this.elemContent.children("tr:not(.selected)").hide();
            this.elemContent.children("tr.selected").show();
        };
        OverviewContent.prototype.getSelectedIdentityStrings = function () {
            var identityStrings = {};
            this.elemContent.find("tr.selected > .rocket-entry-selector").each(function () {
                var elemEntrySelector = $(this);
                identityStrings[elemEntrySelector.data("entry-id-rep")] = elemEntrySelector.data("identity-string");
            });
            return identityStrings;
        };
        OverviewContent.prototype.removeSelection = function () {
            this.elemContent.children("tr.selected").each(function () {
                $(this).data('entry-row').select(false);
            });
        };
        OverviewContent.prototype.showAll = function () {
            var that = this;
            this.elemContent.children("tr:not(.selected)").show();
            this.elemContent.children("tr.selected").each(function () {
                var elemEntryRow = $(this), entryRow = elemEntryRow.data('entry-row');
                if (entryRow.getPageNo() < that.pagination.getFirstLoadedPageNo()) {
                    elemEntryRow.hide();
                }
                else {
                    elemEntryRow.show();
                }
            });
        };
        OverviewContent.prototype.clear = function () {
            this.elemContent.children("tr:not(.selected)").remove();
            this.elemContent.children("tr.selected").hide();
        };
        OverviewContent.prototype.isScrolling = function () {
            return this.scrolling;
        };
        OverviewContent.prototype.getPageOffsets = function () {
            var pageOffsets = {};
            this.elemContent.children("tr." + OverviewContent.CLASS_NAME_NEW_ROW).each(function () {
                var elemRow = $(this);
                pageOffsets[elemRow.data("page-no")] = elemRow.offset().top;
            });
            return pageOffsets;
        };
        OverviewContent.prototype.replaceContent = function (elem, startPageNo) {
            if (startPageNo === void 0) { startPageNo = 1; }
            var that = this;
            this.elemContent.children("tr:not(.selected)").remove();
            this.elemContent.children("tr.selected").hide();
            this.appendPage(elem, startPageNo);
        };
        OverviewContent.prototype.appendRow = function (entryRow) {
            if (this.isSelected(entryRow)) {
                this.removeSelectedEquivalent(entryRow);
                entryRow.select();
            }
            this.elemContent.append(entryRow.getElem());
        };
        OverviewContent.prototype.scrollToPage = function (pageNo) {
            if (this.scrolling)
                return false;
            var elemPage = this.elemContent.find("." + OverviewContent.CLASS_NAME_NEW_ROW + "[data-page-no=" + pageNo + "]"), scrollTop = "0", that = this;
            this.scrolling = true;
            if (elemPage.length > 0) {
                scrollTop = "+=" + (elemPage.offset().top - this.determineHeaderOffsetPosition());
            }
            this.getOverviewTools().getElemFixedContainer().animate({
                "scrollTop": scrollTop
            }, function () {
                that.scrolling = false;
            });
            return true;
        };
        OverviewContent.prototype.determineHeaderOffsetPosition = function () {
            if (this.overviewTools.getFixedHeader().isFixed()) {
                return this.overviewTools.getElem().offset().top + this.overviewTools.getElem().outerHeight();
            }
            return this.overviewTools.getElemFixedContainer().offset().top
                + this.overviewTools.getFixedHeader().getElemH3().outerHeight()
                + this.overviewTools.getElem().outerHeight()
                + this.elemMainContent.find("thead tr:first").outerHeight();
        };
        OverviewContent.prototype.appendPage = function (elem, pageNo) {
            var first = true, that = this;
            this.extractRows(elem).each(function () {
                var entryRow = new EntryRow(that.overviewTools.getOverviewContent(), $(this), pageNo, that.selectable);
                if (first) {
                    entryRow.getElem().addClass(OverviewContent.CLASS_NAME_NEW_ROW)
                        .attr("data-page-no", pageNo);
                    first = false;
                }
                that.appendRow(entryRow);
            });
            this.elemMainContent.trigger('overview.rowappended');
            rocketTs.updateUi();
        };
        OverviewContent.prototype.removeSelectedEquivalent = function (entryRow) {
            this.elemContent.children(".selected.rocket-id-rep-"
                + entryRow.getIdRep()).remove();
        };
        OverviewContent.prototype.isSelected = function (entryRow) {
            return this.elemContent.children(".selected.rocket-id-rep-"
                + entryRow.getIdRep()).length > 0;
        };
        OverviewContent.prototype.extractRows = function (elem) {
            return elem.find(".rocket-overview-content").children("tr");
        };
        OverviewContent.prototype.setNumSelectedEntries = function (numSelectedEntries) {
            this.elemNumSelectedEntries.text((numSelectedEntries === 1)
                ? numSelectedEntries + " " + this.overviewTools.getTextSelectedLabel()
                : numSelectedEntries + " " + this.overviewTools.getTextSelectedPluralLabel());
        };
        OverviewContent.prototype.setNumEntries = function (numEntries) {
            this.elemNumEntries.text((numEntries === 1)
                ? numEntries + " " + this.overviewTools.getTextEntriesLabel()
                : numEntries + " " + this.overviewTools.getTextEntriesPluralLabel());
        };
        OverviewContent.CLASS_NAME_NEW_ROW = 'rocket-overview-page-first-row';
        return OverviewContent;
    }());
    var FixedHeader = (function () {
        function FixedHeader(overviewTools) {
            this.elemFixedContainer = null;
            this.elemH3Clone = null;
            this.elemTableClone = null;
            this.elemTableCloneHeader = null;
            this.fixed = false;
            this.processing = false;
            this.applyDefaultFixedContainer = true;
            this.overviewTools = overviewTools;
        }
        FixedHeader.prototype.isInitialized = function () {
            return null !== this.elemFixedContainer;
        };
        FixedHeader.prototype.isFixed = function () {
            return this.fixed;
        };
        FixedHeader.prototype.assignElemFixedContainer = function (elemFixedContainer) {
            this.elemFixedContainer = elemFixedContainer;
            this.initElements();
            $(window).off("resize.overview overview.rowappended");
            elemFixedContainer.off("scroll.overview");
        };
        FixedHeader.prototype.getElemFixedContainer = function () {
            return this.elemFixedContainer;
        };
        FixedHeader.prototype.getElemH3 = function () {
            return this.elemH3;
        };
        FixedHeader.prototype.isApplyDefaultFixedContainer = function () {
            return this.applyDefaultFixedContainer;
        };
        FixedHeader.prototype.setApplyDefaultFixedContainer = function (applyDefaultFixedContainer) {
            this.applyDefaultFixedContainer = applyDefaultFixedContainer;
        };
        FixedHeader.prototype.initTableHeaders = function () {
            var clonedChildren = this.elemTableCloneHeader.children();
            this.elemTableHeader.children().each(function (index) {
                clonedChildren.eq(index).innerWidth($(this).innerWidth());
                clonedChildren.css({
                    "boxSizing": "border-box"
                });
            });
        };
        FixedHeader.prototype.startObserving = function () {
            var that = this;
            $(window).off("resize.overview overview.rowappended").on("resize.overview overview.rowappended", function () {
                that.initTableHeaders();
                that.elemFixedContainer.trigger("scroll.overview");
            }).trigger('resize.overview');
            this.elemFixedContainer.off("scroll.overview").on("scroll.overview", function () {
                if (that.elemFixedContainer.offset().top > that.elemH3.offset().top) {
                    that.initFixed();
                    return;
                }
                that.reset();
            });
            this.overviewTools.getElem().off("heightChange").on("heightChange", function () {
                if (!that.fixed)
                    return;
                that.elemH3.css("marginBottom", $(this).outerHeight(true) - that.elemTableCloneHeader.outerHeight(true));
            });
        };
        FixedHeader.prototype.stopObserving = function () {
            $(window).off("resize.overview overview.rowappended");
            this.elemFixedContainer.off("scroll.overview");
            this.overviewTools.getElem().off("heightChange");
        };
        FixedHeader.prototype.initFixed = function () {
            if (this.fixed)
                return;
            this.initTableHeaders();
            var elemOverviewTools = this.overviewTools.getElem(), that = this;
            this.elemH3Clone.css({
                "position": "fixed"
            }).appendTo(this.elemRocketPanel);
            this.elemH3.css("marginBottom", "+=" + elemOverviewTools.outerHeight(true));
            elemOverviewTools.css({
                "position": "fixed"
            }).addClass("rocket-fixed");
            this.elemTableClone.appendTo(elemOverviewTools);
            this.fixed = true;
        };
        FixedHeader.prototype.reset = function () {
            if (!this.fixed)
                return;
            this.elemH3Clone.detach();
            this.elemTableClone.detach();
            this.elemH3.removeAttr("style");
            this.overviewTools.getElem().insertAfter(this.elemH3).removeAttr("style").removeClass("rocket-fixed");
            this.fixed = false;
        };
        FixedHeader.prototype.initElements = function () {
            if (null !== this.elemH3Clone) {
                this.elemH3Clone.remove();
            }
            if (null !== this.elemTableClone) {
                this.elemTableClone.remove();
            }
            this.elemH3 = this.elemFixedContainer.find("h3:first");
            this.elemH3Clone = this.elemH3.clone().addClass("rocket-cloned-header");
            this.elemTable = this.elemFixedContainer.find("table.rocket-list:first");
            this.elemTableHeader = this.elemTable.find("thead:first > tr");
            this.elemTableClone = this.elemTable.clone();
            this.elemTableCloneHeader = this.elemTableClone.find("thead:first > tr");
            this.elemRocketPanel = this.elemFixedContainer.find(".rocket-panel:first");
            this.elemTableClone.find("tbody").detach();
            this.reset();
        };
        return FixedHeader;
    }());
    var OverviewTools = (function () {
        function OverviewTools(elem, elemOverview) {
            if (elemOverview === void 0) { elemOverview = null; }
            this.overviewContent = null;
            this.elem = elem;
            this.contentUrl = elem.data("content-url");
            this.critmodFormUrl = elem.data("critmod-form-url");
            this.fixedHeader = new FixedHeader(this);
            this.textEntriesLabel = this.elem.data("entries-label");
            this.textEntriesPluralLabel = this.elem.data("entries-plural-label");
            this.textSelectedLabel = this.elem.data("selected-label");
            this.textSelectedPluralLabel = this.elem.data("selected-plural-label");
            this.elemLoading = rocketTs.createLoadingElem();
            (function (that) {
                that.critmodForm = new CritmodForm(that, elem.find(".rocket-critmod:first"));
                that.quickserachForm = new QuickSearchForm(that, elem.find(".rocket-quicksearch:first"));
                if (null !== elemOverview && elemOverview.length > 0) {
                    that.initOverview(elemOverview, elemOverview.data("num-pages"), elemOverview.data("num-entries"), elemOverview.data("current-page"));
                }
                else {
                    that.elem.before($("<h3 />", {
                        "text": that.elem.data("entries-plural-label")
                    }));
                    that.setLoading(true);
                    that.getContent(1, function (elemContent, data) {
                        that.setLoading(false);
                        that.initOverview(elemContent, data['numPages'], data['numEntries'], 1);
                        rocketTs.updateUi();
                    });
                }
                ;
            }).call(this, this);
        }
        OverviewTools.prototype.setSelectable = function (selectable) {
            this.overviewContent.setSelectable(selectable);
        };
        OverviewTools.prototype.getTextEntriesLabel = function () {
            return this.textEntriesLabel;
        };
        OverviewTools.prototype.getTextEntriesPluralLabel = function () {
            return this.textEntriesPluralLabel;
        };
        OverviewTools.prototype.getTextSelectedLabel = function () {
            return this.textSelectedLabel;
        };
        OverviewTools.prototype.getTextSelectedPluralLabel = function () {
            return this.textSelectedPluralLabel;
        };
        OverviewTools.prototype.getElemFixedContainer = function () {
            return this.fixedHeader.getElemFixedContainer();
        };
        OverviewTools.prototype.setLoading = function (loading) {
            if (loading === void 0) { loading = true; }
            if (loading) {
                if (null !== this.overviewContent) {
                    this.elemLoading.insertAfter(this.overviewContent.getElemMainContent());
                }
                else {
                    this.elemLoading.insertAfter(this.elem);
                }
            }
            else {
                this.elemLoading.detach();
            }
        };
        OverviewTools.prototype.setElemFixedContainer = function (elemFixedContainer, startObserving) {
            if (startObserving === void 0) { startObserving = true; }
            var that = this;
            this.fixedHeader.assignElemFixedContainer(elemFixedContainer);
            if (startObserving) {
                this.fixedHeader.startObserving();
            }
            this.overviewContent.getPagination().applyElemFixedContainer(elemFixedContainer);
        };
        OverviewTools.prototype.getOverviewContent = function () {
            return this.overviewContent;
        };
        OverviewTools.prototype.getElem = function () {
            return this.elem;
        };
        OverviewTools.prototype.getFixedHeader = function () {
            return this.fixedHeader;
        };
        OverviewTools.prototype.initOverview = function (elemOverview, numPages, numEntries, pageNo) {
            elemOverview.insertAfter(this.elem);
            this.overviewContent = new OverviewContent(this, elemOverview, numPages, numEntries, pageNo);
            //Hook to change the fixed container
            this.elem.trigger('overview.contentLoaded', [this]);
            if (this.fixedHeader.isApplyDefaultFixedContainer()) {
                this.setElemFixedContainer(rocketTs.getElemContentContainer());
            }
        };
        OverviewTools.prototype.getContent = function (pageNo, callback) {
            var that = this;
            return $.getJSON(this.contentUrl, {
                pageNo: pageNo
            }, function (data) {
                callback(rocketTs.analyzeAjahData(data), data['additional']);
            });
        };
        OverviewTools.prototype.reloadContent = function () {
            var that = this;
            this.overviewContent.clear();
            this.setLoading(true);
            this.getContent(1, function (elemContent, additionalData) {
                that.setLoading(false);
                that.overviewContent.replaceContent(elemContent);
                that.overviewContent.setNumEntries(additionalData['numEntries']);
                var pagination = that.overviewContent.getPagination();
                pagination.setCurrentPageNo(1);
                pagination.setNumPages(additionalData['numPages']);
            });
        };
        OverviewTools.prototype.postForm = function (methName, formData, callback, formUrl) {
            if (callback === void 0) { callback = null; }
            if (formUrl === void 0) { formUrl = null; }
            this.overviewContent.clear();
            this.setLoading(true);
            formData.append(methName, true);
            var that = this;
            return $.ajax({
                "url": this.contentUrl + "?pageNo=1",
                "type": "POST",
                "data": formData,
                "processData": false,
                "contentType": false,
                "success": function (data) {
                    var elemContent = $($.parseHTML(n2n.dispatch.analyze(data))), additionalData = data['additional'];
                    that.setLoading(false);
                    that.overviewContent.replaceContent(elemContent);
                    that.overviewContent.setNumEntries(additionalData['numEntries']);
                    var pagination = that.overviewContent.getPagination();
                    pagination.setCurrentPageNo(1);
                    pagination.setNumPages(additionalData['numPages']);
                    if (null !== callback) {
                        callback(additionalData);
                    }
                }
            });
        };
        return OverviewTools;
    }());
    spec.OverviewTools = OverviewTools;
    rocketTs.ready(function () {
        var elemOverviewTools = $(".rocket-overview-tools:first"), elemMainContent = $(".rocket-overview-main-content:first");
        if (elemOverviewTools.length > 0) {
            elemOverviewTools.data("initialized-overview-tools", true);
            new OverviewTools(elemOverviewTools, elemMainContent);
        }
        n2n.dispatch.registerCallback(function () {
            $(".rocket-overview-tools").each(function () {
                var elemOverview = $(this);
                if (elemOverview.data("initialized-overview-tools"))
                    return;
                elemOverview.data("initialized-overview-tools", true);
                new OverviewTools(elemOverview);
            });
        });
    });
})(spec || (spec = {}));
/// <reference path="..\..\rocket.ts" />
/// <reference path="..\..\ui\dialog.ts" />
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
 */
var spec;
(function (spec) {
    var edit;
    (function (edit) {
        var CriticalInput = (function () {
            function CriticalInput(elem) {
                this.dialog = null;
                this.elem = elem;
                this.elemLockedContainer = $("<div/>", {
                    "class": "rocket-critical-input-locked-container"
                }).insertAfter(elem);
                this.elemLabel = $("<span/>", {
                    text: this.determineLabel
                }).appendTo(this.elemLockedContainer);
                this.elemUnlock = $("<a/>", {
                    "class": "rocket-critical-input-unlock rocket-control"
                }).append($("<i/>", { "class": elem.data("icon-unlock") || "fa fa-pencil" }))
                    .appendTo(this.elemLockedContainer);
                elem.hide();
                (function (that) {
                    if (elem.data("confirm-message")) {
                        that.initializeDialog();
                    }
                    that.elemUnlock.click(function (e) {
                        e.preventDefault();
                        if (null !== that.dialog) {
                            rocketTs.showDialog(that.dialog);
                        }
                        else {
                            that.showInput();
                        }
                    });
                }).call(this, this);
            }
            CriticalInput.prototype.initializeDialog = function () {
                var that = this;
                this.dialog = new ui.Dialog(this.elem.data("confirm-message"));
                this.dialog.addButton(this.elem.data("edit-label"), function () {
                    that.showInput();
                });
                this.dialog.addButton(this.elem.data("cancel-label"), function () {
                    //defaultbehaviour is to close the dialog
                });
            };
            CriticalInput.prototype.showInput = function () {
                this.elemLockedContainer.hide();
                this.elemLockedContainer.show();
                this.elemUnlock.remove();
            };
            ;
            CriticalInput.prototype.determineLabel = function (elem) {
                var label = elem.val();
                if (elem.is("select")) {
                    var elemOption = elem.find("option[value='" + label + "']");
                    if (elemOption.length > 0) {
                        label = elemOption.text();
                    }
                }
                return label;
            };
            return CriticalInput;
        }());
    })(edit = spec.edit || (spec.edit = {}));
})(spec || (spec = {}));
/// <reference path="..\rocket.ts" />
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
 */
var ui;
(function (ui) {
    var LoginInput = (function () {
        function LoginInput(elemInputLogin) {
            this.elemInputLogin = elemInputLogin;
            this.elemLabel = elemInputLogin.parent().prev();
            (function (that) {
                elemInputLogin.focus(function () {
                    that.elemLabel.addClass("rocket-label-active");
                }).focusout(function () {
                    that.elemLabel.removeClass("rocket-label-active");
                });
            }).call(this, this);
        }
        return LoginInput;
    }());
    rocketTs.ready(function () {
        rocketTs.registerUiInitFunction(".rocket-login-input", function (elemInputLogin) {
            new LoginInput(elemInputLogin);
        });
    });
})(ui || (ui = {}));
/// <reference path="..\rocket.ts" />
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
 */
var ui;
(function (ui) {
    var GlobalNavGroup = (function () {
        function GlobalNavGroup(globalNav, elem) {
            this.globalNav = globalNav;
            this.elem = elem;
            this.elemHeading = elem.children("h3:first");
            this.elemIcon = this.elemHeading.find("i:first");
            this.name = this.elemHeading.text().trim();
            this.elemUl = elem.children("ul:first");
            (function (that) {
                if (!that.isOpen()) {
                    that.close(true);
                }
                that.elemHeading.children("a:first").click(function (e) {
                    e.preventDefault();
                    if (that.isOpen()) {
                        that.close();
                    }
                    else {
                        that.open();
                    }
                    globalNav.saveState();
                });
            }).call(this, this);
        }
        GlobalNavGroup.prototype.getStorageId = function () {
            return this.name;
        };
        GlobalNavGroup.prototype.open = function (immediately) {
            if (immediately === void 0) { immediately = false; }
            if (this.isOpen())
                return;
            this.elem.addClass(GlobalNav.NAV_GROUP_OPEN_CLASS);
            if (immediately) {
                this.elemUl.show();
            }
            else {
                this.elemUl.stop(true, true).slideDown(150);
            }
            this.elemIcon.removeClass("fa-plus").addClass("fa-minus");
        };
        GlobalNavGroup.prototype.close = function (immediately) {
            if (immediately === void 0) { immediately = false; }
            if (!immediately && !this.isOpen())
                return;
            this.elem.removeClass(GlobalNav.NAV_GROUP_OPEN_CLASS);
            if (immediately) {
                this.elemUl.hide();
            }
            else {
                this.elemUl.stop(true, true).slideUp(150);
            }
            this.elemIcon.removeClass("fa-minus").addClass("fa-plus");
        };
        GlobalNavGroup.prototype.isOpen = function () {
            return this.elem.hasClass(GlobalNav.NAV_GROUP_OPEN_CLASS);
        };
        return GlobalNavGroup;
    }());
    var GlobalNav = (function () {
        function GlobalNav(elem, storage) {
            this.storageKey = "globalNav";
            this.navGroups = {};
            this.storage = storage;
            this.elem = elem;
            (function (that) {
                elem.children(".rocket-nav-group").each(function () {
                    var navGroup = new GlobalNavGroup(that, $(this));
                    that.navGroups[that.buildNavGroupKey(navGroup)] = navGroup;
                });
                that.initFromStorage();
            }).call(this, this);
        }
        GlobalNav.prototype.buildNavGroupKey = function (navGroup) {
            var key = navGroup.getStorageId();
            if (!this.navGroups.hasOwnProperty(key))
                return key;
            var i = 1;
            do {
                var tmpKey = key + i;
                if (!this.navGroups.hasOwnProperty(tmpKey))
                    return tmpKey;
                i++;
            } while (true);
        };
        GlobalNav.prototype.saveState = function () {
            var openNavGroupKeys = [];
            $.each(this.navGroups, function (navGroupKey, navGroup) {
                if (!navGroup.isOpen())
                    return;
                openNavGroupKeys.push(navGroupKey);
            });
            this.storage.setData(this.storageKey, openNavGroupKeys);
        };
        GlobalNav.prototype.initFromStorage = function () {
            if (!this.storage.hasData(this.storageKey))
                return;
            var that = this;
            $.each(this.storage.getData(this.storageKey), function (index, openNavGroupKey) {
                if (!that.navGroups.hasOwnProperty(openNavGroupKey))
                    return;
                that.navGroups[openNavGroupKey].open(true);
            });
        };
        GlobalNav.NAV_GROUP_OPEN_CLASS = 'rocket-nav-group-open';
        return GlobalNav;
    }());
    var ConfNav = (function () {
        function ConfNav(elem, elemActivator) {
            this.elem = elem.hide();
            this.elemActivator = elemActivator;
            (function (that) {
                if (!that.isOpen()) {
                    that.close(true);
                }
                that.elemActivator.click(function (e) {
                    e.preventDefault();
                    if (that.isOpen()) {
                        that.close();
                    }
                    else {
                        that.open();
                    }
                });
            }).call(this, this);
        }
        ConfNav.prototype.open = function (immediately) {
            if (immediately === void 0) { immediately = false; }
            this.elem.addClass(ConfNav.NAV_GROUP_OPEN_CLASS);
            this.elem.stop(true, true).slideDown(150);
        };
        ConfNav.prototype.close = function (immediately) {
            if (immediately === void 0) { immediately = false; }
            this.elem.removeClass(ConfNav.NAV_GROUP_OPEN_CLASS);
            if (immediately) {
                this.elem.hide();
            }
            else {
                this.elem.stop(true, true).slideUp(150);
            }
        };
        ConfNav.prototype.isOpen = function () {
            return this.elem.hasClass(ConfNav.NAV_GROUP_OPEN_CLASS);
        };
        ConfNav.NAV_GROUP_OPEN_CLASS = "rocket-conf-nav-open";
        return ConfNav;
    }());
    rocketTs.ready(function () {
        new GlobalNav($("#rocket-global-nav"), rocketTs.getLocalStorage());
        new ConfNav($("#rocket-conf-nav"), $("#rocket-conf-nav-toggle"));
    });
})(ui || (ui = {}));
/// <reference path="..\..\rocket.ts" />
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
 */
var spec;
(function (spec) {
    var display;
    (function (display) {
        $ = jQuery;
        var ContentItem = (function () {
            function ContentItem(elem) {
                this.elem = elem;
                this.elemType = elem.find(".rocket-gui-field-type").hide();
                this.typeLabel = this.elemType.children(".rocket-controls").text();
                elem.find(".rocket-field-orderIndex").hide();
                new spec.EntryHeader(this.typeLabel, elem);
            }
            return ContentItem;
        }());
        var ContentItemPanel = (function () {
            function ContentItemPanel(elemHeader, elemContent) {
                this.elemHeader = elemHeader;
                this.elemContent = elemContent;
                (function () {
                    elemContent.children(".rocket-content-item").each(function () {
                        new ContentItem($(this));
                    });
                }).call(this, this);
            }
            return ContentItemPanel;
        }());
        var ContentItemComposer = (function () {
            function ContentItemComposer(elem) {
                this.elem = elem;
                (function (that) {
                    elem.children("h4").each(function () {
                        new ContentItemPanel($(this), $(this).next());
                    });
                }).call(this, this);
            }
            return ContentItemComposer;
        }());
        rocketTs.ready(function () {
            rocketTs.registerUiInitFunction(".rocket-content-item-composer", function (elemContentItemComposer) {
                new ContentItemComposer(elemContentItemComposer);
            });
        });
    })(display = spec.display || (spec.display = {}));
})(spec || (spec = {}));
/// <reference path="../rocket.ts" />
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
 */
var spec;
(function (spec) {
    var $ = jQuery;
    var Error = (function () {
        function Error(errorList, elemError, elemMessage) {
            this.elemError = elemError;
            this.elemLi = $("<li />");
            this.elemMessage = elemMessage;
            (function (that) {
                var elemA = $("<a />", {
                    "href": "#"
                }).appendTo(that.elemLi);
                $("<div />", {
                    "class": "error-list-label",
                    "text": elemMessage.text() || "Fehler"
                }).appendTo(elemA);
                $("<div />", {
                    "class": "error-list-path",
                    "text": errorList.determinePathLabel(that)
                }).appendTo(elemA);
                elemA.click(function (e) {
                    e.preventDefault();
                    errorList.scrollTo(that);
                    that.elemLi.find("input[type=text], textarea").first().focus();
                });
                elemA.mouseenter(function () {
                    errorList.highlight(that);
                }).mouseleave(function () {
                    errorList.normalize(that);
                });
            }).call(this, this);
        }
        Error.prototype.getElem = function () {
            return this.elemError;
        };
        Error.prototype.getElemLi = function () {
            return this.elemLi;
        };
        return Error;
    }());
    var ErrorList = (function () {
        function ErrorList() {
            this.elemFixedContainer = rocketTs.getElemContentContainer();
            this.elemList = $("<ul />", {
                "class": "rocket-error-list"
            });
        }
        ErrorList.prototype.hasErrors = function () {
            return this.elemList.children().length > 0;
        };
        ErrorList.prototype.getElemList = function () {
            return this.elemList;
        };
        ErrorList.prototype.determinePathElements = function (elem) {
            return elem.parents(".rocket-has-error");
        };
        ErrorList.prototype.determinePathLabel = function (error) {
            var elem = error.getElem(), labelParts = [elem.children("label:first").text()];
            this.determinePathElements(error.getElem()).each(function () {
                labelParts.unshift($(this).children("label:first").text());
            });
            return labelParts.join(" / ");
        };
        ErrorList.prototype.highlight = function (error) {
            var elem = error.getElem().addClass("rocket-highlighted");
            //			this.determinePathElements(error.getElem()).each(function() {
            //				$(this).addClass("rocket-highlighted");
            //			});
        };
        ErrorList.prototype.normalize = function (error) {
            var elem = error.getElem().removeClass("rocket-highlighted");
            //			this.determinePathElements(error.getElem()).each(function() {
            //				$(this).removeClass("rocket-highlighted");
            //			});
        };
        ErrorList.prototype.scrollTo = function (error) {
            this.elemFixedContainer.animate({
                scrollTop: "+=" + (error.getElem().offset().top - this.elemFixedContainer.offset().top)
            });
        };
        ErrorList.prototype.addError = function (elemError, elemMessage) {
            var error = new Error(this, elemError, elemMessage);
            this.elemList.append(error.getElemLi());
            return error;
        };
        return ErrorList;
    }());
    rocketTs.ready(function () {
        var errorList = new ErrorList();
        $(".rocket-message-error").each(function () {
            errorList.addError($(this).parents(".rocket-has-error:first"), $(this));
        });
        if (errorList.hasErrors()) {
            var additionalContent = rocketTs.getOrCreateAdditionalContent();
            additionalContent.createAndPrependEntry(additionalContent.getElemContent().data("error-list-label"), errorList.getElemList());
        }
    });
})(spec || (spec = {}));
