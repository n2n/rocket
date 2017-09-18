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
namespace rocket.impl {
	var $ = jQuery;
	
	export class Form {
		private jqForm;
		private _observing = false;
		private _config: Form.Config = new Form.Config();
		private callbackRegistery: util.CallbackRegistry<FormCallback> = new util.CallbackRegistry<FormCallback>();
		private curXhr: JQueryXHR = null;
		
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
			var formData = new FormData(this.jqForm.get(0));
			
			if (submitConfig && submitConfig.button) {
				formData.append(submitConfig.button.name, submitConfig.button.value);
			}
			
			return formData;
		}
		
		public submit(submitConfig?: Form.SubmitDirective) {
			if (this.curXhr) {
				var curXhr = this.curXhr;
				this.curXhr = null;
				curXhr.abort();
			}
			
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
			    "success": function(data, textStatus, jqXHR) {
					if (that.curXhr !== xhr) return;
					
					if (that._config.successResponseHandler) {
						that._config.successResponseHandler(data);
					} else {
						rocket.analyzeResponse(rocket.layerOf(that.jqForm.get(0)), data, url);
					}
					
					if (submitConfig && submitConfig.success) {
						submitConfig.success();
					}
					that.trigger(Form.EventType.SUBMITTED);
			    },
			    "error": function(jqXHR, textStatus, errorThrown) {
					if (that.curXhr !== xhr) return;
					
                    rocket.handleErrorResponse(url, jqXHR);
					if (submitConfig && submitConfig.error) {
						submitConfig.error();
					}     
					that.trigger(Form.EventType.SUBMITTED);
			    }
			});
		}
		
		public static from(jqForm: JQuery): Form {
			var form = jqForm.data("rocketImplForm");
			if (form instanceof Form) return form;
			
			form = new Form(jqForm);
			jqForm.data("rocketImplForm", form);
			form.observe();
			return form;
		}
	}
	
	export namespace Form {
		export class Config {
			public blockContext = true; 
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