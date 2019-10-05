import { SiTypeIdentifier, SiTypeQualifier } from '../meta/si-type-qualifier';

export class SiEntryIdentifier {
	constructor(readonly typeIdentifier: SiTypeIdentifier, readonly id: string|null) {

	}

	equals(obj: any): boolean {
		return obj instanceof SiEntryIdentifier && this.typeIdentifier.category === ( obj as SiEntryIdentifier).typeIdentifier.category
				&& this.id === ( obj as SiEntryIdentifier).id;
	}
}

export class SiEntryQualifier extends SiEntryIdentifier {
	constructor(readonly typeQualifier: SiTypeQualifier, id: string|null, public idName: string|null) {
		super(typeQualifier, id);
	}

	get typeIdentifier(): SiTypeIdentifier {
		return this.typeQualifier;
	}

	equals(obj: any): boolean {
		return obj instanceof SiEntryQualifier && super.equals(obj);
	}
}
