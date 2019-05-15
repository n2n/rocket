
import { SiEntryInput } from "src/app/si/model/input/si-entry-input";

export class SiInput {
	constructor(public entryInputs: SiEntryInput[] = []) {
		
	}

	toParamMap(): Map<string, string|Blob> {
		const map = new Map<string, string|Blob>();
		
		if (this.entryInputs.length == 0) {
			return map;
		}
		
		const entryInputMaps: Array<any> = [];
		
		for (const entryInput of this.entryInputs) {
			const fieldInputObj = {}
			
			for (let [fieldId, inputObj] of entryInput.fieldInputMap) {
				fieldInputObj[fieldId] = inputObj;
			}
			
			entryInputMaps.push({
				buildupId: entryInput.buildupId,
				id: entryInput.id,
				fieldInputMap: fieldInputObj
			});
		}

		map.set('entryInputMaps', JSON.stringify(entryInputMaps));
		
		return map;
	}
	
	private createFileParamName(entryNo: number, fieldId: string, inputName: string): string {
		return 'fileInputs[ ' + encodeURI(JSON.stringify([entryNo, fieldId, inputName])) + ']';
	}
}