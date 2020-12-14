import { SiField } from '../model/content/si-field';
import { Extractor, ObjectMissmatchError } from 'src/app/util/mapping/extractor';
import { NumberInSiField } from '../model/content/impl/alphanum/model/number-in-si-field';
import { FileOutSiField } from '../model/content/impl/file/model/file-out-si-field';
import { FileInSiField } from '../model/content/impl/file/model/file-in-si-field';
import { QualifierSelectInSiField } from '../model/content/impl/qualifier/model/qualifier-select-in-si-field';
import { EmbeddedEntriesInSiField } from '../model/content/impl/embedded/model/embedded-entries-in-si-field';
import { SiMetaFactory } from './si-meta-factory';
import { BooleanInSiField } from '../model/content/impl/boolean/boolean-in-si-field';
import { StringInSiField } from '../model/content/impl/alphanum/model/string-in-si-field';
import { StringOutSiField } from '../model/content/impl/alphanum/model/string-out-si-field';
import { LinkOutSiField } from '../model/content/impl/alphanum/model/link-out-si-field';
import { EnumInSiField } from '../model/content/impl/alphanum/model/enum-in-si-field';
import { SiMask } from '../model/meta/si-type';
import { SiProp } from '../model/meta/si-prop';
import { Subject, Observable } from 'rxjs';
import { SplitContextInSiField } from '../model/content/impl/split/model/split-context-in-si-field';
import { SplitContextOutSiField } from '../model/content/impl/split/model/split-context-out-si-field';
import { SplitSiField } from '../model/content/impl/split/model/split-si-field';
import { SplitContextSiField, SplitContent, SplitStyle } from '../model/content/impl/split/model/split-context-si-field';
import { Injector } from '@angular/core';
import { SiGuiFactory } from './si-gui-factory';
import { SiEntryFactory } from './si-entry-factory';
import { SiDeclaration } from '../model/meta/si-declaration';
import { SiService } from '../manage/si.service';
import { SiControlBoundry } from '../model/control/si-control-bountry';
import { TranslationService } from 'src/app/util/i18n/translation.service';
import { CkeInSiField } from '../model/content/impl/alphanum/model/cke-in-si-field';
import { CrumbOutSiField } from '../model/content/impl/meta/model/crumb-out-si-field';
import { SiControlFactory } from './si-control-factory';
import { SiModStateService } from '../model/mod/model/si-mod-state.service';
import { EmbeddedEntriesOutSiField } from '../model/content/impl/embedded/model/embedded-entries-out-si-field';
import { EmbeddedEntryPanelsOutSiField } from '../model/content/impl/embedded/model/embedded-entry-panels-out-si-field';
import { EmbeddedEntryPanelsInSiField } from '../model/content/impl/embedded/model/embedded-entry-panels-in-si-field';

enum SiFieldType {
	STRING_OUT = 'string-out',
	STRING_IN = 'string-in',
	NUMBER_IN = 'number-in',
	BOOLEAN_IN = 'boolean-in',
	CKE_IN = 'cke-in',
	FILE_OUT = 'file-out',
	FILE_IN = 'file-in',
	LINK_OUT = 'link-out',
	ENUM_IN = 'enum-in',
	QUALIFIER_SELECT_IN = 'qualifier-select-in',
	EMBEDDED_ENTRIES_OUT = 'embedded-entries-out',
	EMBEDDED_ENTRIES_IN = 'embedded-entries-in',
	EMBEDDED_ENTRY_PANELS_OUT = 'embedded-entries-panels-out',
	EMBEDDED_ENTRY_PANELS_IN = 'embedded-entries-panels-in',
	SPLIT_CONTEXT_IN = 'split-context-in',
	SPLIT_CONTEXT_OUT = 'split-context-out',
	SPLIT_PLACEHOLDER = 'split-placeholder',
	IFRAME = 'iframe',
	CRUMB_OUT = 'crumb-out'
}

export class SiFieldFactory {
	constructor(private controlBoundry: SiControlBoundry, private declaration: SiDeclaration, private mask: SiMask,
			private injector: Injector) {
	}

	createFieldMap(data: Map<string, any>): Map<string, SiField> {
		const fieldMap$ = new Subject<Map<string, SiField>>();

		const fieldMap = new Map<string, SiField>();
		for (const [propId, fieldData] of data) {
			fieldMap.set(propId, this.createField(this.mask.getPropById(propId), fieldData, fieldMap$));
		}

		fieldMap$.next(fieldMap);
		fieldMap$.complete();
		return fieldMap;
	}

	private createField(prop: SiProp, data: any, fieldMap$: Observable<Map<string, SiField>>): SiField {
		const extr = new Extractor(data);
		const dataExtr = extr.reqExtractor('data');

		switch (extr.reqString('type')) {

		case SiFieldType.STRING_OUT:
			return new StringOutSiField(dataExtr.nullaString('value'));

		case SiFieldType.STRING_IN:
			const stringInSiField = new StringInSiField(prop.label, dataExtr.nullaString('value'), dataExtr.reqBoolean('multiline'));
			stringInSiField.minlength = dataExtr.nullaNumber('minlength');
			stringInSiField.maxlength = dataExtr.nullaNumber('maxlength');
			stringInSiField.mandatory = dataExtr.reqBoolean('mandatory');
			stringInSiField.prefixAddons = SiGuiFactory.createCrumbGroups(dataExtr.reqArray('prefixAddons'));
			stringInSiField.suffixAddons = SiGuiFactory.createCrumbGroups(dataExtr.reqArray('suffixAddons'));
			return stringInSiField;

		case SiFieldType.NUMBER_IN:
			const numberInSiField = new NumberInSiField(prop.label);
			numberInSiField.min = dataExtr.nullaNumber('min');
			numberInSiField.max = dataExtr.nullaNumber('max');
			numberInSiField.step = dataExtr.reqNumber('step');
			numberInSiField.arrowStep = dataExtr.nullaNumber('arrowStep');
			numberInSiField.fixed = dataExtr.reqBoolean('fixed');
			numberInSiField.value = dataExtr.nullaNumber('value');
			numberInSiField.prefixAddons = SiGuiFactory.createCrumbGroups(dataExtr.reqArray('prefixAddons'));
			numberInSiField.suffixAddons = SiGuiFactory.createCrumbGroups(dataExtr.reqArray('suffixAddons'));
			return numberInSiField;

		case SiFieldType.BOOLEAN_IN:
			const booleanInSiField = new BooleanInSiField();
			booleanInSiField.value = dataExtr.reqBoolean('value');

			fieldMap$.subscribe((fieldMap) => {
				this.finalizeBool(booleanInSiField, dataExtr.reqStringArray('onAssociatedPropIds'),
						dataExtr.reqStringArray('offAssociatedPropIds'), fieldMap);
			});
			return booleanInSiField;

		case SiFieldType.CKE_IN:
			const ckeInSiField = new CkeInSiField(prop.label, dataExtr.nullaString('value'));
			ckeInSiField.minlength = dataExtr.nullaNumber('minlength');
			ckeInSiField.maxlength = dataExtr.nullaNumber('maxlength');
			ckeInSiField.mandatory = dataExtr.reqBoolean('mandatory');
			return ckeInSiField;

		case SiFieldType.FILE_OUT:
			return new FileOutSiField(SiGuiFactory.buildSiFile(dataExtr.nullaObject('value')));

		case SiFieldType.FILE_IN:
			const fileInSiField = new FileInSiField(dataExtr.reqString('apiUrl'),
					dataExtr.reqObject('apiCallId'), SiGuiFactory.buildSiFile(dataExtr.nullaObject('value')));
			fileInSiField.mandatory = dataExtr.reqBoolean('mandatory');
			fileInSiField.maxSize = dataExtr.reqNumber('maxSize');
			fileInSiField.acceptedMimeTypes = dataExtr.reqStringArray('acceptedMimeTypes');
			fileInSiField.acceptedExtensions = dataExtr.reqStringArray('acceptedExtensions');
			return fileInSiField;

		case SiFieldType.LINK_OUT:
			return new LinkOutSiField(SiControlFactory.createNavPoint(dataExtr.reqObject('navPoint')),
					dataExtr.reqString('label'), this.injector);

		case SiFieldType.ENUM_IN:
			const enumInSiField = new EnumInSiField(dataExtr.nullaString('value'), dataExtr.reqStringMap('options'));
			enumInSiField.mandatory = dataExtr.reqBoolean('mandatory');
			return enumInSiField;

		case SiFieldType.QUALIFIER_SELECT_IN:
			const qualifierSelectInSiField = new QualifierSelectInSiField(
					SiMetaFactory.createFrame(dataExtr.reqObject('frame')), prop.label,
					SiGuiFactory.buildEntryQualifiers(dataExtr.reqArray('values')));
			qualifierSelectInSiField.min = dataExtr.reqNumber('min');
			qualifierSelectInSiField.max = dataExtr.nullaNumber('max');
			qualifierSelectInSiField.pickables = SiGuiFactory.buildEntryQualifiers(dataExtr.nullaArray('pickables'));
			return qualifierSelectInSiField;

		case SiFieldType.EMBEDDED_ENTRIES_OUT:
			const embeddedEntryOutSiField = new EmbeddedEntriesOutSiField(this.injector.get(SiService),
					this.injector.get(SiModStateService), SiMetaFactory.createFrame(dataExtr.reqObject('frame')),
					this.injector.get(TranslationService),
					new SiGuiFactory(this.injector).createEmbeddedEntries(dataExtr.reqArray('values')));
			embeddedEntryOutSiField.config.reduced = dataExtr.reqBoolean('reduced');

			return embeddedEntryOutSiField;

		case SiFieldType.EMBEDDED_ENTRIES_IN:
			const embeddedEntryInSiField = new EmbeddedEntriesInSiField(prop.label, this.injector.get(SiService),
					this.injector.get(SiModStateService), SiMetaFactory.createFrame(dataExtr.reqObject('frame')),
					this.injector.get(TranslationService),
					new SiGuiFactory(this.injector).createEmbeddedEntries(dataExtr.reqArray('values')));
			embeddedEntryInSiField.config.reduced = dataExtr.reqBoolean('reduced');
			embeddedEntryInSiField.config.min = dataExtr.reqNumber('min');
			embeddedEntryInSiField.config.max = dataExtr.nullaNumber('max');
			embeddedEntryInSiField.config.nonNewRemovable = dataExtr.reqBoolean('nonNewRemovable');
			embeddedEntryInSiField.config.sortable = dataExtr.reqBoolean('sortable');
			embeddedEntryInSiField.config.allowedTypeIds = dataExtr.nullaArray('allowedSiTypeIds');

			return embeddedEntryInSiField;

		case SiFieldType.EMBEDDED_ENTRY_PANELS_OUT:
			return new EmbeddedEntryPanelsOutSiField(this.injector.get(SiService), this.injector.get(SiModStateService),
					SiMetaFactory.createFrame(dataExtr.reqObject('frame')), this.injector.get(TranslationService),
					new SiGuiFactory(this.injector).createPanels(dataExtr.reqArray('panels')));

		case SiFieldType.EMBEDDED_ENTRY_PANELS_IN:
			return new EmbeddedEntryPanelsInSiField(this.injector.get(SiService), this.injector.get(SiModStateService),
					SiMetaFactory.createFrame(dataExtr.reqObject('frame')), this.injector.get(TranslationService),
					new SiGuiFactory(this.injector).createPanels(dataExtr.reqArray('panels')));

		case SiFieldType.SPLIT_CONTEXT_IN:
			const splitContextInSiField = new SplitContextInSiField();
			splitContextInSiField.style = this.createSplitStyle(dataExtr.reqObject('style'));
			splitContextInSiField.managerStyle = this.createSplitStyle(dataExtr.reqObject('managerStyle'));
			splitContextInSiField.activeKeys = dataExtr.reqStringArray('activeKeys');
			splitContextInSiField.mandatoryKeys = dataExtr.reqStringArray('mandatoryKeys');
			splitContextInSiField.min = dataExtr.reqNumber('min');
			this.compileSplitContents(splitContextInSiField,
					SiMetaFactory.createDeclaration(dataExtr.reqObject('declaration')),
					dataExtr.reqMap('splitContents'));
			this.completeSplitContextSiField(splitContextInSiField, prop.dependantPropIds, fieldMap$);
			return splitContextInSiField;

		case SiFieldType.SPLIT_CONTEXT_OUT:
			const splitContextOutSiField = new SplitContextOutSiField();
			splitContextOutSiField.style = this.createSplitStyle(dataExtr.reqObject('style'));
			this.compileSplitContents(splitContextOutSiField,
					SiMetaFactory.createDeclaration(dataExtr.reqObject('declaration')),
					dataExtr.reqMap('splitContents'));
			this.completeSplitContextSiField(splitContextOutSiField, prop.dependantPropIds, fieldMap$);
			return splitContextOutSiField;

		case SiFieldType.SPLIT_PLACEHOLDER:
			const splitSiField = new SplitSiField(dataExtr.reqString('refPropId'));
			splitSiField.copyStyle = this.createSplitStyle(dataExtr.reqObject('copyStyle'));
			return splitSiField;

		case SiFieldType.CRUMB_OUT:
			return new CrumbOutSiField(SiGuiFactory.createCrumbGroups(dataExtr.reqArray('crumbGroups')));

		default:
			throw new ObjectMissmatchError('Invalid si field type: ' + data.type);
		}
	}

	private createSplitStyle(data: any): SplitStyle {
		const extr = new Extractor(data);

		return {
			iconClass: extr.nullaString('iconClass'),
			tooltip: extr.nullaString('tooltip')
		};
	}

	private compileSplitContents(splitContextSiField: SplitContextSiField, declaration: SiDeclaration, dataMap: Map<string, any>) {
		for (const [key, data] of dataMap) {
			const extr = new Extractor(data);

			const label = extr.reqString('label');
			const shortLabel = extr.reqString('shortLabel');

			const entryData = extr.nullaObject('entry');
			if (entryData) {
				const entryFactory = new SiEntryFactory(declaration, this.injector);
				splitContextSiField.putSplitContent(SplitContent.createEntry(key, label, shortLabel,
						entryFactory.createEntry(entryData)));
				continue;
			}

			const apiUrl = extr.nullaString('apiUrl');
			if (apiUrl) {
				splitContextSiField.putSplitContent(SplitContent.createLazy(key, label, shortLabel, {
					apiUrl,
					entryId: extr.nullaString('entryId'),
					propIds: extr.nullaStringArray('propIds'),
					bulky: extr.reqBoolean('bulky'),
					readOnly: extr.reqBoolean('readOnly'),
					siControlBoundy: this.controlBoundry,
					siService: this.injector.get(SiService)
				}));
				continue;
			}

			splitContextSiField.putSplitContent(SplitContent.createUnavaialble(key, label, shortLabel));
		}
	}

	private completeSplitContextSiField(splitContextSiField: SplitContextSiField, dependantPropIds: Array<string>,
			fieldMap$: Observable<Map<string, SiField>>) {
		fieldMap$.subscribe((fieldMap) => {

			for (const dependantPropId of dependantPropIds) {
				const siField = fieldMap.get(dependantPropId);
				if (siField instanceof SplitSiField) {
					siField.splitContext = splitContextSiField;
				}
			}
		});
	}

	private finalizeBool(booleanInSiField: BooleanInSiField, onAssociatedPropIds: string[],
			offAssociatedPropIds: string[], fieldMap: Map<string, SiField>) {
		let field: SiField;

		for (const propId of onAssociatedPropIds) {
			if (field = fieldMap.get(propId)) {
				booleanInSiField.addOnAssociatedField(field);
			}
		}

		for (const propId of offAssociatedPropIds) {
			if (field = fieldMap.get(propId)) {
				booleanInSiField.addOffAssociatedField(field);
			}
		}
	}
}
