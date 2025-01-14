import { SiValGetInstruction } from './si-val-get-instruction';
import { SiValueBoundaryInput } from '../input/si-input';

export class SiValInstruction {

	public getInstructions: SiValGetInstruction[];

	constructor(public valueBoundaryInput: SiValueBoundaryInput, ...getInstructions: SiValGetInstruction[]) {
		this.getInstructions = getInstructions;
	}

	toJsonStruct(): object {
		return {
			valueBoundaryInput: this.valueBoundaryInput.toJsonStruct(),
			getInstructions: this.getInstructions.map(i => i.toJsonStruct())
		}
	}
}

// export interface SiPartialContentInstruction {
// 	offset: number;
// 	num: number;
// }
