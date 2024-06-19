
import { Extractor, ObjectMissmatchError } from 'src/app/util/mapping/extractor';
import { Message, MessageSeverity } from 'src/app/util/i18n/message';
import { SiCallResponse, SiDirective, SiControlResult, SiInputError, SiInputResult } from '../manage/si-control-result';
import { SiEntryIdentifier, SiObjectIdentifier } from '../model/content/si-entry-qualifier';
import { SiModEvent } from '../model/mod/model/si-mod-state.service';
import { Injector } from '@angular/core';
import { SiDeclaration } from '../model/meta/si-declaration';
import { SiBuildTypes } from './si-build-types';
import { SiEssentialsFactory } from './si-field-essentials-factory';
import { SiMetaFactory } from './si-meta-factory';

export class SiResultFactory {

	constructor(private injector: Injector) {

	}

	createControlResult(data: any, declaration?: SiDeclaration): SiControlResult {
		const extr = new Extractor(data);

		const inputErrorData = extr.nullaObject('inputError');
		if (inputErrorData) {
			return {
				inputError: this.createInputError(inputErrorData, declaration!)
			};
		}

		return {
			callResponse: this.createCallResponse(extr.reqObject('callResponse')),
			inputResult: this.buildInputResult(extr.nullaObject('inputResult'), declaration!)!
		};
	}

	createInputError(data: any, declaration: SiDeclaration): SiInputError {
		const inputError = new SiInputError();
		const entryFactory = new SiBuildTypes.SiEntryFactory(declaration, this.injector);
		for (const [eeKey, eeData] of new Extractor(data).reqMap('siValueBoundary')) {
			inputError.errorEntries.set(eeKey, entryFactory.createValueBoundary(eeData));
		}
		return inputError;
	}

	buildInputResult(data: any, declaration: SiDeclaration): SiInputResult|null {
		if (!data) {
			return null;
		}

		const inputResult = new SiInputResult();
		const entryFactory = new SiBuildTypes.SiEntryFactory(declaration, this.injector);
		for (const [eeKey, eeData] of new Extractor(data).reqMap('siValueBoundary')) {
			inputResult.valueBoundaries.set(eeKey, entryFactory.createValueBoundary(eeData));
		}
		return inputResult;
	}

	createCallResponse(data: any): SiCallResponse {
		const extr = new Extractor(data);

		const result = new SiCallResponse();

		result.directive = extr.nullaString('directive') as SiDirective;
		let navPointData: object|null;
		if (null !== (navPointData = extr.nullaObject('navPoint'))) {
			result.navPoint = SiEssentialsFactory.createNavPoint(navPointData);
		}

		const eventMap = extr.reqMap('eventMap');
		const addedSeis: SiObjectIdentifier[] = [];
		const updatedSeis: SiObjectIdentifier[] = [];
		const removedSeis: SiObjectIdentifier[] = [];

		for (const [typeId, idsEvMapData] of eventMap) {
			const idEvMapExtr = new Extractor(idsEvMapData);

			for (const [id, eventType] of idEvMapExtr.reqStringMap('ids')) {
				switch (eventType) {
					case 'added':
						addedSeis.push({ typeId, id });
						break;
					case 'changed':
						updatedSeis.push({ typeId, id });
						break;
					case 'removed':
						removedSeis.push({ typeId, id });
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

	// static createEntryError(data: any): SiEntryError {
	// 	const extr = new Extractor(data);

	// 	const entryError = new SiEntryError(/*extr.reqStringArray('messages')*/);

	// 	for (const [key, fieldData] of extr.reqMap('fieldErrors')) {
	// 		entryError.fieldErrors.set(key, SiResultFactory.createFieldError(fieldData));
	// 	}

	// 	return entryError;
	// }

	// private static createFieldError(data: any): SiFieldError {
	// 	const extr = new Extractor(data);

	// 	const fieldError = new SiFieldError(Message.createTexts(extr.reqStringArray('messages')));

	// 	for (const [key, entryData] of extr.reqMap('subEntryErrors')) {
	// 		fieldError.subEntryErrors.set(key, SiResultFactory.createEntryError(entryData));
	// 	}

	// 	return fieldError;
	// }

}
