
import { SiComp } from 'src/app/si/model/entity/si-comp';
import { SiZone } from 'src/app/si/model/structure/si-zone';
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiGetResponse } from 'src/app/si/model/api/si-get-response';
import { SiGetResult } from 'src/app/si/model/api/si-get-result';
import { SiResultFactory } from 'src/app/si/build/si-result-factory';
import { SiCompEssentialsFactory } from 'src/app/si/build/si-factory';
import { SiGetRequest } from 'src/app/si/model/api/si-get-request';
import { SiValRequest } from '../model/api/si-val-request';
import { SiValResponse } from '../model/api/si-val-response';
import { SiValInstruction } from '../model/api/si-val-instruction';
import { SiValResult } from '../model/api/si-val-result';
import { SiValGetResult } from '../model/api/si-val-get-result';

export class SiApiFactory {
	private compFactory: SiCompEssentialsFactory;

	constructor() {

	}

	createGetResponse(data: any, request: SiGetRequest): SiGetResponse {
		const extr = new Extractor(data);

		const response = new SiGetResponse();

		const resultsData = extr.reqArray('results');
		for (const key in request.instructions) {
			if (!resultsData[key]) {
				throw new Error('No result for key: ' + key);
			}

			response.results[key] = this.createGetResult(resultsData[key], request.instructions[key].comp);
		}

		return response;
	}

	private createGetResult(data: any, zoneContent: SiComp): SiGetResult {
		const compFactory = new SiCompEssentialsFactory(zoneContent);
		const extr = new Extractor(data);

		const result: SiGetResult = {
			entryDeclaration: null,
			entry: null,
			partialContent: null
		};

		let propData: any = null;

		if (null !== (propData = extr.nullaObject('entryDeclaration'))) {
			result.entryDeclaration = SiResultFactory.createEntryDeclaration(propData);
		}

		if (null !== (propData = extr.nullaObject('entry'))) {
			result.entry = compFactory.createEntry(propData);
		}

		if (null !== (propData = extr.nullaObject('partialContent'))) {
			result.partialContent = compFactory.createPartialContent(propData);
		}

		return result;
	}

	createValResponse(data: any, request: SiValRequest): SiValResponse {
		const extr = new Extractor(data);

		const response = new SiValResponse();

		const resultsData = extr.reqArray('results');
		for (const key in request.instructions) {
			if (!resultsData[key]) {
				throw new Error('No result for key: ' + key);
			}

			response.results[key] = this.createValResult(resultsData[key], request.instructions[key]);
		}

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
		for (const key in instruction.getInstructions) {
			if (!resultsData[key]) {
				throw new Error('No result for key: ' + key);
			}

			result.getResults[key] = this.createValGetResult(resultsData[key], instruction.getInstructions[key].comp);
		}

		return result;
	}

	private createValGetResult(data: any, zoneContent: SiComp): SiValGetResult {
		const compFactory = new SiCompEssentialsFactory(zoneContent);
		const extr = new Extractor(data);

		const result: SiValGetResult = {
			entryDeclaration: null,
			entry: null
		};

		let propData: any = null;

		if (null !== (propData = extr.nullaObject('entryDeclaration'))) {
			result.entryDeclaration = SiResultFactory.createEntryDeclaration(propData);
		}

		if (null !== (propData = extr.nullaObject('entry'))) {
			result.entry = compFactory.createEntry(propData);
		}

		return result;
	}
}
