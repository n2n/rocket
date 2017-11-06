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
module storage {
	export class CookieStorage {
		public getValue(name: string) {
			var nameEQ = name + "=";
		    var ca = document.cookie.split(';');
		    for(var i = 0; i < ca.length; i++) {
		        var c = ca[i];
		        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
		        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
		    }
		    return null;
		}
		
		public setValue(name: string, value: string, hours: number = null) {
			var expires, date;
		    if (hours) {
		        date = new Date();
		        date.setTime(date.getTime() + (hours * 60 * 60 * 1000));
		        expires = "; expires=" + date.toGMTString();
		    } else {
		    	expires = "";
		    }
		    document.cookie = name + "=" + value + expires + "; path=/";
		}
	}	
}
