export class SiEntryInput {

	constructor(public maskId: string, public entryId: string|null,
			readonly fieldInputMap: Map<string, object>) {

	}

	toJSON(): object {
		const fieldInputObj: any = {};
		for (const [propId, inputObj] of this.fieldInputMap) {
			fieldInputObj[propId] = inputObj;
		}

		return {
			maskId: this.maskId,
			entryId: this.entryId,
			fieldInputMap: fieldInputObj,
		};
	}
}
