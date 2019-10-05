
export interface SiTypeIdentifier {
	category: string;
	id: string;
}

export class SiTypeQualifier implements SiTypeIdentifier {
	constructor(public category: string, public id: string, public name: string, public iconClass: string) {
	}

	equals(arg: SiTypeQualifier) {
		return arg instanceof SiTypeQualifier && this.id === arg.id;
	}
}
