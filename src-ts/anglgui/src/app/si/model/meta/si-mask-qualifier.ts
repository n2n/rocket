export class SiMaskIdentifier {
	constructor(readonly id: string, readonly typeId: string) {

	}

	matches(arg: SiMaskIdentifier): boolean {
		return arg instanceof SiMaskIdentifier && this.id === arg.id;
	}
}

export class SiMaskQualifier extends SiMaskIdentifier {
	constructor(readonly id: string, readonly typeId: string, public name: string, public iconClass: string) {
		super(id, typeId);
	}

}
