import { SiEntry } from 'src/app/si/model/content/si-entry';
import { IllegalSiStateError } from 'src/app/si/model/illegal-si-state-error';

export class SiPage {
	constructor(readonly num: number, public entries: SiEntry[]|null,
			public offsetHeight: number|null) {
		if (num < 1) {
			throw new IllegalSiStateError('Illegal page no: ' + num);
		}
	}

	get loaded(): boolean {
		return !!this.entries;
	}

	get visible(): boolean {
		return this.offsetHeight !== null;
	}
}
