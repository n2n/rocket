
import { Extractor } from "src/app/util/mapping/extractor";
import { SiEntryError } from "src/app/si/model/input/si-entry-error";
import { SiFieldError } from "src/app/si/model/input/si-field-error";
import { SiResult } from "src/app/si/model/control/si-result";
import { SiCompactDeclaration } from "src/app/si/model/structure/si-compact-declaration";
import { SiBulkyDeclaration } from "src/app/si/model/structure/si-bulky-declaration";
import { SiFieldStructureDeclaration } from "src/app/si/model/structure/si-field-structure-declaration";
import { SiFieldDeclaration } from "src/app/si/model/structure/si-field-declaration";

export class SiResultFactory {
	
	static createResult(data: any): SiResult {
		const extr = new Extractor(data);
		
		const result = new SiResult();
		
		result.directive = extr.nullaString('directive');
		result.ref = extr.nullaString('ref');
		result.href = extr.nullaString('href');
		
		const inputErrorData = extr.nullaObject('inputError');
		
		if (inputErrorData) {
			for (let [key, data] of new Extractor(inputErrorData).reqMap('entryErrors')) {
				result.entryErrors.set(key, SiResultFactory.createEntryError(data));
			}
		}
		
		return result;
	}
	
	static createEntryError(data: any): SiEntryError {
		const extr = new Extractor(data);
		
		const entryError = new SiEntryError(/*extr.reqStringArray('messages')*/);
		
		for (let [key, fieldData] of extr.reqMap('fieldErrors')) {
			entryError.fieldErrors.set(key, SiResultFactory.createFieldError(fieldData));
		}
		
		return entryError;
	}
	
	private static createFieldError(data: any): SiFieldError {
		const extr = new Extractor(data);
		
		const fieldError = new SiFieldError(extr.reqStringArray('messages'));
		
		for (let [key, entryData] of extr.reqMap('subEntryErrors')) {
			fieldError.subEntryErrors.set(key, SiResultFactory.createEntryError(entryData));
		}
		
		return fieldError;
	}
	
	static createCompactDeclaration(data: any): SiCompactDeclaration {
		const extr = new Extractor(data);
		
		const declarationMap = new Map<string, SiFieldDeclaration[]>();
		for (let [buildupId, declarationData] of extr.reqArrayMap('fieldDeclarations')) {
			declarationMap.set(buildupId, SiResultFactory.createFieldDeclarations(declarationData));
		}
		
		return new SiCompactDeclaration(declarationMap);
	}
	
	static createBulkyDeclaration(data: any): SiBulkyDeclaration {
		const extr = new Extractor(data);
		
		const declarationMap = new Map<string, SiFieldStructureDeclaration[]>();
		for (let [buildupId, declarationData] of extr.reqArrayMap('fieldStructureDeclarations')) {
			declarationMap.set(buildupId, SiResultFactory.createFieldStructureDeclarations(declarationData));
		}
		
		return new SiBulkyDeclaration(declarationMap);
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
				<any> extr.reqString('structureType'), 
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
