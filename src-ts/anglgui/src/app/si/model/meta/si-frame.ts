import { SiTypeContext } from './si-type-context';

export class SiFrame {
	public sortable = false;

	constructor(public apiUrl: string, public typeContext: SiTypeContext) {
	}
}
