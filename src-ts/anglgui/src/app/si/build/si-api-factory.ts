
import { SiGetResponse } from 'src/app/si/model/api/si-get-response';
import { SiGetResult } from 'src/app/si/model/api/si-get-result';
import { SiResultFactory } from 'src/app/si/build/si-result-factory';
import { SiGetRequest } from 'src/app/si/model/api/si-get-request';
import { SiValRequest } from '../model/api/si-val-request';
import { SiValResponse } from '../model/api/si-val-response';
import { SiValInstruction } from '../model/api/si-val-instruction';
import { SiValResult } from '../model/api/si-val-result';
import { SiValGetResult } from '../model/api/si-val-get-result';
import { SiComp } from '../model/comp/si-comp';
import { SiMetaFactory } from './si-meta-factory';
import { SiEntryFactory } from './si-entry-factory';
import { SiDeclaration } from '../model/meta/si-declaration';
import { Extractor } from 'src/app/util/mapping/extractor';
import { Injector } from '@angular/core';

export class SiApiFactory {

	constructor(private injector: Injector) {
	}

	createGetResponse(data: any, request: SiGetRequest): SiGetResponse {
		const extr = new Extractor(data);

		const response = new SiGetResponse();

		const resultsData = extr.reqArray('results');
		for (const key of request.instructions.keys()) {
			if (!resultsData[key]) {
				throw new Error('No result for key: ' + key);
			}

			response.results[key] = this.createGetResult(resultsData[key], request.instructions[key].comp,
					request.instructions[key].getDeclaration());
		}

		return response;
	}

	private createGetResult(data: any, comp: SiComp, declaration: SiDeclaration|null): SiGetResult {
		const extr = new Extractor(data);

		const result: SiGetResult = {
			declaration: null,
			entry: null,
			partialContent: null
		};

		let propData: any = null;

		if (!declaration) {
			declaration = result.declaration = SiMetaFactory.createDeclaration(extr.reqObject('declaration'));
		}

		if (null !== (propData = extr.nullaObject('entry'))) {
			result.entry = new SiEntryFactory(comp, declaration, this.injector).createEntry(propData);
		}

		if (null !== (propData = extr.nullaObject('partialContent'))) {
			result.partialContent = new SiEntryFactory(comp, declaration, this.injector)
					.createPartialContent(propData);
		}

		return result;
	}

	createValResponse(data: any, request: SiValRequest): SiValResponse {
		const extr = new Extractor(data);

		const response = new SiValResponse();

		const resultsData = extr.reqArray('results');
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

		const result = new SiValResult();

		const entryErrorData = extr.nullaObject('entryError');
		if (entryErrorData) {
			result.entryError = SiResultFactory.createEntryError(entryErrorData);
		}

		const resultsData = extr.reqArray('getResults');
		for (const key of instruction.getInstructions.keys()) {
			if (!resultsData[key]) {
				throw new Error('No result for key: ' + key);
			}

			const getInstruction = instruction.getInstructions[key];
			result.getResults[key] = this.createValGetResult(resultsData[key], getInstruction.comp, getInstruction.getDeclaration());
		}

		return result;
	}

	private createValGetResult(data: any, comp: SiComp, declaration: SiDeclaration|null): SiValGetResult {
		const extr = new Extractor(data);

		const result: SiValGetResult = {
			declaration: null,
			entry: null
		};

		let propData: any = null;

		if (!declaration) {
			declaration = result.declaration = SiMetaFactory.createDeclaration(extr.reqObject('declaration'));
		}

		if (null !== (propData = extr.nullaObject('entry'))) {
			result.entry = new SiEntryFactory(comp, declaration, this.injector).createEntry(propData);
		}

		return result;
	}
}
