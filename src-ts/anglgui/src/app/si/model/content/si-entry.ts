
import { SiField } from "src/app/si/model/content/si-field";
import { SiControl } from "src/app/si/model/control/si-control";
import { SiEntryInput } from "src/app/si/model/input/si-entry-input";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";
import { SiEntryBuildup } from "src/app/si/model/content/si-entry-buildup";
import { SiEntryError } from "src/app/si/model/input/si-entry-error";
import { SiQualifier, SiIdentifier } from "src/app/si/model/content/si-qualifier";

export class SiEntry {
	public treeLevel: number|null = null;
	private _selectedBuildupId: string;
	public inputAvailable: boolean = false;
	private _buildups = new Map<string, SiEntryBuildup>();
	
	constructor(public identifier: SiIdentifier) {	
	}

	private ensureBuildups() {
		if (this._selectedBuildupId) return;
		
		throw new IllegalSiStateError('No buildup available for entry: ' + this.toString());
	}
	
	get qualifier(): SiQualifier {
		const buildup = this.selectedBuildup;
		return new SiQualifier(this.identifier.category, this.identifier.id, buildup.name, buildup.iconClass, 
				buildup.idName);
	}
	
	get selectedBuildup(): SiEntryBuildup {
		return <SiEntryBuildup> this._buildups.get(this.selectedBuildupId);
	}
	
	get selectedBuildupId(): string {
		this.ensureBuildups();
		
		return this._selectedBuildupId;
	}
	
	set selectedBuildupId(id: string) {
		if (!this._buildups.has(id)) {
			throw new IllegalSiStateError('Buildup id does not exist on entry: ' + id);
		}
		
		this._selectedBuildupId = id;
	}
	
	putBuildup(id: string, buildup: SiEntryBuildup) {
		this._buildups.set(id, buildup);
		if (!this._selectedBuildupId) {
			this._selectedBuildupId = id;
		}
	}
	
	
//	getFieldById(id: string): SiField|null {
//		return this.selectedBuildup.getFieldById(id);
//	}
	
	readInput(): SiEntryInput {
		const fieldInputMap = new Map<string, object>();
		
		for (let [id, field] of this.selectedBuildup.fieldMap) {
			if (!field.hasInput()) {
				continue;
			}
			
			fieldInputMap.set(id, field.readInput());
		}
		
		if (fieldInputMap.size == 0) {
			throw new IllegalSiStateError('No input available.');
		}
		
		return new SiEntryInput(this.qualifier, this._selectedBuildupId, fieldInputMap);
	}
	
	handleError(error: SiEntryError) {
		for (let [fieldId, fieldError] of error.fieldErrors) {
			if (!this.selectedBuildup.fieldMap.has(fieldId)) {
				this.selectedBuildup.messages.push(...fieldError.getAllMessages());
				continue;
			}
			
			const field = <SiField> this.selectedBuildup.fieldMap.get(fieldId);
			field.handleError(fieldError);
		}
	}
	
	resetError() {
		for (const [buildupId, buildup] of this._buildups) {
			buildup.messages = [];
			
			for (const [fieldId, field] of this.selectedBuildup.fieldMap) {
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
		
		for (const buildup of this._buildups) {
			entry.putBuildup(buildup.id, buildup.copy());		
		}
		
		entry.selectedBuildupId = this.selectedBuildupId;
		return entry;
	}
}
