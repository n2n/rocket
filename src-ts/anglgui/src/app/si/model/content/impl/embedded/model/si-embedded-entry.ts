
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { BulkyEntrySiComp } from 'src/app/si/model/comp/impl/model/bulky-entry-si-comp';
import { CompactEntrySiComp } from 'src/app/si/model/comp/impl/model/compact-entry-si-comp';
import { SiGenericEmbeddedEntry, SiEmbeddedEntryResetPoint } from './generic-embedded';

export class SiEmbeddedEntry {

	constructor(public comp: BulkyEntrySiComp, public summaryComp: CompactEntrySiComp|null) {
	}

	get entry(): SiEntry {
		return this.comp.entry;
	}

	set entry(entry: SiEntry) {
		this.comp.entry = entry;
	}

	copy(): SiGenericEmbeddedEntry {
		return new SiGenericEmbeddedEntry(this.comp.entry.copy(),
				(this.summaryComp ? this.summaryComp.entry.copy() : null));
	}

	paste(genericEmbeddedEntry: SiGenericEmbeddedEntry): Promise<void> {
		const promise = this.comp.entry.paste(genericEmbeddedEntry.genericEntry);

		if (this.summaryComp && genericEmbeddedEntry.summaryGenericEntry) {
			return Promise.all([promise, this.summaryComp.entry.paste(genericEmbeddedEntry.summaryGenericEntry)])
					.then(() => {});
		}

		return promise;
	}

	createResetPoint(): SiEmbeddedEntryResetPoint {
		const genericEmbeddedEntry = new SiGenericEmbeddedEntry(this.comp.entry.createResetPoint(),
					(this.summaryComp ? this.summaryComp.entry.createResetPoint() : null));

		return {
			origSiEmbeddedEntry: this,
			genericEmbeddedEntry
		};
	}

	resetToPoint(genericEmbeddedEntry: SiGenericEmbeddedEntry): void {
		this.comp.entry.resetToPoint(genericEmbeddedEntry.genericEntry);

		if (this.summaryComp && genericEmbeddedEntry.summaryGenericEntry) {
			this.summaryComp.entry.resetToPoint(genericEmbeddedEntry.summaryGenericEntry);
		}
	}
}

