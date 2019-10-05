import { SiComp } from '../model/comp/si-comp';
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiPartialContent } from '../model/content/si-partial-content';
import { SiEntry } from '../model/content/si-entry';


export class SiCompEssentialsFactory {

	constructor(private comp: SiComp) {
	}

	

	private createQualifiers(datas: Array<any>): SiEntryQualifier[] {
		const qualifiers = new Array<SiEntryQualifier>();
		for (const data of datas) {
			qualifiers.push(this.createQualifier(data));
		}
		return qualifiers;
	}

	private createQualifier(data: any): SiEntryQualifier {
		const extr = new Extractor(data);

		return new SiEntryQualifier(extr.reqString('category'), extr.nullaString('id'),
				this.createType(extr.reqObject('type')), extr.nullaString('idName'));
	}

	// private createTypes(data: Array<any>) {
	// 	const types: SiType[] = [];
	// 	for (const typeData of data) {
	// 		types.push(this.createType(typeData));
	// 	}
	// 	return types;
	// }

	// private createType(data: any): SiType {
	// 	const extr = new Extractor(data);

	// 	return new SiType(extr.reqString('typeId'), extr.reqString('name'), extr.reqString('iconClass'));
	// }


	private createField(data: any): SiField {
		const extr = new Extractor(data);
		const dataExtr = extr.reqExtractor('data');

		switch (extr.reqString('type')) {

		case SiFieldType.STRING_OUT:
			return new StringOutSiField(dataExtr.nullaString('value'));

		case SiFieldType.STRING_IN:
			const stringInSiField = new StringInSiField(dataExtr.nullaString('value'), dataExtr.reqBoolean('multiline'));
			stringInSiField.minlength = dataExtr.nullaNumber('minlength');
			stringInSiField.maxlength = dataExtr.nullaNumber('maxlength');
			stringInSiField.mandatory = dataExtr.reqBoolean('mandatory');
			stringInSiField.prefixAddons = this.createCrumbGroups(dataExtr.reqArray('prefixAddons'));
			stringInSiField.suffixAddons = this.createCrumbGroups(dataExtr.reqArray('suffixAddons'));
			return stringInSiField;

		case SiFieldType.NUMBER_IN:
			const numberInSiField = new NumberInSiField();
			numberInSiField.min = dataExtr.nullaNumber('min');
			numberInSiField.max = dataExtr.nullaNumber('max');
			numberInSiField.step = dataExtr.reqNumber('step');
			numberInSiField.arrowStep = dataExtr.nullaNumber('arrowStep');
			numberInSiField.fixed = dataExtr.reqBoolean('fixed');
			numberInSiField.value = dataExtr.nullaNumber('value');
			numberInSiField.prefixAddons = this.createCrumbGroups(dataExtr.reqArray('prefixAddons'));
			numberInSiField.suffixAddons = this.createCrumbGroups(dataExtr.reqArray('suffixAddons'));
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
		const contentFactory = new SiCompFactory();

		return new SiEmbeddedEntry(
				contentFactory.createComp(extr.reqObject('content'),
						SiCompType.BULKY_ENTRY) as BulkyEntrySiComp,
				contentFactory.createComp(extr.reqObject('summaryContent'),
						SiCompType.COMPACT_ENTRY) as CompactEntrySiComp);
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
						this.comp);
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

	private createCrumbGroups(dataArr: Array<any>): SiCrumbGroup[] {
		const crumbGroups: SiCrumbGroup[] = [];
		for (const data of dataArr) {
			crumbGroups.push(this.createCrumbGroup(data));
		}
		return crumbGroups;
	}

	private createCrumbGroup(data: any): SiCrumbGroup {
		const extr = new Extractor(data);
		return {
			crumbs: this.createCrumbs(extr.reqArray('crumbs'))
		};
	}

	private createCrumbs(dataArr: Array<any>) {
		const crumbs: SiCrumb[] = [];
		for (const data of dataArr) {
			crumbs.push(this.createCrumb(data));
		}
		return crumbs;
	}

	private createCrumb(data: any): SiCrumb {
		const extr = new Extractor(data);

		switch (extr.reqString('type')) {
			case SiCrumb.Type.LABEL:
				return SiCrumb.createLabel(extr.reqString('label'));
			case SiCrumb.Type.ICON:
				return SiCrumb.createIcon(extr.reqString('iconClass'));
		}
	}

}
