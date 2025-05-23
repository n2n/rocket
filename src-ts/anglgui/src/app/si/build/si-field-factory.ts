import { SiField } from '../model/content/si-field';
import { Extractor, ObjectMissmatchError } from 'src/app/util/mapping/extractor';
import { NumberInSiField } from '../model/content/impl/alphanum/model/number-in-si-field';
import { FileOutSiField } from '../model/content/impl/file/model/file-out-si-field';
import { EmbeddedEntriesInSiField } from '../model/content/impl/embedded/model/embedded-entries-in-si-field';
import { SiMetaFactory } from './si-meta-factory';
import { BooleanInSiField } from '../model/content/impl/boolean/boolean-in-si-field';
import { StringInSiField } from '../model/content/impl/alphanum/model/string-in-si-field';
import { StringOutSiField } from '../model/content/impl/alphanum/model/string-out-si-field';
import { SiMask } from '../model/meta/si-mask';
import { SiProp } from '../model/meta/si-prop';
import { Observable, Subject } from 'rxjs';
import { SplitContextInSiField } from '../model/content/impl/split/model/split-context-in-si-field';
import { SplitContextOutSiField } from '../model/content/impl/split/model/split-context-out-si-field';
import { Injector } from '@angular/core';
import { SiDeclaration } from '../model/meta/si-declaration';
import { SiControlBoundary } from '../model/control/si-control-boundary';
import { TranslationService } from 'src/app/util/i18n/translation.service';
import { CrumbOutSiField } from '../model/content/impl/meta/model/crumb-out-si-field';
import { SiModStateService } from '../model/mod/model/si-mod-state.service';
import { EmbeddedEntriesOutSiField } from '../model/content/impl/embedded/model/embedded-entries-out-si-field';
import { EmbeddedEntryPanelsOutSiField } from '../model/content/impl/embedded/model/embedded-entry-panels-out-si-field';
import { EmbeddedEntryPanelsInSiField } from '../model/content/impl/embedded/model/embedded-entry-panels-in-si-field';
import { SplitViewStateService } from '../model/content/impl/split/model/state/split-view-state.service';
import { EnumInSiField } from '../model/content/impl/enum/model/enum-in-si-field';
import { IframeOutSiField } from '../model/content/impl/iframe/model/iframe-out-si-field';
import { DateTimeInSiField } from '../model/content/impl/date/model/datetime-in-si-field';
import { IframeInSiField } from '../model/content/impl/iframe/model/iframe-in-si-field';
import { AppStateService } from 'src/app/app-state.service';
import { StringArrayInSiField } from '../model/content/impl/array/model/string-array-in-si-field';
import { PasswordInSiField } from '../model/content/impl/alphanum/model/password-in-si-field';
import { DateUtils } from 'src/app/util/date/date-utils';
import { Message } from 'src/app/util/i18n/message';
import { SiEssentialsFactory } from './si-field-essentials-factory';
import { FileInSiField } from '../model/content/impl/file/model/file-in-si-field';
import { ObjectQualifiersSelectInSiField } from '../model/content/impl/qualifier/model/object-qualifiers-select-in-si-field';
import { LinkOutSiField } from '../model/content/impl/alphanum/model/link-out-si-field';
import { SiService } from '../manage/si.service';
import { SplitSiField } from '../model/content/impl/split/model/split-si-field';
import { SplitContext, SplitStyle } from '../model/content/impl/split/model/split-context';
import { SplitContent, SplitContentCollection } from '../model/content/impl/split/model/split-content-collection';
import { SiEntryFactory } from './si-entry-factory';
import { SiFrame } from '../model/meta/si-frame';
import { SiGuiFactory } from './si-gui-factory';
import { SiControlFactory } from './si-control-factory';
import { SiEntry } from '../model/content/si-entry';
import { PathPartInSiField } from '../model/content/impl/alphanum/model/path-part-in-si-field';

enum SiFieldType {
	STRING_OUT = 'string-out',
	STRING_IN = 'string-in',
	PATH_PART_IN = 'path-part-in',
	NUMBER_IN = 'number-in',
	BOOLEAN_IN = 'boolean-in',
	FILE_OUT = 'file-out',
	FILE_IN = 'file-in',
	LINK_OUT = 'link-out',
	ENUM_IN = 'enum-in',
	OBJECT_QUALIFIERS_SELECT_IN = 'object-qualifiers-select-in',
	EMBEDDED_ENTRIES_OUT = 'embedded-entries-out',
	EMBEDDED_ENTRIES_IN = 'embedded-entries-in',
	EMBEDDED_ENTRY_PANELS_OUT = 'embedded-entries-panels-out',
	EMBEDDED_ENTRY_PANELS_IN = 'embedded-entries-panels-in',
	SPLIT_CONTEXT_IN = 'split-context-in',
	SPLIT_CONTEXT_OUT = 'split-context-out',
	SPLIT_PLACEHOLDER = 'split-placeholder',
	IFRAME_OUT = 'iframe-out',
	IFRAME_IN = 'iframe-in',
	CRUMB_OUT = 'crumb-out',
	DATETIME_IN = 'datetime-in',
	STRING_ARRAY_IN = 'string-array-in',
	PASSWORD_IN = 'password-in',
}

export class SiFieldFactory {
	constructor(private controlBoundary: SiControlBoundary, private mask: SiMask, private entry: SiEntry,
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
			const stringOutSiField = new StringOutSiField(dataExtr.nullaString('value'));
			stringOutSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));
			return stringOutSiField;

		case SiFieldType.STRING_IN:
			const stringInSiField = new StringInSiField(prop.label, dataExtr.nullaString('value'), dataExtr.reqBoolean('multiline'));
			stringInSiField.minlength = dataExtr.nullaNumber('minlength');
			stringInSiField.maxlength = dataExtr.nullaNumber('maxlength');
			stringInSiField.mandatory = dataExtr.reqBoolean('mandatory');
			stringInSiField.prefixAddons = SiEssentialsFactory.createCrumbGroups(dataExtr.reqArray('prefixAddons'));
			stringInSiField.suffixAddons = SiEssentialsFactory.createCrumbGroups(dataExtr.reqArray('suffixAddons'));
			stringInSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));

			return stringInSiField;

		case SiFieldType.PATH_PART_IN:
			const pathPartInSiField = new PathPartInSiField(prop.label, dataExtr.nullaString('value'));
			pathPartInSiField.minlength = dataExtr.nullaNumber('minlength');
			pathPartInSiField.maxlength = dataExtr.nullaNumber('maxlength');
			pathPartInSiField.mandatory = dataExtr.reqBoolean('mandatory');
			pathPartInSiField.prefixAddons = SiEssentialsFactory.createCrumbGroups(dataExtr.reqArray('prefixAddons'));
			pathPartInSiField.suffixAddons = SiEssentialsFactory.createCrumbGroups(dataExtr.reqArray('suffixAddons'));
			pathPartInSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));

			this.resolvePathPartDependencies(pathPartInSiField, dataExtr.nullaString('basedOnPropName'),
					fieldMap$);

			return pathPartInSiField;

		case SiFieldType.NUMBER_IN:
			const numberInSiField = new NumberInSiField(prop.label, this.injector.get(AppStateService).localeId);
			numberInSiField.min = dataExtr.nullaNumber('min');
			numberInSiField.max = dataExtr.nullaNumber('max');
			numberInSiField.step = dataExtr.reqNumber('step');
			numberInSiField.arrowStep = dataExtr.nullaNumber('arrowStep');
			numberInSiField.fixed = dataExtr.reqBoolean('fixed');
			numberInSiField.mandatory = dataExtr.reqBoolean('mandatory');
			numberInSiField.value = dataExtr.nullaNumber('value');
			numberInSiField.prefixAddons = SiEssentialsFactory.createCrumbGroups(dataExtr.reqArray('prefixAddons'));
			numberInSiField.suffixAddons = SiEssentialsFactory.createCrumbGroups(dataExtr.reqArray('suffixAddons'));
			numberInSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));
			return numberInSiField;

		case SiFieldType.BOOLEAN_IN:
			const booleanInSiField = new BooleanInSiField();
			booleanInSiField.value = dataExtr.reqBoolean('value');
			booleanInSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));

			fieldMap$.subscribe((fieldMap: Map<string, SiField>) => {
				this.finalizeBool(booleanInSiField, dataExtr.reqStringArray('onAssociatedPropIds'),
						dataExtr.reqStringArray('offAssociatedPropIds'), fieldMap);
			});
			return booleanInSiField;

		case SiFieldType.FILE_OUT:
			const fileOutSiField = new FileOutSiField(SiEssentialsFactory.buildSiFile(dataExtr.nullaObject('value')));
			fileOutSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));
			return fileOutSiField;

		case SiFieldType.FILE_IN:
			const apiUrl = this.controlBoundary.getBoundApiUrl();
			if (!apiUrl) {
				throw new Error('API Url not present for FileInSiField.');
			}

			const fileInSiField = new FileInSiField(apiUrl, this.mask.qualifier.maskIdentifier.id,
					this.entry.entryQualifier.identifier.id, prop.name,
					SiEssentialsFactory.buildSiFile(dataExtr.nullaObject('value')));
			fileInSiField.mandatory = dataExtr.reqBoolean('mandatory');
			fileInSiField.maxSize = dataExtr.reqNumber('maxSize');
			fileInSiField.acceptedMimeTypes = dataExtr.reqStringArray('acceptedMimeTypes');
			fileInSiField.acceptedExtensions = dataExtr.reqStringArray('acceptedExtensions');
			fileInSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));
			return fileInSiField;

		case SiFieldType.LINK_OUT:
			const linkOutSiField = new LinkOutSiField(SiEssentialsFactory.createNavPoint(dataExtr.reqObject('navPoint')),
					dataExtr.reqString('label'), this.injector);
			linkOutSiField.lytebox = dataExtr.reqBoolean('lytebox');
			linkOutSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));
			return linkOutSiField;

		case SiFieldType.ENUM_IN:
			const enumInSiField = new EnumInSiField(prop.label, dataExtr.nullaString('value'), dataExtr.reqStringMap('options'));
			enumInSiField.mandatory = dataExtr.reqBoolean('mandatory');
			enumInSiField.emptyLabel = dataExtr.nullaString('emptyLabel');
			enumInSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));

			fieldMap$.subscribe((fieldMap: Map<string, SiField>) => {
				this.finalizeEnum(enumInSiField, dataExtr.reqMap('associatedPropIdsMap'), fieldMap);
			});

			return enumInSiField;

		case SiFieldType.OBJECT_QUALIFIERS_SELECT_IN:
			const objectQualifiersSelectInSiField = new ObjectQualifiersSelectInSiField(
					SiMetaFactory.createFrame(dataExtr.reqObject('frame')), dataExtr.reqString('maskId'), prop.label,
					SiMetaFactory.buildObjectQualifiers(dataExtr.reqArray('values')));
			objectQualifiersSelectInSiField.min = dataExtr.reqNumber('min');
			objectQualifiersSelectInSiField.max = dataExtr.nullaNumber('max');
			objectQualifiersSelectInSiField.pickables = SiMetaFactory.buildObjectQualifiers(dataExtr.nullaArray('pickables'));
			objectQualifiersSelectInSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));
			return objectQualifiersSelectInSiField;

		case SiFieldType.EMBEDDED_ENTRIES_OUT:
			const embeddedEntryOutSiField = new EmbeddedEntriesOutSiField(prop.label, this.injector.get(SiService),
					this.injector.get(SiModStateService), SiMetaFactory.createFrame(dataExtr.reqObject('frame')),
					this.injector.get(TranslationService),
					new SiGuiFactory(this.injector).createEmbeddedEntries(dataExtr.reqArray('values')));
			embeddedEntryOutSiField.config.reduced = dataExtr.reqBoolean('reduced');
			embeddedEntryOutSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));

			return embeddedEntryOutSiField;

		case SiFieldType.EMBEDDED_ENTRIES_IN:
			const embeddedEntryInSiField = new EmbeddedEntriesInSiField(prop.label, this.injector.get(SiService),
					this.injector.get(SiModStateService), SiMetaFactory.createFrame(dataExtr.reqObject('frame')),
					this.injector.get(TranslationService),
					new SiGuiFactory(this.injector).createEmbeddedEntries(dataExtr.reqArray('values')));
			embeddedEntryInSiField.config.bulkyMaskId = dataExtr.reqString('bulkyMaskId');
			embeddedEntryInSiField.config.summaryMaskId = dataExtr.nullaString('summaryMaskId');
			embeddedEntryInSiField.config.min = dataExtr.reqNumber('min');
			embeddedEntryInSiField.config.max = dataExtr.nullaNumber('max');
			embeddedEntryInSiField.config.nonNewRemovable = dataExtr.reqBoolean('nonNewRemovable');
			embeddedEntryInSiField.config.sortable = dataExtr.reqBoolean('sortable');
			embeddedEntryInSiField.config.allowedMaskIds = dataExtr.nullaArray('allowedSiTypeIds');
			embeddedEntryInSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));

			return embeddedEntryInSiField;

		case SiFieldType.EMBEDDED_ENTRY_PANELS_OUT:
			const embeddedEntryPanelsOutSiField = new EmbeddedEntryPanelsOutSiField(this.injector.get(SiService), this.injector.get(SiModStateService),
					SiMetaFactory.createFrame(dataExtr.reqObject('frame')), this.injector.get(TranslationService),
					new SiGuiFactory(this.injector).createPanels(dataExtr.reqArray('panels')));
			embeddedEntryPanelsOutSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));
			return embeddedEntryPanelsOutSiField;

		case SiFieldType.EMBEDDED_ENTRY_PANELS_IN:
			const embeddedEntryPanelsInSiField = new EmbeddedEntryPanelsInSiField(this.injector.get(SiService), this.injector.get(SiModStateService),
					SiMetaFactory.createFrame(dataExtr.reqObject('frame')), this.injector.get(TranslationService),
					new SiGuiFactory(this.injector).createPanels(dataExtr.reqArray('panels')));
			embeddedEntryPanelsInSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));
			return embeddedEntryPanelsInSiField;

		case SiFieldType.SPLIT_CONTEXT_IN:
			const splitContextInSiField = new SplitContextInSiField();
			splitContextInSiField.style = this.createSplitStyle(dataExtr.reqObject('style'));
			splitContextInSiField.managerStyle = this.createSplitStyle(dataExtr.reqObject('managerStyle'));
			splitContextInSiField.activeKeys = dataExtr.reqStringArray('activeKeys');
			splitContextInSiField.mandatoryKeys = dataExtr.reqStringArray('mandatoryKeys');
			splitContextInSiField.min = dataExtr.reqNumber('min');
			this.compileSplitContents(splitContextInSiField.collection,
					SiMetaFactory.createDeclaration(dataExtr.reqObject('declaration'), new SiControlFactory(this.controlBoundary, this.injector)),
					SiMetaFactory.buildFrame(dataExtr.nullaObject('frame')),
					dataExtr.reqMap('splitContents'));
			this.completeSplitContextSiField(splitContextInSiField, prop.dependantPropIds, fieldMap$);
			splitContextInSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));
			return splitContextInSiField;

		case SiFieldType.SPLIT_CONTEXT_OUT:
			const splitContextOutSiField = new SplitContextOutSiField();
			splitContextOutSiField.style = this.createSplitStyle(dataExtr.reqObject('style'));
			this.compileSplitContents(splitContextOutSiField.collection,
					SiMetaFactory.createDeclaration(dataExtr.reqObject('declaration'), new SiControlFactory(this.controlBoundary, this.injector)),
					SiMetaFactory.buildFrame(dataExtr.nullaObject('frame')),
					dataExtr.reqMap('splitContents'));
			this.completeSplitContextSiField(splitContextOutSiField, prop.dependantPropIds, fieldMap$);
			splitContextOutSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));
			return splitContextOutSiField;

		case SiFieldType.SPLIT_PLACEHOLDER:
			const splitSiField = new SplitSiField(dataExtr.reqString('refPropId'), this.injector.get(SplitViewStateService),
					this.injector.get(TranslationService));
			splitSiField.copyStyle = this.createSplitStyle(dataExtr.reqObject('copyStyle'));
			splitSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));
			return splitSiField;

		case SiFieldType.CRUMB_OUT:
			const crumbOutSiField = new CrumbOutSiField(SiEssentialsFactory.createCrumbGroups(
					dataExtr.reqArray('crumbGroups')));
			crumbOutSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));
			return crumbOutSiField;

		case SiFieldType.IFRAME_OUT:
			const iframeOutSiField = new IframeOutSiField(dataExtr.nullaString('url'),
					dataExtr.nullaString('srcDoc'));
			iframeOutSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));
			return iframeOutSiField;

		case SiFieldType.IFRAME_IN:
			// const formData = new Map<string, string>(Object.entries((dataExtr.reqObject('params') as any).formData));
			const formData = dataExtr.reqStringMap('params', true);

			const iframeInSiField = new IframeInSiField(dataExtr.nullaString('url'), dataExtr.nullaString('srcDoc'), formData);
			iframeInSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));
			return iframeInSiField;

		case SiFieldType.DATETIME_IN:
			const dateTimeInSiField = new DateTimeInSiField(prop.label, null);
			const valueStr = dataExtr.nullaString('value');
			if (valueStr) {
				dateTimeInSiField.value = DateUtils.sqlToDate(valueStr);
			}

			dateTimeInSiField.mandatory = dataExtr.reqBoolean('mandatory');
			dateTimeInSiField.dateChoosable = dataExtr.reqBoolean('dateChoosable');
			dateTimeInSiField.timeChoosable = dataExtr.reqBoolean('timeChoosable');
			dateTimeInSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));
			return dateTimeInSiField;

		case SiFieldType.STRING_ARRAY_IN:
			const stringArrayInSiField = new StringArrayInSiField(prop.label, dataExtr.reqArray('values'));
			stringArrayInSiField.min = dataExtr.reqNumber('min');
			stringArrayInSiField.max = dataExtr.nullaNumber('max');
			stringArrayInSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));
			return stringArrayInSiField;

		case SiFieldType.PASSWORD_IN:
			const passwordInSiField = new PasswordInSiField(prop.label);
			passwordInSiField.minlength = dataExtr.nullaNumber('minlength');
			passwordInSiField.maxlength = dataExtr.nullaNumber('maxlength');
			passwordInSiField.mandatory = dataExtr.reqBoolean('mandatory');
			passwordInSiField.passwordSet = dataExtr.reqBoolean('passwordSet');
			passwordInSiField.handleError(Message.createTexts(dataExtr.reqStringArray('messages')));
			return passwordInSiField;

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

	private compileSplitContents(splitContextSiField: SplitContentCollection, declaration: SiDeclaration, frame: SiFrame|null, dataMap: Map<string, any>): void {
		for (const [key, data] of dataMap) {
			const extr = new Extractor(data);

			const label = extr.reqString('label');
			const shortLabel = extr.reqString('shortLabel');

			const entryData = extr.nullaObject('valueBoundary');
			if (entryData) {
				const entryFactory = new SiEntryFactory(declaration, frame?.apiUrl ?? null, this.injector);
				splitContextSiField.putSplitContent(SplitContent.createEntry(key, label, shortLabel,
						entryFactory.createValueBoundary(entryData)));
				continue;
			}

			const apiGetUrl = extr.nullaString('apiGetUrl');
			if (apiGetUrl) {
				splitContextSiField.putSplitContent(SplitContent.createLazy(key, label, shortLabel, {
					apiGetUrl,
					entryId: extr.nullaString('entryId'),
					propIds: extr.nullaStringArray('propIds'),
					maskId: extr.reqString('maskId'),
					siControlBoundary: this.controlBoundary,
					siService: this.injector.get(SiService)
				}));
				continue;
			}

			splitContextSiField.putSplitContent(SplitContent.createUnavaialble(key, label, shortLabel));
		}
	}

	private completeSplitContextSiField(splitContext: SplitContext, dependantPropIds: Array<string>,
			fieldMap$: Observable<Map<string, SiField>>): void {
		fieldMap$.subscribe((fieldMap: { get: (arg0: string) => any; }) => {

			for (const dependantPropId of dependantPropIds) {
				const siField = fieldMap.get(dependantPropId);
				if (siField instanceof SplitSiField) {
					siField.splitContext = splitContext;
				}
			}
		});
	}

	private finalizeBool(booleanInSiField: BooleanInSiField, onAssociatedPropIds: string[],
			offAssociatedPropIds: string[], fieldMap: Map<string, SiField>): void {
		let field: SiField|undefined;

		for (const propId of onAssociatedPropIds) {
			if (undefined !== (field = fieldMap.get(propId))) {
				booleanInSiField.addOnAssociatedField(field);
			}
		}

		for (const propId of offAssociatedPropIds) {
			if (undefined !== (field = fieldMap.get(propId))) {
				booleanInSiField.addOffAssociatedField(field);
			}
		}
	}

	private finalizeEnum(enumInSiField: EnumInSiField, associatedPropIdsMap: Map<string, string[]>,
			fieldMap: Map<string, SiField>): void {
		for (const [value, propIds] of associatedPropIdsMap) {
			enumInSiField.setAssociatedFields(value, propIds
					.map(propId => fieldMap.get(propId)!)
					.filter(field => !!field));
		}
	}

	private resolvePathPartDependencies(pathPartInSiField: PathPartInSiField, basedOnPropName: string|null,
		   fieldMapObservable: Observable<Map<string, SiField>>): void {
		if (!basedOnPropName) {
			return;
		}

		fieldMapObservable.subscribe((fieldMap) => {
			pathPartInSiField.basedOnField = fieldMap.get(basedOnPropName) ?? null;
		});
	}
}
