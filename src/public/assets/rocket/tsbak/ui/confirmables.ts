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
	interface Confirmable {
		getMsg(): string;
		getConfirmOkLabel(): string;
		getConfirmCancelLabel(): string;
		confirmDialog();
	}
	
	class ConfirmableAdapter implements Confirmable {
		protected manager: ConfirmableManager;	
		protected elem: JQuery;
		private msg: string;
		private confirmOkLabel: string;
		private confirmCancelLabel: string;
				
		public constructor(manager: ConfirmableManager, elem: JQuery) {
			this.manager = manager;
			this.elem = elem;
			this.msg = this.elem.data("rocket-confirm-msg") || "Are you sure?";
			this.confirmOkLabel = this.elem.data("rocket-confirm-ok-label") || "Yes";;
			this.confirmCancelLabel = this.elem.data("rocket-confirm-cancel-label") || "No";
		}
		
		public getMsg() {
			return this.msg;
		}
		
		public setMsg(msg: string) {
			this.msg = msg;;	
		}
		
		public getConfirmOkLabel() {
			return this.confirmOkLabel;	
		}
		
		public setConfirmOkLabel(confirmOkLabel: string) {
			this.confirmOkLabel = confirmOkLabel;	
		}
		
		public getConfirmCancelLabel() {
			return this.confirmCancelLabel;	
		}
		
		public setConfirmCancelLabel(confirmCancelLabel: string) {
			this.confirmCancelLabel = confirmCancelLabel;	
		}
		
		public showDialog() {
			this.manager.showDialog(this);
		}
		
		public confirmDialog() { };
	}
	
	class ConfirmableSubmit extends ConfirmableAdapter {
		private elemForm: JQuery;
		
		public constructor(manager: ConfirmableManager, elemInput: JQuery) {
			super(manager, elemInput);
			this.elemForm = elemInput.parents("form:first");
			
			(function(that: ConfirmableSubmit) {
				elemInput.off("click.form").on("click.formInput", function() {
					that.showDialog();
				});
			}).call(this, this);
		}
		
		public confirmDialog() {
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
		}
	}
	
	class ConfirmableForm extends ConfirmableAdapter {
		private elemSubmit: JQuery;
		
		public constructor(manager: ConfirmableManager, elemForm: JQuery) {
			super(manager, elemForm);
			
			(function(that: ConfirmableForm) {
				elemForm.on("click.form", "input[type=submit]", function() {
					that.elemSubmit = this;
					that.showDialog();
					//_obj.jqElemForm.find('input').blur();
					return false;
				});
			}).call(this, this);
		}
		
		public confirmDialog() {
			this.elem.off("click.form");
			var tempInput = $("<input />", {
				"type": "hidden",
				"name": this.elemSubmit.attr("name"),
				"value": this.elemSubmit.val()
			}).appendTo(this.elem);

			this.elem.submit();
			tempInput.remove();
		}
	}
	
	class ConfirmableLink extends ConfirmableAdapter {
		public constructor(manager: ConfirmableManager, elemA: JQuery) {
			super(manager, elemA);
			
			(function(that: ConfirmableForm) {
				elemA.on("click.confirmable", function(e) {
					e.preventDefault();
					that.showDialog();
				});
			}).call(this, this);
		}
		
		public confirmDialog() {
			window.location.assign(this.elem.attr("href"));
		}
	}
	
	export class ConfirmableManager {
		public initElem(elem: JQuery) {
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
		}
		
		public showDialog(confirmable: Confirmable) {
			var that = this,
				dialog = new ui.Dialog(confirmable.getMsg());
			dialog.addButton(confirmable.getConfirmOkLabel(), function() {
				confirmable.confirmDialog();
			});
			
			dialog.addButton(confirmable.getConfirmCancelLabel(), function() {
				//defaultbehaviour is to close the dialog
			});
			rocketTs.showDialog(dialog);
		}
	}
	
	rocketTs.ready(function() {
		rocketTs.registerUiInitFunction("[data-rocket-confirm-msg]", function(elemConfirmable: JQuery) {
			rocketTs.getConfirmableManager().initElem(elemConfirmable);
		});
	});
}
