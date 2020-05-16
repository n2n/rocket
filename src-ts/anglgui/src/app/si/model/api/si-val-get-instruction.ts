
import { SiDeclaration } from '../meta/si-declaration';

export class SiValGetInstruction {

	protected declaration: SiDeclaration|null = null;
	protected controlsIncluded = false;

	constructor(public bulky: boolean, public readOnly: boolean) {
	}

	static create(bulky: boolean, readOnly: boolean): SiValGetInstruction {
		return new SiValGetInstruction(bulky, readOnly);
	}

	getDeclaration(): SiDeclaration|null {
		return this.declaration;
	}

	setDeclaration(declaration: SiDeclaration): SiValGetInstruction {
		this.declaration = declaration;
		return this;
	}

	setControlsIncluded(controlsIncluded: boolean): SiValGetInstruction {
		this.controlsIncluded = controlsIncluded;
		return this;
	}

	toJSON() {
		return {
			bulky: this.bulky,
			readOnly: this.readOnly,
			declarationRequested: !this.declaration,
			controlsIncluded: this.controlsIncluded
		};
	}
}

export interface SiPartialContentInstruction {
	offset: number;
	num: number;
}