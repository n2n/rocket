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
module storage {
	export class WebStorage {
		private baseStorageKey;
		private storage: Storage; 
		private data: Object = {};
		
		public constructor(storageKey, storage: Storage = null) {
			this.baseStorageKey = storageKey;
			this.storage = storage;
			
			if (null !== storage) {
				var dataString = this.storage.getItem(storageKey);
				if (null !== dataString) {
					this.data = $.parseJSON(dataString);
				}	
			}
		}
		
		public isAvailable() {
			return null !== this.storage;
		}
		
		public getData(key: string) {
			if (!this.isAvailable() || !this.data.hasOwnProperty(key)) return null;
			
			return this.data[key];
		}
		
		public hasData(key: string): boolean {
			if (!this.isAvailable()) return false;
			
			return this.data.hasOwnProperty(key);
		}
		
		public setData(key: string, data) {
			if (!this.isAvailable()) return
			
			this.data[key] = data;
			this.storage.setItem(this.baseStorageKey, JSON.stringify(this.data));
		}
	}
}
