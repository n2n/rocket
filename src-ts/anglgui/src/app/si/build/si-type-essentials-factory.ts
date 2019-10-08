import { SiType } from '../model/meta/si-type';
import { UiStructureDeclaration } from '../model/meta/si-structure-declaration';
import { Extractor } from 'src/app/util/mapping/extractor';


export class SiTypeEssentialsFactory {

	constructor(private type: SiType) {
	}

	createStructureDeclarations(data: Array<any>): UiStructureDeclaration[] {
		const declarations: Array<UiStructureDeclaration> = [];
		for (const declarationData of data) {
			declarations.push(this.createStructureDeclaration(declarationData));
		}
		return declarations;
	}

	createStructureDeclaration(data: any): UiStructureDeclaration {
		const extr = new Extractor(data);

		const propId = extr.nullaString('propId');
		const type = extr.reqString('structureType') as any;
		const children = this.createStructureDeclarations(extr.reqArray('children'));

		if (propId !== null) {
			return new UiStructureDeclaration(this.type.getPropById(propId), null,
					type, children);
		}

		return new UiStructureDeclaration(null, extr.nullaString('label'), type, children);
	}
}
