"use strict";

var RocketCore = function() {
	this.contentInitializer = null;
	this.stressWindow = null;
};

var Rocket = function() {
	this.core = new RocketCore();
	this.Dialog = null;
	this.Controls = null;
	this.ControlGroup = null;
	this.activeClassName = "rocket-active";
	this.state = new Object();
	this.script = null;
	this.command = null;
};

var rocket = new Rocket();

(function(rocket, $) {
	var RocketContentInitializer = function() {
		this.initFunctions = new Object();
	}
	
	RocketContentInitializer.prototype.registerInitFunction = function(initFunction) {
		var key = initFunction.toString();
		if (this.initFunctions.hasOwnProperty(key) || typeof initFunction != 'function') return;
		this.initFunctions[key] = initFunction;
	};
	
	RocketContentInitializer.prototype.initElement = function(jqElem) {
		if (jqElem.data("content-initialized") === true) return;
		for (var i in this.initFunctions) {
			this.initFunctions[i](jqElem);
		}
		jqElem.data("content-initialized", true);
	};

	//default initializer
	rocket.core.contentInitializer = new RocketContentInitializer();
	
	rocket.core.contentInitializer.registerInitFunction(function(jqElem){
		var jqElemsDatePicker = jqElem.find(".rocket-date-picker");
		if (jqElemsDatePicker.length === 0 || typeof n2n == 'undefined') return;
		if (typeof n2n.DatePicker != 'function') return;
		jqElemsDatePicker.each(function() {
			new n2n.DatePicker($(this));
		});
	});
})(rocket, jQuery);

(function(rocket, $) {
	var RocketDialog = function(msg, type) {
		this.buttons = new Array();
		this.type = type || 'warning';
		this.msg = msg;
	};

	RocketDialog.prototype.addButton = function(label, callback) {
		this.buttons.push({
			label: label,
			callback: callback
		});
	};
	
	rocket.Dialog = RocketDialog;
	
	var RocketStressWindow = function() {
		this.jqElemDivBackground = null;
		this.jqElemDivDialog = null;
		
		(function(_obj) {
			this.jqElemDivBackground = $("<div/>", {
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
			this.jqElemDivDialog = $("<div/>").css({
				"position": "fixed",
				"z-index": 999
			});
		}).call(this, this);
	};
	
	RocketStressWindow.prototype.defaultClassName = "rocket-dialog";
	
	RocketStressWindow.prototype.open = function(dialog) {
		var _obj = this;
		var jqElemBody = $("body");
		var jqElemWindow = $(window);
		
		this.jqElemDivDialog.empty().removeClass()
				.addClass("rocket-dialog-" + dialog.type + " " + this.defaultClassName);
		
		var jqElemPMessage = $("<p/>").text(dialog.msg).addClass("rocket-dialog-message");
		//remove focus from all other to ensure that the submit button isn't fired twice
		$("<input/>", {type:"text", name:"remove-focus", id:"remove-focus"})
				.appendTo(jqElemBody).focus().remove();
		
		var jqElemDivOptions = $("<ul/>", {"class": "rocket-controls rocket-dialog-controls"});
		var jqElemAConfirm = null;
		for (var i in dialog.buttons) {
			var jqElemA = $("<a>", {
				"href": "#"
			}).data("button-id", i).addClass("rocket-dialog-control rocket-control").click(function(e){
				e.preventDefault();
				dialog.buttons[$(this).data("button-id")].callback();
				_obj.close();
				return false;
			}).text(dialog.buttons[i].label);
			if (jqElemAConfirm == null) {
				jqElemAConfirm = jqElemA;
			} 
			jqElemDivOptions.append($("<li/>").append(jqElemA));
		}
		
		this.jqElemDivDialog.append(jqElemPMessage).append(jqElemDivOptions);
		jqElemBody.append(this.jqElemDivBackground).append(this.jqElemDivDialog);
		
		//Position the dialog 
		this.jqElemDivDialog.css('left', ((jqElemWindow.outerWidth(true) 
				- this.jqElemDivDialog.outerWidth(true)) / 2 ));
		this.jqElemDivDialog.css('top', ((jqElemWindow.outerHeight(true) 
				- this.jqElemDivDialog.outerHeight(true)) / 3 ));
		
		this.jqElemDivDialog.hide();
		this.jqElemDivBackground.show();
		this.jqElemDivBackground.animate({
			opacity: 0.7
		}, 151, function() {
			_obj.jqElemDivDialog.show();
		});
		
		$(window).on('keydown.dialog', function(event) {
			var keyCode = (window.event) ? event.keyCode : event.which;
			if (keyCode == 13) {
				jqElemAConfirm.click(); 
				$(window).off('keydown.dialog');
			}     // enter
			if (keyCode == 27) {
				self.close();
				$(window).off('keydown.dialog');
			}   // esc
		});
		
	};
	
	RocketStressWindow.prototype.close = function() {
		this.jqElemDivBackground.detach();
		this.jqElemDivDialog.detach();
		$(window).off('keydown.dialog');
	};
	
	rocket.core.stressWindow = new RocketStressWindow();
})(rocket, jQuery);

(function(rocket, $) {
	var UnsavedFormManager = function() {
		this.listening = false;
		this.jqElemWindow = $(window);
		(function(_obj) {
			this.jqElemWindow.load(function() {
				_obj.listening = true;
			});
		}).call(this, this);
	};

	UnsavedFormManager.prototype.registerForm = function(jqElemForm) {
		if (!jqElemForm.is("form")) throw "Not a Form";
		var _obj = this;
		jqElemForm.on('submit.UnsavedFormManager', function() {
			_obj.deactivate();
		}).on("keydown.UnsavedFormManager change.UnsavedFormManager", function() {
			if (_obj.activate(jqElemForm.data("text-unload"))) {
				jqElemForm.off("keydown.UnsavedFormManager change.UnsavedFormManager");
			}
		});
	};
	
	UnsavedFormManager.prototype.activate = function(text) {
		if ((!this.listening) || rocket.getCookie('atusch')) return false;
		this.jqElemWindow.off('beforeunload.UnsavedFormManager').on('beforeunload.UnsavedFormManager', function(e){
			return text || "Your changes won't be saved.";
		});
		return true;
	};
	
	UnsavedFormManager.prototype.deactivate = function() {
		this.jqElemWindow.off('beforeunload.UnsavedFormManager');
	};
	
	rocket.core.unsavedFormManager = new UnsavedFormManager();
})(rocket, jQuery);

(function(rocket) {
	rocket.setCookie = function(name, value, hours) {
		var expires, date;
	    if (hours) {
	        date = new Date();
	        date.setTime(date.getTime() + (hours * 60 * 60 * 1000));
	        expires = "; expires=" + date.toGMTString();
	    } else {
	    	expires = "";
	    }
	    document.cookie = name + "=" + value + expires + "; path=/";
	};

	rocket.getCookie = function(name) {
	    var nameEQ = name + "=";
	    var ca = document.cookie.split(';');
	    for(var i = 0; i < ca.length; i++) {
	        var c = ca[i];
	        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
	        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
	    }
	    return null;
	};
})(rocket);

(function(rocket, $) {
	// Rocket Global Classes
	(function() {
		//Rocket Control Group
		var RocketControlGroup = function() {
			this.jqElemDiv = $("<div/>", {
				"class": "rocket-control-group"
			});
		};
		
		RocketControlGroup.prototype.addControl = function(title, callBack, iconClassName) {
			var jqElemA = $("<a/>", {"href": "#", "title": title})
					.addClass("rocket-control")
					.append($("<span/>", {"text": title}))
					.click(function(e) {
						e.preventDefault();
						callBack($(this), e);
					});
			if (null != iconClassName) {
				jqElemA.prepend($("<i/>", {"class": iconClassName}))
			}
			this.jqElemDiv.append(jqElemA);
			return jqElemA;
		};
	
		var RocketControls = function(type) {
			this.jqElemUl = $("<ul/>");
			this.setType(type);
		};
		
		RocketControls.TYPE_SIMPLE = 'simple';
		RocketControls.TYPE_NORMAL = 'normal';
		
		RocketControls.prototype.classNameSimple = 'rocket-simple-controls';
		RocketControls.prototype.classNameNormal = 'rocket-controls';
		
		RocketControls.prototype.addControl = function(title, callBack, iconClassName) {
			var jqElemA = $("<a/>", {"href": "#", "title": title})
				.addClass("rocket-control")
				.append($("<span/>", {"text": title}))
				.click(function(e) {
					e.preventDefault();
					return callBack.call(this, $(this), e);
				});
			if (null != iconClassName) {
				jqElemA.prepend($("<i/>", {"class": iconClassName}))
			}
			this.jqElemUl.append($("<li/>").append(jqElemA));
			return jqElemA;
		};
		
		RocketControls.prototype.addControlGroup = function(controlGroup) {
			this.jqElemUl.append($("<li/>").append(controlGroup.jqElemDiv));
		};
		
		RocketControls.prototype.remove = function() {
			this.jqElemUl.remove();
		};
		
		RocketControls.prototype.setType = function(type) {
			this.type = type || RocketControls.TYPE_NORMAL;
			if (this.type === RocketControls.TYPE_SIMPLE) {
				this.jqElemUl.addClass(this.classNameSimple);
			} else {
				this.jqElemUl.addClass(this.classNameNormal);
			}
		};
		rocket.ControlGroup = RocketControlGroup;
		rocket.Controls = RocketControls;
	})();
	
})(rocket, jQuery);

jQuery(document).ready(function($) {
	(function() {
		var refreshPath = $("body").data("refresh-path"), refresh;
		refresh = function() {
			setTimeout(function() {
				$.get(refreshPath);
				refresh();
			}, 300000)
		}
		refresh();
	})();
	(function() {
		var jqElemsGroupedPanels = $(".rocket-grouped-panels");
		if (jqElemsGroupedPanels.length === 0) return;
		
		var RocketPanel = function(jqElem, group) {
			this.jqElem = jqElem;
			this.jqElemLi = null;
			this.parentPanelId = null;
			(function(_obj) {
				this.jqElemLi = $("<li/>", {
					"class": "rocket-panel-activator"
				}).append($("<a/>", {
					"href": "#",
					"text": jqElem.children(":first").hide().text()
				}).click(function(e) {
					e.preventDefault();
				})).click(function() {
					_obj.show();
				});
				this.hide();
				
				var jqElemParentGroup = group.jqElem.parents(".rocket-grouped-panels:first");
				if (jqElemParentGroup.length > 0) {
					this.parentPanelId = group.jqElem.parentsUntil(".rocket-grouped-panels").last().attr("id") || null;
				}
			}).call(this, this);
		};
		
		RocketPanel.prototype.show = function() {
			this.jqElemLi.addClass(rocket.activeClassName);
			if (this.parentPanelId === null) {
				if (null !== this.getId()) {
					if (history.pushState) {
						history.pushState(null, null, '#' + (this.getId() || ''));
					} else {
							window.location.hash = this.getId();
					}
				}
			}
			this.jqElem.show();
		};
		
		RocketPanel.prototype.hide = function() {
			this.jqElemLi.removeClass(rocket.activeClassName);
			this.jqElem.hide();
		};
		
		RocketPanel.prototype.equals = function(obj) {
			return obj instanceof RocketPanel && this.jqElem.is(obj.jqElem);
		};
		
		RocketPanel.prototype.getId = function() {
			return this.jqElem.attr("id") || null;
		};
		
		var RocketGroupedPanels = function(jqElem) {
			this.jqElem = jqElem;
			this.jqElemUl = null
			this.currentPanel = null;
			
			(function(_obj) {
				var locationHash = window.location.hash.substr(1);
				var panelToActivate = null;
				this.jqElemUl = $("<ul/>", {
					"class": "rocket-grouped-panels-navigation"
				});
				this.jqElem.children().each(function() {
					var panel = new RocketPanel($(this), _obj);
					if (null === panelToActivate 
							|| (panel.getId() !== null && locationHash === panel.getId())) {
						panelToActivate = panel;
					}
					_obj.addPanel(panel);
				});
				this.jqElemUl.prependTo(this.jqElem)
				this.activatePanel(panelToActivate);
			}).call(this, this)
		};
		
		RocketGroupedPanels.prototype.addPanel = function(panel) {
			var _obj = this;
			this.jqElemUl.append(panel.jqElemLi.click(function() {
				_obj.activatePanel(panel);
			}));
		};
		
		RocketGroupedPanels.prototype.activatePanel = function(panel) {
			if (null !== this.currentPanel) {
				if (this.currentPanel.equals(panel)) return;
				this.currentPanel.hide();
			} else {
				panel.show();
			}
			this.currentPanel = panel;
		};
		
		jqElemsGroupedPanels.each(function(i) {
			new RocketGroupedPanels($(this));
		});
	})();
	
	(function() {
		$(".rocket-navigation-hash-appender-submit").click(function(e) {
			if ($(this).data("is-hash-appended")) return;
			var jqElemSubmittingForm = $(this).parents("form:first");
			var action = jqElemSubmittingForm.attr("action");
			jqElemSubmittingForm.attr("action", action + location.hash);
			e.preventDefault();
			$(this).data("is-hash-appended", true);
			$(this).click();
		});
	})();
	
	(function() {
		var jqElemNav = $("#rocket-global-nav");
		if (0 === jqElemNav.length) return
		
		//hide all scripts which are not in the active menu
		var navGroupOpenClass = "rocket-nav-group-open";
		$(".rocket-nav-group").each(function(){
			if(!$(this).hasClass(navGroupOpenClass)) {
				$(this).find("ul").hide();
			}
		});
		
		var jqElemsH3MenuEntry = jqElemNav.find(".rocket-nav-group h3");
		jqElemsH3MenuEntry.click(function() {
			$(this).parents("div").first().toggleClass(navGroupOpenClass);
			$(this).siblings("ul").stop().slideToggle("fast");
		});
	})();
	
	(function() {
		var jqConfNav = $("#rocket-conf-nav");
		if (0 === jqConfNav.length)	return;
		
		//hide all scripts which are not in the active menu
		var navGroupOpenClass = "rocket-conf-nav-open";
		//jqConfNav.addClass(navGroupOpenClass);
		
		if(!jqConfNav.hasClass(navGroupOpenClass)) {
			jqConfNav.hide();
		}
		
		var jqToggleElem = $("#rocket-conf-nav-toggle");
		
		jqToggleElem.click(function() {
			jqConfNav.toggleClass(navGroupOpenClass);
			jqConfNav.toggle("fast");
			return false;
		})
		
	})();
	
	(function() {
		var jqLoginInput = $(".rocket-login-input");
		if (0 == jqLoginInput.length) {
			return;
		}
		
		jqLoginInput.each(function() {
			$(this).focus(function() {
				$(this).parent().prev().addClass('rocket-label-active');
			});
			$(this).focusout(function() {
				$(this).parent().prev().removeClass('rocket-label-active');
			})
		});
		
	})();
	
	// set height of preview iframe
	(function(){
		
		var jqiFrame = $(".rocket-preview #rocket-preview-content");
		if (0 == jqiFrame.length) {
			return;
		}
		
		var waitForFinalEvent = (function () {
			  var timers = {};
			  return function ( callback, ms, uniqueId ) {
			    if ( timers[uniqueId] ) {
			      clearTimeout ( timers[uniqueId] );
			    }
			    timers[uniqueId] = setTimeout( callback, ms );
			  };
			})();
		
		function adjustIframeHeight() {
			var windowHeight = $(window).height();
			var jqHeaderHeight = $("#rocket-header").height();
			var jqContentPanelTitleHeight = $(".rocket-panel h3").outerHeight();
			var jqMainCommandsHeight = $(".rocket-main-commands").outerHeight();
			var iFrameHeight = windowHeight - jqHeaderHeight - jqMainCommandsHeight - jqContentPanelTitleHeight;
			jqiFrame.css('min-height', (iFrameHeight));
		}
		
		$(window).resize(function() {
			waitForFinalEvent(function() {
				adjustIframeHeight();
			}, 30, 'preview.resize');
		});
		
		adjustIframeHeight();
	})();
	
	(function() {
		var RocketConfirmableFormInput = function(jqElemInput, container) {
			this.jqElemForm = null;
			this.jqElemInput = jqElemInput;
			this.container = container;
			this.initialize();
		};
		
		RocketConfirmableFormInput.prototype.initialize = function () {
			var _obj = this;
			this.jqElemForm = this.jqElemInput.parents("form:first");
			this.jqElemInput.off("click.form").on("click.formInput", function() {
				_obj.container.showDialog(_obj);
				return false;
			});
		};
		
		RocketConfirmableFormInput.prototype.confirmDialog = function () {
			this.jqElemInput.off("click.formInput");
			if (this.jqElemForm != null) {
				var tempInput = $("<input/>", {
					type: "hidden",
					name: this.jqElemInput.attr("name"),
					value: this.jqElemInput.val()
				});
				this.jqElemForm.append(tempInput);
				this.jqElemForm.submit();
				tempInput.remove();
			}
		};
		
		RocketConfirmableFormInput.prototype.getJqElem = function () {
			return this.jqElemInput;
		};
		
		var RocketConfirmableForm = function(jqElemForm, container) {
			this.jqElemForm = jqElemForm;
			this.container = container;
			this.inputSubmit = null;
			this.initialize();
		};
		
		RocketConfirmableForm.prototype.initialize = function () {
			var _obj = this;
			this.jqElemForm.on("click.form", "input[type=submit]", function() {
				_obj.inputSubmit = this;
				_obj.container.showDialog(_obj);
				//_obj.jqElemForm.find('input').blur();
				return false;
			});
		};
		
		RocketConfirmableForm.prototype.confirmDialog = function () {
			this.jqElemForm.off("click.form");
			var tempInput = $("<input />", {
				type: "hidden",
				name: this.jqElemInput.attr("name"),
				value: this.jqElemInput.val()
			});
			this.jqElemForm.append(tempInput);
			this.jqElemForm.submit();
			tempInput.remove();
		};
		
		RocketConfirmableForm.prototype.getJqElem = function () {
			return this.jqElemForm;
		};
		
		var RocketConfirmableLink = function(jqElemA, container) {
			this.jqElemA = jqElemA;
			this.container = container;
			this.initialize();
		};
		
		RocketConfirmableLink.prototype.initialize = function () {
			var _obj = this;
			this.jqElemA.on("click.confirmable", function(e) {
				e.preventDefault();
				_obj.container.showDialog(_obj);
				return false;
			});
		};
		
		RocketConfirmableLink.prototype.confirmDialog = function () {
			window.location = this.jqElemA.attr("href");
		};
		
		RocketConfirmableLink.prototype.getJqElem = function () {
			return this.jqElemA;
		};
		
		var RocketConfirmables = function(jqElems) {
			
			this.jqElems = jqElems;
			this.defaultConfirmMessage = "Are you sure?";
			this.defaultConfirmOkLabel = "Yes";
			this.defaultCancelLabel = "No";

			this.initialize();
		};
		
		RocketConfirmables.prototype.initialize = function() {
			var _obj = this;
			this.jqElems.each(function(){
				if ($(this).is('a')) {
					new RocketConfirmableLink($(this), _obj);
				} else if ($(this).is('form')) {
					new RocketConfirmableForm($(this), _obj);
				} else if ($(this).is('input[type=submit], button[type=submit]')) {
					new RocketConfirmableFormInput($(this), _obj);
				}
			});
		};
		
		RocketConfirmables.prototype.showDialog = function(confirmable) {
			var _obj = this;
			var dialog = new rocket.Dialog(this.getMsg(confirmable));
			dialog.addButton(this.getConfirmOkLabel(confirmable), function() {
				confirmable.confirmDialog();
			});
			
			dialog.addButton(this.getConfirmCancelLabel(confirmable), function() {
				//defaultbehaviour is to close the dialog
			});
			rocket.core.stressWindow.open(dialog);
		};
		
		RocketConfirmables.prototype.getMsg = function(confirmable) {
			var confirmMessage = this.defaultConfirmMessage;
			if (confirmable.getJqElem && confirmable.getJqElem().data('rocket-confirm-msg')) {
				confirmMessage = confirmable.getJqElem().data('rocket-confirm-msg');
			};
			return confirmMessage;
		};
		
		RocketConfirmables.prototype.getConfirmOkLabel = function(confirmable) {
			var confirmOkLabel = this.defaultConfirmOkLabel;
			if (confirmable.getJqElem && confirmable.getJqElem().data('rocket-confirm-ok-label')) {
				confirmOkLabel = confirmable.getJqElem().data('rocket-confirm-ok-label');
			};
			return confirmOkLabel;
		};
		
		RocketConfirmables.prototype.getConfirmCancelLabel = function(confirmable) {
			var confirmCancelLabel = this.defaultCancelLabel;
			if (confirmable.getJqElem && confirmable.getJqElem().data('rocket-confirm-cancel-label')) {
				confirmCancelLabel = confirmable.getJqElem().data('rocket-confirm-cancel-label');
			};
			return confirmCancelLabel;
		};
		new RocketConfirmables($("[data-rocket-confirm-msg]"));
	})();
	
	
	(function() {
		$(".rocket-paging select, select.rocket-paging").change(function() {
			window.location = this.value;
		});
	})();
	
	(function() {
		if (typeof $.fn.responsiveTable !== 'function') return;
		$(".rocket-list").responsiveTable();
	})();
	
	(function() {
		$(".rocket-unsaved-check-form").each(function() {
			rocket.core.unsavedFormManager.registerForm($(this));
		});
	})();
});
