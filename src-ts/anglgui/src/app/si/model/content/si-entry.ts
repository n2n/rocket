
import { SiEntryInput } from 'src/app/si/model/input/si-entry-input';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiEntryError } from 'src/app/si/model/input/si-entry-error';
import { Message } from 'src/app/util/i18n/message';
import { SiEntryIdentifier, SiEntryQualifier } from './si-entry-qualifier';
import { SiEntryBuildup } from './si-entry-buildup';
import { SiMaskQualifier } from '../meta/si-mask-qualifier';
import { BehaviorSubject, Observable } from 'rxjs';
import { SiGenericEntry } from '../generic/si-generic-entry';
import { SiGenericEntryBuildup } from '../generic/si-generic-entry-buildup';
import { GenericMissmatchError } from '../generic/generic-missmatch-error';

export class SiEntry {
	public treeLevel: number|null = null;
	private selectedTypeIdSubject = new BehaviorSubject<string|null>(null);
	public bulky = false;
	public readOnly = true;
	private _entryBuildupsMap = new Map<string, SiEntryBuildup>();

	constructor(readonly identifier: SiEntryIdentifier) {
	}

	private ensureBuildupSelected() {
		if (this.selectedTypeId !== null) {
			return;
		}

		throw new IllegalSiStateError('No buildup selected for entry: ' + this.toString());
	}

	get qualifier(): SiEntryQualifier {
		return this.selectedEntryBuildup.entryQualifier;
	}

	get selectedEntryBuildup(): SiEntryBuildup {
		this.ensureBuildupSelected();

		return this._entryBuildupsMap.get(this.selectedTypeId) as SiEntryBuildup;
	}

	get selectedTypeId(): string|null {
		return this.selectedTypeIdSubject.getValue();
	}

	set selectedTypeId(id: string|null) {
		if (id !== null && !this._entryBuildupsMap.has(id)) {
			throw new IllegalSiStateError('Buildup id does not exist on entry: ' + id);
		}

		this.selectedTypeIdSubject.next(id);
	}

	get selectedTypeId$(): Observable<string|null> {
		return this.selectedTypeIdSubject;
	}

	containsTypeId(typeId: string): boolean {
		return this._entryBuildupsMap.has(typeId);
	}

	isMultiType(): boolean {
		return this._entryBuildupsMap.size > 1;
	}

	get maskQualifiers(): SiMaskQualifier[] {
		return Array.from(this._entryBuildupsMap.values()).map(buildup => buildup.entryQualifier.maskQualifier);
	}

	get entryQualifiers(): SiEntryQualifier[] {
		const qualifiers: SiEntryQualifier[] = [];
		for (const buildup of this._entryBuildupsMap.values()) {
			qualifiers.push(buildup.entryQualifier);
		}
		return qualifiers;
	}

	addEntryBuildup(buildup: SiEntryBuildup) {
		this._entryBuildupsMap.set(buildup.entryQualifier.maskQualifier.identifier.typeId, buildup);
	}

// 	getFieldById(id: string): SiField|null {
// 		return this.selectedEntryBuildup.getFieldById(id);
// 	}

	readInput(): SiEntryInput {
		const fieldInputMap = new Map<string, object>();

		for (const [id, field] of this.selectedEntryBuildup.getFieldMap()) {
			if (!field.hasInput()) {
				continue;
			}

			fieldInputMap.set(id, field.readInput());
		}

		if (fieldInputMap.size === 0) {
			throw new IllegalSiStateError('No input available.');
		}

		return new SiEntryInput(this.qualifier.identifier, this.selectedTypeId, this.bulky, fieldInputMap);
	}

	handleError(error: SiEntryError) {
		for (const [propId, fieldError] of error.fieldErrors) {
			if (!this.selectedEntryBuildup.containsPropId(propId)) {
				this.selectedEntryBuildup.messages.push(...fieldError.getAllMessages());
				continue;
			}

			const field = this.selectedEntryBuildup.getFieldById(propId);
			field.handleError(fieldError);
		}
	}

	resetError() {
		for (const [, buildup] of this._entryBuildupsMap) {
			buildup.messages = [];

			for (const [, field] of this.selectedEntryBuildup.getFieldMap()) {
				field.resetError();
			}
		}
	}

	getMessages(): Message[] {
		const messages: Message[] = [];

		if (!this.selectedTypeId) {
			return messages;
		}

		for (const siField of this.selectedEntryBuildup.getFields()) {
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

	// 	for (const buildup of this._entryBuildupsMap.values()) {
	// 		entry.addEntryBuildup(buildup.copy());
	// 	}

	// 	entry.selectedTypeId = this.selectedTypeId;
	// 	return entry;
	// }

	copy(): SiGenericEntry {
		const genericBuildupsMap = new Map<string, SiGenericEntryBuildup>();
		for (const [typeId, entryBuildup] of this._entryBuildupsMap) {
			genericBuildupsMap.set(typeId, entryBuildup.copy());
		}
		return this.createGenericEntry(genericBuildupsMap);
	}

	paste(genericEntry: SiGenericEntry): Promise<void> {
		this.valGenericEntry(genericEntry);

		if (this._entryBuildupsMap.has(genericEntry.selectedTypeId)) {
			this.selectedTypeId = genericEntry.selectedTypeId;
		}

		const promises = new Array<Promise<void>>();
		for (const [typeId, genericEntryBuildup] of genericEntry.entryBuildupsMap) {
			if (this._entryBuildupsMap.has(typeId)) {
				promises.push(this._entryBuildupsMap.get(typeId).paste(genericEntryBuildup));
			}
		}
		return Promise.all(promises).then(() => {});
	}

	createResetPoint(): SiGenericEntry {
		const genericBuildupsMap = new Map<string, SiGenericEntryBuildup>();
		for (const [typeId, entryBuildup] of this._entryBuildupsMap) {
			genericBuildupsMap.set(typeId, entryBuildup.createResetPoint());
		}
		return this.createGenericEntry(genericBuildupsMap);
	}

	private createGenericEntry(genericBuildupsMap: Map<string, SiGenericEntryBuildup>): SiGenericEntry {
		const genericEntry = new SiGenericEntry(this.identifier, this.selectedTypeId, genericBuildupsMap);
		genericEntry.bulky = this.bulky;
		genericEntry.readOnly = this.readOnly;
		return genericEntry;
	}

	resetToPoint(genericEntry: SiGenericEntry): void {
		this.valGenericEntry(genericEntry);

		for (const [typeId, genericEntryBuildup] of genericEntry.entryBuildupsMap) {
			if (this._entryBuildupsMap.has(typeId)) {
				this._entryBuildupsMap.get(typeId).resetToPoint(genericEntryBuildup);
			}
		}
	}

	private valGenericEntry(genericEntry: SiGenericEntry) {
		if (genericEntry.identifier.typeId !== this.identifier.typeId) {
			throw new GenericMissmatchError('SiEntry missmatch: '
					+ genericEntry.identifier.toString() + ' != ' + this.identifier.toString());
		}

		if (genericEntry.bulky !== this.bulky || genericEntry.readOnly !== this.readOnly) {
			throw new GenericMissmatchError('SiEntry missmatch.');
		}
	}
}
