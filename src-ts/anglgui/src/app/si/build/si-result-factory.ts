
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiEntryError } from 'src/app/si/model/input/si-entry-error';
import { SiFieldError } from 'src/app/si/model/input/si-field-error';
import { SiResult } from 'src/app/si/model/control/si-result';
import { SiEntryDeclaration } from 'src/app/si/model/structure/si-entry-declaration';
import { SiFieldStructureDeclaration } from 'src/app/si/model/structure/si-field-structure-declaration';
import { SiFieldDeclaration } from 'src/app/si/model/structure/si-field-declaration';

export class SiResultFactory {

	static createResult(data: any): SiResult {
		const extr = new Extractor(data);

		const result = new SiResult();

		result.directive = extr.nullaString('directive');
		result.ref = extr.nullaString('ref');
		result.href = extr.nullaString('href');

		const inputErrorData = extr.nullaObject('inputError');

		if (inputErrorData) {
			for (const [ieKey, ieData] of new Extractor(inputErrorData).reqMap('entryErrors')) {
				result.entryErrors.set(ieKey, SiResultFactory.createEntryError(ieData));
			}
		}

		return result;
	}

	static createEntryError(data: any): SiEntryError {
		const extr = new Extractor(data);

		const entryError = new SiEntryError(/*extr.reqStringArray('messages')*/);

		for (const [key, fieldData] of extr.reqMap('fieldErrors')) {
			entryError.fieldErrors.set(key, SiResultFactory.createFieldError(fieldData));
		}

		return entryError;
	}

	private static createFieldError(data: any): SiFieldError {
		const extr = new Extractor(data);

		const fieldError = new SiFieldError(extr.reqStringArray('messages'));

		for (const [key, entryData] of extr.reqMap('subEntryErrors')) {
			fieldError.subEntryErrors.set(key, SiResultFactory.createEntryError(entryData));
		}

		return fieldError;
	}

	static createEntryDeclaration(data: any): SiEntryDeclaration {
		const extr = new Extractor(data);

		const fieldDeclarationMap = new Map<string, SiFieldDeclaration[]>();
		for (const [typeId, declarationData] of extr.reqArrayMap('fieldDeclarationsMap')) {
			fieldDeclarationMap.set(typeId, SiResultFactory.createFieldDeclarations(declarationData));
		}

		const fieldStructureDeclarationMap = new Map<string, SiFieldStructureDeclaration[]>();
		for (const [typeId, declarationData] of extr.reqArrayMap('fieldStructureDeclarationsMap')) {
			fieldStructureDeclarationMap.set(typeId, SiResultFactory.createFieldStructureDeclarations(declarationData));
		}

		return new SiEntryDeclaration(fieldDeclarationMap, fieldStructureDeclarationMap);
	}

	private static createFieldStructureDeclarations(data: Array<any>): SiFieldStructureDeclaration[] {
		const declarations: Array<SiFieldStructureDeclaration> = [];
		for (const declarationData of data) {
			declarations.push(SiResultFactory.createFieldStructureDeclaration(declarationData));
		}
		return declarations;
	}

	private static createFieldStructureDeclaration(data: any): SiFieldStructureDeclaration {
		const extr = new Extractor(data);

		return new SiFieldStructureDeclaration(
				SiResultFactory.createFieldDeclaration(extr.reqObject('fieldDeclaration')),
				extr.reqString('structureType') as any,
				SiResultFactory.createFieldStructureDeclarations(extr.reqArray('children')));
	}

	private static createFieldDeclarations(data: Array<any>): SiFieldDeclaration[] {
		const declarations: Array<SiFieldDeclaration> = [];
		for (const declarationData of data) {
			declarations.push(SiResultFactory.createFieldDeclaration(declarationData));
		}
		return declarations;
	}

	private static createFieldDeclaration(data: any): SiFieldDeclaration {
		const extr = new Extractor(data);

		return new SiFieldDeclaration(extr.nullaString('fieldId'),
				extr.nullaString('label'), extr.nullaString('helpText'));
	}
}
