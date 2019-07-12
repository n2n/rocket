import { SiFieldStructureDeclaration } from "src/app/si/model/structure/si-field-structure-declaration";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiControl } from "src/app/si/model/control/si-control";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";
import { Clas1 } from "src/app/si/model/structure/clas1";
import { Clas2 } from "src/app/si/model/structure/clas2";

export class SiBulkyDeclaration {
	constructor(public fieldStructureDeclarationMap: Map<string, SiFieldStructureDeclaration[]>) {

		const clas2 = new Clas2();
		clas2.create();
	}
	
	getFieldStructureDeclarationsByBuildupId(buildupId: string): SiFieldStructureDeclaration[] {
		if (this.fieldStructureDeclarationMap.has(buildupId)) {
			return <SiFieldStructureDeclaration[]> this.fieldStructureDeclarationMap.get(buildupId);
		}
		
		throw new IllegalSiStateError('Unkown buildupId: ' + buildupId);
	}
}
