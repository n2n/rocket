import { SiDeclaration } from '../model/meta/si-declaration';
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiMaskDeclaration } from '../model/meta/si-mask-declaration';
import { SiMask } from '../model/meta/si-type';
import { SiProp } from '../model/meta/si-prop';
import { SiMaskQualifier, SiMaskIdentifier } from '../model/meta/si-mask-qualifier';
import { SiTypeEssentialsFactory as SiMaskEssentialsFactory } from './si-type-essentials-factory';
import { SiStructureDeclaration } from '../model/meta/si-structure-declaration';
import { SiFrame } from '../model/meta/si-frame';
import { SiTypeContext } from '../model/meta/si-type-context';


export class SiMetaFactory {
	static createDeclaration(data: any): SiDeclaration {
		const extr = new Extractor(data);

		const declaration = new SiDeclaration();

		let contextTypeDeclaration: SiMaskDeclaration|null = null ;
		for (const maskDeclarationData of extr.reqArray('maskDeclarations')) {
			const maskDeclaration = SiMetaFactory.createTypeDeclaration(maskDeclarationData, contextTypeDeclaration);
			if (!contextTypeDeclaration) {
				contextTypeDeclaration = maskDeclaration;
			}

			declaration.addTypeDeclaration(maskDeclaration);
		}
		return declaration;
	}

	private static createTypeDeclaration(data: any, contextTypeDeclaration: SiMaskDeclaration|null): SiMaskDeclaration {
		const extr = new Extractor(data);

		let contextSiProps: SiProp[]|null = null;
		let contextStructureDeclarations: SiStructureDeclaration[]|null = null;
		if (contextTypeDeclaration) {
			contextSiProps = contextTypeDeclaration.type.getProps();
			contextStructureDeclarations = contextTypeDeclaration.structureDeclarations;
		}

		const mask = SiMetaFactory.createMask(extr.reqObject('mask'), contextSiProps);
		const structureDeclarationsData = extr.nullaArray('structureDeclarations');
		if (structureDeclarationsData) {
			return new SiMaskDeclaration(mask,
					new SiMaskEssentialsFactory(mask).createStructureDeclarations(structureDeclarationsData));
		}

		return new SiMaskDeclaration(mask, contextStructureDeclarations);
	}

	static createMask(data: any, siProps: SiProp[]|null): SiMask {
		const extr = new Extractor(data);

		const type = new SiMask(SiMetaFactory.createTypeQualifier(extr.reqObject('qualifier')));

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

	static createTypeQualifier(data: any): SiMaskQualifier {
		const extr = new Extractor(data);

		return new SiMaskQualifier(new SiMaskIdentifier(extr.reqString('id'), extr.reqString('typeId')),
				 extr.reqString('name'), extr.reqString('iconClass'));
	}

	static createTypeQualifiers(dataArr: any[]): SiMaskQualifier[] {
		const maskQualifiers = new Array<SiMaskQualifier>();
		for (const data of dataArr) {
			maskQualifiers.push(SiMetaFactory.createTypeQualifier(data));
		}
		return maskQualifiers;
	}

	static createProp(probData: any): SiProp {
		const extr = new Extractor(probData);

		return new SiProp(extr.nullaString('id'), extr.nullaString('label'), extr.nullaString('helpText'),
				extr.reqStringArray('descendantPropIds'));
	}

	static createFrame(data: any): SiFrame {
		const extr = new Extractor(data);

		return new SiFrame(extr.reqString('apiUrl'), this.createTypeContext(extr.reqObject('typeContext')));
	}

	static createTypeContext(data: any): SiTypeContext {
		const extr = new Extractor(data);

		return new SiTypeContext(extr.reqString('contextTypeId'), extr.reqStringArray('subTypeIds'));
	}


}