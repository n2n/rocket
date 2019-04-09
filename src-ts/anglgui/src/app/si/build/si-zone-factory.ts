
import { ObjectMissmatchError, Extractor } from "src/app/util/mapping/extractor";
import { DlSiZone } from "src/app/si/model/structure/impl/dl-si-zone";
import { ListSiZone, SiPage } from "src/app/si/model/structure/impl/list-si-zone";
import { SiZone } from "src/app/si/model/structure/si-zone";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiField } from "src/app/si/model/content/si-field";
import { SiFieldDeclaration } from "src/app/si/model/structure/si-field-declaration";
import { SiCompactDeclaration } from "src/app/si/model/structure/si-compact-declaration";
import { StringOutSiField } from "src/app/si/model/content/impl/string-out-si-field";

export class SiZoneFactory {
	
	static create(data: any): SiZone {
		const extr = new Extractor(data);
		
		switch (extr.reqString('type')) {
			case SiZoneType.LIST:
				const dataExtr = extr.reqExtractor('data');
				
				const listSiZone = new ListSiZone(extr.reqString('apiUrl'), dataExtr.reqNumber('pageSize'));
				
				const compactDeclaration = SiZoneFactory.createCompactDeclaration(dataExtr.reqObject('compactDeclaration'))
				listSiZone.setup(compactDeclaration.siFieldDeclarations, compactDeclaration.count);
				if (compactDeclaration.siEntries) {
					listSiZone.putPage(new SiPage(1, compactDeclaration.siEntries));
				}
				
				return listSiZone;
			case SiZoneType.DL:
				return new DlSiZone();
			default:
				throw new ObjectMissmatchError('Invalid si zone type: ' + data.type);
		}
	}
	
	static createCompactDeclaration(data: any): SiCompactDeclaration {
		const compactExtr = new Extractor(data);
		
		return new SiCompactDeclaration(
				SiZoneFactory.createFieldDeclarations(compactExtr.reqArray('fieldDeclarations')),
				compactExtr.reqNumber('count'),
				SiZoneFactory.createEntries(compactExtr.nullaArray('entries')));
	}
	
	private static createFieldDeclarations(data: Array<any>): SiFieldDeclaration[] {
		const declarations: Array<SiFieldDeclaration> = [];
		for (const declarationData of data) {
			const extr = new Extractor(declarationData);
			
			declarations.push(new SiFieldDeclaration(extr.reqString('fieldId'), 
					extr.nullaString('label'), extr.nullaString('helpText')));
			
		}
		
		return declarations;
	}
	
	private static createEntries(data: Array<any>|null): SiEntry[]|null {
		if (data === null) {
			return null;
		}
		
		let entries: Array<SiEntry> = [];
		for (let entryData of data) {
			const extr = new Extractor(entryData);
			
			const siEntry = new SiEntry(<string> extr.reqString('category'), extr.nullaString('id'), 
						<string> extr.reqString('name'));
			siEntry.treeLevel = extr.nullaNumber('treeLevel');
			siEntry.fieldMap = SiZoneFactory.createFieldMap(extr.reqMap('siFields'));
			
			entries.push(siEntry);
		}
		
		return entries;
	}
	
	private static createFieldMap(data: Map<string, any>): Map<string, SiField> {
		const fields = new Map<string, SiField>();
		
		for (let[fieldId, fieldData] of data) {
			fields.set(fieldId, SiZoneFactory.createField(fieldId, fieldData));
		}
		return fields;
	}
	
	private static createField(fieldId: string, data: any): SiField {
		const extr = new Extractor(data);
		const dataExtr = extr.reqExtractor('data');
		
		switch (extr.reqString('type')) {
		case SiFieldType.STRING_OUT:
			return new StringOutSiField(dataExtr.nullaString('value'));
		default: 
			throw new ObjectMissmatchError('Invalid si field type: ' + data.type);
		}	
	}
}

export enum SiZoneType {
    LIST = 'list',
    DL = 'dl'
} 


export enum SiFieldType {
	STRING_OUT = 'string-out'
}
