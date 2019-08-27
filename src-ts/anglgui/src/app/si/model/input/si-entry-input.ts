
import { SiIdentifier } from 'src/app/si/model/content/si-qualifier';

export class SiEntryInput {

	constructor(public identifier: SiIdentifier, public typeId: string,
			public bulky: boolean, readonly fieldInputMap: Map<string, object>) {

	}
}
