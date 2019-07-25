
import { SiComp } from "src/app/si/model/structure/si-zone-content";
import { SiZone } from "src/app/si/model/structure/si-zone";
import { Extractor } from "src/app/util/mapping/extractor";
import { SiGetResponse } from "src/app/si/model/api/si-get-response";
import { SiGetResult } from "src/app/si/model/api/si-get-result";
import { SiResultFactory } from "src/app/si/build/si-result-factory";
import { SiCompFactory } from "src/app/si/build/si-factory";
import { SiGetRequest } from "src/app/si/model/api/si-get-request";

export class SiApiFactory {
	private compFactory: SiCompFactory;
	
	constructor(public zone: SiZone) {
		
	}
	
	createGetResponse(data: any, request: SiGetRequest): SiGetResponse {
		const extr = new Extractor(data);
	
		const response = new SiGetResponse();
		
		const resultsData = extr.reqArray('results')
		for (const key in request.instructions) {
			if (!resultsData[key]) {
				throw new Error('No result for key: ' + key);
			}
			
			response.results[key] = this.createGetResult(resultsData[key], request.instructions[key].zoneContent);
		}
		
		return response;
	}
	
	private createGetResult(data: any, zoneContent: SiComp): SiGetResult {
		const compFactory = new SiCompFactory(this.zone, zoneContent);
		const extr = new Extractor(data);
		
		const result: SiGetResult = {
			bulkyDeclaration: null,
			compactDeclaration: null,
			entry: null,
			partialContent: null
		}
		
		let propData: any = null;

		if (null !== (propData = extr.nullaObject('bulkyDeclaration'))) {
			result.bulkyDeclaration = SiResultFactory.createBulkyDeclaration(propData);
		}
		
		if (null !== (propData = extr.nullaObject('compactDeclaration'))) {
			result.compactDeclaration = SiResultFactory.createCompactDeclaration(propData);
		}
		
		if (null !== (propData = extr.nullaObject('entry'))) {
			result.entry = compFactory.createEntry(propData);
		}
		
		if (null !== (propData = extr.nullaObject('partialContent'))) {
			result.partialContent = compFactory.createPartialContent(propData);
		}
		
		return result;
	}
}