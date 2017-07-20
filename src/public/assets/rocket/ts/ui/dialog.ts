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
	export class Dialog {
		private buttons: Array<Object> = [];
		private msg: string;
		private dialogType: string;
		
		public constructor(msg: string, dialogType: string = 'warning') {
			this.msg = msg;
			this.dialogType = dialogType;
		}
		
		public addButton(label, callback: (e: JQueryEventObject) => void) {
			this.buttons.push({
				label: label,
				callback: callback	
			});
		}
		
		public getMsg() {
			return this.msg;	
		}
		
		public getDialogType() {
			return this.dialogType;	
		}
		
		public getButtons() {
			return this.buttons;	
		}
	}
	
	export class StressWindow {
		private elemBackground: JQuery;
		private elemDialog: JQuery;
		private elemControls: JQuery;
		private elemMessage: JQuery;
		private elemConfirm: JQuery;
		
		public constructor() {
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
		
		public open(dialog: Dialog) {
			var that = this,
				elemBody = $("body"),
				elemWindow = $(window);
			
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
			}, 151, function() {
				that.elemDialog.show();
			});
			
			elemWindow.on('keydown.dialog', function(event) {
				var keyCode = (window.event) ? event.keyCode : event.which;
				if (keyCode == 13) {
					//Enter
					that.elemConfirm.click(); 
					$(window).off('keydown.dialog');
				} else if (keyCode == 27) {
					//Esc
					that.close();
				}   
			});
		}
		
		private initButtons(dialog: Dialog) {
			var that = this;
			this.elemConfirm = null;
			this.elemControls.empty();
			
			dialog.getButtons().forEach(function(button: Object) {
				var elemA = $("<a>", {
					"href": "#"
				}).addClass("rocket-dialog-control rocket-control").click(function(e){
					e.preventDefault();
					button['callback'](e);
					that.close();
				}).text(button['label']);
				
				if (that.elemConfirm == null) {
					that.elemConfirm = elemA;
				} 
				that.elemControls.append($("<li/>").append(elemA));
			});
			
		}
		
		private removeCurrentFocus() {
			//remove focus from all other to ensure that the submit button isn't fired twice
			$("<input/>", {
				"type": "text", 
				"name": "remove-focus"	
			}).appendTo($("body")).focus().remove();
		}
			
		public close = function() {
			this.elemBackground.detach();
			this.elemDialog.detach();
			$(window).off('keydown.dialog');
		};
	}
}
