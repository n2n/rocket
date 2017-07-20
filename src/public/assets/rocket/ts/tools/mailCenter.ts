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
module tools {
	class MailCenter {
		private elem: JQuery;
		
		public constructor(elem: JQuery) {
			this.elem = elem
			
			elem.find("article").each(function() {
				var elem = $(this);
				var elemMessage = elem.children("dl:first");
				elem.children("header:first").click(function() {
					elemMessage.slideToggle();
				});
				elemMessage.hide();
			});
		}
	}
		
	rocketTs.ready(function() {
		var elemMailCenter = $("#rocket-tools-mail-center");
		if (elemMailCenter.length === 0 ) return;
		
		new MailCenter(elemMailCenter); 
	});
	
}
