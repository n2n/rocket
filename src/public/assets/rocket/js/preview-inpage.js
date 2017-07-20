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
(function() {
	var trigger = function() {

		var elements = document.getElementsByClassName("rocket-preview-inpage-component");
		for (var i in elements) {
			if (elements[i].tagName != 'TEXTAREA') continue;
		
			var event = function() {
				this.style.height = this.scrollHeight + 'px';
			};

			addEvent(elements[i], 'keyup', event);
			addEvent(elements[i], 'keydown', event);
			addEvent(elements[i], 'click', event);
			addEvent(elements[i], 'change', event);
			
			elements[i].style.height = elements[i].scrollHeight + 'px';
		}
	};


	if (document.addEventListener) {
	    document.addEventListener("DOMContentLoaded", trigger, false);
	} else {
		window.addEvent("onload", trigger);
	}
	
	function addEvent( obj, type, fn ){
	   if (obj.addEventListener) {
	      obj.addEventListener( type, fn, false );
	   } else if (obj.attachEvent) {
	      obj["e"+type+fn] = fn;
	      obj[type+fn] = function() { obj["e"+type+fn]( window.event ); }
	      obj.attachEvent( "on"+type, obj[type+fn] );
	   }
	}
})();
