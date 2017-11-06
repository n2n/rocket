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
module ui {
	$ = jQuery;
	class Panel {
		private group: PanelGroup;
		private elem: JQuery;
		private elemLi: JQuery;
		private parentPanelGroupId: string = null;
		
		public constructor(group: PanelGroup, elem: JQuery) {
			this.group = group;
			this.elem = elem;
			this.elemLi = $("<li/>", {
				"class": "rocket-panel-activator"
			});
			
			this.parentPanelGroupId = null;
			(function(that: Panel) {
				that.elemLi.append($("<a/>", {
					"href": "#",
					"text": elem.children(":first").hide().text()
				}).click(function(e) {
					e.preventDefault();
				}));
				
				that.elemLi.click(function() {
					that.show();
				});
				
				that.hide();
			}).call(this, this);
		}
		
		public getElemLi() {
			return this.elemLi;	
		}
				
		public show() {
			this.elemLi.addClass("rocket-active");
			
			if (this.group.hasParentPanelGroup()) {
				if (null !== this.getId()) {
					if (typeof history.pushState !== 'undefined') {
						history.pushState(null, null, '#!' + this.getId());
					} else {
						window.location.hash = "#!" + this.getId();
					}
				}
			}
			
			this.elem.show();
		};
		
		public hide() {
			this.elemLi.removeClass("rocket-active");
			this.elem.hide();
		};
		
		public equals(obj) {
			return obj instanceof Panel && this.elemLi.is(obj.getElemLi());
		};
		
		public getId() {
			return this.elem.attr("id") || null	
		}
	}
	
	export class PanelGroup {
		private elem: JQuery;
		private elemUl: JQuery;
		private currentPanel: Panel = null;
		
		public constructor(elem: JQuery) {
			this.elem = elem;
			this.elemUl = $("<ul/>", {
				"class": "rocket-grouped-panels-navigation"
			});
			
			(function(that: PanelGroup) {
				var currentPanelId = window.location.hash.substr(2), 
					panelToActivate = null;
				elem.children().each(function() {
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
				
		public addPanel(panel: Panel) {
			var that = this;
			this.elemUl.append(panel.getElemLi().click(function() {
				that.activatePanel(panel);
			}));
		};
		
		public hasParentPanelGroup(): boolean {
			return this.elem.parents(".rocket-grouped-panels:first").length > 0;
		}
		
		public activatePanel(panel: Panel) {
			if (null !== this.currentPanel) {
				if (this.currentPanel.equals(panel)) return;
				this.currentPanel.hide();
			} else {
				panel.show();
			}
			this.currentPanel = panel;
		};
	}
	
	rocketTs.ready(function() {
		rocketTs.registerUiInitFunction(".rocket-grouped-panels", function(elemPanelGroup: JQuery) {
			new PanelGroup(elemPanelGroup);
		});
	});
}
