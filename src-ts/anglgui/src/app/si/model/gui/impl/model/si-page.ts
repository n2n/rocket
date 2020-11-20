import { SiEntry, SiEntryState } from 'src/app/si/model/content/si-entry';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiEntryMonitor } from '../../../mod/model/si-entry-monitor';
import { Subscription, Subject, Observable } from 'rxjs';

export class SiPage {
	private _entries: Array<SiEntry>|null = null;
	private _size: number|null = null;
	private _ghostSize: number|null = null;
	private entriesSubscription: Subscription|null = null;
	private loadSubject: Subject<SiEntry[]>|null = null;
	private disposedSubject = new Subject<void>();
	private entryRemovedSubject = new Subject<SiEntry>();

	constructor(private entryMonitor: SiEntryMonitor, readonly no: number,
			public offset: number, entries: SiEntry[]|null) {
		if (no < 1) {
			throw new IllegalSiStateError('Illegal page no: ' + no);
		}

		if (entries) {
			this.applyEntries(entries);
		}

		this.recalcSize();
	}

	get loaded(): boolean {
		this.ensureNotDisposed();

		return !!this._entries;
	}

	get entries(): SiEntry[]|null {
		this.ensureNotDisposed();

		return this._entries;
	}

	set entries(entries: SiEntry[]|null) {
		this.ensureNotDisposed();

		this.applyEntries(entries);
	}

	private ensureLoaded() {
		if (this.loaded) {
			return;
		}

		throw new IllegalSiStateError('Page not loaded.');
	}

	get size(): number {
		this.ensureLoaded();

		if (this._size === null) {
			throw new IllegalSiStateError('Size not set.');
		}

		return this._size;
	}

	get ghostSize(): number {
		this.ensureLoaded();

		if (this._ghostSize === null) {
			throw new IllegalSiStateError('Ghost size not set.');
		}

		return this._ghostSize;
	}

	private recalcSize(): number {
		if (!this._entries) {
			this._size = null;
			this._ghostSize = null;
			return;
		}

		this._size = 0;
		this._ghostSize = 0;
		for (const entry of this._entries) {
			if (entry.isAlive()) {
				this._size++;
			} else {
				this._ghostSize++;
			}
		}
	}

	onLoad(callback: (entries: SiEntry[]) => any) {
		this.ensureNotDisposed();

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

		this.recalcSize();
	}

	private applyEntries(newEntries: SiEntry[]|null) {
		this.removeEntries();

		if (!newEntries) {
			return;
		}

		this._entries = [];
		this.entriesSubscription = new Subscription();
		for (const newEntry of newEntries) {
			this.placeEntry(this._entries.length, newEntry);
		}

		this.entryMonitor.start();

		if (this.loadSubject) {
			this.loadSubject.next(this.entries);
			this.loadSubject.complete();
			this.loadSubject = null;
		}

		this.recalcSize();
	}

	private placeEntry(i: number, newEntry: SiEntry) {
		if (this._entries[i]) {
			this.entryMonitor.unregisterEntry(this._entries[i]);
		}

		this._entries[i] = newEntry;

		this.entriesSubscription.add(newEntry.state$.subscribe((state) => {
			switch (state) {
				case SiEntryState.REPLACED:
					this.placeEntry(i, newEntry.replacementEntry);
					break;
				case SiEntryState.REMOVED:
					this.recalcSize();
					this.entryRemovedSubject.next(newEntry);
					break;
			}
		}));
		this.entryMonitor.registerEntry(newEntry);
	}

	get entryRemoved$(): Observable<SiEntry> {
		return this.entryRemovedSubject.asObservable();
	}

	private ensureNotDisposed() {
		if (this.disposed) {
			throw new IllegalSiStateError('SiPage disposed.');
		}
	}

	get disposed(): boolean {
		return this.disposedSubject.isStopped;
	}

	get disposed$(): Observable<void> {
		return this.disposedSubject.asObservable();
	}

	dipose() {
		if (this.disposed) {
			return;
		}

		this.removeEntries();

		this.entryRemovedSubject.complete();
		this.disposedSubject.next();
		this.disposedSubject.complete();
	}
}
