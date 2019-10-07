
import { SiDeclaration } from '../meta/si-declaration';
import { SiComp } from '../comp/si-comp';

export class SiGetInstruction {

	constructor(public comp: SiComp, public bulky: boolean, public readOnly: boolean) {
	}

	protected declaration: SiDeclaration|null = null;
	protected entryId: string|null = null;
	protected partialContentInstruction: SiPartialContentInstruction|null = null;
	protected newEntryRequested = false;
	protected controlsIncluded = false;

	static partialContent(comp: SiComp, bulky: boolean, readOnly: boolean, offset: number, num: number): SiGetInstruction {
		const instruction = new SiGetInstruction(comp, bulky, readOnly);
		instruction.partialContentInstruction = {
			offset,
			num
		};
		return instruction;
	}

	static entry(comp: SiComp, bulky: boolean, readOnly: boolean, entryId: string): SiGetInstruction {
		const instruction = new SiGetInstruction(comp, bulky, readOnly);
		instruction.entryId = entryId;
		return instruction;
	}

	static newEntry(comp: SiComp, bulky: boolean, readOnly: boolean): SiGetInstruction {
		const instruction = new SiGetInstruction(comp, bulky, readOnly);
		instruction.newEntryRequested = true;
		return instruction;
	}

	getDeclaration(): SiDeclaration|null {
		return this.declaration;
	}

	setDeclaration(declaration: SiDeclaration): SiGetInstruction {
		this.declaration = declaration;
		return this;
	}

	setControlsIncluded(controlsIncluded: boolean): SiGetInstruction {
		this.controlsIncluded = controlsIncluded;
		return this;
	}

	toJSON() {
		return {
			bulky: this.bulky,
			readOnly: this.readOnly,
			declarationRequested: !this.declaration,
			controlsIncluded: this.controlsIncluded,
			entryId: this.entryId,
			partialContentInstruction: this.partialContentInstruction,
			newEntryRequested: this.newEntryRequested
		};
	}
}

export interface SiPartialContentInstruction {
	offset: number;
	num: number;
}
