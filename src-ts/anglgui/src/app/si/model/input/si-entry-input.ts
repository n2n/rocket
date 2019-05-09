
export class SiEntryInput {
	
	constructor(public category: string, public id: string|null, public buildupId, 
			readonly fieldInputMap: Map<string, Map<string, SiInputValue>>) {
		
	}
	
}

export type SiInputValue = string|number|boolean|File|null; 