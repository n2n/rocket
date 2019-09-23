import { SiEmbeddedEntry } from 'src/app/si/model/entity/impl/embedded/si-embedded-entry';
import { SiStructure } from 'src/app/si/model/structure/si-structure';
import { SiEntry } from 'src/app/si/model/entity/si-entry';

export class Embe {
	constructor(public siEmbeddedEntry: SiEmbeddedEntry|null = null,
			public siStructure: SiStructure|null = null,
			public summarySiStructure: SiStructure|null = null) {
	}

	isPlaceholder(): boolean {
		return !this.siEmbeddedEntry;
	}

	get siEntry(): SiEntry {
		return this.siEmbeddedEntry.entry;
	}
}
