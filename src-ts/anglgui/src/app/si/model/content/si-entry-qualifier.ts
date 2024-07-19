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

export class SiEntryQualifier extends SiMaskQualifier {

	constructor(readonly entryIdentifier: SiEntryIdentifier,
			public idName: string|null, name: string, iconClass: string) {
		super(entryIdentifier.maskIdentifier, name, iconClass);
	}

	/**
	 * @deprecated
	 */
	get maskQualifier(): SiMaskQualifier {
		return this;
	}

	getBestName(): string {
		return this.idName || this.name;
	}

	equals(obj: any): boolean {
		return obj instanceof SiEntryQualifier
				&& this.entryIdentifier.equals(obj.entryIdentifier)
				&& this.maskIdentifier.matches(obj.maskIdentifier);
	}

	override toString(): string {
		return this.idName + ' (' + this.entryIdentifier.toString() + ')';
	}
}
