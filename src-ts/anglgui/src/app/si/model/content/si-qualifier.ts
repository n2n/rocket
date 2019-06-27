export interface SiIdentifier {
	category: string;
	id: string|null;
}

export class SiQualifier implements SiIdentifier {
	constructor(public category: string, public id: string|null, public name: string) {
	}
}