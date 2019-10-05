import { SiDeclaration } from '../model/meta/si-declaration';
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiTypeDeclaration } from '../model/meta/si-type-declaration';
import { SiType } from '../model/meta/si-type';
import { SiProp } from '../model/meta/si-prop';
import { SiTypeQualifier, SiTypeIdentifier } from '../model/meta/si-type-qualifier';
import { SiTypeEssentialsFactory } from './si-type-essentials-factory';


export class SiMetaFactory {
	static createDeclaration(data: any): SiDeclaration {
		const extr = new Extractor(data);

		const declaration = new SiDeclaration();

		for (const typeDeclarationData of extr.reqArray('typeDeclarations')) {
			declaration.addTypeDeclaration(SiMetaFactory.createTypeDeclaration(typeDeclarationData));
		}

		return declaration;
	}

	private static createTypeDeclaration(data: any): SiTypeDeclaration {
		const extr = new Extractor(data);

		const type = SiMetaFactory.createType(extr.reqObject('type'));
		return new SiTypeDeclaration(type,
				new SiTypeEssentialsFactory(type).createStructureDeclarations(extr.reqArray('structureDeclarations')));
	}

	static createType(data: any) {
		const extr = new Extractor(data);

		const type = new SiType(SiMetaFactory.createTypeQualifier(extr.reqObject('qualifier')));

		for (const propData of extr.reqArray('props')) {
			type.addProp(this.createProp(propData));
		}

		return type;
	}

	static createTypeIdentifier(data: any): SiTypeIdentifier {
		const extr = new Extractor(data);

		return  {
			category: extr.reqString('category'),
			id: extr.reqString('id')
		};
	}

	static createTypeQualifier(data: any) {
		const extr = new Extractor(data);

		return new SiTypeQualifier(extr.reqString('category'), extr.reqString('id'), extr.reqString('name'),
				extr.reqString('iconClass'));
	}

	static createProp(probData: any): SiProp {
		const extr = new Extractor(probData);

		return new SiProp(extr.nullaString('id'), extr.nullaString('label'), extr.nullaString('helpText'));
	}
}
