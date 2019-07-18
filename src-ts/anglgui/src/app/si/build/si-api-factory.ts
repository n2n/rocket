
import { SiContent } from "src/app/si/model/structure/si-zone-content";
import { SiZone } from "src/app/si/model/structure/si-zone";
import { Extractor } from "src/app/util/mapping/extractor";
import { SiGetResponse } from "src/app/si/model/api/si-get-response";
import { SiGetResult } from "src/app/si/model/api/si-get-result";
import { SiCompFactory } from "src/app/si/build/si-comp-factory";
import { SiResultFactory } from "src/app/si/build/si-result-factory";

export class SiApiFactory {
	private compFactory: SiCompFactory;
	
	constructor(public zone: SiZone, public zoneContent: SiContent) {
		this.compFactory = new SiCompFactory(zone, zoneContent);
	}
	
	createGetResponse(data: any): SiGetResponse {
		const extr = new Extractor(data);
	
		const response = new SiGetResponse();
		
		for (const resultData of extr.reqArray('results')) {
			response.results.push(this.createGetResult(resultData));
		}
		
		return response;
	}
	
	private createGetResult(data: any): SiGetResult {
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
			result.entry = this.compFactory.createEntry(propData);
		}
		
		if (null !== (propData = extr.nullaObject('partialContent'))) {
			result.partialContent = this.compFactory.createPartialContent(propData);
		}
		
		return result;
	}
}