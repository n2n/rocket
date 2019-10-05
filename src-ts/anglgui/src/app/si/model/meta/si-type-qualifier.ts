
export interface SiTypeIdentifier {
	category: string;
	id: string;
}

export class SiTypeQualifier implements SiTypeIdentifier {
	constructor(readonly category: string, readonly id: string, public name: string, public iconClass: string) {
	}

	equals(arg: SiTypeQualifier): boolean {
		return arg instanceof SiTypeQualifier && this.id === arg.id;
	}
}
