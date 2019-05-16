
import { Extractor } from "src/app/util/mapping/extractor";
import { SiEntryError } from "src/app/si/model/input/si-entry-error";
import { SiFieldError } from "src/app/si/model/input/si-field-error";
import { SiResult } from "src/app/si/model/input/si-result";

export class SiResultFactory {
	
	static create(data: any): SiResult {
		const extr = new Extractor(data);
		
		const result = new SiResult();
		
		for (let [key, data] of extr.reqMap('entryErrors')) {
			result.entryErrors.set(key, SiResultFactory.createEntryError(data));
		}
		
		return result;
	}
	
	static createEntryError(data: any): SiEntryError {
		const extr = new Extractor(data);
		
		const entryError = new SiEntryError(extr.reqStringArray('messages'));
		
		for (let [key, fieldData] of extr.reqMap('fieldErrors')) {
			entryError.fieldErrors.set(key, SiResultFactory.createFieldError(fieldData));
		}
		
		return entryError;
	}
	
	private static createFieldError(data: any): SiFieldError {
		const extr = new Extractor(data);
		
		const fieldError = new SiFieldError(extr.reqStringArray('messages'));
		
		for (let [key, entryData] of extr.reqMap('subEntryErrors')) {
			fieldError.subEntryErrors.set(key, SiResultFactory.createEntryError(entryData));
		}
		
		return fieldError;
	}
}
