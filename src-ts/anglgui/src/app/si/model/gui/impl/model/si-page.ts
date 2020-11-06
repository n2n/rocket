import { SiEntry, SiEntryState } from 'src/app/si/model/content/si-entry';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { IllegalArgumentError } from 'src/app/si/util/illegal-argument-error';
import { SiEntryMonitor } from '../../../mod/model/si-entry-monitor';
import { Observable, Subscription, Subject } from 'rxjs';

export class SiPage {
	private _entries: Array<SiEntry>|null = null;
	private entriesSubscription: Subscription|null = null;
	private loadSubject: Subject<SiEntry[]>|null = null;

	constructor(private entryMonitor: SiEntryMonitor, readonly no: number,
			entries: SiEntry[]|null, public offsetHeight: number|null) {
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

	onLoad(callback: (entries: SiEntry[]) => any) {
		if (this.entries) {
			callback(this.entries);
			return;
		}

		if (!this.loadSubject) {
			this.loadSubject = new Subject();
		}

		this.loadSubject.subscribe((entries) => {
			callback(entries);
		});
	}

	private removeEntries() {
		if (!this._entries) {
			return;
		}

		for (const entry of this._entries) {
			this.entryMonitor.unregisterEntry(entry);
		}

		this.entriesSubscription.unsubscribe();
		this._entries = null;

		this.entryMonitor.unregisterAllEntries();
		this.entryMonitor.stop();
	}

	private applyEntries(newEntries: SiEntry[]|null) {
		this.removeEntries();

		if (!newEntries) {
			return;
		}

		this._entries = [];
		this.entriesSubscription = new Subscription();
		for (const newEntry of newEntries) {
			const i = this._entries.length;
			this._entries.push(newEntry);
			this.entriesSubscription.add(newEntry.state$.subscribe((state) => {
				if (state === SiEntryState.REPLACED) {
					this._entries[i] = newEntry.replacementEntry;
				}
			}));
			this.entryMonitor.registerEntry(newEntry);
		}

		this.entryMonitor.start();

		this.loadSubject.next(this.entries);
		this.loadSubject.complete();
		this.loadSubject = null;
	}

	dipose() {
		this.removeEntries();
	}
}
