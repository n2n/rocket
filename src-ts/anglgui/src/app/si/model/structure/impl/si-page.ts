import { SiEntry } from "src/app/si/model/content/si-entry";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";

export class SiPage {
	constructor(readonly number: number, readonly entries: SiEntry[]) {
		if (number < 1) {
			throw new IllegalSiStateError('Illegal page no: ' + number);
		}
	}
}