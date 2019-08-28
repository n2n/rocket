
import { SiComp } from 'src/app/si/model/structure/si-zone-content';

export class SiValGetInstruction {

	protected declarationRequested = false;
	protected controlsIncluded = false;

	constructor(public comp: SiComp, public bulky: boolean, public readOnly: boolean) {
	}

	static create(comp: SiComp, bulky: boolean, readOnly: boolean): SiValGetInstruction {
		return new SiValGetInstruction(comp, bulky, readOnly);
	}

	setDeclarationRequested(declarationRequested: boolean): SiValGetInstruction {
		this.declarationRequested = declarationRequested;
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
			declarationRequested: this.declarationRequested,
			controlsIncluded: this.controlsIncluded
		};
	}
}

export interface SiPartialContentInstruction {
	offset: number;
	num: number;
}
