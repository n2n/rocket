import { Extractor, ObjectMissmatchError } from 'src/app/util/mapping/extractor';
import { Message, MessageSeverity } from 'src/app/util/i18n/message';
import { SiCallResponse, SiDirective, SiInputResult } from '../manage/si-control-result';
import { SiObjectIdentifier } from '../model/content/si-entry-qualifier';
import { SiModEvent } from '../model/mod/model/si-mod-state.service';
import { Injector } from '@angular/core';
import { SiDeclaration } from '../model/meta/si-declaration';
import { SiEssentialsFactory } from './si-field-essentials-factory';
import { SiEntryFactory } from './si-entry-factory';
import { SiApiCallResponse } from '../model/api/si-api-call-response';
import { SiApiCall } from '../model/api/si-api-call';
import { SiFieldCallResponse } from '../model/api/si-field-call-response';
import { SiGetResponse } from '../model/api/si-get-response';
import { SiGetResult } from '../model/api/si-get-result';
import { SiMetaFactory } from './si-meta-factory';
import { SiControlFactory } from './si-control-factory';
import { SiGetRequest } from '../model/api/si-get-request';
import { SiGetInstruction } from '../model/api/si-get-instruction';
import { SimpleSiControlBoundary } from '../model/control/impl/model/simple-si-control-boundary';
import { SiValueBoundary } from '../model/content/si-value-boundary';
import { SiPartialContent } from '../model/content/si-partial-content';

export class SiResultFactory {

	constructor(private injector: Injector, private apiUrl: string) {

	}

	// createControlResult(data: any, declaration?: SiDeclaration): SiControlResult {
	// 	const extr = new Extractor(data);
	//
	// 	const inputErrorData = extr.nullaObject('inputError');
	// 	if (inputErrorData) {
	// 		return {
	// 			inputError: this.createInputResult(inputErrorData, declaration!)
	// 		};
	// 	}
	//
	// 	return {
	// 		callResponse: this.createCallResponse(extr.reqObject('callResponse')),
	// 		inputResult: this.createInputResult(extr.nullaObject('inputResult'), declaration!)!
	// 	};
	// }

	// createInputResult(data: any, declaration: SiDeclaration): SiInputError {
	// 	const inputError = new SiInputError();
	// 	const entryFactory = new SiEntryFactory(declaration, this.apiUrl, this.injector);
	// 	for (const [eeKey, eeData] of new Extractor(data).reqMap('siValueBoundary')) {
	// 		inputError.errorEntries.set(eeKey, entryFactory.createValueBoundary(eeData));
	// 	}
	// 	return inputError;
	// }

	private createInputResult(data: any, declaration: SiDeclaration): SiInputResult {
		const inputResult = new SiInputResult();
		const entryFactory = new SiEntryFactory(declaration, this.apiUrl, this.injector);
		for (const [eeKey, eeData] of new Extractor(data).reqMap('valueBoundaries')) {
			inputResult.valueBoundaries.set(eeKey, entryFactory.createValueBoundary(eeData));
		}
		return inputResult;
	}

	createApiCallResponse(data: any, apiCall: SiApiCall): SiApiCallResponse {
		const extr = new Extractor(data);

		let inputResult: SiInputResult|undefined;
		let callResponse: SiCallResponse|undefined;
		let fieldCallResponse: SiFieldCallResponse|undefined;
		let getResponse: SiGetResponse|undefined;

		if (extr.contains('inputResult') && apiCall.input) {
			inputResult = this.createInputResult(extr.reqObject('inputResult'), apiCall.input.declaration);
		}

		if (data.callResponse) {
			callResponse = this.createCallResponse(extr.reqObject('callResponse'))
		}

		if (data.fieldCallResponse) {
			fieldCallResponse = this.createFieldCallResponse(extr.reqObject('fieldCallResponse'))
		}

		if (data.getResponse && apiCall.getRequest) {
			getResponse = this.createGetResponse(extr.reqObject('getResponse'), apiCall.getRequest);
		}

		return { inputResult, callResponse, fieldCallResponse, getResponse }
	}

	private createCallResponse(data: any): SiCallResponse {
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

	private createFieldCallResponse(data: any): SiFieldCallResponse {
		const extr = new Extractor(data);
		return  {
			data: extr.reqObject('data')
		}
	}

	private createGetResponse(data: any, getRequest: SiGetRequest): SiGetResponse {
		const extr = new Extractor(data);
		const response = new SiGetResponse();

		const resultsData = extr.reqArray('instructionResults');

		for (const key in getRequest.instructions) {
			if (!resultsData[key]) {
				throw new Error('No result for key: ' + key);
			}

			response.instructionResults[key] = this.createGetResult(resultsData[key], getRequest.instructions[key]);
		}

		return response;
	}

	private createGetResult(data: any, getInstruction: SiGetInstruction): SiGetResult {
		const extr = new Extractor(data);

		let declaration: SiDeclaration|null = null;
		let newControlBoundary: SimpleSiControlBoundary|null = null;
		if (null === getInstruction.getDeclaration()) {
			let controlBoundary = getInstruction.getGeneralControlsBoundry()
			if (!controlBoundary) {
				newControlBoundary = controlBoundary = new SimpleSiControlBoundary([], undefined, this.apiUrl);
			}
			const controlFactory = new SiControlFactory(controlBoundary, this.injector);
			declaration = SiMetaFactory.createDeclaration(extr.reqObject('declaration'), controlFactory);

			if (newControlBoundary) {
				newControlBoundary.declaration = declaration
			}
		}

		const entryFactory = new SiEntryFactory(getInstruction.getDeclaration() ?? declaration!, this.apiUrl, this.injector);
		let propData: any;

		let valueBoundary: SiValueBoundary|null = null;
		if (null !== (propData = extr.nullaObject('entry'))) {
			valueBoundary = entryFactory.createValueBoundary(propData);
		}

		let partialContent: SiPartialContent|null = null;
		if (null !== (propData = extr.nullaObject('partialContent'))) {
			partialContent = entryFactory.createPartialContent(propData);
		}

		return {
			declaration,
			valueBoundary,
			partialContent
		}
	}

}
