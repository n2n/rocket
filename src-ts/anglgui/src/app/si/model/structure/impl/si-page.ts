import { SiEntry } from "src/app/si/model/content/si-entry";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";

export class SiPage {
	constructor(readonly number: number, public entries: SiEntry[]|null,
			public offsetHeight: number|null) {
		if (number < 1) {
			throw new IllegalSiStateError('Illegal page no: ' + number);
		}
	}
	
	get loaded(): boolean {
		return !!this.entries;
	}
	
	get visible(): boolean {
		return this.offsetHeight !== null;
	}
}