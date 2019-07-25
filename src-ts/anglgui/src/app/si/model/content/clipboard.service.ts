import { Injectable } from '@angular/core';
import { SiQualifier } from 'src/app/si/model/content/si-qualifier';

@Injectable({
  providedIn: 'root'
})
export class ClipboardService {

	private qualifiersMap = new Map<string, SiQualifier[]>();

	constructor() { }

	add(qualifier: SiQualifier) {
		let arr = this.qualifiersMap.get(qualifier.category);
		if (!arr) {
			arr = [];
			this.qualifiersMap.set(qualifier.category, arr);
		}

		arr.push(qualifier);
	}

	clear() {
		this.qualifiersMap.clear();
	}

	containsCategory(category: string): boolean {
		return this.qualifiersMap.has(category);
	}

	getByCategory(category: string): SiQualifier[] {
		return this.qualifiersMap.get(category) || [];
	}
}
