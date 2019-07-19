
import { ObjectMissmatchError, Extractor } from "src/app/util/mapping/extractor";
import { SiZone } from "src/app/si/model/structure/si-zone";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiField } from "src/app/si/model/content/si-field";
import { SiFieldDeclaration } from "src/app/si/model/structure/si-field-declaration";
import { SiCompactDeclaration } from "src/app/si/model/structure/si-compact-declaration";
import { StringOutSiField } from "src/app/si/model/content/impl/string-out-si-field";
import { SiControl } from "src/app/si/model/control/si-control";
import { SiButton, SiConfirm } from "src/app/si/model/control/si-button";
import { RefSiControl } from "src/app/si/model/control/impl/ref-si-control";
import { SiFieldStructureDeclaration } from "src/app/si/model/structure/si-field-structure-declaration";
import { SiBulkyDeclaration } from "src/app/si/model/structure/si-bulky-declaration";
import { SiContent } from "src/app/si/model/structure/si-zone-content";
import { SiPartialContent } from "src/app/si/model/content/si-partial-content";
import { StringInSiField } from "src/app/si/model/content/impl/string-in-si-field";
import { ApiCallSiControl } from "src/app/si/model/control/impl/api-call-si-control";
import { SiEntryBuildup } from "src/app/si/model/content/si-entry-buildup";
import { SiFile, FileInSiField } from "src/app/si/model/content/impl/file-in-si-field";
import { FileOutSiField } from "src/app/si/model/content/impl/file-out-si-field";
import { SiQualifier } from "src/app/si/model/content/si-qualifier";
import { LinkOutSiField } from "src/app/si/model/content/impl/link-out-si-field";
import { QualifierSelectInSiField } from "src/app/si/model/content/impl/qualifier-select-in-si-field";
import { SiResultFactory } from "src/app/si/build/si-result-factory";
import { SiCompFactory } from "src/app/si/build/si-comp-factory";
import { SiPage } from "src/app/si/model/structure/impl/si-page";
import { EntriesListSiContent } from "src/app/si/model/structure/impl/entries-list-si-content";
import { BulkyEntrySiContent } from "src/app/si/model/structure/impl/bulky-entry-si-content";

export class SiFactory {
	
	constructor(private zone: SiZone) {
	}
	
	createZoneContent(data: any): SiContent {
		const extr = new Extractor(data);
		const dataExtr = extr.reqExtractor('data');
		let compFactory: SiCompFactory;
		
		switch (extr.reqString('type')) {
			case SiZoneType.ENTRIES_LIST:
				const listSiContent = new EntriesListSiContent(extr.reqString('apiUrl'), 
						dataExtr.reqNumber('pageSize'), this.zone);
				
				compFactory = new SiCompFactory(this.zone, listSiContent);
				listSiContent.compactDeclaration = SiResultFactory.createCompactDeclaration(dataExtr.reqObject('compactDeclaration'));
				
				const partialContentData = dataExtr.nullaObject('partialContent');
				if (partialContentData) {
					const partialContent = compFactory.createPartialContent(partialContentData);
					
					listSiContent.size = partialContent.count;
					listSiContent.putPage(new SiPage(1, partialContent.entries, null));
				}
				
				return listSiContent;
			case SiZoneType.BULKY_ENTRY:
				const bulkyDeclaration = SiResultFactory.createBulkyDeclaration(dataExtr.reqObject('bulkyDeclaration'));
				const bulkyEntrySiContent = new BulkyEntrySiContent(bulkyDeclaration, this.zone);
				
				compFactory = new SiCompFactory(this.zone, bulkyEntrySiContent);
				bulkyEntrySiContent.controlMap = compFactory.createControlMap(dataExtr.reqMap('controls'));
				bulkyEntrySiContent.entry = compFactory.createEntry(dataExtr.reqArray('entry'));
				return bulkyEntrySiContent;
			default:
				throw new ObjectMissmatchError('Invalid si zone type: ' + data.type);
		}
	}
}



export enum SiZoneType {
    ENTRIES_LIST = 'entries-list',
    BULKY_ENTRY = 'bulky-entry',
    COMPACT_ENTRY = 'compcat-entry'
} 

