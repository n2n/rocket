import { SiDeclaration } from '../meta/si-declaration';
import { SiControlBoundary } from '../control/si-control-boundary';

export class SiValGetInstruction {

	protected declaration: SiDeclaration|null = null;
	protected controlBoundary: SiControlBoundary|null = null;
	protected controlsIncluded = false;

	constructor(protected maskId: string) {
	}

	// static create(bulky: boolean, readOnly: boolean): SiValGetInstruction {
	// 	return new SiValGetInstruction({ bulky, readOnly });
	// }

	static create(maskId: string): SiValGetInstruction {
		return new SiValGetInstruction(maskId);
	}

	// static createFromDeclaration(declaration: SiDeclaration, controlBoundary: SiControlBoundary): SiValGetInstruction {
	// 	const instruction = new SiValGetInstruction();
	// 	instruction.declaration = declaration;
	// 	instruction.controlBoundary = controlBoundary;
	// 	return instruction;
	// }

	getDeclaration(): SiDeclaration|null {
		return this.declaration;
	}

	// setDeclaration(declaration: SiDeclaration): SiValGetInstruction {
	// 	this.declaration = declaration;
	// 	return this;
	// }

	getControlBoundary(): SiControlBoundary|null {
		return this.controlBoundary;
	}

	setControlsIncluded(controlsIncluded: boolean): SiValGetInstruction {
		this.controlsIncluded = controlsIncluded;
		return this;
	}

	toJsonStruct(): object {
		return {
			maskId: this.maskId,
			declarationRequested: !this.declaration,
			controlsIncluded: this.controlsIncluded
		};
	}
}

// export interface SiPartialContentInstruction {
// 	offset: number;
// 	num: number;
// }
