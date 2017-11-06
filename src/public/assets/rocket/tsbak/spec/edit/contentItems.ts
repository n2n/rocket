/// <reference path="..\..\rocket.ts" />
/// <reference path="toMany.ts" />
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
module spec.edit {
	$ = jQuery;
	
	class ContentItemPanel {
		private elemPanel: JQuery;
		private toMany: ToMany;
		private allowedCiSpecIds: Array<string>;
		private name: string;
		
		public constructor(elemPanel: JQuery, availableTypes: Object) {
			this.elemPanel = elemPanel;
			this.name = elemPanel.data("name");
			this.allowedCiSpecIds = elemPanel.data("allowed-ci-spec-ids") || [];
			this.toMany = <ToMany> elemPanel.children(".rocket-to-many:first").data("to-many");
			this.initTypes(availableTypes);
		}
		
		public getName() {
			return this.name;
		}
		
		private initTypes(availableTypes: Object) {
			var types = {}, 
				that = this;
			
			$.each(availableTypes, function(specId, label) {
				if (!that.isSpecIdAllowed(specId)) return;
				types[specId] = label;
			});
			
			that.toMany.setTypes(types);
		}
		
		private isSpecIdAllowed(specId) {
			if (this.allowedCiSpecIds.length === 0) return true;
			
			return this.allowedCiSpecIds.indexOf(specId) >= 0;
		}
	}
	
	class ContentItems {
		private elem: JQuery;
		
		public constructor(elem: JQuery) {
			this.elem = elem;
			var availableTypes = elem.data("ci-ei-spec-labels");
			elem.children(".rocket-content-item-panel").each(function() {
				new ContentItemPanel($(this), availableTypes);
			});
		}
	}
	
	rocketTs.ready(function() {
		rocketTs.registerUiInitFunction(".rocket-content-items", function(elemContentItems: JQuery) {
			new ContentItems(elemContentItems);
		});
	});
}
