
export class SiEntryInput {
	
	constructor(public id: string|null, public buildupId, 
			readonly fieldInputMap: Map<string, Map<string, SiInputValue>>) {
		
	}
	
}

export type SiInputValue = string|number|boolean|File|null; 