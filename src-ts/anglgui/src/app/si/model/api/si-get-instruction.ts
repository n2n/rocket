
import { SiDeclaration } from '../meta/si-declaration';
import { SiControlBoundry } from '../control/si-control-bountry';

export class SiGetInstruction {

	constructor(public bulky: boolean, public readOnly: boolean) {
	}

	protected declaration: SiDeclaration|null = null;
	protected entryId: string|null = null;
	protected partialContentInstruction: SiPartialContentInstruction|null = null;
	protected newEntryRequested = false;
	protected generalControlsIncluded = false;
	protected generalControlsBoundry: SiControlBoundry|null = null;
	protected entryControlsIncluded = false;
	protected propIds: string[]|null = null;
	protected typeIds: string[]|null = null;

	static partialContent(bulky: boolean, readOnly: boolean, offset: number, num: number, quickSearchStr: string|null): SiGetInstruction {
		const instruction = new SiGetInstruction(bulky, readOnly);
		instruction.partialContentInstruction = {
			offset,
			num,
			quickSearchStr
		};
		return instruction;
	}

	static entry(bulky: boolean, readOnly: boolean, entryId: string): SiGetInstruction {
		const instruction = new SiGetInstruction(bulky, readOnly);
		instruction.entryId = entryId;
		return instruction;
	}

	static newEntry(bulky: boolean, readOnly: boolean): SiGetInstruction {
		const instruction = new SiGetInstruction(bulky, readOnly);
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

	getGeneralControlsIncludes(): boolean {
		return this.generalControlsIncluded;
	}

	setGeneralControlsIncluded(controlsIncluded: boolean): SiGetInstruction {
		this.generalControlsIncluded = controlsIncluded;
		return this;
	}

	setGeneralControlsBoundry(generalControlsBoundry: SiControlBoundry|null) {
		this.generalControlsBoundry = generalControlsBoundry;
	}

	getGeneralControlsBoundry(): SiControlBoundry|null {
		return this.generalControlsBoundry;
	}

	getEntryControlsIncludes(): boolean {
		return this.entryControlsIncluded;
	}

	setEntryControlsIncluded(controlsIncluded: boolean): SiGetInstruction {
		this.entryControlsIncluded = controlsIncluded;
		return this;
	}

	getPropIds(): string[]|null {
		return this.propIds;
	}

	setPropIds(propIds: string[]|null): SiGetInstruction {
		this.propIds = propIds;
		return this;
	}

	getTypeIds(): string[]|null {
		return this.typeIds;
	}

	setTypeIds(typeIds: string[]|null): SiGetInstruction {
		this.typeIds = typeIds;
		return this;
	}

	toJSON() {
		return {
			bulky: this.bulky,
			readOnly: this.readOnly,
			declarationRequested: !this.declaration,
			generalControlsIncluded: this.generalControlsIncluded,
			entryControlsIncluded: this.entryControlsIncluded,
			entryId: this.entryId,
			propIds: this.propIds,
			typeIds: this.typeIds,
			partialContentInstruction: this.partialContentInstruction,
			newEntryRequested: this.newEntryRequested
		};
	}
}

export interface SiPartialContentInstruction {
	offset: number;
	num: number;
	quickSearchStr: string|null;
}
