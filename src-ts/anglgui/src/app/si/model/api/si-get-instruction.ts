
export class SiGetInstruction {
	
	private declarationRequested = true;
	private entryId: number|null = null;
	private partialContentInstruction: SiPartialContentInstruction|null = null;
	private newEntryRequested = false;
	
	constructor(public bulky: boolean, public readOnly: boolean) {
	}
	
	setDeclarationRequested(declarationRequested: boolean): SiGetInstruction {
		this.declarationRequested = declarationRequested;
		return this;
	}
	
	static partialContent(bulky: boolean, readOnly: boolean, offset: number, num: number): SiGetInstruction {
		const instruction = new SiGetInstruction(bulky, readOnly);
		instruction.partialContentInstruction = {
			offset: offset,
			num: num
		}
		return instruction;
	}
	
	static entry(bulky: boolean, readOnly: boolean, entryId: number): SiGetInstruction {
		const instruction = new SiGetInstruction(bulky, readOnly);
		instruction.entryId = entryId;
		return instruction;
	}
	
	static newEntry(bulky: boolean, readOnly: boolean) {
		const instruction = new SiGetInstruction(bulky, readOnly);
		instruction.newEntryRequested = true;
		return instruction;
	}
	
}

export interface SiPartialContentInstruction {
	offset: number;
	num: number;
}