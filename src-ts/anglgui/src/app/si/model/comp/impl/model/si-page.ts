import { SiEntry } from 'src/app/si/model/content/si-entry';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';

export class SiPage {
	constructor(readonly num: number, public entries: SiEntry[]) {
		if (num < 1) {
			throw new IllegalSiStateError('Illegal page no: ' + num);
		}
	}
}
