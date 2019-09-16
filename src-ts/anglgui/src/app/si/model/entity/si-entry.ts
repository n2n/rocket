
import { SiField } from 'src/app/si/model/entity/si-field';
import { SiEntryInput } from 'src/app/si/model/input/si-entry-input';
import { IllegalSiStateError } from 'src/app/si/model/illegal-si-state-error';
import { SiTypeBuildup } from 'src/app/si/model/entity/si-entry-buildup';
import { SiEntryError } from 'src/app/si/model/input/si-entry-error';
import { SiQualifier, SiIdentifier } from 'src/app/si/model/entity/si-qualifier';
import { SiType } from './si-type';
import { SiZoneError } from '../structure/si-zone-error';

export class SiEntry {
	public treeLevel: number|null = null;
	private _selectedTypeId: string|null = null;
	public bulky = false;
	public readOnly = true;
	private _typeBuildupsMap = new Map<string, SiTypeBuildup>();

	constructor(public identifier: SiIdentifier) {
	}

	private ensureBuildups() {
		if (this._selectedTypeId) {
			return;
		}

		throw new IllegalSiStateError('No buildup selected for entry: ' + this.toString());
	}

	get qualifier(): SiQualifier {
		return this.selectedTypeBuildup.createQualifier(this.identifier);
	}

	get selectedTypeBuildup(): SiTypeBuildup {
		return this._typeBuildupsMap.get(this.selectedTypeId) as SiTypeBuildup;
	}

	get selectedTypeId(): string {
		this.ensureBuildups();

		return this._selectedTypeId;
	}

	set selectedTypeId(id: string) {
		if (!this._typeBuildupsMap.has(id)) {
			throw new IllegalSiStateError('Buildup id does not exist on entry: ' + id);
		}

		this._selectedTypeId = id;
	}

	get types(): SiType[] {
		return Array.from(this._typeBuildupsMap.values()).map(buildup => buildup.type);
	}

	get typeQualifiers(): SiQualifier[] {
		const qualifiers: SiQualifier[] = [];
		for (const buildup of this._typeBuildupsMap.values()) {
			qualifiers.push(buildup.createQualifier(this.identifier));
		}
		return qualifiers;
	}

	putTypeBuildup(buildup: SiTypeBuildup) {
		this._typeBuildupsMap.set(buildup.type.typeId, buildup);
		if (!this._selectedTypeId) {
			this._selectedTypeId = buildup.type.typeId;
		}
	}

// 	getFieldById(id: string): SiField|null {
// 		return this.selectedTypeBuildup.getFieldById(id);
// 	}

	readInput(): SiEntryInput {
		const fieldInputMap = new Map<string, object>();

		for (const [id, field] of this.selectedTypeBuildup.fieldMap) {
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
			if (!this.selectedTypeBuildup.fieldMap.has(fieldId)) {
				this.selectedTypeBuildup.messages.push(...fieldError.getAllMessages());
				continue;
			}

			const field = this.selectedTypeBuildup.fieldMap.get(fieldId) as SiField;
			field.handleError(fieldError);
		}
	}

	resetError() {
		for (const [, buildup] of this._typeBuildupsMap) {
			buildup.messages = [];

			for (const [, field] of this.selectedTypeBuildup.fieldMap) {
				field.resetError();
			}
		}
	}

	getZoneErrors(): SiZoneError[] {
		const zoneErrors: SiZoneError[] = [];

		for (const [, siField] of this.selectedTypeBuildup.fieldMap) {
			const siContent = siField.getContent();
			if (siContent) {
				zoneErrors.push(...siContent.getZoneErrors());
			}
		}

		return zoneErrors;
	}

	toString() {
		return this.qualifier.category + '#' + this.qualifier.id;
	}

	copy(): SiEntry {
		const entry = new SiEntry(this.identifier);
		entry.treeLevel = this.treeLevel;

		for (const buildup of this._typeBuildupsMap.values()) {
			entry.putTypeBuildup(buildup.copy());
		}

		entry.selectedTypeId = this.selectedTypeId;
		return entry;
	}
}