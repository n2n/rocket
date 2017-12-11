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
jQuery(document).ready(function($) {
	(function() {
		var Sort = function(jqElem) {
			this.jqElem = jqElem;
			this.jqElemUl = jqElem.find("ul.nav:first");
			this.textAddSort = jqElem.data("text-add-sort");
			this.iconClassNameAdd = jqElem.data("icon-class-name-add");
			this.jqElemEmpty = jqElem.find(".rocket-empty-sort-constraint").removeClass("rocket-empty-sort-constraint").detach();
			this.jqElemAdd = null;
			
			(function(that) {
				this.jqElemAdd = $("<a />", {
					"href": "#",
					"class": "btn btn-secondary"
				}).append($("<i />", {
					"class": this.iconClassNameAdd
				})).append($("<span />", {
					"text": this.textAddSort
				})).appendTo(jqElem).click(function(e) {
					e.preventDefault();
					that.jqElemUl.append(that.jqElemEmpty.clone());
				});
			}).call(this, this);
		};
		
		var initialize = function() {
			$(".rocket-sort").each(function() {
				var jqElem = $(this);
				if (jqElem.data("initialized-sort")) return;
				jqElem.data("initialized-sort", true);
				
				new Sort(jqElem);
			});
		};
		
		if (Jhtml) {
			Jhtml.ready(initialize);
		}
		
		initialize();
		n2n.dispatch.registerCallback(initialize);
	})();
});
