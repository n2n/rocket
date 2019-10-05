import { SiType } from 'src/app/si/model/entity/si-type';

export class SiEntryIdentifier {
	constructor(public category: string , public id: string|null) {

	}

	equals(obj: any): boolean {
		return obj instanceof SiEntryIdentifier && this.category === ( obj as SiEntryIdentifier).category
				&& this.id === ( obj as SiEntryIdentifier).id;
	}
}

export class SiEntryQualifier extends SiEntryIdentifier {
	constructor(category: string, id: string|null, public type: SiType, public idName: string|null) {
		super(category, id);
	}

	equals(obj: any): boolean {
		return obj instanceof SiEntryQualifier && super.equals(obj);
	}
}
