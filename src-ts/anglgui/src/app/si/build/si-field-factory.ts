import { SiEntryBuildup } from '../model/content/si-entry-buildup';
import { SiField } from '../model/content/si-field';
import { StringOutSiField } from '../model/content/impl/string/string-out-si-field';
import { Extractor, ObjectMissmatchError } from 'src/app/util/mapping/extractor';
import { StringInSiField } from '../model/content/impl/string/string-in-si-field';
import { NumberInSiField } from '../model/content/impl/alphanum/model/number-in-si-field';
import { FileOutSiField } from '../model/content/impl/file/model/file-out-si-field';
import { FileInSiField } from '../model/content/impl/file/model/file-in-si-field';
import { LinkOutSiField } from '../model/content/impl/string/link-out-si-field';
import { EnumInSiField } from '../model/content/impl/string/enum-in-si-field';
import { QualifierSelectInSiField } from '../model/content/impl/qualifier/model/qualifier-select-in-si-field';
import { EmbeddedEntryInSiField } from '../model/content/impl/embedded/model/embedded-entry-in-si-field';
import { EmbeddedEntryPanelsInSiField } from '../model/content/impl/embedded/model/embedded-entry-panels-in-si-field';
import { SiContentFactory } from './si-content-factory';
import { SiMetaFactory } from './si-meta-factory';
import { BooleanInSiField } from '../model/content/impl/boolean/boolean-in-si-field';

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
	EMBEDDED_ENTRY_PANELS_IN = 'embedded-entry-panels-in'
}

export class SiFieldFactory {
	constructor(private entryBuildup: SiEntryBuildup) {
	}

	createFieldMap(data: Map<string, any>): Map<string, SiField> {
		const fieldMap = new Map<string, SiField>();
		for (const [fieldId, fieldData] of data) {
			fieldMap.set(fieldId, this.createField(fieldData));
		}
		return fieldMap;
	}

	createField(data: any): SiField {
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
			stringInSiField.prefixAddons = SiContentFactory.createCrumbGroups(dataExtr.reqArray('prefixAddons'));
			stringInSiField.suffixAddons = SiContentFactory.createCrumbGroups(dataExtr.reqArray('suffixAddons'));
			return stringInSiField;

		case SiFieldType.NUMBER_IN:
			const numberInSiField = new NumberInSiField();
			numberInSiField.min = dataExtr.nullaNumber('min');
			numberInSiField.max = dataExtr.nullaNumber('max');
			numberInSiField.step = dataExtr.reqNumber('step');
			numberInSiField.arrowStep = dataExtr.nullaNumber('arrowStep');
			numberInSiField.fixed = dataExtr.reqBoolean('fixed');
			numberInSiField.value = dataExtr.nullaNumber('value');
			numberInSiField.prefixAddons = SiContentFactory.createCrumbGroups(dataExtr.reqArray('prefixAddons'));
			numberInSiField.suffixAddons = SiContentFactory.createCrumbGroups(dataExtr.reqArray('suffixAddons'));
			return numberInSiField;

		case SiFieldType.BOOLEAN_IN:
			const booleanInSiField = new BooleanInSiField();
			booleanInSiField.value = dataExtr.reqBoolean('value');
			return booleanInSiField;

		case SiFieldType.FILE_OUT:
			return new FileOutSiField(SiContentFactory.buildSiFile(dataExtr.nullaObject('value')));

		case SiFieldType.FILE_IN:
			const fileInSiField = new FileInSiField(dataExtr.reqString('apiUrl'),
					dataExtr.reqObject('apiCallId'), SiContentFactory.buildSiFile(dataExtr.nullaObject('value')));
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
					SiContentFactory.createEntryQualifiers(dataExtr.reqArray('values')));
			qualifierSelectInSiField.min = dataExtr.reqNumber('min');
			qualifierSelectInSiField.max = dataExtr.nullaNumber('max');
			return qualifierSelectInSiField;

		case SiFieldType.EMBEDDED_ENTRY_IN:
			const embeddedEntryInSiField = new EmbeddedEntryInSiField(dataExtr.reqString('apiUrl'),
					SiContentFactory.createEmbeddedEntries(dataExtr.reqArray('values')));
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
					SiContentFactory.createPanels(dataExtr.reqArray('panels')));

		default:
			throw new ObjectMissmatchError('Invalid si field type: ' + data.type);
		}
	}
}
