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
	private nextReloadJob: ReloadJob|null = null;

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

	start() {
		if (this.subscription) {
			throw new IllegalSiStateError('Monitor already started.');
		}

		this.subscription = this.modStateService.modEvent$.subscribe((modEvent) => {
			this.handleEvent(modEvent);
		});
	}

	private handleEvent(modEvent: SiModEvent) {
		for (const siEntryIdentifier of modEvent.updated || []) {
			if (!this.entriesMap.has(siEntryIdentifier.toString())) {
				continue;
			}

			this.nextReloadJob.execute();
		}
	}

	stop() {
		if (this.subscription) {
			this.subscription.unsubscribe();
		}

		this.subscription = null;
	}

	get display(): boolean {
		return this.modStateService.containsModEntryIdentifier(entry);
	}
}

class ReloadJob {
	siEntries: Map<SiEntry, SiEntry> = [];

	constructor(private apiUrl: string, private siService: SiService) {
	}

	execute() {
		const getInstructions: SiGetInstruction[] = [];
		const entries: SiEntry[] = [];
		for (const entry of this.siEntries) {
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
		for (const i of results.keys()) {
			entries[i].consume(results[i].entry);
			entries[i].markAsClean();
		}
	}
}

interface SiEntryLoad {
	outdated: SiEntry;
	loaded: SiEntry;
}
