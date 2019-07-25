
import { SiComp } from 'src/app/si/model/structure/si-zone-content';

export class SiGetInstruction {

	constructor(public comp: SiComp, public bulky: boolean, public readOnly: boolean) {
	}

	protected declarationRequested = true;
	protected entryId: string|null = null;
	protected partialContentInstruction: SiPartialContentInstruction|null = null;
	protected newEntryRequested = false;

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

	setDeclarationRequested(declarationRequested: boolean): SiGetInstruction {
		this.declarationRequested = declarationRequested;
		return this;
	}

}

export interface SiPartialContentInstruction {
	offset: number;
	num: number;
}
