
import { SiComp } from "src/app/si/model/structure/si-zone-content";

export class SiGetInstruction {
	
	private declarationRequested = true;
	private entryId: string|null = null;
	private partialContentInstruction: SiPartialContentInstruction|null = null;
	private newEntryRequested = false;
	
	constructor(public comp: SiComp, public bulky: boolean, public readOnly: boolean) {
	}
	
	setDeclarationRequested(declarationRequested: boolean): SiGetInstruction {
		this.declarationRequested = declarationRequested;
		return this;
	}
	
	static partialContent(comp: SiComp, bulky: boolean, readOnly: boolean, offset: number, num: number): SiGetInstruction {
		const instruction = new SiGetInstruction(comp, bulky, readOnly);
		instruction.partialContentInstruction = {
			offset: offset,
			num: num
		}
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
	
}

export interface SiPartialContentInstruction {
	offset: number;
	num: number;
}