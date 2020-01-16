import { SiField } from '../model/content/si-field';
import { Extractor, ObjectMissmatchError } from 'src/app/util/mapping/extractor';
import { NumberInSiField } from '../model/content/impl/alphanum/model/number-in-si-field';
import { FileOutSiField } from '../model/content/impl/file/model/file-out-si-field';
import { FileInSiField } from '../model/content/impl/file/model/file-in-si-field';
import { QualifierSelectInSiField } from '../model/content/impl/qualifier/model/qualifier-select-in-si-field';
import { EmbeddedEntryInSiField } from '../model/content/impl/embedded/model/embedded-entry-in-si-field';
import { EmbeddedEntryPanelsInSiField } from '../model/content/impl/embedded/model/embedded-entry-panels-in-si-field';
import { SiMetaFactory } from './si-meta-factory';
import { BooleanInSiField } from '../model/content/impl/boolean/boolean-in-si-field';
import { StringInSiField } from '../model/content/impl/alphanum/model/string-in-si-field';
import { StringOutSiField } from '../model/content/impl/alphanum/model/string-out-si-field';
import { LinkOutSiField } from '../model/content/impl/alphanum/model/link-out-si-field';
import { EnumInSiField } from '../model/content/impl/alphanum/model/enum-in-si-field';
import { SiType } from '../model/meta/si-type';
import { SiProp } from '../model/meta/si-prop';
import { Subject, Observable } from 'rxjs';
import { SplitContextInSiField } from '../model/content/impl/split/model/split-context-in-si-field';
import { SplitContextOutSiField } from '../model/content/impl/split/model/split-context-out-si-field';
import { SplitSiField } from '../model/content/impl/split/model/split-si-field';
import { SplitContextSiField, SplitContent, SplitStyle } from '../model/content/impl/split/model/split-context';
import { Injector } from '@angular/core';
import { SiCompFactory } from './si-comp-factory';
import { SiEntryFactory } from './si-entry-factory';
import { SiDeclaration } from '../model/meta/si-declaration';
import { SiComp } from '../model/comp/si-comp';
import { SiService } from '../manage/si.service';
import { UiFactory } from './ui-factory';

enum SiFieldType {
	STRING_OUT = 'string-out',
	STRING_IN = 'string-in',
	NUMBER_IN = 'number-in',
	BOOLEAN_IN = 'boolean-in',
	FILE_OUT = 'file-out',
	FILE_IN = 'file-in',
	LINK_OUT = 'link-out',
	ENUM_IN = 'enum-in',
	QUALIFIER_SELECT_IN = 'qualifier-select-in',
	EMBEDDED_ENTRY_IN = 'embedded-entry-in',
	EMBEDDED_ENTRY_PANELS_IN = 'embedded-entry-panels-in',
	SPLIT_CONTEXT_IN = 'split-context-in',
	SPLIT_CONTEXT_OUT = 'split-context-out',
	SPLIT_PLACEHOLDER = 'split-placeholder'
}

export class SiFieldFactory {
	constructor(private comp: SiComp, private declaration: SiDeclaration, private type: SiType,
			private injector: Injector) {
	}

	createFieldMap(data: Map<string, any>): Map<string, SiField> {
		const fieldMap$ = new Subject<Map<string, SiField>>();

		const fieldMap = new Map<string, SiField>();
		for (const [propId, fieldData] of data) {
			fieldMap.set(propId, this.createField(this.type.getPropById(propId), fieldData, fieldMap$));
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
			stringInSiField.prefixAddons = SiCompFactory.createCrumbGroups(dataExtr.reqArray('prefixAddons'));
			stringInSiField.suffixAddons = SiCompFactory.createCrumbGroups(dataExtr.reqArray('suffixAddons'));
			return stringInSiField;

		case SiFieldType.NUMBER_IN:
			const numberInSiField = new NumberInSiField();
			numberInSiField.min = dataExtr.nullaNumber('min');
			numberInSiField.max = dataExtr.nullaNumber('max');
			numberInSiField.step = dataExtr.reqNumber('step');
			numberInSiField.arrowStep = dataExtr.nullaNumber('arrowStep');
			numberInSiField.fixed = dataExtr.reqBoolean('fixed');
			numberInSiField.value = dataExtr.nullaNumber('value');
			numberInSiField.prefixAddons = SiCompFactory.createCrumbGroups(dataExtr.reqArray('prefixAddons'));
			numberInSiField.suffixAddons = SiCompFactory.createCrumbGroups(dataExtr.reqArray('suffixAddons'));
			return numberInSiField;

		case SiFieldType.BOOLEAN_IN:
			const booleanInSiField = new BooleanInSiField();
			booleanInSiField.value = dataExtr.reqBoolean('value');

			fieldMap$.subscribe((fieldMap) => {
				this.finalizeBool(booleanInSiField, dataExtr.reqStringArray('onAssociatedFieldIds'),
						dataExtr.reqStringArray('offAssociatedFieldIds'), fieldMap);
			});
			return booleanInSiField;

		case SiFieldType.FILE_OUT:
			return new FileOutSiField(SiCompFactory.buildSiFile(dataExtr.nullaObject('value')));

		case SiFieldType.FILE_IN:
			const fileInSiField = new FileInSiField(dataExtr.reqString('apiUrl'),
					dataExtr.reqObject('apiCallId'), SiCompFactory.buildSiFile(dataExtr.nullaObject('value')));
			fileInSiField.mandatory = dataExtr.reqBoolean('mandatory');
			fileInSiField.maxSize = dataExtr.reqNumber('maxSize');
			fileInSiField.acceptedMimeTypes = dataExtr.reqStringArray('acceptedMimeTypes');
			fileInSiField.acceptedExtensions = dataExtr.reqStringArray('acceptedExtensions');
			return fileInSiField;

		case SiFieldType.LINK_OUT:
			return new LinkOutSiField(UiFactory.createNavPoint(dataExtr.reqObject('navPoint')),
					dataExtr.reqString('label'));

		case SiFieldType.ENUM_IN:
			const enumInSiField = new EnumInSiField(dataExtr.nullaString('value'), dataExtr.reqStringMap('options'));
			enumInSiField.mandatory = dataExtr.reqBoolean('mandatory');
			return enumInSiField;

		case SiFieldType.QUALIFIER_SELECT_IN:
			const qualifierSelectInSiField = new QualifierSelectInSiField(dataExtr.reqString('apiUrl'),
					SiCompFactory.createEntryQualifiers(dataExtr.reqArray('values')));
			qualifierSelectInSiField.min = dataExtr.reqNumber('min');
			qualifierSelectInSiField.max = dataExtr.nullaNumber('max');
			return qualifierSelectInSiField;

		case SiFieldType.EMBEDDED_ENTRY_IN:
			const embeddedEntryInSiField = new EmbeddedEntryInSiField(dataExtr.reqString('apiUrl'),
					new SiCompFactory(this.injector).createEmbeddedEntries(dataExtr.reqArray('values')));
			embeddedEntryInSiField.content.reduced = dataExtr.reqBoolean('reduced');
			embeddedEntryInSiField.content.min = dataExtr.reqNumber('min');
			embeddedEntryInSiField.content.max = dataExtr.nullaNumber('max');
			embeddedEntryInSiField.content.nonNewRemovable = dataExtr.reqBoolean('nonNewRemovable');
			embeddedEntryInSiField.content.sortable = dataExtr.reqBoolean('sortable');
			embeddedEntryInSiField.content.pasteCategory = dataExtr.nullaString('pasteCategory');

			const allowedSiTypeQualifiersData = dataExtr.nullaArray('allowedSiTypeQualifiers');
			if (allowedSiTypeQualifiersData) {
				embeddedEntryInSiField.content.allowedSiTypeQualifiers = SiMetaFactory.createTypeQualifiers(allowedSiTypeQualifiersData);
			} else {
				embeddedEntryInSiField.content.allowedSiTypeQualifiers = null;
			}

			return embeddedEntryInSiField;

		case SiFieldType.EMBEDDED_ENTRY_PANELS_IN:
			return new EmbeddedEntryPanelsInSiField(dataExtr.reqString('apiUrl'),
					new SiCompFactory(this.injector).createPanels(dataExtr.reqArray('panels')));

		case SiFieldType.SPLIT_CONTEXT_IN:
			const splitContextInSiField = new SplitContextInSiField();
			splitContextInSiField.style = this.createSplitStyle(dataExtr.reqObject('style'));
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
			const splitSiField = new SplitSiField(dataExtr.reqString('refFieldId'));

			return splitSiField;
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
				const entryFactory = new SiEntryFactory(this.comp, declaration, this.injector);
				splitContextSiField.putSplitContent(SplitContent.createEntry(key, label, shortLabel,
						entryFactory.createEntry(entryData)));
				continue;
			}

			const apiUrl = extr.nullaString('apiUrl');
			if (apiUrl) {
				splitContextSiField.putSplitContent(SplitContent.createLazy(key, label, shortLabel, {
					apiUrl,
					entryId: extr.nullaString('entryId'),
					bulky: extr.reqBoolean('bulky'),
					readOnly: extr.reqBoolean('readOnly'),
					siComp: this.comp,
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

	private finalizeBool(booleanInSiField: BooleanInSiField, onAssociatedFieldIds: string[],
			offAssociatedFieldIds: string[], fieldMap: Map<string, SiField>) {
		let field: SiField;

		for (const fieldId of onAssociatedFieldIds) {
			if (field = fieldMap.get(fieldId)) {
				booleanInSiField.addOnAssociatedField(field);
			}
		}

		for (const fieldId of offAssociatedFieldIds) {
			if (field = fieldMap.get(fieldId)) {
				booleanInSiField.addOffAssociatedField(field);
			}
		}
	}
}
