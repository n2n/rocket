import { SiModStateService, SiModEvent } from './si-mod-state.service';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiEntry, SiEntryState } from '../../content/si-entry';
import { SiService } from 'src/app/si/manage/si.service';
import { SiGetRequest } from '../../api/si-get-request';
import { SiGetInstruction } from '../../api/si-get-instruction';
import { IllegalArgumentError } from 'src/app/si/util/illegal-argument-error';
import { SiGetResponse } from '../../api/si-get-response';
import { SiResult } from 'src/app/si/manage/si-result';
import { SiGetResult } from '../../api/si-get-result';
import { Subscription } from 'rxjs';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { IllegalStateError } from 'src/app/util/err/illegal-state-error';

export class SiEntryMonitor {

	private entriesMap = new Map<string, SiEntry[]>();
	private nextReloadJob = new ReloadJob();

	constructor(private apiUrl: string, private siService, private modStateService: SiModStateService) {
	}

	registerEntry(entry: SiEntry) {
		const id = entry.identifier.id;
		if (!id) {
			throw new IllegalArgumentError('New entry can not be monitored.');
		}

		if (!this.entriesMap.has(id)) {
			this.entriesMap.set(id, []);
		}

		const entries = this.entriesMap.get(id)

		if (-1 < entries.indexOf(entry)) {
			throw new IllegalArgumentError('Entry already registered.');
		}

		entries.push(entry);
	}

	unregisterEntry(entry: SiEntry) {
		const id = entry.identifier.id;
		if (!this.entriesMap.has(id)) {
			throw new IllegalStateError('Entry not registed.');
		}

		const entries = this.entriesMap.get(id);
		const i = entries.indexOf(entry);
		entries.splice(i, 1);
	}

	private subscription: Subscription|null = null;

	start(loadCallback: (siEntryLoads: SiEntryLoad[]) => any) {
		if (this.subscription) {
			throw new IllegalSiStateError('Monitor already started.');
		}

		this.subscription = this.modStateService.modEvent$.subscribe((modEvent) => {
			this.handleEvent(modEvent, loadCallback);
		});

		this.subscription.add(this.modStateService.shownEntry$.subscribe((siEntry) => {
			if (this.nextReloadJob.containsSiEntry(siEntry)) {
				this.executeNextReloadJob(loadCallback);
			}
		}))
	}

	private handleEvent(modEvent: SiModEvent) {
		for (const siEntryIdentifier of modEvent.removed || []) {
			const id = siEntryIdentifier.id;
			if (!this.entriesMap.has(id)) {
				continue;
			}

			for (const siEntry of this.entriesMap.get(id)) {
				siEntry.markAsRemoved();
			}
		}

		for (const siEntryIdentifier of modEvent.updated || []) {
			const id = siEntryIdentifier.id;
			if (!this.entriesMap.has(id)) {
				continue;
			}

			for (const siEntry of this.entriesMap.get(id)) {
				siEntry.markAsOutdated();
				this.nextReloadJob.addSiEntry(siEntry);
			}
		}
	}

	private executeNextReloadJob() {
		

	}

	stop() {
		if (this.subscription) {
			this.subscription.unsubscribe();
		}

		this.subscription = null;
	}
}

class ReloadJob {
	private siEntriesMap = new Map<SiEntry, SiEntry>();

	constructor(private apiUrl: string, private siService: SiService) {
	}

	containsSiEntry(entry: SiEntry) {
		return this.siEntriesMap.has(entry);
	}

	addSiEntry(siEntry: SiEntry) {
		this.siEntriesMap.set(siEntry, siEntry);
	}

	execute() {
		const getInstructions: SiGetInstruction[] = [];
		const entries: SiEntry[] = [];
		for (const [, entry] of this.siEntriesMap) {
			if (entry.state !== SiEntryState.OUTDATED) {
				continue;
			}

			entry.markAsReloading();
			getInstructions.push(SiGetInstruction.entry(entry.bulky, entry.readOnly, entry.identifier.id));
			entries.push(entry);
		}

		this.siService.apiGet(this.apiUrl, new SiGetRequest(...getInstructions))
				.subscribe((response: SiGetResponse) => {
					this.handleResults(response.results, entries);
				});
	}

	private handleResults(results: SiGetResult[], entries: SiEntry[]) {
		const entryLoads: SiEntryLoad[] = [];

		for (const i of results.keys()) {
			entryLoads.push({
				outdated: entries[i],
				loaded: results[i].entry
			});
		}
	}
}

interface SiEntryLoad {
	outdated: SiEntry;
	loaded: SiEntry;
}
