
import { SiEntryInput } from 'src/app/si/model/input/si-entry-input';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiEntryError } from 'src/app/si/model/input/si-entry-error';
import { Message } from 'src/app/util/i18n/message';
import { SiEntryIdentifier, SiEntryQualifier } from './si-qualifier';
import { SiEntryBuildup } from './si-entry-buildup';
import { SiTypeQualifier } from '../meta/si-type-qualifier';

export class SiEntry {
	public treeLevel: number|null = null;
	private _selectedTypeId: string|null = null;
	public bulky = false;
	public readOnly = true;
	private _entryBuildupsMap = new Map<string, SiEntryBuildup>();

	constructor(readonly identifier: SiEntryIdentifier) {
	}

	private ensureBuildups() {
		if (this._selectedTypeId) {
			return;
		}

		throw new IllegalSiStateError('No buildup selected for entry: ' + this.toString());
	}

	get qualifier(): SiEntryQualifier {
		return this.selectedEntryBuildup.entryQualifier;
	}

	get selectedEntryBuildup(): SiEntryBuildup {
		return this._entryBuildupsMap.get(this.selectedTypeId) as SiEntryBuildup;
	}

	get selectedTypeId(): string {
		this.ensureBuildups();

		return this._selectedTypeId;
	}

	set selectedTypeId(id: string) {
		if (!this._entryBuildupsMap.has(id)) {
			throw new IllegalSiStateError('Buildup id does not exist on entry: ' + id);
		}

		this._selectedTypeId = id;
	}

	get typeQualifiers(): SiTypeQualifier[] {
		return Array.from(this._entryBuildupsMap.values()).map(buildup => buildup.entryQualifier.typeQualifier);
	}

	get entryQualifiers(): SiEntryQualifier[] {
		const qualifiers: SiEntryQualifier[] = [];
		for (const buildup of this._entryBuildupsMap.values()) {
			qualifiers.push(buildup.entryQualifier);
		}
		return qualifiers;
	}

	addEntryBuildup(buildup: SiEntryBuildup) {
		this._entryBuildupsMap.set(buildup.entryQualifier.typeQualifier.id, buildup);
		if (!this._selectedTypeId) {
			this._selectedTypeId = buildup.entryQualifier.typeQualifier.id;
		}
	}

// 	getFieldById(id: string): SiField|null {
// 		return this.selectedEntryBuildup.getFieldById(id);
// 	}

	readInput(): SiEntryInput {
		const fieldInputMap = new Map<string, object>();

		for (const [id, field] of this.selectedEntryBuildup.fieldMap) {
			if (!field.hasInput()) {
				continue;
			}

			fieldInputMap.set(id, field.readInput());
		}

		if (fieldInputMap.size === 0) {
			throw new IllegalSiStateError('No input available.');
		}

		return new SiEntryInput(this.qualifier, this._selectedTypeId, this.bulky, fieldInputMap);
	}

	handleError(error: SiEntryError) {
		for (const [fieldId, fieldError] of error.fieldErrors) {
			if (!this.selectedEntryBuildup.fieldMap.has(fieldId)) {
				this.selectedEntryBuildup.messages.push(...fieldError.getAllMessages());
				continue;
			}

			const field = this.selectedEntryBuildup.getFieldById(fieldId);
			field.handleError(fieldError);
		}
	}

	resetError() {
		for (const [, buildup] of this._entryBuildupsMap) {
			buildup.messages = [];

			for (const [, field] of this.selectedEntryBuildup.fieldMap) {
				field.resetError();
			}
		}
	}

	getMessages(): Message[] {
		const messages: Message[] = [];

		for (const siField of this.selectedEntryBuildup.getFields()) {
			messages.push(...siField.getMessages());
		}

		return messages;
	}

	toString() {
		return this.qualifier.toString();
	}

	copy(): SiEntry {
		const entry = new SiEntry(this.identifier);
		entry.treeLevel = this.treeLevel;

		for (const buildup of this._entryBuildupsMap.values()) {
			entry.addEntryBuildup(buildup.copy());
		}

		entry.selectedTypeId = this.selectedTypeId;
		return entry;
	}
}
