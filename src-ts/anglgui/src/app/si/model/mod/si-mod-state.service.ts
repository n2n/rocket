import { Injectable } from '@angular/core';
import { SiEntryQualifier, SiEntryIdentifier } from '../content/si-entry-qualifier';

@Injectable({
	providedIn: 'root'
})
export class SiModStateService {

	private addedEventMap = new Map<string, Map<string, SiEntryIdentifier>>();
	private updatedEventMap = new Map<string, Map<string, SiEntryIdentifier>>();
	private removedEventMap = new Map<string, Map<string, SiEntryIdentifier>>();

	constructor() {

	}

	pushModEvent(event: SiModEvent): void {
		this.addedEventMap.clear();
		if (event.added) {
			for (const ei of event.added) {
				this.reqEiMap(this.addedEventMap, ei.typeId).set(ei.id, ei);
			}
		}

		this.updatedEventMap.clear();
		if (event.updated) {
			for (const ei of event.updated) {
				this.reqEiMap(this.updatedEventMap, ei.typeId).set(ei.id, ei);
			}
		}

		this.removedEventMap.clear();
		if (event.removed) {
			for (const ei of event.removed) {
				this.reqEiMap(this.removedEventMap, ei.typeId).set(ei.id, ei);
			}
		}
	}

	private reqEiMap(map: Map<string, Map<string, SiEntryIdentifier>>, typeId: string): Map<string, SiEntryIdentifier> {
		if (!map.has(typeId)) {
			map.set(typeId, new Map());
		}

		return map.get(typeId);
	}

	containsEntryIdentifier(ei: SiEntryIdentifier): boolean {
		return this.addedEventMap.has(ei.typeId) && this.addedEventMap.get(ei.typeId).has(ei.id)
				&& this.updatedEventMap.has(ei.typeId) && this.updatedEventMap.get(ei.typeId).has(ei.id)
				&& this.removedEventMap.has(ei.typeId) && this.removedEventMap.get(ei.typeId).has(ei.id);
	}
}

export interface SiModEvent {
	added?: SiEntryIdentifier[];
	updated?: SiEntryIdentifier[];
	removed?: SiEntryIdentifier[];
}
