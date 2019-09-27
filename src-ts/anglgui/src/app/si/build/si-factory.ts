
import { RefSiControl } from 'src/app/si/model/control/impl/ref-si-control';
import { SiComp } from 'src/app/si/model/entity/si-comp';
import { SiPartialContent } from 'src/app/si/model/entity/si-partial-content';
import { StringInSiField } from 'src/app/si/model/entity/impl/string/string-in-si-field';
import { ApiCallSiControl } from 'src/app/si/model/control/impl/api-call-si-control';
import { SiTypeBuildup } from 'src/app/si/model/entity/si-entry-buildup';
import { FileInSiField } from 'src/app/si/model/entity/impl/file/file-in-si-field';
import { FileOutSiField } from 'src/app/si/model/entity/impl/file/file-out-si-field';
import { SiQualifier, SiIdentifier } from 'src/app/si/model/entity/si-qualifier';
import { LinkOutSiField } from 'src/app/si/model/entity/impl/string/link-out-si-field';
import { QualifierSelectInSiField } from 'src/app/si/model/entity/impl/qualifier/qualifier-select-in-si-field';
import { SiResultFactory } from 'src/app/si/build/si-result-factory';
import { SiPage } from 'src/app/si/model/entity/impl/basic/si-page';
import { EntriesListSiContent } from 'src/app/si/model/entity/impl/basic/entries-list-si-content';
import { BulkyEntrySiComp } from 'src/app/si/model/entity/impl/basic/bulky-entry-si-comp';
import { EmbeddedEntryInSiField } from 'src/app/si/model/entity/impl/embedded/embedded-entry-in-si-field';
import { CompactEntrySiComp } from 'src/app/si/model/entity/impl/basic/compact-entry-si-comp';
import { SiEmbeddedEntry } from 'src/app/si/model/entity/impl/embedded/si-embedded-entry';
import { SiZone } from '../model/structure/si-zone';
import { Extractor, ObjectMissmatchError } from 'src/app/util/mapping/extractor';
import { SiEntry } from '../model/entity/si-entry';
import { SiField } from '../model/entity/si-field';
import { StringOutSiField } from '../model/entity/impl/string/string-out-si-field';
import { SiControl } from '../model/control/si-control';
import { SiButton, SiConfirm } from '../model/control/si-button';
import { SiControlType, SiFieldType, SiContentType } from './si-type';
import { SiType } from 'src/app/si/model/entity/si-type';
import { SiEntryDeclaration } from '../model/entity/si-entry-declaration';
import { EmbeddedEntryPanelsInSiField } from '../model/entity/impl/embedded/embedded-entry-panels-in-si-field';
import { SiPanel, SiGridPos } from '../model/entity/impl/embedded/si-panel';
import { NumberInSiField } from '../model/entity/impl/number/number-in-si-field';
import { BooleanSiField as BooleanInSiField } from '../model/entity/impl/boolean/boolean-in-si-field';
import { EnumInSiField } from '../model/entity/impl/string/enum-in-si-field';

export class SiZoneModelFactory {
	createZoneModel(data: any): SiZoneModel {
		const extr = new Extractor(data);

		return {
			title: extr.reqString('title'),
			breadcrumbs: this.createBreadcrumbs(extr.reqArray('breadcrumbs')),
			structureModel: new SiContentFactory().createContent(extr.reqObject('comp'))
		}
		
	}

	createBreadcrumbs(dataArr: Array<any>): SiBreadcrumb[] {
		const breadcrumbs: SiBreadcrumb[] = [];

		for (const data of dataArr) {
			breadcrumbs.push(this.createBreadcrumb(data));
		}

		return breadcrumbs;
	}

	createBreadcrumb(data: any): SiBreadcrumb {
		const extr = new Extractor(data);

		return {
			url: extr.reqString('url'),
			name: extr.reqString('name')
		};
	}
}

export class SiContentFactory {

	constructor() {
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
		let entryDeclaration: SiEntryDeclaration;

		const type = extr.reqString('type');

		if (!!requiredType && requiredType !== type) {
			throw new ObjectMissmatchError('Type ' + requiredType + ' required. Given: ' + type);
		}

		switch (type) {
			case SiContentType.ENTRIES_LIST:
				const listSiContent = new EntriesListSiContent(dataExtr.reqString('apiUrl'),
						dataExtr.reqNumber('pageSize'));

				compFactory = new SiCompFactory(listSiContent);
				listSiContent.entryDeclaration = SiResultFactory.createEntryDeclaration(dataExtr.reqObject('entryDeclaration'));

				const partialContentData = dataExtr.nullaObject('partialContent');
				if (partialContentData) {
					const partialContent = compFactory.createPartialContent(partialContentData);

					listSiContent.size = partialContent.count;
					listSiContent.putPage(new SiPage(1, partialContent.entries, null));
				}

				return listSiContent;

			case SiContentType.BULKY_ENTRY:
				entryDeclaration = SiResultFactory.createEntryDeclaration(dataExtr.reqObject('entryDeclaration'));
				const bulkyEntrySiContent = new BulkyEntrySiComp(entryDeclaration);

				compFactory = new SiCompFactory(bulkyEntrySiContent);
				bulkyEntrySiContent.controls = Array.from(compFactory.createControlMap(dataExtr.reqMap('controls')).values());
				bulkyEntrySiContent.entry = compFactory.createEntry(dataExtr.reqObject('entry'));
				return bulkyEntrySiContent;

			case SiContentType.COMPACT_ENTRY:
				entryDeclaration = SiResultFactory.createEntryDeclaration(dataExtr.reqObject('entryDeclaration'));
				const compactEntrySiContent = new CompactEntrySiComp(entryDeclaration);

				compFactory = new SiCompFactory(compactEntrySiContent);
				compactEntrySiContent.controlMap = compFactory.createControlMap(dataExtr.reqMap('controls'));
				compactEntrySiContent.entry = compFactory.createEntry(dataExtr.reqObject('entry'));
				return compactEntrySiContent;

			default:
				throw new ObjectMissmatchError('Invalid si zone type: ' + data.type);
		}
	}
}




export class SiCompFactory {

	constructor(private zoneContent: SiComp) {
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
		siEntry.bulky = extr.reqBoolean('bulky');
		siEntry.readOnly = extr.reqBoolean('readOnly');

		for (const [, buildupData] of extr.reqMap('buildups')) {
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
				this.createType(extr.reqObject('type')), extr.nullaString('idName'));
	}

	private createTypes(data: Array<any>) {
		const types: SiType[] = [];
		for (const typeData of data) {
			types.push(this.createType(typeData));
		}
		return types;
	}

	private createType(data: any): SiType {
		const extr = new Extractor(data);

		return new SiType(extr.reqString('typeId'), extr.reqString('name'), extr.reqString('iconClass'));
	}

	private createIdentifier(data: any): SiIdentifier {
		const extr = new Extractor(data);

		return new SiIdentifier(extr.reqString('category'), extr.nullaString('id'));
	}

	private createBuildup(data: any): SiTypeBuildup {
		const extr = new Extractor(data);

		return new SiTypeBuildup(this.createType(extr.reqObject('type')),
				extr.nullaString('idName'), this.createFieldMap(extr.reqMap('fields')),
				this.createControlMap(extr.reqMap('controls')));
	}

	private createFieldMap(data: Map<string, any>): Map<string, SiField> {
		const fields = new Map<string, SiField>();

		for (const [fieldId, fieldData] of data) {
			fields.set(fieldId, this.createField(fieldData));
		}
		return fields;
	}

	private createField(data: any): SiField {
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

		case SiFieldType.NUMBER_IN:
			const numberInSiField = new NumberInSiField();
			numberInSiField.min = dataExtr.nullaNumber('min');
			numberInSiField.max = dataExtr.nullaNumber('max');
			numberInSiField.step = dataExtr.reqNumber('step');
			numberInSiField.arrowStep = dataExtr.nullaNumber('arrowStep');
			numberInSiField.fixed = dataExtr.reqBoolean('fixed');
			numberInSiField.setValue(dataExtr.nullaString('value'));
			return numberInSiField;

		case SiFieldType.BOOLEAN_IN:
			const booleanInSiField = new BooleanInSiField();
			booleanInSiField.value = dataExtr.reqBoolean('value');
			return booleanInSiField;

		case SiFieldType.FILE_OUT:
			return new FileOutSiField(SiResultFactory.buildSiFile(dataExtr.nullaObject('value')));

		case SiFieldType.FILE_IN:
			const fileInSiField = new FileInSiField(dataExtr.reqString('apiUrl'),
					dataExtr.reqObject('apiCallId'), SiResultFactory.buildSiFile(dataExtr.nullaObject('value')));
			fileInSiField.mandatory = dataExtr.reqBoolean('mandatory');
			fileInSiField.maxSize = dataExtr.reqNumber('maxSize');
			fileInSiField.acceptedMimeTypes = dataExtr.reqStringArray('acceptedMimeTypes');
			fileInSiField.acceptedExtensions = dataExtr.reqStringArray('acceptedExtensions');
			return fileInSiField;

		case SiFieldType.LINK_OUT:
			return new LinkOutSiField(dataExtr.reqBoolean('href'), dataExtr.reqString('ref'),
					dataExtr.reqString('label'));

		case SiFieldType.ENUM_IN:
			const enumInSiField = new EnumInSiField(dataExtr.nullaString('value'), dataExtr.reqStringMap('options'));
			enumInSiField.mandatory = dataExtr.reqBoolean('mandatory');
			return enumInSiField;

		case SiFieldType.QUALIFIER_SELECT_IN:
			const qualifierSelectInSiField = new QualifierSelectInSiField(dataExtr.reqString('apiUrl'),
					this.createQualifiers(dataExtr.reqArray('values')));
			qualifierSelectInSiField.min = dataExtr.reqNumber('min');
			qualifierSelectInSiField.max = dataExtr.nullaNumber('max');
			return qualifierSelectInSiField;

		case SiFieldType.EMBEDDED_ENTRY_IN:
			const embeddedEntryInSiField = new EmbeddedEntryInSiField(dataExtr.reqString('apiUrl'),
					this.createEmbeddedEntries(dataExtr.reqArray('values')));
			embeddedEntryInSiField.content.reduced = dataExtr.reqBoolean('reduced');
			embeddedEntryInSiField.content.min = dataExtr.reqNumber('min');
			embeddedEntryInSiField.content.max = dataExtr.nullaNumber('max');
			embeddedEntryInSiField.content.nonNewRemovable = dataExtr.reqBoolean('nonNewRemovable');
			embeddedEntryInSiField.content.sortable = dataExtr.reqBoolean('sortable');
			embeddedEntryInSiField.content.pasteCategory = dataExtr.nullaString('pasteCategory');

			const allowedSiTypesData = dataExtr.nullaArray('allowedSiTypes');
			if (allowedSiTypesData) {
				embeddedEntryInSiField.content.allowedSiTypes = this.createTypes(allowedSiTypesData);
			} else {
				embeddedEntryInSiField.content.allowedSiTypes = null;
			}

			return embeddedEntryInSiField;

		case SiFieldType.EMBEDDED_ENTRY_PANELS_IN:
			return new EmbeddedEntryPanelsInSiField(dataExtr.reqString('apiUrl'),
					this.createPanels(dataExtr.reqArray('panels')));

		default:
			throw new ObjectMissmatchError('Invalid si field type: ' + data.type);
		}
	}

	private createPanels(data: Array<any>): SiPanel[] {
		const panels: SiPanel[] = [];
		for (const panelData of data) {
			panels.push(this.createPanel(panelData));
		}
		return panels;
	}

	private createPanel(data: any): SiPanel {
		const extr = new Extractor(data);

		const panel = new SiPanel(extr.reqString('name'), extr.reqString('label'));
		panel.values = this.createEmbeddedEntries(extr.reqArray('values'));
		panel.reduced = extr.reqBoolean('reduced');
		panel.min = extr.reqNumber('min');
		panel.max = extr.nullaNumber('max');
		panel.nonNewRemovable = extr.reqBoolean('nonNewRemovable');
		panel.sortable = extr.reqBoolean('sortable');
		panel.pasteCategory = extr.nullaString('pasteCategory');
		panel.gridPos = this.buildGridPos(extr.nullaObject('gridPos'));

		const allowedSiTypesData = extr.nullaArray('allowedTypes');
		if (allowedSiTypesData) {
			panel.allowedSiTypes = this.createTypes(allowedSiTypesData);
		} else {
			panel.allowedSiTypes = null;
		}

		return panel;
	}

	private buildGridPos(data: any): SiGridPos|null {
		if (data === null) {
			return null;
		}

		const extr = new Extractor(data);

		return {
			colStart: extr.reqNumber('colStart'),
			colEnd: extr.reqNumber('colEnd'),
			rowStart: extr.reqNumber('rowStart'),
			rowEnd: extr.reqNumber('rowEnd')
		};
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
		const contentFactory = new SiContentFactory();

		return new SiEmbeddedEntry(
				contentFactory.createContent(extr.reqObject('content'),
						SiContentType.BULKY_ENTRY) as BulkyEntrySiComp,
				contentFactory.createContent(extr.reqObject('summaryContent'),
						SiContentType.COMPACT_ENTRY) as CompactEntrySiComp);
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
						this.createButton(dataExtr.reqObject('button')));
			case SiControlType.API_CALL:
				const apiControl = new ApiCallSiControl(
						dataExtr.reqString('apiUrl'),
						dataExtr.reqObject('apiCallId'),
						this.createButton(dataExtr.reqObject('button')),
						this.zoneContent);
				apiControl.inputSent = dataExtr.reqBoolean('inputHandled');
				return apiControl;
			default:
				throw new ObjectMissmatchError('Invalid si field type: ' + data.type);
		}
	}

	private createButton(data: any): SiButton {
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
