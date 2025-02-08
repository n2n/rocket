import { SiDeclaration } from '../model/meta/si-declaration';
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiMask } from '../model/meta/si-mask';
import { SiProp } from '../model/meta/si-prop';
import { SiMaskIdentifier, SiMaskQualifier } from '../model/meta/si-mask-qualifier';
import { SiTypeEssentialsFactory as SiMaskEssentialsFactory } from './si-type-essentials-factory';
import { SiStructureDeclaration } from '../model/meta/si-structure-declaration';
import { SiFrame } from '../model/meta/si-frame';
import { SiEntryIdentifier, SiEntryQualifier } from '../model/content/si-entry-qualifier';
import { SiControlFactory } from './si-control-factory';
import { SiObjectQualifier } from '../model/content/si-object-qualifier';


export class SiMetaFactory {
	static createDeclaration(data: any, controlFactory: SiControlFactory): SiDeclaration {
		const extr = new Extractor(data);

		const declaration = new SiDeclaration(/*SiMetaFactory.createStyle(extr.reqObject('style'))*/);

		let contextMask: SiMask|null = null ;
		for (const masksData of extr.reqArray('masks')) {
			const mask = SiMetaFactory.createMask(masksData, contextMask, controlFactory);
			if (!contextMask) {
				contextMask = mask;
			}

			declaration.addMask(mask);
		}
		return declaration;
	}

	// static createStyle(data: any): SiStyle {
	// 	const extr = new Extractor(data);
	//
	// 	return {
	// 		bulky: extr.reqBoolean('bulky'),
	// 		readOnly: extr.reqBoolean('readOnly')
	// 	};
	// }

	private static createMask(data: any, contextMask: SiMask|null, controlFactory: SiControlFactory): SiMask {
		const extr = new Extractor(data);

		let contextSiProps: SiProp[]|null = null;
		let contextStructureDeclarations: SiStructureDeclaration[]|null = null;
		if (contextMask) {
			contextSiProps = contextMask.getProps();
			contextStructureDeclarations = contextMask.structureDeclarations;
		}

		const mask = new SiMask(SiMetaFactory.createTypeQualifier(extr.reqObject('qualifier')), null);

		let propDatas: Map<string, any>|null;
		if (!contextSiProps) {
			propDatas = extr.reqMap('props');
		} else {
			propDatas = extr.nullaMap('props');
		}

		if (propDatas) {
			for (const [name, propData] of propDatas) {
				mask.addProp(this.createProp(name, propData));
			}
		} else {
			for (const siProp of contextSiProps!) {
				mask.addProp(siProp);
			}
		}

		mask.controls = controlFactory.createControls(mask.qualifier.maskIdentifier.id, null, extr.reqMap('controls'));

		const structureDeclarationsData = extr.nullaArray('structureDeclarations');
		if (structureDeclarationsData) {
			mask.structureDeclarations = new SiMaskEssentialsFactory(mask).createStructureDeclarations(structureDeclarationsData);
		} else {
			mask.structureDeclarations = contextStructureDeclarations;
		}

		return mask;
	}

	// static createMask(data: any, siProps: SiProp[]|null): SiMask {
	// 	const extr = new Extractor(data);
	//
	//
	//
	// 	return type;
	// }

	static createMaskIdentifier(data: any): SiMaskIdentifier {
		const extr = new Extractor(data);

		return new SiMaskIdentifier(extr.reqString('id'), extr.reqString('typeId'), extr.reqString('superTypeId'));
	}

	static createTypeQualifier(data: any): SiMaskQualifier {
		const extr = new Extractor(data);

		const identifierExtr = extr.reqExtractor('identifier');
		return new SiMaskQualifier(
				new SiMaskIdentifier(identifierExtr.reqString('id'),
						identifierExtr.reqString('typeId'), identifierExtr.reqString('typeId')),
				extr.reqString('name'), extr.reqString('iconClass'));
	}

	static createTypeQualifiers(dataArr: any[]): SiMaskQualifier[] {
		const maskQualifiers = new Array<SiMaskQualifier>();
		for (const data of dataArr) {
			maskQualifiers.push(SiMetaFactory.createTypeQualifier(data));
		}
		return maskQualifiers;
	}

	static createProp(name: string, probData: any): SiProp {
		const extr = new Extractor(probData);

		return new SiProp(name, extr.reqString('label'), extr.nullaString('helpText'),
				extr.reqStringArray('descendantPropIds'));
	}

	static createFrame(data: any): SiFrame { 
		const extr = new Extractor(data);

		const siFrame = new SiFrame(extr.reqString('apiUrl')/*, this.createTypeContext(extr.reqObject('typeContext'))*/);
		siFrame.sortable = extr.reqBoolean('sortable');
		siFrame.treeMode = extr.reqBoolean('treeMode');
		return siFrame;
	}

	static buildFrame(data: any): SiFrame|null {
		if (data === null) {
			return null;
		}

		return SiMetaFactory.createFrame(data);
	}

	// static createTypeContext(data: any): SiTypeContext {
	// 	const extr = new Extractor(data);
	//
	// 	return new SiTypeContext(extr.reqString('typeId'), extr.reqStringArray('entryIds'),
	// 			extr.reqBoolean('treeMode'));
	// }

	static createEntryIdentifier(data: any): SiEntryIdentifier {
		const extr = new Extractor(data);

		return new SiEntryIdentifier(SiMetaFactory.createMaskIdentifier(extr.reqObject('maskIdentifier')),
				extr.nullaString('id'));
	}

	static buildEntryQualifiers(dataArr: any[]|null): SiEntryQualifier[] {
		if (dataArr === null) {
			return [];
		}

		const entryQualifiers: SiEntryQualifier[] = [];
		for (const data of dataArr) {
			entryQualifiers.push(SiMetaFactory.createEntryQualifier(data));
		}
		return entryQualifiers;
	}

	static createEntryQualifier(data: any): SiEntryQualifier {
		const extr = new Extractor(data);

		return new SiEntryQualifier(SiMetaFactory.createEntryIdentifier(extr.reqObject('identifier')),
				extr.nullaString('idName'));
	}

	static buildObjectQualifiers(dataArr: any[]|null): SiObjectQualifier[] {
		if (dataArr === null) {
			return [];
		}

		const objectQualifiers: SiObjectQualifier[] = [];
		for (const data of dataArr) {
			objectQualifiers.push(SiMetaFactory.createObjectQualifier(data));
		}
		return objectQualifiers;
	}

	static createObjectQualifier(data: any): SiObjectQualifier {
		const extr = new Extractor(data);

		return new SiObjectQualifier(extr.reqString('superTypeId'), extr.reqString('id'),
				extr.reqString('idName'));
	}
}
