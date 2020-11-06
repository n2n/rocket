import { SiEntry, SiEntryState } from 'src/app/si/model/content/si-entry';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { IllegalArgumentError } from 'src/app/si/util/illegal-argument-error';

export class SiPage {
	private _entries: Array<SiEntry>|null = null;

	constructor(readonly no: number, entries: SiEntry[]|null, public offsetHeight: number|null) {
		if (no < 1) {
			throw new IllegalSiStateError('Illegal page no: ' + no);
		}

		if (entries) {
			this.applyEntries(entries);
		}
	}

	get loaded(): boolean {
		return !!this._entries;
	}

	get visible(): boolean {
		return this.offsetHeight !== null;
	}

	get entries(): SiEntry[]|null {
		return this._entries;
	}

	set entries(entries: SiEntry[]|null) {
		IllegalArgumentError.assertTrue(entries !== null);
		this.applyEntries(entries);
	}

	private applyEntries(entries: SiEntry[]) {
		this._entries = [];
		for (const entry of entries) {
			const i = this._entries.length;
			this._entries.push(entry);
			entry.state$.subscribe((state) => {
				if (state === SiEntryState.REPLACED) {
					this._entries[i] = entry.replacementEntry;
				}
			});
		}
	}
}
