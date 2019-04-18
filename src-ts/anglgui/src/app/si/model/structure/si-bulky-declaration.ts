import { SiFieldStructureDeclaration } from "src/app/si/model/structure/si-field-structure-declaration";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiControl } from "src/app/si/model/control/si-control";

export class SiBulkyDeclaration {
	constructor(public fieldStructureDeclaration: SiFieldStructureDeclaration[],
			public controlMap: Map<string, SiControl>) {
		
	}
}