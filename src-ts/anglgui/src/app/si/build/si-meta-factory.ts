import { SiDeclaration } from '../model/meta/si-declaration';
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiTypeDeclaration } from '../model/meta/si-type-declaration';
import { SiType } from '../model/meta/si-type';
import { SiProp } from '../model/meta/si-prop';
import { SiTypeQualifier, SiTypeIdentifier } from '../model/meta/si-type-qualifier';
import { SiTypeEssentialsFactory } from './si-type-essentials-factory';
import { SiStructureDeclaration } from '../model/meta/si-structure-declaration';


export class SiMetaFactory {
	static createDeclaration(data: any): SiDeclaration {
		const extr = new Extractor(data);

		const declaration = new SiDeclaration();

		let contextTypeDeclaration: SiTypeDeclaration|null = null ;
		for (const typeDeclarationData of extr.reqArray('typeDeclarations')) {
			const typeDeclaration = SiMetaFactory.createTypeDeclaration(typeDeclarationData, contextTypeDeclaration);
			if (!contextTypeDeclaration) {
				contextTypeDeclaration = typeDeclaration;
			}

			declaration.addTypeDeclaration(typeDeclaration);
		}
		return declaration;
	}

	private static createTypeDeclaration(data: any, contextTypeDeclaration: SiTypeDeclaration|null): SiTypeDeclaration {
		const extr = new Extractor(data);

		let contextSiProps: SiProp[]|null = null;
		let structureDeclarationsData: SiStructureDeclaration[]|null;

		if (contextTypeDeclaration) {
			contextSiProps = contextTypeDeclaration.type.getProps();
			structureDeclarationsData = extr.nullaArray('structureDeclarations');
		} else {
			structureDeclarationsData = extr.reqArray('structureDeclarations');
		}

		const type = SiMetaFactory.createType(extr.reqObject('type'), contextSiProps);

		if (structureDeclarationsData) {
			return new SiTypeDeclaration(type,
					new SiTypeEssentialsFactory(type).createStructureDeclarations(structureDeclarationsData));
		}

		return new SiTypeDeclaration(type, contextTypeDeclaration.structureDeclarations);
	}

	static createType(data: any, siProps: SiProp[]|null): SiType {
		const extr = new Extractor(data);

		const type = new SiType(SiMetaFactory.createTypeQualifier(extr.reqObject('qualifier')));

		let propDatas: Array<any>|null;
		if (!siProps) {
			propDatas = extr.reqArray('props');
		} else {
			propDatas = extr.nullaArray('props');
		}

		if (propDatas) {
			for (const propData of propDatas) {
				type.addProp(this.createProp(propData));
			}

			return type;
		}

		for (const siProp of siProps) {
			type.addProp(siProp);
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

	static createTypeQualifier(data: any): SiTypeQualifier {
		const extr = new Extractor(data);

		return new SiTypeQualifier(extr.reqString('category'), extr.reqString('id'), extr.reqString('name'),
				extr.reqString('iconClass'));
	}

	static createTypeQualifiers(dataArr: any[]): SiTypeQualifier[] {
		const typeQualifiers = new Array<SiTypeQualifier>();
		for (const data of dataArr) {
			typeQualifiers.push(SiMetaFactory.createTypeQualifier(data));
		}
		return typeQualifiers;
	}

	static createProp(probData: any): SiProp {
		const extr = new Extractor(probData);

		return new SiProp(extr.nullaString('id'), extr.nullaString('label'), extr.nullaString('helpText'),
				extr.reqStringArray('descendantPropIds'));
	}
}
