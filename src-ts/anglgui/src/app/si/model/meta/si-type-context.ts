
export class SiTypeContext {
	constructor(public typeId: string, public entryBuildupIds: string[]) {
	}

	containsTypeId(typeId: string): boolean {
		return this.typeId === typeId || -1 !== this.entryBuildupIds.indexOf(typeId);
	}
}
