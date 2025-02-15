import { SiValGetInstruction } from './si-val-get-instruction';
import { SiValueBoundaryInput } from '../input/si-input';
import { SiDeclaration } from '../meta/si-declaration';
import { SiControlBoundary } from '../control/si-control-boundary';

export class SiValInstruction {

	public declaration: SiDeclaration|null = null;
	public controlBoundary: SiControlBoundary|null = null;
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
