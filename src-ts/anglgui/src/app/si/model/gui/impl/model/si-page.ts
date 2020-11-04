import { SiEntry } from 'src/app/si/model/content/si-entry';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';

export class SiPage {
	constructor(readonly no: number, public entries: SiEntry[]|null,
			public offsetHeight: number|null) {
		if (no < 1) {
			throw new IllegalSiStateError('Illegal page no: ' + no);
		}
	}

	get loaded(): boolean {
		return !!this.entries;
	}

	get visible(): boolean {
		return this.offsetHeight !== null;
	}
}
