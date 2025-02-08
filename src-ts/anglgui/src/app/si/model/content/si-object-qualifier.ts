import { SiMaskIdentifier } from '../meta/si-mask-qualifier';

export interface SiObjectIdentifier {
	superTypeId: string;
	id: string|null;
}


export class SiObjectQualifier implements SiObjectIdentifier {
	constructor(readonly superTypeId: string, readonly id: string|null, readonly idName: string|null) {
	}

	equals(obj: any): boolean {
		return obj instanceof SiObjectQualifier
				&& this.superTypeId === obj.superTypeId
				&& this.id === obj.id;
	}

	matchesObjectIdentifier(objectIdentifier: SiObjectIdentifier): boolean {
		return this.superTypeId === objectIdentifier.superTypeId
				&& this.id === objectIdentifier.id;
	}

	toString(): string {
		return this.superTypeId + '#' + this.id + ' (' + this.idName + ')';
	}
}
