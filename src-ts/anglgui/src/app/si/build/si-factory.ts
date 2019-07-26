
import { RefSiControl } from 'src/app/si/model/control/impl/ref-si-control';
import { SiComp } from 'src/app/si/model/structure/si-zone-content';
import { SiPartialContent } from 'src/app/si/model/content/si-partial-content';
import { StringInSiField } from 'src/app/si/model/content/impl/string-in-si-field';
import { ApiCallSiControl } from 'src/app/si/model/control/impl/api-call-si-control';
import { SiTypeBuildup } from 'src/app/si/model/content/si-entry-buildup';
import { SiFile, FileInSiField } from 'src/app/si/model/content/impl/file-in-si-field';
import { FileOutSiField } from 'src/app/si/model/content/impl/file-out-si-field';
import { SiQualifier, SiIdentifier } from 'src/app/si/model/content/si-qualifier';
import { LinkOutSiField } from 'src/app/si/model/content/impl/link-out-si-field';
import { QualifierSelectInSiField } from 'src/app/si/model/content/impl/qualifier-select-in-si-field';
import { SiResultFactory } from 'src/app/si/build/si-result-factory';
import { SiPage } from 'src/app/si/model/structure/impl/si-page';
import { EntriesListSiContent } from 'src/app/si/model/structure/impl/entries-list-si-content';
import { BulkyEntrySiComp } from 'src/app/si/model/structure/impl/bulky-entry-si-content';
import { EmbeddedEntryInSiField } from 'src/app/si/model/content/impl/embedded-entry-in-si-field';
import { CompactEntrySiComp } from 'src/app/si/model/structure/impl/compact-entry-si-content';
import { SiEmbeddedEntry } from 'src/app/si/model/content/si-embedded-entry';
import { SiZone } from '../model/structure/si-zone';
import { Extractor, ObjectMissmatchError } from 'src/app/util/mapping/extractor';
import { SiEntry } from '../model/content/si-entry';
import { SiField } from '../model/content/si-field';
import { StringOutSiField } from '../model/content/impl/string-out-si-field';
import { SiControl } from '../model/control/si-control';
import { SiButton, SiConfirm } from '../model/control/si-button';
import { SiControlType, SiFieldType, SiContentType } from './si-type';


export class SiContentFactory {

	constructor(private zone: SiZone) {
	}

	createContents(dataArr: Array<any>, requiredType: SiContentType|null = null): SiComp[] {
		const contents = [];
		for (const data of dataArr) {
			contents.push(this.createContent(data, requiredType));
		}
		return contents;
	}

	createContent(data: any, requiredType: SiContentType|null = null): SiComp {
		const extr = new Extractor(data);
		const dataExtr = extr.reqExtractor('data');
		let compFactory: SiCompFactory;

		const type = extr.reqString('type');

		if (!!requiredType && requiredType !== type) {
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
				const bulkyEntrySiContent = new BulkyEntrySiComp(bulkyDeclaration, this.zone);

				compFactory = new SiCompFactory(this.zone, bulkyEntrySiContent);
				bulkyEntrySiContent.controls = Array.from(compFactory.createControlMap(dataExtr.reqMap('controls')).values());
				bulkyEntrySiContent.entry = compFactory.createEntry(dataExtr.reqObject('entry'));
				return bulkyEntrySiContent;

			case SiContentType.COMPACT_ENTRY:
				const compactDeclaration = SiResultFactory.createCompactDeclaration(dataExtr.reqObject('compactDeclaration'));
				const compactEntrySiContent = new CompactEntrySiComp(compactDeclaration, this.zone);

				compFactory = new SiCompFactory(this.zone, compactEntrySiContent);
				compactEntrySiContent.controlMap = compFactory.createControlMap(dataExtr.reqMap('controls'));
				compactEntrySiContent.entry = compFactory.createEntry(dataExtr.reqObject('entry'));
				return compactEntrySiContent;

			default:
				throw new ObjectMissmatchError('Invalid si zone type: ' + data.type);
		}
	}
}




export class SiCompFactory {

	constructor(private zone: SiZone, private zoneContent: SiComp) {
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
		const entries: Array<SiEntry> = [];
		for (const entryData of data) {
			entries.push(this.createEntry(entryData));
		}

		return entries;
	}

	createEntry(entryData: any): SiEntry {
		const extr = new Extractor(entryData);

		const siEntry = new SiEntry(this.createIdentifier(extr.reqObject('identifier')));
		siEntry.treeLevel = extr.nullaNumber('treeLevel');
		siEntry.inputAvailable = extr.reqBoolean('inputAvailable');

		for (const [buildupId, buildupData] of extr.reqMap('buildups')) {
			siEntry.putTypeBuildup(this.createBuildup(buildupData));
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
				extr.reqString('typeId'), extr.reqString('typeName'), extr.reqString('iconClass'),
				extr.nullaString('idName'));
	}

	private createIdentifier(data: any): SiIdentifier {
		const extr = new Extractor(data);

		return new SiIdentifier(extr.reqString('category'), extr.nullaString('id'));
	}

	private createBuildup(data: any): SiTypeBuildup {
		const extr = new Extractor(data);

		return new SiTypeBuildup(extr.reqString('typeId'), extr.reqString('typeName'), extr.reqString('iconClass'),
				extr.nullaString('idName'), this.createFieldMap(extr.reqMap('fields')),
				this.createControlMap(extr.reqMap('controls')));
	}

	private createFieldMap(data: Map<string, any>): Map<string, SiField> {
		const fields = new Map<string, SiField>();

		for (const [fieldId, fieldData] of data) {
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
			embeddedEntryInSiField.nonNewRemovable = dataExtr.reqBoolean('nonNewRemovable');
			embeddedEntryInSiField.sortable = dataExtr.reqBoolean('sortable');
			return embeddedEntryInSiField;

		default:
			throw new ObjectMissmatchError('Invalid si field type: ' + data.type);
		}
	}

	private createEmbeddedEntries(data: Array<any>): SiEmbeddedEntry[] {
		const entries: SiEmbeddedEntry[] = [];
		for (const entryData of data) {
			entries.push(this.createEmbeddedEntry(entryData));
		}
		return entries;
	}

	private createEmbeddedEntry(data: any): SiEmbeddedEntry {
		const extr = new Extractor(data);
		const contentFactory = new SiContentFactory(this.zone);

		return new SiEmbeddedEntry(
				contentFactory.createContent(extr.reqObject('content'),
						SiContentType.BULKY_ENTRY) as BulkyEntrySiComp,
				contentFactory.createContent(extr.reqObject('summaryContent'),
						SiContentType.COMPACT_ENTRY) as CompactEntrySiComp);
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

		for (const[controlId, controlData] of data) {
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