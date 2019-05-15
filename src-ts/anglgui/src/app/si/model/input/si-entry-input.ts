
export class SiEntryInput {
	
	constructor(public category: string, public id: string|null, public buildupId, 
			readonly fieldInputMap: Map<string, object>) {
		
	}	
}