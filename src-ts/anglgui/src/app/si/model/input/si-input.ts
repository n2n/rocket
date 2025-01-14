import { SiDeclaration } from '../meta/si-declaration';

export class SiInput {

	constructor(public declaration: SiDeclaration, public valueBoundaryInputs: SiValueBoundaryInput[] = []) {

	}

	toJsonStruct(): object[] {
		return this.valueBoundaryInputs.map((i: SiValueBoundaryInput) => i.toJsonStruct())
	}
}


export class SiValueBoundaryInput {
	constructor(private selectedTypeId: string, private entryInput: SiEntryInput) {
	}

	toJsonStruct(): object {
		return {
			selectedTypeId: this.selectedTypeId,
			entryInput: this.entryInput.toJsonStruct()
		};
	}
}

export class SiEntryInput {

	constructor(public maskId: string, public entryId: string|null,
			readonly fieldInputMap: Map<string, object>) {

	}

	toJsonStruct(): object {
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