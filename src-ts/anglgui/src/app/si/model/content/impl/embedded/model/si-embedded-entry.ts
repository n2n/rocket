
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { BulkyEntrySiComp } from 'src/app/si/model/comp/impl/model/bulky-entry-si-comp';
import { CompactEntrySiComp } from 'src/app/si/model/comp/impl/model/compact-entry-si-comp';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';
import { Fresult } from 'src/app/util/err/fresult';
import { SiGenericEmbeddedEntry } from './si-generic-embedded-entry';

export class SiEmbeddedEntry {

	constructor(public comp: BulkyEntrySiComp, public summaryComp: CompactEntrySiComp|null) {
	}

	get entry(): SiEntry {
		return this.comp.entry;
	}

	set entry(entry: SiEntry) {
		this.comp.entry = entry;
	}

	readGeneric(): SiGenericEmbeddedEntry {
		return new SiGenericEmbeddedEntry(this.comp.entry.readGeneric(),
				(this.summaryComp ? this.summaryComp.entry.readGeneric() : null));
	}

	writeGeneric(genericEmbeddedEntry: SiGenericEmbeddedEntry): Fresult<GenericMissmatchError> {
		const result = this.comp.entry.writeGeneric(genericEmbeddedEntry.genericEntry);

		if (!result.isValid()) {
			return result;
		}

		if (this.summaryComp && genericEmbeddedEntry.summaryGenericEntry) {
			return this.summaryComp.entry.writeGeneric(genericEmbeddedEntry.summaryGenericEntry);
		}

		return Fresult.success();
	}
}

