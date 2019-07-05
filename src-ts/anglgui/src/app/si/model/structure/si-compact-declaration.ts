
import { SiFieldDeclaration } from "src/app/si/model/structure/si-field-declaration";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiFieldStructureDeclaration } from "src/app/si/model/structure/si-field-structure-declaration";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";

export class SiCompactDeclaration {
	
	constructor(public fieldDeclarationMap: Map<string, SiFieldDeclaration[]>) {
	}
	
	getBasicFieldDeclarations(): SiFieldDeclaration[] {
		const value = this.fieldDeclarationMap.values().next();
		if (value) {
			return value.value;
		}
		
		throw new IllegalSiStateError('SiCompactDeclaration contains no SiFieldDeclaration.');
	}
	
	getFieldDeclarationsByBuildupId(buildupId: string): SiFieldDeclaration[] {
		if (this.fieldDeclarationMap.has(buildupId)) {
			return <SiFieldDeclaration[]> this.fieldDeclarationMap.get(buildupId);
		}
		
		throw new IllegalSiStateError('Unkown buildupId: ' + buildupId);
	}
}