
import { SiEntryInput } from 'src/app/si/model/input/si-entry-input';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { Message } from 'src/app/util/i18n/message';
import { SiEntryIdentifier, SiEntryQualifier } from './si-entry-qualifier';
import { SiEntry } from './si-entry';
import { SiMaskQualifier } from '../meta/si-mask-qualifier';
import { BehaviorSubject, Observable } from 'rxjs';
import { SiGenericValueBoundary } from '../generic/si-generic-value-boundary';
import { SiGenericEntry } from '../generic/si-generic-entry-buildup';
import { skip } from 'rxjs/operators';
import { SiInputResetPoint } from './si-input-reset-point';
import { CallbackInputResetPoint } from './impl/common/model/callback-si-input-reset-point';

export class SiValueBoundary {

	constructor(/*identifier: SiEntryIdentifier, public style: SiStyle*/) {
	}

	isNew(): boolean {
		return this.identifier.id === null || this.identifier.id === undefined;
	}

	get identifier(): SiEntryIdentifier {
		return this.selectedEntry.entryQualifier.entryIdentifier;
	}

	get qualifier(): SiEntryQualifier {
		return this.selectedEntry.entryQualifier;
	}

	get selectedEntry(): SiEntry {
		this.ensureEntrySelected();

		return this._entriesMap.get(this.selectedMaskId!) as SiEntry;
	}

	get entrySelected(): boolean {
		return !!this.selectedMaskId;
	}

	get selectedMaskId(): string|null {
		return this.selectedMaskIdSubject.getValue();
	}

	set selectedMaskId(id: string|null) {
		if (id !== null && !this._entriesMap.has(id)) {
			throw new IllegalSiStateError('Buildup id does not exist on entry: ' + id + '; available buildup ids: '
					+ Array.from(this._entriesMap.keys()).join(', '));
		}

		if (this.selectedMaskId !== id) {
			this.selectedMaskIdSubject.next(id);
		}
	}

	get selectedMaskId$(): Observable<string|null> {
		return this.selectedMaskIdSubject.asObservable();
	}

	get maskQualifiers(): SiMaskQualifier[] {
		return Array.from(this._entriesMap.values())
				.map(buildup => buildup.entryQualifier.maskQualifier);
	}

	get entryQualifiers(): SiEntryQualifier[] {
		const qualifiers: SiEntryQualifier[] = [];
		for (const buildup of this._entriesMap.values()) {
			qualifiers.push(buildup.entryQualifier);
		}
		return qualifiers;
	}

	get replacementValueBoundary(): SiValueBoundary|null {
		return this._replacementValueBoundary;
	}

	// markAsClean() {
	// 	IllegalSiStateError.assertTrue(this.isAvlive());
	// 	this._state = SiEntryState.CLEAN;
	// }

	// protected markAsConsumed() {
	// 	this._entriessMap.clear();
	// 	this._state = SiEntryState.CONSUMED;
	// }

	get state(): SiEntryState {
		return this.stateSubject.getValue();
	}

	get state$(): Observable<SiEntryState> {
		return this.stateSubject.pipe(skip(1));
	}

	public treeLevel: number|null = null;
	private selectedMaskIdSubject = new BehaviorSubject<string|null>(null);
	private _entriesMap = new Map<string, SiEntry>();

	private stateSubject = new BehaviorSubject<SiEntryState>(SiEntryState.CLEAN);

	private lock: SiEntryLock|null = null;
	private _replacementValueBoundary: SiValueBoundary|null = null;

	private ensureEntrySelected() {
		if (this.selectedMaskId !== null) {
			return;
		}

		throw new IllegalSiStateError('No entry selected for value boundary: '
				+ this.entryQualifiers.map((q) => q.toString()).join(','));
	}

	containsMaskId(maskId: string): boolean {
		return this._entriesMap.has(maskId);
	}

	isMultiType(): boolean {
		return this._entriesMap.size > 1;
	}

	addEntry(entry: SiEntry) {
		this._entriesMap.set(entry.entryQualifier.maskQualifier.maskIdentifier.id, entry);
	}

	containsEntryId(id: string): boolean {
		return this._entriesMap.has(id);
	}

	// getEntryById(id: string): SiEntry {
	// 	if (this.containsEntryId(id)) {
	// 		return this._entriessMap.get(id)!;
	// 	}
	//
	// 	throw new UnknownSiElementError('Unkown SiEntry id ' + id);
	// }

// 	getFieldById(id: string): SiField|null {
// 		return this.selectedEntry.getFieldById(id);
// 	}

	readInput(): SiEntryInput {
		if (this.replacementValueBoundary) {
			throw new IllegalSiStateError('SiEntry already replaced!');
		}
		
		const fieldInputMap = new Map<string, object>();

		for (const [id, field] of this.selectedEntry.getFieldMap()) {
			if (!field.hasInput() || field.isDisabled()) {
				continue;
			}

			fieldInputMap.set(id, field.readInput());
		}

		// if (fieldInputMap.size === 0) {
		// 	throw new IllegalSiStateError('No input available.');
		// }

		return new SiEntryInput(this.selectedMaskId!, this.qualifier.entryIdentifier.id, fieldInputMap);
	}

	// handleError(error: SiEntryError) {
	// 	for (const [propId, fieldError] of error.fieldErrors) {
	// 		if (!this.selectedEntry.containsPropId(propId)) {
	// 			this.selectedEntry.messages.push(...fieldError.getAllMessages());
	// 			continue;
	// 		}

	// 		const field = this.selectedEntry.getFieldById(propId);
	// 		field.handleError(fieldError);
	// 	}
	// }

	// resetError() {
	// 	for (const [, buildup] of this._entriessMap) {
	// 		buildup.messages = [];

	// 		for (const [, field] of this.selectedEntry.getFieldMap()) {
	// 			field.resetError();
	// 		}
	// 	}
	// }

	getMessages(): Message[] {
		const messages: Message[] = [];

		if (!this.selectedMaskId) {
			return messages;
		}

		for (const siField of this.selectedEntry.getFields()) {
			messages.push(...siField.getMessages());
		}

		return messages;
	}

	toString() {
		return this.identifier.toString();
	}

	// copy(): SiEntry {
	// 	const entry = new SiEntry(this.identifier);
	// 	entry.treeLevel = this.treeLevel;

	// 	for (const buildup of this._entriessMap.values()) {
	// 		entry.addEntry(buildup.copy());
	// 	}

	// 	entry.selectedTypeId = this.selectedTypeId;
	// 	return entry;
	// }

	async copy(): Promise<SiGenericValueBoundary> {
		const promises: Promise<void>[] = [];

		const genericEntries: SiGenericEntry[] = [];
		for (const [maskId, entry] of this._entriesMap) {
			entry.copy().then(genericEntry => {
				genericEntries.push(genericEntry);
			});
		}

		await Promise.all(promises);

		return this.createGenericValueBoundary(genericEntries);
	}

	async paste(genericValueBoundary: SiGenericValueBoundary): Promise<boolean> {
		if (!this.valGenericEntry(genericValueBoundary)) {
			return false;
		}

		if (this._entriesMap.has(genericValueBoundary.selectedMaskId!)) {
			this.selectedMaskId = genericValueBoundary.selectedMaskId;
		}

		const promises = new Array<Promise<boolean>>();
		for (const genericEntry of genericValueBoundary.entries) {
			const maskId = genericEntry.maskId;
			if (this._entriesMap.has(maskId)) {
				promises.push(this._entriesMap.get(maskId)!.paste(genericEntry));
			}
		}
		await Promise.all(promises);

		return true;
	}

	async createInputResetPoint(): Promise<SiInputResetPoint> {
		const promise = Promise.all(Array
				.from(this._entriesMap.values())
				.map(entry => entry.createInputResetPoint()));

		const entryResetPoints = await promise;

		return new CallbackInputResetPoint(
				{ selectedEntryId: this.selectedMaskId, entryResetPoints},
				(data) => {
					this.selectedMaskId = data.selectedEntryId;
					data.entryResetPoints.forEach(rp => { rp.rollbackTo(); });
				});
	}

	private createGenericValueBoundary(genericEntries: SiGenericEntry[]): SiGenericValueBoundary {
		const genericEntry = new SiGenericValueBoundary(this.selectedMaskId, genericEntries);
		// genericEntry.style = this.style;
		return genericEntry;
	}


	private valGenericEntry(genericValueBoundary: SiGenericValueBoundary): boolean {
		for (const entry of genericValueBoundary.entries) {
			if (!this.containsMaskId(entry.maskId)) {
				return false;
			}
		}

		return true;

		// return genericValueBoundary.matchMasks(tidentifier.maskIdentifier.matches(this.identifier.maskIdentifier);

		// if (genericValueBoundary.identifier.typeId !== this.identifier.typeId) {
		// 	return false;
		// 	// throw new GenericMissmatchError('SiEntry missmatch: '
		// 	// 		+ genericEntry.identifier.toString() + ' != ' + this.identifier.toString());
		// }
		//
		// if (genericValueBoundary.style.bulky !== this.style.bulky || genericValueBoundary.style.readOnly !== this.style.readOnly) {
		// 	return false;
		// 	// throw new GenericMissmatchError('SiEntry missmatch.');
		// }
		//
		// return true;
	}

	isClean(): boolean {
		return this.state === SiEntryState.CLEAN;
	}

	isAlive(): boolean {
		return this.state !== SiEntryState.REPLACED && this.state !== SiEntryState.REMOVED;
	}

	isClaimed(): boolean {
		return this.state === SiEntryState.LOCKED || this.state === SiEntryState.RELOADING
				|| this.state === SiEntryState.OUTDATED;
	}

	// consume(entry: SiEntry) {
	// 	IllegalArgumentError.assertTrue(entry.state === SiEntryState.CLEAN);
	// 	IllegalSiStateError.assertTrue(this.isAvlive());

	// 	for (const [entryId, entry] of this._entriessMap) {
	// 		entry.consume(entry.getEntryById(entryId));
	// 	}

	// 	entry.markAsConsumed();
	// }


	markAsOutdated(): void {
		IllegalSiStateError.assertTrue(this.state === SiEntryState.CLEAN || this.state === SiEntryState.LOCKED
				|| this.state === SiEntryState.OUTDATED, 'SiEntry not outdated, clean or locked: ' + this.state);
		this.stateSubject.next(SiEntryState.OUTDATED);
	}

	markAsReloading(): void {
		IllegalSiStateError.assertTrue(this.isAlive());
		this.stateSubject.next(SiEntryState.RELOADING);
	}

	markAsRemoved(): void {
		IllegalSiStateError.assertTrue(this.isAlive());
		this.stateSubject.next(SiEntryState.REMOVED);
		this.stateSubject.complete();
	}

	createLock(): SiEntryLock {
		IllegalSiStateError.assertTrue(this.state === SiEntryState.CLEAN,'SiEntry not clean: ' + this.state);

		this.stateSubject.next(SiEntryState.LOCKED);
		const lock = this.lock = {
			release: () => {
				if (this.lock !== lock) {
					throw new IllegalSiStateError('Lock already released.');
				}

				this.lock = null;

				if (!this.isAlive() || this.state === SiEntryState.RELOADING || this.state === SiEntryState.OUTDATED) {
					return;
				}

				IllegalSiStateError.assertTrue(this.state === SiEntryState.LOCKED,
						'SiEntry not locked, loading, outdated nor dead: ' + this.state);
				this.stateSubject.next(SiEntryState.CLEAN);
			}
		};

		return this.lock;
	}

	replace(replacementEntry: SiValueBoundary): void {
		IllegalSiStateError.assertTrue(this.isAlive());

		this._replacementValueBoundary = replacementEntry;
		this.stateSubject.next(SiEntryState.REPLACED);
		this.stateSubject.complete();
	}

	// private uiClaimedSubject = new BehaviorSubject<boolean>(false);
	// private uiClaims = 0;

	// uiClaim() {
	// 	this.uiClaims++;
	// }

	// uiUnclaim() {
	// 	if (this.uiClaims <= 0) {
	// 		throw new IllegalStateError();
	// 	}

	// 	this.uiClaims--;
	// 	this.updateClaimedSubject();
	// }

	// private updateClaimedSubject() {
	// 	var uiClaimed = this.uiClaims > 0;

	// 	if (this.uiClaimed !== claimed) {
	// 		this.uiClaimedSubject.next(claimed);
	// 	}
	// }

	// get uiClaimed$(): Observable<boolean> {
	// 	return this.uiClaimedSubject.asObservable();
	// }

	// get uiClaimed(): boolean {
	// 	return this.uiClaimedSubject.getValue();
	// }

	getFinalReplacementEntry(): SiValueBoundary {
		let siValueBoundary: SiValueBoundary = this;

		while (siValueBoundary.replacementValueBoundary) {
			siValueBoundary = siValueBoundary.replacementValueBoundary;
		}

		return siValueBoundary;
	}
}

export interface SiEntryLock {
	release(): void;
}



export enum SiEntryState {
	CLEAN = 'CLEAN',
	OUTDATED = 'OUTDATED',
	LOCKED = 'LOCKED',
	RELOADING = 'RELOADING',
	REMOVED = 'REMOVED',
	REPLACED = 'REPLACED'
}
