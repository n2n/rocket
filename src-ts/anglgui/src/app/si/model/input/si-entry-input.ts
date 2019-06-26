
import { SiQualifier } from "src/app/si/model/content/si-qualifier";

export class SiEntryInput {
	
	constructor(public qualifier: SiQualifier, public buildupId, 
			readonly fieldInputMap: Map<string, object>) {
		
	}	
}