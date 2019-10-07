import { SiTypeIdentifier, SiTypeQualifier } from '../meta/si-type-qualifier';

export class SiEntryIdentifier {
	constructor(readonly typeCategory: string, readonly id: string|null) {

	}

	equals(obj: any): boolean {
		return obj instanceof SiEntryIdentifier && this.typeCategory === ( obj as SiEntryIdentifier).typeCategory
				&& this.id === ( obj as SiEntryIdentifier).id;
	}
}

export class SiEntryQualifier extends SiEntryIdentifier {
	constructor(readonly typeQualifier: SiTypeQualifier, id: string|null, public idName: string|null) {
		super(typeQualifier.category, id);
	}

	getBestName(): string {
		return this.idName || this.typeQualifier.name;
	}

	equals(obj: any): boolean {
		return obj instanceof SiEntryQualifier && super.equals(obj);
	}

	toString(): string {
		return this.typeQualifier.name + '#' + this.id;
	}
}
