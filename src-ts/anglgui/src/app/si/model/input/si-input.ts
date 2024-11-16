import { SiDeclaration } from '../meta/si-declaration';

export class SiInput {

	constructor(public declaration: SiDeclaration, public valueBoundaryInputs: SiValueBoundaryInput[] = []) {

	}

	toJsonStruct(): object[] {
		return this.valueBoundaryInputs.map((i: SiValueBoundaryInput) => i.toJsonStruct())
	}
}


export class SiValueBoundaryInput {
	constructor(private selectedMaskId: string, private entryInput: SiEntryInput) {
	}

	toJsonStruct(): object {
		return {
			selectedMaskId: this.selectedMaskId,
			entryInput: this.entryInput.toJsonStruct()
		};
	}
}

export class SiEntryInput {

	constructor(public entryId: string|null,
			readonly fieldInputMap: Map<string, object>) {

	}

	toJsonStruct(): object {
		const fieldInputObj: any = {};
		for (const [propId, inputObj] of this.fieldInputMap) {
			fieldInputObj[propId] = inputObj;
		}

		return {
			entryId: this.entryId,
			fieldInputMap: fieldInputObj,
		};
	}
}