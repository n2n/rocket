import { Injectable } from '@angular/core';
import { SiEntryQualifier } from 'src/app/si/model/content/si-qualifier';

@Injectable({
	providedIn: 'root'
})
export class ClipboardService {

	private qualifiersMap = new Map<string, SiEntryQualifier[]>();

	constructor() { }

	add(qualifier: SiEntryQualifier) {
		let arr = this.qualifiersMap.get(qualifier.typeCategory);
		if (!arr) {
			arr = [];
			this.qualifiersMap.set(qualifier.typeCategory, arr);
		}

		arr.push(qualifier);
	}

	clear() {
		this.qualifiersMap.clear();
	}

	containsCategory(category: string): boolean {
		return this.qualifiersMap.has(category);
	}

	getByCategory(category: string): SiEntryQualifier[] {
		return this.qualifiersMap.get(category) || [];
	}
}
