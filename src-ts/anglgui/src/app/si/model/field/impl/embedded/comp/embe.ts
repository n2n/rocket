import { SiEmbeddedEntry } from 'src/app/si/model/entity/impl/embedded/si-embedded-entry';
import { UiStructure } from 'src/app/si/model/structure/ui-structure';
import { SiEntry } from 'src/app/si/model/entity/si-entry';

export class Embe {
	constructor(public siEmbeddedEntry: SiEmbeddedEntry|null = null,
			public siStructure: UiStructure|null = null,
			public summarySiStructure: UiStructure|null = null) {
	}

	isPlaceholder(): boolean {
		return !this.siEmbeddedEntry;
	}

	get siEntry(): SiEntry {
		return this.siEmbeddedEntry.entry;
	}
}
