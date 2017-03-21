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
 * 
 */
module ui {
	class GlobalNavGroup {
		private elem: JQuery;
		private globalNav: GlobalNav;
		private elemHeading: JQuery;
		private elemIcon: JQuery;
		private elemUl: JQuery;
		private name: string;
		
		public constructor(globalNav: GlobalNav, elem) {
			this.globalNav = globalNav;
			this.elem = elem;
			this.elemHeading = elem.children("h3:first");
			this.elemIcon = this.elemHeading.find("i:first");
			this.name = this.elemHeading.text().trim();
			this.elemUl = elem.children("ul:first");
			
			(function(that: GlobalNavGroup) {
				if (!that.isOpen()) {
					that.close(true);
				}
				
				that.elemHeading.children("a:first").click(function(e) {
					e.preventDefault();
					if (that.isOpen()) {
						that.close();	
					} else {
						that.open();	
					}
					
					globalNav.saveState();
				});
			}).call(this, this);
		}
		
		public getStorageId() {
			return this.name;	
		}
		
		public open(immediately: boolean = false) {
			if (this.isOpen()) return;
			
			this.elem.addClass(GlobalNav.NAV_GROUP_OPEN_CLASS);
			if (immediately) {
				this.elemUl.show();
			} else {
				this.elemUl.stop(true, true).slideDown(150);
			}
			
			this.elemIcon.removeClass("fa-plus").addClass("fa-minus");
		}
		
		public close(immediately: boolean = false) {
			if (!immediately && !this.isOpen()) return;
			this.elem.removeClass(GlobalNav.NAV_GROUP_OPEN_CLASS);
			if (immediately) {
				this.elemUl.hide();
			} else {	
				this.elemUl.stop(true, true).slideUp(150);
			}
			
			this.elemIcon.removeClass("fa-minus").addClass("fa-plus");
		}
		
		public isOpen() {
			return this.elem.hasClass(GlobalNav.NAV_GROUP_OPEN_CLASS);
		}
	}
	
	class GlobalNav {
		private elem;
		private storage: storage.WebStorage;
		private storageKey = "globalNav";
		private navGroups: Object = {};
		public static NAV_GROUP_OPEN_CLASS = 'rocket-nav-group-open';
		
		public constructor(elem: JQuery, storage: storage.WebStorage) {
			this.storage = storage;
			this.elem = elem;
			
			(function(that: GlobalNav) {
				elem.children(".rocket-nav-group").each(function() {
					var navGroup = new GlobalNavGroup(that, $(this));
					that.navGroups[that.buildNavGroupKey(navGroup)] = navGroup;	
				});
				
				that.initFromStorage();
			}).call(this, this);
		}
		
		private buildNavGroupKey(navGroup: GlobalNavGroup) {
			var key = navGroup.getStorageId();
			if (!this.navGroups.hasOwnProperty(key)) return key;
			var i = 1
			do {
				var tmpKey = key + i;
				if (!this.navGroups.hasOwnProperty(tmpKey)) return tmpKey;
				i++;
			} while(true);
		}
		
		public saveState() {
			var openNavGroupKeys = [];
			$.each(this.navGroups, function(navGroupKey, navGroup: GlobalNavGroup) {
				if (!navGroup.isOpen()) return;
				
				openNavGroupKeys.push(navGroupKey);
			});
			
			this.storage.setData(this.storageKey, openNavGroupKeys);
		}
		
		private initFromStorage() {
			if (!this.storage.hasData(this.storageKey)) return;
			var that = this;
			$.each(this.storage.getData(this.storageKey), function(index, openNavGroupKey) {
				if (!that.navGroups.hasOwnProperty(openNavGroupKey)) return;
				that.navGroups[openNavGroupKey].open(true);
			});
		}
	}
	
	class ConfNav {
		private elem: JQuery;
		private elemActivator: JQuery;
		private static NAV_GROUP_OPEN_CLASS = "rocket-conf-nav-open"
		
		public constructor(elem: JQuery, elemActivator: JQuery) {
			this.elem = elem.hide();
			this.elemActivator = elemActivator;
			
			(function(that: ConfNav) {
				if (!that.isOpen()) {
					that.close(true);	
				}
				
				that.elemActivator.click(function(e) {
					e.preventDefault();
					if (that.isOpen()) {
						that.close();
					} else {
						that.open();	
					}
				});
			}).call(this, this);
		}
		
		public open(immediately: boolean = false) {
			this.elem.addClass(ConfNav.NAV_GROUP_OPEN_CLASS);
			this.elem.stop(true, true).slideDown(150);
		}
		
		public close(immediately: boolean = false) {
			this.elem.removeClass(ConfNav.NAV_GROUP_OPEN_CLASS);
			if (immediately) {
				this.elem.hide();
			} else {	
				this.elem.stop(true, true).slideUp(150);
			}
		}
		
		public isOpen() {
			return this.elem.hasClass(ConfNav.NAV_GROUP_OPEN_CLASS);	
		}
	}
	
	rocketTs.ready(function() {
		new GlobalNav($("#rocket-global-nav"), rocketTs.getLocalStorage());
		
		new ConfNav($("#rocket-conf-nav"), $("#rocket-conf-nav-toggle"));
	});
}
