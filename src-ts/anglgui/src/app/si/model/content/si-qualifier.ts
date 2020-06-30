import { SiMaskQualifier } from '../meta/si-mask-qualifier';

export class SiEntryIdentifier {
	constructor(readonly typeId: string, readonly id: string|null) {

	}

	equals(obj: any): boolean {
		return obj instanceof SiEntryIdentifier && this.typeId === ( obj as SiEntryIdentifier).typeId
				&& this.id === ( obj as SiEntryIdentifier).id;
	}

	toString(): string {
		return this.typeId + '#' + this.id;
	}
}

export class SiEntryQualifier {

	constructor(readonly maskQualifier: SiMaskQualifier, readonly identifier: SiEntryIdentifier,
			public idName: string|null) {
	}

	getBestName(): string {
		return this.idName || this.maskQualifier.name;
	}

	equals(obj: any): boolean {
		return obj instanceof SiEntryQualifier && this.maskQualifier.identifier.matches(obj.maskQualifier.identifier);
	}

	toString(): string {
		return this.idName + ' (' + this.identifier.toString() + ')';
	}
}
