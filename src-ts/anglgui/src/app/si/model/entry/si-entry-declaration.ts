
import { SiFieldDeclaration } from 'src/app/si/model/entity/si-field-declaration';
import { SiFieldStructureDeclaration } from 'src/app/si/model/entity/si-field-structure-declaration';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';

export class SiEntryDeclaration {

	constructor(public fieldDeclarationMap: Map<string, SiFieldDeclaration[]>,
			public fieldStructureDeclarationMap: Map<string, SiFieldStructureDeclaration[]>) {
	}

	getBasicFieldDeclarations(): SiFieldDeclaration[] {
		const value = this.fieldDeclarationMap.values().next();
		if (value) {
			return value.value;
		}

		throw new IllegalSiStateError('SiDeclaration contains no SiFieldDeclaration.');
	}

	getFieldDeclarationsByTypeId(typeId: string): SiFieldDeclaration[] {
		if (this.fieldDeclarationMap.has(typeId)) {
			return this.fieldDeclarationMap.get(typeId);
		}

		throw new IllegalSiStateError('Unkown typeId: ' + typeId);
	}

	getFieldStructureDeclarationsByTypeId(typeId: string): SiFieldStructureDeclaration[] {
		if (this.fieldStructureDeclarationMap.has(typeId)) {
			return this.fieldStructureDeclarationMap.get(typeId);
		}

		throw new IllegalSiStateError('Unkown typeId: ' + typeId);
	}
}
