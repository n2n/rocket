
import { SiTypeQualifier } from "src/app/si/model/meta/si-type-qualifier";

export class SiType {
	constructor(public qualifier: SiTypeQualifier, public props: Map<string, SiProp>) {
		
	}
}
