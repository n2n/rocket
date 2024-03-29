export class SiMaskIdentifier {
	constructor(readonly id: string, readonly typeId: string) {

	}

	matches(arg: SiMaskIdentifier): boolean {
		return this.id === arg.id;
	}
}

export class SiMaskQualifier {
	constructor(readonly identifier: SiMaskIdentifier, public name: string, public iconClass: string) {
	}
}
