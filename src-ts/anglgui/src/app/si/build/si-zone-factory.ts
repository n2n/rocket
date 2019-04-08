
import { ObjectMissmatchError, Extractor } from "src/app/util/mapping/extractor";
import { DlSiZone } from "src/app/si/model/structure/impl/dl-si-zone";
import { ListSiZone, SiPage } from "src/app/si/model/structure/impl/list-si-zone";
import { SiZone } from "src/app/si/model/structure/si-zone";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiField } from "src/app/si/model/content/si-field";
import { SiFieldDeclaration } from "src/app/si/model/structure/si-field-declaration";
import { SiCompactDeclaration } from "src/app/si/model/structure/si-compact-declaration";

export class SiZoneFactory {
	
	static create(data: any): SiZone {
		const extr = new Extractor(data);
		
		switch (extr.reqString('type')) {
			case SiZoneType.LIST:
				const dataExtr = extr.reqExtractor('data');
				
				const compactDeclaration = SiZoneFactory.createCompactDeclaration(dataExtr.reqObject('siCompactDeclaration'))

				const listSiZone = new ListSiZone(extr.reqString('apiUrl'), dataExtr.reqNumber('pageSize'));
				listSiZone.setup(compactDeclaration.siFieldDeclarations, compactDeclaration.count);
				if (compactDeclaration.siEntries) {
					listSiZone.putPage(new SiPage(1, compactDeclaration.siEntries));
				}
			case SiZoneType.DL:
				return new DlSiZone();
			default:
				throw new ObjectMissmatchError('Invalid si zone type: ' + data.type);
		}
	}
	
	static createCompactDeclaration(data: any): SiCompactDeclaration {
		const compactExtr = new Extractor(data);
		
		return new SiCompactDeclaration(
				SiZoneFactory.createDeclarations(compactExtr.reqArray('siFieldDeclarations')),
				compactExtr.reqNumber('count'),
				SiZoneFactory.createEntries(compactExtr.nullaArray('siEntries')));
	}
	
	private static createDeclarations(data: Array<any>): SiFieldDeclaration[] {
		const declarations: Array<SiFieldDeclaration> = [];
		for (const declarationData of data) {
			const extr = new Extractor(declarationData);
			
			declarations.push(new SiFieldDeclaration(extr.reqString('siFieldId'), 
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
//			siEntry.siFields = this.createFields(extr.reqArray('siFields'));
			entries.push(siEntry);
		}
		
		return entries;
	}
	
	private static createFields(data: Array<any>): SiField[] {
		const fields: Array<SiField> = [];
		for (let fieldData of data) {
			const extr = new Extractor(fieldData);
			
			
		}
		
		return fields;
	}
}

export enum SiZoneType {
    LIST = 'list',
    DL = 'dl'
} 
