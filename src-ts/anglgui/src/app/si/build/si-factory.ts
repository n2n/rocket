
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
				listSiZoneContent.compactDeclaration = this.createCompactDeclaration(dataExtr.reqObject('compactDeclaration'));
				
				const partialContentData = dataExtr.nullaObject('partialContent');
				if (partialContentData) {
					const partialContent = compFactory.createPartialContent(partialContentData);
					
					listSiZoneContent.size = partialContent.count;
					listSiZoneContent.putPage(new SiPage(1, partialContent.entries));
				}
				
				return listSiZoneContent;
			case SiZoneType.DL:
				const bulkyDeclaration = this.createBulkyDeclaration(dataExtr.reqObject('bulkyDeclaration'));
				const dlSiZoneContent = new DlSiZoneContent(extr.reqString('apiUrl'), bulkyDeclaration, this.zone);
				
				compFactory = new SiCompFactory(this.zone, dlSiZoneContent);
				dlSiZoneContent.controlMap = compFactory.createControlMap(dataExtr.reqMap('controls'));
				dlSiZoneContent.entries = compFactory.createEntries(dataExtr.reqArray('entries'));
				return dlSiZoneContent;
			default:
				throw new ObjectMissmatchError('Invalid si zone type: ' + data.type);
		}
	}
	
	createCompactDeclaration(data: any): SiCompactDeclaration {
		const extr = new Extractor(data);
		
		const declarationMap = new Map<string, SiFieldDeclaration[]>();
		for (let [buildupId, declarationData] of extr.reqArrayMap('fieldDeclarations')) {
			declarationMap.set(buildupId, this.createFieldDeclarations(declarationData));
		}
		
		return new SiCompactDeclaration(declarationMap);
	}
	
	createBulkyDeclaration(data: any): SiBulkyDeclaration {
		const extr = new Extractor(data);
		
		const declarationMap = new Map<string, SiFieldStructureDeclaration[]>();
		for (let [buildupId, declarationData] of extr.reqArrayMap('fieldStructureDeclarations')) {
			declarationMap.set(buildupId, this.createFieldStructureDeclarations(declarationData));
		}
		
		return new SiBulkyDeclaration(declarationMap);
	}
	
	private createFieldStructureDeclarations(data: Array<any>): SiFieldStructureDeclaration[] {
		const declarations: Array<SiFieldStructureDeclaration> = [];
		for (const declarationData of data) {
			declarations.push(this.createFieldStructureDeclaration(declarationData));
		}
		return declarations;
	}
	
	private createFieldStructureDeclaration(data: any): SiFieldStructureDeclaration {
		const extr = new Extractor(data);
		
		return new SiFieldStructureDeclaration(
				this.createFieldDeclaration(extr.reqObject('fieldDeclaration')), 
				<any> extr.reqString('structureType'), 
				this.createFieldStructureDeclarations(extr.reqArray('children')));
	}
	
	private createFieldDeclarations(data: Array<any>): SiFieldDeclaration[] {
		const declarations: Array<SiFieldDeclaration> = [];
		for (const declarationData of data) {
			declarations.push(this.createFieldDeclaration(declarationData));
		}
		return declarations;
	}
	
	private createFieldDeclaration(data: any): SiFieldDeclaration {
		const extr = new Extractor(data);
		
		return new SiFieldDeclaration(extr.nullaString('fieldId'), 
				extr.nullaString('label'), extr.nullaString('helpText'));
	}
}


export class SiCompFactory {

	constructor(private zone: SiZone, private zoneContent: SiZoneContent) {
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
			const extr = new Extractor(entryData);
			
			const siEntry = new SiEntry(this.createQualifier(extr.reqObject('qualifier')));
			siEntry.treeLevel = extr.nullaNumber('treeLevel');
			siEntry.inputAvailable = extr.reqBoolean('inputAvailable');
			
			for (let [buildupId, buildupData] of extr.reqMap('buildups')) {
				siEntry.putBuildup(buildupId, this.createBuildup(buildupData));
			}
			
			entries.push(siEntry);
		}
		
		return entries;
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
				extr.reqString('name'));
	}
	
	private createBuildup(data: any): SiEntryBuildup { 
		const extr = new Extractor(data);
		
		return new SiEntryBuildup(extr.reqString('name'),
				this.createFieldMap(extr.reqMap('fields')),
				this.createControlMap(extr.reqMap('controls')));
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
		default: 
			throw new ObjectMissmatchError('Invalid si field type: ' + data.type);
		}	
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

export enum SiZoneType {
    LIST = 'list',
    DL = 'dl'
} 

export enum SiFieldType {
	STRING_OUT = 'string-out',
	STRING_IN = 'string-in',
    FILE_OUT = 'file-out',
    FILE_IN = 'file-in',
    LINK_OUT = 'link-out',
    QUALIFIER_SELECT_IN = 'qualifier-select-in'
}

export enum SiControlType {
	REF = 'ref',
	API_CALL = 'api-call'
}
