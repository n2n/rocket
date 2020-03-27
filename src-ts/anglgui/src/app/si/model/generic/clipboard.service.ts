import { Injectable } from '@angular/core';
import { SiGenericValue } from './si-generic-value';

@Injectable({
	providedIn: 'root'
})
export class ClipboardService {

	private genericValues: SiGenericValue[] = [];

	constructor() { }

	add(genericValue: SiGenericValue) {
		this.genericValues.push(genericValue);
	}

	filter<T>(type: new(...args: any[]) => T): T[] {
		return this.genericValues.filter(genericValue => genericValue.isInstanceOf(type))
				.map(genericValue => genericValue.readInstance(type));
	}
}
