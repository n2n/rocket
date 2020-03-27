
export class SiTypeContext {
	constructor(public contextTypeId: string, public subTypeIds: string[]) {
	}

	containsTypeId(typeId: string): boolean {
		return this.contextTypeId === typeId || -1 !== this.subTypeIds.indexOf(typeId);
	}
}
