import { Injectable } from '@angular/core';
import { SiEntryQualifier, SiEntryIdentifier } from '../../content/si-entry-qualifier';
import { SiEntry } from '../../content/si-entry';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { Subject, Observable } from 'rxjs';

@Injectable({
	providedIn: 'root'
})
export class SiModStateService {

	private addedEventMap = new Map<string, Map<string, SiEntryIdentifier>>();
	private updatedEventMap = new Map<string, Map<string, SiEntryIdentifier>>();
	private removedEventMap = new Map<string, Map<string, SiEntryIdentifier>>();

	private shownEntriesMap = new Map<SiEntry, object[]>();
	private shownEntrySubject = new Subject<SiEntry>();

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

	containsModEntryIdentifier(ei: SiEntryIdentifier): boolean {
		return (this.addedEventMap.has(ei.typeId) && this.addedEventMap.get(ei.typeId).has(ei.id))
				|| (this.updatedEventMap.has(ei.typeId) && this.updatedEventMap.get(ei.typeId).has(ei.id))
				|| (this.removedEventMap.has(ei.typeId) && this.removedEventMap.get(ei.typeId).has(ei.id));
	}

	isEntryShown(entry: SiEntry): boolean {
		return this.shownEntriesMap.has(entry);
	}

	get shownEntry$(): Observable<SiEntry> {
		return this.shownEntrySubject.asObservable();
	}

	registerShownEntry(entry: SiEntry, refObj: object) {
		if (!this.shownEntriesMap.has(entry)) {
			this.shownEntriesMap.set(entry, []);
		}

		const objects = this.shownEntriesMap.get(entry);
		if (-1 === objects.indexOf(refObj)) {
			throw new IllegalSiStateError('Entry already shown.');
		}

		objects.push(refObj);
		this.shownEntrySubject.next(entry);
	}

	unregisterShownEntry(entry: SiEntry, refObj: object) {
		if (!this.shownEntriesMap.has(entry)) {
			throw new IllegalSiStateError('Entry not shown.');
		}

		const objects = this.shownEntriesMap.get(entry);
		const i = objects.indexOf(refObj);
		if (-1 === i) {
			throw new IllegalSiStateError('Entry not shown.');
		}

		objects.splice(i, 1);
	}
}

export interface SiModEvent {
	added?: SiEntryIdentifier[];
	updated?: SiEntryIdentifier[];
	removed?: SiEntryIdentifier[];
}
