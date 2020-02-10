
import { SiType } from './si-type';
import { SiStructureDeclaration } from './si-structure-declaration';
import { SiProp } from './si-prop';

export class SiTypeDeclaration {

	constructor(public type: SiType, public structureDeclarations: Array<SiStructureDeclaration>|null) {
	}

	getSiProps(): SiProp[] {
		// return this.type.getProps();
		return this.structureDeclarations.filter(sd => !!sd.prop).map(sd => sd.prop);
	}
}
