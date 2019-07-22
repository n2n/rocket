
import { SiField } from "src/app/si/model/content/si-field";
import { SiControl } from "src/app/si/model/control/si-control";
import { SiEntryInput } from "src/app/si/model/input/si-entry-input";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";

export class SiEntryBuildup {
	public messages: string[] = [];
	
	constructor(public name: string, public iconClass: string, public idName: string|null,
			public fieldMap: Map<string, SiField> = new Map<string, SiField>(), 
			public controlMap: Map<string, SiControl> = new Map<string, SiControl>()) {	
	}
	
	getBestName(): string {
		if (this.idName) {
			return this.idName;
		}
		
		return this.name;
	}
	
	getFieldById(id: string): SiField|null {
		return this.fieldMap.get(id) || null;
	}
}
