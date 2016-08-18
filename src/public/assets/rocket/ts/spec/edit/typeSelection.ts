/// <reference path="..\..\rocket.ts" />
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
	$ = jQuery;
	
	class TypeSelection {
		private elemEntryForm;
		private elemSelect;
		private types: Object = {}
		private elemCurrentType: JQuery = null;
		
		public constructor(elemEntryForm: JQuery) {
			this.elemEntryForm = elemEntryForm;
			this.elemSelect = elemEntryForm.find("> .rocket-script-type-selector .rocket-script-type-selection");
			
			(function(that: TypeSelection) {
				that.elemSelect.children().each(function() {
					var value = $(this).val();
					that.types[value] = elemEntryForm.children(".rocket-script-type-" + value).detach();
				})
				
				that.elemSelect.change(function() {
					if (null !== that.elemCurrentType) {
						that.elemCurrentType.detach();	
					}
					
					that.elemCurrentType = that.types[that.elemSelect.val()].appendTo(that.elemEntryForm);
					rocketTs.updateUi();
					
				}).change();
			}).call(this, this);
		}
	}
		
	rocketTs.ready(function($) {
		rocketTs.registerUiInitFunction(".rocket-type-dependent-entry-form", function(elemEntryForm: JQuery) {
			new TypeSelection(elemEntryForm);
		});
	});
}
