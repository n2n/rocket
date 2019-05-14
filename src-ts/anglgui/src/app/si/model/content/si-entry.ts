
import { SiField } from "src/app/si/model/content/si-field";
import { SiControl } from "src/app/si/model/control/si-control";
import { SiEntryInput, SiInputValue } from "src/app/si/model/input/si-entry-input";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";
import { SiEntryBuildup } from "src/app/si/model/content/si-entry-buildup";

export class SiEntry {
	public treeLevel: number|null = null;
	private _selectedBuildupId: string;
	public inputAvailable: boolean = false;
	private _buildups = new Map<string, SiEntryBuildup>();
	
	constructor(public category: string, public id: string|null) {	
	}

	private ensureBuildups() {
		if (this._selectedBuildupId) return;
		
		throw new IllegalSiStateError('No buildup available for entry: ' + this.toString());
	}
	
	get selectedBuildup(): SiEntryBuildup {
		return <SiEntryBuildup> this._buildups.get(this.selectedBuildupId);
	}
	
	get selectedBuildupId(): string {
		this.ensureBuildups();
		
		return this._selectedBuildupId
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
		const fieldInputMap = new Map<string, Map<string, SiInputValue>>();
		
		for (let [id, field] of this.selectedBuildup.fieldMap) {
			if (!field.hasInput()) {
				continue;
			}
			
			fieldInputMap.set(id, field.readInput());
		}
		
		if (fieldInputMap.size == 0) {
			throw new IllegalSiStateError('No input available.');
		}
		
		return new SiEntryInput(this.category, this.id, this._selectedBuildupId, fieldInputMap);
	}
	
	toString() {
		return this.category + '#' + this.id;
	}
	
}