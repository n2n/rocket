
import { SiDeclaration } from '../meta/si-declaration';

export class SiValGetInstruction {

	protected declaration: SiDeclaration|null = null;
	protected controlsIncluded = false;

	constructor() {
	}

	// static create(bulky: boolean, readOnly: boolean): SiValGetInstruction {
	// 	return new SiValGetInstruction({ bulky, readOnly });
	// }

	static create(): SiValGetInstruction {
		return new SiValGetInstruction();
	}

	static createFromDeclaration(declaration: SiDeclaration): SiValGetInstruction {
		const instruction = new SiValGetInstruction();
		instruction.declaration = declaration;
		return instruction;
	}

	getDeclaration(): SiDeclaration|null {
		return this.declaration;
	}

	// setDeclaration(declaration: SiDeclaration): SiValGetInstruction {
	// 	this.declaration = declaration;
	// 	return this;
	// }

	setControlsIncluded(controlsIncluded: boolean): SiValGetInstruction {
		this.controlsIncluded = controlsIncluded;
		return this;
	}

	toJSON(): object {
		return {
			declarationRequested: !this.declaration,
			controlsIncluded: this.controlsIncluded
		};
	}
}

// export interface SiPartialContentInstruction {
// 	offset: number;
// 	num: number;
// }
