
import { SiField } from "src/app/si/model/content/si-field";
import { SiControl } from "src/app/si/model/control/si-control";

export class SiEntry {
	public treeLevel: number|null = null;
	public fieldMap = new Map<string, SiField>();
	public controlMap = new Map<string, SiControl>();
	
	constructor(public category: string, public id: string|null, public name: string) {	
	}
	
	getFieldById(id: string): SiField|null {
		return this.fieldMap.get(id) || null;
	}
	
}
