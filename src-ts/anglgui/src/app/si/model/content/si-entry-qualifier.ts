import { SiMaskIdentifier, SiMaskQualifier } from '../meta/si-mask-qualifier';

export interface SiObjectIdentifier {
	typeId: string;
	id: string|null;
}

export class SiEntryIdentifier implements SiObjectIdentifier {
	constructor(readonly maskIdentifier: SiMaskIdentifier, readonly id: string|null) {
	}

	get typeId(): string {
		return this.maskIdentifier.typeId;
	}

	equals(obj: any): boolean {
		return obj instanceof SiEntryIdentifier
				&& this.maskIdentifier.matches((obj as SiEntryIdentifier).maskIdentifier)
				&& this.id === (obj as SiEntryIdentifier).id;
	}

	matchesTypeAndId(otherIdentifier: SiEntryIdentifier): boolean {
		return this.id === otherIdentifier.id && this.maskIdentifier.typeId === otherIdentifier.maskIdentifier.typeId;
	}

	toString(): string {
		return this.maskIdentifier.id + '#' + this.id;
	}
}

export class SiEntryQualifier {

	constructor(readonly identifier: SiEntryIdentifier, public idName: string|null) {

		// if (this.maskQualifier.identifier.typeId !== identifier.typeId) {
		// 	throw new Error('Identifiers do not match: ' + maskQualifier.identifier.typeId + ' != ' + identifier.typeId);
		// }
	}

	// getBestName(): string {
	// 	return this.idName || this.identifiermaskQualifier.name;
	// }

	equals(obj: any): boolean {
		return obj instanceof SiEntryQualifier
				&& this.identifier.equals(obj.identifier)
				&& this.identifier.maskIdentifier.matches(obj.identifier.maskIdentifier);
	}

	toString(): string {
		return this.idName + ' (' + this.identifier.toString() + ')';
	}
}
