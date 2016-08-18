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
 * 
 */
module spec.edit {
	class CriticalInput {
		private elem: JQuery;
		private elemLockedContainer: JQuery;
		private elemUnlock: JQuery;
		private elemLabel: JQuery;
		private dialog: ui.Dialog = null;
		
		public constructor(elem: JQuery) {
			this.elem = elem;
			this.elemLockedContainer = $("<div/>", {
				"class": "rocket-critical-input-locked-container"
			}).insertAfter(elem);
			
			this.elemLabel = $("<span/>", {
				text: this.determineLabel
			}).appendTo(this.elemLockedContainer);
			
			this.elemUnlock = $("<a/>", {
				"class": "rocket-critical-input-unlock rocket-control"
			}).append($("<i/>", {"class": elem.data("icon-unlock") || "fa fa-pencil"}))
			.appendTo(this.elemLockedContainer);
		
			elem.hide();
			
			(function(that: CriticalInput) {
				if (elem.data("confirm-message")) {
					that.initializeDialog();
				}
				that.elemUnlock.click(function(e) {
					e.preventDefault();
					if (null !== that.dialog) {
						rocketTs.showDialog(that.dialog);	
					} else {
						that.showInput();	
					}
				});
			}).call(this, this);
		}
		
		private initializeDialog() {
			var that = this;
			this.dialog = new ui.Dialog(this.elem.data("confirm-message"));
			
			this.dialog.addButton(this.elem.data("edit-label"), function() {
				that.showInput();
			});
			
			this.dialog.addButton(this.elem.data("cancel-label"), function() {
				//defaultbehaviour is to close the dialog
			});
		}
		
				
		private showInput() {
			this.elemLockedContainer.hide();
			this.elemLockedContainer.show();
			this.elemUnlock.remove();
		};
		
		private determineLabel(elem: JQuery) {
			var label = elem.val();
			if (elem.is("select")) {
				var elemOption = elem.find("option[value='" + label + "']");
				if (elemOption.length > 0) {
					label = elemOption.text();
				}
			}
			
			return label;
		}
	}
}
