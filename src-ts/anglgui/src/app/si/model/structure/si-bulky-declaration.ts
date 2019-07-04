import { SiFieldStructureDeclaration } from "src/app/si/model/structure/si-field-structure-declaration";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiControl } from "src/app/si/model/control/si-control";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";

export class SiBulkyDeclaration {
	constructor(public fieldStructureDeclarationMap: Map<string, SiFieldStructureDeclaration[]>) {
	}
	
	getFieldStructureDeclarationsByBuildupId(buildupId: string): SiFieldStructureDeclaration[] {
		if (this.fieldStructureDeclarationMap.has(buildupId)) {
			return <SiFieldStructureDeclaration[]> this.fieldStructureDeclarationMap.get(buildupId);
		}
		
		throw new IllegalSiStateError('Unkown buildupId: ' + buildupId);
	}
}
