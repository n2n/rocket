
import { SiType } from './si-type';
import { UiStructureDeclaration } from './si-structure-declaration';
import { SiProp } from './si-prop';

export class SiTypeDeclaration {

	constructor(public type: SiType, public structureDeclarations: Array<UiStructureDeclaration>) {
	}

	getSiProps(): SiProp[] {
		return this.structureDeclarations.filter(sd => !!sd.prop).map(sd => sd.prop);
	}
}
