import { SiValGetInstruction } from './si-val-get-instruction';
import { SiValueBoundaryInput } from '../input/si-input';

export class SiValInstruction {

	public getInstructions: SiValGetInstruction[];

	constructor(public valueBoundaryInput: SiValueBoundaryInput, ...getInstructions: SiValGetInstruction[]) {
		this.getInstructions = getInstructions;
	}
}

// export interface SiPartialContentInstruction {
// 	offset: number;
// 	num: number;
// }
