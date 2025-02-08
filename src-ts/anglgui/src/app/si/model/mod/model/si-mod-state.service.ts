import { Injectable } from '@angular/core';
import { SiValueBoundary } from '../../content/si-value-boundary';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { BehaviorSubject, Observable, Subject } from 'rxjs';
import { skip } from 'rxjs/operators';
import { Message } from 'src/app/util/i18n/message';
import { SiObjectIdentifier } from '../../content/si-object-qualifier';

@Injectable({
	providedIn: 'root'
})
export class SiModStateService {

	private lastModEventSubject = new BehaviorSubject<SiModEvent|null>(null);
	private lastMessagesSubject = new BehaviorSubject<Message[]>([]);

	private shownEntriesMap = new Map<SiValueBoundary, object[]>();
	private shownEntrySubject = new Subject<SiValueBoundary>();

	constructor() {
	}

	pushModEvent(event: SiModEvent): void {
		this.lastModEventSubject.next(event);
	}

	pushMessages(messages: Message[]): void {
		this.lastMessagesSubject.next(messages);
	}

	get modEvent$(): Observable<SiModEvent|null> {
		return this.lastModEventSubject.pipe(skip(1));
	}

	get lastModEvent(): SiModEvent|null {
		return this.lastModEventSubject.getValue();
	}

	// containsModEntryIdentifier(ei: SiEntryIdentifier): boolean {
	// 	return this.lastModEvent && this.lastModEvent.containsModEntryIdentifier(ei);
	// }

	isEntryShown(entry: SiValueBoundary): boolean {
		return this.shownEntriesMap.has(entry);
	}

	get shownEntry$(): Observable<SiValueBoundary> {
		return this.shownEntrySubject.asObservable();
	}

	get lastMessages(): Message[] {
		return this.lastMessagesSubject.getValue();
	}

	get messages$(): Observable<Message[]> {
		return this.lastMessagesSubject.asObservable();
	}

	registerShownEntry(entry: SiValueBoundary, refObj: object): void {
		if (!this.shownEntriesMap.has(entry)) {
			this.shownEntriesMap.set(entry, []);
		}

		const objects = this.shownEntriesMap.get(entry)!;
		if (-1 < objects.indexOf(refObj)) {
			throw new IllegalSiStateError('Entry already shown.');
		}

		objects.push(refObj);
		this.shownEntrySubject.next(entry);
	}

	unregisterShownEntry(entry: SiValueBoundary, refObj: object): void {
		if (!this.shownEntriesMap.has(entry)) {
			throw new IllegalSiStateError('Entry not shown.');
		}

		const objects = this.shownEntriesMap.get(entry)!;
		const i = objects.indexOf(refObj);
		if (-1 === i) {
			throw new IllegalSiStateError('Entry not shown.');
		}

		objects.splice(i, 1);
	}
}

export class SiModEvent {

	private addedEventMap = new Map<string, Map<string, SiObjectIdentifier>>();
	private updatedEventMap = new Map<string, Map<string, SiObjectIdentifier>>();
	private removedEventMap = new Map<string, Map<string, SiObjectIdentifier>>();

	constructor(readonly added: SiObjectIdentifier[], readonly updated: SiObjectIdentifier[], readonly removed: SiObjectIdentifier[]) {
		this.update();
	}

	private update(): void {
		this.addedEventMap.clear();
		for (const ei of this.added) {
			IllegalSiStateError.assertTrue(ei.id !== null);
			this.reqEiMap(this.addedEventMap, ei.superTypeId).set(ei.id!, ei);
		}

		this.updatedEventMap.clear();
		for (const ei of this.updated) {
			IllegalSiStateError.assertTrue(ei.id !== null);
			this.reqEiMap(this.updatedEventMap, ei.superTypeId).set(ei.id!, ei);
		}

		this.removedEventMap.clear();
		for (const ei of this.removed) {
			IllegalSiStateError.assertTrue(ei.id !== null);
			this.reqEiMap(this.removedEventMap, ei.superTypeId).set(ei.id!, ei);
		}
	}

	private reqEiMap(map: Map<string, Map<string, SiObjectIdentifier>>, superTypeId: string): Map<string, SiObjectIdentifier> {
		if (!map.has(superTypeId)) {
			map.set(superTypeId, new Map());
		}

		return map.get(superTypeId)!;
	}

	containsModEntryIdentifier(ei: SiObjectIdentifier): boolean {
		if (ei.id === null) {
			return false;
		}

		return (this.addedEventMap.has(ei.superTypeId) && this.addedEventMap.get(ei.superTypeId)!.has(ei.id))
				|| (this.updatedEventMap.has(ei.superTypeId) && this.updatedEventMap.get(ei.superTypeId)!.has(ei.id))
				|| (this.removedEventMap.has(ei.superTypeId) && this.removedEventMap.get(ei.superTypeId)!.has(ei.id));
	}

	containsAddedTypeId(typeId: string): boolean {
		return this.addedEventMap.has(typeId);
	}

	containsUpdatedTypeId(typeId: string): boolean {
		return this.updatedEventMap.has(typeId);
	}

	containsRemovedTypeId(typeId: string): boolean {
		return this.removedEventMap.has(typeId);
	}

}
