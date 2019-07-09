
import { ObjectMissmatchError, Extractor } from "src/app/util/mapping/extractor";
import { DlSiZoneContent } from "src/app/si/model/structure/impl/dl-si-zone-content";
import { ListSiZoneContent, SiPage } from "src/app/si/model/structure/impl/list-si-zone-content";
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
import { SiZoneContent } from "src/app/si/model/structure/si-zone-content";
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

export class SiFactory {
	
	constructor(private zone: SiZone) {
	}
	
	createZoneContent(data: any): SiZoneContent {
		const extr = new Extractor(data);
		const dataExtr = extr.reqExtractor('data');
		let compFactory: SiCompFactory;
		
		switch (extr.reqString('type')) {
			case SiZoneType.LIST:
				const listSiZoneContent = new ListSiZoneContent(extr.reqString('apiUrl'), 
						dataExtr.reqNumber('pageSize'), this.zone);
				
				compFactory = new SiCompFactory(this.zone, listSiZoneContent);
				listSiZoneContent.compactDeclaration = SiResultFactory.createCompactDeclaration(dataExtr.reqObject('compactDeclaration'));
				
				const partialContentData = dataExtr.nullaObject('partialContent');
				if (partialContentData) {
					const partialContent = compFactory.createPartialContent(partialContentData);
					
					listSiZoneContent.size = partialContent.count;
					listSiZoneContent.putPage(new SiPage(1, partialContent.entries));
				}
				
				return listSiZoneContent;
			case SiZoneType.DL:
				const bulkyDeclaration = SiResultFactory.createBulkyDeclaration(dataExtr.reqObject('bulkyDeclaration'));
				const dlSiZoneContent = new DlSiZoneContent(extr.reqString('apiUrl'), bulkyDeclaration, this.zone);
				
				compFactory = new SiCompFactory(this.zone, dlSiZoneContent);
				dlSiZoneContent.controlMap = compFactory.createControlMap(dataExtr.reqMap('controls'));
				dlSiZoneContent.entries = compFactory.createEntries(dataExtr.reqArray('entries'));
				return dlSiZoneContent;
			default:
				throw new ObjectMissmatchError('Invalid si zone type: ' + data.type);
		}
	}
}



export enum SiZoneType {
    LIST = 'list',
    DL = 'dl'
} 

