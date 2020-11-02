
import { Extractor, ObjectMissmatchError } from 'src/app/util/mapping/extractor';
import { SiEntryError } from 'src/app/si/model/input/si-entry-error';
import { SiFieldError } from 'src/app/si/model/input/si-field-error';
import { Message } from 'src/app/util/i18n/message';
import { SiResult, SiDirective } from '../manage/si-result';
import { UiFactory } from 'src/app/ui/build/ui-factory';
import { SiEntryIdentifier } from '../model/content/si-entry-qualifier';

export class SiResultFactory {

	static createResult(data: any): SiResult {
		const extr = new Extractor(data);

		const result = new SiResult();

		result.directive = extr.nullaString('directive') as SiDirective;
		let navPointData: object|null;
		if (navPointData = extr.nullaObject('navPoint')) {
			result.navPoint = UiFactory.createNavPoint(navPointData);
		}

		const inputErrorData = extr.nullaObject('inputError');

		if (inputErrorData) {
			for (const [ieKey, ieData] of new Extractor(inputErrorData).reqMap('entryErrors')) {
				result.entryErrors.set(ieKey, SiResultFactory.createEntryError(ieData));
			}
		}

		const eventMap = extr.reqMap('eventMap');
		for (const [typeId, idsEvMapData] of eventMap) {
			const idEvMapExtr = new Extractor(idsEvMapData);
			for (const [id, eventType] of idEvMapExtr.reqStringMap('ids')) {
				switch (eventType) {
					case 'added':
						result.modEvent.added.push(new SiEntryIdentifier(typeId, id));
						break;
					case 'changed':
						result.modEvent.updated.push(new SiEntryIdentifier(typeId, id));
						break;
					case 'removed':
						result.modEvent.removed.push(new SiEntryIdentifier(typeId, id));
						break;
					default:
						throw new ObjectMissmatchError('Unknown event type: ' + eventType);
				}
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

}
