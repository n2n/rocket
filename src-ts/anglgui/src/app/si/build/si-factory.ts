
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
import { SiQualifier, SiIdentifier } from "src/app/si/model/content/si-qualifier";
import { LinkOutSiField } from "src/app/si/model/content/impl/link-out-si-field";
import { QualifierSelectInSiField } from "src/app/si/model/content/impl/qualifier-select-in-si-field";
import { SiResultFactory } from "src/app/si/build/si-result-factory";
import { SiPage } from "src/app/si/model/structure/impl/si-page";
import { EntriesListSiContent } from "src/app/si/model/structure/impl/entries-list-si-content";
import { BulkyEntrySiContent } from "src/app/si/model/structure/impl/bulky-entry-si-content";
import { EmbeddedEntryInSiField } from "src/app/si/model/content/impl/embedded-entry-in-si-field";
import { CompactEntrySiContent } from "src/app/si/model/structure/impl/compact-entry-si-content";
import { SiEmbeddedEntry } from "src/app/si/model/content/si-embedded-entry";


export class SiContentFactory {
	
	constructor(private zone: SiZone) {
	}
	
	createContents(dataArr: Array<any>, requiredType: SiContentType|null = null): SiContent[] {
		const contents = [];
		for (const data of dataArr) {
			contents.push(this.createContent(data));
		}
		return contents;
	}
	
	createContent(data: any, requiredType: SiContentType|null = null): SiContent {
		const extr = new Extractor(data);
		const dataExtr = extr.reqExtractor('data');
		let compFactory: SiCompFactory;
		
		const type = extr.reqString('type');
		
		if (!!requiredType && requiredType != type) {
			throw new ObjectMissmatchError('Type ' + requiredType + ' required. Give. ' + type);
		}
		

		switch (type) {
			case SiContentType.ENTRIES_LIST:
				const listSiContent = new EntriesListSiContent(dataExtr.reqString('apiUrl'), 
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
				
			case SiContentType.BULKY_ENTRY:
				const bulkyDeclaration = SiResultFactory.createBulkyDeclaration(dataExtr.reqObject('bulkyDeclaration'));
				const bulkyEntrySiContent = new BulkyEntrySiContent(bulkyDeclaration, this.zone);
				
				compFactory = new SiCompFactory(this.zone, bulkyEntrySiContent);
				bulkyEntrySiContent.controlMap = compFactory.createControlMap(dataExtr.reqMap('controls'));
				bulkyEntrySiContent.entry = compFactory.createEntry(dataExtr.reqObject('entry'));
				return bulkyEntrySiContent;
				
			case SiContentType.COMPACT_ENTRY:
				const compactDeclaration = SiResultFactory.createCompactDeclaration(dataExtr.reqObject('compactDeclaration'));
				const compactEntrySiContent = new CompactEntrySiContent(compactDeclaration, this.zone);
				
				compFactory = new SiCompFactory(this.zone, compactEntrySiContent);
				compactEntrySiContent.controlMap = compFactory.createControlMap(dataExtr.reqMap('controls'));
				compactEntrySiContent.entry = compFactory.createEntry(dataExtr.reqObject('entry'));
				return compactEntrySiContent;
				
			default:
				throw new ObjectMissmatchError('Invalid si zone type: ' + data.type);
		}
	}
}



export enum SiContentType {
    ENTRIES_LIST = 'entries-list',
    BULKY_ENTRY = 'bulky-entry',
    COMPACT_ENTRY = 'compact-entry'
} 



export class SiCompFactory {
	
	constructor(private zone: SiZone, private zoneContent: SiContent) {
	}
	
	createPartialContent(data: any): SiPartialContent {
		const extr = new Extractor(data);
		return {
			entries: this.createEntries(extr.reqArray('entries')),
			count: extr.reqNumber('count'),
			offset: extr.reqNumber('offset')
		};
	}
	
	createEntries(data: Array<any>): SiEntry[] {
		let entries: Array<SiEntry> = [];
		for (let entryData of data) {
			entries.push(this.createEntry(entryData));
		}
		
		return entries;
	}
	
	createEntry(entryData :any): SiEntry {
		const extr = new Extractor(entryData);
		
		const siEntry = new SiEntry(this.createIdentifier(extr.reqObject('identifier')));
		siEntry.treeLevel = extr.nullaNumber('treeLevel');
		siEntry.inputAvailable = extr.reqBoolean('inputAvailable');
		
		for (let [buildupId, buildupData] of extr.reqMap('buildups')) {
			siEntry.putBuildup(buildupId, this.createBuildup(buildupData));
		}
		
		return siEntry;
	}
	
	private createQualifiers(datas: Array<any>): SiQualifier[] {
		const qualifiers = new Array<SiQualifier>();
		for (const data of datas) {
			qualifiers.push(this.createQualifier(data));
		}
		return qualifiers;
	}
	
	private createQualifier(data: any): SiQualifier {
		const extr = new Extractor(data);
		
		return new SiQualifier(extr.reqString('category'), extr.nullaString('id'),
				extr.reqString('name'), extr.reqString('iconClass'), extr.reqString('idName'));
	}
	
	private createIdentifier(data: any): SiIdentifier {
		const extr = new Extractor(data);
		
		return new SiIdentifier(extr.reqString('category'), extr.nullaString('id'));
	}
	
	private createBuildup(data: any): SiEntryBuildup { 
		const extr = new Extractor(data);
		
		return new SiEntryBuildup(extr.reqString('name'), extr.reqString('iconClass'), extr.nullaString('idName'),
				this.createFieldMap(extr.reqMap('fields')), this.createControlMap(extr.reqMap('controls')));
	}
	
	private createFieldMap(data: Map<string, any>): Map<string, SiField> {
		const fields = new Map<string, SiField>();
		
		for (let[fieldId, fieldData] of data) {
			fields.set(fieldId, this.createField(fieldId, fieldData));
		}
		return fields;
	}
	
	private createField(fieldId: string, data: any): SiField {
		const extr = new Extractor(data);
		const dataExtr = extr.reqExtractor('data');
		
		switch (extr.reqString('type')) {
		
		case SiFieldType.STRING_OUT:
			return new StringOutSiField(dataExtr.nullaString('value'));
			
		case SiFieldType.STRING_IN:
			const stringInSiField = new StringInSiField(dataExtr.nullaString('value'), dataExtr.reqBoolean('multiline'));
			stringInSiField.maxlength = dataExtr.nullaNumber('maxlength');
			stringInSiField.mandatory = dataExtr.reqBoolean('mandatory');
			return stringInSiField;
			
		case SiFieldType.FILE_OUT:
			return new FileOutSiField(this.buildSiFile(dataExtr.nullaObject('value')));
			
		case SiFieldType.FILE_IN:
			const fileInSiField = new FileInSiField(this.buildSiFile(dataExtr.nullaObject('value')));
			fileInSiField.mandatory = dataExtr.reqBoolean('mandatory');
			fileInSiField.mimeTypes = dataExtr.reqStringArray('mimeTypes');
			fileInSiField.extensions = dataExtr.reqStringArray('extensions');
			return fileInSiField;
			
		case SiFieldType.LINK_OUT:
			return new LinkOutSiField(dataExtr.reqBoolean('href'), dataExtr.reqString('ref'),
					dataExtr.reqString('label'));
			
		case SiFieldType.QUALIFIER_SELECT_IN:
			const qualifierSelectInSiField = new QualifierSelectInSiField(this.zone, dataExtr.reqString('apiUrl'),
					this.createQualifiers(dataExtr.reqArray('values')));
			qualifierSelectInSiField.min = dataExtr.reqNumber('min');
			qualifierSelectInSiField.max = dataExtr.nullaNumber('max');
			return qualifierSelectInSiField;
			
		case SiFieldType.EMBEDDED_ENTRY_IN: 
			const embeddedEntryInSiField = new EmbeddedEntryInSiField(this.zone, dataExtr.reqString('apiUrl'),
					this.createEmbeddedEntries(dataExtr.reqArray('values')));
			embeddedEntryInSiField.reduced = dataExtr.reqBoolean('reduced');
			embeddedEntryInSiField.min = dataExtr.reqNumber('min');
			embeddedEntryInSiField.max = dataExtr.nullaNumber('max');
			return embeddedEntryInSiField;
			
		default: 
			throw new ObjectMissmatchError('Invalid si field type: ' + data.type);
		}	
	}
	
	private createEmbeddedEntries(data: Array<any>): SiEmbeddedEntry[] {
		const entries: SiEmbeddedEntry[] = [];
		for (let entryData of data) {
			entries.push(this.createEmbeddedEntry(entryData));
		}
		return entries;
	}
	
	private createEmbeddedEntry(data: any): SiEmbeddedEntry {
		const extr = new Extractor(data);
		const contentFactory = new SiContentFactory(this.zone);
		
		return new SiEmbeddedEntry(
				<BulkyEntrySiContent> contentFactory.createContent(extr.reqObject('content'), 
						SiContentType.BULKY_ENTRY),
				<CompactEntrySiContent> contentFactory.createContent(extr.reqObject('summaryContent'), 
						SiContentType.COMPACT_ENTRY));
	}
	
	private buildSiFile(data: any): SiFile|null {
		if (data === null) {
			return null;
		}
		
		const extr = new Extractor(data);
		
		return {
			valid: extr.reqBoolean('valid'),
			name: extr.reqString('name'),
			url: extr.nullaString('url'),
			thumbUrl: extr.nullaString('thumbUrl')
		}
	} 
	
	createControlMap(data: Map<string, any>): Map<string, SiControl> {
		const controls = new Map<string, SiControl>();
		
		for (let[controlId, controlData] of data) {
			controls.set(controlId, this.createControl(controlId, controlData));
		}
		return controls;
	}
	
	private createControl(controlId: string, data: any): SiControl {
		const extr = new Extractor(data);
		const dataExtr = extr.reqExtractor('data');
		
		switch (extr.reqString('type')) {
			case SiControlType.REF:
				return new RefSiControl(
						dataExtr.reqString('url'),
						this.createButton(controlId, dataExtr.reqObject('button')),
						this.zone.layer);
			case SiControlType.API_CALL:
				const apiControl = new ApiCallSiControl(
						dataExtr.reqString('apiUrl'),
						dataExtr.reqObject('apiCallId'),
						this.createButton(controlId, dataExtr.reqObject('button')),
						this.zoneContent);
				apiControl.inputSent = dataExtr.reqBoolean('inputHandled');
				return apiControl;
			default: 
				throw new ObjectMissmatchError('Invalid si field type: ' + data.type);
		}	
	}
	
	private createButton(controlId, data: any): SiButton {
		const extr = new Extractor(data);
		const btn = new SiButton(extr.reqString('name'), extr.reqString('btnClass'), extr.reqString('iconClass'));
		
		btn.tooltip = extr.nullaString('tooltip');
		btn.important = extr.reqBoolean('important');
		btn.iconImportant = extr.reqBoolean('iconImportant');
		btn.labelImportant = extr.reqBoolean('labelImportant');
		
		const confirmData = extr.nullaObject('confirm');
		if (confirmData) {
			btn.confirm = this.createConfirm(confirmData);
		}
		return btn;
	}
	
	private createConfirm(data: any): SiConfirm {
		const extr = new Extractor(data);
		
		return {
			message: extr.nullaString('message'),
			okLabel: extr.nullaString('okLabel'),
			cancelLabel: extr.nullaString('cancelLabel')
		};
	}
	
}

export enum SiFieldType {
	STRING_OUT = 'string-out',
	STRING_IN = 'string-in',
    FILE_OUT = 'file-out',
    FILE_IN = 'file-in',
    LINK_OUT = 'link-out',
    QUALIFIER_SELECT_IN = 'qualifier-select-in',
    EMBEDDED_ENTRY_IN = 'embedded-entry-in'
}

export enum SiControlType {
	REF = 'ref',
	API_CALL = 'api-call'
}

