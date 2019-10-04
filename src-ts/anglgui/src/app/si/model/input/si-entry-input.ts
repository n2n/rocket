
import { SiEntryIdentifier } from 'src/app/si/model/entity/si-qualifier';

export class SiEntryInput {

	constructor(public identifier: SiEntryIdentifier, public typeId: string,
			public bulky: boolean, readonly fieldInputMap: Map<string, object>) {

	}

	toJSON() {
		const fieldInputObj = {};
		for (const [fieldId, inputObj] of this.fieldInputMap) {
			fieldInputObj[fieldId] = inputObj;
		}

		return {
			identifier: this.identifier,
			typeId: this.typeId,
			bulky: this.bulky,
			fieldInputMap: fieldInputObj
		};
	}
}
