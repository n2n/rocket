
import { SiField } from "src/app/si/model/content/si-field";
import { SiControl } from "src/app/si/model/control/si-control";
import { SiEntryInput, SiInputType } from "src/app/si/model/input/si-entry-input";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";

export class SiEntry {
	public treeLevel: number|null = null;
	public fieldMap = new Map<string, SiField>();
	public controlMap = new Map<string, SiControl>();
	
	constructor(public category: string, public id: string|null, public name: string) {	
	}
	
	getFieldById(id: string): SiField|null {
		return this.fieldMap.get(id) || null;
	}
	
	hasInput(): boolean {
		for (let [id, field] of this.fieldMap) {
			if (field.hasInput()) {
				return true;
			}
		}
		
		return false;
	}
	
	readInput(): SiEntryInput {
		const fieldInputMap = new Map<string, Map<string, SiInputType>>();
		
		for (let [id, field] of this.fieldMap) {
			if (!field.hasInput()) {
				continue;
			}
			
			fieldInputMap.set(id, field.readInput());
		}
		
		if (fieldInputMap.size == 0) {
			throw new IllegalSiStateError('No input available.');
		}
		
		return new SiEntryInput(this.id, fieldInputMap);
	}
	
}
