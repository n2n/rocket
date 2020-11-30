
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { BulkyEntrySiGui } from 'src/app/si/model/gui/impl/model/bulky-entry-si-gui';
import { CompactEntrySiGui } from 'src/app/si/model/gui/impl/model/compact-entry-si-gui';
import { SiMaskQualifier } from 'src/app/si/model/meta/si-mask-qualifier';
import { SiGenericEmbeddedEntry, SiEmbeddedEntryResetPoint } from './generic/generic-embedded';

export class SiEmbeddedEntry {

	constructor(public comp: BulkyEntrySiGui, public summaryComp: CompactEntrySiGui|null) {
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

	get maskQualifiers(): SiMaskQualifier[] {
		return this.entry.maskQualifiers;
	}

	get selectedTypeId(): string|null {
		return this.entry.selectedTypeId;
	}

	set selectedTypeId(typeId: string|null) {
		this.comp.entry.selectedTypeId = typeId;
		if (this.summaryComp.entry) {
			this.summaryComp.entry.selectedTypeId = typeId;
		}
	}

	containsTypeId(typeId: string): boolean {
		return this.entry.containsTypeId(typeId);
	}

	resetToPoint(genericEmbeddedEntry: SiGenericEmbeddedEntry): void {
		this.comp.entry.resetToPoint(genericEmbeddedEntry.genericEntry);

		if (this.summaryComp && genericEmbeddedEntry.summaryGenericEntry) {
			this.summaryComp.entry.resetToPoint(genericEmbeddedEntry.summaryGenericEntry);
		}
	}
}

