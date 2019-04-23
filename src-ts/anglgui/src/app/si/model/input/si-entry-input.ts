
export class SiEntryInput {
	
	constructor(public id: string|null, readonly fieldInputMap: Map<string, Map<string, SiInputType>>) {
		
	}
	
}

export type SiInputType = string|number|boolean|File|null; 