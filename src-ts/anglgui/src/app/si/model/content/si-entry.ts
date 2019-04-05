
import { SiField } from "src/app/si/model/content/si-field";

export class SiEntry {
	public treeLevel: number|null = null;
	public siFields: SiField[] = [];
	
	constructor(public category: string, public id: string|null, public name: string) {	
	}
	
	getSiFieldById(id: string): SiField|null {
		return null;
	}
}
