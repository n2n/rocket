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
		
		constructor(jqForm: JQuery) {
			this.jqForm = jqForm;
		}
		
		public observe() {
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
			
			$.ajax({
			    "url": this.jqForm.attr("action"),
			    "type": "POST",
			    "data": formData,
				"cache": false,
			    "processData": false,
			    "contentType": false,
				"dataType": "json",
			    "success": function(data, textStatus, jqXHR){
					var html = n2n.ajah.analyze(data);
					alert(html);
			       	rocket.contextOf(that.jqForm.get(0)).applyHtml(html);
					n2n.ajah.update();
			    },
			    "error": function(jqXHR, textStatus, errorThrown){
			        //if fails     
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