
import { SiField } from 'src/app/si/model/content/si-field';
import { SiControl } from 'src/app/si/model/control/si-control';
import { SiEntryInput } from 'src/app/si/model/input/si-entry-input';
import { IllegalSiStateError } from 'src/app/si/model/illegal-si-state-error';
import { SiTypeBuildup } from 'src/app/si/model/content/si-entry-buildup';
import { SiEntryError } from 'src/app/si/model/input/si-entry-error';
import { SiQualifier, SiIdentifier } from 'src/app/si/model/content/si-qualifier';

export class SiEntry {
	public treeLevel: number|null = null;
	private _selectedTypeId: string;
	public inputAvailable: boolean = false;
	private _buildupsMap = new Map<string, SiTypeBuildup>();

	constructor(public identifier: SiIdentifier) {
	}

	private ensureBuildups() {
		if (this._selectedTypeId) { 
			return; 
		}

		throw new IllegalSiStateError('No buildup available for entry: ' + this.toString());
	}

	get qualifier(): SiQualifier {
		return this.selectedTypeBuildup.createQualifier(this.identifier);
	}

	get selectedTypeBuildup(): SiTypeBuildup {
		return this._buildupsMap.get(this.selectedTypeId) as SiTypeBuildup;
	}

	get selectedTypeId(): string {
		this.ensureBuildups();

		return this._selectedTypeId;
	}

	set selectedTypeId(id: string) {
		if (!this._buildupsMap.has(id)) {
			throw new IllegalSiStateError('Buildup id does not exist on entry: ' + id);
		}

		this._selectedTypeId = id;
	}

	get typeQualifiers(): SiQualifier[] {
		const qualifiers: SiQualifier[] = [];
		for (const buildup of this._buildupsMap.values()) {
			qualifiers.push(buildup.createQualifier(this.identifier));
		}
		return qualifiers;
	}

	putTypeBuildup(buildup: SiTypeBuildup) {
		this._buildupsMap.set(buildup.typeId, buildup);
		if (!this._selectedTypeId) {
			this._selectedTypeId = buildup.typeId;
		}
	}

// 	getFieldById(id: string): SiField|null {
// 		return this.selectedBuildup.getFieldById(id);
// 	}

	readInput(): SiEntryInput {
		const fieldInputMap = new Map<string, object>();

		for (let [id, field] of this.selectedTypeBuildup.fieldMap) {
			if (!field.hasInput()) {
				continue;
			}

			fieldInputMap.set(id, field.readInput());
		}

		if (fieldInputMap.size === 0) {
			throw new IllegalSiStateError('No input available.');
		}

		return new SiEntryInput(this.qualifier, this._selectedTypeId, fieldInputMap);
	}

	handleError(error: SiEntryError) {
		for (let [fieldId, fieldError] of error.fieldErrors) {
			if (!this.selectedTypeBuildup.fieldMap.has(fieldId)) {
				this.selectedTypeBuildup.messages.push(...fieldError.getAllMessages());
				continue;
			}

			const field = this.selectedTypeBuildup.fieldMap.get(fieldId) as SiField;
			field.handleError(fieldError);
		}
	}

	resetError() {
		for (const [buildupId, buildup] of this._buildupsMap) {
			buildup.messages = [];

			for (const [fieldId, field] of this.selectedTypeBuildup.fieldMap) {
				field.resetError();
			}
		}
	}

	toString() {
		return this.qualifier.category + '#' + this.qualifier.id;
	}

	copy(): SiEntry {
		const entry = new SiEntry(this.identifier);
		entry.treeLevel = this.treeLevel;

		for (const buildup of this._buildupsMap.values()) {
			entry.putTypeBuildup(buildup.copy());
		}

		entry.selectedTypeId = this.selectedTypeId;
		return entry;
	}
}
