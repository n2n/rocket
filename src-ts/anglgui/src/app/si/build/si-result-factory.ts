
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiEntryError } from 'src/app/si/model/input/si-entry-error';
import { SiFieldError } from 'src/app/si/model/input/si-field-error';
import { Message } from 'src/app/util/i18n/message';
import { SiResult } from '../manage/si-result';
import { SiDeclaration } from '../model/meta/si-declaration';

export class SiResultFactory {

	static createResult(data: any): SiResult {
		const extr = new Extractor(data);

		const result = new SiResult();

		result.directive = extr.nullaString('directive');
		result.ref = extr.nullaString('ref');
		result.href = extr.nullaString('href');

		const inputErrorData = extr.nullaObject('inputError');

		if (inputErrorData) {
			for (const [ieKey, ieData] of new Extractor(inputErrorData).reqMap('entryErrors')) {
				result.entryErrors.set(ieKey, SiResultFactory.createEntryError(ieData));
			}
		}

		return result;
	}

	static createEntryError(data: any): SiEntryError {
		const extr = new Extractor(data);

		const entryError = new SiEntryError(/*extr.reqStringArray('messages')*/);

		for (const [key, fieldData] of extr.reqMap('fieldErrors')) {
			entryError.fieldErrors.set(key, SiResultFactory.createFieldError(fieldData));
		}

		return entryError;
	}

	private static createFieldError(data: any): SiFieldError {
		const extr = new Extractor(data);

		const fieldError = new SiFieldError(Message.createTexts(extr.reqStringArray('messages')));

		for (const [key, entryData] of extr.reqMap('subEntryErrors')) {
			fieldError.subEntryErrors.set(key, SiResultFactory.createEntryError(entryData));
		}

		return fieldError;
	}

	
	static buildSiFile(data: any): SiFile|null {
		if (data === null) {
			return null;
		}

		const extr = new Extractor(data);

		const imageDimensions: SiImageDimension[] = [];
		for (const idData of extr.reqArray('imageDimensions')) {
			imageDimensions.push(SiResultFactory.createSiImageDimension(idData));
		}

		return {
			id: extr.reqObject('id'),
			name: extr.reqString('name'),
			url: extr.nullaString('url'),
			thumbUrl: extr.nullaString('thumbUrl'),
			imageDimensions
		};
	}

	static createSiImageDimension(data: any): SiImageDimension {
		const extr = new Extractor(data);

		return {
			id: extr.reqString('id'),
			name: extr.reqString('name'),
			width: extr.reqNumber('width'),
			height: extr.reqNumber('height')
		};
	}
}
