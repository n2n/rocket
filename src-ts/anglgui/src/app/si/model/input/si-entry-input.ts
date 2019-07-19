
import { SiQualifier, SiIdentifier } from "src/app/si/model/content/si-qualifier";

export class SiEntryInput {
	
	constructor(public identifier: SiIdentifier, public buildupId, 
			readonly fieldInputMap: Map<string, object>) {
		
	}	
}