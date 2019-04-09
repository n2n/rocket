
import { SiField } from "src/app/si/model/content/si-field";

export class SiEntry {
	public treeLevel: number|null = null;
	public fieldMap = new Map<string, SiField>();
	
	constructor(public category: string, public id: string|null, public name: string) {	
	}
	
	public getFieldById(id: string): SiField|null {
		return this.fieldMap.get(id) || null;
	}
}
