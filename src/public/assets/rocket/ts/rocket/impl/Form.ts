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
		private 
		
		constructor(jqForm: JQuery) {
			this.jqForm = jqForm;
		}
		
		get observing() {
			return this._observing;
		}
		
		public observe() {
			if (this._observing) return;
			
			var that = this;
			this.jqForm.submit(function () {
				that.submit(new FormData(this));
				return false;
			});
			
			var that = this;
			this.jqForm.find("input[type=submit], button[type=submit]").each(function () {
				$(this).click(function () {
					var formData = new FormData(that.jqForm.get(0));
					formData.append(this.name, this.value);
					that.submit(formData);
					return false;
				});
			});
		}
		
		private submit(formData: FormData) {
			var that = this;
			var url = this.jqForm.attr("action");
			$.ajax({
			    "url": url,
			    "type": "POST",
			    "data": formData,
				"cache": false,
			    "processData": false,
			    "contentType": false,
				"dataType": "json",
			    "success": function(data, textStatus, jqXHR) {
					rocket.analyzeResponse(rocket.layerOf(that.jqForm.get(0)), data, url);
			    },
			    "error": function(jqXHR, textStatus, errorThrown) {
                    rocket.handleErrorResponse(url, jqXHR);     
			    }
			});
		}
		
		public static scan(jqForm: JQuery) {
			var form = jqForm.data("rocketImplForm");
			if (form) return form;
			
			form = new Form(jqForm);
			jqForm.data("rocketImplForm", form);
			form.observe();
			return form;
		}
	}
}