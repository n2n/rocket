
import { Extractor, ObjectMissmatchError } from 'src/app/util/mapping/extractor';
import { SiEntryError } from 'src/app/si/model/input/si-entry-error';
import { SiFieldError } from 'src/app/si/model/input/si-field-error';
import { Message, MessageSeverity } from 'src/app/util/i18n/message';
import { SiControlResult, SiDirective } from '../manage/si-control-result';
import { SiEntryIdentifier } from '../model/content/si-entry-qualifier';
import { SiModEvent } from '../model/mod/model/si-mod-state.service';
import { SiControlFactory } from './si-control-factory';
import { SiEntryFactory } from './si-entry-factory';
import { Injector } from '@angular/core';

export class SiResultFactory {

	cosntructor(private injector: Injector) {

	}

	static createControlResult(data: any): SiControlResult {
		const extr = new Extractor(data);

		const result = new SiControlResult();

		result.directive = extr.nullaString('directive') as SiDirective;
		let navPointData: object|null;
		if (navPointData = extr.nullaObject('navPoint')) {
			result.navPoint = SiControlFactory.createNavPoint(navPointData);
		}

		const inputErrorData = extr.nullaObject('inputError');

		if (inputErrorData) {
			new SiEntryFactory(this.injector);
			for (const [ieKey, ieData] of new Extractor(inputErrorData).reqMap('errorEntries')) {
				result.errorEntries.set(ieKey, SiResultFactory.createEntryError(ieData));
			}
		}

		const eventMap = extr.reqMap('eventMap');
		const addedSeis: SiEntryIdentifier[] = [];
		const updatedSeis: SiEntryIdentifier[] = [];
		const removedSeis: SiEntryIdentifier[] = [];

		for (const [typeId, idsEvMapData] of eventMap) {
			const idEvMapExtr = new Extractor(idsEvMapData);

			for (const [id, eventType] of idEvMapExtr.reqStringMap('ids')) {
				switch (eventType) {
					case 'added':
						addedSeis.push(new SiEntryIdentifier(typeId, id));
						break;
					case 'changed':
						updatedSeis.push(new SiEntryIdentifier(typeId, id));
						break;
					case 'removed':
						removedSeis.push(new SiEntryIdentifier(typeId, id));
						break;
					default:
						throw new ObjectMissmatchError('Unknown event type: ' + eventType);
				}
			}
		}

		result.modEvent = new SiModEvent(addedSeis, updatedSeis, removedSeis);

		result.messages = extr.reqArray('messages').map((msgData) => {
			const msgExtr = new Extractor(msgData);
			return Message.createText(msgExtr.reqString('text'), msgExtr.reqString('severity') as MessageSeverity);
		});

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
