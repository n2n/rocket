import { Extractor, ObjectMissmatchError } from 'src/app/util/mapping/extractor';
import { Message, MessageSeverity } from 'src/app/util/i18n/message';
import { SiCallResponse, SiDirective, SiInputResult } from '../manage/si-control-result';
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
import { SiValRequest } from '../model/api/si-val-request';
import { SiValResponse } from '../model/api/si-val-response';
import { SiValInstruction } from '../model/api/si-val-instruction';
import { SiValResult } from '../model/api/si-val-result';
import { SiControlBoundry } from '../model/control/si-control-boundry';
import { SiValGetResult } from '../model/api/si-val-get-result';
import { SiObjectIdentifier } from '../model/content/si-object-qualifier';

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
		let valResponse: SiValResponse|undefined;

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

		if (data.valResponse && apiCall.valRequest) {
			valResponse = this.createValResponse(extr.reqObject('valResponse'), apiCall.valRequest);
		}

		return { inputResult, callResponse, fieldCallResponse, getResponse, valResponse }
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

		for (const [superTypeId, idsEvMapData] of eventMap) {
			const idEvMapExtr = new Extractor(idsEvMapData);

			for (const [id, eventType] of idEvMapExtr.reqStringMap('ids')) {
				switch (eventType) {
					case 'added':
						addedSeis.push({ superTypeId, id });
						break;
					case 'changed':
						updatedSeis.push({ superTypeId, id });
						break;
					case 'removed':
						removedSeis.push({ superTypeId, id });
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

	private createValResponse(data: any, request: SiValRequest): SiValResponse {
		const extr = new Extractor(data);

		const response = new SiValResponse();

		const resultsData = extr.reqArray('instructionResults');
		request.instructions.forEach((_value, key) => {
			if (!resultsData[key]) {
				throw new Error('No result for key: ' + key);
			}

			response.results[key] = this.createValResult(resultsData[key], request.instructions[key]);
		});

		return response;
	}

	private createValResult(data: any, instruction: SiValInstruction): SiValResult {
		const extr = new Extractor(data);

		if (!instruction.declaration) {
			instruction.declaration = SiMetaFactory.createDeclaration(extr.reqObject('declaration'),
					new SiControlFactory(instruction.controlBoundary!, this.injector));
		}

		const valueBoundary = new SiEntryFactory(instruction.declaration, this.apiUrl, this.injector)
				.createValueBoundary(extr.reqObject('valueBoundary'));

		const result = new SiValResult(extr.reqBoolean('valid'), valueBoundary);

		const resultsData = extr.reqArray('getResults');
		for (const key of instruction.getInstructions.keys()) {
			if (!resultsData[key]) {
				throw new Error('No result for key: ' + key);
			}

			const getInstruction = instruction.getInstructions[key];
			result.getResults[key] = this.createValGetResult(resultsData[key], getInstruction.getDeclaration(), getInstruction.getControlBoundary()!);
		}

		return result;
	}

	private createValGetResult(data: any, declaration: SiDeclaration|null, controlBoundary: SiControlBoundry): SiValGetResult {
		const extr = new Extractor(data);

		const result: SiValGetResult = {
			declaration: null,
			valueBoundary: null
		};

		let propData: any;

		if (!declaration) {
			declaration = result.declaration = SiMetaFactory.createDeclaration(extr.reqObject('declaration'),
					new SiControlFactory(controlBoundary, this.injector));
		}

		if (null !== (propData = extr.nullaObject('valueBoundary'))) {
			result.valueBoundary = new SiEntryFactory(declaration, this.apiUrl, this.injector).createValueBoundary(propData);
		}

		return result;
	}

}
