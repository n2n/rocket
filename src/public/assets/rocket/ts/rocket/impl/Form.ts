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
namespace Rocket.Impl {
	var $ = jQuery;
	
	export class Form {
		private jqForm: JQuery;
		private _observing = false;
		private _config: Form.Config = new Form.Config();
		private callbackRegistery: util.CallbackRegistry<FormCallback> = new util.CallbackRegistry<FormCallback>();
		private curXhr: JQuery.jqXHR = null;
		
		constructor(jqForm: JQuery) {
			this.jqForm = jqForm;
		}
		
		get jQuery(): JQuery {
			return this.jqForm;
		}
		
		get observing() {
			return this._observing;
		}
		
		get config(): Form.Config {
			return this._config;
		}
		
		reset() {
			(<HTMLFormElement> this.jqForm.get(0)).reset();
		}
		
		private trigger(eventType: Form.EventType) {
			var that = this;
			this.callbackRegistery.filter(eventType.toString())
					.forEach(function (callback: FormCallback) {
						callback(that);
					});
		}
		
		public on(eventType: Form.EventType, callback: FormCallback) {
			this.callbackRegistery.register(eventType.toString(), callback);
		}
		
		public off(eventType: Form.EventType, callback: FormCallback) {
			this.callbackRegistery.unregister(eventType.toString(), callback);
		}
		
		public observe() {
			if (this._observing) return;
			
			this._observing = true;
			
			var that = this;
			this.jqForm.submit(function () {
				if (!that._config.autoSubmitAllowed) return false;
				that.submit();
				return false;
			});
			
			var that = this;
			this.jqForm.find("input[type=submit], button[type=submit]").each(function () {
				$(this).click(function () {
					if (!that._config.autoSubmitAllowed) return false;
					
//					var formData = new FormData(that.jqForm.get(0));
//					formData.append(this.name, this.value);
					that.submit({ button: this });
					return false;
				});
			});
		}
		
		private buildFormData(submitConfig?: Form.SubmitDirective): FormData {
			var formData = new FormData(<HTMLFormElement> this.jqForm.get(0));
			
			if (submitConfig && submitConfig.button) {
				formData.append(submitConfig.button.name, submitConfig.button.value);
			}
			
			return formData;
		}
		
		private lock: Cmd.Lock;
		private controlLock: ControlLock;
		private controlLockAutoReleaseable = true;
		
		private block() {
			let context: Cmd.Zone;
			if (!this.lock && this.config.blockPage && (context = Cmd.Zone.findFrom(this.jqForm))) {
				this.lock = context.createLock();
			}
			
			if (!this.controlLock && this.config.disableControls) {
				this.disableControls();
			}	
		}
		
		private unblock() {
			if (this.lock) {
				this.lock.release();
				this.lock = null;
			}
			
			if (this.controlLock && this.controlLockAutoReleaseable) {
				this.controlLock.release();
			}
		}
		
		public disableControls(autoReleaseable: boolean = true) {
			this.controlLockAutoReleaseable = autoReleaseable;
			
			if (this.controlLock) return;
			
			this.controlLock = new ControlLock(this.jqForm);
		}
		
		public enableControls() {
			if (this.controlLock) {
				this.controlLock.release();
				this.controlLock = null;
				this.controlLockAutoReleaseable = true;
			}
		}
		
		public abortSubmit() {
			if (this.curXhr) {
				var curXhr = this.curXhr;
				this.curXhr = null;
				curXhr.abort();
				this.unblock();
			}
		}
		
		public submit(submitConfig?: Form.SubmitDirective) {
			this.abortSubmit();
			
			this.trigger(Form.EventType.SUBMIT);
			
			var formData = this.buildFormData(submitConfig);
			var url = this._config.actionUrl || this.jqForm.attr("action");
			
			var that = this;
			var xhr = this.curXhr = <JQuery.jqXHR> $.ajax({
			    "url": url,
			    "type": "POST",
			    "data": formData,
				"cache": false,
			    "processData": false,
			    "contentType": false,
				"dataType": "json",
			    "success": function(data, textStatus, jqXHR) {
					if (that.curXhr !== xhr) return;
					
					if (that._config.successResponseHandler) {
						that._config.successResponseHandler(data);
					} else {
						Rocket.analyzeResponse(Rocket.layerOf(that.jqForm.get(0)), data, url);
					}
					
					if (submitConfig && submitConfig.success) {
						submitConfig.success();
					}
					
					that.unblock();
					that.trigger(Form.EventType.SUBMITTED);
			    },
			    "error": function(jqXHR, textStatus, errorThrown) {
					if (that.curXhr !== xhr) return;
					
                    Rocket.handleErrorResponse(url, jqXHR);
					if (submitConfig && submitConfig.error) {
						submitConfig.error();
					}
					
					that.unblock();
					that.trigger(Form.EventType.SUBMITTED);
			    }
			});
			
			this.block();
		}
		
		public static from(jqForm: JQuery): Form {
			var form = jqForm.data("rocketImplForm");
			if (form instanceof Form) return form;
			
			if (jqForm.length == 0) {
				throw new Error("asd");
			}
			
			form = new Form(jqForm);
			jqForm.data("rocketImplForm", form);
			form.observe();
			return form;
		}
	}
	
	class ControlLock {
		private jqControls: JQuery;
		
		constructor(jqContainer: JQuery) {
			this.jqControls = jqContainer.find("input:not([disabled]), textarea:not([disabled]), button:not([disabled]), select:not([disabled])");
			this.jqControls.prop("disabled", true);
		}
		
		public release() {
			if (!this.jqControls) return;
			
			this.jqControls.prop("disabled", false);
			this.jqControls = null;
		}
	}
	
	export namespace Form {
		export class Config {
			public blockPage = true; 
			public disableControls = true;
			public successResponseHandler: (data: string) => any;
			public autoSubmitAllowed: boolean = true;
			public actionUrl: string = null;
		}
		
		export enum EventType {
			SUBMIT/* = "submit"*/,
			SUBMITTED/* = "submitted"*/
		}
		
		export interface SubmitDirective {
			success?: () => any,
			error?: () => any,
			button?: any
			
		}
	}
	
	export interface FormCallback {
		(form: Form): any
	}
}