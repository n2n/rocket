
import { SiDeclaration } from '../meta/si-declaration';
import { SiControlBoundry } from '../control/si-control-boundry';
import { SiEntryIdentifier } from '../content/si-entry-qualifier';

export class SiGetInstruction {

	constructor(protected contextMaskId: string) {
	}

	protected declaration: SiDeclaration|null = null;
	protected entryId: string|null = null;
	protected partialContentInstruction: SiPartialContentInstruction|null = null;
	protected newEntryRequested = false;
	protected generalControlsIncluded = false;
	protected generalControlsBoundary: SiControlBoundry|null = null;
	protected entryControlsIncluded = false;
	protected allowedFieldNames: string[]|null = null;
	protected allowedMaskIds: string[]|null = null;

	static partialContent(maskId: string, offset: number, num: number, quickSearchStr: string|null): SiGetInstruction {
		const instruction = new SiGetInstruction(maskId);
		instruction.contextMaskId = maskId;
		instruction.partialContentInstruction = {
			offset,
			num,
			quickSearchStr
		};
		return instruction;
	}

	static entryFromIdentifier(entryIdentifier: SiEntryIdentifier): SiGetInstruction {
		if (entryIdentifier.id === null) {
			return SiGetInstruction.newEntry(entryIdentifier.maskIdentifier.id)
		}

		return SiGetInstruction.entry(entryIdentifier.maskIdentifier.id, entryIdentifier.id);
	}

	static entry(contextMaskId: string, entryId: string): SiGetInstruction {
		const instruction = new SiGetInstruction(contextMaskId);
		instruction.contextMaskId = contextMaskId;
		instruction.entryId = entryId;
		return instruction;
	}

	static newEntry(contextMaskId: string): SiGetInstruction {
		const instruction = new SiGetInstruction(contextMaskId);
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

	setGeneralControlsBoundry(generalControlsBoundry: SiControlBoundry|null): SiGetInstruction {
		this.generalControlsBoundary = generalControlsBoundry;
		return this;
	}

	getGeneralControlsBoundry(): SiControlBoundry|null {
		return this.generalControlsBoundary;
	}

	getEntryControlsIncludes(): boolean {
		return this.entryControlsIncluded;
	}

	setEntryControlsIncluded(controlsIncluded: boolean): SiGetInstruction {
		this.entryControlsIncluded = controlsIncluded;
		return this;
	}

	getPropIds(): string[]|null {
		return this.allowedFieldNames;
	}

	setPropIds(propIds: string[]|null): SiGetInstruction {
		this.allowedFieldNames = propIds;
		return this;
	}

	getTypeIds(): string[]|null {
		return this.allowedMaskIds;
	}

	setAllowedMaskIds(maskIds: string[]|null): SiGetInstruction {
		this.allowedMaskIds = maskIds;
		return this;
	}

	toJSON(): object {
		return {
			maskId: this.contextMaskId,
			declarationRequested: !this.declaration,
			generalControlsIncluded: this.generalControlsIncluded,
			entryControlsIncluded: this.entryControlsIncluded,
			entryId: this.entryId,
			propIds: this.allowedFieldNames,
			typeIds: this.allowedMaskIds,
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
